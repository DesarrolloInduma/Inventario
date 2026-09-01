<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Hardware;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));

        $auditorias = Auditoria::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('Hw_Serial', 'like', "%{$q}%")
                        ->orWhere('Hw_Nombre', 'like', "%{$q}%")
                        ->orWhere('UsuarioInvNombre', 'like', "%{$q}%")
                        ->orWhere('Observaciones', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('FechaAuditoria')
            ->paginate(20)
            ->withQueryString();

        return view('auditorias.index', compact('auditorias', 'q'));
    }

    public function create(string $serial)
    {
        return view('auditorias.create', [
            'hardware' => Hardware::with(['tipo', 'monitor', 'usuarioInv'])->findOrFail($serial),
        ]);
    }

    public function store(Request $request, string $serial)
    {
        $hardware = Hardware::with(['tipo', 'monitor', 'usuarioInv'])->findOrFail($serial);
        $data = $request->validate([
            'FechaAuditoria' => ['required', 'date'],
            'Observaciones' => ['required', 'string', 'max:300'],
            'SistemaOperativo' => ['required', 'string', 'max:50'],
            'ClaveSO' => ['nullable', 'string', 'max:50'],
            'Office' => ['required', 'string', 'max:50'],
            'ClaveOffice' => ['nullable', 'string', 'max:50'],
        ]);

        Auditoria::create(array_merge($data, [
            'UsuarioInvArea' => $hardware->usuarioInv->UsuarioInvArea ?? 'Sin área',
            'Hw_Nombre' => $hardware->Hw_Nombre,
            'UsuarioInvNombre' => $hardware->usuarioInv->UsuarioInvNombre ?? 'Sin asignar',
            'Hw_Serial' => $hardware->Hw_Serial,
            'TipoHardware' => $hardware->tipo->Nombre ?? 'Sin tipo',
            'MonitorID' => $hardware->MonitorID ?? 'Sin monitor',
            'Monitor_Modelo' => $hardware->monitor->Monitor_Modelo ?? 'Sin modelo',
        ]));

        return redirect()->route('hardware.show', $serial)->with('ok', 'Auditoría registrada.');
    }
}
