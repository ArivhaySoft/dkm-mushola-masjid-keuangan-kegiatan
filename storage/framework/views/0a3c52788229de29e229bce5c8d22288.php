<?php

use App\Models\Kategori;
use App\Models\Keuangan;
use App\Models\Rekening;
use Livewire\Volt\Component;

?>

<div>
<div class="card mb-5">
    <div class="grid grid-cols-2 sm:flex sm:flex-row gap-3 items-end">
        <div class="col-span-1 sm:flex-1">
            <label class="label">Tahun</label>
            <select wire:model="tahun" class="input">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = range(now()->year, 2020); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($y); ?>"><?php echo e($y); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
        </div>
        <button wire:click="generate" class="col-span-1 btn-primary">Tampilkan</button>
    </div>
</div>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasData): ?>
<div class="flex justify-end gap-2 mb-4">
    <button wire:click="downloadExcel" class="btn-secondary text-xs">Export Excel</button>
    <button wire:click="downloadPdf"   class="btn-primary text-xs">Cetak PDF</button>
</div>


<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
    <div class="card text-center">
        <p class="text-xs text-gray-500">Total Masuk <?php echo e($tahun); ?></p>
        <p class="text-lg font-extrabold text-emerald-600">Rp <?php echo e(number_format($totalMasuk, 0, ',', '.')); ?></p>
    </div>
    <div class="card text-center">
        <p class="text-xs text-gray-500">Total Keluar <?php echo e($tahun); ?></p>
        <p class="text-lg font-extrabold text-red-500">Rp <?php echo e(number_format($totalKeluar, 0, ',', '.')); ?></p>
    </div>
    <div class="card text-center">
        <p class="text-xs text-gray-500">Surplus/Defisit</p>
        <p class="text-lg font-extrabold <?php echo e($totalMasuk - $totalKeluar >= 0 ? 'text-blue-600' : 'text-red-600'); ?>">
            Rp <?php echo e(number_format($totalMasuk - $totalKeluar, 0, ',', '.')); ?>

        </p>
    </div>
</div>


<div class="card mb-4">
    <h3 class="text-sm font-bold text-gray-700 mb-3">Rekap Per Bulan – <?php echo e($tahun); ?></h3>
    
    <div class="sm:hidden divide-y divide-gray-100">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $dataPerBulan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="py-2.5 space-y-0.5 <?php echo e($row['masuk'] == 0 && $row['keluar'] == 0 ? 'opacity-40' : ''); ?>">
            <p class="font-semibold text-gray-800 text-sm"><?php echo e($row['bulan']); ?></p>
            <div class="space-y-0.5 text-xs">
                <div class="flex justify-between"><span class="text-gray-400">Saldo Awal</span><span class="text-gray-600">Rp <?php echo e(number_format($row['saldo_awal'], 0, ',', '.')); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-400">Masuk</span><span class="text-emerald-600 font-medium"><?php echo e($row['masuk'] > 0 ? 'Rp '.number_format($row['masuk'], 0, ',', '.') : '-'); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-400">Keluar</span><span class="text-red-500 font-medium"><?php echo e($row['keluar'] > 0 ? 'Rp '.number_format($row['keluar'], 0, ',', '.') : '-'); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-400">Saldo Akhir</span><span class="font-bold <?php echo e($row['saldo_akhir'] >= 0 ? 'text-blue-600' : 'text-red-600'); ?>">Rp <?php echo e(number_format($row['saldo_akhir'], 0, ',', '.')); ?></span></div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="py-2.5 bg-gray-100 px-1 space-y-0.5">
            <p class="font-bold text-gray-800 text-sm">Total</p>
            <div class="space-y-0.5 text-xs font-bold">
                <div class="flex justify-between"><span class="text-gray-500">Masuk</span><span class="text-emerald-600">Rp <?php echo e(number_format($totalMasuk, 0, ',', '.')); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Keluar</span><span class="text-red-500">Rp <?php echo e(number_format($totalKeluar, 0, ',', '.')); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Surplus/Defisit</span><span class="text-blue-600">Rp <?php echo e(number_format($totalMasuk - $totalKeluar, 0, ',', '.')); ?></span></div>
            </div>
        </div>
    </div>
    
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
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $dataPerBulan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="<?php echo e($row['masuk'] == 0 && $row['keluar'] == 0 ? 'text-gray-300' : ''); ?>">
                    <td class="px-3 py-2.5 font-medium"><?php echo e($row['bulan']); ?></td>
                    <td class="px-3 py-2.5 text-right text-gray-600">Rp <?php echo e(number_format($row['saldo_awal'], 0, ',', '.')); ?></td>
                    <td class="px-3 py-2.5 text-right text-emerald-600"><?php echo e($row['masuk'] > 0 ? 'Rp '.number_format($row['masuk'], 0, ',', '.') : '-'); ?></td>
                    <td class="px-3 py-2.5 text-right text-red-500"><?php echo e($row['keluar'] > 0 ? 'Rp '.number_format($row['keluar'], 0, ',', '.') : '-'); ?></td>
                    <td class="px-3 py-2.5 text-right font-semibold <?php echo e($row['saldo_akhir'] >= 0 ? 'text-blue-600' : 'text-red-600'); ?>">
                        Rp <?php echo e(number_format($row['saldo_akhir'], 0, ',', '.')); ?>

                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <tr class="bg-gray-100 font-bold">
                    <td class="px-3 py-2.5">Total</td>
                    <td class="px-3 py-2.5 text-right text-gray-500">-</td>
                    <td class="px-3 py-2.5 text-right text-emerald-600">Rp <?php echo e(number_format($totalMasuk, 0, ',', '.')); ?></td>
                    <td class="px-3 py-2.5 text-right text-red-500">Rp <?php echo e(number_format($totalKeluar, 0, ',', '.')); ?></td>
                    <td class="px-3 py-2.5 text-right text-blue-600">Rp <?php echo e(number_format($totalMasuk - $totalKeluar, 0, ',', '.')); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>


