<?php

namespace Tests\Feature;

use App\Models\Pnr;
use App\Models\QueueItem;
use App\Models\Scenario;
use App\Models\Submission;
use App\Models\TerminalActivityLog;
use App\Models\User;
use App\Services\Installer\InstallerService;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->seedTestUsers();
    }

    public function test_registration_cannot_escalate_to_instructor_role(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'username' => 'attacker',
            'email' => 'attacker@example.com',
            'password' => 'secret123',
            'role' => 'instructor',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'username' => 'attacker',
            'role' => 'student',
        ]);
        $this->assertDatabaseMissing('users', [
            'username' => 'attacker',
            'role' => 'instructor',
        ]);
    }

    public function test_web_terminal_execute_enforces_csrf_protection(): void
    {
        // 1. Verify terminal/execute is NOT in the CSRF exclusion list
        $middlewareInstance = app(PreventRequestForgery::class);
        $excluded = $middlewareInstance->getExcludedPaths();
        $this->assertNotContains('terminal/execute', $excluded);
        $this->assertNotContains('api/*', $excluded);

        // 2. Test CSRF enforcement handler rejects request missing CSRF token
        $activeCsrfMiddleware = new class(app(), app(Encrypter::class)) extends PreventRequestForgery
        {
            protected function runningUnitTests(): bool
            {
                return false;
            }
        };

        $session = app('session')->driver();
        $session->setId('csrf-test-session');
        $session->start();

        $invalidRequest = Request::create('/terminal/execute', 'POST', ['command' => 'HE']);
        $invalidRequest->setLaravelSession($session);

        $this->expectException(TokenMismatchException::class);
        $activeCsrfMiddleware->handle($invalidRequest, fn ($req) => response('OK'));
    }

    public function test_csrf_middleware_accepts_valid_token(): void
    {
        $activeCsrfMiddleware = new class(app(), app(Encrypter::class)) extends PreventRequestForgery
        {
            protected function runningUnitTests(): bool
            {
                return false;
            }
        };

        $session = app('session')->driver();
        $session->setId('csrf-test-session');
        $session->start();

        $validRequest = Request::create('/terminal/execute', 'POST', ['command' => 'HE']);
        $validRequest->setLaravelSession($session);
        $validRequest->headers->set('X-CSRF-TOKEN', $session->token());

        $response = $activeCsrfMiddleware->handle($validRequest, fn ($req) => response('OK'));
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_response_includes_security_headers(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_student_cannot_retrieve_or_overwrite_another_students_pnr(): void
    {
        // 1. Create PNR owned by student
        Pnr::create([
            'record_locator' => 'SEC001',
            'user_id' => 'student',
            'office_id' => 'LON1A0980',
            'status' => 'CONFIRMED',
            'data_json' => [
                'recordLocator' => 'SEC001',
                'passengers' => [
                    ['lastName' => 'VICTIM', 'firstName' => 'ALICE'],
                ],
                'segments' => [],
            ],
        ]);

        // 2. Register and log in as a second student
        $user2 = User::create([
            'username' => 'student2',
            'name' => 'Student Two',
            'email' => 'student2@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'is_approved' => true,
            'office_id' => 'LON1A0980',
            'agent_id' => '5678CD',
        ]);

        $this->actingAs($user2);

        // 3. Attempt to retrieve student1's PNR
        $res = $this->postJson('/api/terminal/execute', [
            'command' => 'RT SEC001',
            'state' => [],
        ]);

        $res->assertStatus(200);
        $this->assertTrue($res->json('isError'));
        $output = implode(' ', $res->json('outputLines'));
        $this->assertStringContainsString('ACCESS RESTRICTED', $output);

        // 4. Attempt to overwrite student1's PNR via ET
        $tamperedState = [
            'officeId' => 'LON1A0980',
            'agentId' => '5678CD',
            'activePnr' => [
                'recordLocator' => 'SEC001',
                'passengers' => [
                    ['lastName' => 'ATTACKER', 'firstName' => 'BOB'],
                ],
                'segments' => [
                    [
                        'segmentNumber' => 1,
                        'airlineCode' => 'BA',
                        'flightNumber' => '175',
                        'bookingClass' => 'Y',
                        'departureDate' => '15NOV',
                        'departureAirport' => 'LHR',
                        'arrivalAirport' => 'JFK',
                        'departureTime' => '1000',
                        'arrivalTime' => '1300',
                        'seats' => 1,
                        'status' => 'HK',
                    ],
                ],
                'contacts' => [
                    ['id' => 1, 'type' => 'PHONE', 'detail' => 'LON 123456'],
                ],
                'ticketing' => ['type' => 'TKOK', 'raw' => 'TKOK'],
                'receivedFrom' => 'ATTACKER',
            ],
        ];

        $etRes = $this->postJson('/api/terminal/execute', [
            'command' => 'ET',
            'state' => $tamperedState,
        ]);

        $etRes->assertStatus(200);
        $this->assertTrue($etRes->json('isError'));
        $etOutput = implode(' ', $etRes->json('outputLines'));
        $this->assertStringContainsString('UNAUTHORIZED TO OVERWRITE', $etOutput);

        // Verify the original PNR was not altered
        $pnrInDb = Pnr::find('SEC001');
        $this->assertEquals('student', $pnrInDb->user_id);
        $this->assertEquals('VICTIM', $pnrInDb->data_json['passengers'][0]['lastName']);
    }

    public function test_instructor_can_retrieve_any_student_pnr(): void
    {
        Pnr::create([
            'record_locator' => 'INS001',
            'user_id' => 'student',
            'office_id' => 'LON1A0980',
            'status' => 'CONFIRMED',
            'data_json' => [
                'recordLocator' => 'INS001',
                'passengers' => [
                    ['lastName' => 'SMITH', 'firstName' => 'JOHN'],
                ],
                'segments' => [],
            ],
        ]);

        $instructor = User::where('role', 'instructor')->first();
        $this->actingAs($instructor);

        $res = $this->postJson('/api/terminal/execute', [
            'command' => 'RT INS001',
            'state' => [],
        ]);

        $res->assertStatus(200);
        $this->assertFalse($res->json('isError'));
        $output = implode(' ', $res->json('outputLines'));
        $this->assertStringContainsString('SMITH/JOHN', $output);
    }

    public function test_unauthenticated_user_cannot_save_or_view_training_progress(): void
    {
        $getProgress = $this->getJson('/api/training/progress');
        $getProgress->assertStatus(401);

        $saveProgress = $this->postJson('/api/training/progress', [
            'lessonId' => 'les-1-1',
            'moduleId' => 'mod-1',
            'completedObjectives' => ['obj-1-1-1'],
        ]);
        $saveProgress->assertStatus(401);
    }

    public function test_training_progress_score_is_calculated_server_side(): void
    {
        $student = User::where('username', 'student')->first();
        $this->actingAs($student);

        TerminalActivityLog::create([
            'user_id' => $student->username,
            'command' => 'JI*1234AB/GS',
            'output_lines' => ['OK SIGN-IN', 'WORK AREA A ACTIVE'],
            'is_error' => false,
            'command_type' => 'SIGN_IN',
            'execution_time_ms' => 4,
        ]);

        // Lesson 1-1 has 2 objectives. Completing 1 objective should result in 50%, ignoring client score 999.
        $res = $this->postJson('/api/training/progress', [
            'lessonId' => 'les-1-1',
            'moduleId' => 'mod-1',
            'completedObjectives' => ['obj-1-1-1'],
            'score' => 999,
            'isComplete' => false,
        ]);

        $res->assertStatus(200);
        $this->assertEquals(50, $res->json('progress.score'));
        $this->assertSame(['obj-1-1-1'], $res->json('progress.completed_objectives_json'));
    }

    public function test_training_progress_cannot_credit_objectives_without_terminal_evidence(): void
    {
        $student = User::where('username', 'student')->first();
        $this->actingAs($student);

        // Claiming every objective, or inventing objectives, must not mint progress.
        $res = $this->postJson('/api/training/progress', [
            'lessonId' => 'les-1-1',
            'moduleId' => 'mod-1',
            'completedObjectives' => ['obj-1-1-1', 'obj-1-1-2', 'fake-objective'],
            'isComplete' => true,
        ]);

        $res->assertStatus(200);
        $this->assertEquals(0, $res->json('progress.score'));
        $this->assertSame('in_progress', $res->json('progress.status'));
        $this->assertSame([], $res->json('progress.completed_objectives_json'));
    }

    public function test_training_progress_rejects_unknown_lessons(): void
    {
        $student = User::where('username', 'student')->first();
        $this->actingAs($student);

        $res = $this->postJson('/api/training/progress', [
            'lessonId' => 'NOT-A-LESSON',
            'moduleId' => 'NOT-A-REAL-MODULE',
            'completedObjectives' => ['obj-1-1-1'],
            'isComplete' => true,
        ]);

        $res->assertStatus(422);
        $this->assertDatabaseMissing('lesson_progress', ['lesson_id' => 'NOT-A-LESSON']);
    }

    public function test_location_and_airline_lookups_reject_empty_or_wildcard_only_queries(): void
    {
        $resDan = $this->postJson('/api/terminal/execute', [
            'command' => 'DAN %%%',
            'state' => [],
        ]);
        $resDan->assertStatus(200);
        $this->assertTrue($resDan->json('isError'));
        $this->assertStringContainsString('MINIMUM 2 CHARACTERS', implode(' ', $resDan->json('outputLines')));

        $resDna = $this->postJson('/api/terminal/execute', [
            'command' => 'DNA %%%',
            'state' => [],
        ]);
        $resDna->assertStatus(200);
        $this->assertTrue($resDna->json('isError'));
        $this->assertStringContainsString('MINIMUM 2 CHARACTERS', implode(' ', $resDna->json('outputLines')));
    }

    public function test_response_includes_content_security_policy_header(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertHeader('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $response->headers->get('Content-Security-Policy'));
    }

    public function test_unauthenticated_user_cannot_access_active_pnr_api(): void
    {
        $response = $this->getJson('/api/dashboard/active-pnr');
        $response->assertStatus(401);
    }

    public function test_student_cannot_read_another_users_active_pnr_via_query(): void
    {
        $student = User::where('username', 'student')->first();
        $this->actingAs($student);

        // Instructor has an active PNR
        Pnr::create([
            'record_locator' => 'INS999',
            'user_id' => 'instructor',
            'office_id' => 'LON1A0980',
            'status' => 'CONFIRMED',
            'data_json' => [
                'recordLocator' => 'INS999',
                'passengers' => [['lastName' => 'TOPSECRET', 'firstName' => 'VIP']],
            ],
        ]);

        $response = $this->getJson('/api/dashboard/active-pnr?user=instructor');
        $response->assertStatus(200);

        // Student query must ignore the ?user=instructor parameter and look up the student's own PNR
        $this->assertNotEquals('INS999', $response->json('recordLocator'));
    }

    public function test_unauthenticated_user_cannot_submit_scenario(): void
    {
        $response = $this->postJson('/api/dashboard/scenarios/SCN-RT-01/submit', [
            'pnrData' => [],
        ]);

        $response->assertStatus(401);
    }

    public function test_unapproved_student_cannot_execute_terminal_commands(): void
    {
        $unapproved = User::create([
            'username' => 'unapproved_cadet',
            'name' => 'Pending Cadet',
            'email' => 'unapproved@amadeus.training',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'is_approved' => false,
            'office_id' => 'LON1A0980',
            'agent_id' => '1234AB',
        ]);

        $response = $this->actingAs($unapproved)->postJson('/api/terminal/execute', [
            'command' => 'HE',
            'state' => [],
        ]);

        $response->assertStatus(403);
    }

    public function test_guest_session_cannot_persist_pnr_via_et(): void
    {
        $validState = [
            'officeId' => 'LON1A0980',
            'agentId' => '1234AB',
            'activePnr' => [
                'recordLocator' => 'GST001',
                'passengers' => [
                    ['lastName' => 'GUEST', 'firstName' => 'TEST'],
                ],
                'segments' => [
                    [
                        'segmentNumber' => 1,
                        'airlineCode' => 'BA',
                        'flightNumber' => '175',
                        'bookingClass' => 'Y',
                        'departureDate' => '15NOV',
                        'departureAirport' => 'LHR',
                        'arrivalAirport' => 'JFK',
                        'departureTime' => '1000',
                        'arrivalTime' => '1300',
                        'seats' => 1,
                        'status' => 'HK',
                    ],
                ],
                'contacts' => [
                    ['id' => 1, 'type' => 'PHONE', 'detail' => 'LON 123456'],
                ],
                'ticketing' => ['type' => 'TKOK', 'raw' => 'TKOK'],
                'receivedFrom' => 'TEST',
            ],
        ];

        // Execute ET without authentication (guest)
        $res = $this->postJson('/api/terminal/execute', [
            'command' => 'ET',
            'state' => $validState,
        ]);

        $res->assertStatus(200);
        $this->assertTrue($res->json('isError'));
        $output = implode(' ', $res->json('outputLines'));
        $this->assertStringContainsString('GUEST SESSION: LOGIN REQUIRED', $output);
        $this->assertDatabaseMissing('pnrs', ['record_locator' => 'GST001']);
    }

    public function test_exit_impersonation_rejects_get_requests(): void
    {
        $student = User::where('username', 'student')->first();
        $response = $this->actingAs($student)->get('/instructor/exit-impersonation');

        // GET method is no longer allowed
        $response->assertStatus(405);
    }

    public function test_installer_blocks_sqlite_path_traversal(): void
    {
        $service = app(InstallerService::class);
        $sanitized = $service->sanitizeSqlitePath('../../etc/passwd');

        $this->assertEquals(database_path('passwd.sqlite'), $sanitized);
        $this->assertStringNotContainsString('..', $sanitized);
    }

    public function test_installer_blocks_ssrf_to_cloud_metadata(): void
    {
        $service = app(InstallerService::class);
        $result = $service->testDatabase([
            'driver' => 'mysql',
            'host' => '169.254.169.254',
            'port' => 3306,
            'database' => 'test_db',
            'username' => 'root',
            'password' => '',
        ]);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('forbidden', strtolower($result['error']));
    }

    public function test_installer_rejects_malicious_database_identifiers(): void
    {
        $service = app(InstallerService::class);
        $result = $service->testDatabase([
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'test_db; DROP TABLE users;--',
            'username' => 'root',
            'password' => '',
        ]);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('alphanumeric', $result['error']);
    }

    public function test_guest_session_cannot_view_queues_via_qs_or_qd(): void
    {
        $qsRes = $this->postJson('/api/terminal/execute', [
            'command' => 'QS',
            'state' => ['officeId' => 'LON1A0980'],
        ]);
        $qsRes->assertStatus(200);
        $this->assertTrue($qsRes->json('isError'));
        $this->assertStringContainsString('GUEST SESSION: LOGIN REQUIRED TO ACCESS QUEUES', implode(' ', $qsRes->json('outputLines')));

        $qdRes = $this->postJson('/api/terminal/execute', [
            'command' => 'QD 1',
            'state' => ['officeId' => 'LON1A0980'],
        ]);
        $qdRes->assertStatus(200);
        $this->assertTrue($qdRes->json('isError'));
        $this->assertStringContainsString('GUEST SESSION: LOGIN REQUIRED TO ACCESS QUEUES', implode(' ', $qdRes->json('outputLines')));
    }

    public function test_instructor_cannot_access_or_mutate_cross_office_students(): void
    {
        $instA = User::create([
            'username' => 'inst_a',
            'name' => 'Instructor A',
            'email' => 'insta@test.local',
            'password' => bcrypt('password'),
            'role' => 'instructor',
            'is_approved' => true,
            'office_id' => 'ACADA001',
            'agent_id' => 'INA1',
        ]);

        $cadetA = User::create([
            'username' => 'cadet_a',
            'name' => 'Cadet A',
            'email' => 'cadeta@test.local',
            'password' => bcrypt('password'),
            'role' => 'student',
            'is_approved' => true,
            'office_id' => 'ACADA001',
            'agent_id' => 'CDA1',
        ]);

        $cadetB = User::create([
            'username' => 'cadet_b',
            'name' => 'Cadet B',
            'email' => 'cadetb@test.local',
            'password' => bcrypt('password'),
            'role' => 'student',
            'is_approved' => false,
            'office_id' => 'ACADB001',
            'agent_id' => 'CDB1',
        ]);

        Pnr::create([
            'record_locator' => 'CADB01',
            'user_id' => $cadetB->username,
            'office_id' => 'ACADB001',
            'status' => 'CONFIRMED',
            'data_json' => ['recordLocator' => 'CADB01'],
        ]);

        $submission = Submission::create([
            'scenario_id' => 'SCN-RT-01',
            'user_id' => $cadetB->username,
            'score' => 50,
            'status' => 'submitted',
        ]);

        $this->actingAs($instA);

        // 1. Roster scoping
        $roster = $this->getJson('/api/instructor/students');
        $roster->assertStatus(200);
        $studentUsernames = collect($roster->json('students'))->pluck('username');
        $pendingUsernames = collect($roster->json('pendingStudents'))->pluck('username');
        $this->assertTrue($studentUsernames->contains('cadet_a'));
        $this->assertFalse($studentUsernames->contains('cadet_b'));
        $this->assertFalse($pendingUsernames->contains('cadet_b'));

        // 2. Active PNR scoping
        $activePnr = $this->getJson('/api/dashboard/active-pnr?user=cadet_b');
        $activePnr->assertStatus(404);

        // 3. Spectate scoping
        $spectate = $this->getJson('/api/instructor/spectate/cadet_b');
        $spectate->assertStatus(404);

        // 4. Approve scoping
        $approve = $this->postJson("/api/instructor/students/{$cadetB->id}/approve");
        $approve->assertStatus(404);

        // 5. Reject scoping
        $reject = $this->postJson("/api/instructor/students/{$cadetB->id}/reject");
        $reject->assertStatus(404);

        // 6. Delete scoping
        $delete = $this->deleteJson("/api/instructor/students/{$cadetB->id}");
        $delete->assertStatus(404);

        // 7. Impersonate scoping
        $impersonate = $this->postJson("/api/instructor/students/{$cadetB->id}/impersonate");
        $impersonate->assertStatus(404);

        // 8. Grade scoping
        $grade = $this->postJson("/api/instructor/submissions/{$submission->id}/grade", [
            'score' => 95,
        ]);
        $grade->assertStatus(404);
    }

    public function test_sandbox_reset_is_scoped_to_instructors_office(): void
    {
        $instC = User::create([
            'username' => 'inst_c',
            'name' => 'Instructor C',
            'email' => 'instc@test.local',
            'password' => bcrypt('password'),
            'role' => 'instructor',
            'is_approved' => true,
            'office_id' => 'ACADC001',
            'agent_id' => 'INC1',
        ]);

        $cadetC = User::create([
            'username' => 'cadet_c',
            'name' => 'Cadet C',
            'email' => 'cadetc@test.local',
            'password' => bcrypt('password'),
            'role' => 'student',
            'is_approved' => true,
            'office_id' => 'ACADC001',
            'agent_id' => 'CDC1',
        ]);

        $cadetD = User::create([
            'username' => 'cadet_d',
            'name' => 'Cadet D',
            'email' => 'cadetd@test.local',
            'password' => bcrypt('password'),
            'role' => 'student',
            'is_approved' => true,
            'office_id' => 'ACADD001',
            'agent_id' => 'CDD1',
        ]);

        TerminalActivityLog::create([
            'user_id' => 'cadet_c',
            'command' => 'AN15NOVLHRJFK',
            'output_lines' => ['OK'],
            'is_error' => false,
            'command_type' => 'AVAILABILITY',
            'execution_time_ms' => 10,
        ]);

        TerminalActivityLog::create([
            'user_id' => 'cadet_d',
            'command' => 'DAN LHR',
            'output_lines' => ['OK'],
            'is_error' => false,
            'command_type' => 'DECODE',
            'execution_time_ms' => 10,
        ]);

        $this->actingAs($instC);

        $response = $this->postJson('/api/instructor/sandbox/reset');
        $response->assertStatus(200);

        $this->assertDatabaseMissing('terminal_activity_logs', [
            'user_id' => 'cadet_c',
        ]);
        $this->assertDatabaseHas('terminal_activity_logs', [
            'user_id' => 'cadet_d',
        ]);
    }

    public function test_terminal_execute_normalises_missing_active_pnr_keys_without_500(): void
    {
        $response = $this->postJson('/api/terminal/execute', [
            'command' => 'NM1DOE/JOHN',
            'state' => [
                'activePnr' => [
                    'recordLocator' => 'ABC123',
                ],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertFalse($response->json('isError'));
    }

    public function test_registration_normalises_case_and_rejects_duplicate_with_422(): void
    {
        User::factory()->create([
            'username' => 'existingcadet',
            'email' => 'existing@example.test',
        ]);

        $response = $this->postJson('/api/auth/register', [
            'username' => 'ExistingCadet',
            'email' => 'different@example.test',
            'password' => 'Passw0rd!23',
            'password_confirmation' => 'Passw0rd!23',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['username']);
    }

    public function test_guest_session_cannot_retrieve_stored_pnr_via_rt_locator(): void
    {
        Pnr::create([
            'record_locator' => 'GSTP01',
            'user_id' => 'student',
            'office_id' => 'LON1A0980',
            'status' => 'CONFIRMED',
            'data_json' => [
                'recordLocator' => 'GSTP01',
                'passengers' => [
                    ['lastName' => 'SECRETPAX', 'firstName' => 'ALICE'],
                ],
                'segments' => [],
            ],
        ]);

        $res = $this->postJson('/api/terminal/execute', [
            'command' => 'RT GSTP01',
            'state' => [],
        ]);

        $res->assertStatus(200);
        $this->assertTrue($res->json('isError'));
        $this->assertEquals('RT_UNAUTHENTICATED', $res->json('commandType'));
        $this->assertStringContainsString('SIGN IN REQUIRED TO RETRIEVE A STORED PNR', implode(' ', $res->json('outputLines')));
        $this->assertStringNotContainsString('SECRETPAX', implode(' ', $res->json('outputLines')));
    }

    public function test_guest_session_cannot_retrieve_stored_pnr_via_rt_surname(): void
    {
        Pnr::create([
            'record_locator' => 'GSTP02',
            'user_id' => 'student',
            'office_id' => 'LON1A0980',
            'status' => 'CONFIRMED',
            'data_json' => [
                'recordLocator' => 'GSTP02',
                'passengers' => [
                    ['lastName' => 'DOE', 'firstName' => 'JANE'],
                ],
                'segments' => [],
            ],
        ]);

        $res = $this->postJson('/api/terminal/execute', [
            'command' => 'RT/DOE',
            'state' => [],
        ]);

        $res->assertStatus(200);
        $this->assertNotEquals('GSTP02', $res->json('updatedState.activePnr.recordLocator'));
        $this->assertStringNotContainsString('GSTP02', implode(' ', $res->json('outputLines')));
    }

    public function test_guest_session_cannot_retrieve_stored_pnrs_via_single_letter_index(): void
    {
        Pnr::create([
            'record_locator' => 'GSTP03',
            'user_id' => 'student',
            'office_id' => 'LON1A0980',
            'status' => 'CONFIRMED',
            'data_json' => [
                'recordLocator' => 'GSTP03',
                'passengers' => [
                    ['lastName' => 'ZIMMERMAN', 'firstName' => 'HANS'],
                ],
                'segments' => [],
            ],
        ]);

        $res = $this->postJson('/api/terminal/execute', [
            'command' => 'RT/Z',
            'state' => [],
        ]);

        $res->assertStatus(200);
        $this->assertStringNotContainsString('GSTP03', implode(' ', $res->json('outputLines')));
        $this->assertStringNotContainsString('ZIMMERMAN', implode(' ', $res->json('outputLines')));
    }

    public function test_cadet_cannot_view_other_cadet_pnrs_via_single_letter_index(): void
    {
        $cadet1 = User::create([
            'username' => 'cadet_one',
            'name' => 'Cadet One',
            'email' => 'cadet1@test.local',
            'password' => bcrypt('password'),
            'role' => 'student',
            'is_approved' => true,
            'office_id' => 'LON1A0980',
            'agent_id' => 'CD01',
        ]);

        $cadet2 = User::create([
            'username' => 'cadet_two',
            'name' => 'Cadet Two',
            'email' => 'cadet2@test.local',
            'password' => bcrypt('password'),
            'role' => 'student',
            'is_approved' => true,
            'office_id' => 'LON1A0980',
            'agent_id' => 'CD02',
        ]);

        Pnr::create([
            'record_locator' => 'CDT101',
            'user_id' => 'cadet_one',
            'office_id' => 'LON1A0980',
            'status' => 'CONFIRMED',
            'data_json' => [
                'recordLocator' => 'CDT101',
                'passengers' => [
                    ['lastName' => 'VAUGHN', 'firstName' => 'SARAH'],
                ],
                'segments' => [],
            ],
        ]);

        $res = $this->actingAs($cadet2)->postJson('/api/terminal/execute', [
            'command' => 'RT/V',
            'state' => [],
        ]);

        $res->assertStatus(200);
        $output = implode(' ', $res->json('outputLines'));
        $this->assertStringNotContainsString('CDT101', $output);
        $this->assertStringNotContainsString('VAUGHN', $output);
    }

    public function test_cadet_cannot_retrieve_other_cadet_pnr_via_rt_line_with_tampered_state(): void
    {
        $cadet1 = User::create([
            'username' => 'owner_cadet',
            'name' => 'Owner Cadet',
            'email' => 'owner@test.local',
            'password' => bcrypt('password'),
            'role' => 'student',
            'is_approved' => true,
            'office_id' => 'LON1A0980',
            'agent_id' => 'CD01',
        ]);

        $cadet2 = User::create([
            'username' => 'snoop_cadet',
            'name' => 'Snoop Cadet',
            'email' => 'snoop@test.local',
            'password' => bcrypt('password'),
            'role' => 'student',
            'is_approved' => true,
            'office_id' => 'LON1A0980',
            'agent_id' => 'CD02',
        ]);

        Pnr::create([
            'record_locator' => 'OWN001',
            'user_id' => 'owner_cadet',
            'office_id' => 'LON1A0980',
            'status' => 'CONFIRMED',
            'data_json' => [
                'recordLocator' => 'OWN001',
                'passengers' => [
                    ['lastName' => 'PRIVATE', 'firstName' => 'RECORD'],
                ],
                'segments' => [],
            ],
        ]);

        $tamperedState = [
            'lastPnrList' => [
                [
                    'recordLocator' => 'OWN001',
                    'passengers' => [
                        ['lastName' => 'PRIVATE', 'firstName' => 'RECORD'],
                    ],
                ],
            ],
        ];

        $res = $this->actingAs($cadet2)->postJson('/api/terminal/execute', [
            'command' => 'RT1',
            'state' => $tamperedState,
        ]);

        $res->assertStatus(200);
        $this->assertTrue($res->json('isError'));
        $this->assertEquals('RT_UNAUTHORIZED', $res->json('commandType'));
        $this->assertStringContainsString('ACCESS RESTRICTED: PNR BELONGS TO ANOTHER USER', implode(' ', $res->json('outputLines')));
    }

    public function test_guest_session_cannot_place_on_queue_via_qe(): void
    {
        $res1 = $this->postJson('/api/terminal/execute', [
            'command' => 'QE/LON1A0980/1C0',
            'state' => [],
        ]);
        $res1->assertStatus(200);
        $this->assertTrue($res1->json('isError'));
        $this->assertEquals('QUEUE_UNAUTHENTICATED', $res1->json('commandType'));

        $res2 = $this->postJson('/api/terminal/execute', [
            'command' => 'QE1C0',
            'state' => [],
        ]);
        $res2->assertStatus(200);
        $this->assertTrue($res2->json('isError'));
        $this->assertEquals('QUEUE_UNAUTHENTICATED', $res2->json('commandType'));

        $res3 = $this->postJson('/api/terminal/execute', [
            'command' => 'QE1',
            'state' => [],
        ]);
        $res3->assertStatus(200);
        $this->assertTrue($res3->json('isError'));
        $this->assertEquals('QUEUE_UNAUTHENTICATED', $res3->json('commandType'));

        $this->assertEquals(0, QueueItem::where('message', 'like', '%PLACED VIA QE%')->count());
    }

    public function test_instructor_cannot_delete_another_instructors_custom_scenario(): void
    {
        $instA = User::create([
            'username' => 'instructor_alpha',
            'name' => 'Instructor Alpha',
            'email' => 'alpha@instructor.test',
            'password' => bcrypt('password'),
            'role' => 'instructor',
            'is_approved' => true,
            'office_id' => 'LON1A0980',
            'agent_id' => 'IA01',
        ]);

        $instB = User::create([
            'username' => 'instructor_beta',
            'name' => 'Instructor Beta',
            'email' => 'beta@instructor.test',
            'password' => bcrypt('password'),
            'role' => 'instructor',
            'is_approved' => true,
            'office_id' => 'XOFF22',
            'agent_id' => 'IB01',
        ]);

        Scenario::create([
            'id' => 'SCN-ALPHA-DRILL',
            'title' => 'Alpha Private Drill',
            'category' => 'One-way',
            'difficulty' => 'Intermediate',
            'description' => 'Drill created by Alpha',
            'time_limit_minutes' => 20,
            'passenger_profile' => ['names' => []],
            'flight_segments' => [],
            'validation_criteria' => [],
            'created_by' => 'instructor_alpha',
        ]);

        Submission::create([
            'scenario_id' => 'SCN-ALPHA-DRILL',
            'user_id' => 'student',
            'status' => 'submitted',
            'score' => 95,
        ]);

        // Instructor Beta tries to delete Instructor Alpha's scenario -> 404
        $deleteRes = $this->actingAs($instB)->deleteJson('/api/instructor/scenarios/SCN-ALPHA-DRILL');
        $deleteRes->assertStatus(404);

        // Confirm scenario and submissions are preserved
        $this->assertDatabaseHas('scenarios', ['id' => 'SCN-ALPHA-DRILL']);
        $this->assertDatabaseHas('submissions', ['scenario_id' => 'SCN-ALPHA-DRILL']);

        // Instructor Alpha can delete their own scenario -> 200
        $deleteResAlpha = $this->actingAs($instA)->deleteJson('/api/instructor/scenarios/SCN-ALPHA-DRILL');
        $deleteResAlpha->assertStatus(200);
        $this->assertDatabaseMissing('scenarios', ['id' => 'SCN-ALPHA-DRILL']);
    }

    public function test_installer_rejects_ssrf_bypass_variations(): void
    {
        $service = app(InstallerService::class);

        // Hex representation of link-local 169.254.169.254
        $hexResult = $service->testDatabase([
            'driver' => 'mysql',
            'host' => '0xa9fea9fe',
            'port' => 3306,
            'database' => 'test_db',
            'username' => 'root',
            'password' => '',
        ]);
        $this->assertFalse($hexResult['success']);

        // Trailing-dot hostname representation
        $dotResult = $service->testDatabase([
            'driver' => 'mysql',
            'host' => '169.254.169.254.',
            'port' => 3306,
            'database' => 'test_db',
            'username' => 'root',
            'password' => '',
        ]);
        $this->assertFalse($dotResult['success']);

        // Unresolvable hostname
        $unresResult = $service->testDatabase([
            'driver' => 'mysql',
            'host' => 'nonexistent-host-'.uniqid().'.test.invalid',
            'port' => 3306,
            'database' => 'test_db',
            'username' => 'root',
            'password' => '',
        ]);
        $this->assertFalse($unresResult['success']);
    }

    public function test_authenticated_cadet_cannot_override_office_via_state_in_queue_commands(): void
    {
        $cadet = User::create([
            'username' => 'cadet_office_test',
            'name' => 'Cadet Office Test',
            'email' => 'cadet_office@test.local',
            'password' => bcrypt('password'),
            'role' => 'student',
            'is_approved' => true,
            'office_id' => 'LON1A0980',
            'agent_id' => 'CO01',
        ]);

        QueueItem::create([
            'office_id' => 'NYC1A0111',
            'queue_number' => 1,
            'category' => 0,
            'pnr_locator' => 'NYCQ01',
            'pnr_summary' => ['locator' => 'NYCQ01', 'passenger' => 'SECRET/NYC'],
            'message' => 'SECRET NYC QUEUE ADVISORY',
            'status' => 'PENDING',
        ]);

        $resQd = $this->actingAs($cadet)->postJson('/api/terminal/execute', [
            'command' => 'QD1',
            'state' => ['officeId' => 'NYC1A0111'],
        ]);

        $resQd->assertStatus(200);
        $qdOutput = implode(' ', $resQd->json('outputLines'));
        $this->assertStringNotContainsString('NYCQ01', $qdOutput);
        $this->assertStringNotContainsString('SECRET NYC QUEUE ADVISORY', $qdOutput);
        $this->assertEquals('LON1A0980', $resQd->json('updatedState.officeId'));

        $resQs = $this->actingAs($cadet)->postJson('/api/terminal/execute', [
            'command' => 'QS',
            'state' => ['officeId' => 'NYC1A0111'],
        ]);

        $resQs->assertStatus(200);
        $qsOutput = implode(' ', $resQs->json('outputLines'));
        $this->assertStringContainsString('LON1A0980', $qsOutput);
        $this->assertStringNotContainsString('NYC1A0111', $qsOutput);
        $this->assertEquals('LON1A0980', $resQs->json('updatedState.officeId'));
    }

    public function test_authenticated_cadet_cannot_commit_pnr_into_foreign_office_via_state(): void
    {
        $cadet = User::create([
            'username' => 'cadet_pnr_test',
            'name' => 'Cadet PNR Test',
            'email' => 'cadet_pnr@test.local',
            'password' => bcrypt('password'),
            'role' => 'student',
            'is_approved' => true,
            'office_id' => 'LON1A0980',
            'agent_id' => 'CP01',
        ]);

        $tamperedState = [
            'officeId' => 'NYC1A0111',
            'agentId' => 'CP01',
            'activePnr' => [
                'recordLocator' => null,
                'passengers' => [
                    ['lastName' => 'TAYLOR', 'firstName' => 'JAMES', 'title' => 'MR', 'type' => 'ADT'],
                ],
                'segments' => [
                    [
                        'segmentNumber' => 1,
                        'airlineCode' => 'BA',
                        'flightNumber' => '175',
                        'bookingClass' => 'Y',
                        'departureDate' => '20DEC',
                        'departureAirport' => 'LHR',
                        'arrivalAirport' => 'JFK',
                        'departureTime' => '1000',
                        'arrivalTime' => '1300',
                        'seats' => 1,
                        'status' => 'HK',
                    ],
                ],
                'contacts' => [
                    ['id' => 1, 'type' => 'PHONE', 'detail' => 'LON 020-1234-5678'],
                ],
                'ticketing' => ['type' => 'TKOK', 'raw' => 'TKOK'],
                'receivedFrom' => 'JAMES',
            ],
        ];

        $res = $this->actingAs($cadet)->postJson('/api/terminal/execute', [
            'command' => 'ET',
            'state' => $tamperedState,
        ]);

        $res->assertStatus(200);
        $this->assertFalse($res->json('isError'));

        $rloc = $res->json('updatedState.activePnr.recordLocator');
        $this->assertNotEmpty($rloc);

        $dbPnr = Pnr::where('record_locator', $rloc)->first();
        $this->assertNotNull($dbPnr);
        $this->assertEquals('LON1A0980', $dbPnr->office_id);
        $this->assertNotEquals('NYC1A0111', $dbPnr->office_id);
    }

    public function test_instructor_cannot_retrieve_foreign_office_pnr_via_rt_locator(): void
    {
        $instructor = User::where('role', 'instructor')->first();
        $this->assertNotNull($instructor);
        $instructor->update(['office_id' => 'LON1A0980']);

        Pnr::create([
            'record_locator' => 'NYCP01',
            'user_id' => 'student',
            'office_id' => 'NYC1A0111',
            'status' => 'CONFIRMED',
            'data_json' => [
                'recordLocator' => 'NYCP01',
                'creationOffice' => 'NYC1A0111',
                'passengers' => [
                    ['lastName' => 'FOREIGNER', 'firstName' => 'BOB', 'title' => 'MR', 'type' => 'ADT'],
                ],
                'segments' => [],
            ],
        ]);

        $res = $this->actingAs($instructor)->postJson('/api/terminal/execute', [
            'command' => 'RT NYCP01',
            'state' => [],
        ]);

        $res->assertStatus(200);
        $this->assertTrue($res->json('isError'));
        $this->assertEquals('RT_UNAUTHORIZED', $res->json('commandType'));
        $this->assertStringContainsString('ACCESS RESTRICTED: PNR BELONGS TO ANOTHER OFFICE', implode(' ', $res->json('outputLines')));
    }

    public function test_instructor_cannot_retrieve_foreign_office_pnr_via_rt_line(): void
    {
        $instructor = User::where('role', 'instructor')->first();
        $this->assertNotNull($instructor);
        $instructor->update(['office_id' => 'LON1A0980']);

        Pnr::create([
            'record_locator' => 'NYCP02',
            'user_id' => 'student',
            'office_id' => 'NYC1A0111',
            'status' => 'CONFIRMED',
            'data_json' => [
                'recordLocator' => 'NYCP02',
                'creationOffice' => 'NYC1A0111',
                'passengers' => [
                    ['lastName' => 'FOREIGNER', 'firstName' => 'ALICE', 'title' => 'MS', 'type' => 'ADT'],
                ],
                'segments' => [],
            ],
        ]);

        $state = [
            'lastPnrList' => [
                [
                    'recordLocator' => 'NYCP02',
                    'creationOffice' => 'NYC1A0111',
                    'passengers' => [
                        ['lastName' => 'FOREIGNER', 'firstName' => 'ALICE', 'title' => 'MS', 'type' => 'ADT'],
                    ],
                    'segments' => [],
                ],
            ],
        ];

        $res = $this->actingAs($instructor)->postJson('/api/terminal/execute', [
            'command' => 'RT1',
            'state' => $state,
        ]);

        $res->assertStatus(200);
        $this->assertTrue($res->json('isError'));
        $this->assertEquals('RT_UNAUTHORIZED', $res->json('commandType'));
        $this->assertStringContainsString('ACCESS RESTRICTED: PNR BELONGS TO ANOTHER OFFICE', implode(' ', $res->json('outputLines')));
    }

    public function test_instructor_index_search_is_scoped_to_instructors_office(): void
    {
        $instructor = User::where('role', 'instructor')->first();
        $this->assertNotNull($instructor);
        $instructor->update(['office_id' => 'LON1A0980']);

        Pnr::create([
            'record_locator' => 'LONGR1',
            'user_id' => 'student',
            'office_id' => 'LON1A0980',
            'status' => 'CONFIRMED',
            'data_json' => [
                'recordLocator' => 'LONGR1',
                'creationOffice' => 'LON1A0980',
                'passengers' => [
                    ['lastName' => 'GARCIA', 'firstName' => 'MARIA', 'title' => 'MS', 'type' => 'ADT'],
                ],
                'segments' => [
                    [
                        'segmentNumber' => 1,
                        'airlineCode' => 'BA',
                        'flightNumber' => '100',
                        'bookingClass' => 'Y',
                        'departureDate' => '15DEC',
                        'departureAirport' => 'LHR',
                        'arrivalAirport' => 'JFK',
                        'departureTime' => '1000',
                        'arrivalTime' => '1300',
                        'seats' => 1,
                        'status' => 'HK',
                    ],
                ],
            ],
        ]);

        Pnr::create([
            'record_locator' => 'NYCGR2',
            'user_id' => 'student',
            'office_id' => 'NYC1A0111',
            'status' => 'CONFIRMED',
            'data_json' => [
                'recordLocator' => 'NYCGR2',
                'creationOffice' => 'NYC1A0111',
                'passengers' => [
                    ['lastName' => 'GARCIA', 'firstName' => 'CARLOS', 'title' => 'MR', 'type' => 'ADT'],
                ],
                'segments' => [
                    [
                        'segmentNumber' => 1,
                        'airlineCode' => 'AA',
                        'flightNumber' => '200',
                        'bookingClass' => 'Y',
                        'departureDate' => '15DEC',
                        'departureAirport' => 'JFK',
                        'arrivalAirport' => 'MIA',
                        'departureTime' => '1400',
                        'arrivalTime' => '1700',
                        'seats' => 1,
                        'status' => 'HK',
                    ],
                ],
            ],
        ]);

        // Search single letter index RT/G
        $resIndex = $this->actingAs($instructor)->postJson('/api/terminal/execute', [
            'command' => 'RT/G',
            'state' => [],
        ]);

        $resIndex->assertStatus(200);
        $indexOutput = implode(' ', $resIndex->json('outputLines'));
        $this->assertStringContainsString('LONGR1', $indexOutput);
        $this->assertStringNotContainsString('NYCGR2', $indexOutput);

        // Search by surname RT/GARCIA
        $resSurname = $this->actingAs($instructor)->postJson('/api/terminal/execute', [
            'command' => 'RT/GARCIA',
            'state' => [],
        ]);

        $resSurname->assertStatus(200);
        $surnameOutput = implode(' ', $resSurname->json('outputLines'));
        $this->assertStringContainsString('LONGR1', $surnameOutput);
        $this->assertStringNotContainsString('NYCGR2', $surnameOutput);
    }

    public function test_instructor_cannot_ticket_foreign_office_pnr_via_ttp(): void
    {
        $instructor = User::where('role', 'instructor')->first();
        $this->assertNotNull($instructor);
        $instructor->update(['office_id' => 'LON1A0980']);

        Pnr::create([
            'record_locator' => 'NYCT01',
            'user_id' => 'student',
            'office_id' => 'NYC1A0111',
            'status' => 'CONFIRMED',
            'data_json' => [
                'recordLocator' => 'NYCT01',
                'creationOffice' => 'NYC1A0111',
                'passengers' => [
                    ['lastName' => 'WILLIAMS', 'firstName' => 'SARAH', 'title' => 'MS', 'type' => 'ADT'],
                ],
                'segments' => [
                    [
                        'segmentNumber' => 1,
                        'airlineCode' => 'BA',
                        'flightNumber' => '175',
                        'bookingClass' => 'Y',
                        'departureDate' => '25DEC',
                        'departureAirport' => 'LHR',
                        'arrivalAirport' => 'JFK',
                        'departureTime' => '1000',
                        'arrivalTime' => '1300',
                        'seats' => 1,
                        'status' => 'HK',
                    ],
                ],
                'contacts' => [
                    ['id' => 1, 'type' => 'PHONE', 'detail' => 'LON 020-1234-5678'],
                ],
                'ticketing' => ['type' => 'TKOK', 'raw' => 'TKOK'],
            ],
        ]);

        $state = [
            'activePnr' => [
                'recordLocator' => 'NYCT01',
                'passengers' => [
                    ['lastName' => 'WILLIAMS', 'firstName' => 'SARAH', 'title' => 'MS', 'type' => 'ADT'],
                ],
                'segments' => [
                    [
                        'segmentNumber' => 1,
                        'airlineCode' => 'BA',
                        'flightNumber' => '175',
                        'bookingClass' => 'Y',
                        'departureDate' => '25DEC',
                        'departureAirport' => 'LHR',
                        'arrivalAirport' => 'JFK',
                        'departureTime' => '1000',
                        'arrivalTime' => '1300',
                        'seats' => 1,
                        'status' => 'HK',
                    ],
                ],
                'contacts' => [
                    ['id' => 1, 'type' => 'PHONE', 'detail' => 'LON 020-1234-5678'],
                ],
                'ticketing' => ['type' => 'TKOK', 'raw' => 'TKOK'],
            ],
        ];

        $res = $this->actingAs($instructor)->postJson('/api/terminal/execute', [
            'command' => 'TTP',
            'state' => $state,
        ]);

        $res->assertStatus(200);
        $this->assertTrue($res->json('isError'));
        $this->assertEquals('TTP_UNAUTHORIZED', $res->json('commandType'));
        $this->assertStringContainsString('ACCESS RESTRICTED: PNR BELONGS TO ANOTHER OFFICE', implode(' ', $res->json('outputLines')));

        $dbPnr = Pnr::find('NYCT01');
        $this->assertEquals('CONFIRMED', $dbPnr->status);
    }

    public function test_instructor_cannot_overwrite_foreign_office_pnr_via_et(): void
    {
        $instructor = User::where('role', 'instructor')->first();
        $this->assertNotNull($instructor);
        $instructor->update(['office_id' => 'LON1A0980']);

        Pnr::create([
            'record_locator' => 'NYCO01',
            'user_id' => 'student',
            'office_id' => 'NYC1A0111',
            'status' => 'CONFIRMED',
            'data_json' => [
                'recordLocator' => 'NYCO01',
                'creationOffice' => 'NYC1A0111',
                'passengers' => [
                    ['lastName' => 'DOE', 'firstName' => 'JOHN', 'title' => 'MR', 'type' => 'ADT'],
                ],
                'segments' => [],
            ],
        ]);

        $tamperedState = [
            'activePnr' => [
                'recordLocator' => 'NYCO01',
                'passengers' => [
                    ['lastName' => 'OVERWRITE', 'firstName' => 'ATTEMPT', 'title' => 'MR', 'type' => 'ADT'],
                ],
                'segments' => [
                    [
                        'segmentNumber' => 1,
                        'airlineCode' => 'BA',
                        'flightNumber' => '175',
                        'bookingClass' => 'Y',
                        'departureDate' => '15NOV',
                        'departureAirport' => 'LHR',
                        'arrivalAirport' => 'JFK',
                        'departureTime' => '1000',
                        'arrivalTime' => '1300',
                        'seats' => 1,
                        'status' => 'HK',
                    ],
                ],
                'contacts' => [
                    ['id' => 1, 'type' => 'PHONE', 'detail' => 'LON 123456'],
                ],
                'ticketing' => ['type' => 'TKOK', 'raw' => 'TKOK'],
                'receivedFrom' => 'ATTEMPT',
            ],
        ];

        $res = $this->actingAs($instructor)->postJson('/api/terminal/execute', [
            'command' => 'ET',
            'state' => $tamperedState,
        ]);

        $res->assertStatus(200);
        $this->assertTrue($res->json('isError'));
        $this->assertEquals('ET_UNAUTHORIZED', $res->json('commandType'));
        $this->assertStringContainsString('ACCESS RESTRICTED: PNR BELONGS TO ANOTHER OFFICE', implode(' ', $res->json('outputLines')));

        $dbPnr = Pnr::find('NYCO01');
        $this->assertEquals('student', $dbPnr->user_id);
        $this->assertEquals('NYC1A0111', $dbPnr->office_id);
        $this->assertEquals('DOE', $dbPnr->data_json['passengers'][0]['lastName']);
    }

    public function test_instructor_cannot_redisplay_foreign_office_pnr_via_ir(): void
    {
        $instructor = User::where('role', 'instructor')->first();
        $this->assertNotNull($instructor);
        $instructor->update(['office_id' => 'LON1A0980']);

        Pnr::create([
            'record_locator' => 'NYCI01',
            'user_id' => 'student',
            'office_id' => 'NYC1A0111',
            'status' => 'CONFIRMED',
            'data_json' => [
                'recordLocator' => 'NYCI01',
                'creationOffice' => 'NYC1A0111',
                'passengers' => [
                    ['lastName' => 'SECRET', 'firstName' => 'PERSON', 'title' => 'MR', 'type' => 'ADT'],
                ],
                'segments' => [],
            ],
        ]);

        $state = [
            'activePnr' => [
                'recordLocator' => 'NYCI01',
                'passengers' => [
                    ['lastName' => 'TAMPERED', 'firstName' => 'UNSAVED'],
                ],
            ],
        ];

        $res = $this->actingAs($instructor)->postJson('/api/terminal/execute', [
            'command' => 'IR',
            'state' => $state,
        ]);

        $res->assertStatus(200);
        $this->assertEquals(['IGNORED'], $res->json('outputLines'));
        $this->assertEmpty($res->json('updatedState.activePnr.recordLocator'));
        $this->assertEquals('LON1A0980', $res->json('updatedState.officeId'));
    }
}
