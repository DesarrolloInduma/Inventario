<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->repairMonitorProveedor();
        $this->repairHardwareConsistency();
        $this->repairProcesadores();
        $this->repairModelosSinMarca();
        $this->makeOptionalDateFieldsNullable();
        $this->addProcesadorUniqueIndex();
        $this->addCambioUsuarioForeignKey();
    }

    public function down(): void
    {
        if ($this->hasForeignKey('Cambio_de_Usuario', 'Hard_Srl')) {
            Schema::table('Cambio_de_Usuario', function (Blueprint $table) {
                $table->dropForeign(['Hard_Srl']);
            });
        }

        Schema::table('Procesador', function (Blueprint $table) {
            $table->dropUnique('procesador_nombre_velocidad_unique');
        });
    }

    private function fechaDevolucionColumn(): string
    {
        if (Schema::hasColumn('Hardware', 'Hw_FechaDevolucion')) {
            return 'Hw_FechaDevolucion';
        }

        return 'Hw_FechaGarantiaFin';
    }

    private function repairMonitorProveedor(): void
    {
        $type = Schema::getColumnType('Monitor', 'ProveedorID');
        $needsTypeChange = ! in_array($type, ['string', 'varchar', 'nvarchar'], true);

        if ($needsTypeChange) {
            Schema::table('Monitor', function (Blueprint $table) {
                $table->string('ProveedorID', 50)->nullable()->change();
            });
        }

        $fallbackProveedor = DB::table('Proveedor')->orderBy('ProveedorID')->value('ProveedorID');

        if ($fallbackProveedor) {
            $validIds = DB::table('Proveedor')->pluck('ProveedorID')->all();

            DB::table('Monitor')
                ->where(function ($query) use ($validIds) {
                    $query->whereNull('ProveedorID')
                        ->orWhereNotIn('ProveedorID', $validIds);
                })
                ->update(['ProveedorID' => $fallbackProveedor]);
        }

        if ($needsTypeChange) {
            Schema::table('Monitor', function (Blueprint $table) {
                $table->string('ProveedorID', 50)->nullable(false)->change();
            });
        }

        if (! $this->hasForeignKey('Monitor', 'ProveedorID')) {
            Schema::table('Monitor', function (Blueprint $table) {
                $table->foreign('ProveedorID')->references('ProveedorID')->on('Proveedor');
            });
        }
    }

    private function repairHardwareConsistency(): void
    {
        DB::table('Hardware')
            ->where('Activo', 1)
            ->whereRaw('LOWER(Hw_Estado) = ?', ['inactivo'])
            ->update(['Hw_Estado' => 'Bueno']);

        $fechaDevolucion = $this->fechaDevolucionColumn();

        $equipos = DB::table('Hardware as h')
            ->leftJoin('Leasing as l', 'h.LeasingID', '=', 'l.LeasingID')
            ->select(
                'h.Hw_Serial',
                'h.Hw_FechaCompra',
                "h.{$fechaDevolucion} as fecha_devolucion",
                'l.FechaInicio',
                'l.FechaVencimiento'
            )
            ->get();

        foreach ($equipos as $eq) {
            $updates = [];

            if ($eq->FechaInicio && $eq->Hw_FechaCompra && $eq->Hw_FechaCompra > now()->toDateString()) {
                $updates['Hw_FechaCompra'] = $eq->FechaInicio;
            }

            if ($eq->FechaVencimiento && $eq->fecha_devolucion && $eq->fecha_devolucion > $eq->FechaVencimiento) {
                $updates[$fechaDevolucion] = $eq->FechaVencimiento;
            }

            if ($updates !== []) {
                DB::table('Hardware')->where('Hw_Serial', $eq->Hw_Serial)->update($updates);
            }
        }

        if (DB::table('Hardware')->where('Hw_Serial', '1234f')->exists()) {
            $procesadorId = DB::table('Procesador')
                ->where('Nombre', 'Intel Core i5-10300H')
                ->value('ProcesadorID')
                ?: DB::table('Procesador')->orderBy('ProcesadorID')->value('ProcesadorID');

            $modeloId = DB::table('Modelo')
                ->where('Nombre', 'Latitude 3420')
                ->value('ModeloID')
                ?: DB::table('Modelo')->orderBy('ModeloID')->value('ModeloID');

            DB::table('Hardware')->where('Hw_Serial', '1234f')->update([
                'Hw_Nombre' => 'INDUMA-LAT-001',
                'Hw_Ram' => '16 GB',
                'Hw_Disco_Duro' => 'SSD 512 GB',
                'Hw_Estado' => 'Bueno',
                'ProcesadorID' => $procesadorId,
                'ModeloID' => $modeloId,
                'Activo' => 1,
            ]);

            DB::table('hardware_dispositivos')
                ->where('Serial', '1234f')
                ->update(['Validacion' => 1]);
        }
    }

    private function repairProcesadores(): void
    {
        $malEscritos = DB::table('Procesador')
            ->where(function ($q) {
                $q->where('Nombre', 'Intel 5')
                    ->orWhere('Velocidad', '2wh/s')
                    ->orWhere(function ($inner) {
                        $inner->where('Nombre', 'Intel Core i5')
                            ->where('Velocidad', '2.5 GHz');
                    });
            })
            ->pluck('ProcesadorID');

        if ($malEscritos->isEmpty()) {
            return;
        }

        $canonico = DB::table('Procesador')
            ->where('Nombre', 'Intel Core i5-10300H')
            ->value('ProcesadorID');

        if (! $canonico) {
            $canonico = DB::table('Procesador')->insertGetId([
                'Nombre' => 'Intel Core i5-10300H',
                'Velocidad' => '2.50 GHz',
            ], 'ProcesadorID');
        }

        DB::table('Hardware')
            ->whereIn('ProcesadorID', $malEscritos)
            ->update(['ProcesadorID' => $canonico]);

        DB::table('Procesador')
            ->whereIn('ProcesadorID', $malEscritos)
            ->where('ProcesadorID', '!=', $canonico)
            ->delete();
    }

    private function repairModelosSinMarca(): void
    {
        $acerId = DB::table('Marca')->where('Nombre', 'Acer')->value('MarcaID');

        if ($acerId) {
            // Essential 15 es un modelo (p. ej. Acer), nunca una marca.
            DB::table('Modelo')
                ->whereNull('MarcaID')
                ->where('Nombre', 'like', '%Essential 15%')
                ->update(['MarcaID' => $acerId]);
        }

        DB::table('Modelo')
            ->whereNull('MarcaID')
            ->whereNotIn('ModeloID', function ($query) {
                $query->select('ModeloID')->from('Hardware');
            })
            ->delete();
    }

    private function makeOptionalDateFieldsNullable(): void
    {
        Schema::table('Hardware', function (Blueprint $table) {
            if (Schema::hasColumn('Hardware', 'Hw_FechaCompra')) {
                $table->date('Hw_FechaCompra')->nullable()->change();
            }
            if (Schema::hasColumn('Hardware', 'Hw_FechaDevolucion')) {
                $table->date('Hw_FechaDevolucion')->nullable()->change();
            } elseif (Schema::hasColumn('Hardware', 'Hw_FechaGarantiaFin')) {
                $table->date('Hw_FechaGarantiaFin')->nullable()->change();
            }
            // Valor de compra ya no forma parte del inventario; se elimina en 000120.
            if (Schema::hasColumn('Hardware', 'Hw_ValorCompra')) {
                $table->decimal('Hw_ValorCompra', 18, 0)->nullable()->change();
            }
        });
    }

    private function addProcesadorUniqueIndex(): void
    {
        Schema::table('Procesador', function (Blueprint $table) {
            $table->unique(['Nombre', 'Velocidad'], 'procesador_nombre_velocidad_unique');
        });
    }

    private function addCambioUsuarioForeignKey(): void
    {
        DB::table('Cambio_de_Usuario')
            ->whereNotNull('Hard_Srl')
            ->whereNotIn('Hard_Srl', function ($query) {
                $query->select('Hw_Serial')->from('Hardware');
            })
            ->update(['Hard_Srl' => null]);

        if (! $this->hasForeignKey('Cambio_de_Usuario', 'Hard_Srl')) {
            Schema::table('Cambio_de_Usuario', function (Blueprint $table) {
                $table->foreign('Hard_Srl')->references('Hw_Serial')->on('Hardware')->nullOnDelete();
            });
        }
    }

    private function hasForeignKey(string $table, string $column): bool
    {
        foreach (Schema::getForeignKeys($table) as $foreignKey) {
            if (in_array($column, $foreignKey['columns'], true)) {
                return true;
            }
        }

        return false;
    }
};
