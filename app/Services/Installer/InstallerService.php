<?php

namespace App\Services\Installer;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class InstallerService
{
    /**
     * Determine if the application has already been installed.
     */
    public function isInstalled(): bool
    {
        return File::exists(storage_path('installed'));
    }

    /**
     * Check server environment requirements and directory permissions.
     *
     * @return array{
     *     satisfied: bool,
     *     php_version: array{required: string, current: string, satisfied: bool},
     *     extensions: array<string, bool>,
     *     directories: array<string, bool>
     * }
     */
    public function checkRequirements(): array
    {
        $minPhpVersion = '8.2.0';
        $currentPhpVersion = PHP_VERSION;
        $phpSatisfied = version_compare($currentPhpVersion, $minPhpVersion, '>=');

        $requiredExtensions = [
            'pdo',
            'sqlite3',
            'openssl',
            'mbstring',
            'tokenizer',
            'xml',
            'ctype',
            'json',
            'curl',
            'fileinfo',
        ];

        $extensionResults = [];
        $extensionsSatisfied = true;
        foreach ($requiredExtensions as $ext) {
            $loaded = extension_loaded($ext);
            $extensionResults[$ext] = $loaded;
            if (! $loaded) {
                $extensionsSatisfied = false;
            }
        }

        $writablePaths = [
            'storage' => storage_path(),
            'storage/framework' => storage_path('framework'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];

        $directoryResults = [];
        $directoriesSatisfied = true;
        foreach ($writablePaths as $name => $path) {
            $isWritable = is_writable($path);
            $directoryResults[$name] = $isWritable;
            if (! $isWritable) {
                $directoriesSatisfied = false;
            }
        }

        $allSatisfied = $phpSatisfied && $extensionsSatisfied && $directoriesSatisfied;

        return [
            'satisfied' => $allSatisfied,
            'php_version' => [
                'required' => $minPhpVersion,
                'current' => $currentPhpVersion,
                'satisfied' => $phpSatisfied,
            ],
            'extensions' => $extensionResults,
            'directories' => $directoryResults,
        ];
    }

    /**
     * Run database migrations and seed reference data.
     */
    public function runMigrationsAndSeeders(): void
    {
        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('db:seed', ['--force' => true]);
    }

    /**
     * Create the primary flight instructor account.
     *
     * @param array{
     *     name: string,
     *     username: string,
     *     email: string,
     *     password: string,
     *     office_id?: string,
     *     agent_id?: string
     * } $data
     */
    public function createInstructor(array $data): User
    {
        return User::updateOrCreate(
            ['username' => $data['username']],
            [
                'name' => $data['name'] ?? 'Flight Instructor',
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'instructor',
                'office_id' => $data['office_id'] ?? 'LON1A0980',
                'agent_id' => $data['agent_id'] ?? 'IN01GS',
            ]
        );
    }

    /**
     * Mark the application as installed by writing the lock file.
     */
    public function markInstalled(User $instructor): void
    {
        $payload = [
            'installed_at' => now()->toIso8601String(),
            'app_version' => '1.0.0',
            'installed_by' => [
                'id' => $instructor->id,
                'username' => $instructor->username,
                'email' => $instructor->email,
                'office_id' => $instructor->office_id,
                'agent_id' => $instructor->agent_id,
            ],
        ];

        File::put(storage_path('installed'), json_encode($payload, JSON_PRETTY_PRINT));
    }

    /**
     * Remove the installation lock file (used for testing or resetting).
     */
    public function uninstall(): void
    {
        if ($this->isInstalled()) {
            File::delete(storage_path('installed'));
        }
    }
}
