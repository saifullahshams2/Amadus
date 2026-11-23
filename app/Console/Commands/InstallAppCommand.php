<?php

namespace App\Console\Commands;

use App\Services\Installer\InstallerService;
use Illuminate\Console\Command;

class InstallAppCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:install
                            {--name= : User full name}
                            {--username= : User login username}
                            {--email= : User email address}
                            {--password= : User account password}
                            {--office=LON1A0980 : Amadeus Office ID}
                            {--agent=1234AB : Agent Duty Code}
                            {--app-url= : Application URL / Domain (e.g. https://amadus.example.com)}
                            {--force : Force re-installation even if already installed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Amadus Training Platform and provision the single application user';

    /**
     * Execute the console command.
     */
    public function handle(InstallerService $installerService): int
    {
        $this->info('====================================================');
        $this->info('  Amadus Platform & Cash-Register Installer         ');
        $this->info('====================================================');

        if ($installerService->isInstalled() && ! $this->option('force')) {
            $this->warn('Application is already marked as installed.');
            $this->line('Use --force to overwrite and re-provision the user.');

            return self::SUCCESS;
        }

        // 1. Requirements Check
        $this->line('1. Checking system requirements...');
        $reqs = $installerService->checkRequirements();
        if (! $reqs['satisfied'] && ! $this->option('force')) {
            $this->error('System does not satisfy all requirements:');
            foreach ($reqs['extensions'] as $ext => $loaded) {
                if (! $loaded) {
                    $this->error(" - PHP extension [{$ext}] is missing.");
                }
            }
            foreach ($reqs['directories'] as $dir => $writable) {
                if (! $writable) {
                    $this->error(" - Directory [{$dir}] is not writable.");
                }
            }

            return self::FAILURE;
        }
        $this->info('   [OK] System requirements verified.');

        $isInteractive = $this->input->isInteractive() && ! app()->runningUnitTests();

        // 2. Gather user details
        $name = $this->option('name') ?: ($isInteractive ? $this->ask('Enter Full Name', 'Amadus User') : 'Amadus User');
        $username = $this->option('username') ?: ($isInteractive ? $this->ask('Enter Username', 'user') : 'user');
        $email = $this->option('email') ?: ($isInteractive ? $this->ask('Enter Email', 'user@amadeus-gds.demo') : 'user@amadeus-gds.demo');
        $password = $this->option('password') ?: ($isInteractive ? $this->secret('Enter Password (min 6 chars)') : 'amadeus123');
        if (empty($password)) {
            $password = 'amadeus123';
        }

        $office = $this->option('office') ?: 'LON1A0980';
        $agent = $this->option('agent') ?: '1234AB';

        // 3. Configure Application URL / Domain
        $appUrl = $this->option('app-url');
        if (! $appUrl && $isInteractive) {
            $appUrl = $this->ask('Application URL / Domain', config('app.url', 'http://localhost:8000'));
        }
        $appUrl = $appUrl ?: config('app.url', 'http://localhost:8000');
        if (! preg_match('~^https?://~i', (string) $appUrl)) {
            $appUrl = 'https://'.$appUrl;
        }
        $appUrl = rtrim($appUrl, '/');
        $installerService->updateAppUrl($appUrl);
        $this->info("   [OK] Application URL configured: {$appUrl}");

        // 4. Migrate and Seed Database
        $this->line('3. Running database migrations and seeders...');
        $installerService->runMigrationsAndSeeders();
        $this->info('   [OK] Database migrated and reference data seeded.');

        // 5. Create the application user account
        $this->line('4. Provisioning application user account...');
        $user = $installerService->createUser([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'office_id' => strtoupper($office),
            'agent_id' => strtoupper($agent),
        ]);
        $this->info("   [OK] User account [{$user->username}] created successfully.");

        // 6. Lock Installation
        $installerService->markInstalled($user);
        $this->info('   [OK] Installation lock file written to storage/installed.');

        $this->newLine();
        $this->info('====================================================');
        $this->info('  INSTALLATION COMPLETE!                            ');
        $this->info('====================================================');
        $this->table(
            ['Field', 'Value'],
            [
                ['Application URL', $appUrl],
                ['Name', $user->name],
                ['Username', $user->username],
                ['Email', $user->email],
                ['Office ID', $user->office_id],
                ['Agent ID', $user->agent_id],
                ['Status', 'Active / Ready'],
            ]
        );

        return self::SUCCESS;
    }
}
