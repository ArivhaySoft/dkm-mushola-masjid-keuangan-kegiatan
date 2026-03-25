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

class LaporanBulananExport implements WithMultipleSheets
{
    public function __construct(
        protected string $from,
        protected string $to,
        protected string $bulanLabel
    ) {}

    public function sheets(): array
    {
        $sheets = [new RingkasanSheet($this->from, $this->to, $this->bulanLabel)];

        $kategoriList = Kategori::all();
        foreach ($kategoriList as $kat) {
            $count = Keuangan::where('id_kategori', $kat->id)
                ->whereBetween('tanggal', [$this->from, $this->to])
                ->count();
            if ($count > 0) {
                $sheets[] = new DetailKategoriSheet($kat, $this->from, $this->to);
            }
        }

        return $sheets;
    }
}

// ─── Sheet Ringkasan ────────────────────────────────────────────────────────
class RingkasanSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        protected string $from,
        protected string $to,
        protected string $bulanLabel
    ) {}

    public function title(): string { return 'Ringkasan'; }

    public function columnWidths(): array
    {
        return ['A' => 30, 'B' => 20, 'C' => 20, 'D' => 20, 'E' => 20];
    }

    public function array(): array
    {
        $rows = [];
        $rows[] = ['LAPORAN KEUANGAN MUSHOLA AL-IKHLAS', '', '', '', ''];
        $rows[] = ['Periode: ' . $this->bulanLabel, '', '', '', ''];
        $rows[] = ['Dicetak: ' . now()->format('d/m/Y H:i'), '', '', '', ''];
        $rows[] = ['', '', '', '', ''];

        // Kategori
        $rows[] = ['RINGKASAN PER KATEGORI', '', '', '', ''];
        $rows[] = ['Kategori', 'Saldo Awal', 'Masuk', 'Keluar', 'Saldo Akhir'];

        $kategoriList = Kategori::all();
        $totSaldoAwal = $totMasuk = $totKeluar = $totSaldo = 0;

        foreach ($kategoriList as $kat) {
            $masuk  = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereBetween('tanggal', [$this->from, $this->to])->sum('masuk');
            $keluar = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereBetween('tanggal', [$this->from, $this->to])->sum('keluar');
            $mIn    = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereDate('tanggal', '<', $this->from)->sum('masuk');
            $mOut   = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereDate('tanggal', '<', $this->from)->sum('keluar');
            $saldoAwal = $kat->saldo_awal + $mIn - $mOut;

            $totSaldoAwal += $saldoAwal;
            $totMasuk  += $masuk;
            $totKeluar += $keluar;
            $totSaldo  += ($saldoAwal + $masuk - $keluar);

            $rows[] = [$kat->nama, $saldoAwal, $masuk, $keluar, $saldoAwal + $masuk - $keluar];
        }

        $rows[] = ['TOTAL', $totSaldoAwal, $totMasuk, $totKeluar, $totSaldo];
        $rows[] = ['', '', '', '', ''];

        // Rekening
        $rows[] = ['SALDO PER REKENING', '', '', '', ''];
        $rows[] = ['Rekening', 'Atas Nama', 'Masuk', 'Keluar', 'Saldo Akhir'];

        foreach (Rekening::all() as $rek) {
            $summary = $rek->reportBalanceSummary($this->from, $this->to);
            $rows[] = [$rek->nama_rek, $rek->atas_nama, $summary['masuk'], $summary['keluar'], $summary['saldo_akhir']];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            5 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1b4d3a']], 'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]],
            6 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'e5e7eb']]],
        ];
    }
}

// ─── Sheet Detail Per Kategori ───────────────────────────────────────────────
class DetailKategoriSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        protected Kategori $kategori,
        protected string $from,
        protected string $to
    ) {}

    public function title(): string
    {
        return mb_substr($this->kategori->nama, 0, 31); // max 31 chars for sheet name
    }

    public function columnWidths(): array
    {
        return ['A' => 6, 'B' => 14, 'C' => 40, 'D' => 20, 'E' => 20];
    }

    public function array(): array
    {
        $rows   = [];
        $rows[] = ['Detail Transaksi: ' . $this->kategori->nama, '', '', '', ''];
        $rows[] = ['Periode: ' . \Carbon\Carbon::parse($this->from)->format('d/m/Y') . ' – ' . \Carbon\Carbon::parse($this->to)->format('d/m/Y'), '', '', '', ''];
        $rows[] = ['', '', '', '', ''];
        $rows[] = ['No', 'Tanggal', 'Keterangan', 'Masuk (Rp)', 'Keluar (Rp)'];

        $transaksi = Keuangan::with('rekening')
            ->where('id_kategori', $this->kategori->id)
            ->withoutSaldoAwal()
            ->whereBetween('tanggal', [$this->from, $this->to])
            ->orderBy('tanggal')
            ->get();

        $no = 1;
        foreach ($transaksi as $trx) {
            $rows[] = [
                $no++,
                $trx->tanggal->format('d/m/Y'),
                $trx->keterangan ?? '-',
                $trx->masuk  > 0 ? $trx->masuk  : '',
                $trx->keluar > 0 ? $trx->keluar : '',
            ];
        }

        $rows[] = ['', '', 'TOTAL', $transaksi->sum('masuk'), $transaksi->sum('keluar')];
        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            4 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'e5e7eb']]],
        ];
    }
}
