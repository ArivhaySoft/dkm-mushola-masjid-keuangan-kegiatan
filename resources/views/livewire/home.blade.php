<?php

use App\Models\Kategori;
use App\Models\Kegiatan;
use App\Models\JenisKegiatan;
use App\Models\JadwalPengajian;
use App\Models\Keuangan;
use App\Models\Setting;
use App\Models\User;
use App\Models\Visitor;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public function render(): mixed
    {
        return parent::render()
            ->title('Beranda')
            ->layout('components.layouts.public');
    }

    public string $search = '';
    public string $jenis  = '';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedJenis(): void { $this->resetPage(); }

    public function with(): array
    {
        $hasAdmin = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->exists();

        $showJadwal    = Setting::get('widget_jadwal_pengajian', '1') !== '0';
        $showKeuangan  = Setting::get('widget_posisi_keuangan',  '1') !== '0';
        $showKegiatan  = Setting::get('widget_kegiatan',         '1') !== '0';
        $showPengunjung = Setting::get('widget_pengunjung',      '1') !== '0';

        $data = $showKegiatan
            ? Kegiatan::with('creator', 'fotos')
                ->when($this->search, fn($q) => $q->where('judul', 'like', "%{$this->search}%"))
                ->when($this->jenis, fn($q) => $q->where('jenis', $this->jenis))
                ->orderBy('tanggal_kegiatan', 'desc')
                ->paginate(12)
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);

        $jadwal2Minggu = $showJadwal ? JadwalPengajian::where('aktif', true)->where('frekuensi', '2_minggu')->first() : null;
        $jadwalBulanan = $showJadwal ? JadwalPengajian::where('aktif', true)->where('frekuensi', 'bulanan')->first() : null;

        $visitorHariIni  = $showPengunjung ? Visitor::where('tanggal', today())->count() : 0;
        $visitorBulanIni = $showPengunjung ? Visitor::whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)->count() : 0;
        $visitorTotal    = $showPengunjung ? Visitor::count() : 0;

        $awalBulan = now()->startOfMonth();
        $akhirBulan = now()->endOfMonth();

        $from = $awalBulan->toDateString();
        $to   = $akhirBulan->toDateString();

        $kategoriKas = $showKeuangan
            ? Kategori::all()->map(function (Kategori $kategori) use ($from, $to) {
                $masuk     = (float) Keuangan::where('id_kategori', $kategori->id)->withoutSaldoAwal()->whereBetween('tanggal', [$from, $to])->sum('masuk');
                $keluar    = (float) Keuangan::where('id_kategori', $kategori->id)->withoutSaldoAwal()->whereBetween('tanggal', [$from, $to])->sum('keluar');
                $mIn       = (float) Keuangan::where('id_kategori', $kategori->id)->withoutSaldoAwal()->whereDate('tanggal', '<', $from)->sum('masuk');
                $mOut      = (float) Keuangan::where('id_kategori', $kategori->id)->withoutSaldoAwal()->whereDate('tanggal', '<', $from)->sum('keluar');
                $saldoAwal = (float) $kategori->saldo_awal + $mIn - $mOut;

                $kategori->setAttribute('saldo_awal', $saldoAwal);
                $kategori->setAttribute('masuk', $masuk);
                $kategori->setAttribute('keluar', $keluar);
                $kategori->setAttribute('saldo_akhir', $saldoAwal + $masuk - $keluar);

                return $kategori;
            })
            : collect();

        $kasTerkiniLabel = 'Kas Terkini ' . $awalBulan->translatedFormat('F Y');

        $jenisOptions = $showKegiatan ? JenisKegiatan::orderBy('nama')->get() : collect();

        return compact('data', 'hasAdmin', 'jadwal2Minggu', 'jadwalBulanan', 'visitorHariIni', 'visitorBulanIni', 'visitorTotal', 'kategoriKas', 'kasTerkiniLabel', 'jenisOptions', 'showJadwal', 'showKeuangan', 'showKegiatan', 'showPengunjung');
    }
}; ?>

<div>
@if(!$hasAdmin)
<div class="mb-6 bg-amber-50 border border-amber-200 rounded-2xl p-5">
    <div class="flex items-start gap-3">
        <svg class="w-6 h-6 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div>
            <p class="font-bold text-amber-800">Belum ada Administrator</p>
            <p class="text-sm text-amber-700 mt-1">Aplikasi ini belum memiliki administrator. Silakan <a href="{{ route('login') }}" class="font-bold underline">login</a> untuk menjadi administrator pertama.</p>
        </div>
    </div>
