<?php

namespace Tests\Feature;

use App\Models\Hardware;
use App\Models\HardwareActa;
use App\Models\Modelo;
use App\Models\Tipo;
use App\Models\User;
use App\Models\UsuarioInv;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HardwareActaAndSerialCargadorTest extends TestCase
{
    use RefreshDatabase;

    public function test_hardware_page_displays_serial_cargador_and_actas(): void
    {
        $user = User::factory()->create(['Role' => 'admin']);
        $tipo = Tipo::create(['Nombre' => 'Portátil']);
        $marca = \App\Models\Marca::create(['Nombre' => 'Lenovo']);
        $modelo = Modelo::create(['Nombre' => 'ThinkPad', 'MarcaID' => $marca->MarcaID]);
        $procesador = \App\Models\Procesador::create(['Nombre' => 'Intel Core i7']);
        $usuarioInv = UsuarioInv::create([
            'UsuarioInvID' => 'USR001',
            'UsuarioInvNombre' => 'Juan Perez',
            'UsuarioInvArea' => 'TI',
            'UsuarioInvCorreo' => 'juan@induma.com',
        ]);
        $proveedor = \App\Models\Proveedor::create(['ProveedorID' => 'PROV-1', 'Nombre' => 'Proveedor SAS']);
        $monitor = \App\Models\Monitor::create([
            'MonitorID' => 'MON-01',
            'Monitor_Modelo' => 'Dell 24',
            'FechaCompra' => now()->toDateString(),
            'VencimientoGarantia' => now()->addYear()->toDateString(),
            'ProveedorID' => 1,
        ]);
        $propietario = \App\Models\Propietario::create(['Nombre' => 'INDUMA']);
        $leasing = \App\Models\Leasing::create([
            'Entidad' => 'Leasing Bancolombia',
            'Contrato' => 'C-100',
            'FechaInicio' => now()->toDateString(),
            'FechaVencimiento' => now()->addYear()->toDateString(),
        ]);

        $hardware = Hardware::create([
            'Hw_Serial' => 'TEST-001',
            'Hw_Serial_Cargador' => 'CARG-999',
            'Hw_Nombre' => 'Laptop-01',
            'TipoID' => $tipo->TipoID,
            'ModeloID' => $modelo->ModeloID,
            'ProcesadorID' => $procesador->ProcesadorID,
            'UsuarioInvID' => $usuarioInv->UsuarioInvID,
            'MonitorID' => $monitor->MonitorID,
            'PropietarioID' => $propietario->PropietarioID,
            'ProveedorID' => $proveedor->ProveedorID,
            'LeasingID' => $leasing->LeasingID,
            'Hw_FechaCompra' => now()->toDateString(),
            'Hw_ValorCompra' => 1000,
            'Hw_FechaGarantiaFin' => now()->addYear()->toDateString(),
            'Activo' => true,
        ]);

        $response = $this->actingAs($user)->get(route('hardware.show', $hardware->Hw_Serial));
        $response->assertStatus(200);
        $response->assertSee('Serial cargador');
        $response->assertSee('CARG-999');
        $response->assertSee('Actas y documentos adjuntos');
        $response->assertSee('Descargar acta en PDF');
    }

    public function test_can_upload_acta_during_hardware_creation(): void
    {
        $user = User::factory()->create(['Role' => 'admin']);
        $tipo = Tipo::create(['Nombre' => 'Portátil']);
        $marca = \App\Models\Marca::create(['Nombre' => 'Lenovo']);
        $modelo = Modelo::create(['Nombre' => 'ThinkPad', 'MarcaID' => $marca->MarcaID]);
        $procesador = \App\Models\Procesador::create(['Nombre' => 'Intel Core i7']);
        $usuarioInv = UsuarioInv::create([
            'UsuarioInvID' => 'USR003',
            'UsuarioInvNombre' => 'Carlos Lopez',
            'UsuarioInvArea' => 'Operaciones',
            'UsuarioInvCorreo' => 'carlos@induma.com',
        ]);
        $proveedor = \App\Models\Proveedor::create(['ProveedorID' => 'PROV-3', 'Nombre' => 'Proveedor SAS']);
        $monitor = \App\Models\Monitor::create([
            'MonitorID' => 'MON-03',
            'Monitor_Modelo' => 'Dell 24',
            'FechaCompra' => now()->toDateString(),
            'VencimientoGarantia' => now()->addYear()->toDateString(),
            'ProveedorID' => 1,
        ]);
        $propietario = \App\Models\Propietario::create(['Nombre' => 'INDUMA']);
        $leasing = \App\Models\Leasing::create([
            'Entidad' => 'Leasing Bancolombia',
            'Contrato' => 'C-103',
            'FechaInicio' => now()->toDateString(),
            'FechaVencimiento' => now()->addYear()->toDateString(),
        ]);

        $file = UploadedFile::fake()->create('acta_inicial.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)->post(route('hardware.store'), [
            'Hw_Serial' => 'TEST-003',
            'Hw_Serial_Cargador' => 'CARG-777',
            'Hw_Nombre' => 'Laptop-03',
            'TipoID' => $tipo->TipoID,
            'ModeloID' => $modelo->ModeloID,
            'procesador_nombre' => 'Intel Core i7',
            'usuario_id' => 'USR003',
            'usuario_nombre' => 'Carlos Lopez',
            'usuario_area' => 'Operaciones',
            'usuario_correo' => 'carlos@induma.com',
            'acta_archivo' => $file,
            'acta_titulo' => 'Acta inicial de entrega',
        ]);

        $response->assertRedirect(route('hardware.show', 'TEST-003'));
        $this->assertDatabaseHas('Hardware', [
            'Hw_Serial' => 'TEST-003',
            'Hw_Serial_Cargador' => 'CARG-777',
        ]);
        $this->assertDatabaseHas('hardware_actas', [
            'Hw_Serial' => 'TEST-003',
            'titulo' => 'Acta inicial de entrega',
        ]);
    }

    public function test_user_change_updates_current_owner_and_keeps_history(): void
    {
        $user = User::factory()->create(['Role' => 'admin']);
        $tipo = Tipo::create(['Nombre' => 'Portátil']);
        $marca = \App\Models\Marca::create(['Nombre' => 'Lenovo']);
        $modelo = Modelo::create(['Nombre' => 'ThinkPad', 'MarcaID' => $marca->MarcaID]);
        $procesador = \App\Models\Procesador::create(['Nombre' => 'Intel Core i7']);
        $usuarioInv = UsuarioInv::create([
            'UsuarioInvID' => 'USR002',
            'UsuarioInvNombre' => 'Maria Gomez',
            'UsuarioInvArea' => 'Finanzas',
            'UsuarioInvCorreo' => 'maria@induma.com',
        ]);
        $proveedor = \App\Models\Proveedor::create(['ProveedorID' => 'PROV-2', 'Nombre' => 'Proveedor SAS']);
        $monitor = \App\Models\Monitor::create([
            'MonitorID' => 'MON-02',
            'Monitor_Modelo' => 'Dell 24',
            'FechaCompra' => now()->toDateString(),
            'VencimientoGarantia' => now()->addYear()->toDateString(),
            'ProveedorID' => 1,
        ]);
        $propietario = \App\Models\Propietario::create(['Nombre' => 'INDUMA']);
        $leasing = \App\Models\Leasing::create([
            'Entidad' => 'Leasing Bancolombia',
            'Contrato' => 'C-101',
            'FechaInicio' => now()->toDateString(),
            'FechaVencimiento' => now()->addYear()->toDateString(),
        ]);

        $hardware = Hardware::create([
            'Hw_Serial' => 'TEST-002',
            'Hw_Serial_Cargador' => 'CARG-888',
            'Hw_Nombre' => 'Laptop-02',
            'TipoID' => $tipo->TipoID,
            'ModeloID' => $modelo->ModeloID,
            'ProcesadorID' => $procesador->ProcesadorID,
            'UsuarioInvID' => $usuarioInv->UsuarioInvID,
            'MonitorID' => $monitor->MonitorID,
            'PropietarioID' => $propietario->PropietarioID,
            'ProveedorID' => $proveedor->ProveedorID,
            'LeasingID' => $leasing->LeasingID,
            'Hw_FechaCompra' => now()->toDateString(),
            'Hw_ValorCompra' => 1000,
            'Hw_FechaGarantiaFin' => now()->addYear()->toDateString(),
            'Activo' => true,
        ]);

        $response = $this->actingAs($user)->post(route('hardware.user-changes.store', $hardware->Hw_Serial), [
            'UsuarioActual' => 'Julian',
            'FechaCambio' => now()->toDateString(),
        ]);

        $response->assertSessionHas('ok');
        $hardware->refresh();

        $this->assertDatabaseHas('Cambio_de_Usuario', [
            'Hard_Srl' => $hardware->Hw_Serial,
            'UsuarioActual' => 'Julian',
        ]);

        $this->assertNotNull($hardware->usuarioInv);
        $this->assertSame('Julian', $hardware->usuarioInv->UsuarioInvNombre);
    }

    public function test_delivery_act_downloads_the_attached_file_when_present(): void
    {
        $user = User::factory()->create(['Role' => 'admin']);
        $tipo = Tipo::create(['Nombre' => 'Portátil']);
        $marca = \App\Models\Marca::create(['Nombre' => 'Lenovo']);
        $modelo = Modelo::create(['Nombre' => 'ThinkPad', 'MarcaID' => $marca->MarcaID]);
        $procesador = \App\Models\Procesador::create(['Nombre' => 'Intel Core i7']);
        $usuarioInv = UsuarioInv::create([
            'UsuarioInvID' => 'USR003',
            'UsuarioInvNombre' => 'Maria Gomez',
            'UsuarioInvArea' => 'Finanzas',
            'UsuarioInvCorreo' => 'maria2@induma.com',
        ]);
        $proveedor = \App\Models\Proveedor::create(['ProveedorID' => 'PROV-3', 'Nombre' => 'Proveedor SAS']);
        $monitor = \App\Models\Monitor::create([
            'MonitorID' => 'MON-03',
            'Monitor_Modelo' => 'Dell 24',
            'FechaCompra' => now()->toDateString(),
            'VencimientoGarantia' => now()->addYear()->toDateString(),
            'ProveedorID' => 1,
        ]);
        $propietario = \App\Models\Propietario::create(['Nombre' => 'INDUMA']);
        $leasing = \App\Models\Leasing::create([
            'Entidad' => 'Leasing Bancolombia',
            'Contrato' => 'C-102',
            'FechaInicio' => now()->toDateString(),
            'FechaVencimiento' => now()->addYear()->toDateString(),
        ]);

        $hardware = Hardware::create([
            'Hw_Serial' => 'TEST-003',
            'Hw_Serial_Cargador' => 'CARG-777',
            'Hw_Nombre' => 'Laptop-03',
            'TipoID' => $tipo->TipoID,
            'ModeloID' => $modelo->ModeloID,
            'ProcesadorID' => $procesador->ProcesadorID,
            'UsuarioInvID' => $usuarioInv->UsuarioInvID,
            'MonitorID' => $monitor->MonitorID,
            'PropietarioID' => $propietario->PropietarioID,
            'ProveedorID' => $proveedor->ProveedorID,
            'LeasingID' => $leasing->LeasingID,
            'Hw_FechaCompra' => now()->toDateString(),
            'Hw_ValorCompra' => 1000,
            'Hw_FechaGarantiaFin' => now()->addYear()->toDateString(),
            'Activo' => true,
        ]);

        $file = UploadedFile::fake()->create('acta_entrega.pdf', 100, 'application/pdf');
        $this->actingAs($user)->post(route('hardware.actas.store', $hardware->Hw_Serial), [
            'archivo' => $file,
            'titulo' => 'Acta de entrega firmada',
        ]);

        $response = $this->actingAs($user)->get(route('hardware.delivery-act', [$hardware->Hw_Serial]) . '?pdf=1');
        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename="acta_entrega.pdf"');
    }

    public function test_show_page_shows_inline_attachment_flow_without_redirect_message(): void
    {
        $user = User::factory()->create(['Role' => 'admin']);
        $tipo = Tipo::create(['Nombre' => 'Portátil']);
        $marca = \App\Models\Marca::create(['Nombre' => 'Lenovo']);
        $modelo = Modelo::create(['Nombre' => 'ThinkPad', 'MarcaID' => $marca->MarcaID]);
        $procesador = \App\Models\Procesador::create(['Nombre' => 'Intel Core i7']);
        $usuarioInv = UsuarioInv::create([
            'UsuarioInvID' => 'USR-ATTACH',
            'UsuarioInvNombre' => 'Maria Gomez',
            'UsuarioInvArea' => 'Finanzas',
            'UsuarioInvCorreo' => 'maria@induma.com',
        ]);
        $proveedor = \App\Models\Proveedor::create(['ProveedorID' => 'PROV-ATT', 'Nombre' => 'Proveedor SAS']);
        $monitor = \App\Models\Monitor::create([
            'MonitorID' => 'MON-ATT',
            'Monitor_Modelo' => 'Dell 24',
            'FechaCompra' => now()->toDateString(),
            'VencimientoGarantia' => now()->addYear()->toDateString(),
            'ProveedorID' => 1,
        ]);
        $propietario = \App\Models\Propietario::create(['Nombre' => 'INDUMA']);
        $leasing = \App\Models\Leasing::create([
            'Entidad' => 'Leasing Bancolombia',
            'Contrato' => 'C-200',
            'FechaInicio' => now()->toDateString(),
            'FechaVencimiento' => now()->addYear()->toDateString(),
        ]);

        $hardware = Hardware::create([
            'Hw_Serial' => 'TEST-ATTACH',
            'Hw_Serial_Cargador' => 'CARG-ATTACH',
            'Hw_Nombre' => 'Laptop-ATT',
            'TipoID' => $tipo->TipoID,
            'ModeloID' => $modelo->ModeloID,
            'ProcesadorID' => $procesador->ProcesadorID,
            'UsuarioInvID' => $usuarioInv->UsuarioInvID,
            'MonitorID' => $monitor->MonitorID,
            'PropietarioID' => $propietario->PropietarioID,
            'ProveedorID' => $proveedor->ProveedorID,
            'LeasingID' => $leasing->LeasingID,
            'Hw_FechaCompra' => now()->toDateString(),
            'Hw_ValorCompra' => 1000,
            'Hw_FechaGarantiaFin' => now()->addYear()->toDateString(),
            'Activo' => true,
        ]);

        $response = $this->actingAs($user)->get(route('hardware.show', $hardware->Hw_Serial));

        $response->assertStatus(200);
        $response->assertSee('Adjuntar acta de entrega aquí');
        $response->assertDontSee('Ir a editar equipo para adjuntar');
    }

    public function test_can_upload_download_and_delete_acta(): void
    {
        $user = User::factory()->create(['Role' => 'admin']);
        $tipo = Tipo::create(['Nombre' => 'Portátil']);
        $marca = \App\Models\Marca::create(['Nombre' => 'Lenovo']);
        $modelo = Modelo::create(['Nombre' => 'ThinkPad', 'MarcaID' => $marca->MarcaID]);
        $procesador = \App\Models\Procesador::create(['Nombre' => 'Intel Core i7']);
        $usuarioInv = UsuarioInv::create([
            'UsuarioInvID' => 'USR004',
            'UsuarioInvNombre' => 'Maria Gomez',
            'UsuarioInvArea' => 'Finanzas',
            'UsuarioInvCorreo' => 'maria3@induma.com',
        ]);
        $proveedor = \App\Models\Proveedor::create(['ProveedorID' => 'PROV-4', 'Nombre' => 'Proveedor SAS']);
        $monitor = \App\Models\Monitor::create([
            'MonitorID' => 'MON-04',
            'Monitor_Modelo' => 'Dell 24',
            'FechaCompra' => now()->toDateString(),
            'VencimientoGarantia' => now()->addYear()->toDateString(),
            'ProveedorID' => 1,
        ]);
        $propietario = \App\Models\Propietario::create(['Nombre' => 'INDUMA']);
        $leasing = \App\Models\Leasing::create([
            'Entidad' => 'Leasing Bancolombia',
            'Contrato' => 'C-104',
            'FechaInicio' => now()->toDateString(),
            'FechaVencimiento' => now()->addYear()->toDateString(),
        ]);

        $hardware = Hardware::create([
            'Hw_Serial' => 'TEST-004',
            'Hw_Serial_Cargador' => 'CARG-888',
            'Hw_Nombre' => 'Laptop-04',
            'TipoID' => $tipo->TipoID,
            'ModeloID' => $modelo->ModeloID,
            'ProcesadorID' => $procesador->ProcesadorID,
            'UsuarioInvID' => $usuarioInv->UsuarioInvID,
            'MonitorID' => $monitor->MonitorID,
            'PropietarioID' => $propietario->PropietarioID,
            'ProveedorID' => $proveedor->ProveedorID,
            'LeasingID' => $leasing->LeasingID,
            'Hw_FechaCompra' => now()->toDateString(),
            'Hw_ValorCompra' => 1000,
            'Hw_FechaGarantiaFin' => now()->addYear()->toDateString(),
            'Activo' => true,
        ]);

        $file = UploadedFile::fake()->create('acta_entrega.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)->post(route('hardware.actas.store', $hardware->Hw_Serial), [
            'archivo' => $file,
            'titulo' => 'Acta de entrega firmada',
        ]);

        $response->assertSessionHas('ok');
        $this->assertDatabaseHas('hardware_actas', [
            'Hw_Serial' => $hardware->Hw_Serial,
            'titulo' => 'Acta de entrega firmada',
            'nombre_original' => 'acta_entrega.pdf',
        ]);

        $acta = HardwareActa::where('Hw_Serial', $hardware->Hw_Serial)->latest()->first();
        $this->assertNotNull($acta);
        $this->assertFileExists(storage_path("app/public/{$acta->archivo_path}"));

        $downloadResponse = $this->actingAs($user)->get(route('hardware.actas.download', [$hardware->Hw_Serial, $acta->id]));
        $downloadResponse->assertStatus(200);

        $deliveryActResponse = $this->actingAs($user)->get(route('hardware.delivery-act', $hardware->Hw_Serial));
        $deliveryActResponse->assertStatus(200);
        $deliveryActResponse->assertSee('Acta de entrega de equipo tecnológico');
        $deliveryActResponse->assertSee('CARG-888');

        $deleteResponse = $this->actingAs($user)->delete(route('hardware.actas.destroy', [$hardware->Hw_Serial, $acta->id]));
        $deleteResponse->assertSessionHas('ok');
        $this->assertDatabaseMissing('hardware_actas', [
            'id' => $acta->id,
        ]);
        $this->assertFileDoesNotExist(storage_path("app/public/{$acta->archivo_path}"));
    }
}
