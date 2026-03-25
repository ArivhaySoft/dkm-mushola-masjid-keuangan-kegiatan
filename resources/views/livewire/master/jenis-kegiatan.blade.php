<?php

use App\Models\JenisKegiatan;
use Livewire\Volt\Component;

new class extends Component
{
    public function render(): mixed
    {
        return parent::render()->title('Master Jenis Kegiatan');
    }

    public bool   $showModal     = false;
    public bool   $confirmDelete = false;
    public ?int   $editId        = null;
    public ?int   $deleteId      = null;

    public string $nama  = '';
    public string $warna = 'gray';

    public array $warnaOptions = [
        'primary' => 'Hijau (Primary)',
        'yellow'  => 'Kuning',
        'blue'    => 'Biru',
        'red'     => 'Merah',
        'purple'  => 'Ungu',
        'pink'    => 'Pink',
        'orange'  => 'Oranye',
        'gray'    => 'Abu-abu',
    ];

    public function with(): array
    {
        return ['data' => JenisKegiatan::orderBy('nama')->get()];
    }

    public function openCreate(): void
    {
        $this->reset(['editId', 'nama', 'warna']);
        $this->warna = 'gray';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $j = JenisKegiatan::findOrFail($id);
        $this->editId = $id;
        $this->nama   = $j->nama;
        $this->warna  = $j->warna;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'nama'  => 'required|string|max:100|unique:jenis_kegiatan,nama' . ($this->editId ? ",{$this->editId}" : ''),
            'warna' => 'required|string|max:50',
        ]);

        $data = ['nama' => $this->nama, 'warna' => $this->warna];

        if ($this->editId) {
            $old = JenisKegiatan::findOrFail($this->editId);
            $oldNama = $old->nama;
            $old->update($data);
            // Update kegiatan yang pakai nama lama
            if ($oldNama !== $this->nama) {
                \App\Models\Kegiatan::where('jenis', $oldNama)->update(['jenis' => $this->nama]);
            }
            session()->flash('success', 'Jenis kegiatan berhasil diperbarui.');
        } else {
            JenisKegiatan::create($data);
            session()->flash('success', 'Jenis kegiatan berhasil ditambahkan.');
        }
        $this->showModal = false;
    }

    public function confirmDeleteItem(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteItem(): void
    {
        $jenis = JenisKegiatan::findOrFail($this->deleteId);
        $count = \App\Models\Kegiatan::where('jenis', $jenis->nama)->count();
        if ($count > 0) {
            session()->flash('error', "Jenis \"{$jenis->nama}\" tidak bisa dihapus karena masih digunakan {$count} kegiatan.");
        } else {
            $jenis->delete();
            session()->flash('success', 'Jenis kegiatan berhasil dihapus.');
        }
        $this->confirmDelete = false;
        $this->deleteId      = null;
    }

    public function getBadgeClasses(string $warna): string
    {
        return match($warna) {
            'primary' => 'bg-primary-100 text-primary-700',
            'yellow'  => 'bg-yellow-100 text-yellow-700',
            'blue'    => 'bg-blue-100 text-blue-700',
            'red'     => 'bg-red-100 text-red-700',
            'purple'  => 'bg-purple-100 text-purple-700',
            'pink'    => 'bg-pink-100 text-pink-700',
            'orange'  => 'bg-orange-100 text-orange-700',
            default   => 'bg-gray-100 text-gray-600',
        };
    }
}; ?>

<div>
@if(session('success'))
<div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl px-4 py-3"
     x-data x-init="setTimeout(() => $el.remove(), 4000)">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3"
     x-data x-init="setTimeout(() => $el.remove(), 5000)">⚠ {{ session('error') }}</div>
@endif

<div class="flex justify-between items-center mb-4">
    <h2 class="text-base font-bold text-gray-700">Daftar Jenis Kegiatan</h2>
    @if(auth()->user()?->isAdmin() || auth()->user()?->isEditor())
    <button wire:click="openCreate" class="btn-primary">+ Tambah Jenis</button>
    @endif
</div>

<div class="card overflow-hidden p-0">
    {{-- Mobile --}}
    <div class="sm:hidden divide-y divide-gray-100">
        @forelse($data as $j)
        <div class="px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $this->getBadgeClasses($j->warna) }}">
                        {{ $j->nama }}
                    </span>
                    <span class="text-[11px] text-gray-400">{{ $j->kegiatan()->count() }} kegiatan</span>
                </div>
                @if(auth()->user()?->isAdmin() || auth()->user()?->isEditor())
                <div class="flex items-center gap-1">
                    <button wire:click="openEdit({{ $j->id }})" class="p-1 hover:bg-blue-100 text-blue-600 rounded-lg transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button wire:click="confirmDeleteItem({{ $j->id }})" class="p-1 hover:bg-red-100 text-red-500 rounded-lg transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="px-4 py-12 text-center text-gray-400">Belum ada jenis kegiatan</div>
        @endforelse
    </div>
    {{-- Desktop --}}
    <div class="hidden sm:block">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">Nama Jenis</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">Warna Badge</th>
                    <th class="text-center px-4 py-3 text-xs font-bold text-gray-500">Jumlah Kegiatan</th>
                    @if(auth()->user()?->isAdmin() || auth()->user()?->isEditor())
                    <th class="text-center px-4 py-3 text-xs font-bold text-gray-500">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($data as $j)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $j->nama }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $this->getBadgeClasses($j->warna) }}">
                            {{ $warnaOptions[$j->warna] ?? $j->warna }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ $j->kegiatan()->count() }}</td>
                    @if(auth()->user()?->isAdmin() || auth()->user()?->isEditor())
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center gap-1">
                            <button wire:click="openEdit({{ $j->id }})"
                                    class="p-1.5 hover:bg-blue-100 text-blue-600 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button wire:click="confirmDeleteItem({{ $j->id }})"
                                    class="p-1.5 hover:bg-red-100 text-red-500 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                    @endif
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-12 text-center text-gray-400">Belum ada jenis kegiatan</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal --}}
@if($showModal)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <h3 class="font-bold text-gray-800">{{ $editId ? 'Edit' : 'Tambah' }} Jenis Kegiatan</h3>
            <button wire:click="$set('showModal', false)" class="p-1.5 hover:bg-gray-100 rounded-lg">
                <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-5 space-y-4">
            <div>
                <label class="label">Nama Jenis</label>
                <input type="text" wire:model="nama" class="input" placeholder="Contoh: Pengajian" />
                @error('nama') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Warna Badge</label>
                <select wire:model="warna" class="input">
                    @foreach($warnaOptions as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <div class="mt-2">
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $this->getBadgeClasses($warna) }}">
                        {{ $nama ?: 'Preview' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="px-5 pb-5 flex gap-2">
            <button wire:click="$set('showModal', false)" class="btn-secondary flex-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Batal
            </button>
            <button wire:click="save" class="btn-primary flex-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan
            </button>
        </div>
    </div>
</div>
@endif

{{-- Confirm Delete --}}
@if($confirmDelete)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-2xl w-full max-w-sm p-6 shadow-2xl text-center">
        <h3 class="font-bold text-gray-800 mb-2">Hapus Jenis Kegiatan?</h3>
        <p class="text-sm text-gray-500 mb-5">Jenis yang masih digunakan kegiatan tidak bisa dihapus.</p>
        <div class="flex gap-3">
            <button wire:click="$set('confirmDelete', false)" class="btn-secondary flex-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Batal
            </button>
            <button wire:click="deleteItem" class="btn-danger flex-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Hapus
            </button>
        </div>
    </div>
</div>
@endif

</div>
