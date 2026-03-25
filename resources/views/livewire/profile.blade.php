<?php

use App\Models\Keuangan;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public function render(): mixed
    {
        return parent::render()->title('Profil');
    }

    public string $name = '';
    public $photo = null;
    public bool $editing = false;

    public function mount(): void
    {
        $this->name = auth()->user()->name;
    }

    public function startEdit(): void
    {
        $this->name = auth()->user()->name;
        $this->photo = null;
        $this->editing = true;
    }

    public function cancelEdit(): void
    {
        $this->editing = false;
        $this->photo = null;
        $this->resetValidation();
    }

    public function saveProfile(): void
    {
        $this->validate([
            'name'  => 'required|string|max:100',
            'photo' => 'nullable|image|max:2048',
        ]);

        $user = auth()->user();
        $user->name = $this->name;

        if ($this->photo) {
            $path = $this->photo->store('avatars', 'public');
            $user->avatar = '/storage/' . $path;
        }

        $user->save();
        $this->editing = false;
        $this->photo = null;
        session()->flash('success', 'Profil berhasil diperbarui.');
    }

    public function with(): array
    {
        $user = auth()->user();
        $totalInput  = Keuangan::where('created_by', $user->id)->count();
        $totalMasuk  = Keuangan::where('created_by', $user->id)->sum('masuk');
        $totalKeluar = Keuangan::where('created_by', $user->id)->sum('keluar');

        return compact('user', 'totalInput', 'totalMasuk', 'totalKeluar');
    }
}; ?>
<div>
<div class="max-w-xl mx-auto">
    @if(session('success'))
    <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl px-4 py-3"
         x-data x-init="setTimeout(() => $el.remove(), 4000)">
        ✓ {{ session('success') }}
    </div>
    @endif

    <div class="card text-center mb-4">
        @if($editing)
        {{-- Edit Mode --}}
        <div class="mb-4">
            @if($photo)
                <img src="{{ $photo->temporaryUrl() }}" class="w-24 h-24 rounded-full mx-auto mb-2 ring-4 ring-primary-100 object-cover" />
            @elseif($user->avatar)
                <img src="{{ $user->avatar }}" class="w-24 h-24 rounded-full mx-auto mb-2 ring-4 ring-primary-100 object-cover" />
            @else
                <div class="w-24 h-24 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-3xl font-bold mx-auto mb-2">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif
            <label class="inline-flex items-center gap-1.5 text-xs text-primary-600 font-semibold cursor-pointer hover:text-primary-700">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Ganti Foto
                <input type="file" wire:model="photo" accept="image/*" class="hidden" />
            </label>
            @error('photo') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="max-w-xs mx-auto space-y-3">
            <div>
                <label class="label">Nama</label>
                <input type="text" wire:model="name" class="input text-center" />
                @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-2">
                <button wire:click="cancelEdit" class="btn-secondary flex-1">Batal</button>
                <button wire:click="saveProfile" class="btn-primary flex-1">
                    <span wire:loading.remove wire:target="saveProfile">Simpan</span>
                    <span wire:loading wire:target="saveProfile">Menyimpan...</span>
                </button>
            </div>
        </div>
        @else
        {{-- View Mode --}}
        @if($user->avatar)
        <img src="{{ $user->avatar }}" class="w-24 h-24 rounded-full mx-auto mb-4 ring-4 ring-primary-100 object-cover" />
        @else
        <div class="w-24 h-24 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-3xl font-bold mx-auto mb-4">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        @endif
        <h2 class="text-xl font-extrabold text-gray-800">{{ $user->name }}</h2>
        <p class="text-sm text-gray-500 mb-3">{{ $user->email }}</p>
        <div class="flex flex-wrap justify-center gap-2 mb-3">
            @foreach($user->roles as $role)
            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold
                {{ $role->name === 'admin' ? 'bg-red-100 text-red-700' :
                   ($role->name === 'bendahara' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                {{ $role->label }}
            </span>
            @endforeach
        </div>
        <button wire:click="startEdit" class="btn-secondary text-xs">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit Profil
        </button>
        @endif
    </div>

    <div class="grid grid-cols-3 gap-3">
        <div class="card text-center">
            <p class="text-2xl font-extrabold text-primary-600">{{ $totalInput }}</p>
            <p class="text-xs text-gray-500 mt-1">Input Transaksi</p>
        </div>
        <div class="card text-center">
            <p class="text-lg font-extrabold text-emerald-600">{{ number_format($totalMasuk/1000) }}K</p>
            <p class="text-xs text-gray-500 mt-1">Total Masuk</p>
        </div>
        <div class="card text-center">
            <p class="text-lg font-extrabold text-red-500">{{ number_format($totalKeluar/1000) }}K</p>
            <p class="text-xs text-gray-500 mt-1">Total Keluar</p>
        </div>
    </div>

    <div class="card mt-4">
        <h3 class="text-sm font-bold text-gray-700 mb-3">Informasi Akun</h3>
        <div class="space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Nama</span>
                <span class="font-medium text-gray-800">{{ $user->name }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Email</span>
                <span class="font-medium text-gray-800">{{ $user->email }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Login via</span>
                <span class="font-medium text-gray-800">Google</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Bergabung</span>
                <span class="font-medium text-gray-800">{{ $user->created_at->isoFormat('D MMMM Y') }}</span>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button class="btn-danger w-full justify-center">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Keluar
        </button>
    </form>
</div>

</div>
