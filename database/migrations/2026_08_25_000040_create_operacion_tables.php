<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hardware_dispositivos', function (Blueprint $table) {
            $table->string('Serial', 50);
            $table->unsignedInteger('Dispositivo_Codigo');
            $table->boolean('Validacion')->default(false);

            $table->primary(['Serial', 'Dispositivo_Codigo']);
            $table->foreign('Serial')->references('Hw_Serial')->on('Hardware')->cascadeOnDelete();
            $table->foreign('Dispositivo_Codigo')->references('Dispositivo_Codigo')->on('Dispositivo');
        });

        Schema::create('Mantenimineto', function (Blueprint $table) {
            $table->increments('mantenimiento_Id');
            $table->string('Hw_Serial', 50)->nullable();
            $table->boolean('mantenimiento_RFE_C')->nullable();
            $table->string('mantenimiento_RFE_O', 300)->nullable();
            $table->boolean('mantenimiento_ST_C')->nullable();
            $table->string('mantenimiento_ST_O', 300)->nullable();
            $table->boolean('mantenimiento_STYM_C')->nullable();
            $table->string('mantenimiento_STYM_O', 300)->nullable();
            $table->boolean('mantenimiento_LP_C')->nullable();
            $table->string('mantenimiento_LP_O', 300)->nullable();
            $table->boolean('mantenimiento_LTYM_C')->nullable();
            $table->string('mantenimiento_LTYM_O', 300)->nullable();
            $table->boolean('mantenimiento_LM_C')->nullable();
            $table->string('mantenimiento_LM_O', 300)->nullable();
            $table->boolean('mantenimiento_O_C')->nullable();
            $table->string('mantenimiento_O_O', 300)->nullable();
            $table->string('mantenimiento_Observaciones', 300)->nullable();
            $table->date('mantenimiento_fecha')->nullable();
            $table->string('mantenimiento_Realiza', 60)->nullable();
            $table->string('mantenimiento_Recibe', 60)->nullable();

            $table->foreign('Hw_Serial')->references('Hw_Serial')->on('Hardware')->nullOnDelete();
        });

        Schema::create('Observaciones', function (Blueprint $table) {
            $table->increments('Observacion_Id');
            $table->string('Observacion_Detalle', 300)->nullable();
            $table->date('Observacion_Fecha');
            $table->string('Hw_Serial', 50);

            $table->foreign('Hw_Serial')->references('Hw_Serial')->on('Hardware')->cascadeOnDelete();
        });

        Schema::create('Auditoria', function (Blueprint $table) {
            $table->increments('IdAuditoria');
            $table->dateTime('FechaAuditoria');
            $table->string('Observaciones', 300);
            $table->string('UsuarioInvArea', 50);
            $table->string('Hw_Nombre', 50);
            $table->string('UsuarioInvNombre', 50);
            $table->string('Hw_Serial', 50);
            $table->string('TipoHardware', 50);
            $table->string('MonitorID', 50);
            $table->string('Monitor_Modelo', 20);
            $table->string('SistemaOperativo', 50);
            $table->string('ClaveSO', 50);
            $table->string('Office', 50);
            $table->string('ClaveOffice', 50);

            $table->foreign('Hw_Serial')->references('Hw_Serial')->on('Hardware')->cascadeOnDelete();
        });

        Schema::create('Cambio_de_Usuario', function (Blueprint $table) {
            $table->increments('Id');
            $table->string('Hard_Srl', 50)->nullable();
            $table->string('UsuarioActual', 30)->nullable();
            $table->date('FechaCambio')->nullable();

            $table->index('Hard_Srl');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Cambio_de_Usuario');
        Schema::dropIfExists('Auditoria');
        Schema::dropIfExists('Observaciones');
        Schema::dropIfExists('Mantenimineto');
        Schema::dropIfExists('hardware_dispositivos');
    }
};
