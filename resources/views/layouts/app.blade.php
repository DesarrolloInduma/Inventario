<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inicio') · Inventario INDUMA</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%234f46e5'%3E%3Cpath d='M12 1.5l9 5.25v10.5L12 22.5l-9-5.25V6.75L12 1.5zm0 2.6L5.25 8v8L12 19.9l6.75-3.9V8L12 4.1zM11.25 9h1.5v6h-1.5z'/%3E%3C/svg%3E">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="induma-surface bg-slate-100 font-sans text-slate-800 antialiased">

{{-- Indicador de carga entre páginas --}}
<div id="app-progress" aria-hidden="true"><span></span></div>
<div id="app-loader" role="status" aria-live="polite">
    <div class="app-loader__card">
        <span class="app-loader__ring"><i id="app-loader-icon" class="bi bi-hdd-network"></i></span>
        <p class="app-loader__title" id="app-loader-title">Cargando…</p>
        <p class="app-loader__sub" id="app-loader-sub">Preparando la información del módulo</p>
        <span class="app-loader__bar"><span></span></span>
    </div>
</div>

@auth
@php
    $nav = [
        ['grupo' => 'General', 'items' => [
            ['ruta' => 'dashboard', 'patron' => 'dashboard', 'icono' => 'bi-speedometer2', 'texto' => 'Panel principal'],
            ['ruta' => 'auditorias.index', 'patron' => 'auditorias.*', 'icono' => 'bi-clipboard2-check', 'texto' => 'Auditorías'],
            ['ruta' => 'reportes.index', 'patron' => 'reportes.*', 'icono' => 'bi-file-earmark-bar-graph', 'texto' => 'Reportes', 'children' => [
                ['ruta' => 'reportes.hardware', 'patron' => 'reportes.hardware', 'icono' => 'bi-pc-display', 'texto' => 'Hardware'],
                ['ruta' => 'reportes.software', 'patron' => 'reportes.software', 'icono' => 'bi-key', 'texto' => 'Software licenciado'],
            ]],
        ]],
        ['grupo' => 'Inventario', 'items' => [
            ['ruta' => 'hardware.index', 'patron' => 'hardware.*|mant.create|mant.store', 'icono' => 'bi-pc-display', 'texto' => 'Hardware'],
            ['ruta' => 'softlic.index', 'patron' => 'softlic.*|softnl.*', 'icono' => 'bi-window-stack', 'texto' => 'Software', 'children' => [
                ['ruta' => 'softlic.index', 'patron' => 'softlic.*', 'icono' => 'bi-key', 'texto' => 'Licenciado'],
                ['ruta' => 'softnl.index', 'patron' => 'softnl.*', 'icono' => 'bi-app-indicator', 'texto' => 'Libre'],
            ]],
            ['ruta' => 'mant.index', 'patron' => 'mant.index|mant.show|mant.destroy', 'icono' => 'bi-tools', 'texto' => 'Mantenimientos'],
        ]],
        ['grupo' => 'Configuración', 'admin' => true, 'items' => [
            ['ruta' => 'catalogo.index', 'params' => ['tabla' => 'tipos'], 'patron' => 'catalogos/tipos', 'icono' => 'bi-tags', 'texto' => 'Tipos de hardware'],
            ['ruta' => 'catalogo.index', 'params' => ['tabla' => 'marcas'], 'patron' => 'catalogos/marcas', 'icono' => 'bi-bookmark-star', 'texto' => 'Marcas de hardware'],
            ['ruta' => 'catalogo.index', 'params' => ['tabla' => 'modelos'], 'patron' => 'catalogos/modelos', 'icono' => 'bi-box', 'texto' => 'Modelos de hardware'],
            ['ruta' => 'catalogo.index', 'params' => ['tabla' => 'monitores'], 'patron' => 'catalogos/monitores', 'icono' => 'bi-display', 'texto' => 'Monitores asignables'],
            ['ruta' => 'catalogo.index', 'params' => ['tabla' => 'leasings'], 'patron' => 'catalogos/leasings', 'icono' => 'bi-file-earmark-text', 'texto' => 'Contratos de leasing'],
            ['ruta' => 'catalogo.index', 'params' => ['tabla' => 'ubicaciones'], 'patron' => 'catalogos/ubicaciones', 'icono' => 'bi-geo-alt', 'texto' => 'Ubicaciones físicas'],
            ['ruta' => 'catalogo.index', 'params' => ['tabla' => 'dispositivos'], 'patron' => 'catalogos/dispositivos', 'icono' => 'bi-mouse2', 'texto' => 'Dispositivos y periféricos'],
            ['ruta' => 'catalogo.index', 'params' => ['tabla' => 'proveedores'], 'patron' => 'catalogos/proveedores', 'icono' => 'bi-truck', 'texto' => 'Proveedores tecnológicos'],
            ['ruta' => 'catalogo.index', 'params' => ['tabla' => 'propietarios'], 'patron' => 'catalogos/propietarios', 'icono' => 'bi-person-badge', 'texto' => 'Propietarios de activos'],
            ['ruta' => 'catalogo.index', 'params' => ['tabla' => 'seguros'], 'patron' => 'catalogos/seguros', 'icono' => 'bi-shield-check', 'texto' => 'Pólizas y seguros'],
            ['ruta' => 'catalogo.index', 'params' => ['tabla' => 'tipos-software'], 'patron' => 'catalogos/tipos-software', 'icono' => 'bi-window-stack', 'texto' => 'Categorías de software'],
        ]],
    ];
