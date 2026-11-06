<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Mantenimiento #{{ $mantenimiento->mantenimiento_Id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        h2 { font-size: 13px; margin: 18px 0 8px; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; }
        .meta { color: #64748b; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { text-align: left; padding: 6px 8px; border: 1px solid #e2e8f0; vertical-align: top; }
        th { background: #f1f5f9; width: 38%; }
        .ok { color: #059669; font-weight: bold; }
        .pend { color: #94a3b8; }
        .fotos img { max-width: 240px; max-height: 180px; margin: 6px 8px 6px 0; border: 1px solid #e2e8f0; }
        .footer { margin-top: 24px; font-size: 10px; color: #94a3b8; }
    </style>
</head>
<body>
    <h1>Informe de mantenimiento #{{ $mantenimiento->mantenimiento_Id }}</h1>
    <p class="meta">INDUMA · Activos tecnológicos · Generado {{ now()->format('d/m/Y H:i') }}</p>

    <h2>Equipo</h2>
    <table>
        <tr><th>Serial</th><td>{{ $mantenimiento->Hw_Serial ?? '—' }}</td></tr>
        <tr><th>Nombre</th><td>{{ $mantenimiento->hardware->Hw_Nombre ?? '—' }}</td></tr>
        <tr><th>Modelo</th><td>{{ trim(($mantenimiento->hardware?->modelo?->marca?->Nombre ?? '').' '.($mantenimiento->hardware?->modelo?->Nombre ?? '')) ?: '—' }}</td></tr>
        <tr><th>Usuario asignado</th><td>{{ $mantenimiento->hardware?->usuarioInv?->UsuarioInvNombre ?? '—' }}</td></tr>
    </table>

    <h2>Datos del servicio</h2>
    <table>
        <tr><th>Fecha</th><td>{{ optional($mantenimiento->mantenimiento_fecha)->format('d/m/Y') ?? '—' }}</td></tr>
        <tr><th>Realiza</th><td>{{ $mantenimiento->mantenimiento_Realiza ?? '—' }}</td></tr>
        <tr><th>Recibe</th><td>{{ $mantenimiento->mantenimiento_Recibe ?? '—' }}</td></tr>
        <tr><th>Observaciones</th><td>{{ $mantenimiento->mantenimiento_Observaciones ?: 'Sin observaciones.' }}</td></tr>
    </table>

    <h2>Checklist</h2>
    <table>
        @foreach($checks as $key => $label)
            <tr>
                <th>{{ $label }}</th>
                <td>
                    @if($mantenimiento->{"mantenimiento_{$key}_C"})
                        <span class="ok">Hecho</span>
                    @else
                        <span class="pend">Pendiente</span>
                    @endif
                    @if($mantenimiento->{"mantenimiento_{$key}_O"})
                        <div>{{ $mantenimiento->{"mantenimiento_{$key}_O"} }}</div>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>

    <h2>Foto gráfico</h2>
    @if($mantenimiento->fotos->isEmpty())
        <p>Sin fotos adjuntas.</p>
    @else
        <div class="fotos">
            @foreach($mantenimiento->fotos as $foto)
                @php($uri = $foto->asBase64DataUri())
                @if($uri)
                    <img src="{{ $uri }}" alt="{{ $foto->nombre_original }}">
                @endif
            @endforeach
        </div>
    @endif

    <p class="footer">Documento generado desde el sistema de inventarios INDUMA.</p>
</body>
</html>
