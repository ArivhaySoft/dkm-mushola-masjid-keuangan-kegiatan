<?php

namespace App\Exports;

use App\Models\Kategori;
use App\Models\Keuangan;
use App\Models\Rekening;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LaporanTahunanExport implements WithMultipleSheets
{
    public function __construct(protected int $tahun) {}

    public function sheets(): array
    {
        $sheets = [new RingkasanTahunanSheet($this->tahun)];

        // Sheet per kategori
        foreach (Kategori::all() as $kat) {
            $sheets[] = new DetailKategoriTahunanSheet($kat, $this->tahun);
        }

        return $sheets;
    }
}

// ── Sheet Ringkasan Tahunan ─────────────────────────────────────────────────
class RingkasanTahunanSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(protected int $tahun) {}

    public function title(): string { return 'Ringkasan ' . $this->tahun; }

    public function columnWidths(): array
    {
        return ['A' => 20, 'B' => 18, 'C' => 18, 'D' => 18, 'E' => 18];
    }

    public function array(): array
    {
        $rows = [];
        $rows[] = ['LAPORAN KEUANGAN TAHUNAN ' . $this->tahun . ' — MUSHOLA AL-IKHLAS', '', '', ''];
        $rows[] = ['Dicetak: ' . now()->isoFormat('D MMMM Y, HH:mm'), '', '', ''];
        $rows[] = ['', '', '', ''];

        // Per Bulan
        $rows[] = ['REKAP PER BULAN', '', '', '', ''];
        $rows[] = ['Bulan', 'Saldo Awal (Rp)', 'Masuk (Rp)', 'Keluar (Rp)', 'Saldo Akhir (Rp)'];

        $totalMasuk = $totalKeluar = 0;
        for ($m = 1; $m <= 12; $m++) {
            $startMonth = \Carbon\Carbon::create($this->tahun, $m, 1)->startOfMonth()->format('Y-m-d');
            $masuk  = Keuangan::whereYear('tanggal', $this->tahun)->whereMonth('tanggal', $m)->sum('masuk');
            $keluar = Keuangan::whereYear('tanggal', $this->tahun)->whereMonth('tanggal', $m)->sum('keluar');
            $saldoAwal = Keuangan::whereDate('tanggal', '<', $startMonth)
                ->selectRaw('COALESCE(SUM(masuk),0) - COALESCE(SUM(keluar),0) as saldo')
                ->value('saldo') ?? 0;
            $totalMasuk  += $masuk;
            $totalKeluar += $keluar;
            $bulanNama = \Carbon\Carbon::create($this->tahun, $m)->isoFormat('MMMM');
            $rows[] = [$bulanNama, $saldoAwal, $masuk ?: '', $keluar ?: '', $saldoAwal + $masuk - $keluar];
        }

        $rows[] = ['TOTAL', '', $totalMasuk, $totalKeluar, $totalMasuk - $totalKeluar];
        $rows[] = ['', '', '', '', ''];

        // Per Kategori
        $rows[] = ['REKAP PER KATEGORI', '', '', '', ''];
        $rows[] = ['Kategori', 'Saldo Awal (Rp)', 'Masuk (Rp)', 'Keluar (Rp)', 'Saldo Akhir (Rp)'];

        $startYear = \Carbon\Carbon::create($this->tahun, 1, 1)->startOfYear()->format('Y-m-d');
        $totKatSaldoAwal = $totKatMasuk = $totKatKeluar = $totKatSaldoAkhir = 0;

        foreach (Kategori::all() as $kat) {
            $masuk  = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereYear('tanggal', $this->tahun)->sum('masuk');
            $keluar = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereYear('tanggal', $this->tahun)->sum('keluar');
            $mIn    = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereDate('tanggal', '<', $startYear)->sum('masuk');
            $mOut   = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereDate('tanggal', '<', $startYear)->sum('keluar');
            $saldoAwal = $kat->saldo_awal + $mIn - $mOut;
            $saldoAkhir = $saldoAwal + $masuk - $keluar;

            $totKatSaldoAwal += $saldoAwal;
            $totKatMasuk += $masuk;
            $totKatKeluar += $keluar;
            $totKatSaldoAkhir += $saldoAkhir;

            $rows[] = [$kat->nama, $saldoAwal, $masuk, $keluar, $saldoAkhir];
        }

        $rows[] = ['TOTAL', $totKatSaldoAwal, $totKatMasuk, $totKatKeluar, $totKatSaldoAkhir];

        $rows[] = ['', '', '', '', ''];
        $rows[] = ['REKAP PER REKENING', '', '', '', ''];
        $rows[] = ['Rekening', 'Saldo Awal (Rp)', 'Masuk (Rp)', 'Keluar (Rp)', 'Saldo Akhir (Rp)'];

        $from = \Carbon\Carbon::create($this->tahun, 1, 1)->startOfYear()->format('Y-m-d');
        $to   = \Carbon\Carbon::create($this->tahun, 1, 1)->endOfYear()->format('Y-m-d');

        foreach (Rekening::all() as $rek) {
            $summary = $rek->reportBalanceSummary($from, $to);
            $rows[] = [$rek->nama_rek, $summary['saldo_awal'], $summary['masuk'], $summary['keluar'], $summary['saldo_akhir']];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '1b4d3a']]],
            4 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1b4d3a']], 'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]],
            5 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'e5e7eb']]],
        ];
    }
}

// ── Sheet Detail Per Kategori (Tahunan) ──────────────────────────────────────
class DetailKategoriTahunanSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        protected Kategori $kategori,
        protected int      $tahun
    ) {}

    public function title(): string
    {
        return mb_substr($this->kategori->nama, 0, 31);
    }

    public function columnWidths(): array
    {
        return ['A' => 6, 'B' => 14, 'C' => 38, 'D' => 18, 'E' => 18];
    }

    public function array(): array
    {
        $rows   = [];
        $rows[] = ['Detail ' . $this->kategori->nama . ' Tahun ' . $this->tahun, '', '', '', ''];
        $rows[] = ['', '', '', '', ''];
        $rows[] = ['No', 'Tanggal', 'Keterangan', 'Masuk (Rp)', 'Keluar (Rp)'];

        $transaksi = Keuangan::with('rekening')
            ->where('id_kategori', $this->kategori->id)
            ->withoutSaldoAwal()
            ->whereYear('tanggal', $this->tahun)
            ->orderBy('tanggal')
            ->get();

        $no = 1;
        foreach ($transaksi as $trx) {
            $rows[] = [
                $no++,
                $trx->tanggal->format('d/m/Y'),
                $trx->keterangan ?? '-',
                $trx->masuk  > 0 ? (float)$trx->masuk  : '',
                $trx->keluar > 0 ? (float)$trx->keluar : '',
            ];
        }

        $rows[] = ['', '', 'TOTAL', $transaksi->sum('masuk'), $transaksi->sum('keluar')];
        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            3 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'e5e7eb']]],
        ];
    }
}
