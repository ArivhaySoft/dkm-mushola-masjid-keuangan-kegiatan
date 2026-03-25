<?php

use App\Models\Rekening;
use Livewire\Volt\Component;

new class extends Component
{
    public function render(): mixed
    {
        return parent::render()->title('Master Rekening');
    }

    public bool   $showModal     = false;
    public bool   $confirmDelete = false;
    public ?int   $editId        = null;
    public ?int   $deleteId      = null;

    public string $nama_rek  = '';
    public string $atas_nama = '';
    public string $no_rek    = '';

    public function with(): array
    {
        return ['rekening' => Rekening::orderBy('id')->get()];
    }

    public function openCreate(): void
    {
        $this->reset(['editId', 'nama_rek', 'atas_nama', 'no_rek']);
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $r = Rekening::findOrFail($id);
        $this->editId    = $id;
        $this->nama_rek  = $r->nama_rek;
        $this->atas_nama = $r->atas_nama;
        $this->no_rek    = $r->no_rek;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'nama_rek'  => 'required|string|max:100',
            'atas_nama' => 'required|string|max:100',
            'no_rek'    => 'required|string|max:50',
        ]);

        $data = ['nama_rek' => $this->nama_rek, 'atas_nama' => $this->atas_nama, 'no_rek' => $this->no_rek];

        if ($this->editId) {
            Rekening::findOrFail($this->editId)->update($data);
            session()->flash('success', 'Rekening berhasil diperbarui.');
        } else {
            Rekening::create($data);
            session()->flash('success', 'Rekening berhasil ditambahkan.');
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
        try {
            Rekening::findOrFail($this->deleteId)->delete();
            session()->flash('success', 'Rekening berhasil dihapus.');
        } catch (\Exception $e) {
            session()->flash('error', 'Rekening tidak bisa dihapus karena masih memiliki transaksi.');
        }
        $this->confirmDelete = false;
        $this->deleteId      = null;
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
    <h2 class="text-base font-bold text-gray-700">Daftar Rekening</h2>
    @if(auth()->user()?->isAdmin())
    <button wire:click="openCreate" class="btn-primary">+ Tambah Rekening</button>
    @endif
</div>

<div class="card overflow-hidden p-0">
    {{-- Mobile --}}
    <div class="sm:hidden divide-y divide-gray-100">
        @foreach($rekening as $r)
        <div class="px-4 py-3 space-y-1">
            <div class="flex items-start justify-between">
                <div>
                    <p class="font-semibold text-gray-800 text-sm">{{ $r->nama_rek }}</p>
                    <p class="text-[11px] text-gray-400">{{ $r->atas_nama }} &middot; {{ $r->no_rek }}</p>
                </div>
                <span class="text-sm font-bold {{ $r->saldo >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                    Rp {{ number_format($r->saldo, 0, ',', '.') }}
                </span>
            </div>
            @if(auth()->user()?->isAdmin())
            <div class="flex items-center gap-1">
                <button wire:click="openEdit({{ $r->id }})" class="p-1 hover:bg-blue-100 text-blue-600 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button wire:click="confirmDeleteItem({{ $r->id }})" class="p-1 hover:bg-red-100 text-red-500 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    {{-- Desktop --}}
    <div class="hidden sm:block">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">Nama Rekening</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">Atas Nama</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">No. Rekening</th>
                    <th class="text-right px-4 py-3 text-xs font-bold text-gray-500">Saldo</th>
                    @if(auth()->user()?->isAdmin())
                    <th class="text-center px-4 py-3 text-xs font-bold text-gray-500">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($rekening as $r)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $r->nama_rek }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $r->atas_nama }}</td>
                    <td class="px-4 py-3 text-gray-600 font-mono">{{ $r->no_rek }}</td>
                    <td class="px-4 py-3 text-right font-bold {{ $r->saldo >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                        Rp {{ number_format($r->saldo, 0, ',', '.') }}
                    </td>
                    @if(auth()->user()?->isAdmin())
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center gap-1">
                            <button wire:click="openEdit({{ $r->id }})"
                                    class="p-1.5 hover:bg-blue-100 text-blue-600 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button wire:click="confirmDeleteItem({{ $r->id }})"
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
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Modal --}}
@if($showModal)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <h3 class="font-bold text-gray-800">{{ $editId ? 'Edit' : 'Tambah' }} Rekening</h3>
            <button wire:click="$set('showModal', false)" class="p-1.5 hover:bg-gray-100 rounded-lg">
                <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-5 space-y-4">
            <div>
                <label class="label">Nama Rekening</label>
                <input type="text" wire:model="nama_rek" class="input" placeholder="Contoh: Bank BSI" />
                @error('nama_rek') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Atas Nama</label>
                <input type="text" wire:model="atas_nama" class="input" placeholder="Nama pemilik rekening" />
                @error('atas_nama') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">No. Rekening</label>
                <input type="text" wire:model="no_rek" class="input" placeholder="Nomor rekening" />
                @error('no_rek') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
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
        <h3 class="font-bold text-gray-800 mb-2">Hapus Rekening?</h3>
        <p class="text-sm text-gray-500 mb-5">Rekening yang masih memiliki transaksi tidak bisa dihapus.</p>
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

