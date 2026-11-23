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
     * Requirement 1: Demo accounts are not seeded and the login page exposes no demo helpers.
     */
    public function test_requirement_1_seeder_does_not_seed_demo_accounts_or_demo_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseMissing('users', ['username' => 'student']);
        $this->assertDatabaseMissing('users', ['username' => 'instructor']);
        $this->assertEquals(0, User::count(), 'DatabaseSeeder must not seed any demo accounts into users table.');
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
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertDontSee('ACTIVE PERSPECTIVE');
        $response->assertDontSee('DEV/DEMO');
    }

    /**
     * Requirement 2: Installer supports SQLite/MySQL and provisions exactly one normal user.
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
        $sqliteRes = $this->postJson('/install/test-db', [
            'driver' => 'sqlite',
            'database_path' => 'database/database.sqlite',
        ]);
        $sqliteRes->assertStatus(200);
        $sqliteRes->assertJson(['success' => true]);

        $invalidRes = $this->postJson('/install/test-db', [
            'driver' => 'unsupported_driver',
        ]);
        $invalidRes->assertStatus(422);

        $mysqlRes = $this->postJson('/install/test-db', [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'amadeus_gds',
            'username' => 'root',
        ]);
        $this->assertContains($mysqlRes->status(), [200, 422]);
        $this->assertArrayHasKey('success', $mysqlRes->json());
    }

    public function test_requirement_2_installer_creates_only_a_normal_user_and_locks(): void
    {
        $payload = [
            'database_driver' => 'sqlite',
            'db_path' => 'database/database.sqlite',
            'name' => 'Lead User',
            'username' => 'sky_user',
            'email' => 'sky.user@academy.test',
            'password' => 'Pass@Word123',
            'password_confirmation' => 'Pass@Word123',
            'office_id' => 'LON1A0980',
            'agent_id' => '1234AB',
        ];

        $response = $this->postJson('/install', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'user' => [
                'username' => 'sky_user',
            ],
        ]);

        $this->assertEquals(1, User::count());
        $user = User::first();
        $this->assertEquals('sky_user', $user->username);

        $this->assertTrue($this->installerService->isInstalled());
        $this->assertFileExists(storage_path('installed'));

        $reinstallResponse = $this->postJson('/install', $payload);
        $reinstallResponse->assertStatus(400);

        $this->get('/install')->assertRedirect('/login');
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
     * Requirement 3: Guests are redirected to login; admin surfaces no longer exist.
     */
    public function test_requirement_3_unauthenticated_pages_redirect_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/terminal')->assertRedirect('/login');
    }

    public function test_requirement_3_removed_admin_routes_are_not_available(): void
    {
        $this->get('/instructor')->assertNotFound();
        $this->post('/dashboard/switch-role', ['role' => 'instructor'])->assertStatus(405);
        $this->post('/instructor/exit-impersonation')->assertNotFound();
    }

    public function test_requirement_3_authenticated_user_accesses_protected_routes(): void
    {
        $user = User::factory()->create();

        $terminalRes = $this->actingAs($user)->get('/terminal');
        $terminalRes->assertStatus(200);

        $dashRes = $this->actingAs($user)->get('/dashboard');
        $dashRes->assertStatus(200);
        $dashRes->assertSeeText('AGENT DESK');
    }
}
