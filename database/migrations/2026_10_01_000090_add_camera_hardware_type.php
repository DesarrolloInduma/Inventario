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

        foreach ([['Impresora', 'impresor'], ['Cámara', 'camara']] as [$nombre, $base]) {
            if (!$tipos->contains(fn ($tipo) => str_contains($tipo, $base) || ($base === 'camara' && str_contains($tipo, 'camera')))) {
                DB::table('Tipo')->insert(['Nombre' => $nombre]);
                $tipos->push(Str::ascii(mb_strtolower($nombre)));
            }
        }
    }

    public function down(): void
    {
    }
};