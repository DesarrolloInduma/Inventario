<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Hardware', function (Blueprint $table) {
            $table->string('Hw_Serial_Cargador', 50)->nullable()->after('Hw_Serial');
        });
    }

    public function down(): void
    {
        Schema::table('Hardware', function (Blueprint $table) {
            $table->dropColumn('Hw_Serial_Cargador');
        });
    }
};
