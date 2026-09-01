<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tipo_Software extends Model
{
    protected $table = 'Tipo_Software';
    protected $primaryKey = 'Soft_Tipo_Id';
    public $timestamps = false;

    protected $guarded = [];
}
