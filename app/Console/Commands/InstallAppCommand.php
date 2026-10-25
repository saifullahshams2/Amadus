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
                            {--force : Force re-installation even if already installed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Amadeus GDS Training Platform and provision primary Flight Instructor account';

    /**
     * Execute the console command.
     */
    public function handle(InstallerService $installerService): int
    {
        $this->info('====================================================');
        $this->info('  Amadeus GDS Platform & Cash-Register Installer    ');
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

        // 2. Gather Instructor details
        $name = $this->option('name') ?: ($this->input->isInteractive() ? $this->ask('Enter Instructor Full Name', 'Flight Instructor') : 'Flight Instructor');
        $username = $this->option('username') ?: ($this->input->isInteractive() ? $this->ask('Enter Instructor Username', 'instructor') : 'instructor');
        $email = $this->option('email') ?: ($this->input->isInteractive() ? $this->ask('Enter Instructor Email', 'instructor@amadeus-gds.demo') : 'instructor@amadeus-gds.demo');
        $password = $this->option('password') ?: ($this->input->isInteractive() ? $this->secret('Enter Instructor Password (min 6 chars)') : 'amadeus123');
        if (empty($password)) {
            $password = 'amadeus123';
        }

        $office = $this->option('office') ?: 'LON1A0980';
        $agent = $this->option('agent') ?: 'IN01GS';

        // 3. Migrate and Seed Database
        $this->line('2. Running database migrations and seeders...');
        $installerService->runMigrationsAndSeeders();
        $this->info('   [OK] Database migrated and reference data seeded.');

        // 4. Create Instructor Account
        $this->line('3. Provisioning Flight Instructor account...');
        $instructor = $installerService->createInstructor([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'office_id' => strtoupper($office),
            'agent_id' => strtoupper($agent),
        ]);
        $this->info("   [OK] Instructor account [{$instructor->username}] created successfully (Role: instructor).");

        // 5. Lock Installation
        $installerService->markInstalled($instructor);
        $this->info('   [OK] Installation lock file written to storage/installed.');

        $this->newLine();
        $this->info('====================================================');
        $this->info('  INSTALLATION COMPLETE!                            ');
        $this->info('====================================================');
        $this->table(
            ['Field', 'Value'],
            [
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
