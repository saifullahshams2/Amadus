<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureAppIsInstalled;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResponsiveNavigationAndSidebarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        EnsureAppIsInstalled::$bypassInTesting = true;
        $this->seedTestUsers();
    }

    protected function user(): User
    {
        return User::where('username', 'student')->firstOrFail();
    }

    public function test_01_dashboard_layout_has_mobile_and_tablet_header_elements(): void
    {
        $response = $this->actingAs($this->user())->get('/dashboard');
        $response->assertStatus(200);

        $response->assertSee('xl:hidden', false);
        $response->assertSee('airplane-ticket.png', false);
        $response->assertSeeText('Amadus');
        $response->assertSee('@click="mobileSidebarOpen = true"', false);
        $response->assertSee('d="M4 6h16M4 12h16M4 18h16"', false);
        $response->assertSee('w-[80vw]', false);
        $response->assertSee('max-w-[80vw]', false);
        $response->assertSee('@click="mobileSidebarOpen = false"', false);
        $response->assertSee('d="M6 18L18 6M6 6l12 12"', false);

        $response->assertSeeText('AGENT DESK');
        $response->assertSee(route('dashboard'), false);
        $response->assertSee(route('dashboard.section', 'workspace'), false);
        $response->assertSee(route('dashboard.section', 'drills'), false);
        $response->assertDontSee(route('dashboard.section', 'validator'), false);
        $response->assertDontSee(route('dashboard.section', 'analytics'), false);
        $response->assertSee(route('terminal.index'), false);
    }

    public function test_02_dashboard_does_not_expose_admin_or_instructor_navigation(): void
    {
        $response = $this->actingAs($this->user())->get('/dashboard');
        $response->assertStatus(200);

        $response->assertDontSeeText('COMMAND CENTER');
        $response->assertDontSee(route('dashboard.section', 'classroom'), false);
        $response->assertDontSee(route('dashboard.section', 'students'), false);
        $response->assertDontSee(route('dashboard.section', 'builder'), false);
        $response->assertDontSee(route('dashboard.section', 'gradebook'), false);
    }

    public function test_03_index_main_page_redirects_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_04_terminal_page_has_sidebar_menu_and_3_line_icon(): void
    {
        $response = $this->actingAs($this->user())->get('/terminal');
        $response->assertStatus(200);

        $response->assertSee('xl:hidden', false);
        $response->assertSee('airplane-ticket.png', false);
        $response->assertSee('@click="mobileSidebarOpen = true"', false);
        $response->assertSee('d="M4 6h16M4 12h16M4 18h16"', false);
        $response->assertSee('w-[80vw]', false);
        $response->assertSee('max-w-[80vw]', false);
        $response->assertSee('@click="mobileSidebarOpen = false"', false);
        $response->assertSee('d="M6 18L18 6M6 6l12 12"', false);
        $response->assertSee(route('dashboard'), false);
    }

    public function test_05_terminal_page_has_all_terminal_types_in_sidebar_menu(): void
    {
        $response = $this->actingAs($this->user())->get('/terminal');
        $response->assertStatus(200);

        $response->assertSeeText('TERMINAL TYPE');
        $response->assertSeeText('Modern Web');
        $response->assertSeeText('Classic CRT Green');
        $response->assertSeeText('Classic CRT Amber');

        $response->assertSee('@click="theme = \'modern\'"', false);
        $response->assertSee('@click="theme = \'crt-green\'"', false);
        $response->assertSee('@click="theme = \'crt-amber\'"', false);
    }

    public function test_06_dynamic_custom_popup_is_rendered_and_available(): void
    {
        $response = $this->actingAs($this->user())->get('/dashboard');
        $response->assertStatus(200);

        $response->assertSee('id="dynamic-popup-container"', false);
        $response->assertSee('id="dynamic-popup-card"', false);
        $response->assertSee('window.customAlert', false);
        $response->assertSee('window.customConfirm', false);
        $response->assertSee('window.customPrompt', false);
    }

    public function test_07_training_hub_removed_and_validator_moved_to_main_dashboard(): void
    {
        $user = $this->user();

        $dashboard = $this->actingAs($user)->get('/dashboard');
        $dashboard->assertStatus(200);
        $dashboard->assertDontSeeText('AMADEUS Cryptic Terminal');
        $dashboard->assertDontSeeText('Trainee Training Modules & Workspace');
        $dashboard->assertDontSeeText('Assigned Booking Scenarios & Drills');
        $dashboard->assertSeeText('Interactive Cryptic Entry Validator');

        $this->actingAs($user)->get('/dashboard/workspace')->assertSeeText('Current Active PNR Status');
        $this->actingAs($user)->get('/dashboard/drills')->assertSeeText('Assigned Booking Scenarios & Drills');
        $this->actingAs($user)->get('/dashboard/validator')->assertRedirect(route('dashboard'));
        $this->actingAs($user)->get('/dashboard/analytics')->assertRedirect(route('dashboard'));
    }
}
