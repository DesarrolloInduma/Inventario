<?php

namespace Tests\Feature;

use App\Models\Hardware;
use App\Models\Leasing;
use App\Models\Modelo;
use App\Models\Propietario;
use App\Models\Proveedor;
use App\Models\Tipo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_printer_and_camera_creation_forms_only_show_general_fields(): void
    {
        $user = User::factory()->create(['Role' => 'admin']);

        foreach ([['impresoras.create', 'Impresora'], ['camaras.create', 'Cámara']] as [$route, $label]) {
            Tipo::firstOrCreate(['Nombre' => $label]);

            $this->actingAs($user)->get(route($route))
                ->assertOk()
                ->assertSee('Serial equipo')
                ->assertSee('Serial de cargador')
                ->assertSee('Nombre del equipo')
                ->assertSee('Tipo de hardware')
                ->assertSee('Modelo *')
                ->assertDontSee('Nombre del procesador')
                ->assertDontSee('Información del usuario');
        }
    }

    public function test_printers_and_cameras_can_be_created_with_general_fields_only(): void
    {
        $user = User::factory()->create(['Role' => 'admin']);
        $modelo = Modelo::create(['Nombre' => 'Modelo básico']);
        $propietario = Propietario::create(['Nombre' => 'Local']);
        $proveedor = Proveedor::create(['ProveedorID' => 'PROV-LOCAL', 'Nombre' => 'Proveedor local']);
        $leasing = Leasing::create([
            'Entidad' => 'Local',
            'Contrato' => 'LOCAL-001',
            'FechaInicio' => now()->toDateString(),
            'FechaVencimiento' => now()->addYear()->toDateString(),
        ]);
        $tipoImpresora = Tipo::firstOrCreate(['Nombre' => 'Impresora']);
        $tipoCamara = Tipo::firstOrCreate(['Nombre' => 'Cámara']);

        $this->actingAs($user)->post(route('impresoras.store'), [
            'Hw_Serial' => 'PRINT-001',
            'Hw_Serial_Cargador' => 'CHARGER-001',
            'Hw_Nombre' => 'Impresora local',
            'TipoID' => $tipoImpresora->TipoID,
            'ModeloID' => $modelo->ModeloID,
        ])->assertRedirect(route('hardware.show', 'PRINT-001'));

        $this->actingAs($user)->post(route('camaras.store'), [
            'Hw_Serial' => 'CAM-001',
            'Hw_Nombre' => 'Cámara local',
            'TipoID' => $tipoCamara->TipoID,
            'ModeloID' => $modelo->ModeloID,
        ])->assertRedirect(route('hardware.show', 'CAM-001'));

        $this->assertDatabaseHas('Hardware', [
            'Hw_Serial' => 'PRINT-001',
            'Hw_Serial_Cargador' => 'CHARGER-001',
            'ProcesadorID' => null,
            'UsuarioInvID' => null,
        ]);
        $this->assertDatabaseHas('Hardware', [
            'Hw_Serial' => 'CAM-001',
            'TipoID' => $tipoCamara->TipoID,
            'ProcesadorID' => null,
            'UsuarioInvID' => null,
        ]);

        $this->get(route('impresoras.index'))->assertOk()->assertSee('Impresora local')->assertDontSee('Cámara local');
        $this->get(route('camaras.index'))->assertOk()->assertSee('Cámara local')->assertDontSee('Impresora local');
        $this->assertSame(2, Hardware::where('PropietarioID', $propietario->PropietarioID)
            ->where('ProveedorID', $proveedor->ProveedorID)
            ->where('LeasingID', $leasing->LeasingID)
            ->count());
    }
}