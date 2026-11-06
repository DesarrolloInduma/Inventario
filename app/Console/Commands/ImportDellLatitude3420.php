<?php

namespace App\Console\Commands;

use Database\Seeders\DellLatitude3420ImportSeeder;
use Illuminate\Console\Command;

class ImportDellLatitude3420 extends Command
{
    protected $signature = 'inventario:import-dell-3420
                            {--fresh-fixture : Regenera el JSON desde el .xlsm del store si openpyxl está disponible}';

    protected $description = 'Importa existencias Dell Latitude 3420 / Inspiron 14 desde database/data/dell_latitude_3420_import.json';

    public function handle(): int
    {
        $fixture = database_path('data/dell_latitude_3420_import.json');
        if (! is_file($fixture)) {
            $this->error("Falta el fixture {$fixture}");

            return self::FAILURE;
        }

        $this->info('Importando existencias Dell (Latitude 3420 / Inspiron 14)...');
        $this->call('db:seed', [
            '--class' => DellLatitude3420ImportSeeder::class,
            '--force' => true,
        ]);

        return self::SUCCESS;
    }
}
