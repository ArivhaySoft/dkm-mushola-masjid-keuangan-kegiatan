<?php

namespace App\Http\Controllers;

use App\Exports\KeuanganExport;
use App\Exports\KeuanganTemplateExport;
use App\Exports\LaporanBulananExport;
use App\Models\Kategori;
use App\Models\Keuangan;
use App\Models\Rekening;
use App\Models\Setting;
use App\Models\TransferRekening;
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

    // ── Excel: Laporan Mutasi Rekening (detail per rekening) ──────────────
    public function laporanMutasiRekening(Request $request)
    {
        $from = $request->from ? Carbon::parse($request->from)->startOfDay()->format('Y-m-d') : null;
        $to = $request->to ? Carbon::parse($request->to)->endOfDay()->format('Y-m-d') : null;
        $rekeningId = $request->rekening_id ? (int) $request->rekening_id : null;
        
        $filename = 'laporan-mutasi-rekening-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(
            new \App\Exports\LaporanMutasiRekeningExport($from, $to, $rekeningId),
            $filename
        );
    }

    // ── PDF: Laporan Mutasi Rekening ──────────────────────────────────────
    public function laporanMutasiRekeningPdf(Request $request)
    {
        $from = $request->from;
        $to   = $request->to;
        $rekeningId = $request->rekening_id ? (int) $request->rekening_id : null;
        [$appName, $namaMushola, $logoDataUri] = $this->pdfIdentity();

        $rekeningList = $rekeningId ? Rekening::where('id', $rekeningId)->get() : Rekening::all();
        $dataMutasi = [];

        foreach ($rekeningList as $rekening) {
            $mutatsiData = $this->getTransaksiRekeningForPdf($rekening, $from, $to);
            if (count($mutatsiData['transaksi']) > 0) {
                $dataMutasi[] = [
                    'rekening' => $rekening,
                    'nama_rek' => $rekening->nama_rek,
                    'atas_nama' => $rekening->atas_nama,
                    'no_rek' => $rekening->no_rek,
                    'saldo_awal' => $mutatsiData['saldo_awal'],
                    'masuk' => $mutatsiData['masuk'],
                    'keluar' => $mutatsiData['keluar'],
                    'saldo_akhir' => $mutatsiData['saldo_akhir'],
                    'transaksi' => $mutatsiData['transaksi'],
                ];
            }
        }

        $pdf = Pdf::loadView('laporan.mutasi-rekening-pdf', compact(
            'from', 'to', 'dataMutasi', 'appName', 'namaMushola', 'logoDataUri'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('laporan-mutasi-rekening-' . now()->format('Ymd') . '.pdf');
    }

    private function getTransaksiRekeningForPdf(Rekening $rekening, ?string $from, ?string $to): array
    {
        $transaksi = [];
        $saldoAwal = $rekening->calculateSaldoSebelum($from);
        $saldoRunning = $saldoAwal;

        // Get Keuangan entries
        $keuanganData = Keuangan::where('id_rekening', $rekening->id)
            ->with(['kategori'])
            ->when($from, fn($q) => $q->whereDate('tanggal', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('tanggal', '<=', $to))
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        $totalMasuk = 0;
        $totalKeluar = 0;

        foreach ($keuanganData as $k) {
            $tipe = (float) $k->masuk > 0 ? 'Pemasukan' : 'Pengeluaran';
            $masuk = (float) $k->masuk;
            $keluar = (float) $k->keluar;
            $totalMasuk += $masuk;
            $totalKeluar += $keluar;

            $transaksi[] = [
                'tanggal' => $k->tanggal->format('d/m/Y'),
                'keterangan' => $k->keterangan ?? '-',
                'tipe' => $tipe,
                'masuk' => $masuk,
                'keluar' => $keluar,
                'kategori_ref' => $k->kategori->nama ?? '-',
                'sort_date' => $k->tanggal,
                'sort_id' => $k->id,
            ];
        }

        // Get Transfer Masuk
        $transferMasuk = TransferRekening::where('ke_rekening', $rekening->id)
            ->with(['dariRekening', 'kategori'])
            ->when($from, fn($q) => $q->whereDate('tanggal', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('tanggal', '<=', $to))
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        foreach ($transferMasuk as $t) {
            $masuk = (float) $t->jumlah;
            $totalMasuk += $masuk;

            $transaksi[] = [
                'tanggal' => $t->tanggal->format('d/m/Y'),
                'keterangan' => 'Transfer dari ' . ($t->dariRekening->nama_rek ?? '-'),
                'tipe' => 'Transfer Masuk',
                'masuk' => $masuk,
                'keluar' => 0,
                'kategori_ref' => $t->kategori->nama ?? '-',
                'sort_date' => $t->tanggal,
                'sort_id' => 'tr-' . $t->id,
            ];
        }

        // Get Transfer Keluar
        $transferKeluar = TransferRekening::where('dari_rekening', $rekening->id)
            ->with(['keRekening', 'kategori'])
            ->when($from, fn($q) => $q->whereDate('tanggal', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('tanggal', '<=', $to))
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        foreach ($transferKeluar as $t) {
            $keluar = (float) $t->jumlah;
            $totalKeluar += $keluar;

            $transaksi[] = [
                'tanggal' => $t->tanggal->format('d/m/Y'),
                'keterangan' => 'Transfer ke ' . ($t->keRekening->nama_rek ?? '-'),
                'tipe' => 'Transfer Keluar',
                'masuk' => 0,
                'keluar' => $keluar,
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

        foreach ($transaksi as &$t) {
            $saldoRunning += ((float) ($t['masuk'] ?? 0)) - ((float) ($t['keluar'] ?? 0));
            $t['saldo'] = $saldoRunning;
            unset($t['sort_date'], $t['sort_id']);
        }
        unset($t);

        return [
            'saldo_awal' => $saldoAwal,
            'masuk' => $totalMasuk,
            'keluar' => $totalKeluar,
            'saldo_akhir' => $saldoRunning,
            'transaksi' => $transaksi,
        ];
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