</div>
@endif

{{-- Jadwal Pengajian --}}
@if($showJadwal)
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">

    {{-- Pengajian 2 Mingguan --}}
    <div class="card p-0 overflow-hidden">
        <div class="px-5 py-3.5 bg-primary-800 flex items-center gap-2.5">
            <svg class="w-5 h-5 text-primary-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <h3 class="font-bold text-white text-sm">Pengajian 2 Mingguan</h3>
        </div>
        @if($jadwal2Minggu)
        @php
            $last2 = $jadwal2Minggu->tanggal_terakhir;
            $next2 = $jadwal2Minggu->tanggal_berikutnya;
        @endphp
        <div class="p-5">
            <p class="font-bold text-gray-800 text-sm">{{ $jadwal2Minggu->nama }}</p>
            @if($jadwal2Minggu->ustadz)
            <p class="text-xs text-gray-400 mt-0.5">{{ $jadwal2Minggu->ustadz }}</p>
            @endif
            <div class="flex items-center gap-2 mt-2.5 text-xs text-gray-500">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ $jadwal2Minggu->hari }}, {{ \Carbon\Carbon::parse($jadwal2Minggu->jam_mulai)->format('H:i') }}{{ $jadwal2Minggu->jam_selesai ? ' – '.\Carbon\Carbon::parse($jadwal2Minggu->jam_selesai)->format('H:i') : '' }}
            </div>
            <div class="mt-3 space-y-2">
                @if($last2)
                <div class="bg-gray-50 rounded-xl px-4 py-2.5">
                    <p class="text-[11px] text-gray-400 font-semibold uppercase">Terakhir</p>
                    <p class="text-sm font-bold text-gray-600 mt-0.5">{{ $last2->translatedFormat('l, d F Y') }}</p>
                </div>
                @endif
                @if($next2)
                <div class="bg-blue-50 rounded-xl px-4 py-2.5">
                    <p class="text-[11px] text-blue-500 font-semibold uppercase">Berikutnya</p>
                    <p class="text-sm font-bold text-blue-800 mt-0.5">{{ $next2->translatedFormat('l, d F Y') }}</p>
                </div>
                @endif
            </div>
        </div>
        @else
        <div class="p-5 text-center"><p class="text-sm text-gray-400">Belum ada jadwal</p></div>
        @endif
    </div>

    {{-- Pengajian Bulanan --}}
    <div class="card p-0 overflow-hidden">
        <div class="px-5 py-3.5 bg-primary-800 flex items-center gap-2.5">
            <svg class="w-5 h-5 text-primary-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <h3 class="font-bold text-white text-sm">Pengajian Bulanan</h3>
        </div>
        @if($jadwalBulanan)
        @php
            $lastB = $jadwalBulanan->tanggal_terakhir;
            $nextB = $jadwalBulanan->tanggal_berikutnya;
        @endphp
        <div class="p-5">
            <p class="font-bold text-gray-800 text-sm">{{ $jadwalBulanan->nama }}</p>
            @if($jadwalBulanan->ustadz)
            <p class="text-xs text-gray-400 mt-0.5">{{ $jadwalBulanan->ustadz }}</p>
            @endif
            <div class="flex items-center gap-2 mt-2.5 text-xs text-gray-500">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ $jadwalBulanan->hari }}, {{ \Carbon\Carbon::parse($jadwalBulanan->jam_mulai)->format('H:i') }}{{ $jadwalBulanan->jam_selesai ? ' – '.\Carbon\Carbon::parse($jadwalBulanan->jam_selesai)->format('H:i') : '' }}
            </div>
            <p class="text-[11px] text-gray-400 mt-1">Minggu pertama setiap awal bulan</p>
            <div class="mt-3 space-y-2">
                @if($lastB)
                <div class="bg-gray-50 rounded-xl px-4 py-2.5">
                    <p class="text-[11px] text-gray-400 font-semibold uppercase">Terakhir</p>
                    <p class="text-sm font-bold text-gray-600 mt-0.5">{{ $lastB->translatedFormat('l, d F Y') }}</p>
                </div>
                @endif
                @if($nextB)
                <div class="bg-emerald-50 rounded-xl px-4 py-2.5">
                    <p class="text-[11px] text-emerald-500 font-semibold uppercase">Berikutnya</p>
                    <p class="text-sm font-bold text-emerald-800 mt-0.5">{{ $nextB->translatedFormat('l, d F Y') }}</p>
                </div>
                @endif
            </div>
        </div>
        @else
        <div class="p-5 text-center"><p class="text-sm text-gray-400">Belum ada jadwal</p></div>
        @endif
    </div>

