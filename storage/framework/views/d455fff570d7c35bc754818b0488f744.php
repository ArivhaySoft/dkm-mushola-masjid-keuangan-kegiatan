<?php

use App\Models\Role;
use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;

?>

<div>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!auth()->user()?->isAdmin()): ?>
<div class="card text-center py-14">
    <svg class="w-14 h-14 text-gray-200 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
    </svg>
    <p class="text-gray-500 text-sm">Hanya administrator yang dapat mengakses halaman ini.</p>
</div>
<?php else: ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
<div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl px-4 py-3"
     x-data x-init="setTimeout(() => $el.remove(), 4000)">
    ✓ <?php echo e(session('success')); ?>

</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<div class="mb-4">
    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari pengguna..."
           class="input max-w-sm" />
</div>

<div class="card overflow-hidden p-0">
    
    <div class="sm:hidden divide-y divide-gray-100">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="px-4 py-3 space-y-1.5">
            <div class="flex items-center gap-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->avatar): ?>
                    <img src="<?php echo e($user->avatar); ?>" class="w-8 h-8 rounded-full" />
                <?php else: ?>
                    <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">
                        <?php echo e(strtoupper(substr($user->name, 0, 1))); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 text-sm"><?php echo e($user->name); ?></p>
                    <p class="text-[11px] text-gray-400 truncate"><?php echo e($user->email); ?></p>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex flex-wrap gap-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_2 = true; $__currentLoopData = $user->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                    <span class="inline-flex px-1.5 py-0.5 rounded-full text-[11px] font-semibold
                        <?php echo e($role->name === 'admin' ? 'bg-red-100 text-red-700' :
                           ($role->name === 'bendahara' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600')); ?>">
                        <?php echo e($role->label); ?>

                    </span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                    <span class="text-[11px] text-gray-400">Tidak ada</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <button wire:click="openRoles(<?php echo e($user->id); ?>)"
                        class="text-xs text-primary-600 font-semibold">
                    Atur Peran
                </button>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="px-4 py-10 text-center text-gray-400">Tidak ada pengguna</div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    
    <div class="hidden sm:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase">Pengguna</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase">Email</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase">Peran</th>
                    <th class="text-center px-4 py-3 text-xs font-bold text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->avatar): ?>
                                <img src="<?php echo e($user->avatar); ?>" class="w-8 h-8 rounded-full" />
                            <?php else: ?>
                                <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">
                                    <?php echo e(strtoupper(substr($user->name, 0, 1))); ?>

                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <span class="font-medium text-gray-800"><?php echo e($user->name); ?></span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600"><?php echo e($user->email); ?></td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-1">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_2 = true; $__currentLoopData = $user->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                                <?php echo e($role->name === 'admin' ? 'bg-red-100 text-red-700' :
                                   ($role->name === 'bendahara' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600')); ?>">
                                <?php echo e($role->label); ?>

                            </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                            <span class="text-xs text-gray-400">Tidak ada</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <button wire:click="openRoles(<?php echo e($user->id); ?>)"
                                class="text-xs text-primary-600 hover:text-primary-800 font-semibold hover:underline">
                            Atur Peran
                        </button>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="4" class="px-4 py-10 text-center text-gray-400">Tidak ada pengguna</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t"><?php echo e($users->links()); ?></div>
</div>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal): ?>
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <h3 class="text-base font-bold">Atur Peran Pengguna</h3>
            <button wire:click="$set('showModal', false)" class="p-1.5 hover:bg-gray-100 rounded-lg">
                <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-5 space-y-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50">
                <input type="checkbox" wire:model="selectedRoles" value="<?php echo e($role->id); ?>"
                       class="w-4 h-4 text-primary-600 rounded" />
                <div>
                    <p class="text-sm font-semibold text-gray-800"><?php echo e($role->label); ?></p>
                    <p class="text-xs text-gray-400"><?php echo e($role->name); ?></p>
                </div>
            </label>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div class="px-5 pb-5 flex gap-2">
            <button wire:click="$set('showModal', false)" class="btn-secondary flex-1">Batal</button>
            <button wire:click="saveRoles" class="btn-primary flex-1">Simpan</button>
        </div>
    </div>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div><?php /**PATH /srv/apkpulsa/web/mushola-finance/resources/views/livewire/hak-akses.blade.php ENDPATH**/ ?>