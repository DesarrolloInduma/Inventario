@extends('layouts.app')

@section('title', $hardware->Hw_Serial)
@section('page_title', 'Detalle del equipo')

@section('content')
{{-- Encabezado tipo ficha --}}
<div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card">
    <div class="relative bg-gradient-to-r from-brand-700 via-brand-800 to-slate-900 px-6 py-7 sm:px-8">
        <div class="pointer-events-none absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 22px 22px;"></div>
        <div class="relative flex flex-wrap items-center gap-4">
            <span class="grid size-14 place-items-center rounded-2xl bg-white/10 text-white ring-1 ring-white/20 backdrop-blur">
                <i class="bi bi-pc-display text-2xl"></i>
            </span>
            <div class="min-w-0 flex-1">
                <p class="font-mono text-xs font-semibold tracking-wider text-brand-200">{{ $hardware->Hw_Serial }}</p>
                <h2 class="truncate text-xl font-extrabold tracking-tight text-white">{{ $hardware->Hw_Nombre }}</h2>
            </div>
            <div class="flex items-center gap-2">
                @if($hardware->Activo)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-400/15 px-3 py-1.5 text-xs font-bold text-emerald-300 ring-1 ring-emerald-400/30"><span class="size-1.5 rounded-full bg-emerald-400"></span>Activo</span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-400/15 px-3 py-1.5 text-xs font-bold text-red-300 ring-1 ring-red-400/30"><span class="size-1.5 rounded-full bg-red-400"></span>Inactivo</span>
                @endif
                @if($hardware->Revisado)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-400/15 px-3 py-1.5 text-xs font-bold text-sky-300 ring-1 ring-sky-400/30"><i class="bi bi-patch-check"></i>Revisado</span>
                @endif
            </div>
        </div>

        <div class="relative mt-6 flex flex-wrap gap-2">
            <a href="{{ route('hardware.edit', $hardware->Hw_Serial) }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-brand-700 active:scale-[.98]">
                <i class="bi bi-pencil"></i> Editar equipo
            </a>
            <a href="{{ route('mant.create', $hardware->Hw_Serial) }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-sm font-semibold text-white ring-1 ring-white/20 backdrop-blur transition hover:bg-white/20 active:scale-[.98]">
                <i class="bi bi-tools"></i> Registrar mantenimiento
            </a>
            <a href="{{ route('auditorias.create', $hardware->Hw_Serial) }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-sm font-semibold text-white ring-1 ring-white/20 backdrop-blur transition hover:bg-white/20 active:scale-[.98]">
                <i class="bi bi-clipboard2-check"></i> Auditar
            </a>
            @if(auth()->user()->esAdmin())
                                <form method="POST" action="{{ $hardware->Activo ? route('hardware.destroy', $hardware->Hw_Serial) : route('hardware.restore', $hardware->Hw_Serial) }}"
                                            data-confirm="{{ $hardware->Activo ? 'El equipo se marcará como inactivo y conservará sus registros.' : 'El equipo se marcará como activo nuevamente.' }}"
                      class="ml-auto">
                                        @csrf @method($hardware->Activo ? 'DELETE' : 'PATCH')
                                        <button class="inline-flex items-center gap-2 rounded-xl {{ $hardware->Activo ? 'bg-red-500/15 text-red-200 ring-red-400/30 hover:bg-red-500/25' : 'bg-emerald-500/15 text-emerald-200 ring-emerald-400/30 hover:bg-emerald-500/25' }} px-4 py-2 text-sm font-semibold ring-1 transition active:scale-[.98]">
                                                <i class="bi {{ $hardware->Activo ? 'bi-archive' : 'bi-arrow-counterclockwise' }}"></i> {{ $hardware->Activo ? 'Dar de baja' : 'Reactivar' }}
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

