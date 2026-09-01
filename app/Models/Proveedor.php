<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'Proveedor';
    protected $primaryKey = 'ProveedorID';
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];
}

