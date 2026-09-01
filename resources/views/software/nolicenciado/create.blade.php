@extends('layouts.app')

@section('title', $software->exists ? 'Editar software' : 'Nuevo software')
@section('page_title', $software->exists ? 'Editar software #' . $software->Softwarenl_Id : 'Registrar software no licenciado')

@section('content')
<form method="POST"
      action="{{ $software->exists ? route('softnl.update', $software->Softwarenl_Id) : route('softnl.store') }}"
      x-data="{ enviando: false }" x-on:submit="enviando = true"
      class="mx-auto max-w-2xl">
    @csrf
    @if($software->exists) @method('PUT') @endif

    <div class="rounded-3xl border border-slate-200 bg-white shadow-card">
        <header class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
            <span class="grid size-9 place-items-center rounded-xl bg-sky-50 text-sky-600"><i class="bi bi-app-indicator"></i></span>
            <div>
                <h3 class="text-sm font-bold text-slate-700">Datos del software</h3>
                <p class="text-xs text-slate-400">Aplicaciones sin licencia comercial</p>
            </div>
        </header>
        <div class="grid gap-4 p-6 sm:grid-cols-3">
            <label class="block sm:col-span-2">
                <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Nombre *</span>
                <input type="text" name="Softwarenl_Nombre" maxlength="50" value="{{ old('Softwarenl_Nombre', $software->Softwarenl_Nombre) }}"
                       class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10 @error('Softwarenl_Nombre') border-red-300 @enderror">
                @error('Softwarenl_Nombre')<span class="mt-1 flex items-center gap-1 text-xs font-medium text-red-600"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>@enderror
            </label>
            <label class="block">
                <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Versión</span>
                <input type="text" name="Softwarenl_Version" maxlength="50" value="{{ old('Softwarenl_Version', $software->Softwarenl_Version) }}"
                       class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10">
            </label>
            <label class="block sm:col-span-3">
                <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Tipo de software</span>
                <select name="Soft_Tipo_Id"
                        class="w-full appearance-none rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10">
                    <option value="">— Sin tipo —</option>
                    @foreach($tipos as $t)
                        <option value="{{ $t->Soft_Tipo_Id }}" @selected(old('Soft_Tipo_Id', $software->Soft_Tipo_Id) == $t->Soft_Tipo_Id)>{{ $t->Soft_Tipo_Nombre }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </div>

    <div class="sticky bottom-4 z-10 mt-5 flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white/90 p-3 shadow-pop backdrop-blur">
        <a href="{{ route('softnl.index') }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-500 transition hover:bg-slate-100">Cancelar</a>
        <button type="submit" :disabled="enviando"
                class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-600/30 transition hover:bg-brand-700 active:scale-[.98] disabled:opacity-60">
            <span x-show="!enviando" class="flex items-center gap-2"><i class="bi bi-save"></i> Guardar</span>
            <span x-show="enviando" x-cloak class="flex items-center gap-2"><i class="bi bi-arrow-repeat animate-spin"></i> Guardando...</span>
        </button>
    </div>
</form>
@endsection
