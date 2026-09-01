<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hardware extends Model
{
    protected $table = 'Hardware';

    protected $primaryKey = 'Hw_Serial';

    public $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'Hw_FechaCompra' => 'date',
        'Hw_FechaGarantiaFin' => 'date',
        'Hw_FehaRenovacion' => 'date',
        'Hw_ValorCompra' => 'float',
        'Revisado' => 'boolean',
        'Activo' => 'boolean',
    ];

    public function tipo()
    {
        return $this->belongsTo(Tipo::class, 'TipoID', 'TipoID');
    }

    public function procesador()
    {
        return $this->belongsTo(Procesador::class, 'ProcesadorID', 'ProcesadorID');
    }

    public function modelo()
    {
        return $this->belongsTo(Modelo::class, 'ModeloID', 'ModeloID');
    }

    public function monitor()
    {
        return $this->belongsTo(Monitor::class, 'MonitorID', 'MonitorID');
    }

    public function usuarioInv()
    {
        return $this->belongsTo(UsuarioInv::class, 'UsuarioInvID', 'UsuarioInvID');
    }

    public function propietario()
    {
        return $this->belongsTo(Propietario::class, 'PropietarioID', 'PropietarioID');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'ProveedorID', 'ProveedorID');
    }

    public function leasing()
    {
        return $this->belongsTo(Leasing::class, 'LeasingID', 'LeasingID');
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'UbicacionId', 'UbicacionId');
    }

    public function seguro()
    {
        return $this->belongsTo(Seguro::class, 'SeguroID', 'SeguroID');
    }

    public function softwaresLicenciados()
    {
        return $this->belongsToMany(
            Software_Licenciado::class,
            'Hard_Soft',
            'Hw_Serial',
            'Software_Id'
        );
    }

    public function softwaresNoLicenciados()
    {
        return $this->belongsToMany(
            Software_NoLicenciado::class,
            'Hard_Soft_Nl',
            'Hw_Serial',
            'Softwarenl_Id'
        );
    }

    public function dispositivos()
    {
        return $this->belongsToMany(
            Dispositivo::class,
            'hardware_dispositivos',
            'Serial',
            'Dispositivo_Codigo'
        )->withPivot('Validacion');
    }

    public function mantenimientos()
    {
        return $this->hasMany(Mantenimineto::class, 'Hw_Serial', 'Hw_Serial');
    }

    public function observaciones()
    {
        return $this->hasMany(Observaciones::class, 'Hw_Serial', 'Hw_Serial');
    }

    public function auditorias()
    {
        return $this->hasMany(Auditoria::class, 'Hw_Serial', 'Hw_Serial');
    }

    public function cambiosDeUsuario()
    {
        return $this->hasMany(Cambio_de_Usuario::class, 'Hard_Srl', 'Hw_Serial')
            ->orderByDesc('FechaCambio')
            ->orderByDesc('Id');
    }

    public function actas()
    {
        return $this->hasMany(HardwareActa::class, 'Hw_Serial', 'Hw_Serial')->latest();
    }
}
