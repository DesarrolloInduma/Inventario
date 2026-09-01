<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispositivo extends Model
{
    protected $table = 'Dispositivo';
    protected $primaryKey = 'Dispositivo_Codigo';
    public $timestamps = false;

    protected $guarded = [];
}
