<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Hardware', function (Blueprint $table) {
            $table->string('Hw_Serial', 50)->primary();
            $table->string('Hw_Placa', 50)->nullable();
            $table->string('Hw_Nombre', 50);
            $table->unsignedInteger('ProcesadorID');
            $table->string('Hw_Ram', 50)->nullable();
            $table->string('Hw_Disco_Duro', 50)->nullable();
            $table->date('Hw_FechaCompra');
            $table->decimal('Hw_ValorCompra', 18, 0);
            $table->date('Hw_FechaGarantiaFin');
            $table->unsignedInteger('TipoID');
            $table->unsignedInteger('ModeloID');
            $table->string('MonitorID', 50);
            $table->string('UsuarioInvID', 30);
            $table->unsignedInteger('PropietarioID');
            $table->string('ProveedorID', 50);
            $table->unsignedInteger('LeasingID');
            $table->unsignedInteger('UbicacionId')->nullable();
            $table->unsignedInteger('SeguroID')->nullable();
            $table->boolean('Revisado')->nullable();
            $table->boolean('Activo')->nullable();
            $table->date('Hw_FehaRenovacion')->nullable();
            $table->string('Hw_Estado', 50)->nullable();
            $table->string('MacEthernet', 50)->nullable();
            $table->string('MacWireless', 50)->nullable();

            $table->foreign('ProcesadorID')->references('ProcesadorID')->on('Procesador');
            $table->foreign('TipoID')->references('TipoID')->on('Tipo');
            $table->foreign('ModeloID')->references('ModeloID')->on('Modelo');
            $table->foreign('MonitorID')->references('MonitorID')->on('Monitor');
            $table->foreign('UsuarioInvID')->references('UsuarioInvID')->on('UsuarioInv');
            $table->foreign('PropietarioID')->references('PropietarioID')->on('Propietario');
            $table->foreign('ProveedorID')->references('ProveedorID')->on('Proveedor');
            $table->foreign('LeasingID')->references('LeasingID')->on('Leasing');
            $table->foreign('UbicacionId')->references('UbicacionId')->on('Ubicacion');
            $table->foreign('SeguroID')->references('SeguroID')->on('Seguro');

            $table->index('Hw_Nombre');
            $table->index('UsuarioInvID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Hardware');
    }
};
