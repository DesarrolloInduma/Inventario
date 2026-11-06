<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MantenimientoFoto extends Model
{
    protected $table = 'MantenimientoFoto';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'tamanio' => 'integer',
    ];

    public function mantenimiento()
    {
        return $this->belongsTo(Mantenimiento::class, 'mantenimiento_Id', 'mantenimiento_Id');
    }

    public function urlPublica(): string
    {
        return asset('storage/'.$this->archivo_path);
    }

    public function rutaAbsoluta(): ?string
    {
        $public = storage_path('app/public/'.$this->archivo_path);
        if (is_file($public)) {
            return $public;
        }

        $legacy = storage_path('app/'.$this->archivo_path);

        return is_file($legacy) ? $legacy : null;
    }

    public function asBase64DataUri(): ?string
    {
        $path = $this->rutaAbsoluta();
        if (! $path) {
            return null;
        }

        $mime = $this->mime_type ?: (mime_content_type($path) ?: 'image/jpeg');
        $data = base64_encode((string) file_get_contents($path));

        return "data:{$mime};base64,{$data}";
    }

    protected static function booted(): void
    {
        static::deleting(function (MantenimientoFoto $foto) {
            if ($foto->archivo_path) {
                Storage::disk('public')->delete($foto->archivo_path);
            }
        });
    }
}
