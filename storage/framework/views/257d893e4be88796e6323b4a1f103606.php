<?php

use App\Models\Setting;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
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

    
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-blue-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="font-bold text-blue-800">Pengaturan Aplikasi</p>
                <p class="text-sm text-blue-700 mt-1">Sesuaikan nama dan identitas organisasi Anda. Perubahan akan langsung terlihat di seluruh aplikasi.</p>
            </div>
        </div>
    </div>

    <form wire:submit="save">
        
        <div class="card space-y-5">
            <h3 class="text-base font-bold text-gray-800">Identitas Organisasi</h3>

            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Aplikasi</label>
                <input type="text" wire:model="app_name" class="input" placeholder="Contoh: Keuangan Mushola">
                <p class="text-xs text-gray-400 mt-1">Ditampilkan di header, sidebar, dan halaman login.</p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['app_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Mushola / Organisasi</label>
                <input type="text" wire:model="nama_mushola" class="input" placeholder="Contoh: Fajrul Iman">
                <p class="text-xs text-gray-400 mt-1">Ditampilkan di bawah nama aplikasi pada sidebar.</p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['nama_mushola'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Logo / Foto Mushola</label>
                <div class="flex items-start gap-4">
                    
                    <div class="flex-shrink-0">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($foto_mushola): ?>
                            <img src="<?php echo e($foto_mushola->temporaryUrl()); ?>" class="w-20 h-20 rounded-2xl object-cover border-2 border-primary-200 shadow">
                        <?php elseif($current_foto): ?>
                            <img src="<?php echo e(Storage::url($current_foto)); ?>" class="w-20 h-20 rounded-2xl object-cover border-2 border-primary-200 shadow">
                        <?php else: ?>
                            <div class="w-20 h-20 rounded-2xl bg-gray-100 flex items-center justify-center border-2 border-dashed border-gray-300">
                                <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="flex-1 space-y-2">
                        <input type="file" wire:model="foto_mushola" accept="image/*"
                               class="block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer">
                        <p class="text-xs text-gray-400">Format: JPG, PNG, WebP. Maksimal 2MB.</p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($current_foto): ?>
                        <button type="button" wire:click="removeFoto" wire:confirm="Hapus foto ini?" class="text-xs text-red-500 hover:text-red-700 font-medium">
                            Hapus foto
                        </button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['foto_mushola'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="mt-5">
            <button type="submit" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Pengaturan
            </button>
        </div>
    </form>

</div>
</div><?php /**PATH /srv/apkpulsa/web/mushola-finance/resources/views/livewire/pengaturan/index.blade.php ENDPATH**/ ?>