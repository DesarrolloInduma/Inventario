<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cadena histórica: FechaGarantiaFin → FechaDevolucion → FechaEntrega (día del préstamo).
        if (Schema::hasColumn('Hardware', 'Hw_FechaGarantiaFin') && ! Schema::hasColumn('Hardware', 'Hw_FechaEntrega')) {
            Schema::table('Hardware', function (Blueprint $table) {
                $table->renameColumn('Hw_FechaGarantiaFin', 'Hw_FechaEntrega');
            });
        }

        if (Schema::hasColumn('Hardware', 'Hw_FechaDevolucion') && ! Schema::hasColumn('Hardware', 'Hw_FechaEntrega')) {
            Schema::table('Hardware', function (Blueprint $table) {
                $table->renameColumn('Hw_FechaDevolucion', 'Hw_FechaEntrega');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('Hardware', 'Hw_FechaEntrega') && ! Schema::hasColumn('Hardware', 'Hw_FechaDevolucion')) {
            Schema::table('Hardware', function (Blueprint $table) {
                $table->renameColumn('Hw_FechaEntrega', 'Hw_FechaDevolucion');
            });
        }
    }
};
