<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AmadeusGdsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->seedTestUsers();
    }

    public function test_01_home_page_returns_http_200(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Amadus');
    }

    public function test_02_student_authentication_succeeds(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'username' => 'student',
            'password' => 'amadeus123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'user' => [
                'username' => 'student',
                'role' => 'student',
            ],
        ]);
    }

    public function test_03_iata_decoding_commands(): void
    {
        $state = [
            'area' => 'A',
            'officeId' => 'LON1A0980',
            'agentId' => '1234AB',
            'activePnr' => null,
            'lastAvailabilityDisplay' => null,
            'history' => [],
            'outputLines' => [],
        ];

        // DAN LONDON
        $res = $this->postJson('/api/terminal/execute', ['command' => 'DAN LONDON', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'LHR') || str_contains($lines, 'HEATHROW'));

        // DNA QR
        $res = $this->postJson('/api/terminal/execute', ['command' => 'DNA QR', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'QATAR AIRWAYS'));

        // DNE 77W
        $res = $this->postJson('/api/terminal/execute', ['command' => 'DNE 77W', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'BOEING 777-300ER'));
    }

    public function test_04_full_pnr_creation_pricing_eticketing_and_instructor_audit(): void
    {
        // 1. Log in as student
        $loginRes = $this->postJson('/api/auth/login', [
            'username' => 'student',
            'password' => 'amadeus123',
        ]);
        $loginRes->assertStatus(200);

        $state = [
            'area' => 'A',
            'officeId' => 'LON1A0980',
            'agentId' => '1234AB',
            'activePnr' => null,
            'lastAvailabilityDisplay' => null,
            'history' => [],
            'outputLines' => [],
        ];

        // 2. Flight availability: AN15NOVLHRJFK
        $res = $this->postJson('/api/terminal/execute', ['command' => 'AN15NOVLHRJFK', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertNotEmpty($res->json('updatedState.lastAvailabilityDisplay.lines'));
        $state = $res->json('updatedState');

        // 3. Short sell: SS1Y1
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SS1Y1', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertCount(1, $res->json('updatedState.activePnr.segments'));
        $state = $res->json('updatedState');

        // 4. Passenger name: NM1ALEXANDER/LEO MR
        $res = $this->postJson('/api/terminal/execute', ['command' => 'NM1ALEXANDER/LEO MR', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertCount(1, $res->json('updatedState.activePnr.passengers'));
        $state = $res->json('updatedState');

        // 5. Contact element: AP LON 44-20-7946-0912-A
        $res = $this->postJson('/api/terminal/execute', ['command' => 'AP LON 44-20-7946-0912-A', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertCount(1, $res->json('updatedState.activePnr.contacts'));
        $state = $res->json('updatedState');

        // 6. Ticketing: TKTL15NOV/LON1A0980
        $res = $this->postJson('/api/terminal/execute', ['command' => 'TKTL15NOV/LON1A0980', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertNotNull($res->json('updatedState.activePnr.ticketing'));
        $state = $res->json('updatedState');

        // 7. Received From: RF PASSENGER
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RF PASSENGER', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('PASSENGER', $res->json('updatedState.activePnr.receivedFrom'));
        $state = $res->json('updatedState');

        // 8. Pricing & TST: FXP
        $res = $this->postJson('/api/terminal/execute', ['command' => 'FXP', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertCount(1, $res->json('updatedState.activePnr.tsts'));
        $state = $res->json('updatedState');

        // 9. Form of payment: FP CASH
        $res = $this->postJson('/api/terminal/execute', ['command' => 'FP CASH', 'state' => $state]);
        $res->assertStatus(200);
        $state = $res->json('updatedState');

        // 10. End Transaction: ET (Allocates Record Locator & Saves to DB)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'ET', 'state' => $state]);
        $res->assertStatus(200);
        $rloc = $res->json('updatedState.activePnr.recordLocator');
        $this->assertNotEmpty($rloc);
        $this->assertEquals(6, strlen($rloc));
        $state = $res->json('updatedState');

        // Verify PNR persisted to DB
        $this->assertDatabaseHas('pnrs', [
            'record_locator' => $rloc,
            'status' => 'CONFIRMED',
        ]);

        // 11. Electronic Ticket Issuance: TTP
        $res = $this->postJson('/api/terminal/execute', ['command' => 'TTP', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertCount(1, $res->json('updatedState.activePnr.tickets'));
        $state = $res->json('updatedState');

        // 12. Display Ticket Record: TWD
        $res = $this->postJson('/api/terminal/execute', ['command' => 'TWD', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'ELECTRONIC TICKET RECORD'));
        $this->assertTrue(str_contains($lines, 'OPEN'));

        // 13. Instructor login & PNR audit
        $instLogin = $this->postJson('/api/auth/login', [
            'username' => 'instructor',
            'password' => 'amadeus123',
        ]);
        $instLogin->assertStatus(200);

        $instData = $this->getJson('/api/instructor/students');
        $instData->assertStatus(200);
        $this->assertNotEmpty($instData->json('students'));

        $matchingPnr = collect($instData->json('pnrs'))->firstWhere('record_locator', $rloc);
        $this->assertNotNull($matchingPnr, "Instructor should find student PNR {$rloc}");
    }
}
