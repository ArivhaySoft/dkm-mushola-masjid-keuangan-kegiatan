<?php

namespace App\Exports;

use App\Models\TransferRekening;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TransferRekeningExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnWidths
{
    public function __construct(
        protected ?string $from = null,
        protected ?string $to   = null
    ) {}

    public function title(): string { return 'Transfer Rekening'; }

    public function columnWidths(): array
    {
        return ['A' => 6, 'B' => 14, 'C' => 20, 'D' => 20, 'E' => 20, 'F' => 18, 'G' => 30, 'H' => 20];
    }

    public function collection()
    {
        return TransferRekening::with(['dariRekening', 'keRekening', 'kategori', 'creator'])
            ->when($this->from, fn($q) => $q->whereDate('tanggal', '>=', $this->from))
            ->when($this->to,   fn($q) => $q->whereDate('tanggal', '<=', $this->to))
            ->orderBy('tanggal')
            ->get();
    }

    public function headings(): array
    {
        return ['No', 'Tanggal', 'Dari Rekening', 'Ke Rekening', 'Kategori', 'Jumlah (Rp)', 'Keterangan', 'Oleh'];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;
        return [
            $no,
            $row->tanggal->format('d/m/Y'),
            $row->dariRekening->nama_rek ?? '-',
            $row->keRekening->nama_rek   ?? '-',
            $row->kategori->nama          ?? '-',
            (float) $row->jumlah,
            $row->keterangan ?? '-',
            $row->creator->name ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1b4d3a']],
            ],
        ];
    }
}
