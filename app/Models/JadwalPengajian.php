<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class JadwalPengajian extends Model
{
    protected $table = 'jadwal_pengajian';

    protected $fillable = [
        'nama', 'ustadz', 'frekuensi', 'tanggal_mulai', 'hari',
        'jam_mulai', 'jam_selesai', 'lokasi', 'keterangan', 'aktif',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'jam_mulai'     => 'datetime:H:i',
        'jam_selesai'   => 'datetime:H:i',
        'aktif'         => 'boolean',
    ];

    /**
     * Hitung tanggal pengajian terakhir (sudah lewat / hari ini).
     */
    public function getTanggalTerakhirAttribute(): ?Carbon
    {
        $next = $this->tanggal_berikutnya;
        if (!$next) return null;

        if ($this->frekuensi === 'bulanan') {
            // Mundur 1 bulan ke minggu pertama bulan sebelumnya
            $hariTarget = $this->hariToCarbon($this->hari);
            $prevMonth = $next->copy()->subMonthNoOverflow()->startOfMonth();
            while ($prevMonth->dayOfWeek !== $hariTarget) {
                $prevMonth->addDay();
            }
            return $prevMonth->day <= 7 ? $prevMonth : null;
        }

        // 2 mingguan: mundur 2 minggu dari next
        return $next->copy()->subWeeks(2);
    }

    /**
     * Hitung tanggal pengajian berikutnya (>= hari ini).
     */
    public function getTanggalBerikutnyaAttribute(): ?Carbon
    {
        $hariTarget = $this->hariToCarbon($this->hari);
        if ($hariTarget === null) return null;

        $now = Carbon::today();

        if ($this->frekuensi === 'bulanan') {
            return $this->nextFirstWeekDay($hariTarget, $now);
        }

        // 2 minggu sekali — hitung dari tanggal_mulai
        $start = $this->tanggal_mulai ? $this->tanggal_mulai->copy() : $now->copy();

        // Pastikan start jatuh di hari yang benar
        while ($start->dayOfWeek !== $hariTarget) {
            $start->addDay();
        }

        // Maju per 2 minggu sampai >= hari ini
        while ($start->lt($now)) {
            $start->addWeeks(2);
        }

        return $start;
    }

    private function nextFirstWeekDay(int $dayOfWeek, Carbon $from): Carbon
    {
        // Cek bulan ini dulu
        $firstOfMonth = $from->copy()->startOfMonth();
        $candidate = $firstOfMonth->copy();

        // Maju ke hari target di minggu pertama
        while ($candidate->dayOfWeek !== $dayOfWeek) {
            $candidate->addDay();
        }

        // Jika masih di minggu pertama (tanggal <= 7) dan belum lewat
        if ($candidate->day <= 7 && $candidate->gte($from->copy()->startOfDay())) {
            return $candidate;
        }

        // Pindah ke bulan depan
        $firstOfNext = $from->copy()->addMonthNoOverflow()->startOfMonth();
        while ($firstOfNext->dayOfWeek !== $dayOfWeek) {
            $firstOfNext->addDay();
        }

        return $firstOfNext;
    }

    private function hariToCarbon(string $hari): ?int
    {
        return match (strtolower($hari)) {
            'minggu'  => Carbon::SUNDAY,
            'senin'   => Carbon::MONDAY,
            'selasa'  => Carbon::TUESDAY,
            'rabu'    => Carbon::WEDNESDAY,
            'kamis'   => Carbon::THURSDAY,
            'jumat'   => Carbon::FRIDAY,
            'sabtu'   => Carbon::SATURDAY,
            default   => null,
        };
    }
}
