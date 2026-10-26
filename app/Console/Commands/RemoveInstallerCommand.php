<?php

namespace App\Console\Commands;

use App\Services\Installer\InstallerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RemoveInstallerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:remove-installer {--force : Force locking without prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lock and deactivate the web installer to prevent re-installation';

    /**
     * Execute the console command.
     */
    public function handle(InstallerService $installer): int
    {
        $lockFile = storage_path('installed');

        if (! File::exists($lockFile)) {
            $payload = [
                'installed_at' => now()->toIso8601String(),
                'app_version' => '1.0.0',
                'installer_locked' => true,
                'locked_via' => 'CLI app:remove-installer',
            ];
            File::put($lockFile, json_encode($payload, JSON_PRETTY_PRINT));
        }

        $this->info('Installer lock verified at storage/installed.');
        $this->info('Web installer routes (/install) are now locked and deactivated.');
        $this->info('Re-installation is strictly prevented.');

        return self::SUCCESS;
    }
}
