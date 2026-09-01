@extends('layouts.app')
@section('title', 'Reportes')
@section('page_title', 'Reportes')
@section('content')
<div class="grid gap-5 md:grid-cols-2">
    <a href="{{ route('reportes.hardware') }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-card transition hover:-translate-y-0.5 hover:border-brand-300"><i class="bi bi-pc-display text-2xl text-brand-600"></i><h2 class="mt-4 text-lg font-extrabold text-slate-800">Reporte de hardware</h2><p class="mt-1 text-sm text-slate-500">Consulta y exporta el inventario de equipos.</p></a>
    <a href="{{ route('reportes.software') }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-card transition hover:-translate-y-0.5 hover:border-brand-300"><i class="bi bi-key text-2xl text-emerald-600"></i><h2 class="mt-4 text-lg font-extrabold text-slate-800">Reporte de software licenciado</h2><p class="mt-1 text-sm text-slate-500">Consulta licencias y asignaciones actuales.</p></a>
    <a href="{{ route('reportes.auditoria') }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-card transition hover:-translate-y-0.5 hover:border-brand-300"><i class="bi bi-clipboard2-check text-2xl text-sky-600"></i><h2 class="mt-4 text-lg font-extrabold text-slate-800">Reporte de auditoría</h2><p class="mt-1 text-sm text-slate-500">Consulta las verificaciones realizadas por equipo.</p></a>
</div>
@endsection
