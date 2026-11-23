<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
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

    public function test_01_main_page_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_02_guests_are_redirected_to_login_for_protected_pages(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/terminal')->assertRedirect('/login');
    }

    public function test_03_user_login_redirects_to_dashboard(): void
    {
        $webResponse = $this->post('/login', [
            'username' => 'student',
            'password' => 'amadeus123',
        ]);
        $webResponse->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        Auth::logout();

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
            ],
        ]);
    }

    public function test_04_authenticated_user_can_access_dashboard_and_terminal(): void
    {
        $user = User::where('username', 'student')->firstOrFail();

        $dashboard = $this->actingAs($user)->get('/dashboard');
        $dashboard->assertStatus(200);
        $dashboard->assertSeeText('Amadus');

        $terminal = $this->actingAs($user)->get('/terminal');
        $terminal->assertStatus(200);
        $terminal->assertSeeText('WORK AREA:');
        $terminal->assertSeeText('Dashboard');
    }
}
