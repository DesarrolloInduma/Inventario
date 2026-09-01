<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Acta de entrega {{ $hardware->Hw_Serial }}</title>
    <style>
        body { color: #172033; font-family: Arial, sans-serif; margin: 36px auto; max-width: 820px; }
        h1 { color: #172554; font-size: 24px; margin-bottom: 4px; }
        h2 { border-bottom: 2px solid #dbe3f0; color: #1e3a8a; font-size: 15px; margin: 26px 0 10px; padding-bottom: 7px; }
        p { font-size: 13px; line-height: 1.55; }
        .meta { color: #64748b; font-size: 12px; }
        table { border-collapse: collapse; margin-top: 10px; width: 100%; }
        th, td { border: 1px solid #dbe3f0; font-size: 12px; padding: 9px; text-align: left; }
        th { background: #eff4fb; color: #334155; width: 28%; }
        .signatures { display: flex; gap: 60px; margin-top: 90px; }
        .signature { border-top: 1px solid #172033; flex: 1; padding-top: 8px; }
        .no-print-bar { background: #0f172a; color: white; padding: 12px 20px; border-radius: 12px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .btn { background: #059669; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn:hover { background: #047857; }
        .btn-secondary { background: #334155; }
        .btn-secondary:hover { background: #475569; }
        @media print {
            body { margin: 20px; }
            .no-print, .no-print-bar { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print-bar no-print">
        <span style="font-size: 13px; font-weight: 600;">Acta de entrega - {{ $hardware->Hw_Serial }}</span>
        <div style="display: flex; gap: 10px;">
            <button onclick="window.print()" class="btn">
                🖨️ Guardar como PDF / Imprimir
            </button>
            <button onclick="window.close(); history.back();" class="btn btn-secondary">
                Volver
            </button>
        </div>
    </div>

    <h1>Acta de entrega de equipo tecnológico</h1>
    <p class="meta">INDUMA · Fecha de entrega: {{ now()->format('d/m/Y') }}</p>
    <p>Por medio de la presente se hace entrega del equipo descrito a continuación, para uso y custodia del usuario asignado.</p>

    <h2>Información del usuario</h2>
    <table>
        <tr><th>Nombre</th><td>{{ $hardware->usuarioInv->UsuarioInvNombre ?? 'Sin asignar' }}</td></tr>
        <tr><th>Correo</th><td>{{ $hardware->usuarioInv->UsuarioInvCorreo ?? 'Sin registrar' }}</td></tr>
        <tr><th>Cargo / área</th><td>{{ $hardware->usuarioInv->UsuarioInvCargo ?? 'Sin registrar' }} / {{ $hardware->usuarioInv->UsuarioInvArea ?? 'Sin registrar' }}</td></tr>
        <tr><th>Ubicación</th><td>{{ $hardware->ubicacion->UbicacionNombre ?? 'Sin asignar' }}</td></tr>
    </table>

    <h2>Equipo entregado</h2>
    <table>
        <tr><th>Serial equipo</th><td>{{ $hardware->Hw_Serial }}</td></tr>
        <tr><th>Serial cargador</th><td>{{ $hardware->Hw_Serial_Cargador ?: 'Sin registrar' }}</td></tr>
        <tr><th>Nombre</th><td>{{ $hardware->Hw_Nombre }}</td></tr>
        <tr><th>Tipo</th><td>{{ $hardware->tipo->Nombre ?? 'Sin registrar' }}</td></tr>
        <tr><th>Marca / modelo</th><td>{{ $hardware->modelo->marca->Nombre ?? '' }} {{ $hardware->modelo->Nombre ?? '' }}</td></tr>
        <tr><th>Procesador</th><td>{{ $hardware->procesador->Nombre ?? 'Sin registrar' }}</td></tr>
        <tr><th>RAM / disco</th><td>{{ $hardware->Hw_Ram ?? 'Sin registrar' }} / {{ $hardware->Hw_Disco_Duro ?? 'Sin registrar' }}</td></tr>
        <tr><th>Monitor</th><td>{{ $hardware->monitor->MonitorID ?? 'Sin monitor' }} {{ $hardware->monitor->Monitor_Modelo ?? '' }}</td></tr>
    </table>

    <h2>Accesorios y software</h2>
    <table>
        <tr><th>Accesorios</th><td>{{ $hardware->dispositivos->pluck('Dispositivos_Nombre')->join(', ') ?: 'Sin accesorios registrados' }}</td></tr>
        <tr><th>Software licenciado</th><td>{{ $hardware->softwaresLicenciados->pluck('Software_Clave')->join(', ') ?: 'Ninguno' }}</td></tr>
        <tr><th>Software no licenciado</th><td>{{ $hardware->softwaresNoLicenciados->pluck('Softwarenl_Nombre')->join(', ') ?: 'Ninguno' }}</td></tr>
    </table>

    <p>El usuario declara recibir el equipo y sus accesorios en las condiciones descritas, y se compromete a hacer uso adecuado de los activos tecnológicos de INDUMA.</p>
    <div class="signatures"><div class="signature">Firma quien entrega</div><div class="signature">Firma quien recibe<br>{{ $hardware->usuarioInv->UsuarioInvNombre ?? '' }}</div></div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('pdf') || urlParams.has('print')) {
                setTimeout(() => {
                    window.print();
                }, 300);
            }
        });
    </script>
</body>
</html>