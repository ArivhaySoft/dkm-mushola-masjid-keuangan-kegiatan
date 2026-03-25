<?php

use App\Models\Kategori;
use App\Models\Keuangan;
use App\Models\Rekening;
use Livewire\Volt\Component;

new class extends Component
{
    public function render(): mixed
    {
        return parent::render()->title('Laporan Tahunan');
    }

    public int  $tahun   = 0;
    public bool $hasData = false;
    public array $dataPerBulan = [];
    public array $dataKategori = [];
    public array $dataRekening = [];
    public float $totalMasuk   = 0;
    public float $totalKeluar  = 0;

    public function mount(): void
    {
        $this->tahun = now()->year;
    }

    public function generate(): void
    {
        $this->validate(['tahun' => 'required|integer|min:2000|max:2099']);

        $kategoriList = Kategori::all();
        $rekeningList = Rekening::all();
        $startYear = \Carbon\Carbon::create($this->tahun, 1, 1)->startOfYear()->format('Y-m-d');
        $this->dataKategori = $kategoriList->map(function ($kat) use ($startYear) {
            $masuk  = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereYear('tanggal', $this->tahun)->sum('masuk');
            $keluar = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereYear('tanggal', $this->tahun)->sum('keluar');
            $mIn    = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereDate('tanggal', '<', $startYear)->sum('masuk');
            $mOut   = Keuangan::where('id_kategori', $kat->id)->withoutSaldoAwal()->whereDate('tanggal', '<', $startYear)->sum('keluar');
            $saldoAwal = $kat->saldo_awal + $mIn - $mOut;

            return [
                'nama' => $kat->nama,
                'saldo_awal' => $saldoAwal,
                'masuk' => $masuk,
                'keluar' => $keluar,
                'saldo_akhir' => $saldoAwal + $masuk - $keluar,
            ];
        })->toArray();

        $from = \Carbon\Carbon::create($this->tahun, 1, 1)->startOfYear()->format('Y-m-d');
        $to   = \Carbon\Carbon::create($this->tahun, 1, 1)->endOfYear()->format('Y-m-d');

        $this->dataRekening = $rekeningList->map(function ($rek) use ($from, $to) {
            $summary = $rek->reportBalanceSummary($from, $to);

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

        $this->dataPerBulan = collect(range(1, 12))->map(function ($m) {
            $startMonth = \Carbon\Carbon::create($this->tahun, $m, 1)->startOfMonth()->format('Y-m-d');
            $masuk  = Keuangan::whereYear('tanggal', $this->tahun)->whereMonth('tanggal', $m)->sum('masuk');
            $keluar = Keuangan::whereYear('tanggal', $this->tahun)->whereMonth('tanggal', $m)->sum('keluar');
            $saldoAwal = Keuangan::whereDate('tanggal', '<', $startMonth)
                ->selectRaw('COALESCE(SUM(masuk),0) - COALESCE(SUM(keluar),0) as saldo')
                ->value('saldo') ?? 0;

            return [
                'bulan'  => \Carbon\Carbon::create($this->tahun, $m)->isoFormat('MMM'),
                'saldo_awal' => $saldoAwal,
                'masuk'  => $masuk,
                'keluar' => $keluar,
                'saldo_akhir'  => $saldoAwal + $masuk - $keluar,
            ];
        })->toArray();

        $this->totalMasuk  = collect($this->dataPerBulan)->sum('masuk');
        $this->totalKeluar = collect($this->dataPerBulan)->sum('keluar');
        $this->hasData     = true;
    }

    public function downloadExcel(): void
    {
        $this->redirect('/export/laporan-tahunan?tahun=' . $this->tahun);
    }

    public function downloadPdf(): void
    {
        $this->redirect('/export/laporan-pdf-tahunan?tahun=' . $this->tahun);
    }
}; ?>

<div>
<div class="card mb-5">
    <div class="grid grid-cols-2 sm:flex sm:flex-row gap-3 items-end">
        <div class="col-span-1 sm:flex-1">
            <label class="label">Tahun</label>
            <select wire:model="tahun" class="input">
                @foreach(range(now()->year, 2020) as $y)
                <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <button wire:click="generate" class="col-span-1 btn-primary">Tampilkan</button>
    </div>
</div>

@if($hasData)
<div class="flex justify-end gap-2 mb-4">
    <button wire:click="downloadExcel" class="btn-secondary text-xs">Export Excel</button>
    <button wire:click="downloadPdf"   class="btn-primary text-xs">Cetak PDF</button>
</div>

{{-- Summary --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
    <div class="card text-center">
        <p class="text-xs text-gray-500">Total Masuk {{ $tahun }}</p>
        <p class="text-lg font-extrabold text-emerald-600">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</p>
    </div>
    <div class="card text-center">
        <p class="text-xs text-gray-500">Total Keluar {{ $tahun }}</p>
        <p class="text-lg font-extrabold text-red-500">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</p>
    </div>
    <div class="card text-center">
        <p class="text-xs text-gray-500">Surplus/Defisit</p>
        <p class="text-lg font-extrabold {{ $totalMasuk - $totalKeluar >= 0 ? 'text-blue-600' : 'text-red-600' }}">
            Rp {{ number_format($totalMasuk - $totalKeluar, 0, ',', '.') }}
        </p>
    </div>
</div>

{{-- Per bulan --}}
<div class="card mb-4">
    <h3 class="text-sm font-bold text-gray-700 mb-3">Rekap Per Bulan – {{ $tahun }}</h3>
    {{-- Mobile --}}
    <div class="sm:hidden divide-y divide-gray-100">
        @foreach($dataPerBulan as $row)
        <div class="py-2.5 space-y-0.5 {{ $row['masuk'] == 0 && $row['keluar'] == 0 ? 'opacity-40' : '' }}">
            <p class="font-semibold text-gray-800 text-sm">{{ $row['bulan'] }}</p>
            <div class="space-y-0.5 text-xs">
                <div class="flex justify-between"><span class="text-gray-400">Saldo Awal</span><span class="text-gray-600">Rp {{ number_format($row['saldo_awal'], 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-400">Masuk</span><span class="text-emerald-600 font-medium">{{ $row['masuk'] > 0 ? 'Rp '.number_format($row['masuk'], 0, ',', '.') : '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-400">Keluar</span><span class="text-red-500 font-medium">{{ $row['keluar'] > 0 ? 'Rp '.number_format($row['keluar'], 0, ',', '.') : '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-400">Saldo Akhir</span><span class="font-bold {{ $row['saldo_akhir'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">Rp {{ number_format($row['saldo_akhir'], 0, ',', '.') }}</span></div>
            </div>
        </div>
        @endforeach
        <div class="py-2.5 bg-gray-100 px-1 space-y-0.5">
            <p class="font-bold text-gray-800 text-sm">Total</p>
            <div class="space-y-0.5 text-xs font-bold">
                <div class="flex justify-between"><span class="text-gray-500">Masuk</span><span class="text-emerald-600">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Keluar</span><span class="text-red-500">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Surplus/Defisit</span><span class="text-blue-600">Rp {{ number_format($totalMasuk - $totalKeluar, 0, ',', '.') }}</span></div>
            </div>
        </div>
    </div>
    {{-- Desktop --}}
    <div class="hidden sm:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-3 py-2 text-xs font-bold text-gray-500">Bulan</th>
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Saldo Awal</th>
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Masuk</th>
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Keluar</th>
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Saldo Akhir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($dataPerBulan as $row)
                <tr class="{{ $row['masuk'] == 0 && $row['keluar'] == 0 ? 'text-gray-300' : '' }}">
                    <td class="px-3 py-2.5 font-medium">{{ $row['bulan'] }}</td>
                    <td class="px-3 py-2.5 text-right text-gray-600">Rp {{ number_format($row['saldo_awal'], 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-emerald-600">{{ $row['masuk'] > 0 ? 'Rp '.number_format($row['masuk'], 0, ',', '.') : '-' }}</td>
                    <td class="px-3 py-2.5 text-right text-red-500">{{ $row['keluar'] > 0 ? 'Rp '.number_format($row['keluar'], 0, ',', '.') : '-' }}</td>
                    <td class="px-3 py-2.5 text-right font-semibold {{ $row['saldo_akhir'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                        Rp {{ number_format($row['saldo_akhir'], 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
                <tr class="bg-gray-100 font-bold">
                    <td class="px-3 py-2.5">Total</td>
                    <td class="px-3 py-2.5 text-right text-gray-500">-</td>
                    <td class="px-3 py-2.5 text-right text-emerald-600">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-red-500">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-blue-600">Rp {{ number_format($totalMasuk - $totalKeluar, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Per kategori --}}
<div class="card mb-4">
    <h3 class="text-sm font-bold text-gray-700 mb-3">Rekap Per Kategori – {{ $tahun }}</h3>
    {{-- Mobile --}}
    <div class="sm:hidden divide-y divide-gray-100">
        @foreach($dataKategori as $dk)
        <div class="py-2.5 space-y-0.5">
            <p class="font-semibold text-gray-800 text-sm">{{ $dk['nama'] }}</p>
            <div class="space-y-0.5 text-xs">
                <div class="flex justify-between"><span class="text-gray-400">Saldo Awal</span><span class="text-gray-600">Rp {{ number_format($dk['saldo_awal'], 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-400">Masuk</span><span class="text-emerald-600 font-medium">Rp {{ number_format($dk['masuk'], 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-400">Keluar</span><span class="text-red-500 font-medium">Rp {{ number_format($dk['keluar'], 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-400">Saldo Akhir</span><span class="font-bold text-blue-600">Rp {{ number_format($dk['saldo_akhir'], 0, ',', '.') }}</span></div>
            </div>
        </div>
        @endforeach
        <div class="py-2.5 bg-gray-100 px-1 space-y-0.5">
            <p class="font-bold text-gray-800 text-sm">Total</p>
            <div class="space-y-0.5 text-xs font-bold">
                <div class="flex justify-between"><span class="text-gray-500">Saldo Awal</span><span>Rp {{ number_format(collect($dataKategori)->sum('saldo_awal'), 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Masuk</span><span class="text-emerald-600">Rp {{ number_format(collect($dataKategori)->sum('masuk'), 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Keluar</span><span class="text-red-500">Rp {{ number_format(collect($dataKategori)->sum('keluar'), 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Saldo Akhir</span><span class="text-blue-600">Rp {{ number_format(collect($dataKategori)->sum('saldo_akhir'), 0, ',', '.') }}</span></div>
            </div>
        </div>
    </div>
    {{-- Desktop --}}
    <div class="hidden sm:block overflow-x-auto">
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
                    <td class="px-3 py-2.5 text-gray-800">Total</td>
                    <td class="px-3 py-2.5 text-right text-gray-600">Rp {{ number_format(collect($dataKategori)->sum('saldo_awal'), 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-emerald-600">Rp {{ number_format(collect($dataKategori)->sum('masuk'), 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-red-500">Rp {{ number_format(collect($dataKategori)->sum('keluar'), 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-blue-600">Rp {{ number_format(collect($dataKategori)->sum('saldo_akhir'), 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Per rekening --}}
<div class="card">
    <h3 class="text-sm font-bold text-gray-700 mb-3">Rekap Per Rekening – {{ $tahun }}</h3>
    {{-- Mobile --}}
    <div class="sm:hidden divide-y divide-gray-100">
        @foreach($dataRekening as $dr)
        <div class="py-2.5 space-y-0.5">
            <p class="font-semibold text-gray-800 text-sm">{{ $dr['nama'] }}</p>
            <p class="text-[11px] text-gray-400">{{ $dr['atas_nama'] }} &middot; {{ $dr['no_rek'] }}</p>
            <div class="space-y-0.5 text-xs">
                <div class="flex justify-between"><span class="text-gray-400">Saldo Awal</span><span class="text-gray-600">Rp {{ number_format($dr['saldo_awal'], 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-400">Masuk</span><span class="text-emerald-600 font-medium">Rp {{ number_format($dr['masuk'], 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-400">Keluar</span><span class="text-red-500 font-medium">Rp {{ number_format($dr['keluar'], 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-400">Saldo Akhir</span><span class="font-bold {{ $dr['saldo_akhir'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">Rp {{ number_format($dr['saldo_akhir'], 0, ',', '.') }}</span></div>
            </div>
        </div>
        @endforeach
        <div class="py-2.5 bg-gray-100 px-1 space-y-0.5">
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
                    <th class="text-left px-3 py-2 text-xs font-bold text-gray-500">Atas Nama</th>
                    <th class="text-left px-3 py-2 text-xs font-bold text-gray-500">No. Rekening</th>
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Saldo Awal</th>
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Masuk</th>
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Keluar</th>
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Saldo Akhir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($dataRekening as $dr)
                <tr>
                    <td class="px-3 py-2.5 font-medium text-gray-800">{{ $dr['nama'] }}</td>
                    <td class="px-3 py-2.5 text-gray-600">{{ $dr['atas_nama'] }}</td>
                    <td class="px-3 py-2.5 text-gray-600">{{ $dr['no_rek'] }}</td>
                    <td class="px-3 py-2.5 text-right text-gray-600">Rp {{ number_format($dr['saldo_awal'], 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-emerald-600">Rp {{ number_format($dr['masuk'], 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-red-500">Rp {{ number_format($dr['keluar'], 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right font-bold {{ $dr['saldo_akhir'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">Rp {{ number_format($dr['saldo_akhir'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="bg-gray-100 font-bold">
                    <td class="px-3 py-2.5 text-gray-800" colspan="3">Total</td>
                    <td class="px-3 py-2.5 text-right text-gray-600">Rp {{ number_format(collect($dataRekening)->sum('saldo_awal'), 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right text-gray-500">-</td>
                    <td class="px-3 py-2.5 text-right text-gray-500">-</td>
                    <td class="px-3 py-2.5 text-right text-blue-600">Rp {{ number_format(collect($dataRekening)->sum('saldo_akhir'), 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@else
<div class="card text-center py-14">
    <p class="text-gray-400 text-sm">Pilih tahun dan klik <strong>Tampilkan</strong>.</p>
</div>
@endif

</div>

