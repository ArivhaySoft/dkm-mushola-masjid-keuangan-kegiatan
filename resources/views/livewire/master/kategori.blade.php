<?php

use App\Models\Kategori;
use App\Models\Keuangan;
use App\Models\Rekening;
use Livewire\Volt\Component;

new class extends Component
{
    public function render(): mixed
    {
        return parent::render()->title('Master Kategori');
    }

    public bool   $showModal     = false;
    public bool   $confirmDelete = false;
    public ?int   $editId        = null;
    public ?int   $deleteId      = null;

    public string $nama       = '';
    public string $saldo_awal = '0';
    public string $id_rekening_saldo_awal = '';
    public string $tanggal_saldo_awal = '';

    public function with(): array
    {
        return [
            'kategori' => Kategori::orderBy('id')->get(),
            'rekening' => Rekening::orderBy('nama_rek')->get(),
        ];
    }

    public function openCreate(): void
    {
        $this->reset(['editId', 'nama']);
        $this->saldo_awal = '0';
        $this->id_rekening_saldo_awal = (string) (Rekening::orderBy('id')->value('id') ?? '');
        $this->tanggal_saldo_awal = now()->format('Y-m-d');
        $this->showModal  = true;
    }

    public function openEdit(int $id): void
    {
        $k = Kategori::findOrFail($id);
        $this->editId     = $id;
        $this->nama       = $k->nama;
        $this->saldo_awal = $k->saldo_awal;
        $this->id_rekening_saldo_awal = '';
        $this->tanggal_saldo_awal = '';
        $this->showModal  = true;
    }

    public function save(): void
    {
        $this->saldo_awal = preg_replace('/[^0-9]/', '', (string) $this->saldo_awal) ?: '0';

        $this->validate([
            'nama'       => 'required|string|max:100',
            'saldo_awal' => 'required|numeric|min:0',
            'id_rekening_saldo_awal' => 'nullable|exists:rekening,id',
            'tanggal_saldo_awal' => 'nullable|date',
        ]);

        if (!$this->editId && (float) $this->saldo_awal > 0 && !$this->id_rekening_saldo_awal) {
            $this->addError('id_rekening_saldo_awal', 'Pilih rekening tujuan saldo awal.');
            return;
        }

        if (!$this->editId && (float) $this->saldo_awal > 0 && !$this->tanggal_saldo_awal) {
            $this->addError('tanggal_saldo_awal', 'Pilih tanggal beginning/saldo awal.');
            return;
        }

        $data = ['nama' => $this->nama, 'saldo_awal' => $this->saldo_awal];

        if ($this->editId) {
            $k = Kategori::findOrFail($this->editId);
            $k->update($data);
            $k->recalculate();
            session()->flash('success', 'Kategori berhasil diperbarui.');
        } else {
            $k = Kategori::create(array_merge($data, ['masuk' => 0, 'keluar' => 0, 'saldo_akhir' => $this->saldo_awal]));

            if ((float) $this->saldo_awal > 0) {
                Keuangan::create([
                    'masuk'       => $this->saldo_awal,
                    'keluar'      => 0,
                    'keterangan'  => '__SALDO_AWAL__ Saldo awal kategori: ' . $k->nama,
                    'id_rekening' => (int) $this->id_rekening_saldo_awal,
                    'id_kategori' => $k->id,
                    'created_by'  => auth()->id(),
                    'tanggal'     => $this->tanggal_saldo_awal,
                ]);
            }

            session()->flash('success', 'Kategori berhasil ditambahkan dan saldo awal dibukukan ke rekening terpilih.');
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
            Kategori::findOrFail($this->deleteId)->delete();
            session()->flash('success', 'Kategori berhasil dihapus.');
        } catch (\Exception $e) {
            session()->flash('error', 'Kategori tidak bisa dihapus karena masih memiliki transaksi.');
        }
        $this->confirmDelete = false;
        $this->deleteId      = null;
    }
}; ?>

<div>
<div class="flex justify-between items-center mb-4">
    <h2 class="text-base font-bold text-gray-700">Daftar Kategori Kas</h2>
    @if(auth()->user()?->isAdmin())
    <button wire:click="openCreate" class="btn-primary">+ Tambah Kategori</button>
    @endif
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($kategori as $k)
    <div class="card">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                </svg>
            </div>
            @if(auth()->user()?->isAdmin())
            <div class="flex gap-1">
                <button wire:click="openEdit({{ $k->id }})"
                        class="p-1.5 hover:bg-blue-100 text-blue-600 rounded-lg transition text-xs">Edit</button>
                <button wire:click="confirmDeleteItem({{ $k->id }})"
                        class="p-1.5 hover:bg-red-100 text-red-500 rounded-lg transition text-xs">Hapus</button>
            </div>
            @endif
        </div>
        <h3 class="font-bold text-gray-800 mb-3">{{ $k->nama }}</h3>
        <div class="space-y-1.5 text-xs">
            <div class="flex justify-between">
                <span class="text-gray-500">Saldo Awal</span>
                <span class="font-medium text-gray-700">Rp {{ number_format($k->saldo_awal, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Total Masuk</span>
                <span class="font-semibold text-emerald-600">Rp {{ number_format($k->masuk, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Total Keluar</span>
                <span class="font-semibold text-red-500">Rp {{ number_format($k->keluar, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between border-t pt-1.5 mt-1.5">
                <span class="font-bold text-gray-700">Saldo Akhir</span>
                <span class="font-bold text-blue-600">Rp {{ number_format($k->saldo_akhir, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Modal --}}
@if($showModal)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <h3 class="font-bold text-gray-800">{{ $editId ? 'Edit' : 'Tambah' }} Kategori</h3>
            <button wire:click="$set('showModal', false)" class="p-1.5 hover:bg-gray-100 rounded-lg">
                <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-5 space-y-4">
            <div>
                <label class="label">Nama Kategori</label>
                <input type="text" wire:model="nama" class="input" placeholder="Contoh: Kas Utama" />
                @error('nama') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Saldo Awal (Rp)</label>
                <input type="text" wire:model="saldo_awal" class="input" inputmode="numeric"
                       @input="$event.target.value = ($event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'))"
                       placeholder="0" />
                @error('saldo_awal') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-gray-400 mt-1">Saldo sebelum sistem ini digunakan</p>
            </div>
            @if(!$editId)
            <div>
                <label class="label">Tanggal Beginning Saldo Awal</label>
                <input type="date" wire:model="tanggal_saldo_awal" class="input" />
                @error('tanggal_saldo_awal') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-gray-400 mt-1">Tanggal ini dipakai untuk transaksi pembukaan saldo awal.</p>
            </div>
            <div>
                <label class="label">Rekening Tujuan Saldo Awal</label>
                <select wire:model="id_rekening_saldo_awal" class="input">
                    <option value="">-- Pilih Rekening --</option>
                    @foreach($rekening as $rek)
                    <option value="{{ $rek->id }}">{{ $rek->nama_rek }} ({{ $rek->no_rek }})</option>
                    @endforeach
                </select>
                @error('id_rekening_saldo_awal') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-gray-400 mt-1">Jika saldo awal > 0, nominal akan dibukukan ke rekening ini sebagai transaksi pembukaan.</p>
            </div>
            @endif
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
        <h3 class="font-bold text-gray-800 mb-2">Hapus Kategori?</h3>
        <p class="text-sm text-gray-500 mb-5">Kategori yang masih memiliki transaksi tidak bisa dihapus.</p>
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

