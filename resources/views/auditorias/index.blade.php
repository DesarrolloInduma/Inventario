@extends('layouts.app')

@section('title', 'Auditorías')
@section('page_title', 'Auditorías')

@section('content')
<div class="mb-4 flex flex-wrap items-center gap-3">
    <form method="GET" class="relative flex-1 sm:max-w-md">
        <i class="bi bi-search pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
        <input name="q" value="{{ $q }}" placeholder="Buscar serial, usuario u observación..." class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-9 pr-4 text-sm shadow-card">
    </form>
    <span class="rounded-full bg-brand-50 px-3 py-1.5 text-xs font-bold text-brand-700">{{ $auditorias->total() }} auditoría(s)</span>
</div>
<div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[800px] text-left text-sm">
            <thead class="border-b border-slate-100 bg-slate-50/70 text-[11px] uppercase tracking-wider text-slate-400">
                <tr><th class="px-5 py-3.5">Fecha</th><th class="px-5 py-3.5">Equipo</th><th class="px-5 py-3.5">Usuario</th><th class="px-5 py-3.5">Área</th><th class="px-5 py-3.5">Observaciones</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($auditorias as $auditoria)
                    <tr class="hover:bg-brand-50/40"><td class="px-5 py-3.5 font-semibold text-slate-600">{{ optional($auditoria->FechaAuditoria)->format('d/m/Y H:i') }}</td><td class="px-5 py-3.5"><a class="font-mono text-xs font-semibold text-brand-600" href="{{ route('hardware.show', $auditoria->Hw_Serial) }}">{{ $auditoria->Hw_Serial }}</a><span class="block text-slate-500">{{ $auditoria->Hw_Nombre }}</span></td><td class="px-5 py-3.5 text-slate-600">{{ $auditoria->UsuarioInvNombre }}</td><td class="px-5 py-3.5 text-slate-500">{{ $auditoria->UsuarioInvArea }}</td><td class="max-w-xs px-5 py-3.5 text-slate-500">{{ $auditoria->Observaciones }}</td></tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-slate-400">No hay auditorías registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-6 py-3.5">{{ $auditorias->links() }}</div>
</div>
@endsection
