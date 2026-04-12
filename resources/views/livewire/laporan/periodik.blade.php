<?php

use App\Models\HistoryLaporan;
use App\Models\HistoryLaporanKategori;
use App\Models\HistoryLaporanRekening;
use App\Models\Kategori;
use App\Models\Keuangan;
use App\Models\Rekening;
use Carbon\Carbon;
use Livewire\Volt\Component;

new class extends Component
{
    public function render(): mixed
    {
        return parent::render()->title('Laporan Periodik');
    }

    public string $from = '';
    public string $to   = '';

    public bool $hasData = false;
    public bool $showHistory = false;

    public array $historyList   = [];
    public int $historyPage     = 1;
    public int $historyLimit    = 3;
    public int $historyTotal    = 0;
    public array $dataKategori  = [];
    public array $dataRekening  = [];
    public array $detailMasuk   = [];
    public array $detailKeluar  = [];
    public float $totalMasuk    = 0;
    public float $totalKeluar   = 0;
    public float $totalSaldo    = 0;
    public float $globalSaldoAwal = 0;

    public function mount(): void
    {
        $this->from = now()->startOfMonth()->format('Y-m-d');
        $this->to   = now()->format('Y-m-d');
        $this->loadHistory();
    }

    public function historyPages(): int
    {
        return (int) max(1, ceil($this->historyTotal / $this->historyLimit));
    }

    public function loadHistory(): void
    {
        $this->historyTotal = HistoryLaporan::count();
        if ($this->historyPage > $this->historyPages()) {
            $this->historyPage = $this->historyPages();
        }

        $this->historyList = HistoryLaporan::with('creator')
            ->orderByDesc('created_at')
            ->offset(($this->historyPage - 1) * $this->historyLimit)
            ->limit($this->historyLimit)
            ->get()
            ->map(fn ($h) => [
                'id'              => $h->id,
                'tanggal_dari'    => $h->tanggal_dari->format('Y-m-d'),
                'tanggal_sampai'  => $h->tanggal_sampai->format('Y-m-d'),
                'saldo_awal'      => (float) $h->saldo_awal,
                'masuk'           => (float) $h->masuk,
                'keluar'          => (float) $h->keluar,
                'saldo_akhir'     => (float) $h->saldo_akhir,
                'created_by_name' => $h->creator->name ?? '-',
                'created_at'      => $h->created_at->format('d/m/Y H:i'),
            ])
            ->toArray();
    }

    public function toggleHistory(): void
    {
        $this->showHistory = !$this->showHistory;
    }

    public function applyHistory(int $id): void
    {
        $h = HistoryLaporan::find($id);
        if ($h) {
            $this->from = $h->tanggal_sampai->addDay()->format('Y-m-d');
            $this->to   = now()->format('Y-m-d');
        }
    }

    public function previousHistoryPage(): void
    {
        if ($this->historyPage > 1) {
            $this->historyPage--;
            $this->loadHistory();
        }
    }

    public function nextHistoryPage(): void
    {
        if ($this->historyPage < $this->historyPages()) {
            $this->historyPage++;
            $this->loadHistory();
        }
    }

    public function goToHistoryPage(int $page): void
    {
        if ($page >= 1 && $page <= $this->historyPages()) {
            $this->historyPage = $page;
            $this->loadHistory();
        }
    }

    public function simpanHistory(): void
    {
        if (!$this->hasData) return;

        $history = HistoryLaporan::create([
            'tanggal_dari'    => $this->from,
            'tanggal_sampai'  => $this->to,
            'saldo_awal'      => $this->globalSaldoAwal,
            'masuk'           => $this->totalMasuk,
            'keluar'          => $this->totalKeluar,
            'saldo_akhir'     => $this->globalSaldoAwal + $this->totalMasuk - $this->totalKeluar,
            'created_by'      => auth()->id(),
        ]);

        foreach ($this->dataKategori as $kategori) {
            HistoryLaporanKategori::create([
                'history_laporan_id' => $history->id,
                'kategori_id'        => $kategori['id'],
                'saldo_awal'         => $kategori['saldo_awal'],
                'masuk'              => $kategori['masuk'],
                'keluar'             => $kategori['keluar'],
                'saldo_akhir'        => $kategori['saldo_akhir'],
            ]);
        }

        foreach ($this->dataRekening as $rekening) {
            HistoryLaporanRekening::create([
                'history_laporan_id' => $history->id,
                'rekening_id'        => $rekening['id'],
                'saldo_awal'         => $rekening['saldo_awal'],
                'masuk'              => $rekening['masuk'],
                'keluar'             => $rekening['keluar'],
                'saldo_akhir'        => $rekening['saldo_akhir'],
            ]);
        }

        $this->loadHistory();
        $this->dispatch('swal', type: 'success', message: 'History laporan berhasil disimpan.');
    }

    public function hapusHistory(int $id): void
    {
        HistoryLaporan::where('id', $id)->delete();
        $this->loadHistory();
        $this->dispatch('swal', type: 'success', message: 'History laporan berhasil dihapus.');
    }

    public function generate(): void
    {
        $this->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        $baseTransaksi = Keuangan::with(['kategori', 'rekening', 'creator'])
            ->withoutSaldoAwal()
            ->whereDate('tanggal', '>=', $this->from)
            ->whereDate('tanggal', '<=', $this->to);

        $this->detailMasuk = (clone $baseTransaksi)
            ->where('masuk', '>', 0)
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get()
            ->toArray();

        $this->detailKeluar = (clone $baseTransaksi)
            ->where('keluar', '>', 0)
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get()
            ->toArray();

        $kategoriList = Kategori::all();
        $rekeningList = Rekening::all();

        $historySeed = null;
        if ($this->from) {
            $fromDate = Carbon::parse($this->from);
            $historySeed = HistoryLaporan::where(function ($query) use ($fromDate) {
                    $query->whereDate('tanggal_sampai', $fromDate->format('Y-m-d'))
                          ->orWhereDate('tanggal_sampai', $fromDate->copy()->subDay()->format('Y-m-d'));
                })
                ->with(['kategoriDetails', 'rekeningDetails'])
                ->orderByDesc('tanggal_sampai')
                ->first();
        }

        $historyKategoriBalances = [];
        $historyRekeningBalances = [];
        if ($historySeed) {
            $historyKategoriBalances = $historySeed->kategoriDetails
                ->keyBy('kategori_id')
                ->map(fn ($detail) => (float) $detail->saldo_akhir)
                ->toArray();

            $historyRekeningBalances = $historySeed->rekeningDetails
                ->keyBy('rekening_id')
                ->map(fn ($detail) => (float) $detail->saldo_akhir)
                ->toArray();
        }

        $this->dataKategori = $kategoriList->map(function ($kat) use ($historyKategoriBalances) {
            $masuk  = Keuangan::where('id_kategori', $kat->id)
                ->withoutSaldoAwal()
                ->whereDate('tanggal', '>=', $this->from)
                ->whereDate('tanggal', '<=', $this->to)->sum('masuk');
            $keluar = Keuangan::where('id_kategori', $kat->id)
                ->withoutSaldoAwal()
                ->whereDate('tanggal', '>=', $this->from)
                ->whereDate('tanggal', '<=', $this->to)->sum('keluar');

            $saldoAwal = $kat->saldo_awal;
            if (array_key_exists($kat->id, $historyKategoriBalances)) {
                $saldoAwal = $historyKategoriBalances[$kat->id];
            } else {
                $mIn  = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereDate('tanggal', '<', $this->from)->sum('masuk');
                $mOut = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereDate('tanggal', '<', $this->from)->sum('keluar');
                $saldoAwal = $kat->saldo_awal + $mIn - $mOut;
            }

            return [
                'id'          => $kat->id,
                'nama'        => $kat->nama,
                'saldo_awal'  => $saldoAwal,
                'masuk'       => $masuk,
                'keluar'      => $keluar,
                'saldo_akhir' => $saldoAwal + $masuk - $keluar,
            ];
        })->toArray();

        $this->dataRekening = $rekeningList->map(function ($rek) use ($historyRekeningBalances) {
            $summary = $rek->reportBalanceSummary($this->from, $this->to);
            $saldoAwal = $summary['saldo_awal'];
            if (array_key_exists($rek->id, $historyRekeningBalances)) {
                $saldoAwal = $historyRekeningBalances[$rek->id];
            }

            return [
                'id'          => $rek->id,
                'nama'        => $rek->nama_rek,
                'atas_nama'   => $rek->atas_nama,
                'no_rek'      => $rek->no_rek,
                'saldo_awal'  => $saldoAwal,
                'masuk'       => $summary['masuk'],
                'keluar'      => $summary['keluar'],
                'saldo_akhir' => $saldoAwal + $summary['masuk'] - $summary['keluar'],
            ];
        })->toArray();

        $this->totalMasuk  = collect($this->detailMasuk)->sum('masuk');
        $this->totalKeluar = collect($this->detailKeluar)->sum('keluar');
        $this->totalSaldo  = $this->totalMasuk - $this->totalKeluar;
        $this->globalSaldoAwal = collect($this->dataKategori)->sum('saldo_awal');
        $this->hasData     = true;
    }

    public function downloadPdf(): void
    {
        $params = http_build_query([
            'from' => $this->from,
            'to'   => $this->to,
            'tipe' => 'periodik',
        ]);
        $this->redirect('/export/laporan-pdf?' . $params);
    }

    public function downloadExcel(): void
    {
        $params = http_build_query(['from' => $this->from, 'to' => $this->to]);
        $this->redirect('/export/laporan-periodik?' . $params);
    }
}; ?>

