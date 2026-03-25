<?php

use App\Models\Kategori;
use App\Models\Rekening;
use App\Models\TransferRekening;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public function render(): mixed
    {
        return parent::render()->title('Riwayat Transfer Rekening');
    }

    public string $filterFrom = '';
    public string $filterTo   = '';

    // Edit form
    public bool $showEdit     = false;
    public ?int $editId       = null;
    public string $tr_dari    = '';
    public string $tr_ke      = '';
    public string $tr_kat     = '';
    public string $tr_jumlah  = '';
    public string $tr_ket     = '';
    public string $tr_tanggal = '';

    // Delete confirm
    public bool $showDelete   = false;
    public ?int $deleteId     = null;

    public function with(): array
    {
        $transfers = TransferRekening::with(['dariRekening', 'keRekening', 'kategori', 'creator'])
            ->when($this->filterFrom, fn($q) => $q->whereDate('tanggal', '>=', $this->filterFrom))
            ->when($this->filterTo,   fn($q) => $q->whereDate('tanggal', '<=', $this->filterTo))
            ->orderBy('tanggal', 'desc')
            ->paginate(15);

        $rekening = Rekening::orderBy('nama_rek')->get();
        $kategori = Kategori::orderBy('nama')->get();

        return compact('transfers', 'rekening', 'kategori');
    }

    public function editTransfer(int $id): void
    {
        $tr = TransferRekening::findOrFail($id);
        $this->editId       = $tr->id;
        $this->tr_dari      = (string) $tr->dari_rekening;
        $this->tr_ke        = (string) $tr->ke_rekening;
        $this->tr_kat       = (string) $tr->id_kategori;
        $this->tr_jumlah    = number_format($tr->jumlah, 0, '', '.');
        $this->tr_ket       = $tr->keterangan ?? '';
        $this->tr_tanggal   = $tr->tanggal->format('Y-m-d');
        $this->showEdit     = true;
    }

    public function updateTransfer(): void
    {
        $this->tr_jumlah = preg_replace('/[^0-9]/', '', (string) $this->tr_jumlah) ?: '0';

        $this->validate([
            'tr_dari'    => 'required|exists:rekening,id',
            'tr_ke'      => 'required|exists:rekening,id|different:tr_dari',
            'tr_kat'     => 'required|exists:kategori,id',
            'tr_jumlah'  => 'required|numeric|min:1',
            'tr_tanggal' => 'required|date',
        ], [
            'tr_ke.different' => 'Rekening tujuan harus berbeda dengan rekening asal.',
        ]);

        $tr = TransferRekening::findOrFail($this->editId);
        $tr->update([
            'dari_rekening' => $this->tr_dari,
            'ke_rekening'   => $this->tr_ke,
            'id_kategori'   => $this->tr_kat,
            'jumlah'        => $this->tr_jumlah,
            'keterangan'    => $this->tr_ket ?: null,
            'tanggal'       => $this->tr_tanggal,
        ]);

        session()->flash('success', 'Transfer rekening berhasil diperbarui.');
        $this->showEdit = false;
        $this->resetEditForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId   = $id;
        $this->showDelete = true;
    }

    public function deleteTransfer(): void
    {
        TransferRekening::findOrFail($this->deleteId)->delete();
        session()->flash('success', 'Transfer rekening berhasil dihapus.');
        $this->showDelete = false;
        $this->deleteId   = null;
    }

    private function resetEditForm(): void
    {
        $this->editId = null;
        $this->reset(['tr_dari', 'tr_ke', 'tr_kat', 'tr_jumlah', 'tr_ket', 'tr_tanggal']);
    }
}; ?>

<div>

@if(session('success'))
<div class="mb-4 p-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-medium">
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-2 sm:flex sm:flex-row gap-3 items-end mb-5">
    <div class="col-span-1">
        <label class="label">Dari Tanggal</label>
        <input type="date" wire:model.live="filterFrom" class="input" />
    </div>
    <div class="col-span-1">
        <label class="label">Sampai Tanggal</label>
        <input type="date" wire:model.live="filterTo" class="input" />
    </div>
    <a href="{{ route('arus-kas') }}" class="btn-secondary col-span-1">← Kembali</a>
    <a href="/export/transfer-rekening?from={{ $filterFrom }}&to={{ $filterTo }}"
       class="btn-secondary col-span-1">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Excel
    </a>
</div>

