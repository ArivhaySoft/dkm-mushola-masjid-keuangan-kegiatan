<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HistoryLaporan extends Model
{
    protected $table = 'history_laporan';

    protected $fillable = [
        'tanggal_dari', 'tanggal_sampai',
        'saldo_awal', 'masuk', 'keluar', 'saldo_akhir',
        'created_by',
    ];

    protected $casts = [
        'tanggal_dari'   => 'date',
        'tanggal_sampai' => 'date',
        'saldo_awal'     => 'decimal:2',
        'masuk'          => 'decimal:2',
        'keluar'         => 'decimal:2',
        'saldo_akhir'    => 'decimal:2',
    ];

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function kategoriDetails(): HasMany
    {
        return $this->hasMany(HistoryLaporanKategori::class, 'history_laporan_id');
    }

    public function rekeningDetails(): HasMany
    {
        return $this->hasMany(HistoryLaporanRekening::class, 'history_laporan_id');
    }
}
