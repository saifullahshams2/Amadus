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

    public function test_01_dashboard_layout_has_mobile_and_tablet_header_elements_for_student(): void
    {
        $student = User::where('role', 'student')->first();

        $response = $this->actingAs($student)->get('/dashboard');
        $response->assertStatus(200);

        // 1. Mobile & tablet header has logo on left (xl:hidden)
        $response->assertSee('xl:hidden', false);
        $response->assertSee('airplane-ticket.png', false);
        $response->assertSeeText('Amadus');

        // 2. Mobile & tablet header has 3-line menu icon button on right
        $response->assertSee('@click="mobileSidebarOpen = true"', false);
        $response->assertSee('d="M4 6h16M4 12h16M4 18h16"', false); // 3-line SVG path

        // 3. Sidebar drawer opens 80% with cross icon and 20% click-to-close backdrop
        $response->assertSee('w-[80vw]', false);
        $response->assertSee('max-w-[80vw]', false);
        $response->assertSee('@click="mobileSidebarOpen = false"', false); // Backdrop click & cross icon click
        $response->assertSee('d="M6 18L18 6M6 6l12 12"', false); // Cross icon SVG path

        // 4. All menu links moved into the mobile sidebar drawer
        $response->assertSeeText('TRAINEE DESK');
        $response->assertSee(route('dashboard'), false);
        $response->assertSee(route('dashboard.section', 'workspace'), false);
        $response->assertSee(route('dashboard.section', 'drills'), false);
        $response->assertSee(route('dashboard.section', 'validator'), false);
        $response->assertSee(route('dashboard.section', 'analytics'), false);
        $response->assertSee(route('terminal.index'), false);
    }

    public function test_02_dashboard_layout_has_mobile_and_tablet_header_elements_for_instructor(): void
    {
        $instructor = User::where('role', 'instructor')->first();

        $response = $this->actingAs($instructor)->get('/dashboard');
        $response->assertStatus(200);

        // 1. Header has logo on left and 3-line menu icon on right
        $response->assertSee('@click="mobileSidebarOpen = true"', false);
        $response->assertSee('d="M4 6h16M4 12h16M4 18h16"', false);

        // 2. 80% drawer with cross icon and backdrop click
        $response->assertSee('w-[80vw]', false);
        $response->assertSee('@click="mobileSidebarOpen = false"', false);
        $response->assertSee('d="M6 18L18 6M6 6l12 12"', false);

        // 3. All instructor menus moved into the sidebar drawer
        $response->assertSeeText('COMMAND CENTER');
        $response->assertSee(route('dashboard'), false);
        $response->assertSee(route('dashboard.section', 'classroom'), false);
        $response->assertSee(route('dashboard.section', 'students'), false);
        $response->assertSee(route('dashboard.section', 'builder'), false);
        $response->assertSee(route('dashboard.section', 'gradebook'), false);
        $response->assertSee(route('dashboard.section', 'queues'), false);
        $response->assertSee(route('dashboard.section', 'analytics'), false);
        $response->assertSee(route('terminal.index'), false);
    }

    public function test_03_index_landing_page_renders_as_is(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSeeText('Amadus');
        $response->assertSeeText('Master Global Distribution in');
    }

    public function test_04_terminal_page_has_sidebar_menu_and_3_line_icon_for_mobile_and_tablet(): void
    {
        $student = User::where('role', 'student')->first();

        $response = $this->actingAs($student)->get('/terminal');
        $response->assertStatus(200);

        // 1. Mobile & tablet header has logo on left (xl:hidden) and 3-line menu icon on right
        $response->assertSee('xl:hidden', false);
        $response->assertSee('airplane-ticket.png', false);
        $response->assertSee('@click="mobileSidebarOpen = true"', false);
        $response->assertSee('d="M4 6h16M4 12h16M4 18h16"', false); // 3-line SVG path

        // 2. Sidebar drawer opens 80% with cross icon and 20% click-to-close backdrop
        $response->assertSee('w-[80vw]', false);
        $response->assertSee('max-w-[80vw]', false);
        $response->assertSee('@click="mobileSidebarOpen = false"', false); // Backdrop click & cross icon click
        $response->assertSee('d="M6 18L18 6M6 6l12 12"', false); // Cross icon SVG path

        // 3. Navigation links back to dashboard are present in sidebar
        $response->assertSee(route('dashboard'), false);
    }

    public function test_05_terminal_page_has_all_terminal_types_in_sidebar_menu(): void
    {
        $student = User::where('role', 'student')->first();

        $response = $this->actingAs($student)->get('/terminal');
        $response->assertStatus(200);

        // 1. All terminal types moved to sidebar menu
        $response->assertSeeText('TERMINAL TYPE');
        $response->assertSeeText('Modern Web');
        $response->assertSeeText('Classic CRT Green');
        $response->assertSeeText('Classic CRT Amber');

        // 2. Terminal theme switching Alpine handlers exist in sidebar
        $response->assertSee('@click="theme = \'modern\'"', false);
        $response->assertSee('@click="theme = \'crt-green\'"', false);
        $response->assertSee('@click="theme = \'crt-amber\'"', false);
    }

    public function test_06_dynamic_custom_popup_is_rendered_and_available(): void
    {
        $student = User::where('role', 'student')->first();

        $response = $this->actingAs($student)->get('/dashboard');
        $response->assertStatus(200);

        // Verify dynamic custom popup elements are present
        $response->assertSee('id="dynamic-popup-container"', false);
        $response->assertSee('id="dynamic-popup-card"', false);
        $response->assertSee('window.customAlert', false);
        $response->assertSee('window.customConfirm', false);
        $response->assertSee('window.customPrompt', false);
    }

    public function test_07_dashboard_sections_removed_from_main_and_kept_in_subpages(): void
    {
        $student = User::where('role', 'student')->first();
        $instructor = User::where('role', 'instructor')->first();

        // 1. Student /dashboard does not have these sections
        $studentDash = $this->actingAs($student)->get('/dashboard');
        $studentDash->assertStatus(200);
        $studentDash->assertDontSeeText('AMADEUS Cryptic Terminal');
        $studentDash->assertDontSeeText('Assigned Booking Scenarios & Drills');
        $studentDash->assertDontSeeText('Interactive Cryptic Entry Validator');

        // But student subpages have them (AMADEUS Cryptic Terminal completely removed from workspace per requirements)
        $this->actingAs($student)->get('/dashboard/workspace')->assertDontSeeText('AMADEUS Cryptic Terminal');
        $this->actingAs($student)->get('/dashboard/workspace')->assertSeeText('Current Active PNR Status');
        $this->actingAs($student)->get('/dashboard/drills')->assertSeeText('Assigned Booking Scenarios & Drills');
        $this->actingAs($student)->get('/dashboard/validator')->assertSeeText('Interactive Cryptic Entry Validator');

        // 2. Instructor /dashboard does not have these sections
        $instructorDash = $this->actingAs($instructor)->get('/dashboard');
        $instructorDash->assertStatus(200);
        $instructorDash->assertDontSeeText('Live Classroom Grid & Spectator');
        $instructorDash->assertDontSeeText('Simulated Queue & Sandbox Management');
        $instructorDash->assertDontSeeText('Classroom Analytics & Audit Trail');

        // But instructor subpages have them
        $this->actingAs($instructor)->get('/dashboard/classroom')->assertSeeText('Live Classroom Grid & Spectator');
        $this->actingAs($instructor)->get('/dashboard/builder')->assertSeeText('Scenario & Drill Builder');
        $this->actingAs($instructor)->get('/dashboard/queues')->assertSeeText('Simulated Queue & Sandbox Management');
    }
}
