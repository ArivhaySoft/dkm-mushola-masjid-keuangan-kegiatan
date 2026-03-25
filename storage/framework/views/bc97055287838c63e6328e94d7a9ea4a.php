<?php

use App\Models\Kegiatan;
use App\Models\KegiatanFoto;
use App\Models\JenisKegiatan;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

?>

<div>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
<div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl px-4 py-3"
     x-data x-init="setTimeout(() => $el.remove(), 4000)">
    ✓ <?php echo e(session('success')); ?>

</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<div class="flex flex-col sm:flex-row gap-3 mb-5">
    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kegiatan..."
           class="input flex-1" />
    <a href="<?php echo e(route('export.kegiatan')); ?>" class="btn-secondary whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export Excel
    </a>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()?->isEditor()): ?>
    <button wire:click="openCreate" class="btn-primary whitespace-nowrap">+ Tambah Kegiatan</button>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>


<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $hl = $item->display_media;
            $imageCount = $item->fotos->where('media_type', 'image')->count();
            $videoCount = $item->fotos->where('media_type', 'video')->count();
        ?>
    <a href="<?php echo e(route('kegiatan.detail', $item->id)); ?>" class="card p-0 overflow-hidden hover:shadow-md transition-shadow cursor-pointer block">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hl): ?>
        <div class="h-44 bg-gray-100 overflow-hidden relative">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($hl->media_type ?? 'image') === 'video'): ?>
                <video src="<?php echo e(asset('storage/' . $hl->path)); ?>" class="w-full h-full object-cover" muted playsinline preload="metadata"></video>
                <span class="absolute top-2 left-2 bg-black/60 text-white text-[11px] font-bold px-2 py-0.5 rounded-lg">Video</span>
                <span class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <span class="w-11 h-11 rounded-full bg-black/45 backdrop-blur-[1px] flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M6.5 5.5a1 1 0 011.53-.848l7 4.5a1 1 0 010 1.696l-7 4.5A1 1 0 016.5 13.5v-8z" />
                        </svg>
                    </span>
                </span>
                <?php else: ?>
            <img src="<?php echo e(asset('storage/' . $hl->path)); ?>" class="w-full h-full object-cover" alt="<?php echo e($item->judul); ?>">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->fotos->count() > 1): ?>
            <span class="absolute top-2 right-2 bg-black/60 text-white text-xs font-bold px-2 py-0.5 rounded-lg">
                    <?php echo e($item->fotos->count()); ?> media
            </span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <span class="absolute bottom-2 right-2 bg-black/60 text-white text-[11px] font-semibold px-2 py-0.5 rounded-lg">
                    <?php echo e($imageCount); ?> foto<?php echo e($videoCount ? ' • '.$videoCount.' video' : ''); ?>

                </span>
        </div>
        <?php else: ?>
        <div class="h-44 bg-gradient-to-br from-primary-700 to-emerald-500 flex items-center justify-center">
            <svg class="w-16 h-16 text-white/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="p-4">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-xs font-bold px-2.5 py-1 rounded-full <?php echo e($item->jenis_badge_class); ?>">
                    <?php echo e(ucfirst($item->jenis)); ?>

                </span>
                <span class="text-xs text-gray-400"><?php echo e($item->tanggal_kegiatan->isoFormat('D MMM Y')); ?></span>
            </div>
            <h3 class="font-bold text-gray-800 text-sm mb-1 line-clamp-2"><?php echo e($item->judul); ?></h3>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->lokasi): ?>
            <p class="text-xs text-gray-400 flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <?php echo e($item->lokasi); ?>

            </p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()?->isEditor()): ?>
            <div class="flex gap-2 mt-3 pt-3 border-t border-gray-100" @click.prevent.stop>
                <button wire:click.prevent="openEdit(<?php echo e($item->id); ?>)"
                        class="text-xs text-blue-600 hover:text-blue-800 font-semibold">Edit</button>
                <button wire:click.prevent="confirmDeleteItem(<?php echo e($item->id); ?>)"
                        class="text-xs text-red-500 hover:text-red-700 font-semibold">Hapus</button>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="col-span-3 card text-center py-14">
        <svg class="w-14 h-14 text-gray-200 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <p class="text-gray-400 text-sm">Belum ada kegiatan</p>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<div class="mt-4"><?php echo e($data->links()); ?></div>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal): ?>
