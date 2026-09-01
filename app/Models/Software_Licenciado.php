<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Software_Licenciado extends Model
{
    protected $table = 'Software_Licenciado';
    protected $primaryKey = 'Software_Id';
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
            'Hard_Soft',
            'Software_Id',
            'Hw_Serial'
        );
    }
}
