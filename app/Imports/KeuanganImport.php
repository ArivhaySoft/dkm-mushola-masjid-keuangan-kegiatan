<?php

namespace App\Imports;

use App\Models\Kategori;
use App\Models\Keuangan;
use App\Models\Rekening;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\IOFactory;

class KeuanganImport implements ToCollection, WithHeadingRow
{
    protected int $userId;
    protected int $imported = 0;
    protected array $errors = [];

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function collection(Collection $rows): void
    {
        $analysis = $this->analyzeRows($rows);

        $this->errors = $analysis['errors'];
        $this->imported = $this->persistRows($analysis['valid_rows']);
    }

    public function preview(string $filePath): array
    {
        return $this->analyzeRows($this->rowsFromFile($filePath));
    }

    public function importFromFile(string $filePath): array
    {
        $analysis = $this->preview($filePath);
        $this->errors = $analysis['errors'];
        $this->imported = $this->persistRows($analysis['valid_rows']);

        return [
            'imported' => $this->imported,
            'errors' => $this->errors,
            'rows' => $analysis['rows'],
            'summary' => $analysis['summary'],
        ];
    }

    protected function analyzeRows(iterable $rows): array
    {
        $kategoriMap = Kategori::pluck('id', 'nama')->mapWithKeys(fn($id, $nama) => [strtolower(trim($nama)) => $id]);
        $rekeningMap = Rekening::pluck('id', 'nama_rek')->mapWithKeys(fn($id, $nama) => [strtolower(trim($nama)) => $id]);

        $previewRows = [];
        $validRows = [];
        $errors = [];
        $totalRows = 0;
        $skippedRows = 0;

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;

            if ($this->isEmptyRow($row)) {
                $skippedRows++;
                continue;
            }

            $totalRows++;

            $keterangan = trim((string) ($row['keterangan'] ?? ''));
            $tanggal = $this->parseDate($row['tanggal'] ?? null);
            $kategoriNama = trim((string) ($row['kategori'] ?? ''));
            $rekeningNama = trim((string) ($row['rekening'] ?? ''));
            $kategoriId = $kategoriMap[strtolower($kategoriNama)] ?? null;
            $rekeningId = $rekeningMap[strtolower($rekeningNama)] ?? null;
            $masuk = $this->parseNumber($row['masuk'] ?? null);
            $keluar = $this->parseNumber($row['keluar'] ?? null);

            $rowErrors = [];

            if (!$tanggal) {
                $rowErrors[] = 'Format tanggal tidak valid.';
            }

            if (!$kategoriId) {
                $rowErrors[] = "Kategori '{$kategoriNama}' tidak ditemukan.";
            }

            if (!$rekeningId) {
                $rowErrors[] = "Rekening '{$rekeningNama}' tidak ditemukan.";
            }

            if ($masuk <= 0 && $keluar <= 0) {
                $rawMasuk = trim((string) ($row['masuk'] ?? ''));
                $rawKeluar = trim((string) ($row['keluar'] ?? ''));
                $hint = ($rawMasuk !== '' || $rawKeluar !== '')
                    ? " Nilai file: masuk='{$rawMasuk}', keluar='{$rawKeluar}'."
                    : '';
                $rowErrors[] = 'Masuk atau Keluar harus diisi minimal salah satu.' . $hint;
            }

            $previewRows[] = [
                'row_num' => $rowNum,
                'tanggal' => $tanggal,
                'tanggal_raw' => (string) ($row['tanggal'] ?? ''),
                'keterangan' => $keterangan,
                'kategori' => $kategoriNama,
                'rekening' => $rekeningNama,
                'masuk' => $masuk,
                'keluar' => $keluar,
                'status' => empty($rowErrors) ? 'valid' : 'error',
                'errors' => $rowErrors,
            ];

            if (!empty($rowErrors)) {
                $errors[] = 'Baris ' . $rowNum . ': ' . implode(' ', $rowErrors);
                continue;
            }

            $validRows[] = [
                'tanggal' => $tanggal,
                'keterangan' => $keterangan,
                'masuk' => $masuk,
                'keluar' => $keluar,
                'id_kategori' => $kategoriId,
                'id_rekening' => $rekeningId,
                'created_by' => $this->userId,
            ];
        }

        return [
            'rows' => $previewRows,
            'valid_rows' => $validRows,
            'errors' => $errors,
            'summary' => [
                'total_rows' => $totalRows,
                'valid_rows' => count($validRows),
                'invalid_rows' => count($errors),
                'skipped_rows' => $skippedRows,
            ],
        ];
    }

    protected function persistRows(array $rows): int
    {
        foreach ($rows as $row) {
            Keuangan::create($row);
        }

        return count($rows);
    }

    protected function rowsFromFile(string $filePath): Collection
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getSheet(0);
        $rows = $worksheet->toArray(null, true, true, false);

        if (empty($rows)) {
            return collect();
        }

        $headers = array_map(fn($header) => $this->normalizeHeading($header), array_shift($rows));

        return collect($rows)->map(function (array $row) use ($headers) {
            $assoc = [];
            foreach ($headers as $index => $header) {
                if ($header === '') {
                    continue;
                }

                $assoc[$header] = $row[$index] ?? null;
            }

            return $assoc;
        });
    }

    protected function normalizeHeading(mixed $value): string
    {
        $heading = strtolower(trim((string) $value));
        $heading = preg_replace('/[^a-z0-9]+/i', '_', $heading) ?? '';
        return trim($heading, '_');
    }

    protected function isEmptyRow(array $row): bool
    {
        foreach (['tanggal', 'keterangan', 'kategori', 'rekening', 'masuk', 'keluar'] as $key) {
            $value = $row[$key] ?? null;
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    protected function parseDate($value): ?string
    {
        if (empty($value)) return null;

        // Excel serial number
        if (is_numeric($value)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        // Try common date formats
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d'] as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, trim($value))->format('Y-m-d');
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }

    protected function parseNumber($value): float
    {
        if ($value === null || $value === '' || $value === false) return 0;
        if (is_numeric($value)) return (float) $value;

        $str = (string) $value;

        // Strip currency symbols, letters, spaces — keep only digits, dot, comma
        $str = preg_replace('/[^0-9.,]/', '', $str);

        if ($str === '') return 0;

        $dotCount   = substr_count($str, '.');
        $commaCount = substr_count($str, ',');

        if ($dotCount > 0 && $commaCount > 0) {
            // Both separators present — whichever is last is the decimal separator
            if (strrpos($str, ',') > strrpos($str, '.')) {
                // Indonesian/European: 1.000.000,50
                $str = str_replace(['.', ','], ['', '.'], $str);
            } else {
                // US: 1,000,000.50
                $str = str_replace(',', '', $str);
            }
        } elseif ($commaCount > 0) {
            // Only commas — treat as thousands separator (e.g. 1,000,000)
            $str = str_replace(',', '', $str);
        } elseif ($dotCount > 1) {
            // Multiple dots — Indonesian thousands: 1.000.000
            $str = str_replace('.', '', $str);
        }
        // Single dot: keep as decimal

        return is_numeric($str) ? (float) $str : 0;
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
