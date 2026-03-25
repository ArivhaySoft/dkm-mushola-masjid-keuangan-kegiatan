<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KegiatanFoto extends Model
{
    protected $table = 'kegiatan_fotos';

    protected $fillable = ['kegiatan_id', 'path', 'media_type', 'is_headline', 'sort_order'];

    protected $casts = [
        'is_headline' => 'boolean',
    ];

    public function isImage(): bool
    {
        return $this->media_type === 'image';
    }

    public function isVideo(): bool
    {
        return $this->media_type === 'video';
    }

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class);
    }
}
