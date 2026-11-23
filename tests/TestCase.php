<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends BaseTestCase
{
    /**
     * Helper to provision the single application user for feature tests.
     *
     * @return array{user: User}
     */
    protected function seedTestUsers(): array
    {
        $user = User::firstOrCreate(
            ['username' => 'student'],
            [
                'name' => 'Test User',
                'email' => 'student@amadeus-gds.demo',
                'password' => Hash::make('amadeus123'),
                'office_id' => 'LON1A0980',
                'agent_id' => '1234AB',
            ]
        );

        return ['user' => $user];
    }
}
