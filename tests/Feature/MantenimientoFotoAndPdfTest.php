<?php

namespace Tests\Feature;

use App\Models\Mantenimiento;
use App\Models\MantenimientoFoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MantenimientoFotoAndPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register_mantenimiento_with_foto_grafico_and_download_pdf(): void
    {
        Storage::fake('public');
        $this->seed();

        $admin = User::where('email', 'admin@inventario.local')->firstOrFail();
        $serial = 'DL3420-IND-001';
        $foto = $this->fakePngUpload('grafico-mant.png');

        $this->actingAs($admin)
            ->get(route('mant.create', $serial))
            ->assertOk()
            ->assertSee('Foto gráfico')
            ->assertSee('name="fotos[]"', false)
            ->assertSee('Observaciones generales');

        $this->actingAs($admin)
            ->post(route('mant.store', $serial), [
                'mantenimiento_fecha' => '2026-11-06',
                'mantenimiento_Realiza' => 'Técnico QA',
                'mantenimiento_Recibe' => 'Juan Pérez',
                'mantenimiento_Observaciones' => 'Servicio con evidencia fotográfica.',
                'mantenimiento_RFE_C' => '1',
                'fotos' => [$foto],
            ])
            ->assertRedirect(route('hardware.show', $serial));

        $mant = Mantenimiento::where('Hw_Serial', $serial)
            ->where('mantenimiento_Realiza', 'Técnico QA')
            ->firstOrFail();

        $this->assertDatabaseHas('MantenimientoFoto', [
            'mantenimiento_Id' => $mant->mantenimiento_Id,
            'nombre_original' => 'grafico-mant.png',
        ]);

        $registro = MantenimientoFoto::where('mantenimiento_Id', $mant->mantenimiento_Id)->firstOrFail();
        Storage::disk('public')->assertExists($registro->archivo_path);

        $this->actingAs($admin)
            ->get(route('mant.show', $mant->mantenimiento_Id))
            ->assertOk()
            ->assertSee('Foto gráfico')
            ->assertSee('grafico-mant.png');

        $this->actingAs($admin)
            ->get(route('mant.index'))
            ->assertOk()
            ->assertSee(route('mant.pdf', $mant->mantenimiento_Id), false);

        $this->actingAs($admin)
            ->get(route('mant.pdf', $mant->mantenimiento_Id))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_rejects_non_image_foto_grafico(): void
    {
        Storage::fake('public');
        $this->seed();
        $admin = User::where('email', 'admin@inventario.local')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('mant.store', 'DL3420-IND-001'), [
                'mantenimiento_fecha' => '2026-11-06',
                'fotos' => [UploadedFile::fake()->create('malware.exe', 100, 'application/octet-stream')],
            ])
            ->assertSessionHasErrors('fotos.0');
    }

    private function fakePngUpload(string $name): UploadedFile
    {
        $path = sys_get_temp_dir().'/'.$name;
        // PNG 1x1 válido (sin depender de la extensión GD).
        file_put_contents($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }
}