{{-- Especificaciones --}}
<div class="mt-5 grid gap-5 lg:grid-cols-2">
    <div class="rounded-3xl border border-slate-200 bg-white shadow-card">
        <header class="flex items-center gap-2 border-b border-slate-100 px-6 py-4">
            <i class="bi bi-cpu text-brand-500"></i><h3 class="text-sm font-bold text-slate-700">Especificaciones</h3>
        </header>
        <dl class="divide-y divide-slate-50 px-6">
            @foreach([
                ['Tipo', $hardware->tipo->Nombre ?? null, 'bi-tags'],
                ['Marca / Modelo', trim(($hardware->modelo?->marca?->Nombre ?? '') . ' ' . ($hardware->modelo->Nombre ?? '')) ?: null, 'bi-box'],
                ['Serial cargador', $hardware->Hw_Serial_Cargador, 'bi-plug'],
                ['Procesador', ($hardware->procesador->Nombre ?? null) . (($hardware->procesador->Velocidad ?? null) ? ' · ' . $hardware->procesador->Velocidad : ''), 'bi-cpu'],
                ['RAM', $hardware->Hw_Ram, 'bi-memory'],
                ['Disco duro', $hardware->Hw_Disco_Duro, 'bi-device-hdd'],
                ['Placa', $hardware->Hw_Placa, 'bi-upc'],
                ['Estado', $hardware->Hw_Estado, 'bi-activity'],
            ] as [$label, $valor, $icono])
                <div class="flex items-center justify-between gap-4 py-3">
                    <dt class="flex items-center gap-2 text-sm text-slate-400"><i class="bi {{ $icono }} text-xs"></i>{{ $label }}</dt>
                    <dd class="text-right text-sm font-semibold text-slate-700">{{ $valor ?: '—' }}</dd>
                </div>
            @endforeach
            <div class="flex items-center justify-between gap-4 py-3">
                <dt class="flex items-center gap-2 text-sm text-slate-400"><i class="bi bi-ethernet text-xs"></i>MAC Eth / WiFi</dt>
                <dd class="text-right font-mono text-xs text-slate-600">{{ $hardware->MacEthernet ?: '—' }}<br>{{ $hardware->MacWireless ?: '—' }}</dd>
            </div>
        </dl>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white shadow-card">
        <header class="flex items-center gap-2 border-b border-slate-100 px-6 py-4">
            <i class="bi bi-cash-coin text-emerald-500"></i><h3 class="text-sm font-bold text-slate-700">Financiero y asignación</h3>
        </header>
        <dl class="divide-y divide-slate-50 px-6">
            @foreach([
                ['Usuario asignado', ($hardware->usuarioInv->UsuarioInvNombre ?? null) . (($hardware->usuarioInv->UsuarioInvArea ?? null) ? ' · ' . $hardware->usuarioInv->UsuarioInvArea : ''), 'bi-person-check'],
                ['Monitor', trim(($hardware->monitor->MonitorID ?? '') . ' ' . ($hardware->monitor->Monitor_Modelo ?? '')) ?: null, 'bi-display'],
                ['Compra', optional($hardware->Hw_FechaCompra)->format('d/m/Y') ? optional($hardware->Hw_FechaCompra)->format('d/m/Y') . ' · $' . number_format($hardware->Hw_ValorCompra) : null, 'bi-cart-check'],
                ['Garantía hasta', 'vencimiento', 'bi-shield-check'],
                ['Propietario', $hardware->propietario->Nombre ?? null, 'bi-building'],
                ['Proveedor', $hardware->proveedor->Nombre ?? null, 'bi-truck'],
                ['Leasing', ($hardware->leasing->Entidad ?? null) ? ($hardware->leasing->Entidad . ' · ' . $hardware->leasing->Contrato) : null, 'bi-file-earmark-text'],
                ['Ubicación', $hardware->ubicacion->UbicacionNombre ?? null, 'bi-geo-alt'],
                ['Seguro', $hardware->seguro->Seguro_Nombre ?? null, 'bi-umbrella'],
            ] as [$label, $valor, $icono])
                <div class="flex items-center justify-between gap-4 py-3">
                    <dt class="flex items-center gap-2 text-sm text-slate-400"><i class="bi {{ $icono }} text-xs"></i>{{ $label }}</dt>
                    <dd class="text-right text-sm font-semibold text-slate-700">
                        @if($valor === 'vencimiento')
                            <x-vencimiento :fecha="$hardware->Hw_FechaGarantiaFin" dias />
                        @else
                            {{ $valor ?: '—' }}
                        @endif
                    </dd>
                </div>
            @endforeach
        </dl>
    </div>
