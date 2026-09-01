@extends('layouts.app')

@php($editando = request()->query('edit') !== null && auth()->user()->esAdmin() ? $registros->firstWhere($cfg['pk'], request()->query('edit')) : null)
@php($pkManual = $cfg['pk_manual'] ?? false)

@section('title', $cfg['titulo'])
@section('page_title', 'Catálogo: ' . $cfg['titulo'])

@section('content')
<div class="grid gap-5 lg:grid-cols-3">

    @if(auth()->user()->esAdmin())
        {{-- Formulario --}}
        <div class="h-fit lg:sticky lg:top-24">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card">
                <header class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
                    <span class="grid size-9 place-items-center rounded-xl {{ $editando ? 'bg-amber-50 text-amber-600' : 'bg-brand-50 text-brand-600' }}">
                        <i class="bi {{ $editando ? 'bi-pencil-square' : 'bi-plus-circle' }}"></i>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-700">{{ $editando ? 'Editar elemento' : 'Agregar elemento' }}</h3>
                        <p class="text-xs text-slate-400">{{ $editando ? 'Modificando #' . $editando->{$cfg['pk']} : 'Nuevo elemento en ' . $cfg['titulo'] }}</p>
                    </div>
                </header>
                <form method="POST"
                      action="{{ $editando ? route('catalogo.update', [$tabla, $editando->{$cfg['pk']}]) : route('catalogo.store', $tabla) }}"
                      x-data="{ enviando: false }" x-on:submit="enviando = true" class="p-6">
                    @csrf
                    @if($editando) @method('PUT') @endif

                    @error('general')
                        <div class="mb-4 flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-3 py-2.5 text-xs font-medium text-red-700"><i class="bi bi-exclamation-circle mt-0.5"></i>{{ $message }}</div>
                    @enderror

                    @foreach($cfg['campos'] as $c)
                        <label class="mb-3.5 block last:mb-0">
                            <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">{{ $c['label'] }}{{ $c['req'] ? ' *' : '' }}</span>
                            @if($c['tipo'] === 'date')
                                <input type="date" name="{{ $c['col'] }}"
                                       value="{{ old($c['col'], optional($editando?->{$c['col']} ?? null)->format('Y-m-d') ?? old($c['col'])) }}"
                                       class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10 @error($c['col']) border-red-300 @enderror">
                            @elseif($c['tipo'] === 'number')
                                <input type="number" step="1" name="{{ $c['col'] }}"
                                       value="{{ old($c['col'], $editando?->{$c['col']} ?? '') }}"
                                       class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10 @error($c['col']) border-red-300 @enderror">
                            @elseif($c['tipo'] === 'email')
                                <input type="email" name="{{ $c['col'] }}" maxlength="{{ $c['max'] ?? 100 }}"
                                       value="{{ old($c['col'], $editando?->{$c['col']} ?? '') }}"
                                       class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10 @error($c['col']) border-red-300 @enderror">
                            @else
                                <input type="text" name="{{ $c['col'] }}" maxlength="{{ $c['max'] ?? 255 }}"
                                       value="{{ old($c['col'], $editando?->{$c['col']} ?? '') }}"
                                       class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10 @error($c['col']) border-red-300 @enderror"
                                       @if($editando && $c['col'] === $cfg['pk'] && !$pkManual) readonly @endif>
                            @endif
                            @error($c['col'])<span class="mt-1 flex items-center gap-1 text-xs font-medium text-red-600"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>@enderror
                        </label>
                    @endforeach

                    <div class="mt-5 flex gap-2">
                        <button type="submit" :disabled="enviando"
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-brand-600 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-600/30 transition hover:bg-brand-700 active:scale-[.98] disabled:opacity-60">
                            <span x-show="!enviando" class="flex items-center gap-2"><i class="bi bi-save"></i> Guardar</span>
                            <span x-show="enviando" x-cloak class="flex items-center gap-2"><i class="bi bi-arrow-repeat animate-spin"></i> Guardando...</span>
                        </button>
                        @if($editando)
                            <a href="{{ route('catalogo.index', $tabla) }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-500 transition hover:bg-slate-50">Cancelar</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Tabla --}}
    <div class="{{ auth()->user()->esAdmin() ? 'lg:col-span-2' : '' }} overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="flex items-center gap-2 text-sm font-bold text-slate-700"><i class="bi bi-table text-brand-500"></i> {{ $cfg['titulo'] }}</h3>
            <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-bold text-brand-700">{{ $registros->total() }} total</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[520px] text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] uppercase tracking-wider text-slate-400">
                        @foreach($cfg['campos'] as $c)<th class="px-5 py-3.5 font-semibold">{{ $c['label'] }}</th>@endforeach
                        @if(auth()->user()->esAdmin())<th class="px-5 py-3.5 text-right font-semibold">Acciones</th>@endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($registros as $r)
                        <tr class="group transition hover:bg-brand-50/40">
                            @foreach($cfg['campos'] as $c)
                                @php($valor = $r->{$c['col']})
                                <td class="px-5 py-3.5 {{ $loop->first ? 'font-semibold text-slate-700' : 'text-slate-500' }}">
                                    @if($c['tipo'] === 'date')
                                        {{ $valor ? \Carbon\Carbon::parse($valor)->format('d/m/Y') : '—' }}
                                    @elseif($c['tipo'] === 'email')
                                        {{ $valor ?? '—' }}
                                    @else
                                        {{ $valor ?? '—' }}
                                    @endif
                                </td>
                            @endforeach
                            @if(auth()->user()->esAdmin())
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-1 opacity-60 transition group-hover:opacity-100">
                                        <a href="{{ route('catalogo.index', ['tabla' => $tabla, 'edit' => $r->{$cfg['pk']}]) }}" title="Editar"
                                           class="inline-grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-amber-50 hover:text-amber-600"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" action="{{ route('catalogo.destroy', [$tabla, $r->{$cfg['pk']}]) }}"
                                              data-confirm="¿Eliminar este registro del catálogo? Si está en uso el sistema lo bloqueará.">
                                            @csrf @method('DELETE')
                                            <button title="Eliminar" class="inline-grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-red-50 hover:text-red-600"><i class="bi bi-trash3"></i></button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($cfg['campos']) + 1 }}"><x-empty-state icono="bi-inboxes" texto="Este catálogo está vacío." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-3.5 text-sm text-slate-500">{{ $registros->links() }}</div>
    </div>
</div>
@endsection

