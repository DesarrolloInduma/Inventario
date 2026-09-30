<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Hardware', function (Blueprint $table) {
            $table->dropForeign(['ProcesadorID']);
            $table->dropForeign(['MonitorID']);
            $table->dropForeign(['UsuarioInvID']);
        });

        Schema::table('Hardware', function (Blueprint $table) {
            $table->unsignedInteger('ProcesadorID')->nullable()->change();
            $table->string('MonitorID', 50)->nullable()->change();
            $table->string('UsuarioInvID', 30)->nullable()->change();
        });

        Schema::table('Hardware', function (Blueprint $table) {
            $table->foreign('ProcesadorID')->references('ProcesadorID')->on('Procesador');
            $table->foreign('MonitorID')->references('MonitorID')->on('Monitor');
            $table->foreign('UsuarioInvID')->references('UsuarioInvID')->on('UsuarioInv');
        });
    }

    public function down(): void
    {
        Schema::table('Hardware', function (Blueprint $table) {
            $table->dropForeign(['ProcesadorID']);
            $table->dropForeign(['MonitorID']);
            $table->dropForeign(['UsuarioInvID']);
        });

        Schema::table('Hardware', function (Blueprint $table) {
            $table->unsignedInteger('ProcesadorID')->nullable(false)->change();
            $table->string('MonitorID', 50)->nullable(false)->change();
            $table->string('UsuarioInvID', 30)->nullable(false)->change();
        });

        Schema::table('Hardware', function (Blueprint $table) {
            $table->foreign('ProcesadorID')->references('ProcesadorID')->on('Procesador');
            $table->foreign('MonitorID')->references('MonitorID')->on('Monitor');
            $table->foreign('UsuarioInvID')->references('UsuarioInvID')->on('UsuarioInv');
        });
    }
};
