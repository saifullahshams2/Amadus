<?php

namespace Tests\Feature;

use App\Models\Pnr;
use App\Models\Scenario;
use App\Models\User;
use App\Services\Training\ScenarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedTestUsers();
        $this->other = User::firstOrCreate(
            ['username' => 'student2'],
            [
                'name' => 'Second User',
                'email' => 'student2@amadeus-gds.demo',
                'password' => Hash::make('amadeus123'),
                'office_id' => 'LON1A0980',
                'agent_id' => '5678CD',
            ]
        );
    }

    protected function payload(): array
    {
        return [
            'title' => 'My Custom Round Trip',
            'category' => 'Round-trip',
            'difficulty' => 'Beginner',
            'description' => 'Practice a simple return booking.',
            'time_limit_minutes' => 15,
            'requireTst' => true,
            'requireETicket' => false,
            'passenger_names' => [
                ['lastName' => 'doe', 'firstName' => 'jane', 'title' => 'MRS'],
            ],
            'flight_segments' => [
                ['origin' => 'lhr', 'destination' => 'jfk', 'date' => '15NOV', 'airline' => 'ba', 'class' => 'Y'],
            ],
        ];
    }

    public function test_user_can_create_and_see_custom_assignment(): void
    {
        $response = $this->actingAs(User::where('username', 'student')->firstOrFail())
            ->postJson('/api/dashboard/scenarios', $this->payload());

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('scenario.title', 'My Custom Round Trip')
            ->assertJsonPath('scenario.can_delete', true);

        $scenario = Scenario::where('title', 'My Custom Round Trip')->firstOrFail();
        $this->assertSame('student', $scenario->created_by);
        $this->assertSame('LHR', $scenario->flight_segments[0]['origin']);
        $this->assertSame('JFK', $scenario->flight_segments[0]['destination']);
        $this->assertSame('DOE', $scenario->passenger_profile['names'][0]['lastName']);
        $this->assertStringStartsWith('SCN-USER-', $scenario->id);

        $this->actingAs(User::where('username', 'student')->firstOrFail())
            ->get('/dashboard/drills')
            ->assertSeeText('My Custom Round Trip');
    }

    public function test_created_assignment_can_be_submitted_for_grading(): void
    {
        $user = User::where('username', 'student')->firstOrFail();
        $create = $this->actingAs($user)->postJson('/api/dashboard/scenarios', $this->payload());
        $id = $create->json('scenario.id');

        $this->storePnr('student');

        $submit = $this->actingAs($user)->postJson("/api/dashboard/scenarios/{$id}/submit", [
            'recordLocator' => 'ABC123',
        ]);

        $submit->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['submission' => ['score'], 'grade' => ['score', 'checks']]);

        $this->assertDatabaseHas('submissions', ['scenario_id' => $id, 'user_id' => 'student']);
    }

    public function test_unauthenticated_user_cannot_create_assignment(): void
    {
        $this->postJson('/api/dashboard/scenarios', $this->payload())->assertStatus(401);
    }

    public function test_validation_rejects_invalid_assignment(): void
    {
        $this->actingAs(User::where('username', 'student')->firstOrFail())
            ->postJson('/api/dashboard/scenarios', ['title' => '', 'category' => '', 'difficulty' => 'Expert'])
            ->assertStatus(422);
    }

    public function test_owner_can_delete_custom_assignment_and_cascade_submissions(): void
    {
        $user = User::where('username', 'student')->firstOrFail();
        $id = $this->actingAs($user)->postJson('/api/dashboard/scenarios', $this->payload())->json('scenario.id');
        $this->storePnr('student', 'DEL001');
        $this->actingAs($user)->postJson("/api/dashboard/scenarios/{$id}/submit", ['recordLocator' => 'DEL001'])->assertStatus(200);

        $this->actingAs($user)->deleteJson("/api/dashboard/scenarios/{$id}")
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('scenarios', ['id' => $id]);
        $this->assertDatabaseMissing('submissions', ['scenario_id' => $id]);
    }

    public function test_user_cannot_delete_assignment_owned_by_another_user(): void
    {
        $id = $this->actingAs(User::where('username', 'student')->firstOrFail())
            ->postJson('/api/dashboard/scenarios', $this->payload())
            ->json('scenario.id');

        $this->actingAs($this->other)->deleteJson("/api/dashboard/scenarios/{$id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('scenarios', ['id' => $id]);
    }

    public function test_system_scenarios_cannot_be_deleted_by_users(): void
    {
        $this->seed();

        $this->actingAs(User::where('username', 'student')->firstOrFail())
            ->deleteJson('/api/dashboard/scenarios/SCN-RT-01')
            ->assertStatus(404);

        $this->assertDatabaseHas('scenarios', ['id' => 'SCN-RT-01']);
    }

    public function test_can_delete_flag_is_true_only_for_owner(): void
    {
        $user = User::where('username', 'student')->firstOrFail();
        $id = $this->actingAs($user)->postJson('/api/dashboard/scenarios', $this->payload())->json('scenario.id');

        $own = collect($this->scenariosList('student'))->firstWhere('id', $id);
        $foreign = collect($this->scenariosList('student2'))->firstWhere('id', $id);

        $this->assertTrue($own['can_delete']);
        $this->assertFalse($foreign['can_delete']);
    }

    protected function scenariosList(string $username): array
    {
        return app(ScenarioService::class)
            ->getScenariosForUser($username)
            ->values()
            ->all();
    }

    protected function storePnr(string $username, string $locator = 'ABC123'): void
    {
        Pnr::create([
            'record_locator' => $locator,
            'user_id' => $username,
            'office_id' => 'LON1A0980',
            'status' => 'ACTIVE',
            'data_json' => [
                'passengers' => [['id' => '1.1', 'lastName' => 'DOE', 'firstName' => 'JANE', 'title' => 'MRS', 'type' => 'ADT']],
                'segments' => [['segmentNumber' => 1, 'airlineCode' => 'BA', 'flightNumber' => '117', 'bookingClass' => 'Y', 'departureDate' => '15NOV', 'departureAirport' => 'LHR', 'arrivalAirport' => 'JFK', 'seats' => 1, 'departureTime' => '1000', 'arrivalTime' => '1310', 'statusCode' => 'HK1']],
                'contacts' => [['detail' => 'LON 44-20-7123-4567-A']],
                'ticketing' => ['raw' => 'OK'],
                'receivedFrom' => 'PASSENGER',
            ],
        ]);
    }
}
