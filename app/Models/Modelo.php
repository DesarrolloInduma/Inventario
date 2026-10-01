<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modelo extends Model
{
    protected $table = 'Modelo';
    protected $primaryKey = 'ModeloID';
    public $timestamps = false;

    protected $guarded = [];

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'MarcaID', 'MarcaID');
    }

    public function hardwares()
    {
        return $this->hasMany(Hardware::class, 'ModeloID', 'ModeloID');
    }
}
