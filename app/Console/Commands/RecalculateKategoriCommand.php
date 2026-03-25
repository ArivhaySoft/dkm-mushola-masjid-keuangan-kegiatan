<?php

namespace App\Console\Commands;

use App\Models\Kategori;
use Illuminate\Console\Command;

class RecalculateKategoriCommand extends Command
{
    protected $signature   = 'mushola:recalculate';
    protected $description = 'Hitung ulang saldo semua kategori';

    public function handle(): void
    {
        $kategoriList = Kategori::all();

        foreach ($kategoriList as $kat) {
            $kat->recalculate();
            $this->info("✅ {$kat->nama} → Saldo: Rp " . number_format($kat->saldo_akhir, 0, ',', '.'));
        }

        $this->info("\n✅ Selesai menghitung ulang " . $kategoriList->count() . " kategori.");
    }
}
