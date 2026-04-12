<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoryLaporanRekening extends Model
{
    protected $table = 'history_laporan_rekening';

    protected $fillable = [
        'history_laporan_id',
        'rekening_id',
        'saldo_awal',
        'masuk',
        'keluar',
        'saldo_akhir',
    ];

    protected $casts = [
        'saldo_awal'  => 'decimal:2',
        'masuk'       => 'decimal:2',
        'keluar'      => 'decimal:2',
        'saldo_akhir' => 'decimal:2',
    ];

    public function historyLaporan(): BelongsTo
    {
        return $this->belongsTo(HistoryLaporan::class, 'history_laporan_id');
    }

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(Rekening::class, 'rekening_id');
    }
}
