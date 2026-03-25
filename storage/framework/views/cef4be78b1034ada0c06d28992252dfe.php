<?php

use App\Models\Kategori;
use App\Models\Kegiatan;
use App\Models\KegiatanFoto;
use App\Models\Keuangan;
use App\Models\Rekening;
use App\Models\TransferRekening;
use App\Models\User;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

?>

<div>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
<div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl px-4 py-3"
     x-data x-init="setTimeout(() => $el.remove(), 4000)">
    ✓ <?php echo e(session('success')); ?>

</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<div class="max-w-2xl mx-auto space-y-5">

    
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <p class="font-bold text-amber-800">Perhatian!</p>
                <p class="text-sm text-amber-700 mt-1">Reset data bersifat <strong>permanen</strong> dan tidak dapat dikembalikan. Pastikan Anda sudah mengunduh backup data sebelum melakukan reset.</p>
            </div>
        </div>
    </div>

    
    <div class="card">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-base font-bold text-gray-800">Reset Arus Kas</h3>
                <p class="text-sm text-gray-500 mt-1">Menghapus semua <strong>transaksi keuangan</strong> dan <strong>transfer rekening</strong>. Saldo awal kategori tetap.</p>
                <div class="flex items-center gap-4 mt-2 text-sm">
                    <span class="text-gray-500">Transaksi: <strong class="text-gray-700"><?php echo e(number_format($totalArusKas)); ?></strong></span>
                    <span class="text-gray-500">Transfer: <strong class="text-gray-700"><?php echo e(number_format($totalTransfer)); ?></strong></span>
                </div>
                <button wire:click="openReset('arus-kas')" class="btn-danger mt-3 text-sm" <?php if($totalArusKas === 0 && $totalTransfer === 0): ?> disabled <?php endif; ?>>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Reset Arus Kas
                </button>
            </div>
        </div>
    </div>

    
    <div class="card">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-base font-bold text-gray-800">Reset Kegiatan</h3>
                <p class="text-sm text-gray-500 mt-1">Menghapus semua <strong>data kegiatan</strong> beserta foto terupload.</p>
                <div class="flex items-center gap-4 mt-2 text-sm">
                    <span class="text-gray-500">Total: <strong class="text-gray-700"><?php echo e(number_format($totalKegiatan)); ?></strong></span>
                </div>
                <button wire:click="openReset('kegiatan')" class="btn-danger mt-3 text-sm" <?php if($totalKegiatan === 0): ?> disabled <?php endif; ?>>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Reset Kegiatan
                </button>
            </div>
        </div>
    </div>

    
    <div class="card">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-base font-bold text-gray-800">Reset Rekening</h3>
                <p class="text-sm text-gray-500 mt-1">Menghapus semua <strong>data rekening</strong>. Arus kas & transfer juga akan ikut terhapus.</p>
                <div class="flex items-center gap-4 mt-2 text-sm">
                    <span class="text-gray-500">Total: <strong class="text-gray-700"><?php echo e(number_format($totalRekening)); ?></strong></span>
                </div>
                <button wire:click="openReset('rekening')" class="btn-danger mt-3 text-sm" <?php if($totalRekening === 0): ?> disabled <?php endif; ?>>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Reset Rekening
                </button>
            </div>
        </div>
    </div>

    
    <div class="card">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-base font-bold text-gray-800">Reset Kategori</h3>
                <p class="text-sm text-gray-500 mt-1">Menghapus semua <strong>data kategori</strong>. Arus kas & transfer juga akan ikut terhapus.</p>
                <div class="flex items-center gap-4 mt-2 text-sm">
                    <span class="text-gray-500">Total: <strong class="text-gray-700"><?php echo e(number_format($totalKategori)); ?></strong></span>
                </div>
                <button wire:click="openReset('kategori')" class="btn-danger mt-3 text-sm" <?php if($totalKategori === 0): ?> disabled <?php endif; ?>>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Reset Kategori
                </button>
            </div>
        </div>
    </div>

    
    <div class="bg-red-50 border-2 border-red-300 rounded-2xl p-5">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 bg-red-200 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-base font-bold text-red-800">Reset Semua Data</h3>
                <p class="text-sm text-red-600 mt-1">Menghapus <strong>SELURUH data</strong>: arus kas, transfer, kegiatan, rekening, kategori, dan <strong>semua user</strong>. Anda akan logout dan harus login ulang sebagai Administrator baru.</p>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-sm">
                    <span class="text-red-500">User: <strong><?php echo e(number_format($totalUser)); ?></strong></span>
                    <span class="text-red-500">Transaksi: <strong><?php echo e(number_format($totalArusKas)); ?></strong></span>
                    <span class="text-red-500">Rekening: <strong><?php echo e(number_format($totalRekening)); ?></strong></span>
                    <span class="text-red-500">Kategori: <strong><?php echo e(number_format($totalKategori)); ?></strong></span>
                    <span class="text-red-500">Kegiatan: <strong><?php echo e(number_format($totalKegiatan)); ?></strong></span>
                </div>
                <button wire:click="openReset('all')" class="btn-danger mt-3 text-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Reset Semua Data
                </button>
            </div>
        </div>
    </div>
</div>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeReset): ?>
<?php
    $resetLabels = [
        'arus-kas' => ['title' => 'Reset Arus Kas', 'text' => 'RESET ARUS KAS', 'desc' => 'Menghapus ' . number_format($totalArusKas) . ' transaksi dan ' . number_format($totalTransfer) . ' transfer.'],
        'kegiatan' => ['title' => 'Reset Kegiatan', 'text' => 'RESET KEGIATAN', 'desc' => 'Menghapus ' . number_format($totalKegiatan) . ' kegiatan beserta fotonya.'],
        'rekening' => ['title' => 'Reset Rekening', 'text' => 'RESET REKENING', 'desc' => 'Menghapus ' . number_format($totalRekening) . ' rekening. Arus kas & transfer juga ikut terhapus.'],
        'kategori' => ['title' => 'Reset Kategori', 'text' => 'RESET KATEGORI', 'desc' => 'Menghapus ' . number_format($totalKategori) . ' kategori. Arus kas & transfer juga ikut terhapus.'],
        'all'      => ['title' => 'Reset Semua Data', 'text' => 'RESET SEMUA DATA', 'desc' => 'Menghapus SELURUH data termasuk ' . number_format($totalUser) . ' user. Anda akan logout.'],
    ];
    $rl = $resetLabels[$activeReset] ?? null;
?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rl): ?>
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl" @click.away="$wire.closeReset()">
        <div class="p-6 text-center">
            <div class="w-16 h-16 <?php echo e($activeReset === 'all' ? 'bg-red-200' : 'bg-red-100'); ?> rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2"><?php echo e($rl['title']); ?></h3>
            <p class="text-sm text-gray-500 mb-1"><?php echo e($rl['desc']); ?></p>
            <p class="text-sm text-gray-500 mb-4">Ketik <strong class="text-red-600"><?php echo e($rl['text']); ?></strong> untuk konfirmasi:</p>
            <input type="text" wire:model="confirmText" class="input text-center text-sm" placeholder="Ketik <?php echo e($rl['text']); ?>" autocomplete="off">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['confirmText'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div class="flex gap-3 p-5 border-t">
            <button wire:click="closeReset" class="btn-secondary flex-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Batal
            </button>
            <button wire:click="doReset" class="btn-danger flex-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Ya, Reset Sekarang
            </button>
        </div>
    </div>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div><?php /**PATH /srv/apkpulsa/web/mushola-finance/resources/views/livewire/reset-data/index.blade.php ENDPATH**/ ?>