<?php

use App\Models\Kategori;
use App\Models\Keuangan;
use App\Models\Rekening;
use Livewire\Volt\Component;

?>

<div>
<div class="card mb-5">
    <h2 class="text-sm font-bold text-gray-700 mb-4">Pilih Bulan & Tahun</h2>
    <div class="grid grid-cols-2 sm:flex sm:flex-row gap-3 items-end">
        <div class="col-span-1 sm:flex-1">
            <label class="label">Bulan</label>
            <select wire:model="bulan" class="input">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = range(1,12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($m); ?>"><?php echo e(\Carbon\Carbon::create(null, $m)->isoFormat('MMMM')); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
        </div>
        <div class="col-span-1 sm:flex-1">
            <label class="label">Tahun</label>
            <select wire:model="tahun" class="input">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = range(now()->year, 2020); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($y); ?>"><?php echo e($y); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasData): ?>
<div class="flex justify-end gap-2 mb-4">
    <button wire:click="downloadExcel" class="btn-secondary text-xs">Export Excel</button>
    <button wire:click="downloadPdf"   class="btn-primary text-xs">Cetak PDF</button>
</div>


<div class="card mb-4">
    <h3 class="text-sm font-bold text-gray-700 mb-1">Laporan Bulan: <?php echo e($this->getBulanLabel()); ?></h3>

    <div class="grid grid-cols-3 gap-3 mb-5">
        <div class="bg-emerald-50 rounded-xl p-3 text-center">
            <p class="text-xs text-gray-500">Total Masuk</p>
            <p class="text-sm font-extrabold text-emerald-600">Rp <?php echo e(number_format($totalMasuk, 0, ',', '.')); ?></p>
        </div>
        <div class="bg-red-50 rounded-xl p-3 text-center">
            <p class="text-xs text-gray-500">Total Keluar</p>
            <p class="text-sm font-extrabold text-red-500">Rp <?php echo e(number_format($totalKeluar, 0, ',', '.')); ?></p>
        </div>
        <div class="bg-blue-50 rounded-xl p-3 text-center">
            <p class="text-xs text-gray-500">Saldo Akhir</p>
            <p class="text-sm font-extrabold text-blue-600">Rp <?php echo e(number_format($totalSaldo, 0, ',', '.')); ?></p>
        </div>
    </div>

    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Ringkasan per Kategori</h4>
    
    <div class="sm:hidden divide-y divide-gray-100 border rounded-xl mb-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $dataKategori; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="px-4 py-3 space-y-1">
            <p class="font-semibold text-gray-800 text-sm"><?php echo e($dk['nama']); ?></p>
            <div class="space-y-0.5 text-xs">
                <div class="flex justify-between"><span class="text-gray-400">Saldo Awal</span><span class="text-gray-600">Rp <?php echo e(number_format($dk['saldo_awal'], 0, ',', '.')); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-400">Masuk</span><span class="text-emerald-600 font-medium">Rp <?php echo e(number_format($dk['masuk'], 0, ',', '.')); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-400">Keluar</span><span class="text-red-500 font-medium">Rp <?php echo e(number_format($dk['keluar'], 0, ',', '.')); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-400">Saldo Akhir</span><span class="font-bold text-blue-600">Rp <?php echo e(number_format($dk['saldo_akhir'], 0, ',', '.')); ?></span></div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="px-4 py-3 bg-gray-100 space-y-1">
            <p class="font-bold text-gray-800 text-sm">Total</p>
            <div class="space-y-0.5 text-xs font-bold">
                <div class="flex justify-between"><span class="text-gray-500">Saldo Awal</span><span>Rp <?php echo e(number_format(collect($dataKategori)->sum('saldo_awal'), 0, ',', '.')); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Masuk</span><span class="text-emerald-600">Rp <?php echo e(number_format($totalMasuk, 0, ',', '.')); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Keluar</span><span class="text-red-500">Rp <?php echo e(number_format($totalKeluar, 0, ',', '.')); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Saldo Akhir</span><span class="text-blue-600">Rp <?php echo e(number_format($totalSaldo, 0, ',', '.')); ?></span></div>
            </div>
        </div>
    </div>
    
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
                    <td class="px-3 py-2.5">Total</td>
                    <td class="px-3 py-2.5 text-right">Rp <?php echo e(number_format(collect($dataKategori)->sum('saldo_awal'), 0, ',', '.')); ?></td>
                    <td class="px-3 py-2.5 text-right text-emerald-600">Rp <?php echo e(number_format($totalMasuk, 0, ',', '.')); ?></td>
                    <td class="px-3 py-2.5 text-right text-red-500">Rp <?php echo e(number_format($totalKeluar, 0, ',', '.')); ?></td>
                    <td class="px-3 py-2.5 text-right text-blue-600">Rp <?php echo e(number_format($totalSaldo, 0, ',', '.')); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Saldo per Rekening</h4>
    
    <div class="sm:hidden divide-y divide-gray-100 border rounded-xl">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $dataRekening; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="px-4 py-3 space-y-1">
            <p class="font-semibold text-gray-800 text-sm"><?php echo e($dr['nama']); ?></p>
            <p class="text-[11px] text-gray-400"><?php echo e($dr['atas_nama'] ?? ''); ?> &middot; <?php echo e($dr['no_rek']); ?></p>
            <div class="space-y-0.5 text-xs">
                <div class="flex justify-between"><span class="text-gray-400">Saldo Awal</span><span class="text-gray-600">Rp <?php echo e(number_format($dr['saldo_awal'], 0, ',', '.')); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-400">Masuk</span><span class="text-emerald-600 font-medium">Rp <?php echo e(number_format($dr['masuk'], 0, ',', '.')); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-400">Keluar</span><span class="text-red-500 font-medium">Rp <?php echo e(number_format($dr['keluar'], 0, ',', '.')); ?></span></div>
                <div class="flex justify-between"><span class="text-gray-400">Saldo Akhir</span><span class="font-bold text-blue-600">Rp <?php echo e(number_format($dr['saldo_akhir'], 0, ',', '.')); ?></span></div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="px-4 py-3 bg-gray-100 space-y-1">
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
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Saldo Awal</th>
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Masuk</th>
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Keluar</th>
                    <th class="text-right px-3 py-2 text-xs font-bold text-gray-500">Saldo Akhir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $dataRekening; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="px-3 py-2.5"><p class="font-medium text-gray-800"><?php echo e($dr['nama']); ?></p><p class="text-xs text-gray-400"><?php echo e($dr['no_rek']); ?></p></td>
                    <td class="px-3 py-2.5 text-right text-gray-600">Rp <?php echo e(number_format($dr['saldo_awal'], 0, ',', '.')); ?></td>
                    <td class="px-3 py-2.5 text-right text-emerald-600">Rp <?php echo e(number_format($dr['masuk'], 0, ',', '.')); ?></td>
                    <td class="px-3 py-2.5 text-right text-red-500">Rp <?php echo e(number_format($dr['keluar'], 0, ',', '.')); ?></td>
                    <td class="px-3 py-2.5 text-right text-blue-600 font-bold">Rp <?php echo e(number_format($dr['saldo_akhir'], 0, ',', '.')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <tr class="bg-gray-100 font-bold">
                    <td class="px-3 py-2.5">Total</td>
                    <td class="px-3 py-2.5 text-right">Rp <?php echo e(number_format(collect($dataRekening)->sum('saldo_awal'), 0, ',', '.')); ?></td>
                    <td class="px-3 py-2.5 text-right text-gray-500">-</td>
                    <td class="px-3 py-2.5 text-right text-gray-500">-</td>
                    <td class="px-3 py-2.5 text-right text-blue-600">Rp <?php echo e(number_format(collect($dataRekening)->sum('saldo_akhir'), 0, ',', '.')); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>


<div class="space-y-4">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $detailPerKategori; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($detail['transaksi']) > 0): ?>
    <div class="card">
        <h4 class="text-sm font-bold text-gray-700 mb-3">Detail: <?php echo e($detail['kategori']); ?></h4>
        
        <div class="sm:hidden divide-y divide-gray-100">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $detail['transaksi']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="py-2.5 space-y-0.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-400"><?php echo e(\Carbon\Carbon::parse($trx['tanggal'])->isoFormat('D MMM Y')); ?></span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($trx['masuk'] > 0): ?>
                    <span class="text-sm font-bold text-emerald-600">+Rp <?php echo e(number_format($trx['masuk'], 0, ',', '.')); ?></span>
                    <?php else: ?>
                    <span class="text-sm font-bold text-red-500">-Rp <?php echo e(number_format($trx['keluar'], 0, ',', '.')); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <p class="text-sm text-gray-800"><?php echo e($trx['keterangan'] ?? '-'); ?></p>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        
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
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $detail['transaksi']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="px-3 py-2 text-gray-600"><?php echo e(\Carbon\Carbon::parse($trx['tanggal'])->format('d/m/Y')); ?></td>
                        <td class="px-3 py-2 text-gray-800"><?php echo e($trx['keterangan'] ?? '-'); ?></td>
                        <td class="px-3 py-2 text-right text-emerald-600"><?php echo e($trx['masuk'] > 0 ? 'Rp '.number_format($trx['masuk'], 0, ',', '.') : ''); ?></td>
                        <td class="px-3 py-2 text-right text-red-500"><?php echo e($trx['keluar'] > 0 ? 'Rp '.number_format($trx['keluar'], 0, ',', '.') : ''); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php else: ?>
<div class="card text-center py-14">
    <p class="text-gray-400 text-sm">Pilih bulan dan tahun lalu klik <strong>Tampilkan</strong>.</p>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div><?php /**PATH /srv/apkpulsa/web/mushola-finance/resources/views/livewire/laporan/bulanan.blade.php ENDPATH**/ ?>