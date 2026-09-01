<?php

namespace App\Http\Controllers;

use App\Models\Hardware;
use App\Models\Software_Licenciado;
use App\Models\Tipo_Software;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SoftwareLicenciadoController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));

        $software = Software_Licenciado::with(['tipoSoftware', 'hardwares'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where('Software_Nombre', 'like', "%{$q}%")
                    ->orWhere('Software_Clave', 'like', "%{$q}%")
                    ->orWhere('Software_Version', 'like', "%{$q}%");
            })
            ->orderBy('Software_Id')
            ->paginate(15)
            ->withQueryString();

        return view('software.licenciado.index', compact('software', 'q'));
    }

    public function create()
    {
        return view('software.licenciado.create', [
            'software' => new Software_Licenciado(),
            'tipos' => Tipo_Software::orderBy('Soft_Tipo_Nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Software_Licenciado::create($data);

        return redirect()->route('softlic.index')->with('ok', 'Software licenciado creado.');
    }

    public function show(string $id)
    {
        $software = Software_Licenciado::with(['tipoSoftware', 'hardwares.usuarioInv'])->findOrFail($id);

        return view('software.licenciado.show', [
            'software' => $software,
            'equipos' => Hardware::whereNotIn('Hw_Serial', $software->hardwares->pluck('Hw_Serial'))
                ->orderBy('Hw_Serial')->get(),
        ]);
    }

    public function edit(string $id)
    {
        return view('software.licenciado.create', [
            'software' => Software_Licenciado::findOrFail($id),
            'tipos' => Tipo_Software::orderBy('Soft_Tipo_Nombre')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $software = Software_Licenciado::findOrFail($id);
        $software->update($this->validated($request, $id));

        return redirect()->route('softlic.show', $id)->with('ok', 'Software actualizado.');
    }

    public function destroy(string $id)
    {
        Software_Licenciado::whereKey($id)->delete();

        return redirect()->route('softlic.index')->with('ok', 'Software eliminado.');
    }

    public function asignar(Request $request, string $id)
    {
        $data = $request->validate([
            'Hw_Serial' => ['required', 'string', 'exists:Hardware,Hw_Serial'],
        ]);

        $software = Software_Licenciado::findOrFail($id);

        if ($software->Software_Cantidad !== null && $software->hardwares()->count() >= (int) $software->Software_Cantidad) {
            return back()->withErrors(['Hw_Serial' => 'Se alcanzó el límite de licencias disponibles.']);
        }

        $software->hardwares()->syncWithoutDetaching([$data['Hw_Serial']]);

        return redirect()->route('softlic.show', $id)->with('ok', "Licencia asignada a {$data['Hw_Serial']}.");
    }

    public function liberar(string $id, string $serial)
    {
        $software = Software_Licenciado::findOrFail($id);
        $software->hardwares()->detach($serial);

        return redirect()->route('softlic.show', $id)->with('ok', "Licencia liberada de {$serial}.");
    }

    private function validated(Request $request, ?string $ignoreId = null): array
    {
        return $request->validate([
            'Soft_Tipo_Id' => ['required', 'integer', 'exists:Tipo_Software,Soft_Tipo_Id'],
            'Software_Nombre' => ['required', 'string', 'max:100', Rule::unique('Software_Licenciado', 'Software_Nombre')->ignore($ignoreId, 'Software_Id')],
            'Software_Clave' => ['nullable', 'string', 'max:50', Rule::unique('Software_Licenciado', 'Software_Clave')->ignore($ignoreId, 'Software_Id')],
            'Software_Cantidad' => ['nullable', 'integer', 'min:0'],
            'Software_Version' => ['nullable', 'string', 'max:50'],
            'Software_Tip_Licenciamiento' => ['nullable', 'string', 'max:50'],
        ]);
    }
}
