<?php

use App\Models\Kategori;
use App\Models\Keuangan;
use App\Models\Rekening;
use App\Models\TransferRekening;
use App\Models\User;
use App\Imports\KeuanganImport;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination, WithFileUploads;

    public function render(): mixed
    {
        return parent::render()->title('Arus Kas');
    }

    // Filter
    public string $search     = '';
    public string $filterKat  = '';
    public string $filterRek  = '';
    public string $filterFrom = '';
    public string $filterTo   = '';
    public string $filterUser = '';
    public int    $perPage    = 10;

    // Modal form - Transaksi
    public bool   $showModal   = false;
    public bool   $showTransfer = false;
    public ?int   $editId      = null;

    public string $tanggal     = '';
    public string $jenis       = 'masuk'; // masuk | keluar
    public string $jumlah      = '';
    public string $keterangan  = '';
    public string $id_rekening = '';
    public string $id_kategori = '';

    // Transfer rekening
    public string $tr_dari    = '';
    public string $tr_ke      = '';
    public string $tr_kat     = '';
    public string $tr_jumlah  = '';
    public string $tr_ket     = '';
    public string $tr_tanggal = '';

    public bool $confirmDelete = false;
    public ?int $deleteId      = null;

    // Import
    public bool $showImport = false;
    public $importFile = null;
    public array $importErrors = [];
    public array $importPreviewRows = [];
    public array $importPreviewSummary = [];
    public bool $importPreviewReady = false;
    public ?string $importResult = null;

    public function mount(): void
    {
        $this->tanggal    = now()->format('Y-m-d');
        $this->tr_tanggal = now()->format('Y-m-d');
        $this->filterFrom = now()->copy()->startOfMonth()->format('Y-m-d');
        $this->filterTo   = now()->copy()->endOfMonth()->format('Y-m-d');
    }

    public function with(): array
    {
        if ($this->filterFrom === '') {
            $this->filterFrom = now()->copy()->startOfMonth()->format('Y-m-d');
        }
        if ($this->filterTo === '') {
            $this->filterTo = now()->copy()->endOfMonth()->format('Y-m-d');
        }

        $from = $this->filterFrom !== '' ? $this->filterFrom : null;
        $to   = $this->filterTo   !== '' ? $this->filterTo   : null;

        $query = Keuangan::with(['rekening', 'kategori', 'creator'])
            ->when($this->search,    fn($q) => $q->where('keterangan', 'like', "%{$this->search}%"))
            ->when($this->filterKat, fn($q) => $q->where('id_kategori', $this->filterKat))
            ->when($this->filterRek, fn($q) => $q->where('id_rekening', $this->filterRek))
            ->when($this->filterUser, fn($q) => $q->where('created_by', $this->filterUser))
            ->when($from, fn($q) => $q->whereDate('tanggal', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('tanggal', '<=', $to))
            ->orderBy('tanggal', 'desc')->orderBy('id', 'desc');

        $saldoAwal = 0;
        if ($from) {
            $saldoAwal = Keuangan::where('tanggal', '<', $from)
                ->when($this->filterKat, fn($q) => $q->where('id_kategori', $this->filterKat))
                ->when($this->filterRek, fn($q) => $q->where('id_rekening', $this->filterRek))
                ->selectRaw('COALESCE(SUM(masuk),0) - COALESCE(SUM(keluar),0) as saldo')
                ->value('saldo') ?? 0;
        }

        $totalMasuk  = (clone $query)->getQuery()->sum('masuk');
        $totalKeluar = (clone $query)->getQuery()->sum('keluar');

        return [
            'transaksi'   => $query->paginate($this->perPage),
            'rekening'    => Rekening::all(),
            'kategori'    => Kategori::all(),
            'users'       => User::orderBy('name')->get(),
            'totalMasuk'  => $totalMasuk,
            'totalKeluar' => $totalKeluar,
            'saldoAwal'   => $saldoAwal,
        ];
    }

    public function openCreate(): void
    {
        $this->reset(['editId', 'jumlah', 'keterangan', 'id_rekening', 'id_kategori']);
        $this->jenis     = 'masuk';
        $this->tanggal   = now()->format('Y-m-d');
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $trx = Keuangan::findOrFail($id);

        if ($this->isSaldoAwalTx($trx->keterangan)) {
            session()->flash('error', 'Transaksi saldo awal tidak bisa diedit.');
            return;
        }

        $this->editId      = $id;
        $this->tanggal     = $trx->tanggal->format('Y-m-d');
        $this->jenis       = $trx->masuk > 0 ? 'masuk' : 'keluar';
        $this->jumlah      = number_format((float) ($trx->masuk > 0 ? $trx->masuk : $trx->keluar), 0, '', '.');
        $this->keterangan  = $trx->keterangan ?? '';
        $this->id_rekening = $trx->id_rekening;
        $this->id_kategori = $trx->id_kategori;
        $this->showModal   = true;
    }

    public function save(): void
    {
        $this->jumlah = preg_replace('/[^0-9]/', '', (string) $this->jumlah) ?: '0';

        $this->validate([
            'tanggal'     => 'required|date',
            'jumlah'      => 'required|numeric|min:1',
            'id_rekening' => 'required|exists:rekening,id',
            'id_kategori' => 'required|exists:kategori,id',
        ]);

        $data = [
            'tanggal'     => $this->tanggal,
            'masuk'       => $this->jenis === 'masuk'  ? $this->jumlah : 0,
            'keluar'      => $this->jenis === 'keluar' ? $this->jumlah : 0,
            'keterangan'  => $this->keterangan,
            'id_rekening' => $this->id_rekening,
            'id_kategori' => $this->id_kategori,
            'created_by'  => auth()->id(),
        ];

        if ($this->editId) {
            Keuangan::findOrFail($this->editId)->update($data);
            session()->flash('success', 'Transaksi berhasil diperbarui.');
        } else {
            Keuangan::create($data);
            session()->flash('success', 'Transaksi berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->resetPage();
    }

    public function confirmDeleteItem(int $id): void
    {
        $trx = Keuangan::findOrFail($id);
        if ($this->isSaldoAwalTx($trx->keterangan)) {
            session()->flash('error', 'Transaksi saldo awal tidak bisa dihapus.');
            return;
        }

        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteItem(): void
    {
        if ($this->deleteId) {
            $trx = Keuangan::findOrFail($this->deleteId);
            if ($this->isSaldoAwalTx($trx->keterangan)) {
                $this->confirmDelete = false;
                $this->deleteId      = null;
                session()->flash('error', 'Transaksi saldo awal tidak bisa dihapus.');
                return;
            }

            $trx->delete();
            session()->flash('success', 'Transaksi berhasil dihapus.');
        }
        $this->confirmDelete = false;
        $this->deleteId      = null;
    }

    public function saveTransfer(): void
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

        TransferRekening::create([
            'dari_rekening' => $this->tr_dari,
            'ke_rekening'   => $this->tr_ke,
            'id_kategori'   => $this->tr_kat,
            'jumlah'        => $this->tr_jumlah,
            'keterangan'    => $this->tr_ket,
            'tanggal'       => $this->tr_tanggal,
            'created_by'    => auth()->id(),
        ]);

        session()->flash('success', 'Transfer rekening berhasil dicatat.');
        $this->showTransfer = false;
        $this->reset(['tr_dari', 'tr_ke', 'tr_kat', 'tr_jumlah', 'tr_ket']);
    }

    public function exportExcel()
    {
        $params = http_build_query([
            'from'        => $this->filterFrom,
            'to'          => $this->filterTo,
            'kategori_id' => $this->filterKat,
            'rekening_id' => $this->filterRek,
        ]);
        return redirect('/export/keuangan?' . $params);
    }

    public function updatedSearch(): void     { $this->resetPage(); }
    public function updatedFilterKat(): void  { $this->resetPage(); }
    public function updatedFilterRek(): void  { $this->resetPage(); }
    public function updatedFilterFrom(): void { $this->resetPage(); }
    public function updatedFilterTo(): void   { $this->resetPage(); }
    public function updatedFilterUser(): void { $this->resetPage(); }
    public function updatedPerPage(): void    { $this->resetPage(); }

    public function openImport(): void
    {
        $this->resetImportState();
        $this->showImport = true;
    }

    public function updatedImportFile(): void
    {
        $this->reset(['importErrors', 'importPreviewRows', 'importPreviewSummary', 'importPreviewReady', 'importResult']);
    }

    public function downloadTemplate()
    {
        return redirect()->route('export.template-import');
    }

    protected function validateImportFile(): void
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'importFile.required' => 'File harus dipilih.',
            'importFile.mimes'    => 'File harus berformat Excel (xlsx/xls) atau CSV.',
            'importFile.max'      => 'Ukuran file maksimal 5MB.',
        ]);
    }

    public function previewImport(): void
    {
        $this->validateImportFile();

        $importer = new KeuanganImport(auth()->id());
        $result = $importer->preview($this->importFile->getRealPath());

        $this->importPreviewRows = $result['rows'];
        $this->importPreviewSummary = $result['summary'];
        $this->importErrors = $result['errors'];
        $this->importPreviewReady = true;
        $this->importResult = null;
    }

    public function processImport(): void
    {
        $this->validateImportFile();

        if (!$this->importPreviewReady) {
            $this->previewImport();
            return;
        }

        $importer = new KeuanganImport(auth()->id());
        $result = $importer->importFromFile($this->importFile->getRealPath());

        $this->importErrors = $result['errors'];
        $count = $result['imported'];
        $invalidCount = $result['summary']['invalid_rows'] ?? count($this->importErrors);

        if ($count > 0) {
            $this->importResult = $invalidCount > 0
                ? "{$count} transaksi berhasil diimport. {$invalidCount} baris tidak diimport."
                : "{$count} transaksi berhasil diimport.";
        } else {
            $this->importResult = 'Tidak ada data yang berhasil diimport.';
        }

        session()->flash($count > 0 ? 'success' : 'error', $this->importResult);
        $this->showImport = false;
        $this->resetImportState();
        $this->resetPage();
    }

    protected function resetImportState(): void
    {
        $this->reset([
            'importFile',
            'importErrors',
            'importPreviewRows',
            'importPreviewSummary',
            'importPreviewReady',
            'importResult',
        ]);
    }

    public function isSaldoAwalTx(?string $keterangan): bool
    {
        if (!$keterangan) return false;
        return str_starts_with($keterangan, '__SALDO_AWAL__');
    }

    public function readableKeterangan(?string $keterangan): string
    {
        if (!$keterangan) return '-';

        if ($this->isSaldoAwalTx($keterangan)) {
            $clean = trim(str_replace('__SALDO_AWAL__', '', $keterangan));
            return $clean !== '' ? $clean : 'Transaksi saldo awal';
        }

        return $keterangan;
    }
}; ?>

