@extends('layouts.app')

@section('title', 'Licencia #' . $software->Software_Id)
@section('page_title', 'Detalle de licencia')

@section('content')
<div class="mx-auto max-w-5xl">

    {{-- Cabecera de la licencia --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-600 via-teal-700 to-slate-900 px-6 py-7 shadow-card sm:px-8">
        <div class="pointer-events-none absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 22px 22px;"></div>
        <div class="relative flex flex-wrap items-center gap-4">
            <span class="grid size-14 place-items-center rounded-2xl bg-white/10 text-white ring-1 ring-white/20 backdrop-blur">
                <i class="bi bi-key text-2xl"></i>
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-emerald-200">{{ $software->tipoSoftware->Soft_Tipo_Nombre ?? '—' }} · {{ $software->Software_Tip_Licenciamiento ?? 'sin tipo' }}</p>
                <h2 class="truncate font-mono text-xl font-extrabold tracking-tight text-white">{{ $software->Software_Clave ?? '(sin clave)' }}</h2>
                <p class="text-sm text-slate-300">Versión {{ $software->Software_Version ?? '?' }}</p>
            </div>
            @php($asignadas = $software->hardwares->count())
            @php($agotada = $software->Software_Cantidad !== null && $asignadas >= $software->Software_Cantidad)
            <div class="rounded-2xl bg-white/10 px-5 py-3 text-center ring-1 ring-white/20 backdrop-blur">
                <p class="text-2xl font-extrabold text-white">{{ $asignadas }}<span class="text-base font-semibold text-slate-300">{{ $software->Software_Cantidad !== null ? ' / ' . $software->Software_Cantidad : '' }}</span></p>
                <p class="text-[11px] font-bold uppercase tracking-wide {{ $agotada ? 'text-red-300' : 'text-emerald-200' }}">{{ $agotada ? 'Agotada' : 'Asignadas' }}</p>
            </div>
        </div>
        <div class="relative mt-6 flex flex-wrap gap-2">
            @if(auth()->user()->esAdmin())
                <a href="{{ route('softlic.edit', $software->Software_Id) }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-brand-700 active:scale-[.98]"><i class="bi bi-pencil"></i> Editar</a>
                <form method="POST" action="{{ route('softlic.destroy', $software->Software_Id) }}" data-confirm="La licencia y sus asignaciones se eliminarán." class="ml-auto">
                    @csrf @method('DELETE')
                    <button class="inline-flex items-center gap-2 rounded-xl bg-red-500/15 px-4 py-2 text-sm font-semibold text-red-100 ring-1 ring-red-400/30 transition hover:bg-red-500/25 active:scale-[.98]"><i class="bi bi-trash3"></i> Eliminar</button>
                </form>
            @endif
        </div>
    </div>

    {{-- Asignación --}}
    <div class="mt-5 grid gap-5 lg:grid-cols-3">
        <div class="lg:col-span-2 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card">
            <header class="border-b border-slate-100 px-6 py-4">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-700"><i class="bi bi-pc-display text-brand-500"></i> Equipos con esta licencia ({{ $asignadas }})</h3>
            </header>
            <ul class="divide-y divide-slate-50 px-6">
                @forelse($software->hardwares as $eq)
                    <li class="flex items-center justify-between gap-3 py-3.5">
                        <span class="flex min-w-0 items-center gap-3">
                            <span class="rounded-lg bg-slate-100 px-2 py-1 font-mono text-xs font-semibold text-slate-600">{{ $eq->Hw_Serial }}</span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-slate-700">{{ $eq->Hw_Nombre }}</span>
                                <span class="block truncate text-xs text-slate-400">{{ $eq->usuarioInv->UsuarioInvNombre ?? '' }}</span>
                            </span>
                        </span>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <a href="{{ route('hardware.show', $eq->Hw_Serial) }}" class="inline-grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-brand-50 hover:text-brand-600" title="Ver equipo"><i class="bi bi-box-arrow-up-right text-xs"></i></a>
                            @if(auth()->user()->esAdmin())
                                <form method="POST" action="{{ route('softlic.liberar', [$software->Software_Id, $eq->Hw_Serial]) }}" data-confirm="¿Liberar la licencia del equipo {{ $eq->Hw_Serial }}?">
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
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-card h-fit">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-700"><i class="bi bi-plus-circle text-brand-500"></i> Asignar a equipo</h3>
                @error('Hw_Serial')
                    <div class="mt-3 flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-3 py-2.5 text-xs font-medium text-red-700"><i class="bi bi-exclamation-circle mt-0.5"></i>{{ $message }}</div>
                @enderror
                <form method="POST" action="{{ route('softlic.asignar', $software->Software_Id) }}" class="mt-4 space-y-3">
                    @csrf
                    <select name="Hw_Serial" required
                            class="w-full appearance-none rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10">
                        <option value="">Seleccione equipo...</option>
                        @foreach($equipos as $eq)
                            <option value="{{ $eq->Hw_Serial }}">{{ $eq->Hw_Serial }} — {{ $eq->Hw_Nombre }}</option>
                        @endforeach
                    </select>
                    <button class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-600/30 transition hover:bg-brand-700 active:scale-[.98]" @if($agotada) disabled title="Licencias agotadas" @endif>
                        <i class="bi bi-check2-circle"></i> {{ $agotada ? 'Sin disponibilidad' : 'Asignar licencia' }}
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
