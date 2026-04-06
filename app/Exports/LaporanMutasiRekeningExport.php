<?php

namespace App\Exports;

use App\Models\Keuangan;
use App\Models\Rekening;
use App\Models\TransferRekening;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LaporanMutasiRekeningExport implements WithMultipleSheets
{
    public function __construct(
        protected ?string $from = null,
        protected ?string $to   = null,
        protected ?int $rekeningId = null
    ) {}

    public function sheets(): array
    {
        $sheets = [];
        
        if ($this->rekeningId) {
            // Specific rekening
            $rekening = Rekening::find($this->rekeningId);
            if ($rekening) {
                $count = $this->countTransaksi($rekening);
                if ($count > 0) {
                    $sheets[] = new MutasiRekeningSheet($rekening, $this->from, $this->to);
                }
            }
        } else {
            // All rekening
            $rekeningList = Rekening::all();
            foreach ($rekeningList as $rekening) {
                $count = $this->countTransaksi($rekening);
                if ($count > 0) {
                    $sheets[] = new MutasiRekeningSheet($rekening, $this->from, $this->to);
                }
            }
        }

        return $sheets;
    }

    private function countTransaksi(Rekening $rekening): int
    {
        $keuangan = Keuangan::where('id_rekening', $rekening->id)
            ->when($this->from, fn($q) => $q->whereDate('tanggal', '>=', $this->from))
            ->when($this->to, fn($q) => $q->whereDate('tanggal', '<=', $this->to))
            ->count();

        $transfer = TransferRekening::where(function ($q) use ($rekening) {
            $q->where('dari_rekening', $rekening->id)
              ->orWhere('ke_rekening', $rekening->id);
        })
            ->when($this->from, fn($q) => $q->whereDate('tanggal', '>=', $this->from))
            ->when($this->to, fn($q) => $q->whereDate('tanggal', '<=', $this->to))
            ->count();

        return $keuangan + $transfer;
    }
}

