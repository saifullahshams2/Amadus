<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_renders_successfully(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertSeeText('Recover Password');
        $response->assertSeeText('EMAIL ADDRESS OR CALLSIGN');
        $response->assertSeeText('SEND RESET LINK');
        $response->assertSee('/login');
    }

    public function test_login_page_contains_forgot_password_links(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSeeText('Forgot password?');
        $response->assertSeeText('Reset Password');
        $response->assertSee('/forgot-password');
    }

    public function test_user_can_request_password_reset_link_using_email(): void
    {
        $user = User::factory()->create([
            'email' => 'cadet.pilot@amadeus.training',
            'username' => 'pilot_cadet',
        ]);

        $response = $this->post('/forgot-password', [
            'email' => 'cadet.pilot@amadeus.training',
        ]);

        $response->assertSessionHas('status');
        $response->assertSessionMissing('reset_link');

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'cadet.pilot@amadeus.training',
        ]);
    }

    public function test_user_can_request_password_reset_link_using_username(): void
    {
        $user = User::factory()->create([
            'email' => 'navigator@amadeus.training',
            'username' => 'navigator_cadet',
        ]);

        $response = $this->post('/forgot-password', [
            'email' => 'navigator_cadet',
        ]);

        $response->assertSessionHas('status');

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'navigator@amadeus.training',
        ]);
    }

    public function test_request_reset_link_fails_for_unknown_user(): void
    {
        $response = $this->post('/forgot-password', [
            'email' => 'nonexistent@nowhere.test',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_reset_password_page_renders_with_token_and_email(): void
    {
        $user = User::factory()->create([
            'email' => 'flight.ops@amadeus.training',
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->get("/reset-password/{$token}?email=".urlencode($user->email));

        $response->assertStatus(200);
        $response->assertSeeText('Set New Password');
        $response->assertSee($token);
        $response->assertSee($user->email);
    }

    public function test_user_can_successfully_reset_password_and_login_with_new_password(): void
    {
        $user = User::factory()->create([
            'email' => 'skywalker@amadeus.training',
            'username' => 'skywalker',
            'password' => Hash::make('oldPassword123!'),
            'is_approved' => true,
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newSecretPassword2026!',
            'password_confirmation' => 'newSecretPassword2026!',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('info');

        // Verify old password no longer works
        $this->assertFalse(Hash::check('oldPassword123!', $user->fresh()->password));

        // Verify new password is valid
        $this->assertTrue(Hash::check('newSecretPassword2026!', $user->fresh()->password));

        // Test login with new password
        $loginResponse = $this->post('/login', [
            'username' => 'skywalker',
            'password' => 'newSecretPassword2026!',
        ]);

        $loginResponse->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_reset_password_fails_with_invalid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'target@amadeus.training',
        ]);

        $response = $this->post('/reset-password', [
            'token' => 'invalid-token-12345',
            'email' => $user->email,
            'password' => 'newPassword123!',
            'password_confirmation' => 'newPassword123!',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_api_password_reset_flow(): void
    {
        $user = User::factory()->create([
            'email' => 'api.cadet@amadeus.training',
            'username' => 'api_cadet',
        ]);

        // 1. API request reset link
        $forgotResponse = $this->postJson('/api/auth/forgot-password', [
            'email' => $user->email,
        ]);

        $forgotResponse->assertStatus(200);
        $forgotResponse->assertJson([
            'success' => true,
            'email' => $user->email,
        ]);
        $this->assertNull($forgotResponse->json('reset_url'));

        // Extract token
        $token = Password::broker()->createToken($user);

        // 2. API submit new password
        $resetResponse = $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'brandNewPassword99!',
            'password_confirmation' => 'brandNewPassword99!',
        ]);

        $resetResponse->assertStatus(200);
        $resetResponse->assertJson(['success' => true]);

        $this->assertTrue(Hash::check('brandNewPassword99!', $user->fresh()->password));
    }

    public function test_user_can_login_with_email_as_well_as_username(): void
    {
        $user = User::factory()->create([
            'email' => 'duallogin@amadeus.training',
            'username' => 'callsign_dual',
            'password' => Hash::make('secretFlight123!'),
            'is_approved' => true,
        ]);

        // 1. Login with callsign/username
        $res1 = $this->post('/login', [
            'username' => 'callsign_dual',
            'password' => 'secretFlight123!',
        ]);
        $res1->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        Auth::logout();

        // 2. Login with email
        $res2 = $this->post('/login', [
            'username' => 'duallogin@amadeus.training',
            'password' => 'secretFlight123!',
        ]);
        $res2->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }
}
