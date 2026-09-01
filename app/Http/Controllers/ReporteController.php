<?php

namespace App\Http\Controllers;

use App\Models\Hardware;
use App\Models\Auditoria;
use App\Models\Software_Licenciado;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    public function index()
    {
        return view('reportes.index');
    }

    public function hardware(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $hardware = Hardware::with(['tipo', 'modelo.marca', 'usuarioInv', 'ubicacion'])
            ->when($q !== '', fn ($query) => $query->where('Hw_Serial', 'like', "%{$q}%"))
            ->orderBy('Hw_Serial')->get();

        return view('reportes.hardware', compact('hardware', 'q'));
    }

    public function software()
    {
        $software = Software_Licenciado::withCount('hardwares')->orderBy('Software_Nombre')->get();

        return view('reportes.software', compact('software'));
    }

    public function auditoria(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $auditorias = Auditoria::query()
            ->when($q !== '', fn ($query) => $query->where('Hw_Serial', 'like', "%{$q}%"))
            ->orderByDesc('FechaAuditoria')->get();

        return view('reportes.auditoria', compact('auditorias', 'q'));
    }

    public function hardwareCsv(Request $request): StreamedResponse
    {
        $q = trim((string) $request->query('q'));
        $hardware = Hardware::with(['tipo', 'modelo.marca', 'usuarioInv'])
            ->when($q !== '', fn ($query) => $query->where('Hw_Serial', 'like', "%{$q}%"))
            ->orderBy('Hw_Serial')->get();

        return response()->streamDownload(function () use ($hardware) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Serial', 'Equipo', 'Tipo', 'Marca', 'Modelo', 'Usuario', 'Activo']);
            foreach ($hardware as $equipo) {
                fputcsv($handle, [$equipo->Hw_Serial, $equipo->Hw_Nombre, $equipo->tipo->Nombre ?? '', $equipo->modelo->marca->Nombre ?? '', $equipo->modelo->Nombre ?? '', $equipo->usuarioInv->UsuarioInvNombre ?? '', $equipo->Activo ? 'Si' : 'No']);
            }
            fclose($handle);
        }, 'reporte-hardware.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
