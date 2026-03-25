@if(session('success') || session('error') || session('warning') || session('info'))
<div
    x-data="{ show: true }"
    x-show="show"
    x-init="setTimeout(() => show = false, 4500)"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed bottom-4 right-4 z-50 max-w-sm w-full"
>
    @if(session('success'))
    <div class="flex items-start gap-3 bg-white border border-emerald-200 rounded-2xl shadow-lg p-4">
        <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-800">Berhasil</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ session('success') }}</p>
        </div>
        <button @click="show=false" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="flex items-start gap-3 bg-white border border-red-200 rounded-2xl shadow-lg p-4">
        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-800">Terjadi Kesalahan</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ session('error') }}</p>
        </div>
        <button @click="show=false" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    @endif

    @if(session('warning'))
    <div class="flex items-start gap-3 bg-white border border-yellow-200 rounded-2xl shadow-lg p-4">
        <div class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-800">Perhatian</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ session('warning') }}</p>
        </div>
        <button @click="show=false" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    @endif
</div>
@endif
