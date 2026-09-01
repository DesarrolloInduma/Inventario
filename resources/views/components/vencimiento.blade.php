@props([
    'fecha' => null,
    'dias' => false,      // añade "· en 45 días" junto a la fecha
    'sinFecha' => 'Sin fecha',
])

@php
    $fecha = $fecha ? \Illuminate\Support\Carbon::parse($fecha)->startOfDay() : null;
    $restantes = $fecha ? (int) round(now()->startOfDay()->diffInDays($fecha, false)) : null;

    // Mismos cortes y colores que la leyenda: vencida (rojo), < 3 meses (naranja), < 6 meses (lima).
    $estado = match (true) {
        $restantes === null => 'sin-fecha',
        $restantes <= 0 => 'vencida',
        $restantes <= 90 => 'critica',
        $restantes <= 180 => 'proxima',
        default => 'vigente',
    };

    [$clase, $icono, $titulo] = match ($estado) {
        'vencida' => ['bg-red-500 text-white ring-1 ring-red-600/40', 'bi-exclamation-triangle-fill', $restantes === 0 ? 'Vence hoy' : 'Vencida hace ' . abs($restantes) . ' día(s)'],
        'critica' => ['bg-orange-400 text-orange-950 ring-1 ring-orange-500/40', 'bi-exclamation-circle-fill', 'Vence en ' . $restantes . ' día(s) · menos de 3 meses'],
        'proxima' => ['bg-lime-300 text-lime-900 ring-1 ring-lime-400/50', 'bi-clock-fill', 'Vence en ' . $restantes . ' día(s) · menos de 6 meses'],
        'vigente' => ['bg-slate-100 text-slate-600 ring-1 ring-slate-200', 'bi-shield-check', 'Vigente · faltan ' . $restantes . ' día(s)'],
        default => ['bg-slate-50 text-slate-400 ring-1 ring-slate-200', 'bi-dash-circle', 'Sin fecha de vencimiento registrada'],
    };

    $relativo = match (true) {
        $estado === 'sin-fecha' => null,
        $restantes === 0 => 'hoy',
        $estado === 'vencida' => 'hace ' . abs($restantes) . ' d',
        default => 'en ' . $restantes . ' d',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-bold ' . $clase]) }}
      title="{{ $titulo }}">
    <i class="bi {{ $icono }} text-[11px]"></i>
    {{ $fecha?->format('d/m/Y') ?? $sinFecha }}
    @if($dias && $relativo)
        <span class="font-semibold opacity-70">· {{ $relativo }}</span>
    @endif
</span>
