@extends('layouts.app')

@section('title', 'Software libre')
@section('page_title', 'Software no licenciado')

@section('content')
<div x-data="{ q: '' }"
     x-effect="$refs.rows && $refs.rows.querySelectorAll('tbody tr[data-row]').forEach(t => t.hidden = !!q && !t.textContent.toLowerCase().includes(q.toLowerCase()))">

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 sm:max-w-xs">
            <i class="bi bi-funnel pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
            <input type="text" x-model.debounce.250ms="q" placeholder="Filtrar en esta página..."
                   class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-9 pr-4 text-sm shadow-card outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10">
        </div>
        <form method="GET" class="relative flex-1 sm:max-w-xs">
            <i class="bi bi-search pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
            <input type="text" name="q" value="{{ $q }}" placeholder="Buscar en toda la BD... ⏎"
                   class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-9 pr-4 text-sm shadow-card outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10">
        </form>
        @if(auth()->user()->esAdmin())
            <a href="{{ route('softnl.create') }}" class="ml-auto inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-600/30 transition hover:bg-brand-700 active:scale-[.98]"><i class="bi bi-plus-lg"></i> Nuevo software</a>
        @endif
    </div>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] uppercase tracking-wider text-slate-400">
                        @foreach(['Nombre', 'Versión', 'Tipo', 'Equipos'] as $h)
                            <th class="cursor-pointer select-none px-5 py-3.5 font-semibold transition hover:text-brand-600" data-sort onclick="tableSort(this)">
                                {{ $h }} <i class="bi bi-arrow-down-up ml-0.5 text-[9px] opacity-40"></i>
                            </th>
                        @endforeach
                        <th class="px-5 py-3.5 text-right font-semibold">Acciones</th>
                    </tr>
                </thead>
                <tbody x-ref="rows" class="divide-y divide-slate-50">
                    @forelse($software as $sw)
                        <tr data-row class="group transition hover:bg-brand-50/40">
                            <td class="px-5 py-3.5 font-semibold text-slate-700">{{ $sw->Softwarenl_Nombre }}</td>
                            <td class="px-5 py-3.5 text-slate-500">{{ $sw->Softwarenl_Version ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-slate-500">{{ $sw->tipoSoftware->Soft_Tipo_Nombre ?? '—' }}</td>
                            <td class="px-5 py-3.5"><span class="inline-flex h-6 w-12 items-center justify-center rounded-lg bg-sky-50 text-xs font-bold text-sky-700 ring-1 ring-sky-200">{{ $sw->hardwares->count() }}</span></td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1 opacity-60 transition group-hover:opacity-100">
                                    <a href="{{ route('softnl.show', $sw->Softwarenl_Id) }}" title="Ver" class="inline-grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-brand-50 hover:text-brand-600"><i class="bi bi-eye"></i></a>
                                    @if(auth()->user()->esAdmin())
                                        <a href="{{ route('softnl.edit', $sw->Softwarenl_Id) }}" title="Editar" class="inline-grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-amber-50 hover:text-amber-600"><i class="bi bi-pencil"></i></a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty-state icono="bi-app-indicator" texto="{{ $q !== '' ? 'Sin resultados.' : 'Aún no hay software registrado.' }}" accion="softnl.create" boton="Nuevo software" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-3.5 text-sm text-slate-500">{{ $software->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script>function tableSort(th){const t=th.closest('table'),i=Array.from(th.parentElement.children).indexOf(th),a=th.dataset.dir!=='asc';t.querySelectorAll('th[data-sort]').forEach(h=>delete h.dataset.dir);th.dataset.dir=a?'asc':'desc';const b=t.querySelector('tbody');[...b.querySelectorAll('tr[data-row]')].sort((x,y)=>{const av=x.children[i].innerText.trim().toLowerCase(),bv=y.children[i].innerText.trim().toLowerCase(),an=parseFloat(av),bn=parseFloat(bv);return((!isNaN(an)&&!isNaN(bn)&&String(an)===av&&String(bn)===bv)?an-bn:av.localeCompare(bv,'es'))*(a?1:-1)}).forEach(r=>b.appendChild(r));b.querySelectorAll('tr:not([data-row])').forEach(r=>b.appendChild(r))}</script>
@endpush
