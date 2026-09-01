@extends('layouts.app')

@section('title', 'Nuevo mantenimiento')
@section('page_title', 'Registrar mantenimiento')

@section('content')
<form method="POST" action="{{ route('mant.store', $hardware->Hw_Serial) }}"
      x-data="{ enviando: false, hechos: 0 }" x-on:submit="enviando = true"
      class="mx-auto max-w-4xl">
    @csrf

    {{-- Equipo --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-amber-500 via-orange-600 to-slate-900 px-6 py-6 shadow-card sm:px-8">
        <div class="pointer-events-none absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 22px 22px;"></div>
        <div class="relative flex flex-wrap items-center gap-4">
            <span class="grid size-12 place-items-center rounded-2xl bg-white/10 text-white ring-1 ring-white/20 backdrop-blur"><i class="bi bi-tools text-xl"></i></span>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-amber-200">Mantenimiento para</p>
                <h2 class="truncate text-lg font-extrabold tracking-tight text-white">
                    <span class="font-mono">{{ $hardware->Hw_Serial }}</span> · {{ $hardware->Hw_Nombre }}
                </h2>
                <p class="text-xs text-slate-300">Usuario: {{ $hardware->usuarioInv->UsuarioInvNombre ?? '—' }}</p>
            </div>
        </div>
    </div>

    {{-- Datos generales --}}
    <div class="mt-5 rounded-3xl border border-slate-200 bg-white shadow-card">
        <header class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
            <span class="grid size-9 place-items-center rounded-xl bg-brand-50 text-brand-600"><i class="bi bi-calendar-check"></i></span>
            <h3 class="text-sm font-bold text-slate-700">Datos generales</h3>
        </header>
        <div class="grid gap-4 p-6 sm:grid-cols-3">
            <label class="block">
                <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Fecha *</span>
                <input type="date" name="mantenimiento_fecha" value="{{ old('mantenimiento_fecha', date('Y-m-d')) }}"
                       class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10 @error('mantenimiento_fecha') border-red-300 @enderror">
                @error('mantenimiento_fecha')<span class="mt-1 flex items-center gap-1 text-xs font-medium text-red-600"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>@enderror
            </label>
            <label class="block">
                <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Realiza</span>
                <input type="text" name="mantenimiento_Realiza" maxlength="60" value="{{ old('mantenimiento_Realiza') }}"
                       class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10">
            </label>
            <label class="block">
                <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Recibe</span>
                <input type="text" name="mantenimiento_Recibe" maxlength="60" value="{{ old('mantenimiento_Recibe', $hardware->usuarioInv->UsuarioInvNombre ?? '') }}"
                       class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10">
            </label>
        </div>
    </div>

    {{-- Checklist interactivo --}}
    <div class="mt-5 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card"
         x-effect="hechos = $el.querySelectorAll('.chk:checked').length">
        <header class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-4">
            <span class="flex items-center gap-2">
                <i class="bi bi-list-check text-emerald-500"></i>
                <h3 class="text-sm font-bold text-slate-700">Checklist de tareas</h3>
            </span>
            <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600">
                <i class="bi bi-check2-all"></i><span x-text="hechos"></span>/7 completadas
            </span>
        </header>
        <div class="h-1.5 bg-slate-100">
            <div class="h-full rounded-r-full bg-gradient-to-r from-brand-500 to-emerald-500 transition-all duration-300"
                 :style="'width:' + (hechos / 7 * 100) + '%'"></div>
        </div>
        <ul class="divide-y divide-slate-50 px-6">
            @foreach($checks as $key => $label)
                <li class="flex flex-wrap items-start gap-x-4 gap-y-2 py-4 sm:flex-nowrap">
                    <label class="flex shrink-0 cursor-pointer select-none items-center justify-between gap-3 rounded-xl border border-slate-200 px-4 py-2 transition hover:bg-slate-50 min-[420px]:w-40">
                        <input type="checkbox" value="1" name="mantenimiento_{{ $key }}_C" class="peer sr-only chk" id="chk{{ $key }}"
                               @checked(old('mantenimiento_' . $key . '_C'))>
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-400 peer-checked:text-emerald-600 transition-colors"
                              x-data="{ on: false }"
                              x-init="on = document.getElementById('chk{{ $key }}').checked; document.getElementById('chk{{ $key }}').addEventListener('change', e => on = e.target.checked)"
                              x-text="on ? 'Hecho' : 'Pendiente'"></span>
                        <span class="relative h-6 w-11 rounded-full bg-slate-200 transition peer-checked:bg-emerald-500 after:absolute after:left-0.5 after:top-0.5 after:size-5 after:rounded-full after:bg-white after:shadow after:transition-all peer-checked:after:translate-x-5"></span>
                    </label>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-slate-600">{{ $label }}</p>
                        <input type="text" name="mantenimiento_{{ $key }}_O" maxlength="300"
                               placeholder="Observación (opcional)" value="{{ old('mantenimiento_' . $key . '_O') }}"
                               class="mt-1.5 w-full rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-xs outline-none transition focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-500/10">
                    </div>
                </li>
            @endforeach
            <li class="py-4">
                <p class="mb-1.5 text-sm font-semibold text-slate-600"><i class="bi bi-journal-text mr-1 text-slate-400"></i>Observaciones generales</p>
                <textarea name="mantenimiento_Observaciones" rows="2" maxlength="300"
                          placeholder="Resumen del servicio realizado..."
                          class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10">{{ old('mantenimiento_Observaciones') }}</textarea>
            </li>
        </ul>
    </div>

    <div class="sticky bottom-4 z-10 mt-5 flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white/90 p-3 shadow-pop backdrop-blur">
        <a href="{{ route('hardware.show', $hardware->Hw_Serial) }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-500 transition hover:bg-slate-100">Cancelar</a>
        <button type="submit" :disabled="enviando"
                class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-600/30 transition hover:bg-brand-700 active:scale-[.98] disabled:opacity-60">
            <span x-show="!enviando" class="flex items-center gap-2"><i class="bi bi-save"></i> Guardar mantenimiento</span>
            <span x-show="enviando" x-cloak class="flex items-center gap-2"><i class="bi bi-arrow-repeat animate-spin"></i> Guardando...</span>
        </button>
    </div>
</form>
@endsection