</div>

{{-- Software --}}
<div class="mt-5 grid gap-5 lg:grid-cols-2">
    <div class="rounded-3xl border border-slate-200 bg-white shadow-card">
        <header class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <span class="flex items-center gap-2"><i class="bi bi-key-fill text-emerald-500"></i><h3 class="text-sm font-bold text-slate-700">Software licenciado</h3></span>
            <a href="{{ route('softlic.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">Gestionar →</a>
        </header>
        <ul class="divide-y divide-slate-50 px-6">
            @forelse($hardware->softwaresLicenciados as $sw)
                <li class="flex items-center justify-between gap-3 py-3">
                    <span class="flex min-w-0 items-center gap-2">
                        <code class="rounded-lg bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">{{ $sw->Software_Clave ?? '(sin clave)' }}</code>
                    </span>
                    <small class="shrink-0 text-xs text-slate-400">v{{ $sw->Software_Version ?? '?' }} · {{ $sw->tipoSoftware->Soft_Tipo_Nombre ?? '—' }}</small>
                </li>
            @empty
                <li class="py-6 text-center text-sm text-slate-400">Sin software licenciado asignado.</li>
            @endforelse
        </ul>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white shadow-card">
        <header class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <span class="flex items-center gap-2"><i class="bi bi-app-indicator text-sky-500"></i><h3 class="text-sm font-bold text-slate-700">Software no licenciado</h3></span>
            <a href="{{ route('softnl.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">Gestionar →</a>
        </header>
        <ul class="divide-y divide-slate-50 px-6">
            @forelse($hardware->softwaresNoLicenciados as $sw)
                <li class="flex items-center justify-between gap-3 py-3">
                    <span class="truncate text-sm font-medium text-slate-600">{{ $sw->Softwarenl_Nombre }}</span>
                    <small class="text-xs text-slate-400">v{{ $sw->Softwarenl_Version ?? '?' }}</small>
                </li>
            @empty
                <li class="py-6 text-center text-sm text-slate-400">Sin software libre asignado.</li>
            @endforelse
        </ul>
    </div>
</div>

{{-- Dispositivos, observaciones e historial --}}
<div class="mt-5 grid gap-5 lg:grid-cols-3">
    <div class="rounded-3xl border border-slate-200 bg-white shadow-card">
        <header class="border-b border-slate-100 px-6 py-4"><h3 class="text-sm font-bold text-slate-700">Dispositivos asociados</h3></header>
        <form method="POST" action="{{ route('hardware.devices.sync', $hardware->Hw_Serial) }}" class="space-y-3 px-6 py-4">
            @csrf @method('PUT')
            @foreach($dispositivosDisponibles as $dispositivo)
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="dispositivos[]" value="{{ $dispositivo->Dispositivo_Codigo }}" @checked($hardware->dispositivos->contains('Dispositivo_Codigo', $dispositivo->Dispositivo_Codigo))>
                    {{ $dispositivo->Dispositivos_Nombre }}
                </label>
            @endforeach
            <button class="rounded-xl bg-brand-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-brand-700">Guardar dispositivos</button>
        </form>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white shadow-card">
        <header class="border-b border-slate-100 px-6 py-4"><h3 class="text-sm font-bold text-slate-700">Nueva observación</h3></header>
        <form method="POST" action="{{ route('hardware.observations.store', $hardware->Hw_Serial) }}" class="space-y-3 px-6 py-4">
            @csrf
            <textarea name="Observacion_Detalle" required maxlength="300" rows="3" placeholder="Detalle de la observación" class="w-full rounded-xl border border-slate-200 p-3 text-sm"></textarea>
            <input type="date" name="Observacion_Fecha" value="{{ now()->format('Y-m-d') }}" required class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
            <button class="rounded-xl bg-brand-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-brand-700">Registrar observación</button>
        </form>
        <ul class="divide-y divide-slate-50 px-6">
            @foreach($hardware->observaciones as $observacion)
                <li class="flex items-start justify-between gap-3 py-3 text-sm text-slate-600">
                    <span>{{ $observacion->Observacion_Detalle }}<small class="block text-xs text-slate-400">{{ optional($observacion->Observacion_Fecha)->format('d/m/Y') }}</small></span>
                    <form method="POST" action="{{ route('hardware.observations.destroy', [$hardware->Hw_Serial, $observacion->Observacion_Id]) }}">@csrf @method('DELETE')<button title="Eliminar" class="text-red-500"><i class="bi bi-trash3"></i></button></form>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white shadow-card">
        <header class="border-b border-slate-100 px-6 py-4"><h3 class="text-sm font-bold text-slate-700">Cambio de usuario</h3></header>
        <form method="POST" action="{{ route('hardware.user-changes.store', $hardware->Hw_Serial) }}" class="space-y-3 px-6 py-4">
            @csrf
            <input name="UsuarioActual" required maxlength="30" placeholder="Usuario actual" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
            <input type="date" name="FechaCambio" value="{{ now()->format('Y-m-d') }}" required class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
            <button class="rounded-xl bg-brand-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-brand-700">Registrar cambio</button>
        </form>
        <ul class="divide-y divide-slate-50 px-6">
            @foreach($hardware->cambiosDeUsuario as $cambio)
                <li class="py-3 text-sm text-slate-600">{{ $cambio->UsuarioActual }}<small class="block text-xs text-slate-400">{{ optional($cambio->FechaCambio)->format('d/m/Y') }}</small></li>
            @endforeach
        </ul>
    </div>
