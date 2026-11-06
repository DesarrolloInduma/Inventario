<?php

namespace Database\Seeders;

use App\Models\Dispositivo;
use App\Models\Hardware;
use App\Models\Leasing;
use App\Models\Mantenimiento;
use App\Models\Modelo;
use App\Models\Marca;
use App\Models\Monitor;
use App\Models\Procesador;
use App\Models\Propietario;
use App\Models\Proveedor;
use App\Models\Seguro;
use App\Models\Tipo;
use App\Models\Tipo_Software;
use App\Models\Ubicacion;
use App\Models\User;
use App\Models\UsuarioInv;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@inventario.local',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'activo' => true,
        ]);

        User::create([
            'name' => 'Consulta',
            'email' => 'consulta@inventario.local',
            'password' => Hash::make('consulta123'),
            'role' => 'consulta',
            'activo' => true,
        ]);

        $tipos = ['Portátil', 'Escritorio', 'All in One', 'Servidor', 'Impresora', 'Tableta'];
        foreach ($tipos as $t) {
            Tipo::create(['Nombre' => $t]);
        }

        $marcas = ['Dell', 'HP', 'Lenovo', 'Asus', 'Acer', 'Apple'];
        foreach ($marcas as $m) {
            Marca::create(['Nombre' => $m]);
        }

        $modelosPorMarca = [
            'Dell' => ['Latitude 3420', 'Vostro 3500', 'OptiPlex 3080'],
            'HP' => ['ProBook 450 G8', 'EliteDesk 800 G6'],
            'Lenovo' => ['ThinkPad E14', 'IdeaPad 3'],
            'Asus' => ['VivoBook 15', 'ExpertBook B1'],
            // Essential 15 es modelo Acer (no es marca).
            'Acer' => ['Aspire 5', 'Essential 15'],
            'Apple' => ['MacBook Air M2'],
        ];
        foreach ($modelosPorMarca as $marcaNombre => $nombres) {
            $marcaId = Marca::where('Nombre', $marcaNombre)->value('MarcaID');
            foreach ($nombres as $n) {
                Modelo::create(['Nombre' => $n, 'MarcaID' => $marcaId]);
            }
        }

        $procesadores = [
            ['Nombre' => 'Intel Core i5-10300H', 'Velocidad' => '2.50 GHz'],
            ['Nombre' => 'Intel Core i7-1165G7', 'Velocidad' => '2.80 GHz'],
            ['Nombre' => 'AMD Ryzen 5 4600H', 'Velocidad' => '3.00 GHz'],
            ['Nombre' => 'Intel Core i5-1135G7', 'Velocidad' => '2.40 GHz'],
            ['Nombre' => 'Intel Core i7-10700', 'Velocidad' => '2.90 GHz'],
            ['Nombre' => 'Apple M2', 'Velocidad' => '3.49 GHz'],
        ];
        foreach ($procesadores as $p) {
            Procesador::create($p);
        }

        Propietario::create(['Nombre' => 'INDUMA']);
        Proveedor::create(['ProveedorID' => 'PROV001', 'Nombre' => 'Distribuidor Tecnológico S.A.S.']);
        Seguro::create(['SeguroID' => 1, 'Seguro_Nombre' => 'Póliza todo riesgo']);
        Ubicacion::create(['UbicacionNombre' => 'Sede Principal']);
        Ubicacion::create(['UbicacionNombre' => 'Bodega TI']);

        $leasing = Leasing::create([
            'Entidad' => 'Leasing Andina',
            'Contrato' => 'CTR-2024-001',
            'FechaInicio' => '2024-01-15',
            'FechaVencimiento' => '2027-01-15',
            'ValorTotal' => 48000000,
            'CanonArrendamiento' => 1333333,
        ]);

        Monitor::create([
            'MonitorID' => 'MON-001',
            'Monitor_Modelo' => 'Dell P2422H',
            'Monitor_Tamaño' => '24 pulg',
            'FechaCompra' => '2024-01-15',
            'VencimientoGarantia' => '2027-01-15',
            'ProveedorID' => 'PROV001',
        ]);

        Monitor::create([
            'MonitorID' => 'MON-002',
            'Monitor_Modelo' => 'HP E24 G5',
            'Monitor_Tamaño' => '23.8 pulg',
            'FechaCompra' => '2024-03-01',
            'VencimientoGarantia' => '2027-01-15',
            'ProveedorID' => 'PROV001',
        ]);

        UsuarioInv::create([
            'UsuarioInvID' => 'USU001',
            'UsuarioInvNombre' => 'Juan Pérez',
            'UsuarioInvCargo' => 'Analista',
            'UsuarioInvCorreo' => 'jperez@induma.com',
            'UsuarioInvArea' => 'Administración',
        ]);

        UsuarioInv::create([
            'UsuarioInvID' => 'USU002',
            'UsuarioInvNombre' => 'María Gómez',
            'UsuarioInvCargo' => 'Coordinadora TI',
            'UsuarioInvCorreo' => 'mgomez@induma.com',
            'UsuarioInvArea' => 'Tecnología',
        ]);

        $dispositivos = ['Mouse', 'Teclado', 'Diadema', 'Docking Station', 'Maletín'];
        foreach ($dispositivos as $d) {
            Dispositivo::create(['Dispositivos_Nombre' => $d]);
        }

        $tipoSoft = [
            ['Soft_Tipo_Nombre' => 'Sistema Operativo', 'Soft_Tipo_Descripcion' => 'Windows, Linux, macOS'],
            ['Soft_Tipo_Nombre' => 'Ofimática', 'Soft_Tipo_Descripcion' => 'Office, suites de productividad'],
            ['Soft_Tipo_Nombre' => 'Diseño', 'Soft_Tipo_Descripcion' => 'Adobe, Corel y similares'],
        ];
        foreach ($tipoSoft as $ts) {
            Tipo_Software::create($ts);
        }

        $this->seedDemoHardware($leasing);
    }

    private function seedDemoHardware(Leasing $leasing): void
    {
        $tipoPortatil = Tipo::where('Nombre', 'Portátil')->value('TipoID');
        $tipoEscritorio = Tipo::where('Nombre', 'Escritorio')->value('TipoID');
        $propietarioId = Propietario::query()->value('PropietarioID');
        $ubicacionSede = Ubicacion::where('UbicacionNombre', 'Sede Principal')->value('UbicacionId');
        $seguroId = Seguro::query()->value('SeguroID');

        $equipos = [
            [
                'Hw_Serial' => 'DL3420-IND-001',
                'Hw_Placa' => 'IND-HW-001',
                'Hw_Nombre' => 'INDUMA-ADM-01',
                'Hw_Serial_Cargador' => 'CARG-DL-78421',
                'ProcesadorID' => Procesador::where('Nombre', 'Intel Core i5-10300H')->value('ProcesadorID'),
                'Hw_Ram' => '16 GB',
                'Hw_Disco_Duro' => 'SSD 512 GB',
                'Hw_FechaCompra' => '2024-01-15',
                'Hw_FechaIngresoInventario' => '2024-01-15',
                'Hw_FechaEntrega' => '2024-01-20',
                'TipoID' => $tipoPortatil,
                'ModeloID' => Modelo::where('Nombre', 'Latitude 3420')->value('ModeloID'),
                'MonitorID' => 'MON-001',
                'UsuarioInvID' => 'USU001',
                'MacEthernet' => 'A4:C3:F0:12:34:56',
                'MacWireless' => 'A4:C3:F0:12:34:57',
                'dispositivos' => [1, 2, 3, 4, 5],
            ],
            [
                'Hw_Serial' => 'HP450-IND-002',
                'Hw_Placa' => 'IND-HW-002',
                'Hw_Nombre' => 'INDUMA-TI-02',
                'Hw_Serial_Cargador' => 'CARG-HP-55210',
                'ProcesadorID' => Procesador::where('Nombre', 'Intel Core i7-1165G7')->value('ProcesadorID'),
                'Hw_Ram' => '16 GB',
                'Hw_Disco_Duro' => 'SSD 1 TB',
                'Hw_FechaCompra' => '2024-03-01',
                'Hw_FechaIngresoInventario' => '2024-03-01',
                'Hw_FechaEntrega' => '2024-03-05',
                'TipoID' => $tipoPortatil,
                'ModeloID' => Modelo::where('Nombre', 'ProBook 450 G8')->value('ModeloID'),
                'MonitorID' => 'MON-002',
                'UsuarioInvID' => 'USU002',
                'MacEthernet' => 'B8:27:EB:AA:BB:01',
                'MacWireless' => 'B8:27:EB:AA:BB:02',
                'dispositivos' => [1, 2, 4],
            ],
            [
                'Hw_Serial' => 'OPT3080-IND-003',
                'Hw_Placa' => 'IND-HW-003',
                'Hw_Nombre' => 'INDUMA-DESK-03',
                'Hw_Serial_Cargador' => null,
                'ProcesadorID' => Procesador::where('Nombre', 'Intel Core i7-10700')->value('ProcesadorID'),
                'Hw_Ram' => '32 GB',
                'Hw_Disco_Duro' => 'SSD 512 GB + HDD 1 TB',
                'Hw_FechaCompra' => '2024-02-10',
                'Hw_FechaIngresoInventario' => '2024-02-10',
                'Hw_FechaEntrega' => '2024-02-15',
                'TipoID' => $tipoEscritorio,
                'ModeloID' => Modelo::where('Nombre', 'OptiPlex 3080')->value('ModeloID'),
                'MonitorID' => 'MON-001',
                'UsuarioInvID' => 'USU002',
                'MacEthernet' => '3C:7C:3F:10:20:30',
                'MacWireless' => null,
                'dispositivos' => [1, 2],
            ],
        ];

        foreach ($equipos as $equipo) {
            $dispositivos = $equipo['dispositivos'];
            unset($equipo['dispositivos']);

            $hardware = Hardware::create(array_merge($equipo, [
                'PropietarioID' => $propietarioId,
                'ProveedorID' => 'PROV001',
                'LeasingID' => $leasing->LeasingID,
                'UbicacionId' => $ubicacionSede,
                'SeguroID' => $seguroId,
                'Revisado' => true,
                'Activo' => true,
                'Hw_Estado' => 'Bueno',
            ]));

            $sync = [];
            foreach ($dispositivos as $codigo) {
                $sync[$codigo] = ['Validacion' => 1];
            }
            $hardware->dispositivos()->sync($sync);
        }

        Mantenimiento::create([
            'Hw_Serial' => 'DL3420-IND-001',
            'mantenimiento_RFE_C' => true,
            'mantenimiento_ST_C' => true,
            'mantenimiento_STYM_C' => true,
            'mantenimiento_LP_C' => true,
            'mantenimiento_LTYM_C' => false,
            'mantenimiento_LM_C' => true,
            'mantenimiento_O_C' => false,
            'mantenimiento_Observaciones' => 'Mantenimiento preventivo semestral sede principal.',
            'mantenimiento_fecha' => '2025-07-15',
            'mantenimiento_Realiza' => 'Jerónimo',
            'mantenimiento_Recibe' => 'Sergio',
        ]);
    }
}
