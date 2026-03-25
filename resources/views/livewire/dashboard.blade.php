<?php

use App\Models\Kategori;
use App\Models\Keuangan;
use App\Models\Rekening;
use Livewire\Volt\Component;

new class extends Component
{
    public function render(): mixed
    {
        return parent::render()->title('Dashboard');
    }

    public function with(): array
    {
        $now        = now();
        $startMonth = $now->copy()->startOfMonth();
        $endMonth   = $now->copy()->endOfMonth();

        $totalMasukBulan  = Keuangan::whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)->sum('masuk');
        $totalKeluarBulan = Keuangan::whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)->sum('keluar');

        $saldoAwalBulan   = Keuangan::where('tanggal', '<', $startMonth)
            ->selectRaw('COALESCE(SUM(masuk),0) - COALESCE(SUM(keluar),0) as saldo')
            ->value('saldo') ?? 0;

        $totalMasukAll    = Keuangan::sum('masuk');
        $totalKeluarAll   = Keuangan::sum('keluar');
        $sisaSaldo        = $totalMasukAll - $totalKeluarAll;

        $rekening = Rekening::all()->map(function ($rek) {
            return array_merge($rek->toArray(), ['saldo' => $rek->saldo]);
        });

        $kategori   = Kategori::all();

        $transaksiTerbaru = Keuangan::with(['rekening', 'kategori', 'creator'])
            ->orderBy('created_at', 'desc')->limit(8)->get();

        return compact(
            'saldoAwalBulan', 'totalMasukBulan', 'totalKeluarBulan', 'sisaSaldo',
            'rekening', 'kategori', 'transaksiTerbaru'
        );
    }
}; ?>

<div>
    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Saldo Awal Bulan --}}
        <div class="card flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium">Saldo Awal Bulan</p>
                <p class="text-xl font-extrabold text-amber-600">
                    Rp {{ number_format($saldoAwalBulan, 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- Masuk --}}
        <div class="card flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium">Masuk Bulan Ini</p>
                <p class="text-xl font-extrabold text-emerald-600">
                    Rp {{ number_format($totalMasukBulan, 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- Keluar --}}
        <div class="card flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium">Keluar Bulan Ini</p>
                <p class="text-xl font-extrabold text-red-500">
                    Rp {{ number_format($totalKeluarBulan, 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- Sisa Saldo --}}
        <div class="card flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium">Total Saldo</p>
                <p class="text-xl font-extrabold text-blue-600">
                    Rp {{ number_format($sisaSaldo, 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        {{-- Saldo per Rekening --}}
        <div class="card">
            <h2 class="text-sm font-bold text-gray-700 mb-4">Saldo per Rekening</h2>
            <div class="space-y-3">
                @foreach($rekening as $rek)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ $rek['nama_rek'] }}</p>
                        <p class="text-xs text-gray-400">{{ $rek['atas_nama'] }} · {{ $rek['no_rek'] }}</p>
                    </div>
                    <p class="text-sm font-bold {{ $rek['saldo'] >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                        Rp {{ number_format($rek['saldo'], 0, ',', '.') }}
                    </p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Saldo per Kategori --}}
        <div class="card">
            <h2 class="text-sm font-bold text-gray-700 mb-4">Saldo per Kategori</h2>
            <div class="space-y-3">
                @foreach($kategori as $kat)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ $kat->nama }}</p>
                        <div class="flex gap-3 mt-0.5">
                            <span class="text-xs text-emerald-600">↑ Rp {{ number_format($kat->masuk, 0, ',', '.') }}</span>
                            <span class="text-xs text-red-500">↓ Rp {{ number_format($kat->keluar, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <p class="text-sm font-bold text-blue-600">
                        Rp {{ number_format($kat->saldo_akhir, 0, ',', '.') }}
                    </p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Transaksi Terbaru --}}
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-gray-700">Transaksi Terbaru</h2>
                <a href="{{ route('arus-kas') }}" class="text-xs text-primary-600 font-semibold hover:underline">Lihat Semua →</a>
            </div>
            <div class="space-y-2">
                @forelse($transaksiTerbaru as $trx)
                <div class="flex items-start justify-between py-2 border-b border-gray-100 last:border-0">
                    <div class="flex-1 min-w-0 pr-2">
                        <p class="text-xs font-semibold text-gray-700 truncate">{{ $trx->keterangan ?? '-' }}</p>
                        <p class="text-xs text-gray-400">{{ $trx->tanggal->format('d/m/Y') }} · {{ $trx->kategori->nama }}</p>
                    </div>
                    @if($trx->masuk > 0)
                        <span class="text-xs font-bold text-emerald-600 whitespace-nowrap">+{{ number_format($trx->masuk, 0, ',', '.') }}</span>
                    @else
                        <span class="text-xs font-bold text-red-500 whitespace-nowrap">-{{ number_format($trx->keluar, 0, ',', '.') }}</span>
                    @endif
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-6">Belum ada transaksi</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

