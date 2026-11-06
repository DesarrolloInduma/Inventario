<?php

namespace App\Http\Controllers;

use App\Models\Hardware;
use App\Models\Mantenimiento;
use App\Models\MantenimientoFoto;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MantenimientoController extends Controller
{
    private const CHECKS = [
        'RFE' => 'Revisión física y externa',
        'ST' => 'Software instalado',
        'STYM' => 'Sistema operativo y memoria',
        'LP' => 'Limpieza física',
        'LTYM' => 'Limpieza interna y mantenimiento',
        'LM' => 'Libre de malware',
        'O' => 'Otros',
    ];

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));

        $mantenimientos = Mantenimiento::with('hardware')
            ->when($q !== '', function ($query) use ($q) {
                $query->whereTextLike('Hw_Serial', "%{$q}%")
                    ->orWhereTextLike('mantenimiento_Recibe', "%{$q}%")
                    ->orWhereTextLike('mantenimiento_Realiza', "%{$q}%");
            })
            ->orderByDesc('mantenimiento_Id')
            ->paginate(15)
            ->withQueryString();

        return view('mantenimiento.index', [
            'mantenimientos' => $mantenimientos,
            'q' => $q,
            'checks' => self::CHECKS,
        ]);
    }

    public function create(string $serial)
    {
        return view('mantenimiento.create', [
            'hardware' => Hardware::findOrFail($serial),
            'checks' => self::CHECKS,
        ]);
    }

    public function store(Request $request, string $serial)
    {
        Hardware::findOrFail($serial);

        $data = $this->validated($request);
        unset($data['fotos']);
        $data['Hw_Serial'] = $serial;

        foreach (array_keys(self::CHECKS) as $k) {
            $data["mantenimiento_{$k}_C"] = $request->boolean("mantenimiento_{$k}_C");
        }

        $mantenimiento = Mantenimiento::create($data);
        $this->storeFotos($request, $mantenimiento);

        return redirect()
            ->route('hardware.show', $serial)
            ->with('ok', 'Mantenimiento registrado.');
    }

    public function show(string $id)
    {
        return view('mantenimiento.show', [
            'mantenimiento' => Mantenimiento::with(['hardware', 'fotos'])->findOrFail($id),
            'checks' => self::CHECKS,
        ]);
    }

    public function pdf(string $id)
    {
        $mantenimiento = Mantenimiento::with(['hardware.usuarioInv', 'hardware.modelo.marca', 'fotos'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('mantenimiento.pdf', [
            'mantenimiento' => $mantenimiento,
            'checks' => self::CHECKS,
        ])->setPaper('a4');

        $filename = 'mantenimiento-'.$mantenimiento->mantenimiento_Id.'.pdf';

        return $pdf->download($filename);
    }

    public function destroy(string $id)
    {
        $mant = Mantenimiento::with('fotos')->findOrFail($id);
        $serial = $mant->Hw_Serial;

        foreach ($mant->fotos as $foto) {
            $foto->delete();
        }

        $mant->delete();

        return redirect()
            ->route('hardware.show', $serial ?? 'hardware.index')
            ->with('ok', 'Mantenimiento eliminado.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'mantenimiento_fecha' => ['required', 'date'],
            'mantenimiento_Realiza' => ['nullable', 'string', 'max:60'],
            'mantenimiento_Recibe' => ['nullable', 'string', 'max:60'],
            'mantenimiento_Observaciones' => ['nullable', 'string', 'max:300'],
            'mantenimiento_RFE_O' => ['nullable', 'string', 'max:300'],
            'mantenimiento_ST_O' => ['nullable', 'string', 'max:300'],
            'mantenimiento_STYM_O' => ['nullable', 'string', 'max:300'],
            'mantenimiento_LP_O' => ['nullable', 'string', 'max:300'],
            'mantenimiento_LTYM_O' => ['nullable', 'string', 'max:300'],
            'mantenimiento_LM_O' => ['nullable', 'string', 'max:300'],
            'mantenimiento_O_O' => ['nullable', 'string', 'max:300'],
            'fotos' => ['nullable', 'array', 'max:8'],
            'fotos.*' => ['image', 'mimes:jpeg,jpg,png,webp,gif', 'max:5120'],
        ]);
    }

    private function storeFotos(Request $request, Mantenimiento $mantenimiento): void
    {
        if (! $request->hasFile('fotos')) {
            return;
        }

        $dir = 'mantenimientos/'.$mantenimiento->mantenimiento_Id;
        Storage::disk('public')->makeDirectory($dir);

        foreach ($request->file('fotos') as $file) {
            if (! $file->isValid()) {
                continue;
            }

            $safeName = uniqid().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
            $path = $file->storeAs($dir, $safeName, 'public');

            MantenimientoFoto::create([
                'mantenimiento_Id' => $mantenimiento->mantenimiento_Id,
                'archivo_path' => $path,
                'nombre_original' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType() ?: 'image/jpeg',
                'tamanio' => $file->getSize(),
                'created_at' => now(),
            ]);
        }
    }
}
