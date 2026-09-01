<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        if (
            request()->server->get('HTTP_X_FORWARDED_PROTO') === 'https' ||
            request()->isSecure() ||
            str_contains(request()->header('host') ?? '', 'devtunnels.ms') ||
            str_contains(request()->header('host') ?? '', 'ngrok') ||
            app()->environment('production')
        ) {
            URL::forceScheme('https');
        }
    }
}
