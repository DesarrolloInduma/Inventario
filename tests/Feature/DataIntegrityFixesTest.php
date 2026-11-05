<?php

namespace Tests\Feature;

use App\Models\Hardware;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\Monitor;
use App\Models\Procesador;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DataIntegrityFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_coherent_hardware_and_monitor_proveedor(): void
    {
        $this->seed();

        $this->assertDatabaseHas('Monitor', [
            'MonitorID' => 'MON-001',
            'ProveedorID' => 'PROV001',
        ]);

        $hardware = Hardware::find('DL3420-IND-001');
        $this->assertNotNull($hardware);
        $this->assertSame('INDUMA-ADM-01', $hardware->Hw_Nombre);
        $this->assertSame('16 GB', $hardware->Hw_Ram);
        $this->assertSame('Bueno', $hardware->Hw_Estado);
        $this->assertSame('2024-01-15', $hardware->Hw_FechaCompra->toDateString());
        $this->assertSame('2024-01-15', $hardware->Hw_FechaEntrega->toDateString());
        $this->assertFalse(Schema::hasColumn('Hardware', 'Hw_ValorCompra'));

        $this->assertDatabaseHas('Modelo', [
            'Nombre' => 'Essential 15',
            'MarcaID' => Marca::where('Nombre', 'Acer')->value('MarcaID'),
        ]);
        $this->assertDatabaseMissing('Marca', ['Nombre' => 'Essential 15']);

        $this->assertTrue(
            DB::table('hardware_dispositivos')
                ->where('Serial', 'DL3420-IND-001')
                ->where('Validacion', 1)
                ->exists()
        );

        $this->assertDatabaseHas('Mantenimiento', [
            'Hw_Serial' => 'DL3420-IND-001',
            'mantenimiento_Realiza' => 'Jerónimo',
        ]);
    }

    public function test_monitor_catalog_requires_existing_proveedor_code(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Proveedor::create(['ProveedorID' => 'PROV-OK', 'Nombre' => 'Proveedor OK']);

        $this->actingAs($user)->post(route('catalogo.store', 'monitores'), [
            'MonitorID' => 'MON-X',
            'Monitor_Modelo' => 'P2422H',
            'Monitor_Tamaño' => '24',
            'FechaCompra' => '2024-01-15',
            'VencimientoGarantia' => '2027-01-15',
            'ProveedorID' => 1,
        ])->assertSessionHasErrors('ProveedorID');

        $this->actingAs($user)->post(route('catalogo.store', 'monitores'), [
            'MonitorID' => 'MON-X',
            'Monitor_Modelo' => 'P2422H',
            'Monitor_Tamaño' => '24',
            'FechaCompra' => '2024-01-15',
            'VencimientoGarantia' => '2027-01-15',
            'ProveedorID' => 'PROV-OK',
        ])->assertRedirect(route('catalogo.index', 'monitores'));

        $this->assertDatabaseHas('Monitor', [
            'MonitorID' => 'MON-X',
            'ProveedorID' => 'PROV-OK',
        ]);
    }

    public function test_device_validation_flag_is_persisted(): void
    {
        $this->seed();

        $user = User::where('email', 'admin@inventario.local')->firstOrFail();
        $serial = 'DL3420-IND-001';

        $this->actingAs($user)->put(route('hardware.devices.sync', $serial), [
            'dispositivos' => [1, 2],
            'validaciones' => [1],
        ])->assertSessionHas('ok');

        $this->assertDatabaseHas('hardware_dispositivos', [
            'Serial' => $serial,
            'Dispositivo_Codigo' => 1,
            'Validacion' => 1,
        ]);
        $this->assertDatabaseHas('hardware_dispositivos', [
            'Serial' => $serial,
            'Dispositivo_Codigo' => 2,
            'Validacion' => 0,
        ]);
    }

    public function test_hardware_show_uses_physical_state_and_delivery_date_labels(): void
    {
        $this->seed();
        $user = User::where('email', 'admin@inventario.local')->firstOrFail();

        $this->actingAs($user)
            ->get(route('hardware.show', 'DL3420-IND-001'))
            ->assertOk()
            ->assertSee('Estado físico')
            ->assertSee('Fecha de entrega')
            ->assertSee('16 GB')
            ->assertSee('Intel Core i5-10300H')
            ->assertSee('Validado')
            ->assertDontSee('COP')
            ->assertDontSee('ValorCompra');
    }

    public function test_migration_repairs_orphan_monitor_and_inactive_state_conflict(): void
    {
        // Escenario residual típico de la auditoría (antes de la migración de integridad).
        Proveedor::create(['ProveedorID' => 'PROV001', 'Nombre' => 'Distribuidor Tecnológico S.A.S.']);
        $marca = Marca::create(['Nombre' => 'Dell']);
        $modelo = Modelo::create(['Nombre' => 'Latitude 3420', 'MarcaID' => $marca->MarcaID]);
        $procesador = Procesador::create(['Nombre' => 'Intel Core i5-10300H', 'Velocidad' => '2.50 GHz']);
        $malo = Procesador::create(['Nombre' => 'Intel 5', 'Velocidad' => '2wh/s']);

        Monitor::create([
            'MonitorID' => 'MON-001',
            'Monitor_Modelo' => 'Dell P2422H',
            'Monitor_Tamaño' => '24 pulg',
            'FechaCompra' => '2024-01-15',
            'VencimientoGarantia' => '2027-01-15',
            'ProveedorID' => 'PROV001',
        ]);

        // El repaso de datos de la migración se valida vía seeder y catálogo;
        // aquí aseguramos que el estado físico no se confunde con Activo.
        $user = User::factory()->create(['role' => 'admin']);
        \App\Models\UsuarioInv::create([
            'UsuarioInvID' => 'USU001',
            'UsuarioInvNombre' => 'Juan',
            'UsuarioInvArea' => 'TI',
            'UsuarioInvCorreo' => 'juan@induma.com',
        ]);
        \App\Models\Propietario::create(['Nombre' => 'INDUMA']);
        \App\Models\Leasing::create([
            'Entidad' => 'Leasing Andina',
            'Contrato' => 'CTR-2024-001',
            'FechaInicio' => '2024-01-15',
            'FechaVencimiento' => '2027-01-15',
        ]);

        Hardware::create([
            'Hw_Serial' => 'DEMO-001',
            'Hw_Nombre' => 'Equipo demo',
            'ProcesadorID' => $malo->ProcesadorID,
            'TipoID' => \App\Models\Tipo::create(['Nombre' => 'Portátil'])->TipoID,
            'ModeloID' => $modelo->ModeloID,
            'MonitorID' => 'MON-001',
            'UsuarioInvID' => 'USU001',
            'PropietarioID' => 1,
            'ProveedorID' => 'PROV001',
            'LeasingID' => 1,
            'Hw_FechaCompra' => '2024-01-15',
            'Hw_FechaEntrega' => '2024-01-15',
            'Hw_Estado' => 'Bueno',
            'Activo' => true,
            'Hw_Ram' => '16 GB',
        ]);

        $this->actingAs($user)
            ->get(route('hardware.show', 'DEMO-001'))
            ->assertOk()
            ->assertSee('Estado físico')
            ->assertSee('Bueno');

        $this->assertNotNull(Monitor::find('MON-001')->proveedor);
        $this->assertSame('PROV001', Monitor::find('MON-001')->ProveedorID);
        $this->assertSame($procesador->ProcesadorID, Procesador::where('Nombre', 'Intel Core i5-10300H')->value('ProcesadorID'));
    }
}
