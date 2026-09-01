@extends('layouts.app')

@section('title', 'Inicio')

@section('content')

<div class="relative mb-6 overflow-hidden rounded-3xl bg-[#14212b] px-6 py-7 text-white shadow-[0_18px_45px_-25px_rgb(15_23_42/.5)] sm:px-8">
    <div class="induma-grid pointer-events-none absolute inset-0 opacity-50"></div>
    <div class="relative flex flex-wrap items-end justify-between gap-5">
        <div><p class="font-display text-xs font-bold uppercase tracking-[.28em] text-cyan-300">Centro de operaciones · INDUMA S.A.S.</p><h2 class="mt-2 font-display text-4xl font-bold uppercase tracking-wide sm:text-5xl">Panel de control</h2><p class="mt-2 max-w-xl text-sm text-slate-300">Una vista clara del estado de tus activos tecnológicos.</p></div>
        <div class="flex items-center gap-2 rounded-xl border border-white/10 bg-white/[.06] px-3 py-2 text-xs font-semibold text-slate-200"><span class="size-2 rounded-full bg-emerald-400 shadow-[0_0_12px_rgb(52_211_153)]"></span> Sistema operativo</div>
    </div>
</div>

{{-- KPIs --}}
<div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
    @php
        $kpis = [
            ['Hardware', $totalHardware, 'bi-pc-display', 'from-[#b3072d] to-[#7f0624]', 'hardware.index'],
            ['SW licenciado', $totalSoftwareLic, 'bi-key', 'from-cyan-500 to-cyan-700', 'softlic.index'],
            ['SW libre', $totalSoftwareNl, 'bi-app-indicator', 'from-slate-600 to-slate-800', 'softnl.index'],
            ['Mantenimientos', $totalMantenimientos, 'bi-tools', 'from-orange-500 to-orange-700', 'mant.index'],
            ['Auditorías', $totalAuditorias, 'bi-clipboard-check', 'from-emerald-500 to-emerald-700', null],
            ['Usuarios inv.', $totalUsuariosInv, 'bi-people', 'from-slate-500 to-slate-700', null],
        ];
    @endphp
    @foreach($kpis as [$label, $valor, $icono, $gradiente, $ruta])
        @php($tag = $ruta ? 'a' : 'div')
        <{{ $tag }} @if($ruta) href="{{ route($ruta) }}" data-loading="{{ $label }}" @endif
             class="group block rounded-3xl border border-slate-200 bg-white p-5 shadow-card transition hover:-translate-y-0.5 hover:shadow-pop">
            <div class="mb-3 grid size-11 place-items-center rounded-2xl bg-gradient-to-br {{ $gradiente }} text-white shadow-md transition group-hover:scale-105">
                <i class="bi {{ $icono }} text-lg"></i>
            </div>
            <p class="text-2xl font-extrabold tracking-tight text-slate-800" data-count="{{ $valor }}">{{ number_format($valor) }}</p>
            <p class="mt-0.5 text-xs font-medium text-slate-400">{{ $label }}</p>
        </{{ $tag }}>
    @endforeach
</div>

{{-- Alertas rápidas --}}
@if($garantiasVencidas > 0 || $equiposInactivos > 0)
    <div class="mt-4 grid gap-3 sm:grid-cols-2">
        @if($garantiasVencidas > 0)
            <a href="{{ route('hardware.index') }}" class="flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800 transition hover:border-amber-300 hover:bg-amber-100">
                <i class="bi bi-shield-exclamation text-lg"></i>
                <strong>{{ $garantiasVencidas }}</strong> equipo(s) con garantía vencida
                <i class="bi bi-chevron-right ml-auto text-xs"></i>
            </a>
        @endif
        @if($equiposInactivos > 0)
            <a href="{{ route('hardware.index') }}" class="flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800 transition hover:border-red-300 hover:bg-red-100">
                <i class="bi bi-x-octagon text-lg"></i>
                <strong>{{ $equiposInactivos }}</strong> equipo(s) inactivos
                <i class="bi bi-chevron-right ml-auto text-xs"></i>
            </a>
        @endif
    </div>
@endif

{{-- Gráficos --}}
<div class="mt-6 grid gap-5 lg:grid-cols-3">
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-card">
        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-700"><i class="bi bi-pie-chart text-brand-500"></i> Equipos por tipo</h3>
        @if($chartTipos['data']->sum() === 0)
            <x-empty-state small icono="bi-pie-chart" texto="Aún no hay equipos registrados." />
        @else
            <div class="relative mt-4 h-56" data-chart>
                <div class="chart-skeleton items-center justify-center">
                    <span class="skeleton !rounded-full" style="flex:none;width:8.5rem;height:8.5rem"></span>
                </div>
                <canvas id="chartTipos"></canvas>
            </div>
        @endif
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-card">
        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-700"><i class="bi bi-bar-chart text-brand-500"></i> Top marcas</h3>
        @if($chartMarcas['data']->sum() === 0)
            <x-empty-state small icono="bi-bar-chart" texto="Aún no hay equipos registrados." />
        @else
            <div class="relative mt-4 h-56" data-chart>
                <div class="chart-skeleton">
                    @foreach([55, 80, 40, 95, 65, 30] as $alto)
                        <span class="skeleton" style="height: {{ $alto }}%"></span>
                    @endforeach
                </div>
                <canvas id="chartMarcas"></canvas>
            </div>
        @endif
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-card">
        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-700"><i class="bi bi-graph-up-arrow text-brand-500"></i> Mantenimientos (6 meses)</h3>
        <div class="relative mt-4 h-56" data-chart>
            <div class="chart-skeleton">
                @foreach([35, 60, 45, 75, 50, 85] as $alto)
                    <span class="skeleton" style="height: {{ $alto }}%"></span>
                @endforeach
            </div>
            <canvas id="chartMant"></canvas>
        </div>
    </div>
