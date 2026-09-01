<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Procesador extends Model
{
    protected $table = 'Procesador';
    protected $primaryKey = 'ProcesadorID';
    public $timestamps = false;

    protected $guarded = [];
}
