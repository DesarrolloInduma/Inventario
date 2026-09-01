@extends('layouts.app')

@section('title', 'Hardware')

@section('page_title', 'Hardware')

@section('content')
<div x-data="{ q: '' }" x-effect="$refs.rows && $refs.rows.querySelectorAll('tr[data-row]').forEach(row => row.hidden = !!q && !row.textContent.toLowerCase().includes(q.toLowerCase()))">
    <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="flex items-center gap-3"><span class="grid size-11 place-items-center rounded-2xl bg-brand-600 text-white shadow-lg shadow-brand-600/25"><i class="bi bi-pc-display text-xl"></i></span><div><h2 class="text-2xl font-extrabold tracking-tight text-slate-900">Hardware</h2><p class="text-sm text-slate-500">Consulta y administra todos los equipos registrados.</p></div></div>
        </div>
        <a href="{{ route('hardware.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm shadow-brand-600/25 transition hover:bg-brand-700"><i class="bi bi-plus-lg"></i>Nuevo hardware</a>
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-card">
        <label class="relative min-w-[240px] flex-1 sm:max-w-md"><i class="bi bi-search pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i><input type="search" x-model.debounce.150ms="q" placeholder="Buscar serial, equipo, usuario..." class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-4 text-sm outline-none transition focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-500/10"></label>
        <form method="GET" class="relative min-w-[240px] flex-1 sm:max-w-md"><i class="bi bi-database-search pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i><input type="search" name="q" value="{{ $q }}" placeholder="Buscar serial, nombre, área o correo..." class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-4 text-sm outline-none transition focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-500/10"></form>
        <span class="ml-auto rounded-full bg-brand-50 px-3 py-1.5 text-xs font-bold text-brand-700">{{ $hardware->total() }} equipo(s)</span>
    </div>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card">
        <div class="max-h-[min(62vh,620px)] overflow-auto">
            <table class="w-full min-w-[1120px] text-left text-sm">
                <thead class="sticky top-0 z-10 bg-slate-900 text-[11px] uppercase tracking-wider text-white">
                    <tr><th class="px-4 py-3.5">Acciones</th><th class="px-4 py-3.5">Serial</th><th class="px-4 py-3.5">Nombre equipo</th><th class="px-4 py-3.5">Tipo</th><th class="px-4 py-3.5">Usuario</th><th class="px-4 py-3.5">Cargo</th><th class="px-4 py-3.5">Área</th><th class="px-4 py-3.5">Correo</th><th class="px-4 py-3.5">Ubicación</th><th class="px-4 py-3.5">Vencimiento garantía</th></tr>
                </thead>
                <tbody x-ref="rows" class="divide-y divide-slate-100">
                    @forelse($hardware as $eq)
                        <tr data-row class="group transition hover:bg-brand-50/40">
                            <td class="px-4 py-3.5"><div class="flex items-center gap-1 text-brand-600"><a href="{{ route('hardware.show', $eq->Hw_Serial) }}" title="Ver detalle" class="grid size-8 place-items-center rounded-lg transition hover:bg-brand-100"><i class="bi bi-eye-fill"></i></a><a href="{{ route('hardware.edit', $eq->Hw_Serial) }}" title="Editar" class="grid size-8 place-items-center rounded-lg transition hover:bg-amber-100 hover:text-amber-700"><i class="bi bi-pencil-fill"></i></a><a href="{{ route('mant.create', $eq->Hw_Serial) }}" title="Registrar mantenimiento" class="grid size-8 place-items-center rounded-lg transition hover:bg-sky-100 hover:text-sky-700"><i class="bi bi-tools"></i></a></div></td>
                            <td class="px-4 py-3.5"><span class="font-mono text-xs font-bold text-slate-700">{{ $eq->Hw_Serial }}</span></td>
                            <td class="px-4 py-3.5"><p class="font-semibold text-slate-700">{{ $eq->Hw_Nombre }}</p>@if(!$eq->Activo)<span class="text-xs font-semibold text-red-600">Inactivo</span>@endif</td>
                            <td class="px-4 py-3.5 text-slate-600">{{ $eq->tipo->Nombre ?? '—' }}</td>
                            <td class="max-w-[180px] px-4 py-3.5 text-slate-600">{{ $eq->usuarioInv->UsuarioInvNombre ?? 'Sin asignar' }}</td>
                            <td class="px-4 py-3.5 text-slate-600">{{ $eq->usuarioInv->UsuarioInvCargo ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-slate-600">{{ $eq->usuarioInv->UsuarioInvArea ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-slate-600">{{ $eq->usuarioInv->UsuarioInvCorreo ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-slate-600">{{ $eq->ubicacion->UbicacionNombre ?? '—' }}</td>
                            <td class="px-4 py-3.5"><x-vencimiento :fecha="$eq->Hw_FechaGarantiaFin" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="10"><x-empty-state icono="bi-pc-display" texto="{{ $q !== '' ? 'Sin resultados para «' . $q . '».' : 'Aún no hay hardware registrado.' }}" accion="hardware.create" boton="Registrar hardware" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-5 py-3.5 text-sm text-slate-500"><div>{{ $hardware->links() }}</div><div class="flex flex-wrap items-center gap-3 text-xs"><span class="inline-flex items-center gap-1.5"><i class="size-3 rounded-sm bg-slate-200"></i>Garantía vigente</span><span class="inline-flex items-center gap-1.5"><i class="size-3 rounded-sm bg-lime-300"></i>Garantía &lt; 6 meses</span><span class="inline-flex items-center gap-1.5"><i class="size-3 rounded-sm bg-orange-400"></i>Garantía &lt; 3 meses</span><span class="inline-flex items-center gap-1.5"><i class="size-3 rounded-sm bg-red-500"></i>Garantía vencida</span></div></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function tableSort(th) {
        const table = th.closest('table');
        const idx = Array.from(th.parentElement.children).indexOf(th);
        const asc = th.dataset.dir !== 'asc';
        table.querySelectorAll('th[data-sort]').forEach(h => delete h.dataset.dir);
        th.dataset.dir = asc ? 'asc' : 'desc';
        const tbody = table.querySelector('tbody');
        const filas = [...tbody.querySelectorAll('tr[data-row]')];
        filas.sort((a, b) => {
            const av = a.children[idx].innerText.trim().toLowerCase();
            const bv = b.children[idx].innerText.trim().toLowerCase();
            const an = parseFloat(av), bn = parseFloat(bv);
            const cmp = (!isNaN(an) && !isNaN(bn) && String(an) === av && String(bn) === bv) ? an - bn : av.localeCompare(bv, 'es');
            return cmp * (asc ? 1 : -1);
        });
        filas.forEach(tr => tbody.appendChild(tr));
        tbody.querySelectorAll('tr:not([data-row])').forEach(tr => tbody.appendChild(tr));
    }
</script>
@endpush

