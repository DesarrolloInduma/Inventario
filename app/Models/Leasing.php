<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leasing extends Model
{
    protected $table = 'Leasing';
    protected $primaryKey = 'LeasingID';
    public $timestamps = false;

    protected $guarded = [];
}
