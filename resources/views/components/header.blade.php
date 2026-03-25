<!-- Header -->
<header class="bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-3 sticky top-0 z-10 flex-shrink-0">
    <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-xl hover:bg-gray-100 transition">
        <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <div class="flex-1 min-w-0">
        <h1 class="text-base font-bold text-gray-800 truncate">{{ $title ?? 'Beranda' }}</h1>
    </div>
    <div class="hidden sm:flex items-center gap-3 text-right flex-shrink-0">
        <div>
            <p class="text-xs font-semibold text-gray-600">{{ now()->isoFormat('dddd') }}</p>
            <p class="text-xs text-gray-400">{{ now()->isoFormat('D MMM Y') }}</p>
        </div>
        @if(auth()->user()?->avatar)
        <a href="{{ route('profile') }}" class="flex-shrink-0">
            <img src="{{ auth()->user()->avatar }}" class="w-8 h-8 rounded-full ring-2 ring-primary-200">
        </a>
        @endif
    </div>
</header>
