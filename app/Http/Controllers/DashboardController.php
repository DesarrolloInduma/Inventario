<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Hardware;
use App\Models\Hard_Soft;
use App\Models\Mantenimineto;
use App\Models\Software_Licenciado;
use App\Models\Software_NoLicenciado;
use App\Models\UsuarioInv;
use App\Support\SqlServer;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $tipoNombre = SqlServer::qi('Tipo.Nombre');
        $marcaNombre = SqlServer::qi('Marca.Nombre');
        $marcaExpr = "COALESCE({$marcaNombre}, 'Sin marca')";

        $equiposPorTipo = DB::table('Hardware')
            ->join('Tipo', 'Tipo.TipoID', '=', 'Hardware.TipoID')
            ->selectRaw("{$tipoNombre} as etiqueta, COUNT(*) as total")
            ->groupBy(DB::raw($tipoNombre))
            ->orderByDesc('total')
            ->get();

        $equiposPorMarca = DB::table('Hardware')
            ->join('Modelo', 'Modelo.ModeloID', '=', 'Hardware.ModeloID')
            ->leftJoin('Marca', 'Marca.MarcaID', '=', 'Modelo.MarcaID')
            ->selectRaw("{$marcaExpr} as etiqueta, COUNT(*) as total")
            ->groupBy(DB::raw($marcaExpr))
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $inicio = now()->subMonths(5)->startOfMonth();
        $mesExpression = SqlServer::yearMonth('mantenimiento_fecha');

        $mantenimientosPorMes = Mantenimineto::query()
            ->whereNotNull('mantenimiento_fecha')
            ->where('mantenimiento_fecha', '>=', $inicio->toDateString())
            ->selectRaw("{$mesExpression} as mes, COUNT(*) as total")
            ->groupBy(DB::raw($mesExpression))
            ->orderBy('mes')
            ->get()
            ->keyBy('mes');

        $meses = collect();
        for ($i = 5; $i >= 0; $i--) {
            $clave = now()->subMonths($i)->format('Y-m');
            $meses->push((object) [
                'mes' => $clave,
                'etiqueta' => ucfirst(now()->subMonths($i)->translatedFormat('M')),
                'total' => (int) ($mantenimientosPorMes[$clave]->total ?? 0),
            ]);
        }

        $licencias = Software_Licenciado::query()
            ->selectRaw('COALESCE(SUM('.SqlServer::qi('Software_Cantidad').'), 0) as compradas, COALESCE(SUM('.SqlServer::qi('Software_Cantidad').'), 0) - (SELECT COUNT(*) FROM '.SqlServer::qi('Hard_Soft').') as disponibles')
            ->first();

        return view('dashboard', [
            'totalHardware' => Hardware::count(),
            'totalSoftwareLic' => Software_Licenciado::count(),
            'totalSoftwareNl' => Software_NoLicenciado::count(),
            'totalMantenimientos' => Mantenimineto::count(),
            'totalAuditorias' => Auditoria::count(),
            'totalUsuariosInv' => UsuarioInv::count(),

            'garantiasVencidas' => Hardware::where('Hw_FechaGarantiaFin', '<', now()->toDateString())->count(),
            'equiposInactivos' => Hardware::where('Activo', false)->count(),

            'chartTipos' => [
                'labels' => $equiposPorTipo->pluck('etiqueta'),
                'data' => $equiposPorTipo->pluck('total'),
            ],
            'chartMarcas' => [
                'labels' => $equiposPorMarca->pluck('etiqueta'),
                'data' => $equiposPorMarca->pluck('total'),
            ],
            'chartMantenimientos' => [
                'labels' => $meses->pluck('etiqueta'),
                'data' => $meses->pluck('total'),
            ],
            'licenciasResumen' => [
                'asignadas' => Hard_Soft::count(),
                'compradas' => (int) ($licencias->compradas ?? 0),
            ],

            'recientes' => Hardware::with(['tipo', 'modelo.marca', 'usuarioInv'])
                ->orderByDesc('Hw_FechaCompra')
                ->take(6)
                ->get(),
        ]);
    }
}
