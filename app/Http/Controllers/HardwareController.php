<?php

namespace App\Http\Controllers;

use App\Models\Hardware;
use App\Models\HardwareActa;
use App\Models\Dispositivo;
use App\Models\Software_Licenciado;
use App\Models\Software_NoLicenciado;
use App\Models\Leasing;
use App\Models\Modelo;
use App\Models\Monitor;
use App\Models\Propietario;
use App\Models\Procesador;
use App\Models\Proveedor;
use App\Models\Seguro;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\UsuarioInv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class HardwareController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));

        $hardware = Hardware::with(['tipo', 'modelo.marca', 'usuarioInv', 'ubicacion'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('Hw_Serial', 'like', "%{$q}%")
                        ->orWhere('Hw_Nombre', 'like', "%{$q}%")
                        ->orWhereHas('usuarioInv', function ($user) use ($q) {
                            $user->where('UsuarioInvNombre', 'like', "%{$q}%")
                                ->orWhere('UsuarioInvArea', 'like', "%{$q}%")
                                ->orWhere('UsuarioInvCargo', 'like', "%{$q}%")
                                ->orWhere('UsuarioInvCorreo', 'like', "%{$q}%");
                        });
                });
            })
            ->orderBy('Hw_Serial')
            ->paginate(15)
            ->withQueryString();

        return view('hardware.index', compact('hardware', 'q'));
    }

    public function create()
    {
        return view('hardware.create', [
            'hardware' => new Hardware(),
            'tipos' => Tipo::orderBy('Nombre')->get(),
            'modelos' => Modelo::with('marca')->orderBy('Nombre')->get(),
            'procesadores' => Procesador::orderBy('Nombre')->get(),
            'monitores' => Monitor::orderBy('MonitorID')->get(),
            'usuarios' => UsuarioInv::orderBy('UsuarioInvNombre')->get(),
            'propietarios' => Propietario::orderBy('Nombre')->get(),
            'proveedores' => Proveedor::orderBy('Nombre')->get(),
            'leasings' => Leasing::orderBy('Entidad')->get(),
            'ubicaciones' => Ubicacion::orderBy('UbicacionNombre')->get(),
            'seguros' => Seguro::orderBy('SeguroID')->get(),
            'dispositivos' => Dispositivo::orderBy('Dispositivos_Nombre')->get(),
            'softwareLicenciado' => Software_Licenciado::orderBy('Software_Clave')->get(),
            'softwareNoLicenciado' => Software_NoLicenciado::orderBy('Softwarenl_Nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->merge(['ProcesadorID' => $this->saveManualProcessor($request)]);
        $request->merge(['UsuarioInvID' => $this->saveManualUser($request)]);
        $data = $this->validated($request);

        $hardware = Hardware::create($data);
        $hardware->dispositivos()->sync($request->input('dispositivos', []));
        $hardware->softwaresNoLicenciados()->sync($request->input('software_no_licenciado', []));
        if ($request->filled('observacion')) {
            $hardware->observaciones()->create([
                'Observacion_Detalle' => $request->input('observacion'),
                'Observacion_Fecha' => $request->input('observacion_fecha') ?: now()->toDateString(),
            ]);
        }
        foreach ($request->input('software_licenciado', []) as $softwareId) {
            $software = Software_Licenciado::find($softwareId);
            if ($software && ($software->Software_Cantidad === null || $software->hardwares()->count() < (int) $software->Software_Cantidad)) {
                $hardware->softwaresLicenciados()->syncWithoutDetaching([$softwareId]);
            }
        }

        $this->handleActaUpload($request, $hardware, 'acta_archivo', 'acta_titulo');

        return redirect()
            ->route('hardware.show', $data['Hw_Serial'])
            ->with('ok', 'Equipo creado correctamente.');
    }

    public function show(string $serial)
    {
        $hardware = Hardware::with([
            'tipo', 'procesador', 'modelo.marca', 'monitor', 'usuarioInv',
            'propietario', 'proveedor', 'leasing', 'ubicacion', 'seguro',
            'softwaresLicenciados', 'softwaresNoLicenciados',
            'dispositivos', 'mantenimientos', 'observaciones', 'actas',
        ])->findOrFail($serial);

        return view('hardware.show', [
            'hardware' => $hardware,
            'dispositivosDisponibles' => Dispositivo::orderBy('Dispositivos_Nombre')->get(),
        ]);
    }

    public function deliveryAct(Request $request, string $serial)
    {
        $hardware = Hardware::with([
            'tipo', 'modelo.marca', 'procesador', 'monitor', 'usuarioInv',
            'ubicacion', 'dispositivos', 'softwaresLicenciados', 'softwaresNoLicenciados',
            'actas',
        ])->findOrFail($serial);

        $latestActa = $hardware->actas->first();
        if ($request->boolean('pdf') && $latestActa) {
            return $this->downloadActa($request, $serial, $latestActa->id);
        }

        return view('hardware.delivery-act', compact('hardware'));
    }

    public function edit(string $serial)
    {
        $hardware = Hardware::with('actas')->findOrFail($serial);

        return view('hardware.edit', [
            'hardware' => $hardware,
            'tipos' => Tipo::orderBy('Nombre')->get(),
            'modelos' => Modelo::with('marca')->orderBy('Nombre')->get(),
            'procesadores' => Procesador::orderBy('Nombre')->get(),
            'monitores' => Monitor::orderBy('MonitorID')->get(),
            'usuarios' => UsuarioInv::orderBy('UsuarioInvNombre')->get(),
            'propietarios' => Propietario::orderBy('Nombre')->get(),
            'proveedores' => Proveedor::orderBy('Nombre')->get(),
            'leasings' => Leasing::orderBy('Entidad')->get(),
            'ubicaciones' => Ubicacion::orderBy('UbicacionNombre')->get(),
            'seguros' => Seguro::orderBy('SeguroID')->get(),
            'dispositivos' => Dispositivo::orderBy('Dispositivos_Nombre')->get(),
            'softwareLicenciado' => Software_Licenciado::orderBy('Software_Clave')->get(),
            'softwareNoLicenciado' => Software_NoLicenciado::orderBy('Softwarenl_Nombre')->get(),
        ]);
    }

    public function update(Request $request, string $serial)
    {
        $hardware = Hardware::findOrFail($serial);
        $request->merge(['ProcesadorID' => $this->saveManualProcessor($request)]);
        $request->merge(['UsuarioInvID' => $this->saveManualUser($request)]);
        $data = $this->validated($request, $serial);

        unset($data['Hw_Serial']);
        $hardware->update($data);
        $hardware->dispositivos()->sync($request->input('dispositivos', []));
        $hardware->softwaresNoLicenciados()->sync($request->input('software_no_licenciado', []));
        $hardware->softwaresLicenciados()->sync($request->input('software_licenciado', []));

        $this->handleActaUpload($request, $hardware, 'acta_archivo', 'acta_titulo');

        return redirect()
            ->route('hardware.show', $serial)
            ->with('ok', 'Equipo actualizado correctamente.');
    }

    public function destroy(string $serial)
    {
        if (!auth()->user()->esAdmin()) {
            abort(403, 'Solo un administrador puede dar de baja equipos.');
        }

        $hardware = Hardware::findOrFail($serial);
        $hardware->update(['Activo' => false]);

        return redirect()
            ->route('hardware.index')
            ->with('ok', "Equipo {$serial} dado de baja.");
    }

    public function restore(string $serial)
    {
        if (!auth()->user()->esAdmin()) {
            abort(403, 'Solo un administrador puede reactivar equipos.');
        }

        $hardware = Hardware::findOrFail($serial);
        $hardware->update(['Activo' => true]);

        return redirect()
            ->route('hardware.show', $serial)
            ->with('ok', "Equipo {$serial} reactivado.");
    }

    public function storeObservation(Request $request, string $serial)
    {
        $hardware = Hardware::findOrFail($serial);
        $data = $request->validate([
            'Observacion_Detalle' => ['required', 'string', 'max:300'],
            'Observacion_Fecha' => ['required', 'date'],
        ]);

        $hardware->observaciones()->create($data);

        return back()->with('ok', 'Observación registrada.');
    }

    public function destroyObservation(string $serial, int $id)
    {
        Hardware::findOrFail($serial)->observaciones()
            ->whereKey($id)
            ->firstOrFail()
            ->delete();

        return back()->with('ok', 'Observación eliminada.');
    }

    public function syncDevices(Request $request, string $serial)
    {
        $hardware = Hardware::findOrFail($serial);
        $data = $request->validate([
            'dispositivos' => ['nullable', 'array'],
            'dispositivos.*' => ['integer', 'exists:Dispositivo,Dispositivo_Codigo'],
        ]);

        $hardware->dispositivos()->sync($data['dispositivos'] ?? []);

        return back()->with('ok', 'Dispositivos actualizados.');
    }

    public function storeUserChange(Request $request, string $serial)
    {
        $hardware = Hardware::findOrFail($serial);
        $data = $request->validate([
            'UsuarioActual' => ['required', 'string', 'max:30'],
            'FechaCambio' => ['required', 'date'],
        ]);

        $usuarioNombre = trim($data['UsuarioActual']);
        $usuario = UsuarioInv::query()
            ->whereRaw('LOWER(UsuarioInvNombre) = ?', [mb_strtolower($usuarioNombre)])
            ->first();

        if (!$usuario) {
            $usuarioId = strtoupper(Str::slug($usuarioNombre, '')) ?: 'USR-' . Str::random(4);
            $usuario = UsuarioInv::create([
                'UsuarioInvID' => $usuarioId,
                'UsuarioInvNombre' => $usuarioNombre,
                'UsuarioInvArea' => 'Sin asignar',
                'UsuarioInvCorreo' => $usuarioId . '@induma.local',
            ]);
        }

        $hardware->update(['UsuarioInvID' => $usuario->UsuarioInvID]);
        $hardware->cambiosDeUsuario()->create([
            'UsuarioActual' => $usuarioNombre,
            'FechaCambio' => $data['FechaCambio'],
        ]);

        return back()->with('ok', 'Cambio de usuario registrado.');
    }

    public function storeActa(Request $request, string $serial)
    {
        $hardware = Hardware::findOrFail($serial);

        $request->validate([
            'archivo' => ['required', 'file', 'max:25600'], // hasta 25MB
            'titulo' => ['nullable', 'string', 'max:150'],
        ]);

        $file = $request->file('archivo');
        $originalName = $file->getClientOriginalName();
        $mime = $file->getClientMimeType() ?: 'application/octet-stream';
        $size = $file->getSize();
        $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $relativeDir = "actas/{$serial}";
        
        $destinationPath = storage_path("app/public/{$relativeDir}");
        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }
        $file->move($destinationPath, $filename);
        $path = "{$relativeDir}/{$filename}";

        $hardware->actas()->create([
            'nombre_original' => $originalName,
            'archivo_path' => $path,
            'titulo' => $request->filled('titulo') ? $request->input('titulo') : pathinfo($originalName, PATHINFO_FILENAME),
            'mime_type' => $mime,
            'tamanio' => $size,
        ]);

        return back()->with('ok', 'Acta o documento adjuntado correctamente.');
    }

    public function downloadActa(Request $request, string $serial, int $id)
    {
        $hardware = Hardware::findOrFail($serial);
        $acta = $hardware->actas()->findOrFail($id);

        $filePath = storage_path("app/public/{$acta->archivo_path}");
        if (!file_exists($filePath)) {
            $filePath = storage_path("app/{$acta->archivo_path}");
        }

        if (!file_exists($filePath)) {
            abort(404, 'El archivo solicitado no se encuentra en el servidor.');
        }

        if ($request->boolean('inline') && in_array($acta->mime_type, ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])) {
            return response()->file($filePath, [
                'Content-Type' => $acta->mime_type ?: 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="' . $acta->nombre_original . '"',
            ]);
        }

        return response()->download($filePath, $acta->nombre_original, [
            'Content-Type' => $acta->mime_type ?: 'application/octet-stream',
        ]);
    }

    public function destroyActa(string $serial, int $id)
    {
        $hardware = Hardware::findOrFail($serial);
        $acta = $hardware->actas()->findOrFail($id);

        $filePath = storage_path("app/public/{$acta->archivo_path}");
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
        $legacyPath = storage_path("app/{$acta->archivo_path}");
        if (file_exists($legacyPath)) {
            @unlink($legacyPath);
        }

        $acta->delete();

        return back()->with('ok', 'Acta eliminada correctamente.');
    }

    private function validated(Request $request, ?string $ignoreSerial = null): array
    {
        $serialUnique = Rule::unique('Hardware', 'Hw_Serial');
        if ($ignoreSerial !== null) {
            $serialUnique->ignore($ignoreSerial, 'Hw_Serial');
        }

        $data = $request->validate([
            'Hw_Serial' => ['required', 'string', 'max:50', $serialUnique],
            'Hw_Serial_Cargador' => ['nullable', 'string', 'max:50'],
            'Hw_Nombre' => ['required', 'string', 'max:50'],
            'ProcesadorID' => ['required', 'integer', 'exists:Procesador,ProcesadorID'],
            'Hw_Ram' => ['nullable', 'string', 'max:50'],
            'Hw_Disco_Duro' => ['nullable', 'string', 'max:50'],
            'Hw_FechaCompra' => ['nullable', 'date'],
            'Hw_ValorCompra' => ['nullable', 'numeric', 'min:0'],
            'Hw_FechaGarantiaFin' => ['nullable', 'date'],
            'TipoID' => ['required', 'integer', 'exists:Tipo,TipoID'],
            'ModeloID' => ['required', 'integer', 'exists:Modelo,ModeloID'],
            'MonitorID' => ['nullable', 'string', 'exists:Monitor,MonitorID'],
            'UsuarioInvID' => ['required', 'string', 'exists:UsuarioInv,UsuarioInvID'],
            'PropietarioID' => ['nullable', 'integer', 'exists:Propietario,PropietarioID'],
            'ProveedorID' => ['nullable', 'string', 'exists:Proveedor,ProveedorID'],
            'LeasingID' => ['nullable', 'integer', 'exists:Leasing,LeasingID'],
            'UbicacionId' => ['nullable', 'integer', 'exists:Ubicacion,UbicacionId'],
            'SeguroID' => ['nullable', 'integer', 'exists:Seguro,SeguroID'],
            'Revisado' => ['nullable', 'boolean'],
            'Activo' => ['nullable', 'boolean'],
            'Hw_FehaRenovacion' => ['nullable', 'date'],
            'Hw_Estado' => ['nullable', 'string', 'max:50'],
            'MacEthernet' => ['nullable', 'string', 'max:50', Rule::unique('Hardware', 'MacEthernet')->ignore($ignoreSerial, 'Hw_Serial')],
            'MacWireless' => ['nullable', 'string', 'max:50', Rule::unique('Hardware', 'MacWireless')->ignore($ignoreSerial, 'Hw_Serial')],
        ]);

        foreach (['Revisado', 'Activo'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }

        $data['Hw_FechaCompra'] ??= now()->toDateString();
        $data['Hw_ValorCompra'] ??= 0;
        $data['Hw_FechaGarantiaFin'] ??= now()->addYear()->toDateString();
        $data['MonitorID'] ??= Monitor::query()->value('MonitorID');
        $data['PropietarioID'] ??= Propietario::query()->value('PropietarioID');
        $data['ProveedorID'] ??= Proveedor::query()->value('ProveedorID');
        $data['LeasingID'] ??= Leasing::query()->value('LeasingID');

        return $data;
    }

    private function saveManualUser(Request $request): string
    {
        $user = $request->validate([
            'usuario_id' => ['required', 'string', 'max:30'],
            'usuario_nombre' => ['required', 'string', 'max:50'],
            'usuario_cargo' => ['nullable', 'string', 'max:50'],
            'usuario_area' => ['required', 'string', 'max:50'],
            'usuario_correo' => ['required', 'email', 'max:50', Rule::unique('UsuarioInv', 'UsuarioInvCorreo')->ignore($request->input('usuario_id'), 'UsuarioInvID')],
        ]);

        UsuarioInv::updateOrCreate(
            ['UsuarioInvID' => $user['usuario_id']],
            [
                'UsuarioInvNombre' => $user['usuario_nombre'],
                'UsuarioInvCargo' => $user['usuario_cargo'] ?? null,
                'UsuarioInvArea' => $user['usuario_area'],
                'UsuarioInvCorreo' => $user['usuario_correo'],
            ]
        );

        return $user['usuario_id'];
    }

    private function saveManualProcessor(Request $request): int
    {
        $processor = $request->validate([
            'procesador_nombre' => ['required', 'string', 'max:50'],
            'procesador_velocidad' => ['nullable', 'string', 'max:10'],
        ]);

        return Procesador::firstOrCreate([
            'Nombre' => $processor['procesador_nombre'],
            'Velocidad' => $processor['procesador_velocidad'] ?? null,
        ])->ProcesadorID;
    }

    private function handleActaUpload(Request $request, Hardware $hardware, string $fileInput = 'acta_archivo', string $titleInput = 'acta_titulo'): ?HardwareActa
    {
        if (!$request->hasFile($fileInput)) {
            return null;
        }

        $file = $request->file($fileInput);
        $originalName = $file->getClientOriginalName();
        $mime = $file->getClientMimeType() ?: 'application/octet-stream';
        $size = $file->getSize();
        $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $relativeDir = "actas/{$hardware->Hw_Serial}";
        
        $destinationPath = storage_path("app/public/{$relativeDir}");
        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }
        $file->move($destinationPath, $filename);
        $path = "{$relativeDir}/{$filename}";

        return $hardware->actas()->create([
            'nombre_original' => $originalName,
            'archivo_path' => $path,
            'titulo' => $request->filled($titleInput) ? $request->input($titleInput) : pathinfo($originalName, PATHINFO_FILENAME),
            'mime_type' => $mime,
            'tamanio' => $size,
        ]);
    }
}
