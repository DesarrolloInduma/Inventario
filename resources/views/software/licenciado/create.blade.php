@extends('layouts.app')

@section('title', $software->exists ? 'Editar licencia' : 'Nueva licencia')
@section('page_title', $software->exists ? 'Editar software licenciado #' . $software->Software_Id : 'Registrar software licenciado')

@section('content')
<form method="POST"
      action="{{ $software->exists ? route('softlic.update', $software->Software_Id) : route('softlic.store') }}"
      x-data="{ enviando: false }" x-on:submit="enviando = true"
      class="mx-auto max-w-3xl">
    @csrf
    @if($software->exists) @method('PUT') @endif

    <div class="rounded-3xl border border-slate-200 bg-white shadow-card">
        <header class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
            <span class="grid size-9 place-items-center rounded-xl bg-emerald-50 text-emerald-600"><i class="bi bi-key"></i></span>
            <div>
                <h3 class="text-sm font-bold text-slate-700">Datos de la licencia</h3>
                <p class="text-xs text-slate-400">Producto y condiciones de licenciamiento</p>
            </div>
        </header>
        <div class="grid gap-4 p-6 sm:grid-cols-2">
            <label class="block sm:col-span-2">
                <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Tipo de software *</span>
                <select name="Soft_Tipo_Id"
                        class="w-full appearance-none rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10 @error('Soft_Tipo_Id') border-red-300 @enderror">
                    <option value="">Seleccione...</option>
                    @foreach($tipos as $t)
                        <option value="{{ $t->Soft_Tipo_Id }}" @selected(old('Soft_Tipo_Id', $software->Soft_Tipo_Id) == $t->Soft_Tipo_Id)>{{ $t->Soft_Tipo_Nombre }}</option>
                    @endforeach
                </select>
                @error('Soft_Tipo_Id')<span class="mt-1 flex items-center gap-1 text-xs font-medium text-red-600"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>@enderror
            </label>

            <label class="block sm:col-span-2">
                <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Nombre de la licencia *</span>
                <input type="text" name="Software_Nombre" maxlength="100" required placeholder="Ej: Microsoft 365 Business Standard" value="{{ old('Software_Nombre', $software->Software_Nombre) }}"
                       class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-600 focus:ring-4 focus:ring-brand-600/10 @error('Software_Nombre') border-red-300 @enderror">
                @error('Software_Nombre')<span class="mt-1 flex items-center gap-1 text-xs font-medium text-red-600"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>@enderror
            </label>

            <label class="block">
                <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Clave de licencia</span>
                <input type="text" name="Software_Clave" maxlength="50" value="{{ old('Software_Clave', $software->Software_Clave) }}"
                       class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 font-mono text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10 @error('Software_Clave') border-red-300 @enderror">
            </label>
            <label class="block">
                <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Cantidad de licencias</span>
                <input type="number" min="0" name="Software_Cantidad" value="{{ old('Software_Cantidad', $software->Software_Cantidad) }}"
                       class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10 @error('Software_Cantidad') border-red-300 @enderror">
                <span class="mt-1 block text-[11px] text-slate-400">Deja vacío si no tiene límite.</span>
            </label>
            <label class="block">
                <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Versión</span>
                <input type="text" name="Software_Version" maxlength="50" value="{{ old('Software_Version', $software->Software_Version) }}"
                       class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10">
            </label>
            <label class="block">
                <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Tipo de licenciamiento</span>
                <input type="text" name="Software_Tip_Licenciamiento" maxlength="50" placeholder="Ej: OEM, Retail, Volumen" value="{{ old('Software_Tip_Licenciamiento', $software->Software_Tip_Licenciamiento) }}"
                       class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10">
            </label>
        </div>
    </div>

    <div class="sticky bottom-4 z-10 mt-5 flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white/90 p-3 shadow-pop backdrop-blur">
        <a href="{{ route('softlic.index') }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-500 transition hover:bg-slate-100">Cancelar</a>
        <button type="submit" :disabled="enviando"
                class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-600/30 transition hover:bg-brand-700 active:scale-[.98] disabled:opacity-60">
            <span x-show="!enviando" class="flex items-center gap-2"><i class="bi bi-save"></i> Guardar</span>
            <span x-show="enviando" x-cloak class="flex items-center gap-2"><i class="bi bi-arrow-repeat animate-spin"></i> Guardando...</span>
        </button>
    </div>
</form>
@endsection
