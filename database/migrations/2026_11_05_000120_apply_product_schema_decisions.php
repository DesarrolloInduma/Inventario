<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('Hardware', 'Hw_ValorCompra')) {
            Schema::table('Hardware', function (Blueprint $table) {
                $table->dropColumn('Hw_ValorCompra');
            });
        }

        if (Schema::hasColumn('Hardware', 'Hw_FechaGarantiaFin') && ! Schema::hasColumn('Hardware', 'Hw_FechaDevolucion')) {
            Schema::table('Hardware', function (Blueprint $table) {
                $table->renameColumn('Hw_FechaGarantiaFin', 'Hw_FechaDevolucion');
            });
        }

        if (Schema::hasColumn('Hardware', 'Hw_FehaRenovacion') && ! Schema::hasColumn('Hardware', 'Hw_FechaRenovacion')) {
            Schema::table('Hardware', function (Blueprint $table) {
                $table->renameColumn('Hw_FehaRenovacion', 'Hw_FechaRenovacion');
            });
        }

        if (Schema::hasTable('Mantenimineto') && ! Schema::hasTable('Mantenimiento')) {
            Schema::rename('Mantenimineto', 'Mantenimiento');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('Mantenimiento') && ! Schema::hasTable('Mantenimineto')) {
            Schema::rename('Mantenimiento', 'Mantenimineto');
        }

        if (Schema::hasColumn('Hardware', 'Hw_FechaRenovacion') && ! Schema::hasColumn('Hardware', 'Hw_FehaRenovacion')) {
            Schema::table('Hardware', function (Blueprint $table) {
                $table->renameColumn('Hw_FechaRenovacion', 'Hw_FehaRenovacion');
            });
        }

        if (Schema::hasColumn('Hardware', 'Hw_FechaDevolucion') && ! Schema::hasColumn('Hardware', 'Hw_FechaGarantiaFin')) {
            Schema::table('Hardware', function (Blueprint $table) {
                $table->renameColumn('Hw_FechaDevolucion', 'Hw_FechaGarantiaFin');
            });
        }

        if (! Schema::hasColumn('Hardware', 'Hw_ValorCompra')) {
            Schema::table('Hardware', function (Blueprint $table) {
                $table->decimal('Hw_ValorCompra', 18, 0)->nullable();
            });
        }
    }
};
