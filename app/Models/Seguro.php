<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seguro extends Model
{
    protected $table = 'Seguro';
    protected $primaryKey = 'SeguroID';
    public $timestamps = false;

    protected $guarded = [];
}