<div class="card mb-4">
    <h3 class="text-sm font-bold text-gray-700 mb-3">Rekap Per Kategori – <?php echo e($tahun); ?></h3>
    
    <div class="sm:hidden divide-y divide-gray-100">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $dataKategori; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="py-2.5 space-y-0.5">
            <p class="font-semibold text-gray-800 text-sm"><?php echo e($dk['nama']); ?></p>
            <div class="space-y-0.5 text-xs">
                <div class="flex justify-between"><span class="text-gray-400">Saldo Awal</span><span class="text-gray-600">Rp <?php echo e(number_format($dk['saldo_awal'], 0, ',', '.')); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-400">Masuk</span><span class="text-emerald-600 font-medium">Rp <?php echo e(number_format($dk['masuk'], 0, ',', '.')); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-400">Keluar</span><span class="text-red-500 font-medium">Rp <?php echo e(number_format($dk['keluar'], 0, ',', '.')); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-400">Saldo Akhir</span><span class="font-bold text-blue-600">Rp <?php echo e(number_format($dk['saldo_akhir'], 0, ',', '.')); ?></span></div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="py-2.5 bg-gray-100 px-1 space-y-0.5">
            <p class="font-bold text-gray-800 text-sm">Total</p>
            <div class="space-y-0.5 text-xs font-bold">
                <div class="flex justify-between"><span class="text-gray-500">Saldo Awal</span><span>Rp <?php echo e(number_format(collect($dataKategori)->sum('saldo_awal'), 0, ',', '.')); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Masuk</span><span class="text-emerald-600">Rp <?php echo e(number_format(collect($dataKategori)->sum('masuk'), 0, ',', '.')); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Keluar</span><span class="text-red-500">Rp <?php echo e(number_format(collect($dataKategori)->sum('keluar'), 0, ',', '.')); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Saldo Akhir</span><span class="text-blue-600">Rp <?php echo e(number_format(collect($dataKategori)->sum('saldo_akhir'), 0, ',', '.')); ?></span></div>
            </div>
        </div>
    </div>
    
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
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $dataKategori; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="px-3 py-2.5 font-medium text-gray-800"><?php echo e($dk['nama']); ?></td>
                    <td class="px-3 py-2.5 text-right text-gray-600">Rp <?php echo e(number_format($dk['saldo_awal'], 0, ',', '.')); ?></td>
                    <td class="px-3 py-2.5 text-right text-emerald-600">Rp <?php echo e(number_format($dk['masuk'], 0, ',', '.')); ?></td>
                    <td class="px-3 py-2.5 text-right text-red-500">Rp <?php echo e(number_format($dk['keluar'], 0, ',', '.')); ?></td>
                    <td class="px-3 py-2.5 text-right text-blue-600 font-bold">Rp <?php echo e(number_format($dk['saldo_akhir'], 0, ',', '.')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <tr class="bg-gray-100 font-bold">
                    <td class="px-3 py-2.5 text-gray-800">Total</td>
                    <td class="px-3 py-2.5 text-right text-gray-600">Rp <?php echo e(number_format(collect($dataKategori)->sum('saldo_awal'), 0, ',', '.')); ?></td>
                    <td class="px-3 py-2.5 text-right text-emerald-600">Rp <?php echo e(number_format(collect($dataKategori)->sum('masuk'), 0, ',', '.')); ?></td>
                    <td class="px-3 py-2.5 text-right text-red-500">Rp <?php echo e(number_format(collect($dataKategori)->sum('keluar'), 0, ',', '.')); ?></td>
                    <td class="px-3 py-2.5 text-right text-blue-600">Rp <?php echo e(number_format(collect($dataKategori)->sum('saldo_akhir'), 0, ',', '.')); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>


<div class="card">
    <h3 class="text-sm font-bold text-gray-700 mb-3">Rekap Per Rekening – <?php echo e($tahun); ?></h3>
    
    <div class="sm:hidden divide-y divide-gray-100">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $dataRekening; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="py-2.5 space-y-0.5">
            <p class="font-semibold text-gray-800 text-sm"><?php echo e($dr['nama']); ?></p>
            <p class="text-[11px] text-gray-400"><?php echo e($dr['atas_nama']); ?> &middot; <?php echo e($dr['no_rek']); ?></p>
            <div class="space-y-0.5 text-xs">
                <div class="flex justify-between"><span class="text-gray-400">Saldo Awal</span><span class="text-gray-600">Rp <?php echo e(number_format($dr['saldo_awal'], 0, ',', '.')); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-400">Masuk</span><span class="text-emerald-600 font-medium">Rp <?php echo e(number_format($dr['masuk'], 0, ',', '.')); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-400">Keluar</span><span class="text-red-500 font-medium">Rp <?php echo e(number_format($dr['keluar'], 0, ',', '.')); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-400">Saldo Akhir</span><span class="font-bold <?php echo e($dr['saldo_akhir'] >= 0 ? 'text-blue-600' : 'text-red-600'); ?>">Rp <?php echo e(number_format($dr['saldo_akhir'], 0, ',', '.')); ?></span></div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="py-2.5 bg-gray-100 px-1 space-y-0.5">
            <p class="font-bold text-gray-800 text-sm">Total</p>
            <div class="space-y-0.5 text-xs font-bold">
                <div class="flex justify-between"><span class="text-gray-500">Saldo Awal</span><span>Rp <?php echo e(number_format(collect($dataRekening)->sum('saldo_awal'), 0, ',', '.')); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Saldo Akhir</span><span class="text-blue-600">Rp <?php echo e(number_format(collect($dataRekening)->sum('saldo_akhir'), 0, ',', '.')); ?></span></div>
            </div>
        </div>
    </div>
    
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
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $dataRekening; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="px-3 py-2.5 font-medium text-gray-800"><?php echo e($dr['nama']); ?></td>
                    <td class="px-3 py-2.5 text-gray-600"><?php echo e($dr['atas_nama']); ?></td>
                    <td class="px-3 py-2.5 text-gray-600"><?php echo e($dr['no_rek']); ?></td>
                    <td class="px-3 py-2.5 text-right text-gray-600">Rp <?php echo e(number_format($dr['saldo_awal'], 0, ',', '.')); ?></td>
                    <td class="px-3 py-2.5 text-right text-emerald-600">Rp <?php echo e(number_format($dr['masuk'], 0, ',', '.')); ?></td>
                    <td class="px-3 py-2.5 text-right text-red-500">Rp <?php echo e(number_format($dr['keluar'], 0, ',', '.')); ?></td>
                    <td class="px-3 py-2.5 text-right font-bold <?php echo e($dr['saldo_akhir'] >= 0 ? 'text-blue-600' : 'text-red-600'); ?>">Rp <?php echo e(number_format($dr['saldo_akhir'], 0, ',', '.')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <tr class="bg-gray-100 font-bold">
                    <td class="px-3 py-2.5 text-gray-800" colspan="3">Total</td>
                    <td class="px-3 py-2.5 text-right text-gray-600">Rp <?php echo e(number_format(collect($dataRekening)->sum('saldo_awal'), 0, ',', '.')); ?></td>
                    <td class="px-3 py-2.5 text-right text-gray-500">-</td>
                    <td class="px-3 py-2.5 text-right text-gray-500">-</td>
                    <td class="px-3 py-2.5 text-right text-blue-600">Rp <?php echo e(number_format(collect($dataRekening)->sum('saldo_akhir'), 0, ',', '.')); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="card text-center py-14">
    <p class="text-gray-400 text-sm">Pilih tahun dan klik <strong>Tampilkan</strong>.</p>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div><?php /**PATH /srv/apkpulsa/web/mushola-finance/resources/views/livewire/laporan/tahunan.blade.php ENDPATH**/ ?>