<?php

use App\Models\Setting;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;

    public function render(): mixed
    {
        return parent::render()->title('Pengaturan Aplikasi');
    }

    public string $app_name = '';
    public string $nama_mushola = '';
    public $foto_mushola;
    public string $current_foto = '';

    public function mount(): void
    {
        $this->app_name = Setting::get('app_name', 'Keuangan Mushola');
        $this->nama_mushola = Setting::get('nama_mushola', '');
        $this->current_foto = Setting::get('foto_mushola', '') ?? '';
    }

    public function save(): void
    {
        $this->validate([
            'app_name' => 'required|string|max:100',
            'nama_mushola' => 'nullable|string|max:100',
            'foto_mushola' => 'nullable|image|max:2048',
        ]);

        Setting::set('app_name', $this->app_name);
        Setting::set('nama_mushola', $this->nama_mushola);

        if ($this->foto_mushola) {
            // Hapus foto lama jika ada
            if ($this->current_foto && Storage::disk('public')->exists($this->current_foto)) {
                Storage::disk('public')->delete($this->current_foto);
            }
            $path = $this->foto_mushola->store('settings', 'public');
            Setting::set('foto_mushola', $path);
            $this->current_foto = $path;
            $this->reset('foto_mushola');
        }

        session()->flash('success', 'Pengaturan berhasil disimpan.');
    }

    public function removeFoto(): void
    {
        if ($this->current_foto && Storage::disk('public')->exists($this->current_foto)) {
            Storage::disk('public')->delete($this->current_foto);
        }
        Setting::set('foto_mushola', '');
        $this->current_foto = '';
        session()->flash('success', 'Foto berhasil dihapus.');
    }
}; ?>

<div>
<div class="max-w-2xl mx-auto space-y-5">

    {{-- Info Banner --}}
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
        {{-- Identitas --}}
        <div class="card space-y-5">
            <h3 class="text-base font-bold text-gray-800">Identitas Organisasi</h3>

            {{-- Nama Aplikasi --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Aplikasi</label>
                <input type="text" wire:model="app_name" class="input" placeholder="Contoh: Keuangan Mushola">
                <p class="text-xs text-gray-400 mt-1">Ditampilkan di header, sidebar, dan halaman login.</p>
                @error('app_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Nama Mushola --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Mushola / Organisasi</label>
                <input type="text" wire:model="nama_mushola" class="input" placeholder="Contoh: Fajrul Iman">
                <p class="text-xs text-gray-400 mt-1">Ditampilkan di bawah nama aplikasi pada sidebar.</p>
                @error('nama_mushola') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Foto / Logo --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Logo / Foto Mushola</label>
                <div class="flex items-start gap-4">
                    {{-- Preview --}}
                    <div class="flex-shrink-0">
                        @if($foto_mushola)
                            <img src="{{ $foto_mushola->temporaryUrl() }}" class="w-20 h-20 rounded-2xl object-cover border-2 border-primary-200 shadow">
                        @elseif($current_foto)
                            <img src="{{ Storage::url($current_foto) }}" class="w-20 h-20 rounded-2xl object-cover border-2 border-primary-200 shadow">
                        @else
                            <div class="w-20 h-20 rounded-2xl bg-gray-100 flex items-center justify-center border-2 border-dashed border-gray-300">
                                <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 space-y-2">
                        <input type="file" wire:model="foto_mushola" accept="image/*"
                               class="block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer">
                        <p class="text-xs text-gray-400">Format: JPG, PNG, WebP. Maksimal 2MB.</p>
                        @if($current_foto)
                        <button type="button" wire:click="removeFoto" wire:confirm="Hapus foto ini?" class="text-xs text-red-500 hover:text-red-700 font-medium">
                            Hapus foto
                        </button>
                        @endif
                        @error('foto_mushola') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Save Button --}}
        <div class="mt-5">
            <button type="submit" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Pengaturan
            </button>
        </div>
    </form>

</div>
</div>
