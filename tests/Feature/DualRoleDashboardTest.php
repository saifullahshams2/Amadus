<?php

namespace Tests\Feature;

use App\Models\Pnr;
use App\Models\QueueItem;
use App\Models\Scenario;
use App\Models\Submission;
use App\Models\TerminalActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DualRoleDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_01_student_dashboard_renders_successfully(): void
    {
        $student = User::where('role', 'student')->first();

        $response = $this->actingAs($student)->get('/dashboard?role=student');
        $response->assertStatus(200);
        $response->assertSee('TRAINEE AGENT DESK');
        $response->assertSee('Syntax Accuracy');
        $response->assertSee('Current Active PNR Status');
        $response->assertSeeText('Assigned Booking Scenarios & Drills');
        $response->assertSee('Interactive Cryptic Entry Validator');
        $response->assertDontSee('Amadeus Cryptic Cheat Sheet');
        $response->assertDontSee('Essential entries by operational domain');
    }

    public function test_02_instructor_dashboard_renders_successfully(): void
    {
        $instructor = User::where('role', 'instructor')->first();

        $response = $this->actingAs($instructor)->get('/dashboard?role=instructor');
        $response->assertStatus(200);
        $response->assertSee('INSTRUCTOR OPERATIONS HUB');
        $response->assertSeeText('Live Classroom Grid & Spectator');
        $response->assertSeeText('Scenario & Drill Builder');
        $response->assertSeeText('Submissions & Gradebook');
        $response->assertSeeText('Simulated Queue & Sandbox Management');
        $response->assertDontSee('Amadeus Cryptic Cheat Sheet');
        $response->assertDontSee('Essential entries by operational domain');
    }

    public function test_03_dev_demo_role_switcher_redirects_and_updates_role(): void
    {
        $response = $this->post('/dashboard/switch-role', ['role' => 'instructor']);
        $response->assertRedirect('/dashboard?role=instructor');
        $response->assertSessionHas('demo_role', 'instructor');

        $response2 = $this->post('/dashboard/switch-role', ['role' => 'student']);
        $response2->assertRedirect('/dashboard?role=student');
        $response2->assertSessionHas('demo_role', 'student');
    }

    public function test_04_active_pnr_api_returns_structured_data(): void
    {
        // Seed an active PNR for student
        Pnr::updateOrCreate(
            ['record_locator' => 'T7R2M9'],
            [
                'user_id' => 'student',
                'office_id' => 'LON1A0980',
                'status' => 'CONFIRMED',
                'data_json' => [
                    'recordLocator' => 'T7R2M9',
                    'passengers' => [
                        ['index' => 1, 'lastName' => 'SMITH', 'firstName' => 'JOHN', 'title' => 'MR', 'type' => 'ADT'],
                    ],
                    'segments' => [
                        ['segmentNumber' => 1, 'airlineCode' => 'BA', 'flightNumber' => '117', 'bookingClass' => 'Y', 'departureDate' => '15NOV', 'departureAirport' => 'LHR', 'arrivalAirport' => 'JFK', 'seats' => 1, 'departureTime' => '1030', 'arrivalTime' => '1345'],
                    ],
                    'contacts' => [
                        ['index' => 1, 'type' => 'PHONE', 'detail' => 'LON 44-20-7123-4567-A'],
                    ],
                    'ticketing' => ['raw' => 'TL15NOV/LON1A0980', 'type' => 'TL'],
                    'receivedFrom' => 'PASSENGER',
                ],
            ]
        );

        $student = User::where('username', 'student')->first();
        $response = $this->actingAs($student)->getJson('/api/dashboard/active-pnr');

        $response->assertStatus(200);
        $response->assertJson([
            'hasActivePnr' => true,
            'recordLocator' => 'T7R2M9',
            'status' => 'CONFIRMED',
            'officeId' => 'LON1A0980',
        ]);
        $this->assertCount(1, $response->json('passengers'));
        $this->assertCount(1, $response->json('segments'));
    }

    public function test_05_syntax_validator_api_provides_accurate_hints(): void
    {
        // Valid Availability command
        $resValid = $this->postJson('/api/dashboard/validate-syntax', [
            'command' => 'AN15NOVLHRJFK',
        ]);
        $resValid->assertStatus(200);
        $resValid->assertJson([
            'isValid' => true,
            'category' => 'Availability',
        ]);

        // Malformed Availability command
        $resInvalid = $this->postJson('/api/dashboard/validate-syntax', [
            'command' => 'ANXYZ',
        ]);
        $resInvalid->assertStatus(200);
        $resInvalid->assertJson([
            'isValid' => false,
            'category' => 'Availability',
        ]);
        $this->assertNotEmpty($resInvalid->json('correctionHint'));

        // Valid Passenger Name command
        $resName = $this->postJson('/api/dashboard/validate-syntax', [
            'command' => 'NM1DOE/JOHN MR',
        ]);
        $resName->assertStatus(200);
        $resName->assertJson([
            'isValid' => true,
            'category' => 'PNR Elements',
        ]);
    }

    public function test_06_scenario_submission_and_automated_grading(): void
    {
        $student = User::where('username', 'student')->first();

        $pnrData = [
            'record_locator' => 'X9L3P2',
            'passengers' => [
                ['index' => 1, 'lastName' => 'SMITH', 'firstName' => 'JOHN', 'title' => 'MR', 'type' => 'ADT'],
            ],
            'segments' => [
                ['segmentNumber' => 1, 'airlineCode' => 'BA', 'flightNumber' => '117', 'bookingClass' => 'Y', 'departureDate' => '15NOV', 'departureAirport' => 'LHR', 'arrivalAirport' => 'JFK', 'seats' => 1, 'departureTime' => '1030', 'arrivalTime' => '1345'],
                ['segmentNumber' => 2, 'airlineCode' => 'BA', 'flightNumber' => '178', 'bookingClass' => 'Y', 'departureDate' => '22NOV', 'departureAirport' => 'JFK', 'arrivalAirport' => 'LHR', 'seats' => 1, 'departureTime' => '1830', 'arrivalTime' => '0645'],
            ],
            'contacts' => [
                ['index' => 1, 'type' => 'PHONE', 'detail' => 'LON 44-20-7946-0123-A'],
            ],
            'ticketing' => ['raw' => 'TL15NOV/LON1A0980'],
            'receivedFrom' => 'PASSENGER',
            'tst' => ['currency' => 'GBP', 'baseFare' => 500, 'totalAmount' => 645.20],
        ];

        $response = $this->actingAs($student)->postJson('/api/dashboard/scenarios/SCN-RT-01/submit', [
            'pnrData' => $pnrData,
            'terminalLogs' => [
                ['command' => 'AN15NOVLHRJFK', 'is_error' => false],
                ['command' => 'SS1Y1', 'is_error' => false],
                ['command' => 'NM1SMITH/JOHN MR', 'is_error' => false],
                ['command' => 'AP LON 44-20-7946-0123-A', 'is_error' => false],
                ['command' => 'TKTL15NOV/LON1A0980', 'is_error' => false],
                ['command' => 'RF PASSENGER', 'is_error' => false],
                ['command' => 'FXP', 'is_error' => false],
            ],
            'timeElapsedSeconds' => 320,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $grade = $response->json('grade');
        $this->assertGreaterThanOrEqual(80, $grade['score']);
        $this->assertTrue($grade['passed']);
        $this->assertEquals(6, $grade['elementsPassedCount']);
    }

    public function test_07_instructor_can_spectate_student_keystrokes(): void
    {
        // Seed logs for student
        TerminalActivityLog::create([
            'user_id' => 'student',
            'command' => 'AN15NOVLHRJFK',
            'output_lines' => ['1 BA 117 Y9 B9'],
            'is_error' => false,
            'command_type' => 'AVAILABILITY',
            'execution_time_ms' => 45,
            'created_at' => now(),
        ]);

        $instructor = User::where('role', 'instructor')->first();
        $response = $this->actingAs($instructor)->getJson('/api/instructor/spectate/student');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'student' => ['username', 'name', 'office_id'],
            'logs' => [
                '*' => ['id', 'command', 'output_lines', 'is_error', 'command_type'],
            ],
        ]);
        $this->assertNotEmpty($response->json('logs'));
    }

    public function test_08_instructor_scenario_builder_creates_new_drill(): void
    {
        $instructor = User::where('role', 'instructor')->first();

        $payload = [
            'id' => 'SCN-TEST-99',
            'title' => 'Express One-Way Booking',
            'category' => 'Round-trip',
            'difficulty' => 'Beginner',
            'description' => 'Book a simple one-way domestic ticket.',
            'time_limit_minutes' => 15,
            'passenger_profile' => [
                'names' => [['lastName' => 'BROWN', 'firstName' => 'CHARLIE', 'title' => 'MR', 'type' => 'ADT']],
            ],
            'flight_segments' => [
                ['origin' => 'LHR', 'destination' => 'MAN', 'date' => '10DEC', 'airline' => 'BA'],
            ],
            'validation_criteria' => [
                'mandatoryPrint' => ['name' => true, 'itinerary' => true, 'contact' => true, 'ticketing' => true, 'receivedFrom' => true],
                'minSegments' => 1,
                'requireTst' => false,
                'requireETicket' => false,
            ],
        ];

        $response = $this->actingAs($instructor)->postJson('/api/instructor/scenarios', $payload);
        $response->assertStatus(201);
        $this->assertDatabaseHas('scenarios', ['id' => 'SCN-TEST-99']);
    }

    public function test_09_instructor_can_override_grade_and_add_feedback(): void
    {
        $submission = Submission::where('user_id', 'student')->first();
        $this->assertNotNull($submission);

        $instructor = User::where('role', 'instructor')->first();
        $response = $this->actingAs($instructor)->postJson("/api/instructor/submissions/{$submission->id}/grade", [
            'score' => 98,
            'feedback' => 'Superb command efficiency and zero syntax errors!',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'submission' => [
                'manual_override_score' => 98,
                'instructor_feedback' => 'Superb command efficiency and zero syntax errors!',
                'status' => 'reviewed',
            ],
        ]);
    }

    public function test_10_simulated_queue_broadcast_and_cryptic_queue_commands(): void
    {
        $instructor = User::where('role', 'instructor')->first();

        // 1. Broadcast PNR to Queue 7
        $broadcastRes = $this->actingAs($instructor)->postJson('/api/instructor/queues/broadcast', [
            'queue_number' => 7,
            'category' => 'SCHEDULE_CHANGE',
            'pnr_locator' => 'Z8X4V1',
            'passenger' => 'TAYLOR/EMMA MS',
            'itinerary' => 'LHR-DOH (QR 004)',
            'urgency' => 'HIGH',
            'message' => 'FLIGHT QR 004 DEPARTURE ADVANCED BY 30 MINUTES',
            'target_user_id' => 'student',
        ]);

        $broadcastRes->assertStatus(201);
        $this->assertDatabaseHas('queue_items', [
            'pnr_locator' => 'Z8X4V1',
            'queue_number' => 7,
        ]);

        $student = User::where('username', 'student')->first();

        // 2. Execute Amadeus cryptic entry QS (Queue Summary)
        $state = ['area' => 'A', 'officeId' => 'LON1A0980', 'agentId' => '1234AB'];
        $qsRes = $this->actingAs($student)->postJson('/api/terminal/execute', ['command' => 'QS', 'state' => $state]);
        $qsRes->assertStatus(200);
        $qsText = implode(' ', $qsRes->json('outputLines'));
        $this->assertStringContainsString('AMADEUS QUEUE BANK SUMMARY', $qsText);

        // 3. Execute Amadeus cryptic entry QD 7 (Queue Display 7)
        $qdRes = $this->actingAs($student)->postJson('/api/terminal/execute', ['command' => 'QD 7', 'state' => $state]);
        $qdRes->assertStatus(200);
        $qdText = implode(' ', $qdRes->json('outputLines'));
        $this->assertStringContainsString('QUEUE DISPLAY: Q7', $qdText);
        $this->assertStringContainsString('Z8X4V1', $qdText);
    }

    public function test_11_instructor_can_reset_sandbox_state(): void
    {
        $instructor = User::where('role', 'instructor')->first();

        // Create a test queue item and log for student
        QueueItem::create([
            'queue_number' => 9,
            'category' => 'GENERAL_MESSAGE',
            'office_id' => 'LON1A0980',
            'target_user_id' => 'student',
            'pnr_locator' => 'DEL123',
            'pnr_summary' => ['passenger' => 'TEST/USER MR', 'itinerary' => 'LHR-JFK', 'urgency' => 'NORMAL'],
            'message' => 'Temporary test message',
            'status' => 'PENDING',
        ]);

        $this->assertDatabaseHas('queue_items', ['pnr_locator' => 'DEL123']);

        $resetRes = $this->actingAs($instructor)->postJson('/api/instructor/sandbox/reset', [
            'target_user_id' => 'student',
        ]);

        $resetRes->assertStatus(200);
        $resetRes->assertJson(['success' => true]);
        $this->assertDatabaseMissing('queue_items', ['pnr_locator' => 'DEL123']);
    }

    public function test_12_instructor_can_delete_scenario_and_drill(): void
    {
        $instructor = User::where('role', 'instructor')->first();

        // 1. Create a drill with submission
        $scenario = Scenario::create([
            'id' => 'SCN-DELETE-ME',
            'title' => 'Temporary Drill To Remove',
            'category' => 'Round-trip',
            'difficulty' => 'Beginner',
            'description' => 'Test scenario deletion.',
            'time_limit_minutes' => 15,
            'passenger_profile' => ['names' => []],
            'flight_segments' => [],
            'validation_criteria' => ['minSegments' => 1],
            'created_by' => 'instructor',
        ]);

        Submission::create([
            'scenario_id' => 'SCN-DELETE-ME',
            'user_id' => 'student',
            'status' => 'submitted',
            'score' => 85,
        ]);

        $this->assertDatabaseHas('scenarios', ['id' => 'SCN-DELETE-ME']);
        $this->assertDatabaseHas('submissions', ['scenario_id' => 'SCN-DELETE-ME']);

        // 2. Instructor deletes the scenario
        $deleteRes = $this->actingAs($instructor)->deleteJson('/api/instructor/scenarios/SCN-DELETE-ME');
        $deleteRes->assertStatus(200);
        $deleteRes->assertJson([
            'success' => true,
            'deleted_id' => 'SCN-DELETE-ME',
        ]);

        // 3. Confirm scenario and related submissions are removed
        $this->assertDatabaseMissing('scenarios', ['id' => 'SCN-DELETE-ME']);
        $this->assertDatabaseMissing('submissions', ['scenario_id' => 'SCN-DELETE-ME']);

        // 4. Deleting again returns 404
        $repeatDeleteRes = $this->actingAs($instructor)->deleteJson('/api/instructor/scenarios/SCN-DELETE-ME');
        $repeatDeleteRes->assertStatus(404);
    }
}
