<?php

use App\Models\Kategori;
use App\Models\Keuangan;
use App\Models\Rekening;
use Livewire\Volt\Component;

new class extends Component
{
    public function render(): mixed
    {
        return parent::render()->title('Laporan Bulanan');
    }

    public int    $bulan  = 0;
    public int    $tahun  = 0;
    public bool   $hasData = false;

    public array $dataKategori = [];
    public array $dataRekening = [];
    public array $detailPerKategori = [];
    public float $totalMasuk  = 0;
    public float $totalKeluar = 0;
    public float $totalSaldo  = 0;

    public function mount(): void
    {
        $this->bulan = now()->month;
        $this->tahun = now()->year;
    }

    public function generate(): void
    {
        $this->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2099',
        ]);

        $from = \Carbon\Carbon::create($this->tahun, $this->bulan, 1)->startOfMonth()->format('Y-m-d');
        $to   = \Carbon\Carbon::create($this->tahun, $this->bulan, 1)->endOfMonth()->format('Y-m-d');

        $kategoriList = Kategori::all();
        $rekeningList = Rekening::all();

        $this->dataKategori = $kategoriList->map(function ($kat) use ($from, $to) {
            $masuk  = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereBetween('tanggal', [$from, $to])->sum('masuk');
            $keluar = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereBetween('tanggal', [$from, $to])->sum('keluar');
            $mIn    = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereDate('tanggal', '<', $from)->sum('masuk');
            $mOut   = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereDate('tanggal', '<', $from)->sum('keluar');
            $saldoAwal = $kat->saldo_awal + $mIn - $mOut;
            return ['nama' => $kat->nama, 'saldo_awal' => $saldoAwal, 'masuk' => $masuk, 'keluar' => $keluar, 'saldo_akhir' => $saldoAwal + $masuk - $keluar];
        })->toArray();

        $this->dataRekening = $rekeningList->map(function ($rek) use ($from, $to) {
            $summary = $rek->reportBalanceSummary($from, $to);
            return ['nama' => $rek->nama_rek, 'atas_nama' => $rek->atas_nama, 'no_rek' => $rek->no_rek] + $summary;
        })->toArray();

        $this->detailPerKategori = $kategoriList->map(function ($kat) use ($from, $to) {
            $transaksi = Keuangan::with(['rekening', 'creator'])
                ->where('id_kategori', $kat->id)
                ->withoutSaldoAwal()
                ->whereBetween('tanggal', [$from, $to])
                ->orderBy('tanggal')->get()->toArray();
            return ['kategori' => $kat->nama, 'transaksi' => $transaksi];
        })->toArray();

        $this->totalMasuk  = collect($this->dataKategori)->sum('masuk');
        $this->totalKeluar = collect($this->dataKategori)->sum('keluar');
        $this->totalSaldo  = collect($this->dataKategori)->sum('saldo_akhir');
        $this->hasData     = true;
    }

    public function downloadPdf(): void
    {
        $from = \Carbon\Carbon::create($this->tahun, $this->bulan, 1)->startOfMonth()->format('Y-m-d');
        $to   = \Carbon\Carbon::create($this->tahun, $this->bulan, 1)->endOfMonth()->format('Y-m-d');
        $params = http_build_query(['from' => $from, 'to' => $to, 'tipe' => 'bulanan']);
        $this->redirect('/export/laporan-pdf?' . $params);
    }

    public function downloadExcel(): void
    {
        $params = http_build_query(['bulan' => $this->bulan, 'tahun' => $this->tahun]);
        $this->redirect('/export/laporan-bulanan?' . $params);
    }

    public function getBulanLabel(): string
    {
        return \Carbon\Carbon::create($this->tahun, $this->bulan, 1)->isoFormat('MMMM Y');
    }
}; ?>

<div>
<div class="card mb-5">
    <h2 class="text-sm font-bold text-gray-700 mb-4">Pilih Bulan & Tahun</h2>
    <div class="grid grid-cols-2 sm:flex sm:flex-row gap-3 items-end">
        <div class="col-span-1 sm:flex-1">
            <label class="label">Bulan</label>
            <select wire:model="bulan" class="input">
                @foreach(range(1,12) as $m)
                <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m)->isoFormat('MMMM') }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-span-1 sm:flex-1">
            <label class="label">Tahun</label>
            <select wire:model="tahun" class="input">
                @foreach(range(now()->year, 2020) as $y)
                <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
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
    <button wire:click="downloadExcel" class="btn-secondary text-xs">Export Excel</button>
    <button wire:click="downloadPdf"   class="btn-primary text-xs">Cetak PDF</button>
</div>

