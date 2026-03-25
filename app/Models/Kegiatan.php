<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    protected $table    = 'kegiatan';
    protected $fillable = [
        'judul', 'jenis', 'konten',
        'tanggal_kegiatan', 'lokasi', 'created_by',
    ];

    protected $casts = [
        'tanggal_kegiatan' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fotos()
    {
        return $this->hasMany(KegiatanFoto::class)->orderBy('sort_order');
    }

    public function medias()
    {
        return $this->fotos();
    }

    public function imageMedias()
    {
        return $this->hasMany(KegiatanFoto::class)->where('media_type', 'image')->orderBy('sort_order');
    }

    public function videoMedias()
    {
        return $this->hasMany(KegiatanFoto::class)->where('media_type', 'video')->orderBy('sort_order');
    }

    public function headline()
    {
        return $this->hasOne(KegiatanFoto::class)->where('is_headline', true);
    }

    public function getHeadlineFotoAttribute(): ?string
    {
        $h = $this->headline;
        return $h ? asset('storage/' . $h->path) : null;
    }

    public function getDisplayMediaAttribute(): ?KegiatanFoto
    {
        if ($this->relationLoaded('fotos')) {
            $medias = $this->fotos;
            $headline = $medias->firstWhere('is_headline', true);
            if ($headline) {
                return $headline;
            }

            $image = $medias->firstWhere('media_type', 'image');
            return $image ?? $medias->first();
        }

        $headline = $this->fotos()->where('is_headline', true)->first();
        if ($headline) {
            return $headline;
        }

        return $this->fotos()->where('media_type', 'image')->first() ?? $this->fotos()->first();
    }

    public function jenisKegiatan()
    {
        return $this->belongsTo(JenisKegiatan::class, 'jenis', 'nama');
    }

    public function getJenisBadgeClassAttribute(): string
    {
        $warna = $this->jenisKegiatan?->warna ?? 'gray';

        return match($warna) {
            'primary' => 'bg-primary-100 text-primary-700',
            'yellow'  => 'bg-yellow-100 text-yellow-700',
            'blue'    => 'bg-blue-100 text-blue-700',
            'red'     => 'bg-red-100 text-red-700',
            'purple'  => 'bg-purple-100 text-purple-700',
            'pink'    => 'bg-pink-100 text-pink-700',
            'orange'  => 'bg-orange-100 text-orange-700',
            default   => 'bg-gray-100 text-gray-600',
        };
    }
}
