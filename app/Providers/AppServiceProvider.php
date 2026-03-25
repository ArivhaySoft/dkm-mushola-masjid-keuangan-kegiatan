<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Livewire\Volt\Volt;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.tailwind');
        Paginator::defaultSimpleView('vendor.pagination.simple-tailwind');

        // Allow larger Livewire temporary uploads (e.g. video files up to 20MB).
        config([
            'livewire.temporary_file_upload.rules' => 'file|max:20480',
            'livewire.temporary_file_upload.max_upload_time' => 10,
        ]);

        Volt::mount([
            resource_path('views/livewire'),
        ]);
    }
}