{{-- Summary --}}
<div class="card mb-4">
    <h3 class="text-sm font-bold text-gray-700 mb-1">Laporan Bulan: {{ $this->getBulanLabel() }}</h3>

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
            <p class="text-xs text-gray-500">Saldo Akhir</p>
            <p class="text-sm font-extrabold text-blue-600">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</p>
        </div>
    </div>

    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Ringkasan per Kategori</h4>
    {{-- Mobile --}}
    <div class="sm:hidden divide-y divide-gray-100 border rounded-xl mb-4">
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
                <div class="flex justify-between"><span class="text-gray-500">Saldo Awal</span><span>Rp {{ number_format(collect($dataKategori)->sum('saldo_awal'), 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Masuk</span><span class="text-emerald-600">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Keluar</span><span class="text-red-500">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Saldo Akhir</span><span class="text-blue-600">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</span></div>
            </div>
        </div>
    </div>
    {{-- Desktop --}}
    <div class="hidden sm:block overflow-x-auto mb-4">
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
                <tr>
                    <td class="px-3 py-2.5 font-medium text-gray-800">{{ $dk['nama'] }}</td>
                    <td class="px-3 py-2.5 text-right text-gray-600">Rp {{ number_format($dk['saldo_awal'], 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-emerald-600">Rp {{ number_format($dk['masuk'], 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-red-500">Rp {{ number_format($dk['keluar'], 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-blue-600 font-bold">Rp {{ number_format($dk['saldo_akhir'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="bg-gray-100 font-bold">
                    <td class="px-3 py-2.5">Total</td>
                    <td class="px-3 py-2.5 text-right">Rp {{ number_format(collect($dataKategori)->sum('saldo_awal'), 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-emerald-600">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-red-500">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-blue-600">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Saldo per Rekening</h4>
    {{-- Mobile --}}
    <div class="sm:hidden divide-y divide-gray-100 border rounded-xl">
        @foreach($dataRekening as $dr)
        <div class="px-4 py-3 space-y-1">
            <p class="font-semibold text-gray-800 text-sm">{{ $dr['nama'] }}</p>
            <p class="text-[11px] text-gray-400">{{ $dr['atas_nama'] ?? '' }} &middot; {{ $dr['no_rek'] }}</p>
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
                <div class="flex justify-between"><span class="text-gray-500">Saldo Awal</span><span>Rp {{ number_format(collect($dataRekening)->sum('saldo_awal'), 0, ',', '.') }}</span></div>
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
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Saldo Awal</th>
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Masuk</th>
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Keluar</th>
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Saldo Akhir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($dataRekening as $dr)
                <tr>
                    <td class="px-3 py-2.5"><p class="font-medium text-gray-800">{{ $dr['nama'] }}</p><p class="text-xs text-gray-400">{{ $dr['no_rek'] }}</p></td>
                    <td class="px-3 py-2.5 text-right text-gray-600">Rp {{ number_format($dr['saldo_awal'], 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-emerald-600">Rp {{ number_format($dr['masuk'], 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-red-500">Rp {{ number_format($dr['keluar'], 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-blue-600 font-bold">Rp {{ number_format($dr['saldo_akhir'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="bg-gray-100 font-bold">
                    <td class="px-3 py-2.5">Total</td>
                    <td class="px-3 py-2.5 text-right">Rp {{ number_format(collect($dataRekening)->sum('saldo_awal'), 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-gray-500">-</td>
                    <td class="px-3 py-2.5 text-right text-gray-500">-</td>
                    <td class="px-3 py-2.5 text-right text-blue-600">Rp {{ number_format(collect($dataRekening)->sum('saldo_akhir'), 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Detail per kategori --}}
<div class="space-y-4">
    @foreach($detailPerKategori as $detail)
    @if(count($detail['transaksi']) > 0)
    <div class="card">
        <h4 class="text-sm font-bold text-gray-700 mb-3">Detail: {{ $detail['kategori'] }}</h4>
        {{-- Mobile --}}
        <div class="sm:hidden divide-y divide-gray-100">
            @foreach($detail['transaksi'] as $trx)
            <div class="py-2.5 space-y-0.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($trx['tanggal'])->isoFormat('D MMM Y') }}</span>
                    @if($trx['masuk'] > 0)
                    <span class="text-sm font-bold text-emerald-600">+Rp {{ number_format($trx['masuk'], 0, ',', '.') }}</span>
                    @else
                    <span class="text-sm font-bold text-red-500">-Rp {{ number_format($trx['keluar'], 0, ',', '.') }}</span>
                    @endif
                </div>
                <p class="text-sm text-gray-800">{{ $trx['keterangan'] ?? '-' }}</p>
            </div>
            @endforeach
        </div>
        {{-- Desktop --}}
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-3 py-2 text-xs font-bold text-gray-500">Tanggal</th>
                        <th class="text-left px-3 py-2 text-xs font-bold text-gray-500">Keterangan</th>
                        <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Masuk</th>
                        <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Keluar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($detail['transaksi'] as $trx)
                    <tr>
                        <td class="px-3 py-2 text-gray-600">{{ \Carbon\Carbon::parse($trx['tanggal'])->format('d/m/Y') }}</td>
                        <td class="px-3 py-2 text-gray-800">{{ $trx['keterangan'] ?? '-' }}</td>
                        <td class="px-3 py-2 text-right text-emerald-600">{{ $trx['masuk'] > 0 ? 'Rp '.number_format($trx['masuk'], 0, ',', '.') : '' }}</td>
                        <td class="px-3 py-2 text-right text-red-500">{{ $trx['keluar'] > 0 ? 'Rp '.number_format($trx['keluar'], 0, ',', '.') : '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @endforeach
</div>
@else
<div class="card text-center py-14">
    <p class="text-gray-400 text-sm">Pilih bulan dan tahun lalu klik <strong>Tampilkan</strong>.</p>
</div>
@endif

</div>

