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
@if(session('success') || session('error'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
     class="fixed bottom-4 right-4 z-50 max-w-sm">
    @if(session('success'))
    <div class="flex items-center gap-3 bg-white border border-emerald-200 rounded-2xl shadow-lg p-4">
        <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        </div>
        <p class="text-sm text-gray-700 font-medium">{{ session('success') }}</p>
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 bg-white border border-red-200 rounded-2xl shadow-lg p-4">
        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
        </div>
        <p class="text-sm text-gray-700 font-medium">{{ session('error') }}</p>
    </div>
    @endif
</div>
@endif
</body>
</html>