<div>
@if(session('success'))
<div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl px-4 py-3"
     x-data x-init="setTimeout(() => $el.remove(), 4000)">
    ✓ {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3"
     x-data x-init="setTimeout(() => $el.remove(), 5000)">
    ⚠ {{ session('error') }}
</div>
@endif

{{-- Action Buttons --}}
@if(auth()->user()?->isBendahara())
<div class="flex flex-wrap items-center gap-2 mb-3">
    <button wire:click="openCreate" class="btn-primary whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah
    </button>
    <button wire:click="openImport" class="btn-secondary whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m4-8l-4-4m0 0l-4 4m4-4v12"/>
        </svg>
        Import
    </button>
    <button wire:click="$set('showTransfer', true)" class="btn-secondary whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
        </svg>
        Transfer
    </button>
    <button wire:click="exportExcel" class="btn-secondary whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Excel
    </button>
</div>
@else
<div class="flex items-center gap-2 mb-3">
    <button wire:click="exportExcel" class="btn-secondary whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export Excel
    </button>
</div>
@endif

{{-- Filters: search + grid layout for mobile --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 mb-4">
    <div class="col-span-2 sm:col-span-3 lg:col-span-1">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari keterangan..." class="input text-sm" />
    </div>
    <select wire:model.live="filterKat" class="input text-sm">
        <option value="">Semua Kategori</option>
        @foreach($kategori as $k)
        <option value="{{ $k->id }}">{{ $k->nama }}</option>
        @endforeach
    </select>
    <select wire:model.live="filterRek" class="input text-sm">
        <option value="">Semua Rekening</option>
        @foreach($rekening as $r)
        <option value="{{ $r->id }}">{{ $r->nama_rek }}</option>
        @endforeach
    </select>
    <select wire:model.live="filterUser" class="input text-sm">
        <option value="">Semua User</option>
        @foreach($users as $u)
        <option value="{{ $u->id }}">{{ $u->name }}</option>
        @endforeach
    </select>
    <input type="date" wire:model.live="filterFrom" value="{{ $this->filterFrom }}" class="input text-sm" title="Dari tanggal">
    <input type="date" wire:model.live="filterTo"   value="{{ $this->filterTo }}"   class="input text-sm" title="Sampai tanggal">
</div>

{{-- Summary row --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
    <div class="bg-amber-50 rounded-xl p-3">
        <p class="text-xs text-gray-500">Saldo Awal</p>
        <p class="text-base font-bold text-amber-600">Rp {{ number_format($saldoAwal, 0, ',', '.') }}</p>
    </div>
    <div class="bg-emerald-50 rounded-xl p-3">
        <p class="text-xs text-gray-500">Total Masuk</p>
        <p class="text-base font-bold text-emerald-600">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</p>
    </div>
    <div class="bg-red-50 rounded-xl p-3">
        <p class="text-xs text-gray-500">Total Keluar</p>
        <p class="text-base font-bold text-red-500">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</p>
    </div>
    <div class="bg-blue-50 rounded-xl p-3">
        <p class="text-xs text-gray-500">Saldo Akhir</p>
        <p class="text-base font-bold text-blue-600">Rp {{ number_format($saldoAwal + $totalMasuk - $totalKeluar, 0, ',', '.') }}</p>
    </div>
</div>

{{-- Table --}}
<div class="card overflow-hidden p-0">
    {{-- Mobile card layout --}}
    <div class="sm:hidden divide-y divide-gray-100">
        @forelse($transaksi as $trx)
        <div class="px-4 py-3 space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-400">{{ $trx->tanggal->isoFormat('D MMM Y') }}</span>
                @if($trx->masuk > 0)
                <span class="text-sm font-bold text-emerald-600">+Rp {{ number_format($trx->masuk, 0, ',', '.') }}</span>
                @else
                <span class="text-sm font-bold text-red-500">-Rp {{ number_format($trx->keluar, 0, ',', '.') }}</span>
                @endif
            </div>
            <div>
                @if($this->isSaldoAwalTx($trx->keterangan))
                <div class="flex items-center gap-1.5">
                    <span class="inline-flex px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">SA</span>
                    <p class="text-sm text-gray-800">{{ $this->readableKeterangan($trx->keterangan) }}</p>
                </div>
                @else
                <p class="text-sm text-gray-800">{{ $this->readableKeterangan($trx->keterangan) }}</p>
                @endif
            </div>
            <div class="flex items-center gap-1.5 text-[11px] text-gray-400">
                <span class="px-1.5 py-0.5 rounded bg-primary-50 text-primary-700 font-medium">{{ $trx->kategori->nama ?? '-' }}</span>
                <span>&middot;</span>
                <span>{{ $trx->rekening->nama_rek ?? '-' }}</span>
                <span>&middot;</span>
                <span>{{ $trx->creator->name ?? '-' }}</span>
            </div>
            @if(auth()->user()?->isBendahara() && !$this->isSaldoAwalTx($trx->keterangan))
            <div class="flex items-center gap-1 pt-0.5">
                <button wire:click="openEdit({{ $trx->id }})" class="p-1 hover:bg-blue-100 text-blue-600 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button wire:click="confirmDeleteItem({{ $trx->id }})" class="p-1 hover:bg-red-100 text-red-500 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
            @endif
        </div>
        @empty
        <div class="px-4 py-12 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Belum ada transaksi
        </div>
        @endforelse
    </div>
    {{-- Desktop table --}}
    <div class="hidden sm:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-center px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider w-12">No</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Keterangan</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider hidden md:table-cell">Kategori</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Rekening</th>
                    <th class="text-right px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Masuk</th>
                    <th class="text-right px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Keluar</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Dibuat oleh</th>
                    @if(auth()->user()?->isBendahara())
                    <th class="text-center px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($transaksi as $trx)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 text-center text-gray-400 text-xs">{{ $transaksi->firstItem() + $loop->index }}</td>
                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $trx->tanggal->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-gray-800 font-medium max-w-xs">
                        @if($this->isSaldoAwalTx($trx->keterangan))
                        <div class="flex items-center gap-2">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-700">Saldo Awal</span>
                            <span class="truncate">{{ $this->readableKeterangan($trx->keterangan) }}</span>
                        </div>
                        @else
                        <span class="truncate block">{{ $this->readableKeterangan($trx->keterangan) }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-primary-100 text-primary-700">
                            {{ $trx->kategori->nama ?? '-' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 hidden lg:table-cell">{{ $trx->rekening->nama_rek ?? '-' }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-emerald-600">
                        {{ $trx->masuk > 0 ? 'Rp '.number_format($trx->masuk, 0, ',', '.') : '' }}
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-red-500">
                        {{ $trx->keluar > 0 ? 'Rp '.number_format($trx->keluar, 0, ',', '.') : '' }}
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs hidden lg:table-cell">{{ $trx->creator->name ?? '-' }}</td>
                    @if(auth()->user()?->isBendahara())
                    <td class="px-4 py-3 text-center">
                        @if($this->isSaldoAwalTx($trx->keterangan))
                        <span class="inline-flex items-center px-2 py-1 rounded-lg text-[11px] font-bold bg-gray-100 text-gray-500">
                            Terkunci
                        </span>
                        @else
                        <div class="flex items-center justify-center gap-1">
                            <button wire:click="openEdit({{ $trx->id }})"
                                    class="p-1.5 hover:bg-blue-100 text-blue-600 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button wire:click="confirmDeleteItem({{ $trx->id }})"
                                    class="p-1.5 hover:bg-red-100 text-red-500 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                        @endif
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-12 text-center text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Belum ada transaksi
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transaksi->hasPages() || $transaksi->total() > 0)
    <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/50 space-y-3">
        {{-- Info row --}}
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <span>Menampilkan {{ $transaksi->firstItem() ?? 0 }}–{{ $transaksi->lastItem() ?? 0 }} dari {{ $transaksi->total() }}</span>
                <select wire:model.live="perPage" class="border border-gray-300 rounded-md py-1 px-2 text-xs bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                    @foreach([10, 20, 30, 50, 100, 500, 1000] as $size)
                    <option value="{{ $size }}">{{ $size }}</option>
                    @endforeach
                </select>
                <span>per halaman</span>
            </div>
        </div>
        {{-- Pagination buttons --}}
        @if($transaksi->hasPages())
        <div class="flex flex-wrap items-center justify-center gap-1">
            {{-- Previous --}}
            @if($transaksi->onFirstPage())
                <span class="px-3 py-1.5 text-xs text-gray-400 bg-gray-100 rounded-lg cursor-default">&laquo; Prev</span>
            @else
                <button wire:click="previousPage" class="px-3 py-1.5 text-xs text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    &laquo; Prev
                </button>
            @endif

            {{-- Page Numbers --}}
            @php
                $current = $transaksi->currentPage();
                $last = $transaksi->lastPage();
                $pages = collect();
                $pages->push(1);
                for ($i = max(2, $current - 2); $i <= min($last - 1, $current + 2); $i++) {
                    $pages->push($i);
                }
                if ($last > 1) $pages->push($last);
                $pages = $pages->unique()->sort()->values();
            @endphp

            @foreach($pages as $idx => $page)
                @if($idx > 0 && $page - $pages[$idx - 1] > 1)
                    <span class="px-2 py-1.5 text-xs text-gray-400">...</span>
                @endif
                @if($page == $current)
                    <span class="px-3 py-1.5 text-xs font-bold text-white bg-primary-600 rounded-lg">{{ $page }}</span>
                @else
                    <button wire:click="gotoPage({{ $page }})" class="px-3 py-1.5 text-xs text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        {{ $page }}
                    </button>
                @endif
            @endforeach

            {{-- Next --}}
            @if($transaksi->hasMorePages())
                <button wire:click="nextPage" class="px-3 py-1.5 text-xs text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Next &raquo;
                </button>
            @else
                <span class="px-3 py-1.5 text-xs text-gray-400 bg-gray-100 rounded-lg cursor-default">Next &raquo;</span>
            @endif
        </div>
        @endif
    </div>
    @endif
</div>

{{-- Modal Transaksi --}}
@if($showModal)
<div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/50"
     x-data x-transition>
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl" @click.away="$wire.set('showModal', false)">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <h3 class="text-base font-bold text-gray-800">
                {{ $editId ? 'Edit Transaksi' : 'Tambah Transaksi' }}
            </h3>
            <button wire:click="$set('showModal', false)" class="p-1.5 hover:bg-gray-100 rounded-lg">
                <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-5 space-y-4">
            {{-- Jenis --}}
            <div>
                <label class="label">Jenis Transaksi</label>
                <div class="flex rounded-xl overflow-hidden border border-gray-200">
                    <button type="button" wire:click="$set('jenis', 'masuk')"
                            class="flex-1 py-2.5 text-sm font-semibold transition
                                   {{ $jenis === 'masuk' ? 'bg-emerald-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                        ↑ Masuk
                    </button>
                    <button type="button" wire:click="$set('jenis', 'keluar')"
                            class="flex-1 py-2.5 text-sm font-semibold transition border-l border-gray-200
                                   {{ $jenis === 'keluar' ? 'bg-red-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                        ↓ Keluar
                    </button>
                </div>
            </div>
            <div>
                <label class="label">Tanggal</label>
                <input type="date" wire:model="tanggal" class="input" />
                @error('tanggal') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Jumlah (Rp)</label>
                <input type="text" wire:model="jumlah" class="input" inputmode="numeric"
                       @input="$event.target.value = ($event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'))"
                       placeholder="0" />
                @error('jumlah') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Rekening</label>
                <select wire:model="id_rekening" class="input">
                    <option value="">-- Pilih Rekening --</option>
                    @foreach($rekening as $r)
                    <option value="{{ $r->id }}">{{ $r->nama_rek }} ({{ $r->no_rek }})</option>
                    @endforeach
                </select>
                @error('id_rekening') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Kategori</label>
                <select wire:model="id_kategori" class="input">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($kategori as $k)
                    <option value="{{ $k->id }}">{{ $k->nama }}</option>
                    @endforeach
                </select>
                @error('id_kategori') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Keterangan</label>
                <textarea wire:model="keterangan" class="input" rows="2" placeholder="Keterangan transaksi..."></textarea>
            </div>
        </div>
        <div class="px-5 pb-5 flex gap-2">
            <button wire:click="$set('showModal', false)" class="btn-secondary flex-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Batal
            </button>
            <button wire:click="save" class="btn-primary flex-1">
                <svg class="w-4 h-4" wire:loading.remove wire:target="save" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span wire:loading.remove wire:target="save">Simpan</span>
                <span wire:loading wire:target="save">Menyimpan...</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- Modal Transfer Rekening --}}
@if($showTransfer)
<div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <h3 class="text-base font-bold text-gray-800">Transfer Rekening</h3>
            <button wire:click="$set('showTransfer', false)" class="p-1.5 hover:bg-gray-100 rounded-lg">
                <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-5 space-y-4">
            <div class="bg-blue-50 rounded-xl p-3 text-xs text-blue-700">
                💡 Transfer rekening tidak mempengaruhi saldo masuk/keluar kategori.
                Ini hanya mencatat perpindahan dana antar rekening.
            </div>
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
                       @input="$event.target.value = ($event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'))"
                       placeholder="0" />
                @error('tr_jumlah') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Tanggal</label>
                <input type="date" wire:model="tr_tanggal" class="input" />
            </div>
            <div>
                <label class="label">Keterangan</label>
                <textarea wire:model="tr_ket" class="input" rows="2" placeholder="Keterangan transfer..."></textarea>
            </div>
        </div>
        <div class="px-5 pb-5 flex gap-2">
            <button wire:click="$set('showTransfer', false)" class="btn-secondary flex-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Batal
            </button>
            <button wire:click="saveTransfer" class="btn-primary flex-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Transfer
            </button>
        </div>
    </div>
</div>
@endif

{{-- Confirm Delete --}}
@if($confirmDelete)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-2xl w-full max-w-sm p-6 shadow-2xl text-center">
        <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h3 class="text-base font-bold text-gray-800 mb-2">Hapus Transaksi?</h3>
        <p class="text-sm text-gray-500 mb-5">Data yang dihapus tidak dapat dikembalikan.</p>
        <div class="flex gap-3">
            <button wire:click="$set('confirmDelete', false)" class="btn-secondary flex-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Batal
            </button>
            <button wire:click="deleteItem" class="btn-danger flex-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Ya, Hapus
            </button>
        </div>
    </div>
</div>
@endif

{{-- Import Modal --}}
@if($showImport)
<div class="fixed inset-0 z-50 bg-black/50">
    <div class="h-full w-full p-0 sm:p-4">
        <div class="bg-white h-full w-full sm:rounded-2xl shadow-2xl flex flex-col overflow-hidden" @click.away="$wire.set('showImport', false)">
        <div class="flex items-center justify-between p-5 border-b bg-white shrink-0">
            <h3 class="text-lg font-bold text-gray-800">Import Data Keuangan</h3>
            <button wire:click="$set('showImport', false)" class="p-1.5 hover:bg-gray-100 rounded-lg">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-5 space-y-4 bg-gray-50/40">
            {{-- Download Template --}}
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <p class="text-sm text-blue-800 mb-2"><strong>Langkah 1:</strong> Download template Excel terlebih dahulu</p>
                <button wire:click="downloadTemplate" class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-800">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download Template
                </button>
                <p class="text-xs text-blue-600 mt-1">Template berisi contoh data + daftar kategori & rekening yang valid.</p>
            </div>

            {{-- Upload File --}}
            <div>
                <p class="text-sm text-gray-700 mb-2"><strong>Langkah 2:</strong> Upload file Excel yang sudah diisi</p>
                <input type="file" wire:model="importFile" accept=".xlsx,.xls,.csv"
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100" />
                @error('importFile')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <div wire:loading wire:target="importFile" class="text-sm text-gray-500 mt-2">Uploading...</div>
            </div>

            @if($importPreviewReady)
            <div class="bg-white border border-gray-200 rounded-xl p-4 space-y-4 shadow-sm">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div>
                        <p class="text-sm font-bold text-gray-800">Preview Import</p>
                        <p class="text-xs text-gray-500">Periksa data valid dan error sebelum konfirmasi import.</p>
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-700">Total: {{ $importPreviewSummary['total_rows'] ?? 0 }}</span>
                        <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700">Valid: {{ $importPreviewSummary['valid_rows'] ?? 0 }}</span>
                        <span class="px-2.5 py-1 rounded-full bg-red-100 text-red-700">Error: {{ $importPreviewSummary['invalid_rows'] ?? 0 }}</span>
                        @if(($importPreviewSummary['skipped_rows'] ?? 0) > 0)
                        <span class="px-2.5 py-1 rounded-full bg-gray-200 text-gray-700">Kosong dilewati: {{ $importPreviewSummary['skipped_rows'] }}</span>
                        @endif
                    </div>
                </div>

                <div class="overflow-auto max-h-[52vh] border border-gray-200 rounded-xl bg-white">
                    <table class="min-w-full text-xs">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr class="text-gray-600">
                                <th class="px-3 py-2 text-left">Baris</th>
                                <th class="px-3 py-2 text-left">Tanggal</th>
                                <th class="px-3 py-2 text-left">Keterangan</th>
                                <th class="px-3 py-2 text-left">Kategori</th>
                                <th class="px-3 py-2 text-left">Rekening</th>
                                <th class="px-3 py-2 text-right">Masuk</th>
                                <th class="px-3 py-2 text-right">Keluar</th>
                                <th class="px-3 py-2 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($importPreviewRows as $row)
                            <tr class="align-top {{ $row['status'] === 'valid' ? 'bg-white' : 'bg-red-50/40' }}">
                                <td class="px-3 py-2 text-gray-500 whitespace-nowrap">{{ $row['row_num'] }}</td>
                                <td class="px-3 py-2 text-gray-700 whitespace-nowrap">{{ $row['tanggal'] ?: ($row['tanggal_raw'] ?: '-') }}</td>
                                <td class="px-3 py-2 text-gray-700 min-w-[220px]">{{ $row['keterangan'] ?: '-' }}</td>
                                <td class="px-3 py-2 text-gray-700 whitespace-nowrap">{{ $row['kategori'] ?: '-' }}</td>
                                <td class="px-3 py-2 text-gray-700 whitespace-nowrap">{{ $row['rekening'] ?: '-' }}</td>
                                <td class="px-3 py-2 text-right text-emerald-700 whitespace-nowrap">{{ $row['masuk'] > 0 ? 'Rp '.number_format($row['masuk'], 0, ',', '.') : '-' }}</td>
                                <td class="px-3 py-2 text-right text-red-700 whitespace-nowrap">{{ $row['keluar'] > 0 ? 'Rp '.number_format($row['keluar'], 0, ',', '.') : '-' }}</td>
                                <td class="px-3 py-2 min-w-[220px]">
                                    @if($row['status'] === 'valid')
                                    <span class="inline-flex px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 font-medium">Siap diimport</span>
                                    @else
                                    <div class="space-y-1">
                                        <span class="inline-flex px-2 py-1 rounded-full bg-red-100 text-red-700 font-medium">Perlu diperbaiki</span>
                                        @foreach($row['errors'] as $error)
                                        <p class="text-red-600">• {{ $error }}</p>
                                        @endforeach
                                    </div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-3 py-6 text-center text-gray-400">Belum ada data untuk dipreview.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Import Result --}}
            @if($importResult)
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl px-4 py-3">
                ✓ {{ $importResult }}
            </div>
            @endif

            {{-- Import Errors --}}
            @if(count($importErrors) > 0)
            <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                <p class="text-sm font-bold text-red-700 mb-2">Beberapa baris gagal diimport:</p>
                <ul class="text-xs text-red-600 space-y-1 max-h-40 overflow-y-auto">
                    @foreach($importErrors as $err)
                    <li>• {{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
        <div class="flex gap-3 p-5 border-t bg-white shrink-0">
            <button wire:click="$set('showImport', false)" class="btn-secondary flex-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Tutup
            </button>
            @if(!$importPreviewReady)
            <button wire:click="previewImport" class="btn-primary flex-1" wire:loading.attr="disabled" wire:target="previewImport,importFile">
                <svg class="w-4 h-4" wire:loading.remove wire:target="previewImport" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <span wire:loading.remove wire:target="previewImport">Preview Data</span>
                <span wire:loading wire:target="previewImport">Membaca file...</span>
            </button>
            @else
            <button wire:click="previewImport" class="btn-secondary flex-1" wire:loading.attr="disabled" wire:target="previewImport">
                <svg class="w-4 h-4" wire:loading.remove wire:target="previewImport" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m14.836 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span wire:loading.remove wire:target="previewImport">Refresh Preview</span>
                <span wire:loading wire:target="previewImport">Memperbarui...</span>
            </button>
            <button wire:click="processImport" class="btn-primary flex-1" wire:loading.attr="disabled" wire:target="processImport">
                <svg class="w-4 h-4" wire:loading.remove wire:target="processImport" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span wire:loading.remove wire:target="processImport">Konfirmasi Import</span>
                <span wire:loading wire:target="processImport">Mengimport...</span>
            </button>
            @endif
        </div>
        </div>
    </div>
</div>
@endif

</div>

