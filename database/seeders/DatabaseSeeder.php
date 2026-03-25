<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Rekening
        if (DB::table('rekening')->count() === 0) {
            DB::table('rekening')->insert([
                ['nama_rek' => 'Cash',     'atas_nama' => 'Cash',        'no_rek' => '1234',      'created_at' => now(), 'updated_at' => now()],
                ['nama_rek' => 'Bank BSI', 'atas_nama' => 'Arif Rahman', 'no_rek' => '123456789', 'created_at' => now(), 'updated_at' => now()],
                ['nama_rek' => 'DKM',      'atas_nama' => 'DKM',         'no_rek' => '123456677', 'created_at' => now(), 'updated_at' => now()],
            ]);
            $this->command->info('Rekening berhasil dibuat.');
        }

        // Kategori
        if (DB::table('kategori')->count() === 0) {
            DB::table('kategori')->insert([
                ['nama' => 'Kas Utama',            'saldo_awal' => 0, 'masuk' => 0, 'keluar' => 0, 'saldo_akhir' => 0, 'created_at' => now(), 'updated_at' => now()],
                ['nama' => 'Kas Qurban',           'saldo_awal' => 0, 'masuk' => 0, 'keluar' => 0, 'saldo_akhir' => 0, 'created_at' => now(), 'updated_at' => now()],
                ['nama' => 'Kas Renovasi Mushola', 'saldo_awal' => 0, 'masuk' => 0, 'keluar' => 0, 'saldo_akhir' => 0, 'created_at' => now(), 'updated_at' => now()],
            ]);
            $this->command->info('Kategori berhasil dibuat.');
        }

        // Roles
        if (DB::table('roles')->count() === 0) {
            DB::table('roles')->insert([
                ['name' => 'admin',     'label' => 'Administrator', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'bendahara','label' => 'Bendahara',     'created_at' => now(), 'updated_at' => now()],
                ['name' => 'editor',   'label' => 'Editor',        'created_at' => now(), 'updated_at' => now()],
                ['name' => 'viewer',   'label' => 'Viewer',        'created_at' => now(), 'updated_at' => now()],
            ]);
            $this->command->info('Roles berhasil dibuat.');
        }

        // Jenis Kegiatan
        if (DB::table('jenis_kegiatan')->count() === 0) {
            DB::table('jenis_kegiatan')->insert([
                ['nama' => 'Pengajian', 'warna' => 'primary', 'created_at' => now(), 'updated_at' => now()],
                ['nama' => 'Dakwah',    'warna' => 'yellow',  'created_at' => now(), 'updated_at' => now()],
                ['nama' => 'Lainnya',   'warna' => 'gray',    'created_at' => now(), 'updated_at' => now()],
            ]);
            $this->command->info('Jenis kegiatan berhasil dibuat.');
        }

        // Jadwal Pengajian
        if (DB::table('jadwal_pengajian')->count() === 0) {
            DB::table('jadwal_pengajian')->insert([
                [
                    'nama'        => 'Pengajian Rutin Ba\'da Maghrib',
                    'ustadz'      => null,
                    'frekuensi'   => '2_minggu',
                    'tanggal_mulai' => '2026-01-05',
                    'hari'        => 'Minggu',
                    'jam_mulai'   => '18:30',
                    'jam_selesai' => '20:00',
                    'lokasi'      => null,
                    'keterangan'  => 'Kajian rutin setiap 2 minggu sekali',
                    'aktif'       => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'nama'        => 'Pengajian Bulanan',
                    'ustadz'      => null,
                    'frekuensi'   => 'bulanan',
                    'tanggal_mulai' => '2026-01-04',
                    'hari'        => 'Minggu',
                    'jam_mulai'   => '08:00',
                    'jam_selesai' => '10:00',
                    'lokasi'      => null,
                    'keterangan'  => 'Kajian bulanan setiap minggu pertama awal bulan',
                    'aktif'       => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
            ]);
            $this->command->info('Jadwal Pengajian berhasil dibuat.');
        }

        $this->command->newLine();
        $this->command->info('Setup dasar selesai!');
        $this->command->info('Untuk data contoh: php artisan db:seed --class=DemoSeeder');
    }
}
