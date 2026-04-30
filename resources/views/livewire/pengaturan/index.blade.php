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

    public bool   $widget_jadwal_pengajian = true;
    public bool   $widget_posisi_keuangan  = true;
    public bool   $widget_kegiatan         = true;
    public bool   $widget_pengunjung       = true;
    public string $theme_color             = 'hijau';

    public function mount(): void
    {
        $this->app_name     = Setting::get('app_name', 'Keuangan Mushola');
        $this->nama_mushola = Setting::get('nama_mushola', '');
        $this->current_foto = Setting::get('foto_mushola', '') ?? '';

        $this->widget_jadwal_pengajian = Setting::get('widget_jadwal_pengajian', '1') !== '0';
        $this->widget_posisi_keuangan  = Setting::get('widget_posisi_keuangan',  '1') !== '0';
        $this->widget_kegiatan         = Setting::get('widget_kegiatan',         '1') !== '0';
        $this->widget_pengunjung       = Setting::get('widget_pengunjung',        '1') !== '0';
        $this->theme_color             = Setting::get('theme_color', 'hijau');
    }

    public function save(): void
    {
        $this->validate([
            'app_name'     => 'required|string|max:100',
            'nama_mushola' => 'nullable|string|max:100',
            'foto_mushola' => 'nullable|image|max:2048',
        ]);

        Setting::set('app_name', $this->app_name);
        Setting::set('nama_mushola', $this->nama_mushola);

        if ($this->foto_mushola) {
            if ($this->current_foto && Storage::disk('public')->exists($this->current_foto)) {
                Storage::disk('public')->delete($this->current_foto);
            }
            $path = $this->foto_mushola->store('settings', 'public');
            Setting::set('foto_mushola', $path);
            $this->current_foto = $path;
            $this->reset('foto_mushola');
        }

        Setting::set('widget_jadwal_pengajian', $this->widget_jadwal_pengajian ? '1' : '0');
        Setting::set('widget_posisi_keuangan',  $this->widget_posisi_keuangan  ? '1' : '0');
        Setting::set('widget_kegiatan',         $this->widget_kegiatan         ? '1' : '0');
        Setting::set('widget_pengunjung',       $this->widget_pengunjung        ? '1' : '0');
        Setting::set('theme_color',             $this->theme_color);

        $this->dispatch('swal', ['type' => 'success', 'message' => 'Pengaturan berhasil disimpan.']);
    }

    public function removeFoto(): void
    {
        if ($this->current_foto && Storage::disk('public')->exists($this->current_foto)) {
            Storage::disk('public')->delete($this->current_foto);
        }
        Setting::set('foto_mushola', '');
        $this->current_foto = '';
        $this->dispatch('swal', ['type' => 'success', 'message' => 'Foto berhasil dihapus.']);
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
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Organisasi</label>
                <input type="text" wire:model="nama_mushola" class="input" placeholder="Contoh: Fajrul Iman">
                <p class="text-xs text-gray-400 mt-1">Ditampilkan di bawah nama aplikasi pada sidebar.</p>
                @error('nama_mushola') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Foto / Logo --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Logo / Foto Organisasi</label>
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

        {{-- Tema Warna --}}
        <div class="card mt-5 space-y-4">
            <div>
                <h3 class="text-base font-bold text-gray-800">Tema Warna</h3>
                <p class="text-xs text-gray-400 mt-0.5">Warna utama yang digunakan di seluruh aplikasi.</p>
            </div>

            @php
            $themes = [
                ['key' => 'hijau',   'label' => 'Hijau',   'hex' => '#277a5a'],
                ['key' => 'biru',    'label' => 'Biru',    'hex' => '#2563eb'],
                ['key' => 'ungu',    'label' => 'Ungu',    'hex' => '#9333ea'],
                ['key' => 'merah',   'label' => 'Merah',   'hex' => '#e11d48'],
                ['key' => 'oranye',  'label' => 'Oranye',  'hex' => '#ea580c'],
                ['key' => 'teal',    'label' => 'Teal',    'hex' => '#0d9488'],
                ['key' => 'slate',   'label' => 'Abu-abu', 'hex' => '#475569'],
                ['key' => 'emas',    'label' => 'Emas',    'hex' => '#d97706'],
            ];
            @endphp

            <div class="flex flex-wrap gap-3">
                @foreach($themes as $t)
                <button type="button"
                        wire:click="$set('theme_color', '{{ $t['key'] }}')"
                        title="{{ $t['label'] }}"
                        class="flex flex-col items-center gap-1.5 group">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-150 ring-offset-2
                        {{ $theme_color === $t['key'] ? 'ring-2 ring-offset-2 scale-110' : 'hover:scale-105' }}"
                        style="background: {{ $t['hex'] }}; {{ $theme_color === $t['key'] ? 'ring-color:' . $t['hex'] . ';' : '' }}">
                        @if($theme_color === $t['key'])
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                        @endif
                    </span>
                    <span class="text-xs font-medium {{ $theme_color === $t['key'] ? 'text-gray-800' : 'text-gray-400' }}">
                        {{ $t['label'] }}
                    </span>
                </button>
                @endforeach
            </div>
        </div>

        {{-- Widget Beranda --}}
        <div class="card mt-5 space-y-4">
            <div>
                <h3 class="text-base font-bold text-gray-800">Widget Halaman Publik</h3>
                <p class="text-xs text-gray-400 mt-0.5">Pilih widget yang ditampilkan di halaman beranda publik.</p>
            </div>

            @php
            $widgets = [
                ['key' => 'widget_jadwal_pengajian', 'label' => 'Jadwal Pengajian',  'desc' => 'Jadwal pengajian 2 mingguan dan bulanan'],
                ['key' => 'widget_posisi_keuangan',  'label' => 'Posisi Keuangan',   'desc' => 'Ringkasan kas bulan ini per kategori'],
                ['key' => 'widget_kegiatan',          'label' => 'Kegiatan',          'desc' => 'Grid foto dan informasi kegiatan'],
                ['key' => 'widget_pengunjung',        'label' => 'Statistik Pengunjung', 'desc' => 'Jumlah pengunjung hari ini, bulan ini, dan total'],
            ];
            @endphp

            <div class="divide-y divide-gray-100">
                @foreach($widgets as $w)
                <label class="flex items-center justify-between py-3 cursor-pointer group">
                    <div>
                        <p class="text-sm font-semibold text-gray-800 group-hover:text-primary-700 transition-colors">{{ $w['label'] }}</p>
                        <p class="text-xs text-gray-400">{{ $w['desc'] }}</p>
                    </div>
                    <button type="button"
                            wire:click="$toggle('{{ $w['key'] }}')"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none {{ $this->{$w['key']} ? 'bg-primary-600' : 'bg-gray-200' }}">
                        <span class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition duration-200 {{ $this->{$w['key']} ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </label>
                @endforeach
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
