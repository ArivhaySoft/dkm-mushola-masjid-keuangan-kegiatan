<?php

return [
    'name'     => env('APP_NAME', 'Keuangan Mushola'),
    'env'      => env('APP_ENV', 'production'),
    'debug'    => (bool) env('APP_DEBUG', false),
    'url'      => env('APP_URL', 'http://localhost'),
    'timezone' => 'Asia/Jakarta',
    'locale'   => 'id',
    'fallback_locale' => 'en',
    'faker_locale'    => 'id_ID',
    'admin_secret'    => env('ADMIN_SECRET'),
    'cipher'   => 'AES-256-CBC',
    'key'      => env('APP_KEY'),
    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],
    'maintenance' => ['driver' => 'file'],
    'providers' => \Illuminate\Support\ServiceProvider::defaultProviders()->merge([
        App\Providers\AppServiceProvider::class,
    ])->toArray(),
    'aliases' => \Illuminate\Support\Facades\Facade::defaultAliases()->merge([
        'Excel' => \Maatwebsite\Excel\Facades\Excel::class,
        'PDF'   => \Barryvdh\DomPDF\Facade\Pdf::class,
    ])->toArray(),
];
