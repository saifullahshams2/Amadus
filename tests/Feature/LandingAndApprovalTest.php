<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LandingAndApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->seedTestUsers();
    }

    public function test_01_landing_page_renders_with_brand_and_login_button(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSeeText('AMADEUS GDS SIMULATOR');
        $response->assertSee('Login');
        $response->assertSee('Sign Up');
        $response->assertSee('/login');
        $response->assertSee('/register');
        $response->assertSee('/terminal');
    }

    public function test_02_terminal_simulator_requires_authentication_and_redirects_guests(): void
    {
        // Guests redirect to main landing page
        $guestResponse = $this->get('/terminal');
        $guestResponse->assertRedirect('/');

        // Authenticated users can access terminal
        $student = User::where('role', 'student')->first();
        $authResponse = $this->actingAs($student)->get('/terminal');
        $authResponse->assertStatus(200);
        $authResponse->assertSeeText('WORK AREA:');
        $authResponse->assertSeeText('1A TRAINING CONNECT');
        $authResponse->assertSeeText('Dashboard Hub');
    }

    public function test_03_approved_student_login_redirects_to_dashboard(): void
    {
        // 1. Web Login redirects to /dashboard
        $webResponse = $this->post('/login', [
            'username' => 'student',
            'password' => 'amadeus123',
        ]);
        $webResponse->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        Auth::logout();

        // 2. API Login returns redirect => /dashboard
        $apiResponse = $this->postJson('/api/auth/login', [
            'username' => 'student',
            'password' => 'amadeus123',
        ]);
        $apiResponse->assertStatus(200);
        $apiResponse->assertJson([
            'success' => true,
            'redirect' => '/dashboard',
            'user' => [
                'username' => 'student',
                'role' => 'student',
            ],
        ]);
    }

    public function test_04_new_user_registration_is_marked_unapproved_and_redirects_to_pending_approval(): void
    {
        // 1. Web Registration
        $response = $this->post('/register', [
            'name' => 'Cadet Tom Cruise',
            'username' => 'cadet_tom',
            'email' => 'tom@topgun.academy',
            'password' => 'FlyNavy2026!',
            'password_confirmation' => 'FlyNavy2026!',
        ]);

        $response->assertRedirect('/pending-approval');

        $this->assertDatabaseHas('users', [
            'username' => 'cadet_tom',
            'email' => 'tom@topgun.academy',
            'role' => 'student',
            'is_approved' => false,
        ]);

        // Accessing /pending-approval renders instructions
        $pendingPage = $this->get('/pending-approval');
        $pendingPage->assertStatus(200);
        $pendingPage->assertSeeText('Cadet Enrollment Submitted');
        $pendingPage->assertSeeText('Pending Instructor Approval');

        // 2. API Registration
        $apiRes = $this->postJson('/api/auth/register', [
            'name' => 'Cadet Goose',
            'username' => 'cadet_goose',
            'email' => 'goose@topgun.academy',
            'password' => 'FlyNavy2026!',
        ]);

        $apiRes->assertStatus(201);
        $apiRes->assertJson([
            'success' => true,
            'pending_approval' => true,
            'user' => [
                'username' => 'cadet_goose',
                'is_approved' => false,
            ],
        ]);
    }

    public function test_05_unapproved_cadet_cannot_login(): void
    {
        $unapproved = User::factory()->unapproved()->create([
            'username' => 'pending_cadet',
            'password' => Hash::make('password123'),
        ]);

        // 1. Web Login blocked
        $webRes = $this->post('/login', [
            'username' => 'pending_cadet',
            'password' => 'password123',
        ]);

        $webRes->assertSessionHasErrors('username');
        $this->assertGuest();

        // 2. API Login blocked with 403
        $apiRes = $this->postJson('/api/auth/login', [
            'username' => 'pending_cadet',
            'password' => 'password123',
        ]);

        $apiRes->assertStatus(403);
        $apiRes->assertJson([
            'pending_approval' => true,
        ]);
    }

    public function test_06_instructor_can_approve_cadet(): void
    {
        $instructor = User::where('role', 'instructor')->first();
        $unapproved = User::factory()->unapproved()->create([
            'username' => 'cadet_maverick',
        ]);

        $this->assertFalse($unapproved->is_approved);

        // Instructor approves cadet via API
        $response = $this->actingAs($instructor)->postJson("/api/instructor/students/{$unapproved->id}/approve");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => "Cadet 'cadet_maverick' approved successfully.",
        ]);

        $this->assertTrue($unapproved->fresh()->is_approved);
    }

    public function test_07_once_approved_cadet_can_login_and_access_dashboard(): void
    {
        $cadet = User::factory()->unapproved()->create([
            'username' => 'cadet_iceman',
            'password' => Hash::make('icepass123'),
        ]);

        // Manually approve cadet (simulating instructor approval)
        $cadet->is_approved = true;
        $cadet->save();

        // Login succeeds and redirects to /dashboard
        $response = $this->post('/login', [
            'username' => 'cadet_iceman',
            'password' => 'icepass123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($cadet);

        $dashRes = $this->actingAs($cadet)->get('/dashboard');
        $dashRes->assertStatus(200);
        $dashRes->assertSeeText('TRAINEE AGENT DESK');
    }

    public function test_08_instructor_can_reject_cadet(): void
    {
        $instructor = User::where('role', 'instructor')->first();
        $unapproved = User::factory()->unapproved()->create([
            'username' => 'cadet_spam',
        ]);

        $response = $this->actingAs($instructor)->postJson("/api/instructor/students/{$unapproved->id}/reject");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('users', [
            'id' => $unapproved->id,
        ]);
    }
}
