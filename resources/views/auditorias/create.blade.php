@extends('layouts.app')

@section('title', 'Nueva auditoría')
@section('page_title', 'Nueva auditoría · ' . $hardware->Hw_Serial)

@section('content')
<div class="mx-auto max-w-2xl rounded-3xl border border-slate-200 bg-white p-6 shadow-card sm:p-8">
    <div class="mb-6"><p class="font-mono text-xs text-brand-600">{{ $hardware->Hw_Serial }}</p><h2 class="mt-1 text-xl font-extrabold text-slate-800">Auditar {{ $hardware->Hw_Nombre }}</h2><p class="mt-1 text-sm text-slate-500">Usuario: {{ $hardware->usuarioInv->UsuarioInvNombre ?? 'Sin asignar' }}</p></div>
    <form method="POST" action="{{ route('auditorias.store', $hardware->Hw_Serial) }}" class="grid gap-4 sm:grid-cols-2">
        @csrf
        <label class="text-sm font-semibold text-slate-600">Fecha y hora<input type="datetime-local" name="FechaAuditoria" value="{{ now()->format('Y-m-d\\TH:i') }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"></label>
        <label class="text-sm font-semibold text-slate-600">Sistema operativo<input name="SistemaOperativo" required maxlength="50" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"></label>
        <label class="text-sm font-semibold text-slate-600">Clave del sistema<input name="ClaveSO" maxlength="50" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"></label>
        <label class="text-sm font-semibold text-slate-600">Office<input name="Office" required maxlength="50" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"></label>
        <label class="text-sm font-semibold text-slate-600">Clave de Office<input name="ClaveOffice" maxlength="50" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"></label>
        <label class="text-sm font-semibold text-slate-600 sm:col-span-2">Observaciones<textarea name="Observaciones" required maxlength="300" rows="4" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"></textarea></label>
        <div class="flex gap-3 sm:col-span-2"><a href="{{ route('hardware.show', $hardware->Hw_Serial) }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600">Cancelar</a><button class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Guardar auditoría</button></div>
    </form>
</div>
@endsection