</div>
@endif

{{-- Posisi Keuangan --}}
@if($showKeuangan && $kategoriKas->count() > 0)
<div class="card p-0 overflow-hidden mb-6">
    <div class="px-5 py-3.5 bg-primary-800 flex items-center gap-2.5">
        <svg class="w-5 h-5 text-primary-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h3 class="font-bold text-white text-sm">{{ $kasTerkiniLabel }}</h3>
    </div>
    {{-- Mobile: card layout --}}
    <div class="sm:hidden divide-y divide-gray-100">
        @foreach($kategoriKas as $kat)
        <div class="px-4 py-3 space-y-1.5">
            <p class="font-semibold text-gray-800 text-sm">{{ $kat->nama }}</p>
            <div class="space-y-1 text-xs">
                <div class="flex justify-between"><span class="text-gray-400">Saldo Awal</span><span class="text-gray-600">Rp {{ number_format($kat->saldo_awal, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-400">Masuk</span><span class="text-emerald-600 font-medium">Rp {{ number_format($kat->masuk, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-400">Keluar</span><span class="text-red-500 font-medium">Rp {{ number_format($kat->keluar, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-400">Saldo Akhir</span><span class="font-bold text-gray-800">Rp {{ number_format($kat->saldo_akhir, 0, ',', '.') }}</span></div>
            </div>
        </div>
        @endforeach
        <div class="px-4 py-3 bg-primary-50/60 space-y-1.5">
            <p class="font-bold text-primary-800 text-sm">Total</p>
            <div class="space-y-1 text-xs font-bold">
                <div class="flex justify-between"><span class="text-gray-500">Saldo Awal</span><span class="text-primary-700">Rp {{ number_format($kategoriKas->sum('saldo_awal'), 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Masuk</span><span class="text-emerald-700">Rp {{ number_format($kategoriKas->sum('masuk'), 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Keluar</span><span class="text-red-600">Rp {{ number_format($kategoriKas->sum('keluar'), 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Saldo Akhir</span><span class="text-primary-800">Rp {{ number_format($kategoriKas->sum('saldo_akhir'), 0, ',', '.') }}</span></div>
            </div>
        </div>
    </div>

    {{-- Desktop: table layout --}}
    <div class="hidden sm:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                    <th class="px-5 py-2.5 text-left font-semibold">Kategori</th>
                    <th class="px-4 py-2.5 text-right font-semibold">Saldo Awal</th>
                    <th class="px-4 py-2.5 text-right font-semibold">Masuk</th>
                    <th class="px-4 py-2.5 text-right font-semibold">Keluar</th>
                    <th class="px-5 py-2.5 text-right font-semibold">Saldo Akhir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($kategoriKas as $kat)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-5 py-3 font-semibold text-gray-800">{{ $kat->nama }}</td>
                    <td class="px-4 py-3 text-right text-gray-600">{{ number_format($kat->saldo_awal, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-emerald-600 font-medium">{{ number_format($kat->masuk, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-red-500 font-medium">{{ number_format($kat->keluar, 0, ',', '.') }}</td>
                    <td class="px-5 py-3 text-right font-bold text-gray-800">{{ number_format($kat->saldo_akhir, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-primary-50/60 font-bold">
                    <td class="px-5 py-3 text-primary-800">Total</td>
                    <td class="px-4 py-3 text-right text-primary-700">{{ number_format($kategoriKas->sum('saldo_awal'), 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-emerald-700">{{ number_format($kategoriKas->sum('masuk'), 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-red-600">{{ number_format($kategoriKas->sum('keluar'), 0, ',', '.') }}</td>
                    <td class="px-5 py-3 text-right text-primary-800">{{ number_format($kategoriKas->sum('saldo_akhir'), 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif

{{-- Kegiatan --}}
@if($showKegiatan)

{{-- Filter --}}
<div class="grid grid-cols-2 gap-3 mb-5">
    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kegiatan..."
           class="input col-span-1" />
    <select wire:model.live="jenis" class="input col-span-1">
        <option value="">Semua Jenis</option>
        @foreach($jenisOptions as $jo)
        <option value="{{ $jo->nama }}">{{ $jo->nama }}</option>
        @endforeach
    </select>
</div>

{{-- Grid cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($data as $item)
        @php
            $hl = $item->display_media;
            $imageCount = $item->fotos->where('media_type', 'image')->count();
            $videoCount = $item->fotos->where('media_type', 'video')->count();
        @endphp
    <a href="{{ route('kegiatan.detail', $item->id) }}" class="card p-0 overflow-hidden hover:shadow-md transition-shadow group block">
        @if($hl)
        <div class="h-48 bg-gray-100 overflow-hidden relative">
                @if(($hl->media_type ?? 'image') === 'video')
                <video src="{{ asset('storage/' . $hl->path) }}" class="w-full h-full object-cover" muted playsinline preload="metadata"></video>
                <span class="absolute top-2 left-2 bg-black/60 text-white text-[11px] font-bold px-2 py-0.5 rounded-lg">Video</span>
                <span class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <span class="w-11 h-11 rounded-full bg-black/45 backdrop-blur-[1px] flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M6.5 5.5a1 1 0 011.53-.848l7 4.5a1 1 0 010 1.696l-7 4.5A1 1 0 016.5 13.5v-8z" />
                        </svg>
                    </span>
                </span>
                @else
            <img src="{{ asset('storage/' . $hl->path) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="{{ $item->judul }}">
                @endif
            @if($item->fotos->count() > 1)
                <span class="absolute top-2 right-2 bg-black/60 text-white text-xs font-bold px-2 py-0.5 rounded-lg">{{ $item->fotos->count() }} media</span>
            @endif
                <span class="absolute bottom-2 right-2 bg-black/60 text-white text-[11px] font-semibold px-2 py-0.5 rounded-lg">
                    {{ $imageCount }} foto{{ $videoCount ? ' • '.$videoCount.' video' : '' }}
                </span>
        </div>
        @else
        <div class="h-48 bg-gradient-to-br from-primary-700 to-emerald-500 flex items-center justify-center">
            <svg class="w-16 h-16 text-white/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        @endif

        <div class="p-4">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $item->jenis_badge_class }}">
                    {{ ucfirst($item->jenis) }}
                </span>
                <span class="text-xs text-gray-400">{{ $item->tanggal_kegiatan->isoFormat('D MMM Y') }}</span>
            </div>
            <h3 class="font-bold text-gray-800 text-sm mb-1 line-clamp-2 group-hover:text-primary-700 transition-colors">{{ $item->judul }}</h3>
            @if($item->konten)
            <p class="text-xs text-gray-500 line-clamp-2 mt-1">{{ Str::limit(strip_tags($item->konten), 120) }}</p>
            @endif
            @if($item->lokasi)
            <p class="text-xs text-gray-400 flex items-center gap-1 mt-2">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                {{ $item->lokasi }}
            </p>
            @endif
            @if($item->tanggal_kegiatan->isFuture())
            <div class="mt-2">
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">Akan datang</span>
            </div>
            @endif
        </div>
    </a>
    @empty
    <div class="col-span-3 card text-center py-14">
        <svg class="w-14 h-14 text-gray-200 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <p class="text-gray-400 text-sm">Belum ada kegiatan</p>
    </div>
    @endforelse
</div>

<div class="mt-4">{{ $data->links() }}</div>

@endif

{{-- Jama'ah Pengunjung --}}
@if($showPengunjung)
<div class="card p-0 overflow-hidden mt-6">
    <div class="px-5 py-3.5 bg-primary-800 flex items-center gap-2.5">
        <svg class="w-5 h-5 text-primary-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <h3 class="font-bold text-white text-sm">Pengunjung</h3>
    </div>
    <div class="p-5">
        <div class="grid grid-cols-3 gap-4">
            <div class="text-center">
                <p class="text-2xl sm:text-3xl font-extrabold text-primary-700">{{ number_format($visitorHariIni) }}</p>
                <p class="text-xs text-gray-400 mt-1">Hari Ini</p>
            </div>
            <div class="text-center border-x border-gray-100">
                <p class="text-2xl sm:text-3xl font-extrabold text-gray-700">{{ number_format($visitorBulanIni) }}</p>
                <p class="text-xs text-gray-400 mt-1">Bulan Ini</p>
            </div>
            <div class="text-center">
                <p class="text-2xl sm:text-3xl font-extrabold text-gray-700">{{ number_format($visitorTotal) }}</p>
                <p class="text-xs text-gray-400 mt-1">Total</p>
            </div>
        </div>
    </div>
</div>
@endif

</div>

