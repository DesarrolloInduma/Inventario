<?php

namespace App\Http\Controllers;

use App\Models\Hardware;
use App\Models\Software_NoLicenciado;
use App\Models\Tipo_Software;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SoftwareNoLicenciadoController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));

        $software = Software_NoLicenciado::with(['tipoSoftware', 'hardwares'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where('Softwarenl_Nombre', 'like', "%{$q}%")
                    ->orWhere('Softwarenl_Version', 'like', "%{$q}%");
            })
            ->orderBy('Softwarenl_Nombre')
            ->paginate(15)
            ->withQueryString();

        return view('software.nolicenciado.index', compact('software', 'q'));
    }

    public function create()
    {
        return view('software.nolicenciado.create', [
            'software' => new Software_NoLicenciado(),
            'tipos' => Tipo_Software::orderBy('Soft_Tipo_Nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Software_NoLicenciado::create($this->validated($request));

        return redirect()->route('softnl.index')->with('ok', 'Software no licenciado creado.');
    }

    public function show(string $id)
    {
        $software = Software_NoLicenciado::with(['tipoSoftware', 'hardwares.usuarioInv'])->findOrFail($id);

        return view('software.nolicenciado.show', [
            'software' => $software,
            'equipos' => Hardware::whereNotIn('Hw_Serial', $software->hardwares->pluck('Hw_Serial'))
                ->orderBy('Hw_Serial')->get(),
        ]);
    }

    public function edit(string $id)
    {
        return view('software.nolicenciado.create', [
            'software' => Software_NoLicenciado::findOrFail($id),
            'tipos' => Tipo_Software::orderBy('Soft_Tipo_Nombre')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        Software_NoLicenciado::whereKey($id)->update($this->validated($request, $id));

        return redirect()->route('softnl.show', $id)->with('ok', 'Software actualizado.');
    }

    public function destroy(string $id)
    {
        Software_NoLicenciado::whereKey($id)->delete();

        return redirect()->route('softnl.index')->with('ok', 'Software eliminado.');
    }

    public function asignar(Request $request, string $id)
    {
        $data = $request->validate([
            'Hw_Serial' => ['required', 'string', 'exists:Hardware,Hw_Serial'],
        ]);

        $software = Software_NoLicenciado::findOrFail($id);
        $software->hardwares()->syncWithoutDetaching([$data['Hw_Serial']]);

        return redirect()->route('softnl.show', $id)->with('ok', "Software asignado a {$data['Hw_Serial']}.");
    }

    public function liberar(string $id, string $serial)
    {
        $software = Software_NoLicenciado::findOrFail($id);
        $software->hardwares()->detach($serial);

        return redirect()->route('softnl.show', $id)->with('ok', "Software liberado de {$serial}.");
    }

    private function validated(Request $request, ?string $ignoreId = null): array
    {
        return $request->validate([
            'Softwarenl_Nombre' => ['required', 'string', 'max:50', Rule::unique('Software_NoLicenciado', 'Softwarenl_Nombre')->where(fn ($query) => $query->where('Softwarenl_Version', $request->input('Softwarenl_Version')))->ignore($ignoreId, 'Softwarenl_Id')],
            'Softwarenl_Version' => ['nullable', 'string', 'max:50'],
            'Soft_Tipo_Id' => ['nullable', 'integer', 'exists:Tipo_Software,Soft_Tipo_Id'],
        ]);
    }
}
