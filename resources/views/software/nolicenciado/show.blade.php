@extends('layouts.app')

@section('title', $software->Softwarenl_Nombre)
@section('page_title', 'Software: ' . $software->Softwarenl_Nombre)

@section('content')
<div class="mx-auto max-w-5xl">

    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-sky-600 via-cyan-700 to-slate-900 px-6 py-7 shadow-card sm:px-8">
        <div class="pointer-events-none absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 22px 22px;"></div>
        <div class="relative flex flex-wrap items-center gap-4">
            <span class="grid size-14 place-items-center rounded-2xl bg-white/10 text-white ring-1 ring-white/20 backdrop-blur">
                <i class="bi bi-app-indicator text-2xl"></i>
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-sky-200">{{ $software->tipoSoftware->Soft_Tipo_Nombre ?? 'Sin tipo' }}</p>
                <h2 class="truncate text-xl font-extrabold tracking-tight text-white">{{ $software->Softwarenl_Nombre }}</h2>
                <p class="text-sm text-slate-300">Versión {{ $software->Softwarenl_Version ?? '?' }}</p>
            </div>
            <div class="rounded-2xl bg-white/10 px-5 py-3 text-center ring-1 ring-white/20 backdrop-blur">
                <p class="text-2xl font-extrabold text-white">{{ $software->hardwares->count() }}</p>
                <p class="text-[11px] font-bold uppercase tracking-wide text-sky-200">Equipos</p>
            </div>
        </div>
        @if(auth()->user()->esAdmin())
            <div class="relative mt-6 flex flex-wrap gap-2">
                <a href="{{ route('softnl.edit', $software->Softwarenl_Id) }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-brand-700 active:scale-[.98]"><i class="bi bi-pencil"></i> Editar</a>
                <form method="POST" action="{{ route('softnl.destroy', $software->Softwarenl_Id) }}" data-confirm="El software y sus asignaciones se eliminarán." class="ml-auto">
                    @csrf @method('DELETE')
                    <button class="inline-flex items-center gap-2 rounded-xl bg-red-500/15 px-4 py-2 text-sm font-semibold text-red-100 ring-1 ring-red-400/30 transition hover:bg-red-500/25 active:scale-[.98]"><i class="bi bi-trash3"></i> Eliminar</button>
                </form>
            </div>
        @endif
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-3">
        <div class="lg:col-span-2 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card">
            <header class="border-b border-slate-100 px-6 py-4">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-700"><i class="bi bi-pc-display text-brand-500"></i> Equipos con este software ({{ $software->hardwares->count() }})</h3>
            </header>
            <ul class="divide-y divide-slate-50 px-6">
                @forelse($software->hardwares as $eq)
                    <li class="flex items-center justify-between gap-3 py-3.5">
                        <span class="flex min-w-0 items-center gap-3">
                            <span class="rounded-lg bg-slate-100 px-2 py-1 font-mono text-xs font-semibold text-slate-600">{{ $eq->Hw_Serial }}</span>
                            <span class="truncate text-sm font-semibold text-slate-700">{{ $eq->Hw_Nombre }}</span>
                        </span>
                        <div class="flex shrink-0 items-center gap-1.5">
                            <a href="{{ route('hardware.show', $eq->Hw_Serial) }}" class="inline-grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-brand-50 hover:text-brand-600" title="Ver equipo"><i class="bi bi-box-arrow-up-right text-xs"></i></a>
                            @if(auth()->user()->esAdmin())
                                <form method="POST" action="{{ route('softnl.liberar', [$software->Softwarenl_Id, $eq->Hw_Serial]) }}" data-confirm="¿Liberar el software del equipo {{ $eq->Hw_Serial }}?">
                                    @csrf @method('DELETE')
                                    <button class="inline-grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-red-50 hover:text-red-600" title="Liberar"><i class="bi bi-x-lg text-xs"></i></button>
                                </form>
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="py-10 text-center text-sm text-slate-400">Sin equipos asignados todavía.</li>
                @endforelse
            </ul>
        </div>

        @if(auth()->user()->esAdmin())
            <div class="h-fit rounded-3xl border border-slate-200 bg-white p-6 shadow-card">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-700"><i class="bi bi-plus-circle text-brand-500"></i> Asignar a equipo</h3>
                <form method="POST" action="{{ route('softnl.asignar', $software->Softwarenl_Id) }}" class="mt-4 space-y-3">
                    @csrf
                    <select name="Hw_Serial" required
                            class="w-full appearance-none rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10">
                        <option value="">Seleccione equipo...</option>
                        @foreach($equipos as $eq)
                            <option value="{{ $eq->Hw_Serial }}">{{ $eq->Hw_Serial }} — {{ $eq->Hw_Nombre }}</option>
                        @endforeach
                    </select>
                    <button class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-600/30 transition hover:bg-brand-700 active:scale-[.98]">
                        <i class="bi bi-check2-circle"></i> Asignar software
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
