<?php

use App\Models\Kategori;
use App\Models\Keuangan;
use App\Models\Rekening;
use App\Models\TransferRekening;
use App\Models\User;
use App\Imports\KeuanganImport;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

?>

<div>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
<div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl px-4 py-3"
     x-data x-init="setTimeout(() => $el.remove(), 4000)">
    ✓ <?php echo e(session('success')); ?>

</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3"
     x-data x-init="setTimeout(() => $el.remove(), 5000)">
    ⚠ <?php echo e(session('error')); ?>

</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()?->isBendahara()): ?>
<div class="flex flex-wrap items-center gap-2 mb-3">
    <button wire:click="openCreate" class="btn-primary whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah
    </button>
    <button wire:click="openImport" class="btn-secondary whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m4-8l-4-4m0 0l-4 4m4-4v12"/>
        </svg>
        Import
    </button>
    <button wire:click="$set('showTransfer', true)" class="btn-secondary whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
        </svg>
        Transfer
    </button>
    <button wire:click="exportExcel" class="btn-secondary whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Excel
    </button>
</div>
<?php else: ?>
<div class="flex items-center gap-2 mb-3">
    <button wire:click="exportExcel" class="btn-secondary whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export Excel
    </button>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 mb-4">
    <div class="col-span-2 sm:col-span-3 lg:col-span-1">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari keterangan..." class="input text-sm" />
    </div>
    <select wire:model.live="filterKat" class="input text-sm">
        <option value="">Semua Kategori</option>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $kategori; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($k->id); ?>"><?php echo e($k->nama); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </select>
    <select wire:model.live="filterRek" class="input text-sm">
        <option value="">Semua Rekening</option>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $rekening; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($r->id); ?>"><?php echo e($r->nama_rek); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </select>
    <select wire:model.live="filterUser" class="input text-sm">
        <option value="">Semua User</option>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($u->id); ?>"><?php echo e($u->name); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </select>
    <input type="date" wire:model.live="filterFrom" value="<?php echo e($this->filterFrom); ?>" class="input text-sm" title="Dari tanggal">
    <input type="date" wire:model.live="filterTo"   value="<?php echo e($this->filterTo); ?>"   class="input text-sm" title="Sampai tanggal">
</div>


<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
    <div class="bg-amber-50 rounded-xl p-3">
        <p class="text-xs text-gray-500">Saldo Awal</p>
        <p class="text-base font-bold text-amber-600">Rp <?php echo e(number_format($saldoAwal, 0, ',', '.')); ?></p>
    </div>
    <div class="bg-emerald-50 rounded-xl p-3">
        <p class="text-xs text-gray-500">Total Masuk</p>
        <p class="text-base font-bold text-emerald-600">Rp <?php echo e(number_format($totalMasuk, 0, ',', '.')); ?></p>
    </div>
    <div class="bg-red-50 rounded-xl p-3">
        <p class="text-xs text-gray-500">Total Keluar</p>
        <p class="text-base font-bold text-red-500">Rp <?php echo e(number_format($totalKeluar, 0, ',', '.')); ?></p>
    </div>
    <div class="bg-blue-50 rounded-xl p-3">
        <p class="text-xs text-gray-500">Saldo Akhir</p>
        <p class="text-base font-bold text-blue-600">Rp <?php echo e(number_format($saldoAwal + $totalMasuk - $totalKeluar, 0, ',', '.')); ?></p>
    </div>
</div>


