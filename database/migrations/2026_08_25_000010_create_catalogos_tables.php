<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Tipo', function (Blueprint $table) {
            $table->increments('TipoID');
            $table->string('Nombre', 50);
        });

        Schema::create('Marca', function (Blueprint $table) {
            $table->increments('MarcaID');
            $table->string('Nombre', 50);
        });

        Schema::create('Modelo', function (Blueprint $table) {
            $table->increments('ModeloID');
            $table->string('Nombre', 50);
            $table->unsignedInteger('MarcaID')->nullable();
            $table->foreign('MarcaID')->references('MarcaID')->on('Marca')->nullOnDelete();
        });

        Schema::create('Procesador', function (Blueprint $table) {
            $table->increments('ProcesadorID');
            $table->string('Nombre', 50)->nullable();
            $table->char('Velocidad', 10)->nullable();
        });

        Schema::create('Propietario', function (Blueprint $table) {
            $table->increments('PropietarioID');
            $table->string('Nombre', 50);
        });

        Schema::create('Proveedor', function (Blueprint $table) {
            $table->string('ProveedorID', 50)->primary();
            $table->string('Nombre', 50);
        });

        Schema::create('Seguro', function (Blueprint $table) {
            $table->integer('SeguroID')->primary();
            $table->string('Seguro_Nombre', 50)->nullable();
        });

        Schema::create('Leasing', function (Blueprint $table) {
            $table->increments('LeasingID');
            $table->string('Entidad', 50);
            $table->string('Contrato', 50);
            $table->date('FechaInicio');
            $table->date('FechaVencimiento');
            $table->decimal('ValorTotal', 18, 0)->nullable();
            $table->decimal('CanonArrendamiento', 18, 0)->nullable();
        });

        Schema::create('Ubicacion', function (Blueprint $table) {
            $table->increments('UbicacionId');
            $table->char('UbicacionNombre', 20)->nullable();
        });

        Schema::create('Dispositivo', function (Blueprint $table) {
            $table->increments('Dispositivo_Codigo');
            $table->string('Dispositivos_Nombre', 20);
        });

        Schema::create('Tipo_Software', function (Blueprint $table) {
            $table->increments('Soft_Tipo_Id');
            $table->string('Soft_Tipo_Nombre', 50)->nullable();
            $table->string('Soft_Tipo_Descripcion', 50)->nullable();
        });

        Schema::create('Monitor', function (Blueprint $table) {
            $table->string('MonitorID', 50)->primary();
            $table->string('Monitor_Modelo', 20)->nullable();
            $table->char('Monitor_Tamaño', 10)->nullable();
            $table->date('FechaCompra');
            $table->date('VencimientoGarantia');
            $table->unsignedInteger('ProveedorID');
        });

        Schema::create('UsuarioInv', function (Blueprint $table) {
            $table->string('UsuarioInvID', 30)->primary();
            $table->string('UsuarioInvNombre', 50)->nullable();
            $table->string('UsuarioInvCargo', 50)->nullable();
            $table->string('UsuarioInvCorreo', 50)->nullable();
            $table->string('UsuarioInvArea', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('UsuarioInv');
        Schema::dropIfExists('Monitor');
        Schema::dropIfExists('Tipo_Software');
        Schema::dropIfExists('Dispositivo');
        Schema::dropIfExists('Ubicacion');
        Schema::dropIfExists('Leasing');
        Schema::dropIfExists('Seguro');
        Schema::dropIfExists('Proveedor');
        Schema::dropIfExists('Propietario');
        Schema::dropIfExists('Procesador');
        Schema::dropIfExists('Modelo');
        Schema::dropIfExists('Marca');
        Schema::dropIfExists('Tipo');
    }
};
