<?php

use App\Models\Kategori;
use App\Models\Keuangan;
use App\Models\Rekening;
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

    public array $dataKategori  = [];
    public array $dataRekening  = [];
    public array $detailMasuk   = [];
    public array $detailKeluar  = [];
    public float $totalMasuk    = 0;
    public float $totalKeluar   = 0;
    public float $totalSaldo    = 0;

    public function mount(): void
    {
        $this->from = now()->startOfMonth()->format('Y-m-d');
        $this->to   = now()->format('Y-m-d');
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

        $this->dataKategori = $kategoriList->map(function ($kat) {
            $masuk  = Keuangan::where('id_kategori', $kat->id)
                ->withoutSaldoAwal()
                ->whereDate('tanggal', '>=', $this->from)
                ->whereDate('tanggal', '<=', $this->to)->sum('masuk');
            $keluar = Keuangan::where('id_kategori', $kat->id)
                ->withoutSaldoAwal()
                ->whereDate('tanggal', '>=', $this->from)
                ->whereDate('tanggal', '<=', $this->to)->sum('keluar');

            $mIn  = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereDate('tanggal', '<', $this->from)->sum('masuk');
            $mOut = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereDate('tanggal', '<', $this->from)->sum('keluar');
            $saldoAwal = $kat->saldo_awal + $mIn - $mOut;

            return [
                'nama'        => $kat->nama,
                'saldo_awal'  => $saldoAwal,
                'masuk'       => $masuk,
                'keluar'      => $keluar,
                'saldo_akhir' => $saldoAwal + $masuk - $keluar,
            ];
        })->toArray();

        $this->dataRekening = $rekeningList->map(function ($rek) {
            $summary = $rek->reportBalanceSummary($this->from, $this->to);

            return [
                'nama'        => $rek->nama_rek,
                'atas_nama'   => $rek->atas_nama,
                'no_rek'      => $rek->no_rek,
                'saldo_awal'  => $summary['saldo_awal'],
                'masuk'       => $summary['masuk'],
                'keluar'      => $summary['keluar'],
                'saldo_akhir' => $summary['saldo_akhir'],
            ];
        })->toArray();

        $this->totalMasuk  = collect($this->detailMasuk)->sum('masuk');
        $this->totalKeluar = collect($this->detailKeluar)->sum('keluar');
        $this->totalSaldo  = $this->totalMasuk - $this->totalKeluar;
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
    <h2 class="text-sm font-bold text-gray-700 mb-4">Filter Periode</h2>
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

