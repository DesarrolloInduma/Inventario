<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Propietario extends Model
{
    protected $table = 'Propietario';
    protected $primaryKey = 'PropietarioID';
    public $timestamps = false;

    protected $guarded = [];
}
