@extends('layouts.app')

@section('title', 'Mantenimiento #' . $mantenimiento->mantenimiento_Id)
@section('page_title', 'Mantenimiento #' . $mantenimiento->mantenimiento_Id)

@section('content')
<div class="mx-auto max-w-4xl">

    {{-- Cabecera --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-amber-500 via-orange-600 to-slate-900 px-6 py-6 shadow-card sm:px-8">
        <div class="pointer-events-none absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 22px 22px;"></div>
        <div class="relative flex flex-wrap items-center gap-4">
            <span class="grid size-12 place-items-center rounded-2xl bg-white/10 text-white ring-1 ring-white/20 backdrop-blur"><i class="bi bi-tools text-xl"></i></span>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-amber-200">Registro de mantenimiento</p>
                <h2 class="text-lg font-extrabold tracking-tight text-white">{{ optional($mantenimiento->mantenimiento_fecha)->format('d/m/Y') }}</h2>
                @if($mantenimiento->Hw_Serial)
                    <a href="{{ route('hardware.show', $mantenimiento->Hw_Serial) }}" class="mt-0.5 inline-flex items-center gap-1.5 font-mono text-xs text-slate-200 transition hover:text-white">
                        {{ $mantenimiento->Hw_Serial }} <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                @endif
            </div>
            @php($hechos = collect(['RFE','ST','STYM','LP','LTYM','LM','O'])->filter(fn ($k) => $mantenimiento->{"mantenimiento_{$k}_C"}))
            <div class="rounded-2xl bg-white/10 px-5 py-3 text-center ring-1 ring-white/20 backdrop-blur">
                <p class="text-2xl font-extrabold text-white">{{ $hechos->count() }}<span class="text-base font-semibold text-slate-300">/7</span></p>
                <p class="text-[11px] font-bold uppercase tracking-wide text-amber-200">Tareas</p>
            </div>
        </div>
        @if(auth()->user()->esAdmin())
            <div class="relative mt-5 flex justify-end">
                <form method="POST" action="{{ route('mant.destroy', $mantenimiento->mantenimiento_Id) }}"
                      data-confirm="El mantenimiento #{{ $mantenimiento->mantenimiento_Id }} se eliminará permanentemente.">
                    @csrf @method('DELETE')
                    <button class="inline-flex items-center gap-2 rounded-xl bg-red-500/15 px-4 py-2 text-sm font-semibold text-red-100 ring-1 ring-red-400/30 transition hover:bg-red-500/25 active:scale-[.98]"><i class="bi bi-trash3"></i> Eliminar</button>
                </form>
            </div>
        @endif
    </div>

    {{-- Responsables --}}
    <div class="mt-5 grid gap-4 sm:grid-cols-2">
        <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-card">
            <span class="grid size-11 place-items-center rounded-xl bg-brand-50 text-brand-600"><i class="bi bi-person-workspace"></i></span>
            <div><p class="text-xs text-slate-400">Realizado por</p><p class="text-sm font-bold text-slate-700">{{ $mantenimiento->mantenimiento_Realiza ?? '—' }}</p></div>
        </div>
        <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-card">
            <span class="grid size-11 place-items-center rounded-xl bg-emerald-50 text-emerald-600"><i class="bi bi-person-check"></i></span>
            <div><p class="text-xs text-slate-400">Recibido por</p><p class="text-sm font-bold text-slate-700">{{ $mantenimiento->mantenimiento_Recibe ?? '—' }}</p></div>
        </div>
    </div>

    {{-- Checklist resultado --}}
    <div class="mt-5 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card">
        <header class="border-b border-slate-100 px-6 py-4">
            <h3 class="flex items-center gap-2 text-sm font-bold text-slate-700"><i class="bi bi-list-check text-brand-500"></i> Resultado del checklist</h3>
        </header>
        <ul class="divide-y divide-slate-50 px-6">
            @foreach($checks as $key => $label)
                <li class="flex items-start gap-4 py-3.5">
                    <span class="mt-0.5 inline-grid size-7 shrink-0 place-items-center rounded-full {{ $mantenimiento->{"mantenimiento_{$key}_C"} ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-400' }}">
                        <i class="bi {{ $mantenimiento->{"mantenimiento_{$key}_C"} ? 'bi-check-lg' : 'bi-dash-lg' }} text-sm"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold {{ $mantenimiento->{"mantenimiento_{$key}_C"} ? 'text-slate-700' : 'text-slate-400' }}">{{ $label }}</p>
                        @if($mantenimiento->{"mantenimiento_{$key}_O"})
                            <p class="mt-0.5 text-xs text-slate-400">{{ $mantenimiento->{"mantenimiento_{$key}_O"} }}</p>
                        @endif
                    </div>
                </li>
            @endforeach
            <li class="py-4">
                <p class="mb-1 text-sm font-semibold text-slate-600"><i class="bi bi-journal-text mr-1 text-slate-400"></i>Observaciones generales</p>
                <p class="rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-500">{{ $mantenimiento->mantenimiento_Observaciones ?? 'Sin observaciones.' }}</p>
            </li>
        </ul>
    </div>

    <a href="{{ route('mant.index') }}" class="mt-5 mb-2 inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-brand-600"><i class="bi bi-arrow-left"></i>Volver a mantenimientos</a>
</div>
@endsection
