<?php

namespace Tests\Feature;

use App\Mail\SmtpTestMail;
use App\Models\Pnr;
use App\Models\SmtpSetting;
use App\Models\Submission;
use App\Models\TerminalActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProfileTest extends TestCase
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

    public function test_profile_main_page_and_sub_pages_render(): void
    {
        $user = $this->user();

        $this->actingAs($user)->get('/profile')
            ->assertOk()
            ->assertSeeText('My Profile')
            ->assertSeeText("User's Name")
            ->assertSeeText('Display Name')
            ->assertSeeText('Email Address')
            ->assertSeeText('SMTP Settings');

        $this->actingAs($user)->get('/profile/password')->assertOk()->assertSeeText('Change Your Password');
        $this->actingAs($user)->get('/profile/smtp')->assertOk()->assertSeeText('SMTP Settings');

        $this->actingAs($user)->get('/profile/name')->assertStatus(405);
        $this->actingAs($user)->get('/profile/display-name')->assertStatus(405);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/profile')->assertRedirect('/login');
    }

    public function test_profile_button_appears_above_gds_session(): void
    {
        $html = $this->actingAs($this->user())->get('/dashboard')->getContent();

        $profilePos = strpos($html, 'My Profile');
        $gdsPos = strpos($html, 'GDS SESSION:');

        $this->assertNotFalse($profilePos);
        $this->assertNotFalse($gdsPos);
        $this->assertLessThan($gdsPos, $profilePos, 'Profile button must appear above the GDS session widget.');
    }

    public function test_user_can_update_display_name(): void
    {
        $this->actingAs($this->user())
            ->put('/profile/display-name', ['name' => 'John Aviator'])
            ->assertRedirect(route('profile.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('users', ['username' => 'student', 'name' => 'John Aviator']);
    }

    public function test_user_can_update_username_with_cascade(): void
    {
        $user = $this->user();

        Pnr::create(['record_locator' => 'CASCA1', 'user_id' => 'student', 'office_id' => 'LON1A0980', 'status' => 'ACTIVE', 'data_json' => []]);
        Submission::create(['scenario_id' => 'SCN-RT-01', 'user_id' => 'student', 'status' => 'submitted', 'score' => 90]);
        TerminalActivityLog::create(['user_id' => 'student', 'command' => 'AN15DECLHRJFK', 'is_error' => false, 'output_lines' => []]);

        $this->actingAs($user)
            ->put('/profile/name', ['username' => 'aviator.jr', 'current_password' => 'amadeus123'])
            ->assertRedirect(route('profile.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('users', ['username' => 'student']);
        $this->assertDatabaseHas('users', ['username' => 'aviator.jr']);
        $this->assertDatabaseHas('pnrs', ['record_locator' => 'CASCA1', 'user_id' => 'aviator.jr']);
        $this->assertDatabaseHas('submissions', ['user_id' => 'aviator.jr']);
        $this->assertDatabaseHas('terminal_activity_logs', ['user_id' => 'aviator.jr']);
    }

    public function test_username_change_requires_correct_current_password(): void
    {
        $this->actingAs($this->user())
            ->put('/profile/name', ['username' => 'aviator.jr', 'current_password' => 'wrong-password'])
            ->assertSessionHasErrors('current_password');

        $this->assertDatabaseHas('users', ['username' => 'student']);
    }

    public function test_username_change_rejects_duplicates(): void
    {
        User::firstOrCreate(
            ['username' => 'student2'],
            ['name' => 'Second', 'email' => 'student2@amadeus-gds.demo', 'password' => Hash::make('amadeus123')]
        );

        $this->actingAs($this->user())
            ->put('/profile/name', ['username' => 'student2', 'current_password' => 'amadeus123'])
            ->assertSessionHasErrors('username');
    }

    public function test_user_can_update_email_address(): void
    {
        $this->actingAs($this->user())
            ->put('/profile/email', ['email' => 'aviator@example.com', 'current_password' => 'amadeus123'])
            ->assertRedirect(route('profile.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('users', ['username' => 'student', 'email' => 'aviator@example.com']);
    }

    public function test_email_change_requires_correct_current_password(): void
    {
        $this->actingAs($this->user())
            ->put('/profile/email', ['email' => 'aviator@example.com', 'current_password' => 'wrong-password'])
            ->assertSessionHasErrors('current_password');

        $this->assertDatabaseHas('users', ['username' => 'student', 'email' => 'student@amadeus-gds.demo']);
    }

    public function test_email_change_rejects_duplicates(): void
    {
        User::firstOrCreate(
            ['username' => 'student2'],
            ['name' => 'Second', 'email' => 'student2@amadeus-gds.demo', 'password' => Hash::make('amadeus123')]
        );

        $this->actingAs($this->user())
            ->put('/profile/email', ['email' => 'student2@amadeus-gds.demo', 'current_password' => 'amadeus123'])
            ->assertSessionHasErrors('email');
    }

    public function test_user_can_change_password(): void
    {
        $this->actingAs($this->user())
            ->put('/profile/password', [
                'current_password' => 'amadeus123',
                'password' => 'new-secret-99',
                'password_confirmation' => 'new-secret-99',
            ])
            ->assertRedirect(route('profile.password'))
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('new-secret-99', $this->user()->fresh()->password));
    }

    public function test_password_change_rejects_wrong_current_password(): void
    {
        $this->actingAs($this->user())
            ->put('/profile/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-secret-99',
                'password_confirmation' => 'new-secret-99',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('amadeus123', $this->user()->fresh()->password));
    }

    public function test_user_can_save_smtp_settings(): void
    {
        $this->actingAs($this->user())
            ->put('/profile/smtp', [
                'enabled' => '1',
                'host' => 'smtp.example.com',
                'port' => '587',
                'username' => 'mailer@example.com',
                'password' => 'smtp-secret',
                'encryption' => 'tls',
                'from_address' => 'no-reply@example.com',
                'from_name' => 'Amadus Training',
            ])
            ->assertRedirect(route('profile.smtp'))
            ->assertSessionHas('status');

        $settings = SmtpSetting::current();
        $this->assertTrue($settings->enabled);
        $this->assertSame('smtp.example.com', $settings->host);
        $this->assertSame(587, $settings->port);
        $this->assertSame('no-reply@example.com', $settings->from_address);
        $this->assertSame('smtp-secret', $settings->password);
        $this->assertTrue($settings->isConfigured());
    }

    public function test_smtp_password_is_kept_when_left_blank_and_host_unchanged(): void
    {
        SmtpSetting::create([
            'enabled' => true,
            'host' => 'smtp.example.com',
            'port' => 587,
            'username' => 'mailer@example.com',
            'password' => 'original-secret',
            'encryption' => 'tls',
            'from_address' => 'no-reply@example.com',
        ]);

        $this->actingAs($this->user())
            ->put('/profile/smtp', [
                'enabled' => '1',
                'host' => 'smtp.example.com',
                'port' => '587',
                'username' => 'mailer@example.com',
                'encryption' => 'tls',
                'from_address' => 'no-reply@example.com',
                'password' => '',
            ])
            ->assertRedirect(route('profile.smtp'));

        $settings = SmtpSetting::current();
        $this->assertSame('original-secret', $settings->password);
    }

    public function test_smtp_password_is_cleared_when_host_changes_without_new_secret(): void
    {
        SmtpSetting::create([
            'enabled' => true,
            'host' => 'smtp.example.com',
            'port' => 587,
            'username' => 'mailer@example.com',
            'password' => 'original-secret',
            'encryption' => 'tls',
            'from_address' => 'no-reply@example.com',
        ]);

        $this->actingAs($this->user())
            ->put('/profile/smtp', [
                'enabled' => '1',
                'host' => 'smtp2.example.com',
                'port' => '465',
                'encryption' => 'ssl',
                'from_address' => 'noreply@example.com',
                'password' => '',
            ])
            ->assertRedirect(route('profile.smtp'));

        $settings = SmtpSetting::current();
        $this->assertNull($settings->password);
        $this->assertSame('smtp2.example.com', $settings->host);
    }

    public function test_smtp_rejects_internal_and_loopback_hosts(): void
    {
        foreach (['127.0.0.1', 'localhost', '169.254.169.254', '10.0.0.5', '192.168.1.1'] as $host) {
            $this->actingAs($this->user())
                ->put('/profile/smtp', [
                    'enabled' => '1',
                    'host' => $host,
                    'port' => '587',
                    'encryption' => 'tls',
                    'from_address' => 'no-reply@example.com',
                ])
                ->assertSessionHasErrors('host');
        }
    }

    public function test_non_owner_cannot_access_smtp_settings(): void
    {
        User::firstOrCreate(
            ['username' => 'student2'],
            ['name' => 'Second', 'email' => 'student2@amadeus-gds.demo', 'password' => Hash::make('amadeus123')]
        );

        $this->actingAs(User::where('username', 'student2')->firstOrFail())
            ->get('/profile/smtp')
            ->assertForbidden();

        $this->actingAs(User::where('username', 'student2')->firstOrFail())
            ->put('/profile/smtp', ['enabled' => '0'])
            ->assertForbidden();
    }

    public function test_smtp_requires_host_and_from_address_when_enabled(): void
    {
        $this->actingAs($this->user())
            ->put('/profile/smtp', [
                'enabled' => '1',
                'port' => '587',
            ])
            ->assertSessionHasErrors(['host', 'from_address']);
    }

    public function test_test_email_uses_stored_smtp_settings(): void
    {
        SmtpSetting::create([
            'enabled' => true,
            'host' => 'smtp.example.com',
            'port' => 587,
            'encryption' => 'tls',
            'from_address' => 'no-reply@example.com',
            'from_name' => 'Amadus Training',
        ]);

        Mail::fake();

        $this->actingAs($this->user())
            ->post('/profile/smtp/test', ['recipient' => 'cadet@example.com'])
            ->assertRedirect()
            ->assertSessionHas('status');

        Mail::assertSent(SmtpTestMail::class, function (SmtpTestMail $mailable) {
            return $mailable->envelope()->from->address === 'no-reply@example.com'
                && $mailable->envelope()->from->name === 'Amadus Training'
                && collect($mailable->to)->contains('address', 'cadet@example.com');
        });
    }

    public function test_password_reset_applies_stored_smtp_settings(): void
    {
        SmtpSetting::create([
            'enabled' => true,
            'host' => 'smtp.example.com',
            'port' => 587,
            'encryption' => 'tls',
            'from_address' => 'no-reply@example.com',
            'from_name' => 'Amadus Training',
        ]);

        Mail::fake();

        $this->postJson('/api/auth/forgot-password', ['email' => 'student'])
            ->assertOk();

        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('smtp.example.com', config('mail.mailers.smtp.host'));
        $this->assertSame('no-reply@example.com', config('mail.from.address'));
        $this->assertSame('Amadus Training', config('mail.from.name'));
    }
}
