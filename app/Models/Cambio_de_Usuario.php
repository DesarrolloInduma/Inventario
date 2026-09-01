<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cambio_de_Usuario extends Model
{
    protected $table = 'Cambio_de_Usuario';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'FechaCambio' => 'date',
    ];
}
