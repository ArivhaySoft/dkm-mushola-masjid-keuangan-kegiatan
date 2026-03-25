<?php

namespace App\Http\Controllers;

use App\Exports\KeuanganExport;
use App\Exports\KeuanganTemplateExport;
use App\Exports\LaporanBulananExport;
use App\Models\Kategori;
use App\Models\Keuangan;
use App\Models\Rekening;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    // ── Excel: Arus Kas ───────────────────────────────────────────────────
    public function keuangan(Request $request)
    {
        $filename = 'laporan-keuangan-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(
            new KeuanganExport(
                $request->from,
                $request->to,
                $request->kategori_id ? (int) $request->kategori_id : null,
                $request->rekening_id ? (int) $request->rekening_id : null
            ),
            $filename
        );
    }

    // ── Excel: Template Import Arus Kas ────────────────────────────────────
    public function templateImport()
    {
        return Excel::download(new KeuanganTemplateExport(), 'template-import-keuangan.xlsx');
    }

    // ── Excel: Laporan Periodik (ringkasan + detail kategori) ───────────
    public function laporanPeriodik(Request $request)
    {
        $from = $request->from ? Carbon::parse($request->from)->startOfDay()->format('Y-m-d') : now()->startOfMonth()->format('Y-m-d');
        $to = $request->to ? Carbon::parse($request->to)->endOfDay()->format('Y-m-d') : now()->format('Y-m-d');
        $label = 'Periodik ' . Carbon::parse($from)->isoFormat('D MMMM Y') . ' - ' . Carbon::parse($to)->isoFormat('D MMMM Y');

        $filename = 'laporan-periodik-' . Carbon::parse($from)->format('Ymd') . '-' . Carbon::parse($to)->format('Ymd') . '.xlsx';

        return Excel::download(new LaporanBulananExport($from, $to, $label), $filename);
    }

    // ── Excel: Laporan Bulanan (multi-sheet) ──────────────────────────────
    public function laporanBulanan(Request $request)
    {
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);
        $from  = Carbon::create($tahun, $bulan, 1)->startOfMonth()->format('Y-m-d');
        $to    = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');
        $label = Carbon::create($tahun, $bulan, 1)->isoFormat('MMMM Y');

        $filename = 'laporan-bulanan-' . str_replace(' ', '-', strtolower($label)) . '.xlsx';
        return Excel::download(new LaporanBulananExport($from, $to, $label), $filename);
    }

    // ── Excel: Laporan Tahunan (multi-sheet) ──────────────────────────────
    public function laporanTahunan(Request $request)
    {
        $tahun    = (int) ($request->tahun ?? now()->year);
        $filename = 'laporan-tahunan-' . $tahun . '.xlsx';
        return Excel::download(new \App\Exports\LaporanTahunanExport($tahun), $filename);
    }

    // ── Excel: Transfer Rekening ──────────────────────────────────────────
    public function transferRekening(Request $request)
    {
        $filename = 'transfer-rekening-' . now()->format('Ymd') . '.xlsx';
        return Excel::download(
            new \App\Exports\TransferRekeningExport($request->from, $request->to),
            $filename
        );
    }

    // ── Excel: Kegiatan ───────────────────────────────────────────────────
    public function kegiatan()
    {
        return Excel::download(
            new \App\Exports\KegiatanExport(),
            'kegiatan-' . now()->format('Ymd') . '.xlsx'
        );
    }

    // ── PDF: Laporan Periodik / Bulanan ───────────────────────────────────
    public function laporanPdf(Request $request)
    {
        $from = $request->from;
        $to   = $request->to;
        $tipe = $request->tipe ?? 'periodik';
        [$appName, $namaMushola, $logoDataUri] = $this->pdfIdentity();

        $kategoriList = Kategori::all();
        $rekeningList = Rekening::all();

        $dataKategori = $kategoriList->map(function ($kat) use ($from, $to) {
            $masuk  = Keuangan::where('id_kategori', $kat->id)
                ->withoutSaldoAwal()
                ->when($from, fn($q) => $q->whereDate('tanggal', '>=', $from))
                ->when($to,   fn($q) => $q->whereDate('tanggal', '<=', $to))
                ->sum('masuk');
            $keluar = Keuangan::where('id_kategori', $kat->id)
                ->withoutSaldoAwal()
                ->when($from, fn($q) => $q->whereDate('tanggal', '>=', $from))
                ->when($to,   fn($q) => $q->whereDate('tanggal', '<=', $to))
                ->sum('keluar');

            $saldoAwal = (float) $kat->saldo_awal;
            if ($from) {
                $mIn  = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereDate('tanggal', '<', $from)->sum('masuk');
                $mOut = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereDate('tanggal', '<', $from)->sum('keluar');
                $saldoAwal = (float) $kat->saldo_awal + $mIn - $mOut;
            }

            return [
                'nama'        => $kat->nama,
                'saldo_awal'  => $saldoAwal,
                'masuk'       => (float) $masuk,
                'keluar'      => (float) $keluar,
                'saldo_akhir' => $saldoAwal + $masuk - $keluar,
            ];
        });

        $dataRekening = $rekeningList->map(function ($rek) use ($from, $to) {
            $summary = $rek->reportBalanceSummary($from, $to);

            return [
                'nama'        => $rek->nama_rek,
                'atas_nama'   => $rek->atas_nama,
                'no_rek'      => $rek->no_rek,
                'saldo_awal'  => (float) $summary['saldo_awal'],
                'masuk'       => (float) $summary['masuk'],
                'keluar'      => (float) $summary['keluar'],
                'saldo_akhir' => (float) $summary['saldo_akhir'],
            ];
        });

        $detailPerKategori = $kategoriList->map(function ($kat) use ($from, $to) {
            $transaksi = Keuangan::with(['rekening', 'creator'])
                ->where('id_kategori', $kat->id)
                ->withoutSaldoAwal()
                ->when($from, fn($q) => $q->whereDate('tanggal', '>=', $from))
                ->when($to,   fn($q) => $q->whereDate('tanggal', '<=', $to))
                ->orderBy('tanggal')
                ->get();
            return ['kategori' => $kat->nama, 'transaksi' => $transaksi];
        });

        $totalMasuk  = $dataKategori->sum('masuk');
        $totalKeluar = $dataKategori->sum('keluar');
        $totalSaldo  = $dataKategori->sum('saldo_akhir');

        $pdf = Pdf::loadView('laporan.pdf', compact(
            'from', 'to', 'tipe',
            'dataKategori', 'dataRekening', 'detailPerKategori',
            'totalMasuk', 'totalKeluar', 'totalSaldo',
            'appName', 'namaMushola', 'logoDataUri'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('laporan-keuangan-' . now()->format('Ymd') . '.pdf');
    }

    // ── PDF: Laporan Tahunan ──────────────────────────────────────────────
    public function laporanPdfTahunan(Request $request)
    {
        $tahun = (int) ($request->tahun ?? now()->year);
        [$appName, $namaMushola, $logoDataUri] = $this->pdfIdentity();

        $kategoriList = Kategori::all();
        $startYear = Carbon::create($tahun, 1, 1)->startOfYear()->format('Y-m-d');

        $dataKategori = $kategoriList->map(function ($kat) use ($tahun, $startYear) {
            $masuk  = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereYear('tanggal', $tahun)->sum('masuk');
            $keluar = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereYear('tanggal', $tahun)->sum('keluar');
            $mIn    = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereDate('tanggal', '<', $startYear)->sum('masuk');
            $mOut   = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereDate('tanggal', '<', $startYear)->sum('keluar');
            $saldoAwal = (float) $kat->saldo_awal + $mIn - $mOut;

            return [
                'nama' => $kat->nama,
                'saldo_awal' => $saldoAwal,
                'masuk' => (float) $masuk,
                'keluar' => (float) $keluar,
                'saldo_akhir' => $saldoAwal + $masuk - $keluar,
            ];
        })->toArray();

        $dataPerBulan = collect(range(1, 12))->map(function ($m) use ($tahun) {
            $startMonth = Carbon::create($tahun, $m, 1)->startOfMonth()->format('Y-m-d');
            $masuk  = Keuangan::whereYear('tanggal', $tahun)->whereMonth('tanggal', $m)->sum('masuk');
            $keluar = Keuangan::whereYear('tanggal', $tahun)->whereMonth('tanggal', $m)->sum('keluar');
            $saldoAwal = Keuangan::whereDate('tanggal', '<', $startMonth)
                ->selectRaw('COALESCE(SUM(masuk),0) - COALESCE(SUM(keluar),0) as saldo')
                ->value('saldo') ?? 0;
            return [
                'bulan'  => Carbon::create($tahun, $m)->isoFormat('MMMM'),
                'saldo_awal' => (float) $saldoAwal,
                'masuk'  => (float)$masuk,
                'keluar' => (float)$keluar,
                'saldo_akhir'  => $saldoAwal + $masuk - $keluar,
            ];
        })->toArray();

        $from = Carbon::create($tahun, 1, 1)->startOfYear()->format('Y-m-d');
        $to   = Carbon::create($tahun, 1, 1)->endOfYear()->format('Y-m-d');

        $dataRekening = Rekening::all()->map(function ($rek) use ($from, $to) {
            $summary = $rek->reportBalanceSummary($from, $to);
            return [
                'nama'        => $rek->nama_rek,
                'atas_nama'   => $rek->atas_nama,
                'no_rek'      => $rek->no_rek,
                'saldo_awal'  => (float) $summary['saldo_awal'],
                'masuk'       => (float) $summary['masuk'],
                'keluar'      => (float) $summary['keluar'],
                'saldo_akhir' => (float) $summary['saldo_akhir'],
            ];
        })->toArray();

        $totalMasuk  = collect($dataPerBulan)->sum('masuk');
        $totalKeluar = collect($dataPerBulan)->sum('keluar');

        $pdf = Pdf::loadView('laporan.pdf-tahunan', compact(
            'tahun', 'dataKategori', 'dataPerBulan', 'dataRekening', 'totalMasuk', 'totalKeluar',
            'appName', 'namaMushola', 'logoDataUri'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('laporan-tahunan-' . $tahun . '.pdf');
    }

    private function pdfIdentity(): array
    {
        $appName = Setting::get('app_name', 'Keuangan Mushola');
        $namaMushola = Setting::get('nama_mushola', 'Mushola Al-Ikhlas');
        $logoPath = Setting::get('foto_mushola', '');
        $logoDataUri = null;

        if ($logoPath) {
            $fullPath = storage_path('app/public/' . ltrim($logoPath, '/'));
            if (is_file($fullPath)) {
                $mime = @mime_content_type($fullPath) ?: 'image/png';
                $logoDataUri = 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($fullPath));
            }
        }

        return [$appName, $namaMushola ?: 'Mushola Al-Ikhlas', $logoDataUri];
    }
}
