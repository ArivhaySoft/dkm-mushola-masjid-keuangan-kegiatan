<?php

use App\Models\ActivityLog;
use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $module = '';
    public string $action = '';
    public string $userId = '';
    public string $from = '';
    public string $to = '';
    public int $perPage = 15;
    public bool $showDeleteModal = false;
    public string $deleteFrom = '';
    public string $deleteTo = '';

    public function render(): mixed
    {
        return parent::render()->title('Log Activity');
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->isBendahara(), 403);
        $this->from = now()->startOfMonth()->format('Y-m-d');
        $this->to = now()->format('Y-m-d');
        $this->deleteFrom = $this->from;
        $this->deleteTo = $this->to;
    }

    public function with(): array
    {
        $query = ActivityLog::with('creator')
            ->whereIn('module', ['arus_kas', 'transfer_saldo'])
            ->when($this->module, fn($q) => $q->where('module', $this->module))
            ->when($this->action, fn($q) => $q->where('action', $this->action))
            ->when($this->userId, fn($q) => $q->where('created_by', $this->userId))
            ->when($this->from, fn($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to, fn($q) => $q->whereDate('created_at', '<=', $this->to))
            ->when($this->search, function ($q) {
                $keyword = '%' . $this->search . '%';
                $q->where(function ($qq) use ($keyword) {
                    $qq->where('description', 'like', $keyword)
                        ->orWhere('entity_type', 'like', $keyword)
                        ->orWhere('entity_id', 'like', $keyword);
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        return [
            'logs' => $query->paginate($this->perPage),
            'users' => User::orderBy('name')->get(['id', 'name']),
        ];
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedModule(): void { $this->resetPage(); }
    public function updatedAction(): void { $this->resetPage(); }
    public function updatedUserId(): void { $this->resetPage(); }
    public function updatedFrom(): void { $this->resetPage(); }
    public function updatedTo(): void { $this->resetPage(); }
    public function updatedPerPage(): void { $this->resetPage(); }

    public function openDeleteModal(): void
    {
        $this->deleteFrom = $this->from !== '' ? $this->from : now()->startOfMonth()->format('Y-m-d');
        $this->deleteTo = $this->to !== '' ? $this->to : now()->format('Y-m-d');
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
    }

    public function deleteLogsByDateRange(): void
    {
        $this->validate([
            'deleteFrom' => 'required|date',
            'deleteTo' => 'required|date|after_or_equal:deleteFrom',
        ], [
            'deleteFrom.required' => 'Tanggal mulai wajib diisi.',
            'deleteTo.required' => 'Tanggal akhir wajib diisi.',
            'deleteTo.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal mulai.',
        ]);

        $deleted = ActivityLog::query()
            ->whereIn('module', ['arus_kas', 'transfer_saldo'])
            ->whereDate('created_at', '>=', $this->deleteFrom)
            ->whereDate('created_at', '<=', $this->deleteTo)
            ->delete();

        $this->showDeleteModal = false;
        $this->resetPage();

        session()->flash(
            $deleted > 0 ? 'success' : 'error',
            $deleted > 0
                ? 'Berhasil menghapus ' . $deleted . ' log aktivitas dari ' . $this->deleteFrom . ' sampai ' . $this->deleteTo . '.'
                : 'Tidak ada log aktivitas pada rentang tanggal tersebut.'
        );
    }

    public function deleteAllLogs(): void
    {
        $deleted = ActivityLog::query()
            ->whereIn('module', ['arus_kas', 'transfer_saldo'])
            ->delete();

        $this->showDeleteModal = false;
        $this->resetPage();

        session()->flash(
            $deleted > 0 ? 'success' : 'error',
            $deleted > 0
                ? 'Berhasil menghapus semua log aktivitas (' . $deleted . ' data).'
                : 'Tidak ada log aktivitas untuk dihapus.'
        );
    }

    public function moduleLabel(string $module): string
    {
        return $module === 'arus_kas' ? 'Arus Kas' : 'Transfer Saldo';
    }

    public function actionLabel(string $action): string
    {
        return match ($action) {
            'create' => 'Tambah',
            'update' => 'Edit',
            'delete' => 'Hapus',
            default => ucfirst($action),
        };
    }
}; ?>

<div
    x-data="{
        confirmDeleteDateRange() {
            Swal.fire({
                title: 'Hapus log pada rentang ini?',
                text: 'Data log pada periode yang dipilih akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#6b7280',
            }).then((result) => {
                if (result.isConfirmed) {
                    $wire.deleteLogsByDateRange()
                }
            })
        },
        confirmDeleteAllLogs() {
            Swal.fire({
                title: 'Hapus semua log activity?',
                text: 'Semua log arus kas dan transfer saldo akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus semua',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
            }).then((result) => {
                if (result.isConfirmed) {
                    $wire.deleteAllLogs()
                }
            })
        }
    }"
>

    <div class="card mb-5">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h2 class="text-sm font-bold text-gray-700">Filter Log Activity</h2>
            <button
                type="button"
                wire:click="openDeleteModal"
                class="btn-danger whitespace-nowrap"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-7 0h8"/>
                </svg>
                Hapus Log
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            <div>
                <label class="label">Cari</label>
                <input type="text" wire:model.live.debounce.300ms="search" class="input" placeholder="Cari deskripsi / ID..." />
            </div>
            <div>
                <label class="label">Modul</label>
                <select wire:model.live="module" class="input">
                    <option value="">Semua</option>
                    <option value="arus_kas">Arus Kas</option>
                    <option value="transfer_saldo">Transfer Saldo</option>
                </select>
            </div>
            <div>
                <label class="label">Aksi</label>
                <select wire:model.live="action" class="input">
                    <option value="">Semua</option>
                    <option value="create">Tambah</option>
                    <option value="update">Edit</option>
                    <option value="delete">Hapus</option>
                </select>
            </div>
            <div>
                <label class="label">User</label>
                <select wire:model.live="userId" class="input">
                    <option value="">Semua</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Dari</label>
                <input type="date" wire:model.live="from" class="input" />
            </div>
            <div>
                <label class="label">Sampai</label>
                <input type="date" wire:model.live="to" class="input" />
            </div>
        </div>
    </div>

    <div class="card overflow-hidden p-0">
        <div class="sm:hidden divide-y divide-gray-100">
            @forelse($logs as $log)
            <div class="px-4 py-3 space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-400">{{ $log->created_at->isoFormat('D MMM Y HH:mm') }}</span>
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600">ID: {{ $log->id }}</span>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="px-2 py-0.5 rounded bg-primary-100 text-primary-700 font-semibold">{{ $this->moduleLabel($log->module) }}</span>
                    <span class="px-2 py-0.5 rounded {{ $log->action === 'delete' ? 'bg-red-100 text-red-700' : ($log->action === 'update' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }} font-semibold">{{ $this->actionLabel($log->action) }}</span>
                </div>
                <p class="text-sm text-gray-800">{{ $log->description ?? '-' }}</p>
                <p class="text-xs text-gray-500">Oleh: {{ $log->creator->name ?? '-' }}</p>
            </div>
            @empty
            <div class="px-4 py-12 text-center text-gray-400">Belum ada log aktivitas</div>
            @endforelse
        </div>

        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">Waktu</th>
                        <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">Modul</th>
                        <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">Aksi</th>
                        <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">Entity</th>
                        <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">Deskripsi</th>
                        <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">User</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-gray-600 text-xs whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-primary-100 text-primary-700">
                                {{ $this->moduleLabel($log->module) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $log->action === 'delete' ? 'bg-red-100 text-red-700' : ($log->action === 'update' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                {{ $this->actionLabel($log->action) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $log->entity_type }}#{{ $log->entity_id }}</td>
                        <td class="px-4 py-3 text-gray-800">{{ $log->description ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $log->creator->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-400">Belum ada log aktivitas</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200">
            {{ $logs->links() }}
        </div>
    </div>

    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" wire:click="closeDeleteModal"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white shadow-xl border border-gray-200 p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Hapus Log Activity</h3>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Pilih hapus berdasarkan rentang tanggal atau hapus semua log arus kas dan transfer saldo.</p>
                </div>
                <button type="button" wire:click="closeDeleteModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="space-y-3">
                <div class="rounded-xl border border-gray-200 p-3.5 sm:p-4">
                    <p class="text-sm font-semibold text-gray-800 mb-3">Hapus Rentang Tanggal</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="label">Dari</label>
                            <input type="date" wire:model="deleteFrom" class="input" />
                            @error('deleteFrom') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="label">Sampai</label>
                            <input type="date" wire:model="deleteTo" class="input" />
                            @error('deleteTo') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="mt-3 flex justify-end">
                        <button type="button" x-on:click="confirmDeleteDateRange()" class="btn-secondary border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100">
                            Hapus Rentang Tanggal
                        </button>
                    </div>
                </div>

                <div class="rounded-xl border border-red-200 bg-red-50 p-3.5 sm:p-4">
                    <p class="text-sm font-semibold text-red-700 mb-1">Hapus Semua Log</p>
                    <p class="text-xs sm:text-sm text-red-600">Aksi ini akan menghapus seluruh log activity untuk arus kas dan transfer saldo.</p>
                    <div class="mt-3 flex justify-end">
                        <button type="button" x-on:click="confirmDeleteAllLogs()" class="btn-danger">
                            Hapus Semua Log
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