<div class="card overflow-hidden p-0">
    
    <div class="sm:hidden divide-y divide-gray-100">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $transaksi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="px-4 py-3 space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-400"><?php echo e($trx->tanggal->isoFormat('D MMM Y')); ?></span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($trx->masuk > 0): ?>
                <span class="text-sm font-bold text-emerald-600">+Rp <?php echo e(number_format($trx->masuk, 0, ',', '.')); ?></span>
                <?php else: ?>
                <span class="text-sm font-bold text-red-500">-Rp <?php echo e(number_format($trx->keluar, 0, ',', '.')); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->isSaldoAwalTx($trx->keterangan)): ?>
                <div class="flex items-center gap-1.5">
                    <span class="inline-flex px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">SA</span>
                    <p class="text-sm text-gray-800"><?php echo e($this->readableKeterangan($trx->keterangan)); ?></p>
                </div>
                <?php else: ?>
                <p class="text-sm text-gray-800"><?php echo e($this->readableKeterangan($trx->keterangan)); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="flex items-center gap-1.5 text-[11px] text-gray-400">
                <span class="px-1.5 py-0.5 rounded bg-primary-50 text-primary-700 font-medium"><?php echo e($trx->kategori->nama ?? '-'); ?></span>
                <span>&middot;</span>
                <span><?php echo e($trx->rekening->nama_rek ?? '-'); ?></span>
                <span>&middot;</span>
                <span><?php echo e($trx->creator->name ?? '-'); ?></span>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()?->isBendahara() && !$this->isSaldoAwalTx($trx->keterangan)): ?>
            <div class="flex items-center gap-1 pt-0.5">
                <button wire:click="openEdit(<?php echo e($trx->id); ?>)" class="p-1 hover:bg-blue-100 text-blue-600 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button wire:click="confirmDeleteItem(<?php echo e($trx->id); ?>)" class="p-1 hover:bg-red-100 text-red-500 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="px-4 py-12 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Belum ada transaksi
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    
    <div class="hidden sm:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-center px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider w-12">No</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Keterangan</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider hidden md:table-cell">Kategori</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Rekening</th>
                    <th class="text-right px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Masuk</th>
                    <th class="text-right px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Keluar</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Dibuat oleh</th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()?->isBendahara()): ?>
                    <th class="text-center px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $transaksi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 text-center text-gray-400 text-xs"><?php echo e($transaksi->firstItem() + $loop->index); ?></td>
                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap"><?php echo e($trx->tanggal->format('d/m/Y')); ?></td>
                    <td class="px-4 py-3 text-gray-800 font-medium max-w-xs">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->isSaldoAwalTx($trx->keterangan)): ?>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-700">Saldo Awal</span>
                            <span class="truncate"><?php echo e($this->readableKeterangan($trx->keterangan)); ?></span>
                        </div>
                        <?php else: ?>
                        <span class="truncate block"><?php echo e($this->readableKeterangan($trx->keterangan)); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-primary-100 text-primary-700">
                            <?php echo e($trx->kategori->nama ?? '-'); ?>

                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 hidden lg:table-cell"><?php echo e($trx->rekening->nama_rek ?? '-'); ?></td>
                    <td class="px-4 py-3 text-right font-semibold text-emerald-600">
                        <?php echo e($trx->masuk > 0 ? 'Rp '.number_format($trx->masuk, 0, ',', '.') : ''); ?>

                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-red-500">
                        <?php echo e($trx->keluar > 0 ? 'Rp '.number_format($trx->keluar, 0, ',', '.') : ''); ?>

                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs hidden lg:table-cell"><?php echo e($trx->creator->name ?? '-'); ?></td>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()?->isBendahara()): ?>
                    <td class="px-4 py-3 text-center">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->isSaldoAwalTx($trx->keterangan)): ?>
                        <span class="inline-flex items-center px-2 py-1 rounded-lg text-[11px] font-bold bg-gray-100 text-gray-500">
                            Terkunci
                        </span>
                        <?php else: ?>
                        <div class="flex items-center justify-center gap-1">
                            <button wire:click="openEdit(<?php echo e($trx->id); ?>)"
                                    class="p-1.5 hover:bg-blue-100 text-blue-600 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button wire:click="confirmDeleteItem(<?php echo e($trx->id); ?>)"
                                    class="p-1.5 hover:bg-red-100 text-red-500 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="9" class="px-4 py-12 text-center text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Belum ada transaksi
                    </td>
                </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($transaksi->hasPages() || $transaksi->total() > 0): ?>
    <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/50 space-y-3">
        
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <span>Menampilkan <?php echo e($transaksi->firstItem() ?? 0); ?>–<?php echo e($transaksi->lastItem() ?? 0); ?> dari <?php echo e($transaksi->total()); ?></span>
                <select wire:model.live="perPage" class="border border-gray-300 rounded-md py-1 px-2 text-xs bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [10, 20, 30, 50, 100, 500, 1000]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($size); ?>"><?php echo e($size); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
                <span>per halaman</span>
            </div>
        </div>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($transaksi->hasPages()): ?>
        <div class="flex flex-wrap items-center justify-center gap-1">
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($transaksi->onFirstPage()): ?>
                <span class="px-3 py-1.5 text-xs text-gray-400 bg-gray-100 rounded-lg cursor-default">&laquo; Prev</span>
            <?php else: ?>
                <button wire:click="previousPage" class="px-3 py-1.5 text-xs text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    &laquo; Prev
                </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php
                $current = $transaksi->currentPage();
                $last = $transaksi->lastPage();
                $pages = collect();
                $pages->push(1);
                for ($i = max(2, $current - 2); $i <= min($last - 1, $current + 2); $i++) {
                    $pages->push($i);
                }
                if ($last > 1) $pages->push($last);
                $pages = $pages->unique()->sort()->values();
            ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($idx > 0 && $page - $pages[$idx - 1] > 1): ?>
                    <span class="px-2 py-1.5 text-xs text-gray-400">...</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($page == $current): ?>
                    <span class="px-3 py-1.5 text-xs font-bold text-white bg-primary-600 rounded-lg"><?php echo e($page); ?></span>
                <?php else: ?>
                    <button wire:click="gotoPage(<?php echo e($page); ?>)" class="px-3 py-1.5 text-xs text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        <?php echo e($page); ?>

                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($transaksi->hasMorePages()): ?>
                <button wire:click="nextPage" class="px-3 py-1.5 text-xs text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Next &raquo;
                </button>
            <?php else: ?>
                <span class="px-3 py-1.5 text-xs text-gray-400 bg-gray-100 rounded-lg cursor-default">Next &raquo;</span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal): ?>
