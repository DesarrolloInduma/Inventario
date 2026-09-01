@props([
    'icono' => 'bi-inbox',
    'texto' => 'No hay registros.',
    'small' => false,
    'accion' => null,
    'boton' => 'Crear',
])

<div class="flex flex-col items-center justify-center px-6 {{ $small ? 'py-10' : 'py-16' }} text-center">
    <div class="{{ $small ? 'size-12 text-xl' : 'size-16 text-2xl' }} grid place-items-center rounded-full bg-slate-100 text-slate-400">
        <i class="bi {{ $icono }}"></i>
    </div>
    <p class="mt-3 max-w-xs text-sm text-slate-400">{{ $texto }}</p>
    @if($accion)
        <a href="{{ route($accion) }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-brand-600/30 transition hover:bg-brand-700">
            <i class="bi bi-plus-lg"></i> {{ $boton }}
        </a>
    @endif
</div>
