<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hardware_actas', function (Blueprint $table) {
            $table->id();
            $table->string('Hw_Serial', 50);
            $table->string('nombre_original');
            $table->string('archivo_path');
            $table->string('titulo')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('tamanio')->nullable();
            $table->timestamps();

            $table->foreign('Hw_Serial')->references('Hw_Serial')->on('Hardware')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hardware_actas');
    }
};
