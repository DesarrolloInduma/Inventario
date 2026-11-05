<?php

namespace App\Http\Controllers;

use App\Models\Hardware;
use App\Models\Mantenimiento;
use Illuminate\Http\Request;

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
        $data['Hw_Serial'] = $serial;

        foreach (array_keys(self::CHECKS) as $k) {
            $data["mantenimiento_{$k}_C"] = $request->boolean("mantenimiento_{$k}_C");
        }

        Mantenimiento::create($data);

        return redirect()
            ->route('hardware.show', $serial)
            ->with('ok', 'Mantenimiento registrado.');
    }

    public function show(string $id)
    {
        return view('mantenimiento.show', [
            'mantenimiento' => Mantenimiento::with('hardware')->findOrFail($id),
            'checks' => self::CHECKS,
        ]);
    }

    public function destroy(string $id)
    {
        $mant = Mantenimiento::findOrFail($id);
        $serial = $mant->Hw_Serial;
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
        ]);
    }
}
