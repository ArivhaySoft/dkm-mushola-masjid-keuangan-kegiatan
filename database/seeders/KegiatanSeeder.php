<?php

namespace Database\Seeders;

use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Database\Seeder;

class KegiatanSeeder extends Seeder
{
    /**
     * Jalankan: php artisan db:seed --class=KegiatanSeeder
     */
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            $this->command->warn('Tidak ada user. Buat user terlebih dahulu.');
            return;
        }

        $kegiatan = [
            [
                'judul'            => 'Pengajian Rutin Malam Jumat',
                'jenis'            => 'pengajian',
                'konten'           => "Alhamdulillah, pengajian rutin malam Jumat berjalan dengan lancar dan penuh keberkahan. Kajian kali ini membahas tentang keutamaan sholat berjamaah dan pentingnya menjaga silaturahmi antar sesama muslim.\n\nUstadz Ahmad Fauzi menyampaikan tausiyah dengan penuh semangat. Beliau mengutip hadits Nabi SAW tentang keutamaan orang yang menjaga sholat lima waktu berjamaah di masjid.\n\nHadirin yang hadir sangat antusias dan banyak yang mengajukan pertanyaan. Kegiatan diakhiri dengan doa bersama dan ramah tamah.",
                'tanggal_kegiatan' => now()->subDays(7)->setTime(19, 30),
                'lokasi'           => 'Mushola Al-Ikhlas, Jl. Mawar No. 12',
            ],
            [
                'judul'            => 'Bersih-Bersih Mushola Bersama',
                'jenis'            => 'lainnya',
                'konten'           => "Kegiatan bersih-bersih mushola dilaksanakan bersama-sama oleh warga sekitar. Kegiatan ini rutin dilaksanakan setiap bulan untuk menjaga kebersihan dan kenyamanan tempat ibadah.\n\nSebanyak 25 warga turut berpartisipasi. Kegiatan meliputi membersihkan karpet, mengepel lantai, membersihkan toilet, dan merapikan taman.\n\nBarakAllah untuk semua yang telah berpartisipasi. Semoga mushola kita selalu bersih dan nyaman untuk beribadah.",
                'tanggal_kegiatan' => now()->subDays(14)->setTime(7, 0),
                'lokasi'           => 'Mushola Al-Ikhlas',
            ],
            [
                'judul'            => 'Kajian Fiqih Ramadhan',
                'jenis'            => 'dakwah',
                'konten'           => "Menyambut bulan suci Ramadhan, mushola Al-Ikhlas menyelenggarakan kajian fiqih khusus membahas hukum-hukum seputar puasa, sholat tarawih, zakat fitrah, dan amalan-amalan yang dianjurkan di bulan Ramadhan.\n\nKajian dipimpin oleh Ustadz Dr. Hasan Basri, M.Ag. yang menyampaikan materi dengan gamblang dan mudah dipahami oleh seluruh jamaah.\n\nAlhamdulillah, kajian dihadiri lebih dari 80 jamaah. Semoga ilmu yang kita dapatkan bermanfaat dan menjadikan ibadah kita di bulan Ramadhan semakin berkah.",
                'tanggal_kegiatan' => now()->subDays(3)->setTime(20, 0),
                'lokasi'           => 'Mushola Al-Ikhlas',
            ],
            [
                'judul'            => 'Santunan Anak Yatim',
                'jenis'            => 'lainnya',
                'konten'           => "Alhamdulillah, mushola Al-Ikhlas berhasil mengumpulkan dana dan menyalurkan santunan kepada 15 anak yatim di lingkungan sekitar mushola. Kegiatan ini merupakan wujud kepedulian sosial masyarakat sekitar mushola.\n\nSetiap anak yatim menerima santunan berupa uang tunai dan perlengkapan sekolah. Semoga bantuan yang diberikan dapat meringankan beban para anak yatim dan keluarganya.\n\nTerima kasih kepada seluruh donatur yang telah berpartisipasi. Semoga Allah membalas kebaikan kalian dengan berlipat ganda.",
                'tanggal_kegiatan' => now()->subDays(21)->setTime(10, 0),
                'lokasi'           => 'Mushola Al-Ikhlas',
            ],
            [
                'judul'            => 'Pelatihan Tajwid untuk Remaja',
                'jenis'            => 'pengajian',
                'konten'           => "Program pelatihan tajwid untuk remaja masjid dimulai hari ini. Program ini diperuntukkan bagi remaja berusia 12-18 tahun yang ingin memperbaiki bacaan Al-Quran mereka.\n\nPertemuan perdana dihadiri oleh 20 remaja yang antusias. Ustadzah Nisa Rahmawati sebagai pembimbing memulai dengan mengajarkan hukum nun mati dan tanwin.\n\nProgram ini akan berlangsung setiap Sabtu dan Minggu selama 3 bulan ke depan. Semoga program ini menghasilkan generasi muda yang fasih membaca Al-Quran.",
                'tanggal_kegiatan' => now()->addDays(2)->setTime(9, 0),
                'lokasi'           => 'Mushola Al-Ikhlas - Ruang Belajar',
            ],
        ];

        foreach ($kegiatan as $k) {
            Kegiatan::create(array_merge($k, ['created_by' => $user->id]));
        }

        $this->command->info('✅ Data kegiatan demo berhasil dibuat! (' . count($kegiatan) . ' kegiatan)');
    }
}
