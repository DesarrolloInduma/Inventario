<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $columnas = [
            'users' => ['created_at', 'updated_at', 'email_verified_at'],
            'hardware_actas' => ['created_at', 'updated_at'],
            'password_reset_tokens' => ['created_at'],
            'failed_jobs' => ['failed_at'],
            'Auditoria' => ['FechaAuditoria'],
        ];

        foreach ($columnas as $tabla => $campos) {
            foreach ($campos as $campo) {
                DB::statement(sprintf(
                    'ALTER TABLE %s ALTER COLUMN %s TYPE timestamp(3)',
                    $this->ident($tabla),
                    $this->ident($campo)
                ));
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }
    }

    private function ident(string $name): string
    {
        return '"'.str_replace('"', '""', $name).'"';
    }
};
