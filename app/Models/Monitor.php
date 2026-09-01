<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Monitor extends Model
{
    protected $table = 'Monitor';
    protected $primaryKey = 'MonitorID';
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];
}

