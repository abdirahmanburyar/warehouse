<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use App\Observers\AssetObserver;
use App\Models\Asset;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Asset::observe(AssetObserver::class);
    }
}
