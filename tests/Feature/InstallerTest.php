<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureAppIsInstalled;
use App\Models\User;
use App\Providers\AppServiceProvider;
use App\Services\Installer\InstallerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InstallerTest extends TestCase
{
    use RefreshDatabase;

    protected InstallerService $installerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->installerService = app(InstallerService::class);
        $this->installerService->uninstall();
        EnsureAppIsInstalled::$bypassInTesting = true;
    }

    protected function tearDown(): void
    {
        $this->installerService->uninstall();
        EnsureAppIsInstalled::$bypassInTesting = true;
        parent::tearDown();
    }

    public function test_01_requirements_check_returns_satisfied(): void
    {
        $results = $this->installerService->checkRequirements();

        $this->assertTrue($results['satisfied']);
        $this->assertTrue($results['php_version']['satisfied']);
        $this->assertTrue($results['extensions']['pdo']);
        $this->assertTrue($results['directories']['storage']);
        $this->assertArrayHasKey('sqlite', $results['database_drivers']);
        $this->assertArrayHasKey('mysql', $results['database_drivers']);
    }

    public function test_02_installer_web_wizard_renders_successfully(): void
    {
        $response = $this->get('/install');

        $response->assertStatus(200);
        $response->assertSeeText('Amadus');
        $response->assertSeeText('Installer');
        $response->assertSeeText('System Prerequisites & Permissions');
        $response->assertSeeText('Application Domain & Instructor Setup');
        $response->assertSeeText('Application Domain / URL');
        $response->assertSeeText('SQLite');
        $response->assertSeeText('MySQL');
    }

    public function test_03_installer_validation_rejects_missing_or_mismatched_password(): void
    {
        $response = $this->postJson('/install', [
            'name' => 'Instructor Test',
            'username' => 'inst_test',
            'email' => 'invalid-email',
            'password' => '12345',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_04_installer_creates_instructor_and_writes_lock_file(): void
    {
        $payload = [
            'database_driver' => 'sqlite',
            'name' => 'Chief Flight Instructor',
            'username' => 'lead_instructor',
            'email' => 'lead.instructor@amadeus.test',
            'password' => 'Pass@12345',
            'password_confirmation' => 'Pass@12345',
            'office_id' => 'LON1A0980',
            'agent_id' => 'IN01GS',
        ];

        $response = $this->postJson('/install', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'instructor' => [
                'username' => 'lead_instructor',
                'role' => 'instructor',
                'office_id' => 'LON1A0980',
                'agent_id' => 'IN01GS',
            ],
        ]);

        // Verify User was saved in database with instructor role
        $instructor = User::where('username', 'lead_instructor')->first();
        $this->assertNotNull($instructor);
        $this->assertEquals('instructor', $instructor->role);
        $this->assertTrue(Hash::check('Pass@12345', $instructor->password));

        // Verify installation lock file was written
        $this->assertTrue($this->installerService->isInstalled());
        $this->assertFileExists(storage_path('installed'));
    }

    public function test_04b_installer_handles_both_db_credentials_and_instructor_password_without_collision(): void
    {
        $payload = [
            'name' => 'Instructor Alpha',
            'username' => 'instructor_alpha',
            'email' => 'alpha@amadeus.test',
            'password' => 'InstructSecret2026!',
            'password_confirmation' => 'InstructSecret2026!',
            'office_id' => 'LON1A0980',
            'agent_id' => 'IN01GS',
            'database_driver' => 'sqlite',
            'db_path' => 'database/database.sqlite',
            'db_host' => '127.0.0.1',
            'db_port' => 3306,
            'db_database' => 'amadeus_gds',
            'db_username' => 'root',
            'db_password' => '',
        ];

        $response = $this->postJson('/install', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'instructor' => [
                'username' => 'instructor_alpha',
                'role' => 'instructor',
            ],
        ]);

        $instructor = User::where('username', 'instructor_alpha')->first();
        $this->assertNotNull($instructor);
        $this->assertTrue(Hash::check('InstructSecret2026!', $instructor->password));
    }

    public function test_05_installer_redirects_away_when_already_installed(): void
    {
        // Mark as installed
        $dummyInstructor = User::factory()->create(['role' => 'instructor']);
        $this->installerService->markInstalled($dummyInstructor);

        // Attempting to visit /install should redirect to landing / main page
        $response = $this->get('/install');
        $response->assertRedirect('/');

        // JSON post to /install should return 400
        $jsonResponse = $this->postJson('/install', [
            'name' => 'Another Instructor',
            'username' => 'another',
            'email' => 'another@amadeus.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);
        $jsonResponse->assertStatus(400);
    }

    public function test_06_uninstalled_application_redirects_to_install_when_enforced(): void
    {
        EnsureAppIsInstalled::$bypassInTesting = false;
        $this->installerService->uninstall();

        $response = $this->get('/dashboard');
        $response->assertRedirect('/install');

        $apiResponse = $this->getJson('/api/dashboard/active-pnr');
        $apiResponse->assertStatus(503);
    }

    public function test_07_artisan_app_install_command_executes_successfully(): void
    {
        $exitCode = Artisan::call('app:install', [
            '--name' => 'CLI Flight Instructor',
            '--username' => 'cli_instructor',
            '--email' => 'cli.instructor@amadeus.test',
            '--password' => 'CliPassword123!',
            '--office' => 'LON1A0980',
            '--agent' => 'IN01GS',
            '--force' => true,
        ]);

        $this->assertEquals(0, $exitCode);

        $cliInstructor = User::where('username', 'cli_instructor')->first();
        $this->assertNotNull($cliInstructor);
        $this->assertEquals('instructor', $cliInstructor->role);
        $this->assertEquals('LON1A0980', $cliInstructor->office_id);
        $this->assertEquals('IN01GS', $cliInstructor->agent_id);
        $this->assertTrue($this->installerService->isInstalled());
    }

    public function test_08_installer_tests_database_connection_successfully(): void
    {
        $sqliteResponse = $this->postJson('/install/test-db', [
            'driver' => 'sqlite',
            'database_path' => 'database/database.sqlite',
        ]);

        $sqliteResponse->assertStatus(200);
        $sqliteResponse->assertJson([
            'success' => true,
        ]);
    }

    public function test_09_artisan_remove_installer_command_locks_installer(): void
    {
        $this->installerService->uninstall();
        $this->assertFalse($this->installerService->isInstalled());

        $exitCode = Artisan::call('app:remove-installer');
        $this->assertEquals(0, $exitCode);
        $this->assertTrue($this->installerService->isInstalled());
    }

    public function test_10_installer_accepts_and_configures_custom_app_url(): void
    {
        $payload = [
            'app_url' => 'https://amadus.aviation.edu',
            'name' => 'URL Test Instructor',
            'username' => 'url_instructor',
            'email' => 'url.instructor@amadeus.test',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'database_driver' => 'sqlite',
        ];

        $response = $this->postJson('/install', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'app_url' => 'https://amadus.aviation.edu',
        ]);

        $this->assertEquals('https://amadus.aviation.edu', config('app.url'));

        // Check lock file payload includes app_url
        $lockContent = json_decode(File::get(storage_path('installed')), true);
        $this->assertEquals('https://amadus.aviation.edu', $lockContent['app_url']);
    }

    public function test_11_artisan_app_install_command_supports_app_url_option(): void
    {
        $exitCode = Artisan::call('app:install', [
            '--app-url' => 'https://cli.amadus.aero',
            '--name' => 'CLI URL Instructor',
            '--username' => 'cli_url_instructor',
            '--email' => 'cli.url@amadeus.test',
            '--password' => 'CliPassword123!',
            '--force' => true,
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertEquals('https://cli.amadus.aero', config('app.url'));

        $lockContent = json_decode(File::get(storage_path('installed')), true);
        $this->assertEquals('https://cli.amadus.aero', $lockContent['app_url']);
    }

    public function test_12_concurrent_installation_attempt_returns_conflict_409(): void
    {
        $handle = $this->installerService->acquireInstallLock();
        $this->assertNotNull($handle);

        $payload = [
            'name' => 'Concurrent Instructor',
            'username' => 'concurrent_inst',
            'email' => 'concurrent@amadeus.test',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'database_driver' => 'sqlite',
        ];

        $response = $this->postJson('/install', $payload);
        $response->assertStatus(409);
        $response->assertJson([
            'error' => 'Another installation is already in progress.',
        ]);

        $this->installerService->releaseInstallLock($handle);
    }

    public function test_13_installer_cannot_reinstall_over_existing_database_instructor(): void
    {
        $existingInstructor = User::factory()->create([
            'username' => 'existing_chief',
            'email' => 'chief@academy.test',
            'role' => 'instructor',
            'password' => Hash::make('OriginalPass123!'),
        ]);

        // Marker missing (simulating container restart without volume)
        $this->installerService->uninstall();
        $this->assertFalse($this->installerService->isInstalled());

        $payload = [
            'name' => 'Attacker',
            'username' => 'existing_chief',
            'email' => 'attacker@evil.example',
            'password' => 'AttackerPwn1!',
            'password_confirmation' => 'AttackerPwn1!',
            'database_driver' => 'sqlite',
        ];

        $response = $this->postJson('/install', $payload);
        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Application is already installed.',
        ]);

        // Verify credentials in DB were not overwritten
        $refreshed = $existingInstructor->fresh();
        $this->assertEquals('chief@academy.test', $refreshed->email);
        $this->assertTrue(Hash::check('OriginalPass123!', $refreshed->password));
    }

    public function test_14_installer_wizard_renders_when_app_key_is_initially_empty(): void
    {
        // Simulate a fresh download where app.key is empty
        config(['app.key' => '']);

        // Running register on AppServiceProvider or hitting the wizard should ensure key
        $provider = new AppServiceProvider($this->app);
        $provider->register();

        $this->assertNotEmpty(config('app.key'));
        $this->assertStringStartsWith('base64:', config('app.key'));

        $response = $this->get('/install');
        $response->assertStatus(200);
        $response->assertSeeText('Installer');
    }

    public function test_15_installer_wizard_redirects_when_database_already_has_instructor(): void
    {
        User::factory()->create([
            'role' => 'instructor',
        ]);

        $this->installerService->uninstall();
        $this->assertFalse($this->installerService->isInstalled());

        $response = $this->get('/install');
        $response->assertRedirect(route('landing'));
        $response->assertSessionHas('info', 'Application is already installed and installer is locked.');
    }
}
