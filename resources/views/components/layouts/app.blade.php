@props(['title' => 'Beranda'])
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title }} – {{ \App\Models\Setting::get('app_name', config('app.name', 'Keuangan Mushola')) }}</title>
    @php $__favicon = \App\Models\Setting::get('foto_mushola'); @endphp
    <link rel="icon" href="{{ $__favicon ? Storage::url($__favicon) : '/favicon.svg' }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-gray-50">
<div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: window.innerWidth >= 1024 }" x-init="window.addEventListener('resize', () => { sidebarOpen = window.innerWidth >= 1024 })">
    <!-- Mobile Overlay -->
    <div class="fixed inset-0 z-20 bg-black/50 lg:hidden" x-show="sidebarOpen" @click="sidebarOpen = false"
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:leave="transition-opacity ease-linear duration-200"></div>

    <!-- Sidebar Component -->
    <x-sidebar />

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col overflow-hidden min-w-0">
        <!-- Header Component -->
        <x-header :title="$title ?? 'Beranda'" />

        <!-- Page Content -->
        <main class="flex-1 overflow-y-auto p-4 lg:p-6 bg-gray-50">
            {{ $slot }}
        </main>
    </div>
</div>

@livewireScripts
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('swal', (params) => {
        const p = Array.isArray(params) ? params[0] : params;
        Swal.fire({
            icon: p.type || 'success',
            title: p.type === 'error' ? 'Gagal!' : (p.type === 'warning' ? 'Perhatian!' : 'Berhasil!'),
            text: p.message || '',
            timer: p.timer ?? 2500,
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
        });
    });
});
</script>
@if(session('success') || session('error'))
<script>
document.addEventListener('DOMContentLoaded', () => {
    @if(session('success'))
    Swal.fire({ icon: 'success', title: 'Berhasil!', text: @js(session('success')), timer: 2500, showConfirmButton: false, toast: true, position: 'top-end' });
    @endif
    @if(session('error'))
    Swal.fire({ icon: 'error', title: 'Gagal!', text: @js(session('error')), timer: 3000, showConfirmButton: false, toast: true, position: 'top-end' });
    @endif
});
</script>
@endif
</body>
</html>
