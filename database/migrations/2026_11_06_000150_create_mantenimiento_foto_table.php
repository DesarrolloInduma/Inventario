<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('MantenimientoFoto', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('mantenimiento_Id');
            $table->string('archivo_path', 255);
            $table->string('nombre_original', 150)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('tamanio')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('mantenimiento_Id')
                ->references('mantenimiento_Id')
                ->on('Mantenimiento')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('MantenimientoFoto');
    }
};
