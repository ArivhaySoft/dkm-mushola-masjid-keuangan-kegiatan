<?php

namespace App\Exports;

use App\Models\Kategori;
use App\Models\Rekening;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class KeuanganTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new KeuanganTemplateDataSheet(),
            new KeuanganTemplateKategoriSheet(),
            new KeuanganTemplateRekeningSheet(),
        ];
    }
}

// ── Sheet 1: Template data (kosong, header saja + contoh) ─────────────
class KeuanganTemplateDataSheet implements
    \Maatwebsite\Excel\Concerns\FromArray,
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithStyles,
    \Maatwebsite\Excel\Concerns\WithColumnWidths,
    \Maatwebsite\Excel\Concerns\WithEvents
{
    use \Maatwebsite\Excel\Concerns\RegistersEventListeners;

    public function title(): string { return 'Data Import'; }

    public function columnWidths(): array
    {
        return ['A' => 16, 'B' => 36, 'C' => 22, 'D' => 22, 'E' => 18, 'F' => 18];
    }

    public function array(): array
    {
        $kategori = Kategori::first();
        $rekening = Rekening::first();

        return [
            ['Tanggal', 'Keterangan', 'Kategori', 'Rekening', 'Masuk', 'Keluar'],
            [
                now()->format('d/m/Y'),
                'Contoh: Sumbangan donatur (hapus baris ini)',
                $kategori?->nama ?? 'Nama Kategori',
                $rekening?->nama_rek ?? 'Nama Rekening',
                100000,
                '',
            ],
            [
                now()->format('d/m/Y'),
                'Contoh: Bayar listrik (hapus baris ini)',
                $kategori?->nama ?? 'Nama Kategori',
                $rekening?->nama_rek ?? 'Nama Rekening',
                '',
                50000,
            ],
        ];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1b4d3a']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public static function afterSheet(\Maatwebsite\Excel\Events\AfterSheet $event): void
    {
        $sheet = $event->sheet->getDelegate();

        // Style contoh rows
        $sheet->getStyle('A2:F3')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '9ca3af']],
        ]);

        // Add note
        $sheet->setCellValue('A5', 'CATATAN:');
        $sheet->setCellValue('A6', '• Kolom Tanggal: format dd/mm/yyyy atau yyyy-mm-dd');
        $sheet->setCellValue('A7', '• Kolom Kategori & Rekening: harus sesuai nama di sheet "Daftar Kategori" & "Daftar Rekening"');
        $sheet->setCellValue('A8', '• Kolom Masuk/Keluar: isi salah satu, angka tanpa titik (contoh: 100000)');
        $sheet->setCellValue('A9', '• Hapus baris contoh sebelum import');

        $sheet->getStyle('A5')->getFont()->setBold(true);
        $sheet->getStyle('A5:A9')->getFont()->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF6b7280'));

        $sheet->mergeCells('A6:F6');
        $sheet->mergeCells('A7:F7');
        $sheet->mergeCells('A8:F8');
        $sheet->mergeCells('A9:F9');
    }
}

// ── Sheet 2: Daftar Kategori ──────────────────────────────────────────
class KeuanganTemplateKategoriSheet implements
    \Maatwebsite\Excel\Concerns\FromCollection,
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithHeadings,
    \Maatwebsite\Excel\Concerns\WithMapping,
    \Maatwebsite\Excel\Concerns\WithStyles
{
    public function title(): string { return 'Daftar Kategori'; }

    public function collection()
    {
        return Kategori::orderBy('nama')->get();
    }

    public function headings(): array
    {
        return ['No', 'Nama Kategori'];
    }

    public function map($row): array
    {
        static $no = 0;
        return [++$no, $row->nama];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1b4d3a']],
            ],
        ];
    }
}

// ── Sheet 3: Daftar Rekening ──────────────────────────────────────────
class KeuanganTemplateRekeningSheet implements
    \Maatwebsite\Excel\Concerns\FromCollection,
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithHeadings,
    \Maatwebsite\Excel\Concerns\WithMapping,
    \Maatwebsite\Excel\Concerns\WithStyles
{
    public function title(): string { return 'Daftar Rekening'; }

    public function collection()
    {
        return Rekening::orderBy('nama_rek')->get();
    }

    public function headings(): array
    {
        return ['No', 'Nama Rekening', 'No Rekening', 'Atas Nama'];
    }

    public function map($row): array
    {
        static $no = 0;
        return [++$no, $row->nama_rek, $row->no_rek, $row->atas_nama];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1b4d3a']],
            ],
        ];
    }
}
