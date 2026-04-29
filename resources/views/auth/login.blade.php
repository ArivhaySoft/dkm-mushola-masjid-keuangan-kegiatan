<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login – {{ \App\Models\Setting::get('app_name', 'Keuangan Mushola') }}</title>
    @php $__favicon = \App\Models\Setting::get('foto_mushola'); @endphp
    <link rel="icon" href="{{ $__favicon ? Storage::url($__favicon) : '/favicon.svg' }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <x-theme-style />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: {
                        primary: {
                            50:  'var(--color-primary-50)',
                            100: 'var(--color-primary-100)',
                            200: 'var(--color-primary-200)',
                            600: 'var(--color-primary-600)',
                            700: 'var(--color-primary-700)',
                            800: 'var(--color-primary-800)',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-primary-800 via-primary-700 to-emerald-600 flex items-center justify-center p-4 font-sans">

    {{-- Decorative circles --}}
    <div class="absolute top-0 left-0 w-72 h-72 bg-white/5 rounded-full -translate-x-1/3 -translate-y-1/3"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-white/5 rounded-full translate-x-1/3 translate-y-1/3"></div>

    <div class="relative w-full max-w-sm">
        {{-- Card --}}
        <div class="bg-white rounded-3xl shadow-2xl p-8">
            {{-- Icon --}}
            @php $loginFoto = \App\Models\Setting::get('foto_mushola'); @endphp
            <div class="flex justify-center mb-6">
                @if($loginFoto)
                <img src="{{ Storage::url($loginFoto) }}" class="w-16 h-16 rounded-2xl object-cover shadow-lg">
                @else
                <div class="w-16 h-16 bg-primary-800 rounded-2xl flex items-center justify-center shadow-lg">
                    <svg class="w-9 h-9 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M3 12l9-9 9 9M5 10v9a1 1 0 001 1h4v-5h4v5h4a1 1 0 001-1v-9"/>
                    </svg>
                </div>
                @endif
            </div>

            <h1 class="text-2xl font-extrabold text-gray-900 text-center mb-1">{{ \App\Models\Setting::get('app_name', 'Keuangan Mushola') }}</h1>
            <p class="text-sm text-gray-500 text-center mb-8">Sistem Pencatatan Keuangan Masjid & Mushola</p>

            @if(session('error'))
            <div class="mb-4 bg-red-50 text-red-700 text-sm rounded-xl px-4 py-3 border border-red-200">
                {{ session('error') }}
            </div>
            @endif

            {{-- Email/Password Form --}}
            <form method="POST" action="{{ route('login.email') }}" class="space-y-3 mb-4">
                @csrf
                <div>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email"
                           class="w-full border {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-200' }} rounded-2xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-transparent transition" />
                    @error('email')
                    <p class="mt-1 text-xs text-red-600 px-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <input type="password" name="password" placeholder="Password"
                           class="w-full border border-gray-200 rounded-2xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-transparent transition" />
                </div>
                <button type="submit"
                        class="w-full bg-primary-700 hover:bg-primary-800 text-white font-semibold py-3.5 rounded-2xl transition-all duration-200">
                    Masuk
                </button>
            </form>

            {{-- Divider --}}
            <div class="flex items-center gap-3 mb-4">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-xs text-gray-400 font-medium">atau</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            {{-- Google Login --}}
            <a href="{{ route('auth.google') }}"
               class="flex items-center justify-center gap-3 w-full border-2 border-gray-200 hover:border-primary-600 hover:bg-primary-50 text-gray-700 hover:text-primary-800 font-semibold py-3.5 rounded-2xl transition-all duration-200 group">
                <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Masuk dengan Google
            </a>

            <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                <p class="text-xs text-gray-400">
                    Masuk untuk melihat laporan keuangan<br>
                    dan mengelola data organisasi.
                </p>
                <a href="{{ route('home') }}" class="inline-block mt-3 text-xs text-primary-600 font-semibold hover:underline">← Kembali ke Beranda</a>
            </div>
        </div>

        <p class="text-center text-white/60 text-xs mt-6">
            © {{ date('Y') }} {{ \App\Models\Setting::get('nama_mushola', '') }} · {{ \App\Models\Setting::get('app_name', 'Keuangan Mushola') }}
        </p>
    </div>
</body>
</html>
