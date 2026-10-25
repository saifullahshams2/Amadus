<?php

namespace Tests\Feature;

use App\Models\Scenario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentInstructorAccountSeparationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_student_cannot_access_instructor_dashboard_perspective(): void
    {
        $student = User::where('role', 'student')->first();

        // 1. Passing role=instructor query parameter directly
        $response = $this->actingAs($student)->get('/dashboard?role=instructor');
        $response->assertStatus(403);

        // 2. Setting demo_role session to instructor cannot bypass student isolation
        $responseWithSession = $this->actingAs($student)
            ->withSession(['demo_role' => 'instructor'])
            ->get('/dashboard');

        $responseWithSession->assertStatus(200);
        $responseWithSession->assertSee('TRAINEE AGENT DESK');
        $responseWithSession->assertDontSee('INSTRUCTOR OPERATIONS HUB');
    }

    public function test_student_cannot_switch_role_to_instructor(): void
    {
        $student = User::where('role', 'student')->first();

        $response = $this->actingAs($student)->post('/dashboard/switch-role', [
            'role' => 'instructor',
        ]);

        $response->assertStatus(403);
    }

    public function test_student_cannot_access_instructor_web_hub(): void
    {
        $student = User::where('role', 'student')->first();

        $response = $this->actingAs($student)->get('/instructor');
        $response->assertStatus(403);
    }

    public function test_student_cannot_access_any_instructor_api_endpoints(): void
    {
        $student = User::where('role', 'student')->first();
        $this->actingAs($student);

        // 1. Trainee roster
        $this->getJson('/api/instructor/students')->assertStatus(403);

        // 2. Student creation
        $this->postJson('/api/instructor/students', [
            'name' => 'Test Trainee',
            'username' => 'testtrainee',
            'email' => 'trainee@example.com',
            'password' => 'secret123',
        ])->assertStatus(403);

        // 3. Batch creation
        $this->postJson('/api/instructor/students/batch', [
            'count' => 5,
        ])->assertStatus(403);

        // 4. Spectator
        $this->getJson('/api/instructor/spectate/student')->assertStatus(403);

        // 5. Scenario creation & deletion
        $this->postJson('/api/instructor/scenarios', [
            'id' => 'SCN-HACK-01',
            'title' => 'Malicious Scenario',
        ])->assertStatus(403);
        $this->deleteJson('/api/instructor/scenarios/SCN-RT-01')->assertStatus(403);

        // 6. Submissions grading
        $this->postJson('/api/instructor/submissions/1/grade', [
            'score' => 100,
        ])->assertStatus(403);

        // 7. Queue broadcast
        $this->postJson('/api/instructor/queues/broadcast', [
            'queue_number' => 1,
        ])->assertStatus(403);

        // 8. Sandbox reset
        $this->postJson('/api/instructor/sandbox/reset', [])->assertStatus(403);
    }

    public function test_unauthenticated_visitor_cannot_access_instructor_apis(): void
    {
        $response = $this->getJson('/api/instructor/students');
        $response->assertStatus(401);

        $webResponse = $this->get('/instructor');
        $webResponse->assertRedirect('/login');
    }

    public function test_instructor_has_full_access_to_dashboard_and_web_hub(): void
    {
        $instructor = User::where('role', 'instructor')->first();

        $dashboardRes = $this->actingAs($instructor)->get('/dashboard');
        $dashboardRes->assertStatus(200);
        $dashboardRes->assertSee('INSTRUCTOR OPERATIONS HUB');

        $hubRes = $this->actingAs($instructor)->get('/instructor');
        $hubRes->assertStatus(200);
        $hubRes->assertSee('Instructor Operations Hub');
    }

    public function test_instructor_dashboard_does_not_display_student_switcher(): void
    {
        $instructor = User::where('role', 'instructor')->first();

        $response = $this->actingAs($instructor)->get('/dashboard');
        $response->assertStatus(200);

        // Sidebar shows Instructor Account status and does not render role switcher
        $response->assertSee('ACCOUNT ACCESS');
        $response->assertSee('Operations Hub');
        $response->assertDontSee(route('dashboard.switch-role'));

        // Querying role=student as instructor strictly retains instructor dashboard
        $studentParamRes = $this->actingAs($instructor)->get('/dashboard?role=student');
        $studentParamRes->assertStatus(200);
        $studentParamRes->assertSee('INSTRUCTOR OPERATIONS HUB');
        $studentParamRes->assertDontSee('TRAINEE AGENT DESK');

        // Attempting to post to switch-role as authenticated instructor is forbidden
        $switchRes = $this->actingAs($instructor)->post('/dashboard/switch-role', ['role' => 'student']);
        $switchRes->assertStatus(403);
    }

    public function test_instructor_can_create_student_account_from_dashboard(): void
    {
        $instructor = User::where('role', 'instructor')->first();

        $payload = [
            'name' => 'Alexander Pilot',
            'username' => 'alex_pilot',
            'email' => 'alex.pilot@flightacademy.test',
            'password' => 'PilotPass@2026',
            'office_id' => 'LON1A0980',
            'agent_id' => 'ALEX01',
        ];

        $response = $this->actingAs($instructor)->postJson('/api/instructor/students', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'student' => [
                'username' => 'alex_pilot',
                'name' => 'Alexander Pilot',
                'email' => 'alex.pilot@flightacademy.test',
                'office_id' => 'LON1A0980',
                'agent_id' => 'ALEX01',
            ],
        ]);

        $this->assertDatabaseHas('users', [
            'username' => 'alex_pilot',
            'role' => 'student',
            'office_id' => 'LON1A0980',
            'agent_id' => 'ALEX01',
        ]);

        $createdUser = User::where('username', 'alex_pilot')->first();
        $this->assertTrue(Hash::check('PilotPass@2026', $createdUser->password));
        $this->assertTrue($createdUser->isStudent());
        $this->assertFalse($createdUser->isInstructor());
    }

    public function test_student_creation_cannot_be_manipulated_to_create_instructor(): void
    {
        $instructor = User::where('role', 'instructor')->first();

        $payload = [
            'name' => 'Sneaky Account',
            'username' => 'sneaky_inst',
            'email' => 'sneaky@test.com',
            'password' => 'secret123',
            'role' => 'instructor', // Attempting to escalate role
        ];

        $response = $this->actingAs($instructor)->postJson('/api/instructor/students', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'username' => 'sneaky_inst',
            'role' => 'student', // Forced to student
        ]);
        $this->assertDatabaseMissing('users', [
            'username' => 'sneaky_inst',
            'role' => 'instructor',
        ]);
    }

    public function test_instructor_student_creation_validates_uniqueness_and_rules(): void
    {
        $instructor = User::where('role', 'instructor')->first();

        // 1. Missing required fields
        $resEmpty = $this->actingAs($instructor)->postJson('/api/instructor/students', []);
        $resEmpty->assertStatus(422);
        $resEmpty->assertJsonValidationErrors(['name', 'username', 'email', 'password']);

        // 2. Duplicate username & email with existing demo student
        $resDuplicate = $this->actingAs($instructor)->postJson('/api/instructor/students', [
            'name' => 'Duplicate Student',
            'username' => 'student',
            'email' => 'student@amadeus-gds.demo',
            'password' => 'password123',
        ]);
        $resDuplicate->assertStatus(422);
        $resDuplicate->assertJsonValidationErrors(['username', 'email']);

        // 3. Short password
        $resShortPw = $this->actingAs($instructor)->postJson('/api/instructor/students', [
            'name' => 'Valid Name',
            'username' => 'valid_user',
            'email' => 'valid@test.com',
            'password' => '123',
        ]);
        $resShortPw->assertStatus(422);
        $resShortPw->assertJsonValidationErrors(['password']);
    }

    public function test_instructor_can_batch_create_unlimited_students(): void
    {
        $instructor = User::where('role', 'instructor')->first();

        $payload = [
            'count' => 12,
            'prefix' => 'batch_cadet',
            'password' => 'CadetPass@2026',
            'office_id' => 'LON1A0980',
        ];

        $response = $this->actingAs($instructor)->postJson('/api/instructor/students/batch', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'count' => 12,
        ]);

        $students = $response->json('students');
        $this->assertCount(12, $students);

        foreach ($students as $st) {
            $this->assertStringStartsWith('batch_cadet_', $st['username']);
            $this->assertDatabaseHas('users', [
                'username' => $st['username'],
                'role' => 'student',
            ]);
        }
    }

    public function test_newly_created_student_can_log_in_and_is_strictly_prevented_from_instructor_privileges(): void
    {
        $instructor = User::where('role', 'instructor')->first();

        // 1. Instructor creates student
        $this->actingAs($instructor)->postJson('/api/instructor/students', [
            'name' => 'New Trainee',
            'username' => 'newtrainee',
            'email' => 'newtrainee@academy.test',
            'password' => 'SecurePass123',
        ])->assertStatus(201);

        // 2. Newly created student logs in
        $loginRes = $this->postJson('/api/auth/login', [
            'username' => 'newtrainee',
            'password' => 'SecurePass123',
        ]);
        $loginRes->assertStatus(200);
        $loginRes->assertJson([
            'success' => true,
            'user' => [
                'username' => 'newtrainee',
                'role' => 'student',
            ],
        ]);

        // 3. New student is blocked from instructor access
        $newStudent = User::where('username', 'newtrainee')->first();
        $this->actingAs($newStudent);

        $this->get('/dashboard?role=instructor')->assertStatus(403);
        $this->get('/instructor')->assertStatus(403);
        $this->post('/dashboard/switch-role', ['role' => 'instructor'])->assertStatus(403);
        $this->getJson('/api/instructor/students')->assertStatus(403);
    }

    public function test_instructor_can_create_student_via_web_form(): void
    {
        $instructor = User::where('role', 'instructor')->first();

        $response = $this->actingAs($instructor)->post('/instructor/students', [
            'name' => 'Web Form Student',
            'username' => 'web_student',
            'email' => 'web_student@test.com',
            'password' => 'WebPass@123',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'username' => 'web_student',
            'role' => 'student',
        ]);
    }

    public function test_instructor_can_delete_scenario_via_web_route(): void
    {
        $instructor = User::where('role', 'instructor')->first();

        Scenario::create([
            'id' => 'SCN-WEB-DEL',
            'title' => 'Web Delete Drill',
            'category' => 'Round-trip',
            'difficulty' => 'Beginner',
            'description' => 'Test scenario deletion via web.',
            'time_limit_minutes' => 10,
            'passenger_profile' => ['names' => []],
            'flight_segments' => [],
            'validation_criteria' => [],
            'created_by' => 'instructor',
        ]);

        $response = $this->actingAs($instructor)->delete('/instructor/scenarios/SCN-WEB-DEL');
        $response->assertRedirect();
        $this->assertDatabaseMissing('scenarios', ['id' => 'SCN-WEB-DEL']);
    }

    public function test_cheat_sheet_is_removed_from_both_student_and_instructor_dashboards(): void
    {
        $student = User::where('role', 'student')->first();
        $instructor = User::where('role', 'instructor')->first();

        // Check student dashboard
        $studentResponse = $this->actingAs($student)->get('/dashboard');
        $studentResponse->assertStatus(200);
        $studentResponse->assertDontSee('Amadeus Cryptic Cheat Sheet');
        $studentResponse->assertDontSee('Essential entries by operational domain');

        // Check instructor dashboard
        $instructorResponse = $this->actingAs($instructor)->get('/dashboard');
        $instructorResponse->assertStatus(200);
        $instructorResponse->assertDontSee('Amadeus Cryptic Cheat Sheet');
        $instructorResponse->assertDontSee('Essential entries by operational domain');
    }
}
