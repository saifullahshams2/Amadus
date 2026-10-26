<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // When application is not yet installed, fallback session and cache to file storage
        // to prevent database table lookup failures before migrations have run.
        if (! file_exists(storage_path('installed')) && ! $this->app->environment('testing')) {
            if (config('session.driver') === 'database') {
                config(['session.driver' => 'file']);
            }
            if (config('cache.default') === 'database') {
                config(['cache.default' => 'file']);
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