<div class="card overflow-hidden p-0">
    {{-- Mobile card layout --}}
    <div class="sm:hidden divide-y divide-gray-100">
        @forelse($transfers as $tr)
        <div class="px-4 py-3 space-y-1.5">
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-400">{{ $tr->tanggal->isoFormat('D MMM Y') }}</span>
                <span class="text-sm font-bold text-blue-600">Rp {{ number_format($tr->jumlah, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center gap-1.5 text-xs">
                <span class="px-1.5 py-0.5 rounded bg-red-100 text-red-700 font-medium">{{ $tr->dariRekening->nama_rek ?? '-' }}</span>
                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">{{ $tr->keRekening->nama_rek ?? '-' }}</span>
            </div>
            <div class="flex items-center gap-1.5 text-[11px] text-gray-400">
                <span>{{ $tr->kategori->nama ?? '-' }}</span>
                @if($tr->keterangan)
                <span>&middot;</span>
                <span class="truncate">{{ $tr->keterangan }}</span>
                @endif
                <span>&middot;</span>
                <span>{{ $tr->creator->name ?? '-' }}</span>
            </div>
            @if(auth()->user()?->isBendahara())
            <div class="flex items-center gap-1 pt-0.5">
                <button wire:click="editTransfer({{ $tr->id }})" class="p-1 hover:bg-blue-100 text-blue-600 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button wire:click="confirmDelete({{ $tr->id }})" class="p-1 hover:bg-red-100 text-red-500 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
            @endif
        </div>
        @empty
        <div class="px-4 py-12 text-center text-gray-400">Belum ada riwayat transfer</div>
        @endforelse
    </div>
    {{-- Desktop table --}}
    <div class="hidden sm:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">Tanggal</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">Dari</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">Ke</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 hidden md:table-cell">Kategori</th>
                    <th class="text-right px-4 py-3 text-xs font-bold text-gray-500">Jumlah</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 hidden lg:table-cell">Keterangan</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 hidden lg:table-cell">Oleh</th>
                    @if(auth()->user()?->isBendahara())
                    <th class="text-center px-4 py-3 text-xs font-bold text-gray-500">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($transfers as $tr)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-600">{{ $tr->tanggal->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                            {{ $tr->dariRekening->nama_rek ?? '-' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                            {{ $tr->keRekening->nama_rek ?? '-' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell text-gray-600">{{ $tr->kategori->nama ?? '-' }}</td>
                    <td class="px-4 py-3 text-right font-bold text-blue-600">
                        Rp {{ number_format($tr->jumlah, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-gray-600 hidden lg:table-cell">{{ $tr->keterangan ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-500 hidden lg:table-cell text-xs">{{ $tr->creator->name ?? '-' }}</td>
                    @if(auth()->user()?->isBendahara())
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <button wire:click="editTransfer({{ $tr->id }})"
                                    class="p-1.5 rounded-lg hover:bg-blue-50 text-blue-600" title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button wire:click="confirmDelete({{ $tr->id }})"
                                    class="p-1.5 rounded-lg hover:bg-red-50 text-red-500" title="Hapus">
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
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-gray-400">Belum ada riwayat transfer</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t">{{ $transfers->links() }}</div>
</div>

{{-- Modal Edit Transfer --}}
@if($showEdit)
<div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <h3 class="text-base font-bold text-gray-800">Edit Transfer Rekening</h3>
            <button wire:click="$set('showEdit', false)" class="p-1.5 hover:bg-gray-100 rounded-lg">
                <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-5 space-y-4">
            <div>
                <label class="label">Dari Rekening</label>
                <select wire:model="tr_dari" class="input">
                    <option value="">-- Pilih --</option>
                    @foreach($rekening as $r)
                    <option value="{{ $r->id }}">{{ $r->nama_rek }}</option>
                    @endforeach
                </select>
                @error('tr_dari') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Ke Rekening</label>
                <select wire:model="tr_ke" class="input">
                    <option value="">-- Pilih --</option>
                    @foreach($rekening as $r)
                    <option value="{{ $r->id }}">{{ $r->nama_rek }}</option>
                    @endforeach
                </select>
                @error('tr_ke') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Kategori</label>
                <select wire:model="tr_kat" class="input">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($kategori as $k)
                    <option value="{{ $k->id }}">{{ $k->nama }}</option>
                    @endforeach
                </select>
                @error('tr_kat') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Jumlah (Rp)</label>
                <input type="text" wire:model="tr_jumlah" class="input" inputmode="numeric"
                       x-on:input="$event.target.value = ($event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'))"
                       placeholder="0" />
                @error('tr_jumlah') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Tanggal</label>
                <input type="date" wire:model="tr_tanggal" class="input" />
                @error('tr_tanggal') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Keterangan</label>
                <textarea wire:model="tr_ket" class="input" rows="2" placeholder="Keterangan transfer..."></textarea>
            </div>
        </div>
        <div class="px-5 pb-5 flex gap-2">
            <button wire:click="$set('showEdit', false)" class="btn-secondary flex-1">
                Batal
            </button>
            <button wire:click="updateTransfer" class="btn-primary flex-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan Perubahan
            </button>
        </div>
    </div>
</div>
@endif

{{-- Modal Konfirmasi Hapus --}}
@if($showDelete)
<div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl">
        <div class="p-6 text-center">
            <div class="mx-auto w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">Hapus Transfer?</h3>
            <p class="text-sm text-gray-500 mb-6">Data transfer ini akan dihapus permanen dan tidak bisa dikembalikan.</p>
            <div class="flex gap-2">
                <button wire:click="$set('showDelete', false)" class="btn-secondary flex-1">Batal</button>
                <button wire:click="deleteTransfer" class="btn-danger flex-1">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>
@endif

</div>

