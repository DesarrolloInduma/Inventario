<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioInv extends Model
{
    protected $table = 'UsuarioInv';
    protected $primaryKey = 'UsuarioInvID';
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];
}