</div>

{{-- Actas y documentos adjuntos --}}
<div class="mt-5 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card">
    <header class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-4">
        <span class="flex items-center gap-2">
            <i class="bi bi-file-earmark-text text-emerald-600 text-lg"></i>
            <h3 class="text-sm font-bold text-slate-700">Actas y documentos adjuntos ({{ $hardware->actas->count() }})</h3>
        </span>
        <div class="flex items-center gap-2">
            <a href="{{ route('hardware.delivery-act', $hardware->Hw_Serial) }}?pdf=1" target="_blank" data-no-loader class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 hover:text-slate-900">
                <i class="bi bi-filetype-pdf text-red-500"></i> Descargar acta en PDF
            </a>
            <a href="#adjuntar-acta" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 py-1.5 text-xs font-bold text-white shadow-sm shadow-emerald-600/30 transition hover:bg-emerald-700">
                <i class="bi bi-paperclip"></i> Adjuntar acta
            </a>
        </div>
    </header>
    <div id="adjuntar-acta" class="border-b border-slate-100 bg-emerald-50/40 p-6">
        <form method="POST" action="{{ route('hardware.actas.store', $hardware->Hw_Serial) }}" enctype="multipart/form-data" class="grid gap-4 md:grid-cols-[1.2fr_1.2fr_auto] md:items-end">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase tracking-wide text-slate-500">Título o descripción</label>
                <input type="text" name="titulo" maxlength="150" placeholder="Ej: Acta de entrega firmada" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wide text-slate-500">Archivo</label>
                <div class="mt-1 flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-2 shadow-sm">
                    <label for="acta-archivo" class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-emerald-600 px-3 py-2 text-xs font-bold text-white shadow-sm shadow-emerald-600/30 transition hover:bg-emerald-700">
                        <i class="bi bi-upload"></i> Seleccionar archivo
                    </label>
                    <span id="acta-archivo-nombre" class="truncate text-xs text-slate-500">Ningún archivo seleccionado</span>
                    <input id="acta-archivo" type="file" name="archivo" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.webp" required class="sr-only">
                </div>
            </div>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-emerald-600/30 transition hover:bg-emerald-700">
                <i class="bi bi-paperclip"></i> Guardar acta
            </button>
        </form>
    </div>
    <div class="p-6">
        @if($hardware->actas->isNotEmpty())
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($hardware->actas as $acta)
                    <div class="group relative flex flex-col justify-between rounded-2xl border border-slate-200 bg-slate-50/50 p-4 transition hover:border-emerald-300 hover:bg-emerald-50/30">
                        <div class="flex items-start gap-3">
                            <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-white text-xl shadow-sm ring-1 ring-slate-200/70">
                                <i class="bi {{ $acta->icono }}"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-slate-800" title="{{ $acta->titulo }}">{{ $acta->titulo }}</p>
                                <p class="truncate font-mono text-xs text-slate-400" title="{{ $acta->nombre_original }}">{{ $acta->nombre_original }}</p>
                                <div class="mt-1 flex items-center gap-2 text-[11px] text-slate-400">
                                    <span>{{ $acta->tamanio_formateado }}</span>
                                    <span>•</span>
                                    <span>{{ $acta->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between border-t border-slate-200/60 pt-3">
                            <a href="{{ route('hardware.actas.download', [$hardware->Hw_Serial, $acta->id]) }}?inline=1" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg bg-white px-2.5 py-1 text-xs font-semibold text-brand-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-brand-50">
                                <i class="bi bi-eye"></i> Ver / Descargar
                            </a>
                            <form method="POST" action="{{ route('hardware.actas.destroy', [$hardware->Hw_Serial, $acta->id]) }}" data-confirm="¿Estás seguro de eliminar esta acta o archivo adjunto?">
                                @csrf @method('DELETE')
                                <button type="submit" class="grid size-7 place-items-center rounded-lg text-slate-400 transition hover:bg-red-50 hover:text-red-600" title="Eliminar archivo">
                                    <i class="bi bi-trash3 text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-8 text-center">
                <span class="grid size-12 place-items-center rounded-2xl bg-emerald-50 text-emerald-600">
                    <i class="bi bi-file-earmark-arrow-up text-2xl"></i>
                </span>
                <p class="mt-3 text-sm font-semibold text-slate-700">Sin actas ni documentos adjuntos</p>
                <p class="mt-1 max-w-sm text-xs text-slate-400">Puedes adjuntar el acta de entrega firmada o documentos relacionados aquí mismo.</p>
                <a href="#adjuntar-acta" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-sm shadow-emerald-600/30 transition hover:bg-emerald-700">
                    <i class="bi bi-paperclip"></i> Adjuntar acta de entrega aquí
                </a>
            </div>
        @endif
    </div>
</div>

{{-- Mantenimientos --}}
<div class="mt-5 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card">
    <header class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
        <span class="flex items-center gap-2"><i class="bi bi-tools text-amber-500"></i><h3 class="text-sm font-bold text-slate-700">Mantenimientos ({{ $hardware->mantenimientos->count() }})</h3></span>
        <a href="{{ route('mant.create', $hardware->Hw_Serial) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm shadow-brand-600/30 transition hover:bg-brand-700"><i class="bi bi-plus-lg"></i>Registrar</a>
    </header>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[640px] text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] uppercase tracking-wider text-slate-400">
                    <th class="px-6 py-3 font-semibold">Fecha</th>
                    <th class="px-6 py-3 font-semibold">Realiza</th>
                    <th class="px-6 py-3 font-semibold">Recibe</th>
                    <th class="px-6 py-3 font-semibold">Tareas</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($hardware->mantenimientos as $m)
                    @php($hechos = collect(['RFE','ST','STYM','LP','LTYM','LM','O'])->filter(fn ($k) => $m->{"mantenimiento_{$k}_C"}))
                    <tr class="transition hover:bg-brand-50/40">
                        <td class="px-6 py-3.5 font-semibold text-slate-600">{{ optional($m->mantenimiento_fecha)->format('d/m/Y') }}</td>
                        <td class="px-6 py-3.5 text-slate-500">{{ $m->mantenimiento_Realiza ?? '—' }}</td>
                        <td class="px-6 py-3.5 text-slate-500">{{ $m->mantenimiento_Recibe ?? '—' }}</td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex h-6 w-12 items-center justify-center rounded-lg {{ $hechos->isNotEmpty() ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }} text-xs font-bold">{{ $hechos->count() }}/7</span>
                        </td>
                        <td class="px-6 py-3.5 text-right">
                            <a href="{{ route('mant.show', $m->mantenimiento_Id) }}" class="inline-grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-brand-50 hover:text-brand-600"><i class="bi bi-chevron-right"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-10 text-center text-sm text-slate-400">Sin mantenimientos registrados para este equipo.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
