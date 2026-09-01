<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Software_Licenciado', function (Blueprint $table) {
            $table->increments('Software_Id');
            $table->unsignedInteger('Soft_Tipo_Id');
            $table->string('Software_Clave', 50)->nullable();
            $table->integer('Software_Cantidad')->nullable();
            $table->string('Software_Version', 50)->nullable();
            $table->string('Software_Tip_Licenciamiento', 50)->nullable();

            $table->foreign('Soft_Tipo_Id')->references('Soft_Tipo_Id')->on('Tipo_Software');
        });

        Schema::create('Software_NoLicenciado', function (Blueprint $table) {
            $table->increments('Softwarenl_Id');
            $table->string('Softwarenl_Nombre', 50);
            $table->string('Softwarenl_Version', 50)->nullable();
            $table->unsignedInteger('Soft_Tipo_Id')->nullable();

            $table->foreign('Soft_Tipo_Id')->references('Soft_Tipo_Id')->on('Tipo_Software');
        });

        Schema::create('Hard_Soft', function (Blueprint $table) {
            $table->string('Hw_Serial', 50);
            $table->unsignedInteger('Software_Id');

            $table->primary(['Hw_Serial', 'Software_Id']);
            $table->foreign('Hw_Serial')->references('Hw_Serial')->on('Hardware')->cascadeOnDelete();
            $table->foreign('Software_Id')->references('Software_Id')->on('Software_Licenciado')->cascadeOnDelete();
        });

        Schema::create('Hard_Soft_Nl', function (Blueprint $table) {
            $table->string('Hw_Serial', 50);
            $table->unsignedInteger('Softwarenl_Id');

            $table->primary(['Hw_Serial', 'Softwarenl_Id']);
            $table->foreign('Hw_Serial')->references('Hw_Serial')->on('Hardware')->cascadeOnDelete();
            $table->foreign('Softwarenl_Id')->references('Softwarenl_Id')->on('Software_NoLicenciado')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Hard_Soft_Nl');
        Schema::dropIfExists('Hard_Soft');
        Schema::dropIfExists('Software_NoLicenciado');
        Schema::dropIfExists('Software_Licenciado');
    }
};
