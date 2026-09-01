<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'Auditoria';
    protected $primaryKey = 'IdAuditoria';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'FechaAuditoria' => 'datetime',
    ];
}
