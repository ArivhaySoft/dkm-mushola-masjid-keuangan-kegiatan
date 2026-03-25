<?php
// =========================================================
// App\Models\Rekening
// =========================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rekening extends Model
{
    protected $table = 'rekening';
    protected $fillable = ['nama_rek', 'atas_nama', 'no_rek'];

    public function keuangan()
    {
        return $this->hasMany(Keuangan::class, 'id_rekening');
    }

    public function transferMasuk()
    {
        return $this->hasMany(TransferRekening::class, 'ke_rekening');
    }

    public function transferKeluar()
    {
        return $this->hasMany(TransferRekening::class, 'dari_rekening');
    }

    public function calculateSaldoSebelum(?string $tanggal): float
    {
        if (!$tanggal) {
            return 0;
        }

        $keuanganMasuk = $this->keuangan()->whereDate('tanggal', '<', $tanggal)->sum('masuk');
        $keuanganKeluar = $this->keuangan()->whereDate('tanggal', '<', $tanggal)->sum('keluar');
        $transferMasuk = $this->transferMasuk()->whereDate('tanggal', '<', $tanggal)->sum('jumlah');
        $transferKeluar = $this->transferKeluar()->whereDate('tanggal', '<', $tanggal)->sum('jumlah');

        return (float) ($keuanganMasuk + $transferMasuk - $keuanganKeluar - $transferKeluar);
    }

    public function calculateMutasi(?string $from = null, ?string $to = null): array
    {
        $keuanganMasuk = $this->keuangan()
            ->when($from, fn($q) => $q->whereDate('tanggal', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('tanggal', '<=', $to))
            ->sum('masuk');

        $keuanganKeluar = $this->keuangan()
            ->when($from, fn($q) => $q->whereDate('tanggal', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('tanggal', '<=', $to))
            ->sum('keluar');

        $transferMasuk = $this->transferMasuk()
            ->when($from, fn($q) => $q->whereDate('tanggal', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('tanggal', '<=', $to))
            ->sum('jumlah');

        $transferKeluar = $this->transferKeluar()
            ->when($from, fn($q) => $q->whereDate('tanggal', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('tanggal', '<=', $to))
            ->sum('jumlah');

        return [
            'masuk' => (float) ($keuanganMasuk + $transferMasuk),
            'keluar' => (float) ($keuanganKeluar + $transferKeluar),
        ];
    }

    public function reportBalanceSummary(?string $from = null, ?string $to = null): array
    {
        $mutasi = $this->calculateMutasi($from, $to);
        $saldoAwal = $this->calculateSaldoSebelum($from);

        return [
            'saldo_awal' => $saldoAwal,
            'masuk' => $mutasi['masuk'],
            'keluar' => $mutasi['keluar'],
            'saldo_akhir' => $saldoAwal + $mutasi['masuk'] - $mutasi['keluar'],
        ];
    }

    public function getSaldoAttribute()
    {
        $mutasi = $this->calculateMutasi();

        return $mutasi['masuk'] - $mutasi['keluar'];
    }
}
