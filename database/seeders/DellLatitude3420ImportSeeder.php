<?php

namespace Database\Seeders;

use App\Models\Hardware;
use App\Models\Leasing;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\Observaciones;
use App\Models\Procesador;
use App\Models\Propietario;
use App\Models\Proveedor;
use App\Models\Seguro;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\UsuarioInv;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DellLatitude3420ImportSeeder extends Seeder
{
    private array $usedEmails = [];

    private array $usedUserIds = [];

    public function run(): void
    {
        $path = database_path('data/dell_latitude_3420_import.json');
        if (! is_file($path)) {
            $this->command?->error("No se encontró el fixture: {$path}");

            return;
        }

        $rows = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($rows) || $rows === []) {
            $this->command?->warn('El fixture de importación está vacío.');

            return;
        }

        $this->ensureCatalogs();

        $tipoPortatil = Tipo::where('Nombre', 'Portátil')->value('TipoID');
        $propietarioId = Propietario::query()->value('PropietarioID');
        $proveedorId = Proveedor::query()->value('ProveedorID') ?? 'PROV001';
        $seguroId = Seguro::query()->value('SeguroID');

        $leasings = $this->ensureLeasings();
        $marcaDellId = Marca::where('Nombre', 'Dell')->value('MarcaID');

        $created = 0;
        $updated = 0;
        $observations = 0;

        foreach ($rows as $row) {
            $serial = trim((string) ($row['Hw_Serial'] ?? ''));
            if ($serial === '') {
                continue;
            }

            [$modeloNombre, $marcaId] = $this->resolveModelo(
                (string) ($row['modelo'] ?? 'Latitude 3420'),
                $marcaDellId
            );
            $modeloId = Modelo::firstOrCreate(
                ['Nombre' => $modeloNombre, 'MarcaID' => $marcaId],
                ['Nombre' => $modeloNombre, 'MarcaID' => $marcaId]
            )->ModeloID;

            $procesadorId = null;
            if (! empty($row['procesador'])) {
                $procNombre = Str::limit((string) $row['procesador'], 50, '');
                $procesadorId = Procesador::firstOrCreate(
                    ['Nombre' => $procNombre],
                    ['Nombre' => $procNombre, 'Velocidad' => null]
                )->ProcesadorID;
            }

            $area = $this->normalizeArea($row['area'] ?? null);
            $usuarioId = $this->ensureUsuario(
                (string) ($row['UsuarioInvID'] ?? ('EQ-'.$serial)),
                (string) ($row['usuario_nombre'] ?? 'Sin asignar'),
                (string) ($row['UsuarioInvCorreo'] ?? ('eq.'.strtolower($serial).'@induma.com')),
                $area
            );

            $ubicacionId = $this->ensureUbicacion($area);
            $leasingKey = (string) ($row['leasing_key'] ?? 'leasing_general');
            $leasing = $leasings[$leasingKey] ?? $leasings['leasing_general'];

            $payload = [
                'Hw_Nombre' => Str::limit((string) ($row['Hw_Nombre'] ?? ('INDUMA-'.$serial)), 50, ''),
                'Hw_Serial_Cargador' => isset($row['Hw_Serial_Cargador'])
                    ? Str::limit((string) $row['Hw_Serial_Cargador'], 50, '')
                    : null,
                'ProcesadorID' => $procesadorId,
                'Hw_Ram' => isset($row['Hw_Ram']) ? Str::limit((string) $row['Hw_Ram'], 50, '') : null,
                'Hw_Disco_Duro' => isset($row['Hw_Disco_Duro']) ? Str::limit((string) $row['Hw_Disco_Duro'], 50, '') : null,
                'Hw_FechaCompra' => $this->nullableDate($row['Hw_FechaCompra'] ?? null),
                'Hw_FechaDevolucion' => $leasing->FechaVencimiento,
                'TipoID' => $tipoPortatil,
                'ModeloID' => $modeloId,
                'MonitorID' => null,
                'UsuarioInvID' => $usuarioId,
                'PropietarioID' => $propietarioId,
                'ProveedorID' => $proveedorId,
                'LeasingID' => $leasing->LeasingID,
                'UbicacionId' => $ubicacionId,
                'SeguroID' => $seguroId,
                'Revisado' => (bool) ($row['Revisado'] ?? false),
                'Activo' => array_key_exists('Activo', $row) ? (bool) $row['Activo'] : true,
                'Hw_Estado' => Str::limit((string) ($row['Hw_Estado'] ?? 'Bueno'), 50, ''),
            ];

            $existing = Hardware::find($serial);
            if ($existing) {
                $existing->fill($payload)->save();
                $updated++;
            } else {
                Hardware::create(array_merge(['Hw_Serial' => $serial], $payload));
                $created++;
            }

            $obs = trim((string) ($row['observacion'] ?? ''));
            if ($obs !== '') {
                $detalle = Str::limit($obs, 300, '');
                $already = Observaciones::query()
                    ->where('Hw_Serial', $serial)
                    ->where('Observacion_Detalle', $detalle)
                    ->exists();
                if (! $already) {
                    Observaciones::create([
                        'Observacion_Detalle' => $detalle,
                        'Observacion_Fecha' => $this->nullableDate($row['Hw_FechaCompra'] ?? null) ?? now()->toDateString(),
                        'Hw_Serial' => $serial,
                    ]);
                    $observations++;
                }
            }
        }

        $this->command?->info("Import Dell Latitude 3420: creados={$created}, actualizados={$updated}, observaciones={$observations}, total_filas=".count($rows));
    }

    private function ensureCatalogs(): void
    {
        Tipo::firstOrCreate(['Nombre' => 'Portátil']);
        Marca::firstOrCreate(['Nombre' => 'Dell']);
        Propietario::firstOrCreate(['Nombre' => 'INDUMA']);
        Proveedor::firstOrCreate(
            ['ProveedorID' => 'PROV001'],
            ['ProveedorID' => 'PROV001', 'Nombre' => 'Distribuidor Tecnológico S.A.S.']
        );
        Seguro::firstOrCreate(
            ['SeguroID' => 1],
            ['SeguroID' => 1, 'Seguro_Nombre' => 'Póliza todo riesgo']
        );
        Ubicacion::firstOrCreate(['UbicacionNombre' => 'Sede Principal']);
        Ubicacion::firstOrCreate(['UbicacionNombre' => 'Bodega TI']);
    }

    /**
     * @return array<string, Leasing>
     */
    private function ensureLeasings(): array
    {
        $defs = [
            'leasing1' => [
                'Entidad' => 'Leasing Dell L1',
                'Contrato' => 'CTR-DELL-L1',
                'FechaInicio' => '2021-01-01',
                'FechaVencimiento' => '2026-12-31',
            ],
            'leasing2' => [
                'Entidad' => 'Leasing Dell L2',
                'Contrato' => 'CTR-DELL-L2',
                'FechaInicio' => '2022-01-01',
                'FechaVencimiento' => '2027-12-31',
            ],
            'leasing_general' => [
                'Entidad' => 'Leasing Dell Gen',
                'Contrato' => 'CTR-DELL-GEN',
                'FechaInicio' => '2024-01-01',
                'FechaVencimiento' => '2027-12-31',
            ],
        ];

        $out = [];
        foreach ($defs as $key => $def) {
            $out[$key] = Leasing::firstOrCreate(
                ['Contrato' => $def['Contrato']],
                array_merge($def, [
                    'ValorTotal' => null,
                    'CanonArrendamiento' => null,
                ])
            );
        }

        return $out;
    }

    private function ensureUsuario(string $rawId, string $nombre, string $correo, ?string $area): string
    {
        $id = Str::upper(Str::limit(preg_replace('/[^A-Za-z0-9]/', '', $rawId) ?: 'USUARIO', 30, ''));
        $nombre = Str::limit(trim($nombre) !== '' ? trim($nombre) : 'Sin asignar', 50, '');
        $correo = $this->uniqueEmail($correo, $id);
        $area = $area ? Str::limit($area, 50, '') : null;

        if (isset($this->usedUserIds[$id])) {
            $existing = UsuarioInv::find($id);
            if ($existing && Str::lower((string) $existing->UsuarioInvNombre) !== Str::lower($nombre)) {
                $suffix = substr(md5($nombre.$id), 0, 4);
                $id = Str::limit(substr($id, 0, 26).$suffix, 30, '');
            }
        }
        $this->usedUserIds[$id] = true;

        UsuarioInv::updateOrCreate(
            ['UsuarioInvID' => $id],
            [
                'UsuarioInvNombre' => $nombre,
                'UsuarioInvCargo' => 'Colaborador',
                'UsuarioInvCorreo' => Str::limit($correo, 50, ''),
                'UsuarioInvArea' => $area,
            ]
        );

        return $id;
    }

    private function ensureUbicacion(?string $area): ?int
    {
        if ($area === null || trim($area) === '') {
            return Ubicacion::where('UbicacionNombre', 'Sede Principal')->value('UbicacionId');
        }

        $clean = preg_replace('/^\(CAJA\d+\)\s*/i', '', $area) ?? $area;
        $clean = trim(preg_replace('/\s+/', ' ', $clean) ?? $clean);
        $short = Str::limit($clean, 20, '');

        return Ubicacion::firstOrCreate(
            ['UbicacionNombre' => $short],
            ['UbicacionNombre' => $short]
        )->UbicacionId;
    }

    private function normalizeArea(mixed $area): ?string
    {
        if ($area === null) {
            return null;
        }
        $area = trim((string) $area);
        if ($area === '') {
            return null;
        }

        $map = [
            'abstecimiento' => 'Abastecimiento',
            'finnciera' => 'Financiera',
            'calidad' => 'Calidad',
            'comercial' => 'Comercial',
            'mercadeo' => 'Mercadeo',
            'produccion' => 'Produccion',
            'tpm' => 'TPM',
            'desarrollo e inovacion' => 'Desarrollo E innovacion',
            'suministro' => 'Suministro',
            't.m' => 'T.M',
            'taller' => 'Taller',
            'ti' => 'TI',
        ];

        $key = Str::lower($area);
        $key = preg_replace('/^\(caja\d+\)\s*/i', '', $key) ?? $key;
        $key = trim($key);

        if (isset($map[$key])) {
            $prefix = '';
            if (preg_match('/^(\(CAJA\d+\))\s*/i', $area, $m)) {
                $prefix = $m[1].' ';
            }

            return trim($prefix.$map[$key]);
        }

        return $area;
    }

    /**
     * @return array{0: string, 1: int}
     */
    private function resolveModelo(string $modelo, int $marcaDellId): array
    {
        $modelo = trim($modelo);
        $lower = Str::lower($modelo);

        if ($modelo === '' || Str::contains($lower, 'latitude')) {
            return ['Latitude 3420', $marcaDellId];
        }
        if (Str::contains($lower, 'inspiron')) {
            return ['Inspiron 14', $marcaDellId];
        }
        // Essential 15 es modelo (Acer), no marca.
        if (Str::contains($lower, 'essential') || Str::contains($lower, 'essencial')) {
            $marcaAcerId = (int) Marca::firstOrCreate(['Nombre' => 'Acer'])->MarcaID;

            return ['Essential 15', $marcaAcerId];
        }
        if (Str::contains($lower, 'pro max')) {
            return ['PRO MAX 14', $marcaDellId];
        }

        return [Str::limit($modelo, 50, ''), $marcaDellId];
    }

    private function uniqueEmail(string $correo, string $fallbackId): string
    {
        $correo = Str::lower(trim($correo));
        if ($correo === '' || ! str_contains($correo, '@')) {
            $correo = Str::lower($fallbackId).'@induma.com';
        }
        [$local, $domain] = array_pad(explode('@', $correo, 2), 2, 'induma.com');
        $domain = $domain !== '' ? $domain : 'induma.com';
        if (! Str::endsWith($domain, 'induma.com')) {
            $domain = 'induma.com';
        }
        $local = preg_replace('/[^a-z0-9._-]/', '', $local) ?: Str::lower($fallbackId);
        $candidate = $local.'@'.$domain;
        $n = 1;
        while (isset($this->usedEmails[$candidate]) || UsuarioInv::where('UsuarioInvCorreo', $candidate)->where('UsuarioInvID', '!=', $fallbackId)->exists()) {
            $n++;
            $candidate = Str::limit($local, 40, '').$n.'@'.$domain;
        }
        $this->usedEmails[$candidate] = true;

        return $candidate;
    }

    private function nullableDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $value = trim((string) $value);
        if (! preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
            return null;
        }

        return substr($value, 0, 10);
    }
}
