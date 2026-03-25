<?php

use App\Models\Kegiatan;
use App\Models\JenisKegiatan;
use Livewire\Volt\Component;

new class extends Component
{
    public int $kegiatanId;

    public function mount(int $id): void
    {
        $this->kegiatanId = $id;
    }

    public function render(): mixed
    {
        $kegiatan = Kegiatan::with(['creator', 'fotos'])->findOrFail($this->kegiatanId);
        $layout = auth()->check() ? 'components.layouts.app' : 'components.layouts.public';

        return parent::render()
            ->title($kegiatan->judul)
            ->layout($layout);
    }

    public function with(): array
    {
        $kegiatan = Kegiatan::with(['creator', 'fotos'])->findOrFail($this->kegiatanId);
        $lainnya  = Kegiatan::with('fotos')
            ->where('id', '!=', $kegiatan->id)
            ->orderBy('tanggal_kegiatan', 'desc')
            ->limit(3)
            ->get();

        return compact('kegiatan', 'lainnya');
    }
}; ?>

<div x-data="{
        items: @js($kegiatan->fotos->values()->map(fn($foto) => ['src' => asset('storage/'.$foto->path), 'type' => $foto->media_type ?? 'image'])->toArray()),
        isOpen: false,
        activeIndex: 0,
        resetVideos() {
            document.querySelectorAll('[data-gallery-modal] video').forEach(v => { v.pause(); v.currentTime = 0; });
        },
        open(i) { this.activeIndex = i; this.isOpen = true; document.body.classList.add('overflow-hidden'); },
        close() { this.isOpen = false; document.body.classList.remove('overflow-hidden'); this.resetVideos(); },
        go(i) { this.resetVideos(); this.activeIndex = i; },
        prev() { if (!this.items.length) return; this.resetVideos(); this.activeIndex = (this.activeIndex - 1 + this.items.length) % this.items.length; },
        next() { if (!this.items.length) return; this.resetVideos(); this.activeIndex = (this.activeIndex + 1) % this.items.length; },
        current() { return this.items[this.activeIndex] || null; }
    }"
    @keydown.escape.window="close()"
    @keydown.arrow-left.window="if (isOpen) prev()"
    @keydown.arrow-right.window="if (isOpen) next()">
{{-- Back button --}}
<div class="mb-5">
    @auth
    <a href="{{ route('kegiatan') }}" class="inline-flex items-center gap-1.5 text-sm text-primary-700 hover:text-primary-900 font-semibold">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Kegiatan
    </a>
    @else
    <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-sm text-primary-700 hover:text-primary-900 font-semibold">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Beranda
    </a>
    @endauth
</div>

<article class="card p-0 overflow-hidden">

    {{-- Content --}}
    <div class="p-5 sm:p-8">
        <div class="flex flex-wrap items-center gap-2 mb-4">
            <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $kegiatan->jenis_badge_class }}">
                {{ ucfirst($kegiatan->jenis) }}
            </span>
            <span class="text-xs text-gray-400">{{ $kegiatan->tanggal_kegiatan->isoFormat('dddd, D MMMM Y · HH:mm') }}</span>
            @if($kegiatan->tanggal_kegiatan->isFuture())
            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">Akan datang</span>
            @endif
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 mb-3 leading-tight">{{ $kegiatan->judul }}</h1>

        @if($kegiatan->lokasi)
        <p class="text-sm text-gray-500 mb-5 flex items-center gap-1.5">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            {{ $kegiatan->lokasi }}
        </p>
        @endif

        @if($kegiatan->konten)
        <div class="prose prose-sm sm:prose-base max-w-none text-gray-700">
            {!! $kegiatan->konten !!}
        </div>
        @else
        <p class="text-gray-400 italic">Belum ada konten artikel.</p>
        @endif

        @if($kegiatan->fotos->count() > 0)
        <div class="mt-6 pt-5 border-t border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Galeri Dokumentasi</h3>
                <span class="text-xs text-gray-400">Klik media untuk buka popup</span>
            </div>
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2">
                @foreach($kegiatan->fotos as $foto)
                @php $fotoType = $foto->media_type ?? 'image'; @endphp
                <button type="button"
                        @click="open({{ $loop->index }})"
                        class="relative rounded-xl overflow-hidden border-2 border-gray-200 hover:border-primary-300 aspect-square bg-gray-100 text-left">
                    @if($fotoType === 'video')
                    <div class="w-full h-full bg-gray-900/95 flex items-center justify-center">
                        <span class="w-9 h-9 rounded-full bg-black/50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M6.5 5.5a1 1 0 011.53-.848l7 4.5a1 1 0 010 1.696l-7 4.5A1 1 0 016.5 13.5v-8z" />
                            </svg>
                        </span>
                    </div>
                    <span class="absolute top-1 right-1 bg-black/70 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-md">Video</span>
                    @else
                    <img src="{{ asset('storage/'.$foto->path) }}" class="w-full h-full object-cover">
                    @endif
                </button>
                @endforeach
            </div>
        </div>
        @endif

        <div class="flex items-center gap-3 mt-8 pt-5 border-t border-gray-100">
            @if($kegiatan->creator?->avatar)
            <img src="{{ $kegiatan->creator->avatar }}" class="w-8 h-8 rounded-full object-cover">
            @else
            <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                <span class="text-xs font-bold text-primary-700">{{ substr($kegiatan->creator->name ?? '?', 0, 1) }}</span>
            </div>
            @endif
            <div>
                <p class="text-sm font-semibold text-gray-700">{{ $kegiatan->creator->name ?? '-' }}</p>
                <p class="text-xs text-gray-400">{{ $kegiatan->created_at->isoFormat('D MMMM Y') }}</p>
            </div>
        </div>
    </div>
