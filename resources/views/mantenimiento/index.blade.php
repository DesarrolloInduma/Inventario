@extends('layouts.app')

@section('title', 'Mantenimientos')
@section('page_title', 'Mantenimientos')

@section('content')
<div class="relative mb-5 overflow-hidden rounded-3xl bg-[#14212b] px-6 py-6 text-white shadow-[0_18px_45px_-25px_rgb(15_23_42/.5)] sm:px-8">
    <div class="induma-grid pointer-events-none absolute inset-0 opacity-50"></div>
    <div class="relative flex items-center gap-4"><span class="grid size-12 place-items-center rounded-2xl bg-[#b3072d] text-white shadow-lg shadow-red-950/30"><i class="bi bi-tools text-xl"></i></span><div><p class="text-xs font-bold uppercase tracking-[.24em] text-cyan-300">Continuidad operativa</p><h2 class="mt-1 font-display text-3xl font-bold uppercase tracking-wide">Mantenimientos</h2><p class="mt-1 text-sm text-slate-300">Seguimiento técnico de los activos tecnológicos de INDUMA.</p></div></div>
</div>
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
            <input type="text" name="q" value="{{ $q }}" placeholder="Buscar serial o responsable... ⏎"
                   class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-9 pr-4 text-sm shadow-card outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10">
        </form>
        <span class="ml-auto rounded-full bg-brand-50 px-3 py-1.5 text-xs font-bold text-brand-700">{{ $mantenimientos->total() }} registro(s)</span>
    </div>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-800 bg-[#14212b] text-[11px] uppercase tracking-wider text-white">
                        @foreach(['ID', 'Equipo', 'Fecha', 'Realiza', 'Recibe', 'Progreso'] as $h)
                            <th class="cursor-pointer select-none px-5 py-3.5 font-semibold transition hover:text-brand-600" data-sort onclick="tableSort(this)">
                                {{ $h }} <i class="bi bi-arrow-down-up ml-0.5 text-[9px] opacity-40"></i>
                            </th>
                        @endforeach
                        <th class="px-5 py-3.5 text-right font-semibold">Acciones</th>
                    </tr>
                </thead>
                <tbody x-ref="rows" class="divide-y divide-slate-50">
                    @forelse($mantenimientos as $m)
                        @php($hechos = collect(['RFE','ST','STYM','LP','LTYM','LM','O'])->filter(fn ($k) => $m->{"mantenimiento_{$k}_C"}))
                        @php($pct = round($hechos->count() / 7 * 100))
                        <tr data-row class="group transition hover:bg-brand-50/40">
                            <td class="px-5 py-3.5 font-mono text-xs font-bold text-slate-400">#{{ $m->mantenimiento_Id }}</td>
                            <td class="px-5 py-3.5"><span class="rounded-lg bg-slate-100 px-2 py-1 font-mono text-xs font-semibold text-slate-600">{{ $m->Hw_Serial ?? '—' }}</span></td>
                            <td class="px-5 py-3.5 font-semibold text-slate-600">{{ optional($m->mantenimiento_fecha)->format('d/m/Y') }}</td>
                            <td class="px-5 py-3.5 text-slate-500">{{ $m->mantenimiento_Realiza ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-slate-500">{{ $m->mantenimiento_Recibe ?? '—' }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-16 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full {{ $pct === 100 ? 'bg-emerald-500' : 'bg-brand-500' }}" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold text-slate-500">{{ $pct }}%</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <a href="{{ route('mant.show', $m->mantenimiento_Id) }}" title="Ver detalle"
                                   class="inline-grid size-8 place-items-center rounded-lg text-slate-400 opacity-60 transition hover:bg-brand-50 hover:text-brand-600 group-hover:opacity-100"><i class="bi bi-chevron-right"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-empty-state icono="bi-tools" texto="{{ $q !== '' ? 'Sin resultados.' : 'Los mantenimientos se registran desde el detalle de cada equipo.' }}" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-3.5 text-sm text-slate-500">{{ $mantenimientos->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script>function tableSort(th){const t=th.closest('table'),i=Array.from(th.parentElement.children).indexOf(th),a=th.dataset.dir!=='asc';t.querySelectorAll('th[data-sort]').forEach(h=>delete h.dataset.dir);th.dataset.dir=a?'asc':'desc';const b=t.querySelector('tbody');[...b.querySelectorAll('tr[data-row]')].sort((x,y)=>{const av=x.children[i].innerText.trim().toLowerCase(),bv=y.children[i].innerText.trim().toLowerCase(),an=parseFloat(av),bn=parseFloat(bv);return((!isNaN(an)&&!isNaN(bn)&&String(an)===av&&String(bn)===bv)?an-bn:av.localeCompare(bv,'es'))*(a?1:-1)}).forEach(r=>b.appendChild(r));b.querySelectorAll('tr:not([data-row])').forEach(r=>b.appendChild(r))}</script>
@endpush

