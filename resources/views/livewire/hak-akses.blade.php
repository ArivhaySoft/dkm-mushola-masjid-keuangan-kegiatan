<?php

use App\Models\Role;
use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public function render(): mixed
    {
        return parent::render()->title('Hak Akses');
    }

    public string $search = '';
    public bool   $showModal = false;
    public ?int   $userId    = null;
    public array  $selectedRoles = [];

    public function with(): array
    {
        return [
            'users' => User::with('roles')
                ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%"))
                ->orderBy('name')->paginate(15),
            'roles' => Role::all(),
        ];
    }

    public function openRoles(int $id): void
    {
        $this->userId       = $id;
        $user               = User::findOrFail($id);
        $this->selectedRoles = $user->roles->pluck('id')->map(fn($v) => (string)$v)->toArray();
        $this->showModal    = true;
    }

    public function saveRoles(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        $user = User::findOrFail($this->userId);
        $user->roles()->sync($this->selectedRoles);
        session()->flash('success', 'Hak akses berhasil diperbarui.');
        $this->showModal = false;
    }
}; ?>

<div>
@if(!auth()->user()?->isAdmin())
<div class="card text-center py-14">
    <svg class="w-14 h-14 text-gray-200 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
    </svg>
    <p class="text-gray-500 text-sm">Hanya administrator yang dapat mengakses halaman ini.</p>
</div>
@else

@if(session('success'))
<div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl px-4 py-3"
     x-data x-init="setTimeout(() => $el.remove(), 4000)">
    ✓ {{ session('success') }}
</div>
@endif

<div class="mb-4">
    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari pengguna..."
           class="input max-w-sm" />
</div>

<div class="card overflow-hidden p-0">
    {{-- Mobile --}}
    <div class="sm:hidden divide-y divide-gray-100">
        @forelse($users as $user)
        <div class="px-4 py-3 space-y-1.5">
            <div class="flex items-center gap-3">
                @if($user->avatar)
                    <img src="{{ $user->avatar }}" class="w-8 h-8 rounded-full" />
                @else
                    <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 text-sm">{{ $user->name }}</p>
                    <p class="text-[11px] text-gray-400 truncate">{{ $user->email }}</p>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex flex-wrap gap-1">
                    @forelse($user->roles as $role)
                    <span class="inline-flex px-1.5 py-0.5 rounded-full text-[11px] font-semibold
                        {{ $role->name === 'admin' ? 'bg-red-100 text-red-700' :
                           ($role->name === 'bendahara' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                        {{ $role->label }}
                    </span>
                    @empty
                    <span class="text-[11px] text-gray-400">Tidak ada</span>
                    @endforelse
                </div>
                <button wire:click="openRoles({{ $user->id }})"
                        class="text-xs text-primary-600 font-semibold">
                    Atur Peran
                </button>
            </div>
        </div>
        @empty
        <div class="px-4 py-10 text-center text-gray-400">Tidak ada pengguna</div>
        @endforelse
    </div>
    {{-- Desktop --}}
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
                @forelse($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if($user->avatar)
                                <img src="{{ $user->avatar }}" class="w-8 h-8 rounded-full" />
                            @else
                                <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                            <span class="font-medium text-gray-800">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-1">
                            @forelse($user->roles as $role)
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $role->name === 'admin' ? 'bg-red-100 text-red-700' :
                                   ($role->name === 'bendahara' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                                {{ $role->label }}
                            </span>
                            @empty
                            <span class="text-xs text-gray-400">Tidak ada</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <button wire:click="openRoles({{ $user->id }})"
                                class="text-xs text-primary-600 hover:text-primary-800 font-semibold hover:underline">
                            Atur Peran
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-10 text-center text-gray-400">Tidak ada pengguna</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t">{{ $users->links() }}</div>
</div>

{{-- Modal --}}
@if($showModal)
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
            @foreach($roles as $role)
            <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50">
                <input type="checkbox" wire:model="selectedRoles" value="{{ $role->id }}"
                       class="w-4 h-4 text-primary-600 rounded" />
                <div>
                    <p class="text-sm font-semibold text-gray-800">{{ $role->label }}</p>
                    <p class="text-xs text-gray-400">{{ $role->name }}</p>
                </div>
            </label>
            @endforeach
        </div>
        <div class="px-5 pb-5 flex gap-2">
            <button wire:click="$set('showModal', false)" class="btn-secondary flex-1">Batal</button>
            <button wire:click="saveRoles" class="btn-primary flex-1">Simpan</button>
        </div>
    </div>
</div>
@endif

@endif

</div>
