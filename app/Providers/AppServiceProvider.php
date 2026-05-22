<?php

namespace App\Providers;

use App\View\Composers\NavigationComposer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        Cache::forget('nav_brands');
        Cache::forget('nav_brands_v2');
        Cache::forget('nav_categories');
        Cache::forget('nav_categories_v2');

        View::composer('*', NavigationComposer::class);
    }
}
