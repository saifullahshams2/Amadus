<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureAppIsInstalled;
use App\Models\Submission;
use App\Models\TerminalActivityLog;
use App\Models\User;
use App\Services\Installer\InstallerService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class UserRequirementsVerificationTest extends TestCase
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

    /**
     * Requirement 1: Verify demo accounts are removed from database and frontend UI/login page.
     */
    public function test_requirement_1_seeder_does_not_seed_demo_accounts_or_demo_data(): void
    {
        // Run DatabaseSeeder
        $this->seed(DatabaseSeeder::class);

        // Confirm student and instructor demo users do NOT exist
        $this->assertDatabaseMissing('users', ['username' => 'student']);
        $this->assertDatabaseMissing('users', ['username' => 'instructor']);
        $this->assertEquals(0, User::count(), 'DatabaseSeeder must not seed any demo accounts into users table.');

        // Confirm no demo submissions or activity logs exist
        $this->assertEquals(0, Submission::where('user_id', 'student')->count());
        $this->assertEquals(0, TerminalActivityLog::where('user_id', 'student')->count());
    }

    public function test_requirement_1_frontend_login_page_has_no_demo_accounts_or_buttons(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertDontSee('1-CLICK DEMO ACCOUNTS');
        $response->assertDontSee('student / amadeus123');
        $response->assertDontSee('instructor / amadeus123');
        $response->assertDontSee('fillCredentials');
        $response->assertDontSee('placeholder="e.g. student or instructor"', false);
    }

    public function test_requirement_1_dashboard_has_no_guest_demo_perspective_switcher(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertDontSee('ACTIVE PERSPECTIVE');
        $response->assertDontSee('DEV/DEMO');
    }

    /**
     * Requirement 2: Installer for first time install, removable/locked, only instructor created, SQLite and MySQL options.
     */
    public function test_requirement_2_installer_presents_sqlite_and_mysql_options(): void
    {
        $response = $this->get('/install');

        $response->assertStatus(200);
        $response->assertSee('SQLite');
        $response->assertSee('MySQL / MariaDB');
        $response->assertSee('Test Database Connection');
        $response->assertSee('database/database.sqlite');
    }

    public function test_requirement_2_database_test_endpoint_supports_sqlite_and_mysql(): void
    {
        // 1. Test SQLite option
        $sqliteRes = $this->postJson('/install/test-db', [
            'driver' => 'sqlite',
            'database_path' => 'database/database.sqlite',
        ]);
        $sqliteRes->assertStatus(200);
        $sqliteRes->assertJson(['success' => true]);

        // 2. Test invalid driver is rejected
        $invalidRes = $this->postJson('/install/test-db', [
            'driver' => 'unsupported_driver',
        ]);
        $invalidRes->assertStatus(422);

        // 3. Test MySQL driver validation
        $mysqlRes = $this->postJson('/install/test-db', [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'amadeus_gds',
            'username' => 'root',
        ]);
        // Result is either 200 (if MySQL is running locally) or 422 with a specific connection error message
        $this->assertContains($mysqlRes->status(), [200, 422]);
        $this->assertArrayHasKey('success', $mysqlRes->json());
    }

    public function test_requirement_2_installer_creates_only_instructor_account_and_locks(): void
    {
        $payload = [
            'database_driver' => 'sqlite',
            'db_path' => 'database/database.sqlite',
            'name' => 'Lead Flight Instructor',
            'username' => 'sky_instructor',
            'email' => 'sky.instructor@academy.test',
            'password' => 'Pass@Word123',
            'password_confirmation' => 'Pass@Word123',
            'office_id' => 'LON1A0980',
            'agent_id' => 'IN01GS',
        ];

        $response = $this->postJson('/install', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'instructor' => [
                'username' => 'sky_instructor',
                'role' => 'instructor',
            ],
        ]);

        // Verify only the instructor account exists in database
        $this->assertEquals(1, User::count());
        $user = User::first();
        $this->assertEquals('sky_instructor', $user->username);
        $this->assertEquals('instructor', $user->role);
        $this->assertTrue($user->is_approved);

        // Verify installer is marked as installed & locked
        $this->assertTrue($this->installerService->isInstalled());
        $this->assertFileExists(storage_path('installed'));

        // Verify re-installation is locked and rejected
        $reinstallResponse = $this->postJson('/install', $payload);
        $reinstallResponse->assertStatus(400);

        // Web request to /install redirects to main page
        $webInstallResponse = $this->get('/install');
        $webInstallResponse->assertRedirect('/');
    }

    public function test_requirement_2_remove_installer_command_locks_installer(): void
    {
        $this->installerService->uninstall();
        $this->assertFalse($this->installerService->isInstalled());

        $exitCode = Artisan::call('app:remove-installer');
        $this->assertEquals(0, $exitCode);
        $this->assertTrue($this->installerService->isInstalled());
    }

    /**
     * Requirement 3: If no one is logged in, redirect to main page.
     */
    public function test_requirement_3_unauthenticated_dashboard_redirects_to_main_page(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/');
    }

    public function test_requirement_3_unauthenticated_terminal_redirects_to_main_page(): void
    {
        $response = $this->get('/terminal');
        $response->assertRedirect('/');
    }

    public function test_requirement_3_unauthenticated_instructor_hub_redirects_to_main_page(): void
    {
        $response = $this->get('/instructor');
        $response->assertRedirect('/');
    }

    public function test_requirement_3_unauthenticated_switch_role_redirects_to_main_page(): void
    {
        $response = $this->post('/dashboard/switch-role', ['role' => 'instructor']);
        $response->assertRedirect('/');
    }

    public function test_requirement_3_authenticated_user_accesses_protected_routes(): void
    {
        $student = User::factory()->create(['role' => 'student', 'is_approved' => true]);

        // Terminal is accessible when logged in
        $terminalRes = $this->actingAs($student)->get('/terminal');
        $terminalRes->assertStatus(200);

        // Dashboard is accessible when logged in
        $dashRes = $this->actingAs($student)->get('/dashboard');
        $dashRes->assertStatus(200);
        $dashRes->assertSee('TRAINEE AGENT DESK');
    }
}