<div class="fixed inset-0 z-50 flex items-stretch justify-center p-0 bg-black/50">
    <div class="bg-white w-full h-full shadow-2xl flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b flex-shrink-0">
            <h3 class="text-base font-bold text-gray-800"><?php echo e($editId ? 'Edit Kegiatan' : 'Tambah Kegiatan'); ?></h3>
            <button wire:click="$set('showModal', false)" class="p-1.5 hover:bg-gray-100 rounded-lg">
                <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1">
            <div>
                <label class="label">Judul</label>
                <input type="text" wire:model="judul" class="input" placeholder="Judul kegiatan..." />
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['judul'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label">Jenis</label>
                    <select wire:model="jenis" class="input">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $jenisOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($jo->nama); ?>"><?php echo e($jo->nama); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
                <div>
                    <label class="label">Tanggal & Waktu</label>
                    <input type="datetime-local" wire:model="tanggal_kegiatan" class="input" />
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['tanggal_kegiatan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <div>
                <label class="label">Lokasi</label>
                <input type="text" wire:model="lokasi" class="input" placeholder="Lokasi kegiatan..." />
            </div>

            
            <div>
                <label class="label">Galeri Dokumentasi</label>
                <p class="text-xs text-gray-400 mb-2">Upload foto dan video, lalu pilih 1 media sebagai headline (utama).</p>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($existing_fotos) > 0): ?>
                <div class="grid grid-cols-3 gap-2 mb-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $existing_fotos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ef): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="relative group">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($ef['media_type'] ?? 'image') === 'video'): ?>
                        <video src="<?php echo e(asset('storage/' . $ef['path'])); ?>" class="w-full h-20 object-cover rounded-xl border-2 <?php echo e($headline_mode === 'existing' && $headline_existing_id === $ef['id'] ? 'border-primary-500 ring-2 ring-primary-300' : 'border-gray-200'); ?>" muted playsinline preload="metadata"></video>
                        <span class="absolute top-1 right-1 bg-black/70 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-md">Video</span>
                        <?php else: ?>
                        <img src="<?php echo e(asset('storage/' . $ef['path'])); ?>" class="w-full h-20 object-cover rounded-xl border-2 <?php echo e($headline_mode === 'existing' && $headline_existing_id === $ef['id'] ? 'border-primary-500 ring-2 ring-primary-300' : 'border-gray-200'); ?>">
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($headline_mode === 'existing' && $headline_existing_id === $ef['id']): ?>
                        <span class="absolute top-1 left-1 bg-primary-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-md">Headline</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="absolute bottom-1 right-1 flex gap-1">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!($headline_mode === 'existing' && $headline_existing_id === $ef['id'])): ?>
                            <button type="button" wire:click="setHeadlineExisting(<?php echo e($ef['id']); ?>)"
                                    class="bg-white/90 text-primary-600 text-[10px] font-bold px-1.5 py-0.5 rounded-md shadow hover:bg-primary-50" title="Jadikan Headline">
                                ★
                            </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <button type="button" wire:click="markDeleteFoto(<?php echo e($ef['id']); ?>)"
                                    class="bg-white/90 text-red-500 text-[10px] font-bold px-1.5 py-0.5 rounded-md shadow hover:bg-red-50" title="Hapus">
                                ✕
                            </button>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <input type="file" wire:model="new_fotos" class="input" accept="image/*,video/*" multiple />
                <p class="text-xs text-gray-400 mt-1">Maks 10 file, format: JPG/PNG/WEBP/GIF/MP4/MOV/M4V/WEBM/AVI/MKV, masing-masing maks 20MB.</p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['new_fotos.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($new_fotos) > 0): ?>
                <div class="grid grid-cols-3 gap-2 mt-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $new_fotos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $nf): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $newMime = (string) $nf->getMimeType();
                        $isVideo = str_starts_with($newMime, 'video/');
                    ?>
                    <div class="relative cursor-pointer" wire:click="setHeadlineNew(<?php echo e($i); ?>)">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isVideo): ?>
                        <div class="w-full h-20 rounded-xl border-2 bg-gray-900/90 <?php echo e($headline_mode === 'new' && $headline_index === $i ? 'border-primary-500 ring-2 ring-primary-300' : 'border-gray-200'); ?> flex items-center justify-center">
                            <svg class="w-7 h-7 text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.868v4.264a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="absolute top-1 right-1 bg-black/70 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-md">Video</span>
                        <?php else: ?>
                        <img src="<?php echo e($nf->temporaryUrl()); ?>" class="w-full h-20 object-cover rounded-xl border-2 <?php echo e($headline_mode === 'new' && $headline_index === $i ? 'border-primary-500 ring-2 ring-primary-300' : 'border-gray-200'); ?>">
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($headline_mode === 'new' && $headline_index === $i): ?>
                        <span class="absolute top-1 left-1 bg-primary-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-md">Headline</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <span class="absolute bottom-1 left-1 bg-black/50 text-white text-[10px] px-1.5 py-0.5 rounded-md">Baru</span>
                        <span class="absolute bottom-1 right-1 bg-black/50 text-white text-[10px] px-1.5 py-0.5 rounded-md"><?php echo e($isVideo ? 'Video' : 'Foto'); ?></span>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <p class="text-xs text-gray-400 mt-1">Klik media untuk menjadikan headline.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div x-data="{
                exec(cmd, val = null) { document.execCommand(cmd, false, val); this.syncContent(); $refs.editor.focus(); },
                syncContent() { $wire.set('konten', $refs.editor.innerHTML); },
                setContent(payload) {
                    const value = Array.isArray(payload) ? (payload[0] ?? '') : (payload ?? '');
                    $refs.editor.innerHTML = value;
                    this.syncContent();
                },
                insertLink() {
                    const url = prompt('Masukkan URL:', 'https://');
                    if (url) this.exec('createLink', url);
                },
                insertTable() {
                    const s = 'border:1px solid #d1d5db;padding:8px';
                    const th = '<th style=\'' + s + '\'>';
                    const td = '<td style=\'' + s + '\'>';
                    const html = '<table><thead><tr>' + th + 'Header 1</th>' + th + 'Header 2</th>' + th + 'Header 3</th></tr></thead><tbody><tr>' + td + '&nbsp;</td>' + td + '&nbsp;</td>' + td + '&nbsp;</td></tr><tr>' + td + '&nbsp;</td>' + td + '&nbsp;</td>' + td + '&nbsp;</td></tr></tbody></table><p></p>';
                    document.execCommand('insertHTML', false, html);
                    this.syncContent();
                    $refs.editor.focus();
                },
                setFontSize(size) {
                    document.execCommand('fontSize', false, size);
                    this.syncContent();
                    $refs.editor.focus();
                },
                setColor(color) {
                    document.execCommand('foreColor', false, color);
                    this.syncContent();
                    $refs.editor.focus();
                },
                setBgColor(color) {
                    document.execCommand('hiliteColor', false, color);
                    this.syncContent();
                    $refs.editor.focus();
                },
                showColorPicker: false,
                showBgColorPicker: false,
                showHeadingMenu: false,
                colors: ['#000000','#434343','#666666','#999999','#dc2626','#ea580c','#d97706','#65a30d','#16a34a','#0891b2','#2563eb','#7c3aed','#c026d3','#e11d48'],
            }">
                <label class="label">Konten / Artikel</label>
                <div class="border border-gray-200 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-primary-400 focus-within:border-primary-400" wire:ignore>
                    
                    <div class="flex flex-wrap gap-0.5 px-2 py-1.5 border-b border-gray-100 bg-gray-50/80">
                        
                        <button type="button" @click="exec('undo')" class="px-1.5 py-1 rounded text-gray-500 hover:bg-gray-200" title="Undo (Ctrl+Z)">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2M3 10l4-4m-4 4l4 4"/></svg>
                        </button>
                        <button type="button" @click="exec('redo')" class="px-1.5 py-1 rounded text-gray-500 hover:bg-gray-200" title="Redo (Ctrl+Y)">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a5 5 0 00-5 5v2m15-7l-4-4m4 4l-4 4"/></svg>
                        </button>
                        <span class="w-px bg-gray-200 mx-0.5"></span>

                        
                        <div class="relative" @click.outside="showHeadingMenu = false">
                            <button type="button" @click="showHeadingMenu = !showHeadingMenu" class="px-2 py-1 rounded text-xs font-bold text-gray-600 hover:bg-gray-200 flex items-center gap-0.5" title="Heading">
                                H
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="showHeadingMenu" x-cloak class="absolute left-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-50 py-1 w-36">
                                <button type="button" @click="exec('formatBlock', 'p'); showHeadingMenu = false" class="w-full text-left px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-100">Paragraf</button>
                                <button type="button" @click="exec('formatBlock', 'h1'); showHeadingMenu = false" class="w-full text-left px-3 py-1.5 text-xl font-bold text-gray-700 hover:bg-gray-100">Heading 1</button>
                                <button type="button" @click="exec('formatBlock', 'h2'); showHeadingMenu = false" class="w-full text-left px-3 py-1.5 text-lg font-bold text-gray-700 hover:bg-gray-100">Heading 2</button>
                                <button type="button" @click="exec('formatBlock', 'h3'); showHeadingMenu = false" class="w-full text-left px-3 py-1.5 text-base font-bold text-gray-700 hover:bg-gray-100">Heading 3</button>
                                <button type="button" @click="exec('formatBlock', 'h4'); showHeadingMenu = false" class="w-full text-left px-3 py-1.5 text-sm font-bold text-gray-700 hover:bg-gray-100">Heading 4</button>
                                <button type="button" @click="exec('formatBlock', 'pre'); showHeadingMenu = false" class="w-full text-left px-3 py-1.5 text-xs font-mono text-gray-700 hover:bg-gray-100">Code Block</button>
                            </div>
                        </div>

                        
                        <select @change="setFontSize($event.target.value); $event.target.value = ''" class="text-xs text-gray-600 bg-transparent border-0 px-1 py-1 hover:bg-gray-200 rounded cursor-pointer focus:ring-0" title="Ukuran Font">
                            <option value="" disabled selected>Size</option>
                            <option value="1">Kecil</option>
                            <option value="3">Normal</option>
                            <option value="5">Besar</option>
                            <option value="7">Sangat Besar</option>
                        </select>
                        <span class="w-px bg-gray-200 mx-0.5"></span>

                        
                        <button type="button" @click="exec('bold')" class="px-2 py-1 rounded text-xs font-bold text-gray-600 hover:bg-gray-200" title="Bold (Ctrl+B)"><b>B</b></button>
                        <button type="button" @click="exec('italic')" class="px-2 py-1 rounded text-xs text-gray-600 hover:bg-gray-200" title="Italic (Ctrl+I)"><i class="font-serif">I</i></button>
                        <button type="button" @click="exec('underline')" class="px-2 py-1 rounded text-xs text-gray-600 hover:bg-gray-200" title="Underline (Ctrl+U)"><u>U</u></button>
                        <button type="button" @click="exec('strikeThrough')" class="px-2 py-1 rounded text-xs text-gray-600 hover:bg-gray-200" title="Strikethrough"><s>S</s></button>
                        <button type="button" @click="exec('subscript')" class="px-2 py-1 rounded text-xs text-gray-600 hover:bg-gray-200" title="Subscript">X<sub class="text-[9px]">2</sub></button>
                        <button type="button" @click="exec('superscript')" class="px-2 py-1 rounded text-xs text-gray-600 hover:bg-gray-200" title="Superscript">X<sup class="text-[9px]">2</sup></button>
                        <span class="w-px bg-gray-200 mx-0.5"></span>

                        
                        <div class="relative" @click.outside="showColorPicker = false">
                            <button type="button" @click="showColorPicker = !showColorPicker; showBgColorPicker = false" class="px-2 py-1 rounded text-xs font-bold text-gray-600 hover:bg-gray-200 flex items-center gap-0.5" title="Warna Teks">
                                A
                                <span class="w-3 h-0.5 bg-red-500 block"></span>
                            </button>
                            <div x-show="showColorPicker" x-cloak class="absolute left-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-50 p-2 grid grid-cols-7 gap-1 w-max">
                                <template x-for="c in colors" :key="c">
                                    <button type="button" @click="setColor(c); showColorPicker = false" class="w-6 h-6 rounded border border-gray-200 hover:scale-110 transition" :style="`background:${c}`"></button>
                                </template>
                            </div>
                        </div>

                        
                        <div class="relative" @click.outside="showBgColorPicker = false">
                            <button type="button" @click="showBgColorPicker = !showBgColorPicker; showColorPicker = false" class="px-1.5 py-1 rounded text-xs font-bold hover:bg-gray-200" title="Warna Latar" style="background: linear-gradient(135deg, #fef08a, #bef264, #67e8f9); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                                <span class="bg-yellow-200 px-1 rounded" style="-webkit-text-fill-color: #374151;">A</span>
                            </button>
                            <div x-show="showBgColorPicker" x-cloak class="absolute left-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-50 p-2 grid grid-cols-7 gap-1 w-max">
                                <button type="button" @click="exec('removeFormat'); showBgColorPicker = false" class="w-6 h-6 rounded border border-gray-200 hover:scale-110 transition bg-white flex items-center justify-center text-[10px] text-gray-400" title="Hapus">✕</button>
                                <template x-for="c in ['#fef08a','#bbf7d0','#bfdbfe','#e9d5ff','#fecdd3','#fed7aa','#e5e7eb']" :key="c">
                                    <button type="button" @click="setBgColor(c); showBgColorPicker = false" class="w-6 h-6 rounded border border-gray-200 hover:scale-110 transition" :style="`background:${c}`"></button>
                                </template>
                            </div>
                        </div>
                        <span class="w-px bg-gray-200 mx-0.5"></span>

                        
                        <button type="button" @click="exec('justifyLeft')" class="px-1.5 py-1 rounded text-gray-500 hover:bg-gray-200" title="Rata Kiri">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h18M3 12h12M3 18h18"/></svg>
                        </button>
                        <button type="button" @click="exec('justifyCenter')" class="px-1.5 py-1 rounded text-gray-500 hover:bg-gray-200" title="Rata Tengah">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h18M6 12h12M3 18h18"/></svg>
                        </button>
                        <button type="button" @click="exec('justifyRight')" class="px-1.5 py-1 rounded text-gray-500 hover:bg-gray-200" title="Rata Kanan">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h18M9 12h12M3 18h18"/></svg>
                        </button>
                        <button type="button" @click="exec('justifyFull')" class="px-1.5 py-1 rounded text-gray-500 hover:bg-gray-200" title="Rata Kiri-Kanan">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h18M3 12h18M3 18h18"/></svg>
                        </button>
                        <span class="w-px bg-gray-200 mx-0.5"></span>

                        
                        <button type="button" @click="exec('insertUnorderedList')" class="px-1.5 py-1 rounded text-gray-500 hover:bg-gray-200" title="Bullet List">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="4" cy="6" r="1.5" fill="currentColor" stroke="none"/><circle cx="4" cy="12" r="1.5" fill="currentColor" stroke="none"/><circle cx="4" cy="18" r="1.5" fill="currentColor" stroke="none"/><path stroke-linecap="round" stroke-width="2" d="M9 6h12M9 12h12M9 18h12"/></svg>
                        </button>
                        <button type="button" @click="exec('insertOrderedList')" class="px-1.5 py-1 rounded text-gray-500 hover:bg-gray-200" title="Numbered List">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><text x="2" y="8" font-size="7" fill="currentColor" stroke="none" font-weight="bold">1.</text><text x="2" y="14.5" font-size="7" fill="currentColor" stroke="none" font-weight="bold">2.</text><text x="2" y="21" font-size="7" fill="currentColor" stroke="none" font-weight="bold">3.</text><path stroke-linecap="round" stroke-width="2" d="M11 6h10M11 12h10M11 18h10"/></svg>
                        </button>
                        <button type="button" @click="exec('indent')" class="px-1.5 py-1 rounded text-gray-500 hover:bg-gray-200" title="Indent">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h18M9 12h12M3 18h18M3 9l3 3-3 3"/></svg>
                        </button>
                        <button type="button" @click="exec('outdent')" class="px-1.5 py-1 rounded text-gray-500 hover:bg-gray-200" title="Outdent">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h18M9 12h12M3 18h18M6 9l-3 3 3 3"/></svg>
                        </button>
                        <span class="w-px bg-gray-200 mx-0.5"></span>

                        
                        <button type="button" @click="exec('formatBlock', 'blockquote')" class="px-1.5 py-1 rounded text-gray-500 hover:bg-gray-200" title="Kutipan">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M4.583 17.321C3.553 16.227 3 15 3 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311C9.591 11.69 11 13.195 11 15c0 1.105-.448 2.105-1.172 2.829S8.105 19 7 19c-1.015 0-1.869-.394-2.417-1.679zM14.583 17.321C13.553 16.227 13 15 13 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311C19.591 11.69 21 13.195 21 15c0 1.105-.448 2.105-1.172 2.829S18.105 19 17 19c-1.015 0-1.869-.394-2.417-1.679z"/></svg>
                        </button>
                        <button type="button" @click="insertLink()" class="px-1.5 py-1 rounded text-gray-500 hover:bg-gray-200" title="Sisipkan Link">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        </button>
                        <button type="button" @click="exec('unlink')" class="px-1.5 py-1 rounded text-gray-500 hover:bg-gray-200" title="Hapus Link">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/><path stroke-linecap="round" stroke-width="2.5" d="M4 20L20 4"/></svg>
                        </button>
                        <button type="button" @click="exec('insertHorizontalRule')" class="px-1.5 py-1 rounded text-gray-500 hover:bg-gray-200" title="Garis Horizontal">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M3 12h18"/></svg>
                        </button>
                        <button type="button" @click="insertTable()" class="px-1.5 py-1 rounded text-gray-500 hover:bg-gray-200" title="Sisipkan Tabel">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M10 3v18M14 3v18M3 6a3 3 0 013-3h12a3 3 0 013 3v12a3 3 0 01-3 3H6a3 3 0 01-3-3V6z"/></svg>
                        </button>
                        <span class="w-px bg-gray-200 mx-0.5"></span>
                        <button type="button" @click="exec('removeFormat')" class="px-1.5 py-1 rounded text-gray-400 hover:bg-gray-200" title="Hapus Format">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div x-ref="editor" contenteditable="true"
                         @input="syncContent()" @blur="syncContent()"
                        x-init="setContent($wire.konten || '')"
                        @set-editor-content.window="setContent($event.detail)"
                        class="p-4 min-h-[320px] sm:min-h-[420px] max-h-[55vh] overflow-y-auto text-sm text-gray-700 focus:outline-none prose prose-sm max-w-none"
                         style="word-break: break-word;"
                         data-placeholder="Tuliskan artikel, ringkasan ceramah, atau dokumentasi kegiatan..."></div>
                </div>
                <style>
                    [contenteditable=true]:empty:before{content:attr(data-placeholder);color:#9ca3af;pointer-events:none;}
                    [contenteditable=true] table{border-collapse:collapse;width:100%;}
                    [contenteditable=true] th,[contenteditable=true] td{border:1px solid #d1d5db;padding:6px 10px;min-width:60px;}
                    [contenteditable=true] th{background:#f3f4f6;font-weight:600;}
                    [contenteditable=true] blockquote{border-left:3px solid #6366f1;padding-left:1em;color:#4b5563;font-style:italic;}
                    [contenteditable=true] pre{background:#1e293b;color:#e2e8f0;padding:12px 16px;border-radius:8px;font-size:13px;overflow-x:auto;}
                    [contenteditable=true] hr{border:none;border-top:2px solid #e5e7eb;margin:16px 0;}
                </style>
                <p class="text-xs text-gray-400 mt-1">Gunakan toolbar di atas untuk format teks. Shortcut: Ctrl+B (bold), Ctrl+I (italic), Ctrl+U (underline), Ctrl+Z (undo).</p>
            </div>
        </div>
        <div class="px-5 pb-5 flex gap-2 flex-shrink-0">
            <button wire:click="$set('showModal', false)" class="btn-secondary flex-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Batal
            </button>
            <button wire:click="save" class="btn-primary flex-1">
                <svg class="w-4 h-4" wire:loading.remove wire:target="save" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span wire:loading.remove wire:target="save">Simpan</span>
                <span wire:loading wire:target="save">Menyimpan...</span>
            </button>
        </div>
    </div>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($confirmDelete): ?>
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-2xl w-full max-w-sm p-6 shadow-2xl text-center">
        <h3 class="text-base font-bold text-gray-800 mb-2">Hapus Kegiatan?</h3>
        <p class="text-sm text-gray-500 mb-5">Data dan semua media tidak dapat dikembalikan.</p>
        <div class="flex gap-3">
            <button wire:click="$set('confirmDelete', false)" class="btn-secondary flex-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Batal
            </button>
            <button wire:click="deleteItem" class="btn-danger flex-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Ya, Hapus
            </button>
        </div>
    </div>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div><?php /**PATH /srv/apkpulsa/web/mushola-finance/resources/views/livewire/kegiatan/index.blade.php ENDPATH**/ ?>