<div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/50"
     x-data x-transition>
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl" @click.away="$wire.set('showModal', false)">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <h3 class="text-base font-bold text-gray-800">
                <?php echo e($editId ? 'Edit Transaksi' : 'Tambah Transaksi'); ?>

            </h3>
            <button wire:click="$set('showModal', false)" class="p-1.5 hover:bg-gray-100 rounded-lg">
                <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-5 space-y-4">
            
            <div>
                <label class="label">Jenis Transaksi</label>
                <div class="flex rounded-xl overflow-hidden border border-gray-200">
                    <button type="button" wire:click="$set('jenis', 'masuk')"
                            class="flex-1 py-2.5 text-sm font-semibold transition
                                   <?php echo e($jenis === 'masuk' ? 'bg-emerald-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'); ?>">
                        ↑ Masuk
                    </button>
                    <button type="button" wire:click="$set('jenis', 'keluar')"
                            class="flex-1 py-2.5 text-sm font-semibold transition border-l border-gray-200
                                   <?php echo e($jenis === 'keluar' ? 'bg-red-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'); ?>">
                        ↓ Keluar
                    </button>
                </div>
            </div>
            <div>
                <label class="label">Tanggal</label>
                <input type="date" wire:model="tanggal" class="input" />
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['tanggal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <label class="label">Jumlah (Rp)</label>
                <input type="text" wire:model="jumlah" class="input" inputmode="numeric"
                       @input="$event.target.value = ($event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'))"
                       placeholder="0" />
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['jumlah'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <label class="label">Rekening</label>
                <select wire:model="id_rekening" class="input">
                    <option value="">-- Pilih Rekening --</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $rekening; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($r->id); ?>"><?php echo e($r->nama_rek); ?> (<?php echo e($r->no_rek); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['id_rekening'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <label class="label">Kategori</label>
                <select wire:model="id_kategori" class="input">
                    <option value="">-- Pilih Kategori --</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $kategori; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($k->id); ?>"><?php echo e($k->nama); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['id_kategori'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <label class="label">Keterangan</label>
                <textarea wire:model="keterangan" class="input" rows="2" placeholder="Keterangan transaksi..."></textarea>
            </div>
        </div>
        <div class="px-5 pb-5 flex gap-2">
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


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showTransfer): ?>
<div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <h3 class="text-base font-bold text-gray-800">Transfer Rekening</h3>
            <button wire:click="$set('showTransfer', false)" class="p-1.5 hover:bg-gray-100 rounded-lg">
                <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-5 space-y-4">
            <div class="bg-blue-50 rounded-xl p-3 text-xs text-blue-700">
                💡 Transfer rekening tidak mempengaruhi saldo masuk/keluar kategori.
                Ini hanya mencatat perpindahan dana antar rekening.
            </div>
            <div>
                <label class="label">Dari Rekening</label>
                <select wire:model="tr_dari" class="input">
                    <option value="">-- Pilih --</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $rekening; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($r->id); ?>"><?php echo e($r->nama_rek); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['tr_dari'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <label class="label">Ke Rekening</label>
                <select wire:model="tr_ke" class="input">
                    <option value="">-- Pilih --</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $rekening; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($r->id); ?>"><?php echo e($r->nama_rek); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['tr_ke'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <label class="label">Kategori</label>
                <select wire:model="tr_kat" class="input">
                    <option value="">-- Pilih Kategori --</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $kategori; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($k->id); ?>"><?php echo e($k->nama); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['tr_kat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <label class="label">Jumlah (Rp)</label>
                <input type="text" wire:model="tr_jumlah" class="input" inputmode="numeric"
                       @input="$event.target.value = ($event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'))"
                       placeholder="0" />
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['tr_jumlah'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <label class="label">Tanggal</label>
                <input type="date" wire:model="tr_tanggal" class="input" />
            </div>
            <div>
                <label class="label">Keterangan</label>
                <textarea wire:model="tr_ket" class="input" rows="2" placeholder="Keterangan transfer..."></textarea>
            </div>
        </div>
        <div class="px-5 pb-5 flex gap-2">
            <button wire:click="$set('showTransfer', false)" class="btn-secondary flex-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Batal
            </button>
            <button wire:click="saveTransfer" class="btn-primary flex-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Transfer
            </button>
        </div>
    </div>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($confirmDelete): ?>
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-2xl w-full max-w-sm p-6 shadow-2xl text-center">
        <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h3 class="text-base font-bold text-gray-800 mb-2">Hapus Transaksi?</h3>
        <p class="text-sm text-gray-500 mb-5">Data yang dihapus tidak dapat dikembalikan.</p>
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


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showImport): ?>
<div class="fixed inset-0 z-50 bg-black/50">
    <div class="h-full w-full p-0 sm:p-4">
        <div class="bg-white h-full w-full sm:rounded-2xl shadow-2xl flex flex-col overflow-hidden" @click.away="$wire.set('showImport', false)">
        <div class="flex items-center justify-between p-5 border-b bg-white shrink-0">
            <h3 class="text-lg font-bold text-gray-800">Import Data Keuangan</h3>
            <button wire:click="$set('showImport', false)" class="p-1.5 hover:bg-gray-100 rounded-lg">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-5 space-y-4 bg-gray-50/40">
            
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <p class="text-sm text-blue-800 mb-2"><strong>Langkah 1:</strong> Download template Excel terlebih dahulu</p>
                <button wire:click="downloadTemplate" class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-800">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download Template
                </button>
                <p class="text-xs text-blue-600 mt-1">Template berisi contoh data + daftar kategori & rekening yang valid.</p>
            </div>

            
            <div>
                <p class="text-sm text-gray-700 mb-2"><strong>Langkah 2:</strong> Upload file Excel yang sudah diisi</p>
                <input type="file" wire:model="importFile" accept=".xlsx,.xls,.csv"
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100" />
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['importFile'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div wire:loading wire:target="importFile" class="text-sm text-gray-500 mt-2">Uploading...</div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($importPreviewReady): ?>
            <div class="bg-white border border-gray-200 rounded-xl p-4 space-y-4 shadow-sm">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div>
                        <p class="text-sm font-bold text-gray-800">Preview Import</p>
                        <p class="text-xs text-gray-500">Periksa data valid dan error sebelum konfirmasi import.</p>
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-700">Total: <?php echo e($importPreviewSummary['total_rows'] ?? 0); ?></span>
                        <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700">Valid: <?php echo e($importPreviewSummary['valid_rows'] ?? 0); ?></span>
                        <span class="px-2.5 py-1 rounded-full bg-red-100 text-red-700">Error: <?php echo e($importPreviewSummary['invalid_rows'] ?? 0); ?></span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($importPreviewSummary['skipped_rows'] ?? 0) > 0): ?>
                        <span class="px-2.5 py-1 rounded-full bg-gray-200 text-gray-700">Kosong dilewati: <?php echo e($importPreviewSummary['skipped_rows']); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="overflow-auto max-h-[52vh] border border-gray-200 rounded-xl bg-white">
                    <table class="min-w-full text-xs">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr class="text-gray-600">
                                <th class="px-3 py-2 text-left">Baris</th>
                                <th class="px-3 py-2 text-left">Tanggal</th>
                                <th class="px-3 py-2 text-left">Keterangan</th>
                                <th class="px-3 py-2 text-left">Kategori</th>
                                <th class="px-3 py-2 text-left">Rekening</th>
                                <th class="px-3 py-2 text-right">Masuk</th>
                                <th class="px-3 py-2 text-right">Keluar</th>
                                <th class="px-3 py-2 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $importPreviewRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="align-top <?php echo e($row['status'] === 'valid' ? 'bg-white' : 'bg-red-50/40'); ?>">
                                <td class="px-3 py-2 text-gray-500 whitespace-nowrap"><?php echo e($row['row_num']); ?></td>
                                <td class="px-3 py-2 text-gray-700 whitespace-nowrap"><?php echo e($row['tanggal'] ?: ($row['tanggal_raw'] ?: '-')); ?></td>
                                <td class="px-3 py-2 text-gray-700 min-w-[220px]"><?php echo e($row['keterangan'] ?: '-'); ?></td>
                                <td class="px-3 py-2 text-gray-700 whitespace-nowrap"><?php echo e($row['kategori'] ?: '-'); ?></td>
                                <td class="px-3 py-2 text-gray-700 whitespace-nowrap"><?php echo e($row['rekening'] ?: '-'); ?></td>
                                <td class="px-3 py-2 text-right text-emerald-700 whitespace-nowrap"><?php echo e($row['masuk'] > 0 ? 'Rp '.number_format($row['masuk'], 0, ',', '.') : '-'); ?></td>
                                <td class="px-3 py-2 text-right text-red-700 whitespace-nowrap"><?php echo e($row['keluar'] > 0 ? 'Rp '.number_format($row['keluar'], 0, ',', '.') : '-'); ?></td>
                                <td class="px-3 py-2 min-w-[220px]">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row['status'] === 'valid'): ?>
                                    <span class="inline-flex px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 font-medium">Siap diimport</span>
                                    <?php else: ?>
                                    <div class="space-y-1">
                                        <span class="inline-flex px-2 py-1 rounded-full bg-red-100 text-red-700 font-medium">Perlu diperbaiki</span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $row['errors']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <p class="text-red-600">• <?php echo e($error); ?></p>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="px-3 py-6 text-center text-gray-400">Belum ada data untuk dipreview.</td>
                            </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($importResult): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl px-4 py-3">
                ✓ <?php echo e($importResult); ?>

            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($importErrors) > 0): ?>
            <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                <p class="text-sm font-bold text-red-700 mb-2">Beberapa baris gagal diimport:</p>
                <ul class="text-xs text-red-600 space-y-1 max-h-40 overflow-y-auto">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $importErrors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li>• <?php echo e($err); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ul>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div class="flex gap-3 p-5 border-t bg-white shrink-0">
            <button wire:click="$set('showImport', false)" class="btn-secondary flex-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Tutup
            </button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$importPreviewReady): ?>
            <button wire:click="previewImport" class="btn-primary flex-1" wire:loading.attr="disabled" wire:target="previewImport,importFile">
                <svg class="w-4 h-4" wire:loading.remove wire:target="previewImport" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <span wire:loading.remove wire:target="previewImport">Preview Data</span>
                <span wire:loading wire:target="previewImport">Membaca file...</span>
            </button>
            <?php else: ?>
            <button wire:click="previewImport" class="btn-secondary flex-1" wire:loading.attr="disabled" wire:target="previewImport">
                <svg class="w-4 h-4" wire:loading.remove wire:target="previewImport" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m14.836 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span wire:loading.remove wire:target="previewImport">Refresh Preview</span>
                <span wire:loading wire:target="previewImport">Memperbarui...</span>
            </button>
            <button wire:click="processImport" class="btn-primary flex-1" wire:loading.attr="disabled" wire:target="processImport">
                <svg class="w-4 h-4" wire:loading.remove wire:target="processImport" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span wire:loading.remove wire:target="processImport">Konfirmasi Import</span>
                <span wire:loading wire:target="processImport">Mengimport...</span>
            </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        </div>
    </div>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div><?php /**PATH /srv/apkpulsa/web/mushola-finance/resources/views/livewire/arus-kas/index.blade.php ENDPATH**/ ?>