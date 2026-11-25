<?php

namespace Tests\Feature;

use App\Models\Pnr;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivePnrDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedTestUsers();
        $this->storePnr('student', 'ABC123');
        $this->storePnr('student', 'XYZ789');
    }

    protected function storePnr(string $username, string $locator): void
    {
        Pnr::create([
            'record_locator' => $locator,
            'user_id' => $username,
            'office_id' => 'LON1A0980',
            'status' => 'ACTIVE',
            'data_json' => [
                'passengers' => [['id' => '1.1', 'lastName' => 'DOE', 'firstName' => 'JANE', 'title' => 'MRS', 'type' => 'ADT']],
                'segments' => [],
                'contacts' => [],
            ],
        ]);
    }

    public function test_user_can_delete_their_active_pnr(): void
    {
        $this->actingAs(User::where('username', 'student')->firstOrFail())
            ->deleteJson('/api/dashboard/active-pnr', ['recordLocator' => 'ABC123'])
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('deletedRecordLocator', 'ABC123');

        $this->assertDatabaseMissing('pnrs', ['record_locator' => 'ABC123']);
        $this->assertDatabaseHas('pnrs', ['record_locator' => 'XYZ789']);
    }

    public function test_delete_defaults_to_most_recent_pnr_when_no_locator_given(): void
    {
        Pnr::where('record_locator', 'ABC123')->update(['updated_at' => now()->subDay()]);

        $this->actingAs(User::where('username', 'student')->firstOrFail())
            ->deleteJson('/api/dashboard/active-pnr')
            ->assertStatus(200)
            ->assertJsonPath('deletedRecordLocator', 'XYZ789');

        $this->assertDatabaseHas('pnrs', ['record_locator' => 'ABC123']);
        $this->assertDatabaseMissing('pnrs', ['record_locator' => 'XYZ789']);
    }

    public function test_user_cannot_delete_another_users_pnr(): void
    {
        $this->storePnr('someotheruser', 'OTHER1');

        $this->actingAs(User::where('username', 'student')->firstOrFail())
            ->deleteJson('/api/dashboard/active-pnr', ['recordLocator' => 'OTHER1'])
            ->assertStatus(404);

        $this->assertDatabaseHas('pnrs', ['record_locator' => 'OTHER1']);
    }

    public function test_delete_without_pnr_returns_404(): void
    {
        Pnr::query()->delete();

        $this->actingAs(User::where('username', 'student')->firstOrFail())
            ->deleteJson('/api/dashboard/active-pnr')
            ->assertStatus(404);
    }

    public function test_delete_active_pnr_requires_authentication(): void
    {
        $this->deleteJson('/api/dashboard/active-pnr')->assertStatus(401);
    }
}
