<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tipo extends Model
{
    protected $table = 'Tipo';
    protected $primaryKey = 'TipoID';
    public $timestamps = false;

    protected $guarded = [];
}
