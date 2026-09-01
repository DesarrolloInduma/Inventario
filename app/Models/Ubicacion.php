<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    protected $table = 'Ubicacion';
    protected $primaryKey = 'UbicacionId';
    public $timestamps = false;

    protected $guarded = [];
}
