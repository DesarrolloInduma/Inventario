<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Observaciones extends Model
{
    protected $table = 'Observaciones';
    protected $primaryKey = 'Observacion_Id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'Observacion_Fecha' => 'date',
    ];
}
