<?php

namespace Tests\Feature;

use App\Models\LessonProgress;
use App\Models\Pnr;
use App\Models\QueueItem;
use App\Models\Submission;
use App\Models\TerminalActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InstructorStudentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->seedTestUsers();
    }

    public function test_terminal_access_is_role_based(): void
    {
        $student = User::where('role', 'student')->first();
        $instructor = User::where('role', 'instructor')->first();

        // 1. Guest redirected to landing
        $this->get('/terminal')->assertRedirect('/');

        // 2. Unapproved student redirected to pending approval
        $unapproved = User::create([
            'name' => 'Unapproved Cadet',
            'username' => 'unapproved_cadet',
            'email' => 'unapproved@test.com',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'is_approved' => false,
            'office_id' => 'LON1A0980',
            'agent_id' => 'UA01AB',
        ]);
        $this->actingAs($unapproved)->get('/terminal')->assertRedirect('/pending-approval');

        // 3. Approved student gets terminal
        $responseStudent = $this->actingAs($student)->get('/terminal');
        $responseStudent->assertStatus(200);
        $responseStudent->assertSee('Amadus');
        $responseStudent->assertSee('WORK AREA:');

        // 4. Instructor gets terminal
        $responseInstructor = $this->actingAs($instructor)->get('/terminal');
        $responseInstructor->assertStatus(200);
        $responseInstructor->assertSee('Amadus');
        $responseInstructor->assertSee('WORK AREA:');
    }

    public function test_terminal_button_removed_from_dashboard_left_menu_and_header_points_to_terminal(): void
    {
        $student = User::where('role', 'student')->first();
        $response = $this->actingAs($student)->get('/dashboard');

        $response->assertStatus(200);
        // Ensure the old left-menu item is removed
        $response->assertDontSee('TERMINAL EMULATOR');
        // Ensure Launch Terminal button in top header points to /terminal
        $response->assertSee(route('terminal.index'), false);
    }

    public function test_instructor_can_delete_student_and_cascade_related_records_via_web(): void
    {
        $instructor = User::where('role', 'instructor')->first();

        $student = User::create([
            'name' => 'Delete Me Cadet',
            'username' => 'deleteme',
            'email' => 'deleteme@test.com',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'is_approved' => true,
            'office_id' => 'LON1A0980',
            'agent_id' => 'DM01AB',
        ]);

        // Create related records
        Pnr::create([
            'user_id' => 'deleteme',
            'record_locator' => 'DEL123',
            'status' => 'confirmed',
            'office_id' => 'LON1A0980',
            'data_json' => ['passengers' => []],
        ]);

        LessonProgress::create([
            'user_id' => 'deleteme',
            'lesson_id' => 'lesson-01',
            'module_id' => 'module-01',
            'status' => 'completed',
            'completed_objectives_json' => json_encode(['obj-1']),
        ]);

        Submission::create([
            'user_id' => 'deleteme',
            'scenario_id' => 'SCN-01',
            'score' => 95,
            'status' => 'submitted',
        ]);

        TerminalActivityLog::create([
            'user_id' => 'deleteme',
            'command' => 'AN 15OCT LONPAR',
            'output_lines' => ['AVAILABLE FLIGHTS'],
            'is_error' => false,
            'command_type' => 'availability',
            'execution_time_ms' => 50,
        ]);

        QueueItem::create([
            'queue_number' => 80,
            'pnr_locator' => 'DEL123',
            'pnr_summary' => '1.SMITH/J',
            'category' => 'General',
            'target_user_id' => 'deleteme',
            'status' => 'pending',
            'message' => 'Test queue item',
        ]);

        $response = $this->actingAs($instructor)->delete("/instructor/students/{$student->id}");
        $response->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $student->id]);
        $this->assertDatabaseMissing('pnrs', ['user_id' => 'deleteme']);
        $this->assertDatabaseMissing('lesson_progress', ['user_id' => 'deleteme']);
        $this->assertDatabaseMissing('submissions', ['user_id' => 'deleteme']);
        $this->assertDatabaseMissing('terminal_activity_logs', ['user_id' => 'deleteme']);
        $this->assertDatabaseMissing('queue_items', ['target_user_id' => 'deleteme']);
    }

    public function test_instructor_can_delete_student_via_api(): void
    {
        $instructor = User::where('role', 'instructor')->first();

        $student = User::create([
            'name' => 'API Delete Cadet',
            'username' => 'apidelete',
            'email' => 'apidelete@test.com',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'is_approved' => true,
            'office_id' => 'LON1A0980',
            'agent_id' => 'AP01AB',
        ]);

        $response = $this->actingAs($instructor)->deleteJson("/api/instructor/students/{$student->id}");
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('users', ['id' => $student->id]);
    }

    public function test_student_cannot_delete_student_account(): void
    {
        $student = User::where('role', 'student')->first();

        $target = User::create([
            'name' => 'Target Cadet',
            'username' => 'targetcadet',
            'email' => 'target@test.com',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'is_approved' => true,
            'office_id' => 'LON1A0980',
            'agent_id' => 'TC01AB',
        ]);

        $this->actingAs($student)->delete("/instructor/students/{$target->id}")->assertStatus(403);
        $this->actingAs($student)->deleteJson("/api/instructor/students/{$target->id}")->assertStatus(403);

        $this->assertDatabaseHas('users', ['id' => $target->id]);
    }

    public function test_instructor_cannot_delete_another_instructor(): void
    {
        $instructor = User::where('role', 'instructor')->first();

        $otherInstructor = User::create([
            'name' => 'Other Flight Instructor',
            'username' => 'other_instructor',
            'email' => 'other_instructor@test.com',
            'password' => Hash::make('password123'),
            'role' => 'instructor',
            'is_approved' => true,
            'office_id' => 'LON1A0980',
            'agent_id' => 'OI01AB',
        ]);

        $this->actingAs($instructor)->delete("/instructor/students/{$otherInstructor->id}")->assertStatus(404);
        $this->actingAs($instructor)->deleteJson("/api/instructor/students/{$otherInstructor->id}")->assertStatus(404);

        $this->assertDatabaseHas('users', ['id' => $otherInstructor->id]);
    }

    public function test_instructor_can_login_directly_to_student_portal_without_password(): void
    {
        $instructor = User::where('role', 'instructor')->first();
        $student = User::where('role', 'student')->first();

        // 1. Post to impersonate
        $response = $this->actingAs($instructor)->post("/instructor/students/{$student->id}/impersonate");
        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('impersonated_by_instructor', $instructor->id);
        $response->assertSessionHas('impersonated_student_id', $student->id);

        // 2. Follow redirect to /dashboard: authenticated user is student, sees Trainee Desk and Impersonation Banner
        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('TRAINEE AGENT DESK');
        $dashboardResponse->assertSee('INSTRUCTOR MODE:');
        $dashboardResponse->assertSee('Exit to Instructor Hub');

        // 3. Follow to /terminal: sees Impersonation Banner in terminal
        $terminalResponse = $this->get('/terminal');
        $terminalResponse->assertStatus(200);
        $terminalResponse->assertSee('INSTRUCTOR MODE:');
        $terminalResponse->assertSee('Exit to Instructor Hub');
    }

    public function test_instructor_can_login_directly_to_student_portal_via_api(): void
    {
        $instructor = User::where('role', 'instructor')->first();
        $student = User::where('role', 'student')->first();

        $response = $this->actingAs($instructor)->postJson("/api/instructor/students/{$student->id}/impersonate");
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect' => '/dashboard',
        ]);
        $response->assertSessionHas('impersonated_by_instructor', $instructor->id);
    }

    public function test_student_cannot_impersonate_another_student(): void
    {
        $student = User::where('role', 'student')->first();

        $target = User::create([
            'name' => 'Target Cadet',
            'username' => 'targetcadet2',
            'email' => 'target2@test.com',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'is_approved' => true,
            'office_id' => 'LON1A0980',
            'agent_id' => 'TC02AB',
        ]);

        $this->actingAs($student)->post("/instructor/students/{$target->id}/impersonate")->assertStatus(403);
        $this->actingAs($student)->postJson("/api/instructor/students/{$target->id}/impersonate")->assertStatus(403);
    }

    public function test_instructor_can_exit_impersonation_and_restore_instructor_session(): void
    {
        $instructor = User::where('role', 'instructor')->first();
        $student = User::where('role', 'student')->first();

        // 1. Instructor impersonates student
        $this->actingAs($instructor)->post("/instructor/students/{$student->id}/impersonate");

        // 2. Verify student is logged in
        $this->assertEquals($student->id, auth()->id());

        // 3. Exit impersonation via POST
        $exitResponse = $this->post('/instructor/exit-impersonation');
        $exitResponse->assertRedirect('/dashboard');

        // 4. Verify instructor session restored
        $this->assertEquals($instructor->id, auth()->id());
        $this->assertNull(session('impersonated_by_instructor'));

        // 5. Follow to /dashboard: sees Instructor Operations Hub
        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('INSTRUCTOR OPERATIONS HUB');
        $dashboardResponse->assertDontSee('Exit to Instructor Hub');
    }
}
