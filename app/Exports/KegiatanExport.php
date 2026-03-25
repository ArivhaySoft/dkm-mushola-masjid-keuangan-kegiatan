<?php

namespace App\Exports;

use App\Models\Kegiatan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class KegiatanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnWidths
{
    public function title(): string { return 'Kegiatan'; }

    public function columnWidths(): array
    {
        return ['A' => 6, 'B' => 30, 'C' => 14, 'D' => 20, 'E' => 22, 'F' => 20];
    }

    public function collection()
    {
        return Kegiatan::with('creator')->orderBy('tanggal_kegiatan', 'desc')->get();
    }

    public function headings(): array
    {
        return ['No', 'Judul', 'Jenis', 'Tanggal', 'Lokasi', 'Dibuat Oleh'];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;
        return [
            $no,
            $row->judul,
            ucfirst($row->jenis),
            $row->tanggal_kegiatan->isoFormat('D MMMM Y, HH:mm'),
            $row->lokasi ?? '-',
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
