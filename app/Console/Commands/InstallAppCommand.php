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
                            {--name= : Flight Instructor full name}
                            {--username= : Instructor login username}
                            {--email= : Instructor email address}
                            {--password= : Instructor account password}
                            {--office=LON1A0980 : Amadeus Office ID}
                            {--agent=IN01GS : Agent Duty Code}
                            {--app-url= : Application URL / Domain (e.g. https://amadus.example.com)}
                            {--force : Force re-installation even if already installed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Amadus Training Platform and provision primary Flight Instructor account';

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
            $this->line('Use --force to overwrite and re-provision instructor.');

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

        // 2. Gather Instructor details
        $name = $this->option('name') ?: ($isInteractive ? $this->ask('Enter Instructor Full Name', 'Flight Instructor') : 'Flight Instructor');
        $username = $this->option('username') ?: ($isInteractive ? $this->ask('Enter Instructor Username', 'instructor') : 'instructor');
        $email = $this->option('email') ?: ($isInteractive ? $this->ask('Enter Instructor Email', 'instructor@amadeus-gds.demo') : 'instructor@amadeus-gds.demo');
        $password = $this->option('password') ?: ($isInteractive ? $this->secret('Enter Instructor Password (min 6 chars)') : 'amadeus123');
        if (empty($password)) {
            $password = 'amadeus123';
        }

        $office = $this->option('office') ?: 'LON1A0980';
        $agent = $this->option('agent') ?: 'IN01GS';

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

        // 5. Create Instructor Account
        $this->line('4. Provisioning Flight Instructor account...');
        $instructor = $installerService->createInstructor([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'office_id' => strtoupper($office),
            'agent_id' => strtoupper($agent),
        ]);
        $this->info("   [OK] Instructor account [{$instructor->username}] created successfully (Role: instructor).");

        // 6. Lock Installation
        $installerService->markInstalled($instructor);
        $this->info('   [OK] Installation lock file written to storage/installed.');

        $this->newLine();
        $this->info('====================================================');
        $this->info('  INSTALLATION COMPLETE!                            ');
        $this->info('====================================================');
        $this->table(
            ['Field', 'Value'],
            [
                ['Application URL', $appUrl],
                ['Role', 'INSTRUCTOR'],
                ['Name', $instructor->name],
                ['Username', $instructor->username],
                ['Email', $instructor->email],
                ['Office ID', $instructor->office_id],
                ['Agent ID', $instructor->agent_id],
                ['Status', 'Active / Ready'],
            ]
        );

        return self::SUCCESS;
    }
}
