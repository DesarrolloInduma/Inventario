<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Software_NoLicenciado extends Model
{
    protected $table = 'Software_NoLicenciado';
    protected $primaryKey = 'Softwarenl_Id';
    public $timestamps = false;

    protected $guarded = [];

    public function tipoSoftware()
    {
        return $this->belongsTo(Tipo_Software::class, 'Soft_Tipo_Id', 'Soft_Tipo_Id');
    }

    public function hardwares()
    {
        return $this->belongsToMany(
            Hardware::class,
            'Hard_Soft_Nl',
            'Softwarenl_Id',
            'Hw_Serial'
        );
    }
}
