<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HardwareActa extends Model
{
    protected $table = 'hardware_actas';

    protected $guarded = [];

    public function hardware()
    {
        return $this->belongsTo(Hardware::class, 'Hw_Serial', 'Hw_Serial');
    }

    public function getTamanioFormateadoAttribute(): string
    {
        $bytes = $this->tamanio ?? 0;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    public function getIconoAttribute(): string
    {
        $mime = strtolower((string) $this->mime_type);
        $ext = strtolower(pathinfo($this->nombre_original, PATHINFO_EXTENSION));

        if (str_contains($mime, 'pdf') || $ext === 'pdf') {
            return 'bi-filetype-pdf text-red-500';
        }
        if (str_contains($mime, 'image') || in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif'])) {
            return 'bi-file-earmark-image text-blue-500';
        }
        if (str_contains($mime, 'word') || in_array($ext, ['doc', 'docx'])) {
            return 'bi-filetype-docx text-indigo-500';
        }
        return 'bi-file-earmark-text text-slate-500';
    }
}
