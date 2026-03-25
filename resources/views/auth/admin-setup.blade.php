<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Setup Administrator – {{ \App\Models\Setting::get('app_name', 'Keuangan Mushola') }}</title>
    @php $__favicon = \App\Models\Setting::get('foto_mushola'); @endphp
    <link rel="icon" href="{{ $__favicon ? Storage::url($__favicon) : '/favicon.svg' }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: {
                        primary: { 600: '#277a5a', 700: '#1f6148', 800: '#1b4d3a' }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-primary-800 via-primary-700 to-emerald-600 flex items-center justify-center p-4 font-sans">

    <div class="absolute top-0 left-0 w-72 h-72 bg-white/5 rounded-full -translate-x-1/3 -translate-y-1/3"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-white/5 rounded-full translate-x-1/3 translate-y-1/3"></div>

    <div class="relative w-full max-w-sm">
        <div class="bg-white rounded-3xl shadow-2xl p-8">
            <div class="flex justify-center mb-6">
                <div class="w-16 h-16 bg-amber-500 rounded-2xl flex items-center justify-center shadow-lg">
                    <svg class="w-9 h-9 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
            </div>

            <h1 class="text-xl font-extrabold text-gray-900 text-center mb-1">Setup Administrator</h1>
            <p class="text-sm text-gray-500 text-center mb-6">Anda adalah pengguna pertama. Masukkan password administrator untuk melanjutkan sebagai Admin.</p>

            @if(session('error'))
            <div class="mb-4 bg-red-50 text-red-700 text-sm rounded-xl px-4 py-3 border border-red-200">
                {{ session('error') }}
            </div>
            @endif

            <form method="POST" action="{{ route('admin-setup.store') }}">
                @csrf
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Password Administrator</label>
                    <input type="password" name="admin_secret" required autofocus
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl text-sm focus:border-primary-600 focus:outline-none transition"
                           placeholder="Masukkan password administrator" />
                    @error('admin_secret')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                        class="w-full bg-primary-700 hover:bg-primary-800 text-white font-bold py-3.5 rounded-2xl transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Aktifkan sebagai Administrator
                </button>
            </form>

            <div class="mt-6 pt-4 border-t border-gray-100 text-center">
                <a href="{{ route('login') }}" class="text-xs text-gray-400 hover:text-gray-600">← Kembali ke halaman login</a>
            </div>
        </div>
    </div>
</body>
</html>
