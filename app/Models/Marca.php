<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    protected $table = 'Marca';
    protected $primaryKey = 'MarcaID';
    public $timestamps = false;

    protected $guarded = [];
}
