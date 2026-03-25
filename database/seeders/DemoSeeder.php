<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Keuangan;
use App\Models\Rekening;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    /**
     * Jalankan: php artisan db:seed --class=DemoSeeder
     * Mengisi data contoh untuk demo / testing
     */
    public function run(): void
    {
        // Pastikan data master sudah ada
        if (Rekening::count() === 0 || Kategori::count() === 0) {
            $this->call(DatabaseSeeder::class);
        }

        $rekening = Rekening::all();
        $kategori = Kategori::all();

        // Buat user demo jika belum ada
        $user = User::firstOrCreate(
            ['email' => 'demo@mushola.com'],
            ['name' => 'Demo Admin', 'google_id' => 'demo123']
        );

        $bulan = now();
        $transaksiContoh = [
            // Kas Utama - infaq & pengeluaran rutin
            ['keterangan' => 'Infaq Jumat', 'masuk' => 850000, 'rek' => 'Cash', 'kat' => 'Kas Utama'],
            ['keterangan' => 'Infaq Jumat', 'masuk' => 920000, 'rek' => 'Cash', 'kat' => 'Kas Utama'],
            ['keterangan' => 'Donasi Online', 'masuk' => 500000, 'rek' => 'Bank BSI', 'kat' => 'Kas Utama'],
            ['keterangan' => 'Pembelian Sabun & Perlengkapan Kebersihan', 'keluar' => 125000, 'rek' => 'Cash', 'kat' => 'Kas Utama'],
            ['keterangan' => 'Listrik Mushola Bulan Ini', 'keluar' => 250000, 'rek' => 'Bank BSI', 'kat' => 'Kas Utama'],
            ['keterangan' => 'Honorarium Marbot', 'keluar' => 300000, 'rek' => 'Cash', 'kat' => 'Kas Utama'],
            ['keterangan' => 'Pembelian Air Galon', 'keluar' => 45000, 'rek' => 'Cash', 'kat' => 'Kas Utama'],
            ['keterangan' => 'Infaq Sholat Tarawih', 'masuk' => 1200000, 'rek' => 'Cash', 'kat' => 'Kas Utama'],

            // Kas Qurban
            ['keterangan' => 'Setoran Qurban Pak Ahmad', 'masuk' => 2500000, 'rek' => 'Bank BSI', 'kat' => 'Kas Qurban'],
            ['keterangan' => 'Setoran Qurban Ibu Sari', 'masuk' => 2500000, 'rek' => 'Bank BSI', 'kat' => 'Kas Qurban'],
            ['keterangan' => 'Setoran Qurban Keluarga Budi', 'masuk' => 5000000, 'rek' => 'Bank BSI', 'kat' => 'Kas Qurban'],
            ['keterangan' => 'Pembelian Hewan Qurban 2 Ekor Kambing', 'keluar' => 4500000, 'rek' => 'Bank BSI', 'kat' => 'Kas Qurban'],

            // Kas Renovasi
            ['keterangan' => 'Donasi Renovasi dari Pak Haji Umar', 'masuk' => 5000000, 'rek' => 'DKM', 'kat' => 'Kas Renovasi Mushola'],
            ['keterangan' => 'Donasi Renovasi Ibu Hajjah Fatimah', 'masuk' => 3000000, 'rek' => 'DKM', 'kat' => 'Kas Renovasi Mushola'],
            ['keterangan' => 'Pembelian Semen & Pasir', 'keluar' => 1800000, 'rek' => 'DKM', 'kat' => 'Kas Renovasi Mushola'],
            ['keterangan' => 'Upah Tukang (3 hari)', 'keluar' => 900000, 'rek' => 'Cash', 'kat' => 'Kas Renovasi Mushola'],
            ['keterangan' => 'Pembelian Cat Tembok', 'keluar' => 450000, 'rek' => 'Cash', 'kat' => 'Kas Renovasi Mushola'],
        ];

        foreach ($transaksiContoh as $i => $t) {
            $rek = $rekening->firstWhere('nama_rek', $t['rek']);
            $kat = $kategori->firstWhere('nama', $t['kat']);

            if (!$rek || !$kat) continue;

            Keuangan::create([
                'masuk'       => $t['masuk']  ?? 0,
                'keluar'      => $t['keluar'] ?? 0,
                'keterangan'  => $t['keterangan'],
                'id_rekening' => $rek->id,
                'id_kategori' => $kat->id,
                'created_by'  => $user->id,
                'tanggal'     => $bulan->copy()->subDays(rand(0, 28))->format('Y-m-d'),
            ]);
        }

        // Recalculate all kategori saldo
        Kategori::all()->each(fn ($k) => $k->recalculate());

        $this->command->info('✅ Demo data berhasil dibuat!');
        $this->command->info('   Total transaksi: ' . count($transaksiContoh));
    }
}
