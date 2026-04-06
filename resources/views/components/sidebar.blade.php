<!-- Sidebar -->
<aside class="fixed lg:static inset-y-0 left-0 z-30 w-64 bg-primary-800 text-white flex flex-col"
       x-show="sidebarOpen"
       x-cloak
       x-transition:enter="transition ease-in-out duration-200"
       x-transition:enter-start="-translate-x-full"
       x-transition:enter-end="translate-x-0"
       x-transition:leave="transition ease-in-out duration-200"
       x-transition:leave-start="translate-x-0"
       x-transition:leave-end="-translate-x-full"
       @click.away="if (window.innerWidth < 1024) sidebarOpen = false">

    <div class="flex items-center gap-3 px-4 py-4 border-b border-white/10">
        @php $fotoMushola = \App\Models\Setting::get('foto_mushola'); @endphp
        @if($fotoMushola)
        <img src="{{ Storage::url($fotoMushola) }}" class="w-10 h-10 rounded-xl object-cover shadow-lg flex-shrink-0">
        @else
        <div class="w-10 h-10 rounded-xl bg-gold-500 flex items-center justify-center shadow-lg flex-shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v9a1 1 0 001 1h4v-5h4v5h4a1 1 0 001-1v-9"/>
            </svg>
        </div>
        @endif
        <div class="min-w-0">
            <p class="font-extrabold text-sm text-white leading-tight">{{ \App\Models\Setting::get('app_name', 'Keuangan Mushola') }}</p>
            <p class="text-xs text-primary-300">{{ \App\Models\Setting::get('nama_mushola', '') }}</p>
        </div>
    </div>

    <nav class="flex-1 px-3 py-3 space-y-0.5 overflow-y-auto">
        <a href="{{ route('home') }}" class="sidebar-link {{ request()->routeIs('home') ? 'active' : 'text-primary-200' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Halaman Publik
        </a>
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : 'text-primary-200' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>
        <a href="{{ route('arus-kas') }}" class="sidebar-link {{ request()->routeIs('arus-kas') ? 'active' : 'text-primary-200' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Arus Kas
        </a>
        <a href="{{ route('transfer.index') }}" class="sidebar-link {{ request()->routeIs('transfer.*') ? 'active' : 'text-primary-200' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            Transfer Rekening
        </a>

        <div class="pt-3 pb-1"><p class="px-3 text-xs font-bold text-primary-400 uppercase tracking-widest">Laporan</p></div>

        <a href="{{ route('laporan.periodik') }}" class="sidebar-link {{ request()->routeIs('laporan.periodik') ? 'active' : 'text-primary-200' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Periodik
        </a>
        <a href="{{ route('laporan.bulanan') }}" class="sidebar-link {{ request()->routeIs('laporan.bulanan') ? 'active' : 'text-primary-200' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Bulanan
        </a>
        <a href="{{ route('laporan.tahunan') }}" class="sidebar-link {{ request()->routeIs('laporan.tahunan') ? 'active' : 'text-primary-200' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Tahunan
        </a>
        <a href="{{ route('laporan.mutasi-rekening') }}" class="sidebar-link {{ request()->routeIs('laporan.mutasi-rekening') ? 'active' : 'text-primary-200' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Mutasi Rekening
        </a>

        <div class="pt-3 pb-1"><p class="px-3 text-xs font-bold text-primary-400 uppercase tracking-widest">Lainnya</p></div>

        <a href="{{ route('kegiatan') }}" class="sidebar-link {{ request()->routeIs('kegiatan') ? 'active' : 'text-primary-200' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            Kegiatan
        </a>

        @if(auth()->user()?->isAdmin())
        <div class="pt-3 pb-1"><p class="px-3 text-xs font-bold text-primary-400 uppercase tracking-widest">Admin</p></div>
        <a href="{{ route('master.rekening') }}" class="sidebar-link {{ request()->routeIs('master.rekening') ? 'active' : 'text-primary-200' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            Master Rekening
        </a>
        <a href="{{ route('master.kategori') }}" class="sidebar-link {{ request()->routeIs('master.kategori') ? 'active' : 'text-primary-200' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Master Kategori Kas
        </a>
        @endif
        @if(auth()->user()?->isAdmin() || auth()->user()?->isEditor())
        <a href="{{ route('master.jenis-kegiatan') }}" class="sidebar-link {{ request()->routeIs('master.jenis-kegiatan') ? 'active' : 'text-primary-200' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            Jenis Kegiatan
        </a>
        @endif
        @if(auth()->user()?->isAdmin())
        <a href="{{ route('hak-akses') }}" class="sidebar-link {{ request()->routeIs('hak-akses') ? 'active' : 'text-primary-200' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            Hak Akses
        </a>
        <a href="{{ route('reset-data') }}" class="sidebar-link {{ request()->routeIs('reset-data') ? 'active' : 'text-primary-200' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Reset Data
        </a>
        <a href="{{ route('pengaturan') }}" class="sidebar-link {{ request()->routeIs('pengaturan') ? 'active' : 'text-primary-200' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Pengaturan
        </a>
        @endif
    </nav>

    <div class="p-3 border-t border-white/10">
        <a href="{{ route('profile') }}" class="flex items-center gap-3 p-2 rounded-xl hover:bg-white/10 transition">
            @if(auth()->user()?->avatar)
                <img src="{{ auth()->user()->avatar }}" class="w-9 h-9 rounded-full ring-2 ring-gold-400 flex-shrink-0">
            @else
                <div class="w-9 h-9 rounded-full bg-primary-600 flex items-center justify-center font-bold flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-white truncate leading-tight">{{ auth()->user()?->name }}</p>
                <p class="text-xs text-primary-300 truncate">{{ auth()->user()?->roles->pluck('label')->join(', ') ?: 'Viewer' }}</p>
            </div>
        </a>
        <form method="POST" action="{{ route('logout') }}" class="mt-1">
            @csrf
            <button class="w-full flex items-center gap-2 px-3 py-2 text-xs text-primary-300 hover:text-white hover:bg-white/10 rounded-xl transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Keluar
            </button>
        </form>
    </div>
</aside>
