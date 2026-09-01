<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mantenimineto extends Model
{
    protected $table = 'Mantenimineto';
    protected $primaryKey = 'mantenimiento_Id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'mantenimiento_fecha' => 'date',
        'mantenimiento_RFE_C' => 'boolean',
        'mantenimiento_ST_C' => 'boolean',
        'mantenimiento_STYM_C' => 'boolean',
        'mantenimiento_LP_C' => 'boolean',
        'mantenimiento_LTYM_C' => 'boolean',
        'mantenimiento_LM_C' => 'boolean',
        'mantenimiento_O_C' => 'boolean',
    ];

    public function hardware()
    {
        return $this->belongsTo(Hardware::class, 'Hw_Serial', 'Hw_Serial');
    }
}