@endphp

<div x-data="{ sidebar: false }" class="min-h-screen">

    {{-- Backdrop móvil --}}
    <div x-show="sidebar" x-transition.opacity x-cloak
         class="fixed inset-0 z-30 bg-slate-900/60 backdrop-blur-sm lg:hidden"
         @click="sidebar = false"></div>

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col bg-[#101a24] transition-transform duration-300 lg:translate-x-0"
           :class="sidebar && '!translate-x-0'"
           x-cloak>
        <div class="flex h-16 items-center gap-3 border-b border-white/5 px-6">
            <div class="flex h-9 w-28 items-center justify-center rounded-xl bg-white px-2 shadow-lg shadow-black/20">
                <img src="{{ asset('images/logo-induma.png') }}" alt="INDUMA S.A.S." class="max-h-7 w-auto object-contain">
            </div>
            <div class="leading-tight">
                <p class="font-display text-lg font-bold tracking-wide text-white">INDUMA</p>
                <p class="text-[10px] font-semibold uppercase tracking-[.18em] text-slate-400">Activos tecnológicos</p>
            </div>
        </div>

        <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5">
            @foreach($nav as $seccion)
                @if(!($seccion['admin'] ?? false) || auth()->user()->esAdmin())
                <div>
                    @if($seccion['admin'] ?? false)
                        <details {{ request()->is('catalogos/*') ? 'open' : '' }}>
                            <summary class="flex cursor-pointer list-none items-center gap-3 px-3 pb-2 text-left text-[10px] font-bold uppercase tracking-widest text-slate-500"><span class="flex-1">{{ $seccion['grupo'] }}</span><i class="bi bi-chevron-down text-[10px]"></i></summary>
                            <ul class="space-y-0.5">
                                @foreach($seccion['items'] as $item)
                                    @php($activo = request()->is('catalogos/' . ($item['params']['tabla'] ?? '—')))
                                    <li><a href="{{ route($item['ruta'], $item['params'] ?? []) }}" class="group relative flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition {{ $activo ? 'bg-brand-600/15 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}"><i class="bi {{ $item['icono'] }} text-base {{ $activo ? 'text-brand-400' : 'text-slate-500 group-hover:text-slate-300' }} transition"></i>{{ $item['texto'] }}</a></li>
                                @endforeach
                            </ul>
                        </details>
                    @else
                        <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">{{ $seccion['grupo'] }}</p>
                        <ul class="space-y-0.5">
                        @foreach($seccion['items'] as $item)
                            @php($activo = request()->routeIs(explode('|', $item['patron'])) || ($item['ruta'] === 'catalogo.index' && request()->is('catalogos/' . ($item['params']['tabla'] ?? '—'))))
                            <li>
                                @if(isset($item['children']))
                                <details {{ $activo ? 'open' : '' }}>
                                <summary class="group relative flex cursor-pointer list-none items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition {{ $activo ? 'bg-brand-600/15 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                    <span class="absolute left-0 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r-full bg-brand-500 {{ $activo ? '' : 'scale-y-0 transition-transform group-hover:scale-y-100' }}"></span>
                                    <i class="bi {{ $item['icono'] }} text-base {{ $activo ? 'text-brand-400' : 'text-slate-500 group-hover:text-slate-300' }} transition"></i>
                                    <span class="flex-1 text-left">{{ $item['texto'] }}</span><i class="bi bi-chevron-down text-[10px]"></i>
                                </summary>
                                <ul class="ml-9 mt-0.5 space-y-0.5 border-l border-white/10 pl-2">
                                    @foreach($item['children'] as $child)
                                        <li><a href="{{ route($child['ruta']) }}" class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs font-medium {{ request()->routeIs($child['patron']) ? 'text-white' : 'text-slate-500 hover:text-slate-200' }}"><i class="bi {{ $child['icono'] }}"></i>{{ $child['texto'] }}</a></li>
                                    @endforeach
                                </ul>
                                </details>
                                @else
                                <a href="{{ route($item['ruta'], $item['params'] ?? []) }}"
                                   class="group relative flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition
                                          {{ $activo ? 'bg-brand-600/15 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                    <span class="absolute left-0 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r-full bg-brand-500 {{ $activo ? '' : 'scale-y-0 transition-transform group-hover:scale-y-100' }}"></span>
                                    <i class="bi {{ $item['icono'] }} text-base {{ $activo ? 'text-brand-400' : 'text-slate-500 group-hover:text-slate-300' }} transition"></i>
                                    {{ $item['texto'] }}
                                </a>
                                @endif
                            </li>
                        @endforeach
                        </ul>
                    @endif
                </div>
            @endif
            @endforeach
        </nav>

        <div class="border-t border-white/5 p-3">
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left transition hover:bg-white/5">
                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-gradient-to-br from-slate-600 to-slate-800 text-sm font-bold text-white ring-2 ring-white/10">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-semibold text-white">{{ auth()->user()->name }}</span>
                        <span class="flex items-center gap-1.5">
                            @if(auth()->user()->esAdmin())
                                <span class="inline-flex items-center gap-1 rounded-md bg-amber-400/15 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-amber-300">Admin</span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-md bg-sky-400/15 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-sky-300">Consulta</span>
                            @endif
                        </span>
                    </span>
                    <i class="bi bi-chevron-up text-xs text-slate-500 transition" :class="open && 'rotate-180'"></i>
                </button>
                <div x-show="open" @click.outside="open = false" x-transition x-cloak
                     class="absolute bottom-full left-0 right-0 mb-2 rounded-xl border border-slate-700 bg-slate-800 p-1 shadow-pop">
                    <form method="POST" action="{{ route('logout') }}" data-loading="Cerrando sesión…" data-loading-sub="Hasta pronto">
                        @csrf
                        <button class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-300 transition hover:bg-red-500/10 hover:text-red-300">
                            <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    {{-- Columna principal --}}
    <div class="flex min-h-screen flex-col lg:pl-72">

        {{-- Topbar --}}
        <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-slate-200/80 bg-white/80 px-4 backdrop-blur-md sm:px-6">
            <button class="grid size-10 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 lg:hidden" @click="sidebar = true" aria-label="Abrir menú">
                <i class="bi bi-list text-xl"></i>
            </button>

            <button type="button" title="Volver a la página anterior"
                    onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = '{{ route('dashboard') }}'; }"
                    class="grid size-9 shrink-0 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700"
                    aria-label="Volver a la página anterior">
                <i class="bi bi-arrow-left"></i>
            </button>
            <h1 class="truncate text-base font-bold text-slate-800 sm:text-lg">@yield('page_title', 'Panel principal')</h1>

            <div class="ml-auto flex items-center gap-2 sm:gap-3">
                <form action="{{ route('hardware.index') }}" method="GET" class="relative hidden md:block">
                    <i class="bi bi-search pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input type="text" name="q" placeholder="Buscar equipo... ⏎"
                           class="h-10 w-56 rounded-xl border-0 bg-slate-100 pl-10 pr-4 text-sm text-slate-700 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-brand-500/40 xl:w-72">
                </form>
                <a href="{{ route('hardware.create') }}" class="hidden items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-brand-600/30 transition hover:bg-brand-700 active:scale-[.98] sm:inline-flex">
                    <i class="bi bi-plus-lg"></i> Nuevo hardware
                </a>
            </div>
        </header>

        {{-- Contenido --}}
        <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-6 sm:px-6 lg:px-8">
            @if(session('ok'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition.opacity.duration.500ms
                     class="mb-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-card">
                    <i class="bi bi-check-circle-fill mt-0.5 text-emerald-500"></i>
                    <p class="flex-1">{{ session('ok') }}</p>
                    <button @click="show=false" class="text-emerald-400 hover:text-emerald-600"><i class="bi bi-x-lg text-xs"></i></button>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-5 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-card">
                    <i class="bi bi-exclamation-triangle-fill mt-0.5 text-red-500"></i>
                    <p class="flex-1">{{ session('error') }}</p>
                </div>
            @endif

            <div class="animate-fade-up">
                @yield('content')
            </div>
        </main>

        <footer class="border-t border-slate-200/70 py-4 text-center text-xs text-slate-400">
            Sistema de Gestión de Activos Tecnológicos · INDUMA S.A.S. · {{ date('Y') }}
        </footer>
    </div>
</div>

{{-- Toasts reactivos --}}
<div x-data="{ items: [] }"
     @toast.window="items.push($event.detail); setTimeout(() => items.shift(), 3500)"
     class="pointer-events-none fixed bottom-5 right-5 z-50 space-y-2">
    <template x-for="(t, i) in items" :key="i">
        <div x-transition:x-show.duration.300ms
             class="animate-toast-in pointer-events-auto flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-white shadow-pop"
             :class="t.type === 'error' ? 'bg-red-600' : 'bg-slate-900'">
            <i class="bi" :class="t.type === 'error' ? 'bi-x-circle' : 'bi-check-circle'"></i>
            <span x-text="t.message"></span>
        </div>
    </template>
</div>

{{-- Modal de confirmación global --}}
<div x-data="{ open: false, message: '', action: null }" x-show="open" style="display: none;"
     @confirm-open.window="message = $event.detail.message; action = $event.detail.action; open = true"
    class="fixed inset-0 z-50 grid place-items-center p-4">
    <div x-show="open" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open=false"></div>
    <div x-show="open" x-transition.scale.origin.center.duration.200ms
         class="relative w-full max-w-sm rounded-3xl bg-white p-6 text-center shadow-pop">
        <div class="mx-auto mb-4 grid size-14 place-items-center rounded-full bg-red-100 text-red-600">
            <i class="bi bi-trash3 text-2xl"></i>
        </div>
        <h3 class="text-lg font-bold text-slate-800">Confirmar acción</h3>
        <p class="mt-1 text-sm text-slate-500" x-text="message"></p>
        <div class="mt-5 flex gap-3">
            <button @click="open=false" class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Cancelar</button>
            <button @click="action && action(); open=false"
                    class="flex-1 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-red-600/30 transition hover:bg-red-700 active:scale-[.98]">
                Sí, continuar
            </button>
        </div>
    </div>
</div>
@else
{{ $slot ?? '' }}
@endauth

@stack('scripts')
</body>
</html>