<div>
<div class="card mb-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-bold text-gray-700">Filter Periode</h2>
        <button wire:click="toggleHistory" class="text-xs font-medium text-blue-600 hover:text-blue-800 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            History Laporan
            @if($historyTotal > 0)
            <span class="bg-blue-100 text-blue-700 rounded-full px-1.5 py-0.5 text-[10px] font-bold">{{ $historyTotal }}</span>
            @endif
        </button>
    </div>

    {{-- History Panel --}}
    @if($showHistory)
    <div class="bg-gray-50 border border-gray-200 rounded-xl mb-4 overflow-hidden">
        <div class="px-4 py-2.5 bg-gray-100 border-b border-gray-200">
            <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wider">History Laporan Periodik</h3>
        </div>
        @forelse($historyList as $h)
        <div class="px-4 py-3 border-b border-gray-100 last:border-b-0 hover:bg-white transition">
            <div class="flex items-start justify-between gap-2">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800">
                        {{ \Carbon\Carbon::parse($h['tanggal_dari'])->isoFormat('D MMM Y') }} –
                        {{ \Carbon\Carbon::parse($h['tanggal_sampai'])->isoFormat('D MMM Y') }}
                    </p>
                    <div class="flex flex-wrap gap-x-4 gap-y-0.5 mt-1 text-xs">
                        <span class="text-gray-500">Saldo Awal: <strong class="text-gray-700">Rp {{ number_format($h['saldo_awal'], 0, ',', '.') }}</strong></span>
                        <span class="text-gray-500">Masuk: <strong class="text-emerald-600">Rp {{ number_format($h['masuk'], 0, ',', '.') }}</strong></span>
                        <span class="text-gray-500">Keluar: <strong class="text-red-500">Rp {{ number_format($h['keluar'], 0, ',', '.') }}</strong></span>
                        <span class="text-gray-500">Saldo Akhir: <strong class="text-blue-600">Rp {{ number_format($h['saldo_akhir'], 0, ',', '.') }}</strong></span>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">{{ $h['created_by_name'] }} · {{ $h['created_at'] }}</p>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <button wire:click="applyHistory({{ $h['id'] }})" class="p-1.5 text-blue-500 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition" title="Pakai sebagai titik awal">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                    <button
                        x-data
                        @click.prevent="Swal.fire({
                            title: 'Hapus history laporan ini?',
                            text: 'Tindakan ini tidak dapat dibatalkan.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Ya, hapus!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $wire.hapusHistory({{ $h['id'] }});
                            }
                        })"
                        class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                        title="Hapus"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="px-4 py-6 text-center text-gray-400 text-sm">Belum ada history laporan yang disimpan.</div>
        @endforelse

        @if($historyTotal > $historyLimit)
        <div class="px-4 py-3 bg-white border-t border-gray-200 flex items-center justify-between gap-3">
            <div class="text-xs text-gray-500">
                Halaman {{ $historyPage }} dari {{ $this->historyPages() }}
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="previousHistoryPage" @if($historyPage === 1) disabled @endif class="btn btn-secondary btn-sm {{ $historyPage === 1 ? 'opacity-50 cursor-not-allowed' : '' }}">
                    Sebelumnya
                </button>
                <button wire:click="nextHistoryPage" @if($historyPage === $this->historyPages()) disabled @endif class="btn btn-secondary btn-sm {{ $historyPage === $this->historyPages() ? 'opacity-50 cursor-not-allowed' : '' }}">
                    Berikutnya
                </button>
            </div>
        </div>
        @endif
    </div>
    @endif

    <div class="grid grid-cols-2 sm:flex sm:flex-row gap-3 items-end">
        <div class="col-span-1 sm:flex-1">
            <label class="label">Dari</label>
            <input type="date" wire:model="from" class="input" />
        </div>
        <div class="col-span-1 sm:flex-1">
            <label class="label">Sampai</label>
            <input type="date" wire:model="to" class="input" />
        </div>
        <button wire:click="generate" class="col-span-2 btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Tampilkan
        </button>
    </div>
