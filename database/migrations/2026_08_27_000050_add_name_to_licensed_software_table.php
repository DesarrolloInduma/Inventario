<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Software_Licenciado', function (Blueprint $table) {
            $table->string('Software_Nombre', 100)->nullable()->after('Soft_Tipo_Id');
        });
    }

    public function down(): void
    {
        Schema::table('Software_Licenciado', function (Blueprint $table) {
            $table->dropColumn('Software_Nombre');
        });
    }
};
