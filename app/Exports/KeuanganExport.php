<?php

namespace App\Exports;

use App\Models\Keuangan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class KeuanganExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    WithColumnWidths,
    WithEvents
{
    protected int   $rowCount  = 0;
    protected float $totalMasuk  = 0;
    protected float $totalKeluar = 0;

    public function __construct(
        protected ?string $from       = null,
        protected ?string $to         = null,
        protected ?int    $kategoriId = null,
        protected ?int    $rekeningId = null
    ) {}

    public function title(): string { return 'Arus Kas'; }

    public function columnWidths(): array
    {
        return ['A' => 6, 'B' => 14, 'C' => 36, 'D' => 20, 'E' => 18, 'F' => 18, 'G' => 18, 'H' => 20];
    }

    public function collection()
    {
        return Keuangan::with(['rekening', 'kategori', 'creator'])
            ->when($this->from,       fn($q) => $q->whereDate('tanggal', '>=', $this->from))
            ->when($this->to,         fn($q) => $q->whereDate('tanggal', '<=', $this->to))
            ->when($this->kategoriId, fn($q) => $q->where('id_kategori', $this->kategoriId))
            ->when($this->rekeningId, fn($q) => $q->where('id_rekening', $this->rekeningId))
            ->orderBy('tanggal')->orderBy('id')
            ->get();
    }

    public function headings(): array
    {
        return ['No', 'Tanggal', 'Keterangan', 'Kategori', 'Rekening', 'Masuk (Rp)', 'Keluar (Rp)', 'Oleh'];
    }

    public function map($row): array
    {
        $this->rowCount++;
        $this->totalMasuk  += (float) $row->masuk;
        $this->totalKeluar += (float) $row->keluar;

        return [
            $this->rowCount,
            $row->tanggal->format('d/m/Y'),
            $row->keterangan ?? '-',
            $row->kategori->nama ?? '-',
            $row->rekening->nama_rek ?? '-',
            $row->masuk  > 0 ? (float) $row->masuk  : '',
            $row->keluar > 0 ? (float) $row->keluar : '',
            $row->creator->name ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1b4d3a']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Insert 3 title rows at top
                $sheet->insertNewRowBefore(1, 3);

                // Row 1: Title
                $sheet->mergeCells('A1:H1');
                $sheet->setCellValue('A1', 'LAPORAN ARUS KAS — MUSHOLA AL-IKHLAS');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '1b4d3a']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Row 2: Period
                $sheet->mergeCells('A2:H2');
                $period = 'Periode: ';
                if ($this->from) $period .= \Carbon\Carbon::parse($this->from)->format('d/m/Y');
                if ($this->from && $this->to) $period .= ' s/d ';
                if ($this->to)   $period .= \Carbon\Carbon::parse($this->to)->format('d/m/Y');
                if (!$this->from && !$this->to) $period .= 'Semua Data';
                $period .= '   |   Dicetak: ' . now()->isoFormat('D MMMM Y, HH:mm');
                $sheet->setCellValue('A2', $period);
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '6b7280']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Row 3: blank

                // Total row
                $totalRow = $this->rowCount + 5;
                $sheet->setCellValue("C{$totalRow}", 'TOTAL');
                $sheet->setCellValue("F{$totalRow}", $this->totalMasuk);
                $sheet->setCellValue("G{$totalRow}", $this->totalKeluar);

                $sheet->getStyle("A{$totalRow}:H{$totalRow}")->applyFromArray([
                    'font'    => ['bold' => true],
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'dcf1e6']],
                    'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1b4d3a']]],
                ]);

                // Number format for currency
                $sheet->getStyle("F5:G{$totalRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                // Color masuk column header
                $sheet->getStyle('F4')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '065f46']],
                ]);
                $sheet->getStyle('G4')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '991b1b']],
                ]);

                // Freeze after header
                $sheet->freezePane('A5');

                // Auto-filter
                $sheet->setAutoFilter("A4:H4");
            },
        ];
    }
}