</div>

{{-- Licencias + recientes --}}
<div class="mt-6 grid gap-5 lg:grid-cols-3">
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-card">
        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-700"><i class="bi bi-key-fill text-brand-500"></i> Licencias</h3>
        <p class="mt-4 flex items-baseline gap-2">
            <span class="text-4xl font-extrabold tracking-tight text-slate-800">{{ number_format($licenciasResumen['asignadas']) }}</span>
            <span class="text-sm text-slate-400">asignadas de {{ number_format(max($licenciasResumen['compradas'], $licenciasResumen['asignadas'])) }}</span>
        </p>
        @php($pct = $licenciasResumen['compradas'] > 0 ? min(100, round($licenciasResumen['asignadas'] / $licenciasResumen['compradas'] * 100)) : ($licenciasResumen['asignadas'] > 0 ? 100 : 0))
        <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-slate-100">
            <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-sky-500 transition-all duration-700" style="width: {{ $pct }}%"></div>
        </div>
        <p class="mt-2 text-xs text-slate-400">{{ $pct }}% de la capacidad comprada en uso</p>
        <a href="{{ route('softlic.index') }}" class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-600 transition hover:text-brand-700">
            Gestionar licencias <i class="bi bi-arrow-right text-xs"></i>
        </a>
    </div>

    <div class="lg:col-span-2 rounded-3xl border border-slate-200 bg-white shadow-card">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="flex items-center gap-2 text-sm font-bold text-slate-700"><i class="bi bi-clock-history text-brand-500"></i> Último hardware registrado</h3>
            <a href="{{ route('hardware.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Ver todos →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] uppercase tracking-wider text-slate-400">
                        <th class="px-6 py-3 font-semibold">Serial</th>
                        <th class="px-6 py-3 font-semibold">Equipo</th>
                        <th class="px-6 py-3 font-semibold hidden sm:table-cell">Tipo</th>
                        <th class="px-6 py-3 font-semibold hidden md:table-cell">Asignado a</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recientes as $eq)
                        <tr class="border-b border-slate-50 transition last:border-0 hover:bg-slate-50/70">
                            <td class="px-6 py-3.5"><span class="rounded-lg bg-slate-100 px-2 py-1 font-mono text-xs font-semibold text-slate-600">{{ $eq->Hw_Serial }}</span></td>
                            <td class="px-6 py-3.5 font-medium text-slate-700">{{ $eq->Hw_Nombre }}</td>
                            <td class="hidden px-6 py-3.5 text-slate-500 sm:table-cell">{{ $eq->tipo->Nombre ?? '—' }}</td>
                            <td class="hidden px-6 py-3.5 text-slate-500 md:table-cell">{{ $eq->usuarioInv->UsuarioInvNombre ?? '—' }}</td>
                            <td class="px-6 py-3.5 text-right">
                                <a href="{{ route('hardware.show', $eq->Hw_Serial) }}" class="inline-grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-brand-50 hover:text-brand-600"><i class="bi bi-chevron-right"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty-state icono="bi-pc-display" texto="Aún no hay hardware. Registra el primero con «Nuevo hardware»." accion="hardware.create" boton="Registrar hardware" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@unless(empty($chartTipos['data']) || $chartTipos['data']->sum() === 0)
<script>
    document.addEventListener('DOMContentLoaded', () => {
        makeChart(document.getElementById('chartTipos'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($chartTipos['labels']->values()) !!},
                datasets: [{ data: {!! json_encode($chartTipos['data']->values()) !!}, backgroundColor: PALETTE, borderWidth: 2, borderColor: '#fff' }],
            },
            options: { maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'right' } } },
        });

        makeChart(document.getElementById('chartMarcas'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartMarcas['labels']->values()) !!},
                datasets: [{ data: {!! json_encode($chartMarcas['data']->values()) !!}, backgroundColor: '#b3072d', borderRadius: 8, maxBarThickness: 26 }],
            },
            options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { ticks: { precision: 0 }, grid: { color: 'rgba(148,163,184,.12)' } }, x: { grid: { display: false } } } },
        });
    });
</script>
@endunless
<script>
    document.addEventListener('DOMContentLoaded', () => {
        makeChart(document.getElementById('chartMant'), {
            type: 'line',
            data: {
                labels: {!! json_encode($chartMantenimientos['labels']) !!},
                datasets: [{
                    data: {!! json_encode($chartMantenimientos['data']) !!},
                    borderColor: '#0891b2',
                    backgroundColor: 'rgba(8,145,178,.08)',
                    fill: true, tension: .4,
                    pointBackgroundColor: '#0891b2', pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: 4,
                }],
            },
            options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(148,163,184,.12)' } }, x: { grid: { display: false } } } },
        });
    });
</script>
@endpush