// ─── Sheet Mutasi Per Rekening ──────────────────────────────────────────────
class MutasiRekeningSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected int $rowCount = 0;
    protected float $saldoRunning = 0;

    public function __construct(
        protected Rekening $rekening,
        protected ?string $from = null,
        protected ?string $to   = null
    ) {
        $this->saldoRunning = $this->rekening->calculateSaldoSebelum($from);
    }

    public function title(): string
    {
        return substr($this->rekening->nama_rek, 0, 31);
    }

    public function columnWidths(): array
    {
        return ['A' => 6, 'B' => 14, 'C' => 30, 'D' => 18, 'E' => 16, 'F' => 16, 'G' => 16, 'H' => 20];
    }

    public function array(): array
    {
        $rows = [];

        // Header
        $rows[] = ['LAPORAN MUTASI REKENING', '', '', '', '', '', '', ''];
        $rows[] = ['Rekening: ' . $this->rekening->nama_rek, '', '', '', '', '', '', ''];
        $rows[] = ['Atas Nama: ' . $this->rekening->atas_nama, '', '', '', '', '', '', ''];
        $rows[] = ['No. Rekening: ' . $this->rekening->no_rek, '', '', '', '', '', '', ''];

        if ($this->from && $this->to) {
            $rows[] = ['Periode: ' . date('d/m/Y', strtotime($this->from)) . ' - ' . date('d/m/Y', strtotime($this->to)), '', '', '', '', '', '', ''];
        }

        $rows[] = ['Dicetak: ' . now()->format('d/m/Y H:i'), '', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', '', '', ''];

        // Saldo Awal
        $rows[] = ['SALDO AWAL', '', '', '', '', '', '', $this->saldoRunning];
        $rows[] = ['', '', '', '', '', '', '', ''];

        // Column headers
        $rows[] = ['No', 'Tanggal', 'Keterangan', 'Tipe Transaksi', 'Masuk (Rp)', 'Keluar (Rp)', 'Saldo (Rp)', 'Kategori/Referensi'];

        // Get all transactions
        $transaksi = $this->getAllTransaksi();

        // Add transaction rows
        foreach ($transaksi as $t) {
            $this->rowCount++;
            $masuk = $t['masuk'] ?? 0;
            $keluar = $t['keluar'] ?? 0;

            $this->saldoRunning += $masuk - $keluar;

            $rows[] = [
                $this->rowCount,
                $t['tanggal'],
                $t['keterangan'],
                $t['tipe'],
                $masuk > 0 ? $masuk : '',
                $keluar > 0 ? $keluar : '',
                $this->saldoRunning,
                $t['kategori_ref'],
            ];
        }

        $rows[] = ['', '', '', '', '', '', '', ''];

        // Summary
        $summary = $this->rekening->reportBalanceSummary($this->from, $this->to);
        $rows[] = ['SUMMARY', '', '', '', '', '', '', ''];
        $rows[] = ['Total Masuk', '', '', '', $summary['masuk'], '', '', ''];
        $rows[] = ['Total Keluar', '', '', '', '', $summary['keluar'], '', ''];
        $rows[] = ['SALDO AKHIR', '', '', '', '', '', $summary['saldo_akhir'], ''];

        return $rows;
    }

    private function getAllTransaksi(): array
    {
        $transaksi = [];

        // Get Keuangan entries
        $keuanganData = Keuangan::where('id_rekening', $this->rekening->id)
            ->with(['kategori'])
            ->when($this->from, fn($q) => $q->whereDate('tanggal', '>=', $this->from))
            ->when($this->to, fn($q) => $q->whereDate('tanggal', '<=', $this->to))
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        foreach ($keuanganData as $k) {
            $tipe = (float) $k->masuk > 0 ? 'Pemasukan' : 'Pengeluaran';
            $transaksi[] = [
                'tanggal' => $k->tanggal->format('d/m/Y'),
                'keterangan' => $k->keterangan ?? '-',
                'tipe' => $tipe,
                'masuk' => (float) $k->masuk,
                'keluar' => (float) $k->keluar,
                'kategori_ref' => $k->kategori->nama ?? '-',
                'sort_date' => $k->tanggal,
                'sort_id' => $k->id,
            ];
        }

        // Get Transfer Masuk
        $transferMasuk = TransferRekening::where('ke_rekening', $this->rekening->id)
            ->with(['dariRekening', 'kategori'])
            ->when($this->from, fn($q) => $q->whereDate('tanggal', '>=', $this->from))
            ->when($this->to, fn($q) => $q->whereDate('tanggal', '<=', $this->to))
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        foreach ($transferMasuk as $t) {
            $transaksi[] = [
                'tanggal' => $t->tanggal->format('d/m/Y'),
                'keterangan' => 'Transfer dari ' . ($t->dariRekening->nama_rek ?? '-'),
                'tipe' => 'Transfer Masuk',
                'masuk' => (float) $t->jumlah,
                'keluar' => 0,
                'kategori_ref' => $t->kategori->nama ?? '-',
                'sort_date' => $t->tanggal,
                'sort_id' => 'tr-' . $t->id,
            ];
        }

        // Get Transfer Keluar
        $transferKeluar = TransferRekening::where('dari_rekening', $this->rekening->id)
            ->with(['keRekening', 'kategori'])
            ->when($this->from, fn($q) => $q->whereDate('tanggal', '>=', $this->from))
            ->when($this->to, fn($q) => $q->whereDate('tanggal', '<=', $this->to))
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        foreach ($transferKeluar as $t) {
            $transaksi[] = [
                'tanggal' => $t->tanggal->format('d/m/Y'),
                'keterangan' => 'Transfer ke ' . ($t->keRekening->nama_rek ?? '-'),
                'tipe' => 'Transfer Keluar',
                'masuk' => 0,
                'keluar' => (float) $t->jumlah,
                'kategori_ref' => $t->kategori->nama ?? '-',
                'sort_date' => $t->tanggal,
                'sort_id' => 'tr-' . $t->id,
            ];
        }

        // Sort by date and ID
        usort($transaksi, function ($a, $b) {
            if ($a['sort_date'] != $b['sort_date']) {
                return $a['sort_date'] <=> $b['sort_date'];
            }
            return strcmp((string)$a['sort_id'], (string)$b['sort_id']);
        });

        // Remove sort keys
        return array_map(function ($t) {
            unset($t['sort_date'], $t['sort_id']);
            return $t;
        }, $transaksi);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true]],
            10 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F4F8']]],
            11 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1b4d3a']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Format currency columns
                $highestRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle('E11:G' . $highestRow)->getNumberFormat()->setFormatCode('#,##0.00');
                // Center align
                $event->sheet->getStyle('A:H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $event->sheet->getStyle('E:G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}
