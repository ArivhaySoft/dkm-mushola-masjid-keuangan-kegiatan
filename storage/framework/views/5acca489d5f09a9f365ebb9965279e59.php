<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title' => 'Beranda']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['title' => 'Beranda']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>
<?php $__favicon = \App\Models\Setting::get('foto_mushola'); ?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo e($title); ?> – <?php echo e(\App\Models\Setting::get('app_name', config('app.name', 'Keuangan Mushola'))); ?></title>
    <link rel="icon" href="<?php echo e($__favicon ? Storage::url($__favicon) : '/favicon.svg'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>
<body class="min-h-screen bg-gray-50">


<nav class="bg-primary-800 text-white sticky top-0 z-30 shadow-lg">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="<?php echo e(route('home')); ?>" class="flex items-center gap-3">
            <?php $fotoMushola = \App\Models\Setting::get('foto_mushola'); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($fotoMushola): ?>
            <img src="<?php echo e(Storage::url($fotoMushola)); ?>" class="w-9 h-9 rounded-xl object-cover shadow">
            <?php else: ?>
            <div class="w-9 h-9 rounded-xl bg-gold-500 flex items-center justify-center shadow">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v9a1 1 0 001 1h4v-5h4v5h4a1 1 0 001-1v-9"/>
                </svg>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div>
                <p class="font-extrabold text-sm leading-tight"><?php echo e(\App\Models\Setting::get('app_name', 'Keuangan Mushola')); ?></p>
                <?php $namaMushola = \App\Models\Setting::get('nama_mushola', ''); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($namaMushola): ?>
                <p class="text-xs text-primary-300"><?php echo e($namaMushola); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </a>
        <div class="flex items-center gap-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
            <a href="<?php echo e(route('dashboard')); ?>" class="text-xs sm:text-sm font-semibold text-primary-200 hover:text-white transition px-3 py-1.5 rounded-xl hover:bg-white/10">
                <svg class="w-4 h-4 inline -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <?php else: ?>
            <a href="<?php echo e(route('login')); ?>" class="text-xs sm:text-sm font-semibold bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-xl transition">
                Masuk
            </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</nav>


<main class="max-w-6xl mx-auto px-4 py-6">
    <?php echo e($slot); ?>

</main>

<?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

<script>
if (navigator.geolocation && !sessionStorage.getItem('geo_sent')) {
    navigator.geolocation.getCurrentPosition(function(pos) {
        fetch('<?php echo e(route("visitor.geo")); ?>', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'<?php echo e(csrf_token()); ?>'},
            body: JSON.stringify({lat: pos.coords.latitude, lng: pos.coords.longitude})
        }).then(function(){ sessionStorage.setItem('geo_sent','1'); });
    }, function(){}, {timeout: 5000});
}
</script>
</body>
</html>
<?php /**PATH /srv/apkpulsa/web/mushola-finance/resources/views/components/layouts/public.blade.php ENDPATH**/ ?>