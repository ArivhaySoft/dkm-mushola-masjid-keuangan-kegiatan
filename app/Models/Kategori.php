<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $table = 'kategori';
    protected $fillable = ['nama', 'saldo_awal', 'masuk', 'keluar', 'saldo_akhir'];

    protected $casts = [
        'saldo_awal'  => 'decimal:2',
        'masuk'       => 'decimal:2',
        'keluar'      => 'decimal:2',
        'saldo_akhir' => 'decimal:2',
    ];

    public function keuangan()
    {
        return $this->hasMany(Keuangan::class, 'id_kategori');
    }

    public function recalculate(): void
    {
        $masuk = $this->keuangan()
            ->withoutSaldoAwal()
            ->sum('masuk');

        $keluar = $this->keuangan()
            ->withoutSaldoAwal()
            ->sum('keluar');

        $this->update([
            'masuk'       => $masuk,
            'keluar'      => $keluar,
            'saldo_akhir' => $this->saldo_awal + $masuk - $keluar,
        ]);
    }
}
