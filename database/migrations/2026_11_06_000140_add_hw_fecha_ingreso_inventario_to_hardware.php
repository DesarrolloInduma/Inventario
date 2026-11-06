<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('Hardware', 'Hw_FechaIngresoInventario')) {
            Schema::table('Hardware', function (Blueprint $table) {
                $table->date('Hw_FechaIngresoInventario')->nullable()->after('Hw_FechaEntrega');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('Hardware', 'Hw_FechaIngresoInventario')) {
            Schema::table('Hardware', function (Blueprint $table) {
                $table->dropColumn('Hw_FechaIngresoInventario');
            });
        }
    }
};