</article>

{{-- Google Drive-style Gallery Viewer --}}
<div x-show="isOpen"
     x-cloak
     x-transition.opacity.duration.150ms
     data-gallery-modal
     class="fixed inset-0 flex flex-col select-none"
     style="z-index: 99999; background: #000;">

    {{-- Top toolbar --}}
    <div class="flex items-center justify-between px-3 sm:px-5 h-14 flex-shrink-0 border-b border-white/[0.06]">
        <div class="flex items-center gap-2.5">
            <button @click="close()"
                    class="text-white/70 hover:text-white p-2 -ml-2 rounded-full hover:bg-white/10 transition-colors"
                    title="Kembali">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </button>
            <div class="leading-tight">
                <p class="text-[13px] text-white/90 font-medium" x-text="current()?.type === 'video' ? 'Video' : 'Foto'"></p>
                <p class="text-[11px] text-white/40" x-text="(activeIndex + 1) + ' dari ' + items.length"></p>
            </div>
        </div>
        <button @click="close()"
                class="text-white/60 hover:text-white p-2 rounded-full hover:bg-white/10 transition-colors"
                title="Tutup">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Media viewport --}}
    <div class="flex-1 relative flex items-center justify-center min-h-0 overflow-hidden">

        {{-- Prev --}}
        <div x-show="items.length > 1"
             @click="prev()"
             class="absolute left-0 top-0 bottom-0 w-16 sm:w-24 z-10 flex items-center justify-start pl-2 sm:pl-4 cursor-pointer group">
            <span class="w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center rounded-full bg-black/40 group-hover:bg-white/15 text-white/60 group-hover:text-white transition-all duration-200 backdrop-blur-sm">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </span>
        </div>

        {{-- Media container --}}
        <div class="w-full h-full flex items-center justify-center px-14 sm:px-28 py-3">
            <template x-if="current() && current().type === 'image'">
                <img :src="current().src"
                     class="max-w-full max-h-full object-contain rounded-sm shadow-2xl"
                     alt="Dokumentasi kegiatan"
                     draggable="false">
            </template>
            <template x-if="current() && current().type === 'video'">
                <video :src="current().src"
                       class="max-w-full max-h-full object-contain rounded-sm shadow-2xl"
                       controls autoplay playsinline></video>
            </template>
        </div>

        {{-- Next --}}
        <div x-show="items.length > 1"
             @click="next()"
             class="absolute right-0 top-0 bottom-0 w-16 sm:w-24 z-10 flex items-center justify-end pr-2 sm:pr-4 cursor-pointer group">
            <span class="w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center rounded-full bg-black/40 group-hover:bg-white/15 text-white/60 group-hover:text-white transition-all duration-200 backdrop-blur-sm">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        </div>
    </div>

    {{-- Bottom filmstrip --}}
    <div x-show="items.length > 1"
         class="flex-shrink-0 border-t border-white/[0.06] bg-black">
        <div class="flex justify-center gap-1.5 sm:gap-2 overflow-x-auto px-4 py-3 scrollbar-hide">
            <template x-for="(item, idx) in items" :key="idx">
                <button @click="go(idx)"
                        class="relative w-11 h-11 sm:w-[52px] sm:h-[52px] rounded-md overflow-hidden flex-shrink-0 transition-all duration-200 border"
                        :class="idx === activeIndex
                            ? 'border-white opacity-100 shadow-[0_0_0_1px_rgba(255,255,255,0.9)]'
                            : 'border-transparent opacity-35 hover:opacity-60'">
                    <template x-if="item.type === 'image'">
                        <img :src="item.src" class="w-full h-full object-cover" draggable="false">
                    </template>
                    <template x-if="item.type === 'video'">
                        <div class="w-full h-full bg-neutral-900 flex items-center justify-center">
                            <svg class="w-4 h-4 text-white/80" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M6.5 5.5a1 1 0 011.53-.848l7 4.5a1 1 0 010 1.696l-7 4.5A1 1 0 016.5 13.5v-8z"/>
                            </svg>
                        </div>
                    </template>
                </button>
            </template>
        </div>
    </div>
</div>

{{-- Related kegiatan --}}
@if($lainnya->count() > 0)
<div class="mt-8">
    <h2 class="text-lg font-bold text-gray-800 mb-4">Kegiatan Lainnya</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @foreach($lainnya as $item)
        @php $hl = $item->display_media; @endphp
        <a href="{{ route('kegiatan.detail', $item->id) }}" class="card p-0 overflow-hidden hover:shadow-md transition-shadow group">
            @if($hl)
            <div class="h-36 bg-gray-100 overflow-hidden relative">
                @if(($hl->media_type ?? 'image') === 'video')
                <video src="{{ asset('storage/' . $hl->path) }}" class="w-full h-full object-cover" muted playsinline preload="metadata"></video>
                <span class="absolute top-2 left-2 bg-black/60 text-white text-[11px] font-bold px-2 py-0.5 rounded-lg">Video</span>
                @else
                <img src="{{ asset('storage/' . $hl->path) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="{{ $item->judul }}">
                @endif
            </div>
            @else
            <div class="h-36 bg-gradient-to-br from-primary-700 to-emerald-500 flex items-center justify-center">
                <svg class="w-10 h-10 text-white/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            @endif
            <div class="p-3">
                <span class="text-xs text-gray-400">{{ $item->tanggal_kegiatan->isoFormat('D MMM Y') }}</span>
                <h3 class="font-bold text-gray-800 text-sm mt-1 line-clamp-2 group-hover:text-primary-700 transition-colors">{{ $item->judul }}</h3>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif
</div>
