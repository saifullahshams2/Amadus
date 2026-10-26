<?php

namespace Tests\Feature;

use App\Models\Pnr;
use App\Models\User;
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
        $this->assertContains('api/*', $excluded);

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
        $response = $this->get('/');

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
}