</div>

@if($hasData)
<div class="flex justify-end gap-2 mb-4">
    <button wire:click="simpanHistory" class="btn-secondary text-xs">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Simpan History
    </button>
    <button wire:click="downloadExcel" class="btn-secondary text-xs">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export Excel
    </button>
    <button wire:click="downloadPdf" class="btn-primary text-xs">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Cetak PDF
    </button>
</div>

{{-- Summary Header --}}
<div class="card mb-4">
    <h3 class="text-sm font-bold text-gray-700 mb-1">
        Laporan Keuangan Periodik
    </h3>
    <p class="text-xs text-gray-400 mb-4">
        Periode: {{ \Carbon\Carbon::parse($from)->isoFormat('D MMMM Y') }} –
        {{ \Carbon\Carbon::parse($to)->isoFormat('D MMMM Y') }}
    </p>

    {{-- Ringkasan Global --}}
    <div class="grid grid-cols-3 gap-3 mb-5">
        <div class="bg-emerald-50 rounded-xl p-3 text-center">
            <p class="text-xs text-gray-500">Total Masuk</p>
            <p class="text-sm font-extrabold text-emerald-600">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</p>
        </div>
        <div class="bg-red-50 rounded-xl p-3 text-center">
            <p class="text-xs text-gray-500">Total Keluar</p>
            <p class="text-sm font-extrabold text-red-500">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</p>
        </div>
        <div class="bg-blue-50 rounded-xl p-3 text-center">
            <p class="text-xs text-gray-500">Netto Periode</p>
            <p class="text-sm font-extrabold text-blue-600">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Tabel Kategori --}}
    <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Per Kategori</h4>
    {{-- Mobile --}}
    <div class="sm:hidden divide-y divide-gray-100 border rounded-xl mb-5">
        @foreach($dataKategori as $dk)
        <div class="px-4 py-3 space-y-1">
            <p class="font-semibold text-gray-800 text-sm">{{ $dk['nama'] }}</p>
            <div class="space-y-0.5 text-xs">
                <div class="flex justify-between"><span class="text-gray-400">Saldo Awal</span><span class="text-gray-600">Rp {{ number_format($dk['saldo_awal'], 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-400">Masuk</span><span class="text-emerald-600 font-medium">Rp {{ number_format($dk['masuk'], 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-400">Keluar</span><span class="text-red-500 font-medium">Rp {{ number_format($dk['keluar'], 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-400">Saldo Akhir</span><span class="font-bold text-blue-600">Rp {{ number_format($dk['saldo_akhir'], 0, ',', '.') }}</span></div>
            </div>
        </div>
        @endforeach
        <div class="px-4 py-3 bg-gray-100 space-y-1">
            <p class="font-bold text-gray-800 text-sm">Total</p>
            <div class="space-y-0.5 text-xs font-bold">
                <div class="flex justify-between"><span class="text-gray-500">Saldo Awal</span><span class="text-gray-600">Rp {{ number_format(collect($dataKategori)->sum('saldo_awal'), 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Masuk</span><span class="text-emerald-600">Rp {{ number_format(collect($dataKategori)->sum('masuk'), 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Keluar</span><span class="text-red-500">Rp {{ number_format(collect($dataKategori)->sum('keluar'), 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Saldo Akhir</span><span class="text-blue-600">Rp {{ number_format(collect($dataKategori)->sum('saldo_akhir'), 0, ',', '.') }}</span></div>
            </div>
        </div>
    </div>
    {{-- Desktop --}}
    <div class="hidden sm:block overflow-x-auto mb-5">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-3 py-2 text-xs font-bold text-gray-500">Kategori</th>
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Saldo Awal</th>
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Masuk</th>
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Keluar</th>
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Saldo Akhir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($dataKategori as $dk)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2.5 font-medium text-gray-800">{{ $dk['nama'] }}</td>
                    <td class="px-3 py-2.5 text-right text-gray-600">Rp {{ number_format($dk['saldo_awal'], 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-emerald-600 font-semibold">Rp {{ number_format($dk['masuk'], 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-red-500 font-semibold">Rp {{ number_format($dk['keluar'], 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-blue-600 font-bold">Rp {{ number_format($dk['saldo_akhir'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="bg-gray-100 font-bold">
                    <td class="px-3 py-2.5 text-gray-800">Total</td>
                    <td class="px-3 py-2.5 text-right text-gray-600">Rp {{ number_format(collect($dataKategori)->sum('saldo_awal'), 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-emerald-600">Rp {{ number_format(collect($dataKategori)->sum('masuk'), 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-red-500">Rp {{ number_format(collect($dataKategori)->sum('keluar'), 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-blue-600">Rp {{ number_format(collect($dataKategori)->sum('saldo_akhir'), 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Tabel Rekening --}}
    <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Per Rekening</h4>
    {{-- Mobile --}}
    <div class="sm:hidden divide-y divide-gray-100 border rounded-xl">
        @foreach($dataRekening as $dr)
        <div class="px-4 py-3 space-y-1">
            <p class="font-semibold text-gray-800 text-sm">{{ $dr['nama'] }}</p>
            <p class="text-[11px] text-gray-400">{{ $dr['atas_nama'] }} &middot; {{ $dr['no_rek'] }}</p>
            <div class="space-y-0.5 text-xs">
                <div class="flex justify-between"><span class="text-gray-400">Saldo Awal</span><span class="text-gray-600">Rp {{ number_format($dr['saldo_awal'], 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-400">Masuk</span><span class="text-emerald-600 font-medium">Rp {{ number_format($dr['masuk'], 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-400">Keluar</span><span class="text-red-500 font-medium">Rp {{ number_format($dr['keluar'], 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-400">Saldo Akhir</span><span class="font-bold text-blue-600">Rp {{ number_format($dr['saldo_akhir'], 0, ',', '.') }}</span></div>
            </div>
        </div>
        @endforeach
        <div class="px-4 py-3 bg-gray-100 space-y-1">
            <p class="font-bold text-gray-800 text-sm">Total</p>
            <div class="space-y-0.5 text-xs font-bold">
                <div class="flex justify-between"><span class="text-gray-500">Saldo Awal</span><span class="text-gray-600">Rp {{ number_format(collect($dataRekening)->sum('saldo_awal'), 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Saldo Akhir</span><span class="text-blue-600">Rp {{ number_format(collect($dataRekening)->sum('saldo_akhir'), 0, ',', '.') }}</span></div>
            </div>
        </div>
    </div>
    {{-- Desktop --}}
    <div class="hidden sm:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-3 py-2 text-xs font-bold text-gray-500">Rekening</th>
                    <th class="text-left px-3 py-2 text-xs font-bold text-gray-500">No. Rek</th>
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Saldo Awal</th>
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Masuk</th>
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Keluar</th>
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Saldo Akhir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($dataRekening as $dr)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2.5">
                        <p class="font-medium text-gray-800">{{ $dr['nama'] }}</p>
                        <p class="text-xs text-gray-400">{{ $dr['atas_nama'] }}</p>
                    </td>
                    <td class="px-3 py-2.5 text-gray-600">{{ $dr['no_rek'] }}</td>
                    <td class="px-3 py-2.5 text-right text-gray-600">Rp {{ number_format($dr['saldo_awal'], 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-emerald-600 font-semibold">Rp {{ number_format($dr['masuk'], 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-red-500 font-semibold">Rp {{ number_format($dr['keluar'], 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-blue-600 font-bold">Rp {{ number_format($dr['saldo_akhir'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="bg-gray-100 font-bold">
                    <td class="px-3 py-2.5 text-gray-800" colspan="2">Total</td>
                    <td class="px-3 py-2.5 text-right text-gray-600">Rp {{ number_format(collect($dataRekening)->sum('saldo_awal'), 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-gray-500">-</td>
                    <td class="px-3 py-2.5 text-right text-gray-500">-</td>
                    <td class="px-3 py-2.5 text-right text-blue-600">Rp {{ number_format(collect($dataRekening)->sum('saldo_akhir'), 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Detail Masuk --}}
<div class="card mb-4">
    <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Detail Transaksi Masuk</h4>
    {{-- Mobile --}}
    <div class="sm:hidden divide-y divide-gray-100">
        @forelse($detailMasuk as $trx)
        <div class="py-3 space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($trx['tanggal'])->isoFormat('D MMM Y') }}</span>
                <span class="text-sm font-bold text-emerald-600">Rp {{ number_format($trx['masuk'] ?? 0, 0, ',', '.') }}</span>
            </div>
            <p class="text-sm text-gray-800">{{ $trx['keterangan'] ?: '-' }}</p>
            <div class="flex gap-2 text-[11px] text-gray-400">
                <span>{{ $trx['kategori']['nama'] ?? '-' }}</span>
                <span>&middot;</span>
                <span>{{ $trx['rekening']['nama_rek'] ?? '-' }}</span>
            </div>
        </div>
        @empty
        <div class="py-6 text-center text-gray-400 text-sm">Tidak ada transaksi masuk pada periode ini.</div>
        @endforelse
        @if(count($detailMasuk) > 0)
        <div class="py-3 flex justify-between font-bold">
            <span class="text-sm text-gray-800">Total Masuk</span>
            <span class="text-sm text-emerald-700">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</span>
        </div>
        @endif
    </div>
    {{-- Desktop --}}
    <div class="hidden sm:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-3 py-2 text-xs font-bold text-gray-500">Tanggal</th>
                    <th class="text-left px-3 py-2 text-xs font-bold text-gray-500">Kategori</th>
                    <th class="text-left px-3 py-2 text-xs font-bold text-gray-500">Rekening</th>
                    <th class="text-left px-3 py-2 text-xs font-bold text-gray-500">Keterangan</th>
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Masuk</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($detailMasuk as $trx)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2.5 text-gray-600 whitespace-nowrap">{{ \Carbon\Carbon::parse($trx['tanggal'])->format('d/m/Y') }}</td>
                    <td class="px-3 py-2.5 text-gray-700">{{ $trx['kategori']['nama'] ?? '-' }}</td>
                    <td class="px-3 py-2.5 text-gray-700">{{ $trx['rekening']['nama_rek'] ?? '-' }}</td>
                    <td class="px-3 py-2.5 text-gray-700">{{ $trx['keterangan'] ?: '-' }}</td>
                    <td class="px-3 py-2.5 text-right text-emerald-600 font-semibold">Rp {{ number_format($trx['masuk'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-3 py-6 text-center text-gray-400">Tidak ada transaksi masuk pada periode ini.</td>
                </tr>
                @endforelse
                <tr class="bg-emerald-50 font-bold">
                    <td colspan="4" class="px-3 py-2.5 text-gray-800">Total Detail Masuk</td>
                    <td class="px-3 py-2.5 text-right text-emerald-700">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Detail Keluar --}}
<div class="card mb-4">
    <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Detail Transaksi Keluar</h4>
    {{-- Mobile --}}
    <div class="sm:hidden divide-y divide-gray-100">
        @forelse($detailKeluar as $trx)
        <div class="py-3 space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($trx['tanggal'])->isoFormat('D MMM Y') }}</span>
                <span class="text-sm font-bold text-red-500">Rp {{ number_format($trx['keluar'] ?? 0, 0, ',', '.') }}</span>
            </div>
            <p class="text-sm text-gray-800">{{ $trx['keterangan'] ?: '-' }}</p>
            <div class="flex gap-2 text-[11px] text-gray-400">
                <span>{{ $trx['kategori']['nama'] ?? '-' }}</span>
                <span>&middot;</span>
                <span>{{ $trx['rekening']['nama_rek'] ?? '-' }}</span>
            </div>
        </div>
        @empty
        <div class="py-6 text-center text-gray-400 text-sm">Tidak ada transaksi keluar pada periode ini.</div>
        @endforelse
        @if(count($detailKeluar) > 0)
        <div class="py-3 flex justify-between font-bold">
            <span class="text-sm text-gray-800">Total Keluar</span>
            <span class="text-sm text-red-600">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</span>
        </div>
        @endif
    </div>
    {{-- Desktop --}}
    <div class="hidden sm:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-3 py-2 text-xs font-bold text-gray-500">Tanggal</th>
                    <th class="text-left px-3 py-2 text-xs font-bold text-gray-500">Kategori</th>
                    <th class="text-left px-3 py-2 text-xs font-bold text-gray-500">Rekening</th>
                    <th class="text-left px-3 py-2 text-xs font-bold text-gray-500">Keterangan</th>
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Keluar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($detailKeluar as $trx)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2.5 text-gray-600 whitespace-nowrap">{{ \Carbon\Carbon::parse($trx['tanggal'])->format('d/m/Y') }}</td>
                    <td class="px-3 py-2.5 text-gray-700">{{ $trx['kategori']['nama'] ?? '-' }}</td>
                    <td class="px-3 py-2.5 text-gray-700">{{ $trx['rekening']['nama_rek'] ?? '-' }}</td>
                    <td class="px-3 py-2.5 text-gray-700">{{ $trx['keterangan'] ?: '-' }}</td>
                    <td class="px-3 py-2.5 text-right text-red-500 font-semibold">Rp {{ number_format($trx['keluar'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-3 py-6 text-center text-gray-400">Tidak ada transaksi keluar pada periode ini.</td>
                </tr>
                @endforelse
                <tr class="bg-red-50 font-bold">
                    <td colspan="4" class="px-3 py-2.5 text-gray-800">Total Detail Keluar</td>
                    <td class="px-3 py-2.5 text-right text-red-600">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="flex items-center justify-between">
        <p class="text-sm font-bold text-gray-700">Total Akhir Periodik</p>
        <p class="text-lg font-extrabold {{ $totalSaldo >= 0 ? 'text-blue-600' : 'text-red-600' }}">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</p>
    </div>
</div>
@else
<div class="card text-center py-14">
    <svg class="w-16 h-16 text-gray-200 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    <p class="text-gray-400 text-sm">Pilih periode dan klik <strong>Tampilkan</strong> untuk melihat laporan.</p>
</div>
@endif

</div>

