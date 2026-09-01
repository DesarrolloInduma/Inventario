<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CatalogoController extends Controller
{
    private const CATALOGOS = [
        'tipos' => [
            'model' => 'Tipo', 'titulo' => 'Tipos de hardware',
            'pk' => 'TipoID',
            'campos' => [['col' => 'Nombre', 'label' => 'Nombre del tipo de hardware', 'tipo' => 'text', 'req' => true, 'max' => 50, 'unique' => true]],
        ],
        'marcas' => [
            'model' => 'Marca', 'titulo' => 'Marcas',
            'pk' => 'MarcaID',
            'campos' => [['col' => 'Nombre', 'label' => 'Nombre de la marca', 'tipo' => 'text', 'req' => true, 'max' => 50, 'unique' => true]],
        ],
        'modelos' => [
            'model' => 'Modelo', 'titulo' => 'Modelos',
            'pk' => 'ModeloID',
            'campos' => [
                ['col' => 'Nombre', 'label' => 'Nombre del modelo', 'tipo' => 'text', 'req' => true, 'max' => 50, 'unique_with' => 'MarcaID'],
                ['col' => 'MarcaID', 'label' => 'Marca asociada', 'tipo' => 'number', 'req' => false],
            ],
        ],
        'procesadores' => [
            'model' => 'Procesador', 'titulo' => 'Procesadores',
            'pk' => 'ProcesadorID',
            'campos' => [
                ['col' => 'Nombre', 'label' => 'Nombre del procesador', 'tipo' => 'text', 'req' => false, 'max' => 50, 'unique_with' => 'Velocidad'],
                ['col' => 'Velocidad', 'label' => 'Velocidad (máx. 10 car.)', 'tipo' => 'text', 'req' => false, 'max' => 10],
            ],
        ],
        'propietarios' => [
            'model' => 'Propietario', 'titulo' => 'Propietarios',
            'pk' => 'PropietarioID',
            'campos' => [['col' => 'Nombre', 'label' => 'Razón social del propietario', 'tipo' => 'text', 'req' => true, 'max' => 50, 'unique' => true]],
        ],
        'proveedores' => [
            'model' => 'Proveedor', 'titulo' => 'Proveedores',
            'pk' => 'ProveedorID', 'pk_texto' => true,
            'campos' => [
                ['col' => 'ProveedorID', 'label' => 'Código', 'tipo' => 'text', 'req' => true, 'max' => 50],
                ['col' => 'Nombre', 'label' => 'Nombre del proveedor tecnológico', 'tipo' => 'text', 'req' => true, 'max' => 50, 'unique' => true],
            ],
        ],
        'seguros' => [
            'model' => 'Seguro', 'titulo' => 'Seguros',
            'pk' => 'SeguroID', 'pk_manual' => true,
            'campos' => [
                ['col' => 'SeguroID', 'label' => 'ID', 'tipo' => 'number', 'req' => true],
                ['col' => 'Seguro_Nombre', 'label' => 'Nombre de la póliza', 'tipo' => 'text', 'req' => false, 'max' => 50, 'unique' => true],
            ],
        ],
        'ubicaciones' => [
            'model' => 'Ubicacion', 'titulo' => 'Ubicaciones',
            'pk' => 'UbicacionId',
            'campos' => [['col' => 'UbicacionNombre', 'label' => 'Nombre de la ubicación física (máx. 20 car.)', 'tipo' => 'text', 'req' => false, 'max' => 20, 'unique' => true]],
        ],
        'dispositivos' => [
            'model' => 'Dispositivo', 'titulo' => 'Dispositivos',
            'pk' => 'Dispositivo_Codigo',
            'campos' => [['col' => 'Dispositivos_Nombre', 'label' => 'Nombre del dispositivo o periférico', 'tipo' => 'text', 'req' => true, 'max' => 20, 'unique' => true]],
        ],
        'tipos-software' => [
            'model' => 'Tipo_Software', 'titulo' => 'Tipos de software',
            'pk' => 'Soft_Tipo_Id',
            'campos' => [
                ['col' => 'Soft_Tipo_Nombre', 'label' => 'Nombre de la categoría de software', 'tipo' => 'text', 'req' => false, 'max' => 50, 'unique' => true],
                ['col' => 'Soft_Tipo_Descripcion', 'label' => 'Descripción', 'tipo' => 'text', 'req' => false, 'max' => 50],
            ],
        ],
        'usuarios-inv' => [
            'model' => 'UsuarioInv', 'titulo' => 'Usuarios de inventario',
            'pk' => 'UsuarioInvID', 'pk_texto' => true,
            'campos' => [
                ['col' => 'UsuarioInvID', 'label' => 'Código', 'tipo' => 'text', 'req' => true, 'max' => 30],
                ['col' => 'UsuarioInvNombre', 'label' => 'Nombre', 'tipo' => 'text', 'req' => false, 'max' => 50],
                ['col' => 'UsuarioInvCargo', 'label' => 'Cargo', 'tipo' => 'text', 'req' => false, 'max' => 50],
                ['col' => 'UsuarioInvCorreo', 'label' => 'Correo', 'tipo' => 'email', 'req' => false, 'max' => 50],
                ['col' => 'UsuarioInvArea', 'label' => 'Área', 'tipo' => 'text', 'req' => false, 'max' => 50],
            ],
        ],
        'monitores' => [
            'model' => 'Monitor', 'titulo' => 'Monitores',
            'pk' => 'MonitorID', 'pk_texto' => true,
            'campos' => [
                ['col' => 'MonitorID', 'label' => 'Código', 'tipo' => 'text', 'req' => true, 'max' => 50],
                ['col' => 'Monitor_Modelo', 'label' => 'Modelo', 'tipo' => 'text', 'req' => false, 'max' => 20],
                ['col' => 'Monitor_Tamaño', 'label' => 'Tamaño', 'tipo' => 'text', 'req' => false, 'max' => 10],
                ['col' => 'FechaCompra', 'label' => 'Fecha de compra', 'tipo' => 'date', 'req' => true],
                ['col' => 'VencimientoGarantia', 'label' => 'Vence garantía', 'tipo' => 'date', 'req' => true],
                ['col' => 'ProveedorID', 'label' => 'ID proveedor', 'tipo' => 'number', 'req' => true],
            ],
        ],
        'leasings' => [
            'model' => 'Leasing', 'titulo' => 'Contratos de leasing',
            'pk' => 'LeasingID',
            'campos' => [
                ['col' => 'Entidad', 'label' => 'Entidad', 'tipo' => 'text', 'req' => true, 'max' => 50],
                ['col' => 'Contrato', 'label' => 'Número de contrato', 'tipo' => 'text', 'req' => true, 'max' => 50, 'unique_with' => 'Entidad'],
                ['col' => 'FechaInicio', 'label' => 'Inicio', 'tipo' => 'date', 'req' => true],
                ['col' => 'FechaVencimiento', 'label' => 'Vencimiento', 'tipo' => 'date', 'req' => true],
                ['col' => 'ValorTotal', 'label' => 'Valor total', 'tipo' => 'number', 'req' => false],
                ['col' => 'CanonArrendamiento', 'label' => 'Canon mensual', 'tipo' => 'number', 'req' => false],
            ],
        ],
    ];

    public function index(string $tabla)
    {
        $cfg = $this->cfg($tabla);
        $model = $this->model($cfg);

        $registros = $model::orderBy($cfg['pk'])->paginate(15);

        return view('catalogos.index', compact('tabla', 'cfg', 'registros'));
    }

    public function store(Request $request, string $tabla)
    {
        $this->authorizeAdmin();
        $cfg = $this->cfg($tabla);

        $data = $this->rules($request, $cfg, null);
        $this->model($cfg)::create($data);

        return redirect()->route('catalogo.index', $tabla)->with('ok', 'Registro creado.');
    }

    public function update(Request $request, string $tabla, string $id)
    {
        $this->authorizeAdmin();
        $cfg = $this->cfg($tabla);
        $model = $this->model($cfg);

        $registro = $model::whereKey($id)->firstOrFail();
        $data = $this->rules($request, $cfg, $id);

        unset($data[$cfg['pk']]);
        $registro->update($data);

        return redirect()->route('catalogo.index', $tabla)->with('ok', 'Registro actualizado.');
    }

    public function destroy(string $tabla, string $id)
    {
        $this->authorizeAdmin();
        $cfg = $this->cfg($tabla);

        try {
            $this->model($cfg)::whereKey($id)->delete();
        } catch (\Throwable) {
            return back()->withErrors(['general' => 'No se puede eliminar: el registro está en uso.']);
        }

        return redirect()->route('catalogo.index', $tabla)->with('ok', 'Registro eliminado.');
    }

    private function cfg(string $tabla): array
    {
        if (!isset(self::CATALOGOS[$tabla])) {
            abort(404);
        }

        return self::CATALOGOS[$tabla];
    }

    /** @return class-string<Model> */
    private function model(array $cfg): string
    {
        return 'App\\Models\\' . $cfg['model'];
    }

    private function rules(Request $request, array $cfg, ?string $ignoreId): array
    {
        $rules = [];
        foreach ($cfg['campos'] as $c) {
            $rule = [$c['req'] ? 'required' : 'nullable', 'string'];
            if ($c['tipo'] === 'number') {
                $rule = [$c['req'] ? 'required' : 'nullable', 'integer'];
            } elseif ($c['tipo'] === 'date') {
                $rule = [$c['req'] ? 'required' : 'nullable', 'date'];
            } elseif ($c['tipo'] === 'email') {
                $rule = [$c['req'] ? 'required' : 'nullable', 'email', 'max:' . ($c['max'] ?? 100)];
            } else {
                $rule[] = 'max:' . ($c['max'] ?? 255);
            }
            $rules[$c['col']] = $rule;

            if ($c['unique'] ?? false) {
                $unique = Rule::unique($cfg['model'], $c['col']);
                if ($ignoreId !== null) {
                    $unique->ignore($ignoreId, $cfg['pk']);
                }
                $rules[$c['col']][] = $unique;
            }

            if (isset($c['unique_with'])) {
                $unique = Rule::unique($cfg['model'], $c['col'])
                    ->where(fn ($query) => $query->where($c['unique_with'], $request->input($c['unique_with'])));
                if ($ignoreId !== null) {
                    $unique->ignore($ignoreId, $cfg['pk']);
                }
                $rules[$c['col']][] = $unique;
            }
        }

        if (($cfg['pk_manual'] ?? false) || ($cfg['pk_texto'] ?? false)) {
            $pkRule = isset($rules[$cfg['pk']])
                ? array_merge([$rules[$cfg['pk']][0]], array_slice($rules[$cfg['pk']], 1))
                : ['required', 'string'];
            $unique = Rule::unique($cfg['model'], $cfg['pk']);
            if ($ignoreId !== null) {
                $unique->ignore($ignoreId, $cfg['pk']);
            }
            $rules[$cfg['pk']] = array_merge($pkRule, [$unique]);
        }

        return $request->validate($rules);
    }

    private function authorizeAdmin(): void
    {
        if (!auth()->user()->esAdmin()) {
            abort(403, 'Solo un administrador puede modificar catálogos.');
        }
    }
}
