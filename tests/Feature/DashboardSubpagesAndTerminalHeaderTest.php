<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardSubpagesAndTerminalHeaderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->seedTestUsers();
    }

    protected function user(): User
    {
        return User::where('username', 'student')->firstOrFail();
    }

    public function test_01_left_menu_has_dashboard_option_and_dedicated_subpages(): void
    {
        $response = $this->actingAs($this->user())->get('/dashboard');
        $response->assertStatus(200);

        $response->assertSee(route('dashboard'), false);
        $response->assertSeeText('Dashboard');
        $response->assertSee(route('dashboard.section', 'workspace'), false);
        $response->assertSee(route('dashboard.section', 'drills'), false);
        $response->assertDontSee(route('dashboard.section', 'validator'), false);
        $response->assertDontSee(route('dashboard.section', 'analytics'), false);
    }

    public function test_02_user_can_access_dedicated_subpages(): void
    {
        $user = $this->user();

        $workspace = $this->actingAs($user)->get('/dashboard/workspace');
        $workspace->assertStatus(200);
        $workspace->assertSeeText('OVERVIEW & ACTIVE PNR');
        $workspace->assertSeeText('Current Active PNR Status');

        $drills = $this->actingAs($user)->get('/dashboard/drills');
        $drills->assertStatus(200);
        $drills->assertSeeText('ASSIGNMENTS & DRILLS');
        $drills->assertSeeText('Assigned Booking Scenarios & Drills');
    }

    public function test_03_removed_subpages_redirect_to_dashboard(): void
    {
        $user = $this->user();

        foreach (['validator', 'analytics', 'classroom', 'students', 'builder', 'gradebook', 'queues'] as $section) {
            $this->actingAs($user)
                ->get('/dashboard/'.$section)
                ->assertRedirect(route('dashboard'));
        }
    }

    public function test_04_terminal_page_has_dashboard_link_and_no_admin_hub(): void
    {
        $user = $this->user();

        $response = $this->actingAs($user)->get('/terminal');
        $response->assertStatus(200);

        $response->assertSee(route('dashboard'), false);
        $response->assertSeeText('Dashboard');
        $response->assertDontSeeText('Dashboard Hub');
        $response->assertDontSeeText('Instructor Hub');
        $response->assertSeeText($user->username);
    }

    public function test_05_terminal_page_portal_navigation_removed_and_dashboard_button_on_top(): void
    {
        $response = $this->actingAs($this->user())->get('/terminal');
        $response->assertStatus(200);

        $response->assertDontSeeText('PORTAL NAVIGATION');
        $response->assertSee(route('dashboard'), false);
        $response->assertSeeText('Dashboard');
        $response->assertSeeText('TERMINAL TYPE');
    }
}
