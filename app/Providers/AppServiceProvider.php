<?php

namespace App\Providers;

use Illuminate\Encryption\Encrypter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->ensureEnvironmentAndKey();

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

    /**
     * Ensure .env exists, required storage directories exist, and an application encryption key is configured.
     */
    protected function ensureEnvironmentAndKey(): void
    {
        // 1. Ensure required storage and cache directories exist with appropriate permissions
        $requiredDirs = [
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('framework/cache'),
            storage_path('framework/cache/data'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ];
        foreach ($requiredDirs as $dir) {
            if (! is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
        }

        // 2. Ensure .env file exists (fallback to .env.example on fresh server downloads)
        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            $examplePath = base_path('.env.example');
            if (file_exists($examplePath)) {
                @copy($examplePath, $envPath);
            }
        }

        // 3. Ensure application encryption key exists to prevent MissingAppKeyException (HTTP 500)
        if (empty(config('app.key'))) {
            $cipher = config('app.cipher', 'AES-256-CBC');
            $generatedKey = 'base64:'.base64_encode(Encrypter::generateKey($cipher));
            config(['app.key' => $generatedKey]);

            if (file_exists($envPath) && is_writable($envPath)) {
                $envContent = @file_get_contents($envPath);
                if ($envContent !== false) {
                    if (preg_match('/^APP_KEY=.*$/m', $envContent)) {
                        $envContent = preg_replace('/^APP_KEY=.*$/m', "APP_KEY={$generatedKey}", $envContent);
                    } else {
                        $envContent .= "\nAPP_KEY={$generatedKey}";
                    }
                    @file_put_contents($envPath, $envContent);
                }
            }
        }

        // 4. If default database is SQLite and uninstalled, ensure SQLite database file exists
        if (! file_exists(storage_path('installed'))) {
            $sqlitePath = config('database.connections.sqlite.database');
            if (config('database.default') === 'sqlite' && is_string($sqlitePath) && $sqlitePath !== ':memory:') {
                $dir = dirname($sqlitePath);
                if (! is_dir($dir)) {
                    @mkdir($dir, 0755, true);
                }
                if (! file_exists($sqlitePath)) {
                    @touch($sqlitePath);
                }
            }
        }
    }
}
