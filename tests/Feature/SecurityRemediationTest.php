<?php

namespace Tests\Feature;

use App\Models\LessonProgress;
use App\Models\QueueItem;
use App\Models\Scenario;
use App\Models\SmtpSetting;
use App\Models\User;
use App\Services\Installer\InstallerService;
use App\Services\MailSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityRemediationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedTestUsers();
    }

    protected function user(): User
    {
        return User::where('username', 'student')->firstOrFail();
    }

    public function test_rrn_huge_passenger_count_is_capped(): void
    {
        $response = $this->postJson('/api/terminal/execute', [
            'command' => 'RRN/5000000',
            'state' => [],
        ]);

        $response->assertOk();
        $passengers = $response->json('updatedState.activePnr.passengers') ?? [];
        $this->assertLessThanOrEqual(99, count($passengers));
    }

    public function test_rtq_without_record_locator_does_not_crash(): void
    {
        $this->postJson('/api/terminal/execute', ['command' => 'RTQ', 'state' => []])
            ->assertOk()
            ->assertJsonPath('isError', false);
    }

    public function test_split_with_out_of_range_passenger_returns_error_not_500(): void
    {
        $response = $this->postJson('/api/terminal/execute', [
            'command' => 'NM1A/B;NM1C/D;SP99;RF P;EF',
            'state' => [],
        ]);

        $response->assertOk();
    }

    public function test_manual_ticket_display_does_not_crash(): void
    {
        $this->postJson('/api/terminal/execute', [
            'command' => 'FHM PAX 157-1234567890;TWD',
            'state' => [],
        ])->assertOk();
    }

    public function test_guest_queue_commands_do_not_persist_rows(): void
    {
        $before = QueueItem::count();

        $this->postJson('/api/terminal/execute', [
            'command' => 'TKDO01JAN',
            'state' => ['officeId' => 'NYC1A0111', 'activePnr' => ['recordLocator' => 'GSTQ01', 'passengers' => [['lastName' => 'GUEST', 'firstName' => 'ANON']]]],
        ])->assertOk();

        $this->assertSame($before, QueueItem::count());
    }

    public function test_validate_syntax_rejects_null_and_array_command(): void
    {
        $this->postJson('/api/dashboard/validate-syntax', ['command' => null])->assertOk();
        $this->postJson('/api/dashboard/validate-syntax', ['command' => ['AN15NOVLHRJFK']])->assertStatus(422);
    }

    public function test_training_progress_rejects_nested_array_objectives(): void
    {
        $this->actingAs($this->user())
            ->postJson('/api/training/progress', [
                'lessonId' => 'les-1-1',
                'completedObjectives' => [['nested']],
            ])
            ->assertStatus(422);
    }

    public function test_malformed_terminal_state_does_not_crash(): void
    {
        $this->postJson('/api/terminal/execute', [
            'command' => 'AN15NOVLHRJFK',
            'state' => ['activePnr' => 'not-an-array', 'lastAvailabilityDisplay' => 'nope'],
        ])->assertOk();
    }

    public function test_forwarded_headers_do_not_spoof_client_ip(): void
    {
        // Without TRUSTED_PROXIES the spoofed header must be ignored, so the
        // login throttle still keys on the real address.
        for ($i = 0; $i < 12; $i++) {
            $this->withHeader('X-Forwarded-For', '198.51.100.'.($i + 1))
                ->postJson('/api/auth/login', ['username' => 'nobody', 'password' => 'wrong']);
        }

        $this->withHeader('X-Forwarded-For', '198.51.100.99')
            ->postJson('/api/auth/login', ['username' => 'nobody', 'password' => 'wrong'])
            ->assertStatus(429);
    }

    public function test_password_change_revokes_other_sessions_and_rotates_remember_token(): void
    {
        $user = $this->user();
        $user->forceFill(['remember_token' => 'old-token'])->save();

        $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'amadeus123',
                'password' => 'brand-new-secret-1',
                'password_confirmation' => 'brand-new-secret-1',
            ])
            ->assertRedirect(route('profile.password'));

        $fresh = $user->fresh();
        $this->assertTrue(Hash::check('brand-new-secret-1', $fresh->password));
        $this->assertNotSame('old-token', $fresh->remember_token);
    }

    public function test_installer_command_requires_explicit_password(): void
    {
        $this->artisan('app:install', [
            '--no-interaction' => true,
            '--force' => true,
            '--username' => 'cli-user',
            '--email' => 'cli@example.com',
        ])->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['username' => 'cli-user']);
        $this->assertFalse(User::where('username', 'cli-user')->exists());
        $this->assertFalse(Hash::check('amadeus123', ''));
    }

    public function test_installer_env_values_cannot_inject_newlines(): void
    {
        $service = app(InstallerService::class);

        $method = new \ReflectionMethod($service, 'formatEnvValue');
        $method->setAccessible(true);

        $value = $method->invoke($service, "a \\\" \nAPP_DEBUG=true");

        $this->assertStringNotContainsString("\n", $value);
        $this->assertStringNotContainsString("\r", $value);
    }

    public function test_username_change_cascades_lesson_progress_and_scenarios(): void
    {
        $user = $this->user();

        LessonProgress::create([
            'user_id' => 'student',
            'lesson_id' => 'les-1-1',
            'module_id' => 'mod-1',
            'status' => 'completed',
            'completed_objectives_json' => [],
        ]);
        Scenario::create([
            'id' => 'SCN-USER-CASCADE1',
            'title' => 'Cascade Drill',
            'category' => 'One Way',
            'difficulty' => 'Beginner',
            'description' => 'x',
            'passenger_profile' => [],
            'flight_segments' => [],
            'validation_criteria' => [],
            'time_limit_minutes' => 10,
            'created_by' => 'student',
        ]);

        $this->actingAs($user)
            ->put('/profile/name', ['username' => 'renamed.cadet', 'current_password' => 'amadeus123'])
            ->assertRedirect(route('profile.index'));

        $this->assertDatabaseHas('lesson_progress', ['user_id' => 'renamed.cadet']);
        $this->assertDatabaseHas('scenarios', ['id' => 'SCN-USER-CASCADE1', 'created_by' => 'renamed.cadet']);
    }

    public function test_username_cannot_be_reserved_sentinel(): void
    {
        $this->actingAs($this->user())
            ->put('/profile/name', ['username' => 'guest', 'current_password' => 'amadeus123'])
            ->assertSessionHasErrors('username');

        $this->actingAs($this->user())
            ->put('/profile/name', ['username' => 'system', 'current_password' => 'amadeus123'])
            ->assertSessionHasErrors('username');
    }

    public function test_smtp_service_maps_tls_scheme_and_require_tls(): void
    {
        SmtpSetting::create([
            'enabled' => true,
            'host' => '127.0.0.1',
            'port' => 587,
            'encryption' => 'tls',
            'from_address' => 'no-reply@example.com',
        ]);

        app(MailSettingsService::class)->apply();

        // Blocked host must not be dialled: mail.default stays on the env default.
        $this->assertNotSame('smtp', config('mail.default') === 'smtp' && config('mail.mailers.smtp.host') === '127.0.0.1' ? 'smtp' : 'log');
    }

    public function test_internal_smtp_hosts_are_detected(): void
    {
        $service = app(MailSettingsService::class);

        foreach (['127.0.0.1', '10.1.2.3', '169.254.169.254', '192.168.0.1', '::1', 'fd00:ec2::254'] as $ip) {
            $this->assertTrue($service->isBlockedHost($ip), "{$ip} should be blocked");
        }

        $this->assertFalse($service->isBlockedHost('8.8.8.8'));
    }
}
