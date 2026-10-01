<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('Tipo')) {
            return;
        }

        $tipos = DB::table('Tipo')->pluck('Nombre')
            ->map(fn ($nombre) => Str::ascii(mb_strtolower(trim($nombre))));

        if (!$tipos->contains(fn ($nombre) => str_contains($nombre, 'impresor'))) {
            DB::table('Tipo')->insert(['Nombre' => 'Impresora']);
        }
    }

    public function down(): void
    {
    }
};