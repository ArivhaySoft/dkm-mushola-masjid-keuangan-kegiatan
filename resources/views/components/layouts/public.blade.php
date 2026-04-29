@props(['title' => 'Beranda'])
@php $__favicon = \App\Models\Setting::get('foto_mushola'); @endphp
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title }} – {{ \App\Models\Setting::get('app_name', config('app.name', 'Keuangan Mushola')) }}</title>
    <link rel="icon" href="{{ $__favicon ? Storage::url($__favicon) : '/favicon.svg' }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-theme-style />
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-50">

{{-- Navbar --}}
<nav class="bg-primary-800 text-white sticky top-0 z-30 shadow-lg">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-3">
            @php $fotoMushola = \App\Models\Setting::get('foto_mushola'); @endphp
            @if($fotoMushola)
            <img src="{{ Storage::url($fotoMushola) }}" class="w-9 h-9 rounded-xl object-cover shadow">
            @else
            <div class="w-9 h-9 rounded-xl bg-gold-500 flex items-center justify-center shadow">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v9a1 1 0 001 1h4v-5h4v5h4a1 1 0 001-1v-9"/>
                </svg>
            </div>
            @endif
            <div>
                <p class="font-extrabold text-sm leading-tight">{{ \App\Models\Setting::get('app_name', 'Keuangan Mushola') }}</p>
                @php $namaMushola = \App\Models\Setting::get('nama_mushola', ''); @endphp
                @if($namaMushola)
                <p class="text-xs text-primary-300">{{ $namaMushola }}</p>
                @endif
            </div>
        </a>
        <div class="flex items-center gap-3">
            @auth
            <a href="{{ route('dashboard') }}" class="text-xs sm:text-sm font-semibold text-primary-200 hover:text-white transition px-3 py-1.5 rounded-xl hover:bg-white/10">
                <svg class="w-4 h-4 inline -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            @else
            <a href="{{ route('login') }}" class="text-xs sm:text-sm font-semibold bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-xl transition">
                Masuk
            </a>
            @endauth
        </div>
    </div>
</nav>

{{-- Content --}}
<main class="max-w-6xl mx-auto px-4 py-6">
    {{ $slot }}
</main>

@livewireScripts
<script>
if (navigator.geolocation && !sessionStorage.getItem('geo_sent')) {
    navigator.geolocation.getCurrentPosition(function(pos) {
        fetch('{{ route("visitor.geo") }}', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
            body: JSON.stringify({lat: pos.coords.latitude, lng: pos.coords.longitude})
        }).then(function(){ sessionStorage.setItem('geo_sent','1'); });
    }, function(){}, {timeout: 5000});
}
</script>
</body>
</html>
