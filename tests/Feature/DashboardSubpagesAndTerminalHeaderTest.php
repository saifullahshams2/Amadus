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

    public function test_01_left_menu_has_dashboard_option_and_dedicated_subpages_for_student(): void
    {
        $student = User::where('role', 'student')->first();

        $response = $this->actingAs($student)->get('/dashboard');
        $response->assertStatus(200);

        // 1. Dashboard option pointing to /dashboard
        $response->assertSee(route('dashboard'), false);
        $response->assertSeeText('Dashboard');

        // 2. Dedicated sub-pages under /dashboard/
        $response->assertSee(route('dashboard.section', 'workspace'), false);
        $response->assertSee(route('dashboard.section', 'drills'), false);
        $response->assertSee(route('dashboard.section', 'validator'), false);
        $response->assertSee(route('dashboard.section', 'analytics'), false);
    }

    public function test_02_student_can_access_dedicated_subpages(): void
    {
        $student = User::where('role', 'student')->first();

        // Workspace sub-page
        $resWorkspace = $this->actingAs($student)->get('/dashboard/workspace');
        $resWorkspace->assertStatus(200);
        $resWorkspace->assertSeeText('OVERVIEW & ACTIVE PNR');
        $resWorkspace->assertSeeText('Current Active PNR Status');

        // Drills sub-page
        $resDrills = $this->actingAs($student)->get('/dashboard/drills');
        $resDrills->assertStatus(200);
        $resDrills->assertSeeText('ASSIGNMENTS & DRILLS');
        $resDrills->assertSeeText('Assigned Booking Scenarios & Drills');

        // Validator sub-page
        $resValidator = $this->actingAs($student)->get('/dashboard/validator');
        $resValidator->assertStatus(200);
        $resValidator->assertSeeText('CRYPTIC SYNTAX VALIDATOR');
        $resValidator->assertSeeText('Interactive Cryptic Entry Validator');

        // Analytics sub-page
        $resAnalytics = $this->actingAs($student)->get('/dashboard/analytics');
        $resAnalytics->assertStatus(200);
        $resAnalytics->assertSeeText('LEARNING ANALYTICS');
        $resAnalytics->assertSeeText('Syntax Accuracy');

        // Accessing instructor subpage redirects student back to /dashboard
        $resForbidden = $this->actingAs($student)->get('/dashboard/classroom');
        $resForbidden->assertRedirect(route('dashboard'));
    }

    public function test_03_left_menu_has_dashboard_option_and_dedicated_subpages_for_instructor(): void
    {
        $instructor = User::where('role', 'instructor')->first();

        $response = $this->actingAs($instructor)->get('/dashboard');
        $response->assertStatus(200);

        // 1. Dashboard option pointing to /dashboard
        $response->assertSee(route('dashboard'), false);
        $response->assertSeeText('Dashboard');

        // 2. Dedicated sub-pages under /dashboard/
        $response->assertSee(route('dashboard.section', 'classroom'), false);
        $response->assertSee(route('dashboard.section', 'students'), false);
        $response->assertSee(route('dashboard.section', 'builder'), false);
        $response->assertSee(route('dashboard.section', 'gradebook'), false);
        $response->assertSee(route('dashboard.section', 'queues'), false);
        $response->assertSee(route('dashboard.section', 'analytics'), false);
    }

    public function test_04_instructor_can_access_dedicated_subpages(): void
    {
        $instructor = User::where('role', 'instructor')->first();

        // Classroom sub-page
        $resClassroom = $this->actingAs($instructor)->get('/dashboard/classroom');
        $resClassroom->assertStatus(200);
        $resClassroom->assertSeeText('LIVE CLASSROOM & SPECTATOR');
        $resClassroom->assertSeeText('Live Classroom Grid & Spectator');

        // Students sub-page
        $resStudents = $this->actingAs($instructor)->get('/dashboard/students');
        $resStudents->assertStatus(200);
        $resStudents->assertSeeText('ENROLLED STUDENT ROSTER');
        $resStudents->assertSeeText('Enrolled Student Roster');

        // Builder sub-page
        $resBuilder = $this->actingAs($instructor)->get('/dashboard/builder');
        $resBuilder->assertStatus(200);
        $resBuilder->assertSeeText('SCENARIO & DRILL BUILDER');
        $resBuilder->assertSeeText('Scenario & Drill Builder');

        // Gradebook sub-page
        $resGradebook = $this->actingAs($instructor)->get('/dashboard/gradebook');
        $resGradebook->assertStatus(200);
        $resGradebook->assertSeeText('SUBMISSIONS & GRADEBOOK');
        $resGradebook->assertSeeText('Submissions & Gradebook');

        // Queues sub-page
        $resQueues = $this->actingAs($instructor)->get('/dashboard/queues');
        $resQueues->assertStatus(200);
        $resQueues->assertSeeText('QUEUE & SANDBOX MANAGER');
        $resQueues->assertSeeText('Simulated Queue & Sandbox Management');

        // Analytics sub-page
        $resAnalytics = $this->actingAs($instructor)->get('/dashboard/analytics');
        $resAnalytics->assertStatus(200);
        $resAnalytics->assertSeeText('CLASSROOM ANALYTICS & AUDIT TRAIL');
        $resAnalytics->assertSeeText('Enrolled Trainees');
        $resAnalytics->assertSeeText('Student PNR Audit Trail');
    }

    public function test_05_terminal_page_header_renamed_to_dashboard_on_right_and_operation_hub_removed(): void
    {
        $instructor = User::where('role', 'instructor')->first();

        $response = $this->actingAs($instructor)->get('/terminal');
        $response->assertStatus(200);

        // Header button renamed from 'Dashboard Hub' to 'Dashboard' and points to /dashboard
        $response->assertSee(route('dashboard'), false);
        $response->assertSeeText('Dashboard');
        $response->assertDontSeeText('Dashboard Hub');

        // Operation hub / Instructor Hub link removed from terminal header
        $response->assertDontSeeText('Instructor Hub');

        // Retains current username
        $response->assertSeeText($instructor->username);
    }

    public function test_06_instructor_page_contents_migrated_to_dashboard_as_analytics(): void
    {
        $instructor = User::where('role', 'instructor')->first();

        // 1. Dedicated analytics sub-page
        $analyticsRes = $this->actingAs($instructor)->get('/dashboard/analytics');
        $analyticsRes->assertStatus(200);
        $analyticsRes->assertSeeText('Classroom Analytics & Audit Trail');
        $analyticsRes->assertSeeText('Enrolled Trainees');
        $analyticsRes->assertSeeText('Lessons Attempted');
        $analyticsRes->assertSeeText('Committed PNR Records');
        $analyticsRes->assertSeeText('Student PNR Audit Trail');

        // 2. Overview dashboard removed analytics per requirements (kept on /dashboard/analytics)
        $dashRes = $this->actingAs($instructor)->get('/dashboard');
        $dashRes->assertStatus(200);
        $dashRes->assertDontSeeText('Classroom Analytics & Audit Trail');
    }

    public function test_07_terminal_page_portal_navigation_removed_and_dashboard_button_on_top(): void
    {
        $student = User::where('role', 'student')->first();
        $instructor = User::where('role', 'instructor')->first();

        foreach ([$student, $instructor] as $user) {
            $response = $this->actingAs($user)->get('/terminal');
            $response->assertStatus(200);

            // PORTAL NAVIGATION section removed
            $response->assertDontSeeText('PORTAL NAVIGATION');

            // Dashboard button is present
            $response->assertSee(route('dashboard'), false);
            $response->assertSeeText('Dashboard');
            $response->assertSeeText('TERMINAL TYPE');
        }
    }
}
