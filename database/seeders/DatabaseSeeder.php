<?php

namespace Database\Seeders;

use App\Models\Dispositivo;
use App\Models\Leasing;
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

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@inventario.local',
            'password' => 'admin123',
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Consulta',
            'email' => 'consulta@inventario.local',
            'password' => 'consulta123',
            'role' => 'consulta',
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
            1 => ['Latitude 3420', 'Vostro 3500', 'OptiPlex 3080'],
            2 => ['ProBook 450 G8', 'EliteDesk 800 G6'],
            3 => ['ThinkPad E14', 'IdeaPad 3'],
        ];
        foreach ($modelosPorMarca as $marcaId => $nombres) {
            foreach ($nombres as $n) {
                Modelo::create(['Nombre' => $n, 'MarcaID' => $marcaId]);
            }
        }

        $procesadores = [
            ['Nombre' => 'Intel Core i5-10300H', 'Velocidad' => '2.50 GHz'],
            ['Nombre' => 'Intel Core i7-1165G7', 'Velocidad' => '2.80 GHz'],
            ['Nombre' => 'AMD Ryzen 5 4600H', 'Velocidad' => '3.00 GHz'],
        ];
        foreach ($procesadores as $p) {
            Procesador::create($p);
        }

        Propietario::create(['Nombre' => 'INDUMA']);
        Proveedor::create(['ProveedorID' => 'PROV001', 'Nombre' => 'Distribuidor Tecnológico S.A.S.']);
        Seguro::create(['SeguroID' => 1, 'Seguro_Nombre' => 'Póliza todo riesgo']);
        Ubicacion::create(['UbicacionNombre' => 'Sede Principal']);

        Leasing::create([
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
            'ProveedorID' => 1,
        ]);

        UsuarioInv::create([
            'UsuarioInvID' => 'USU001',
            'UsuarioInvNombre' => 'Juan Pérez',
            'UsuarioInvCargo' => 'Analista',
            'UsuarioInvCorreo' => 'jperez@indumapps.com',
            'UsuarioInvArea' => 'Administración',
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
    }
}
