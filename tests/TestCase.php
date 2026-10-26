<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends BaseTestCase
{
    /**
     * Helper to provision test users for feature tests (since demo accounts are removed from production seeders).
     *
     * @return array{student: User, instructor: User}
     */
    protected function seedTestUsers(): array
    {
        $student = User::firstOrCreate(
            ['username' => 'student'],
            [
                'name' => 'Test Student',
                'email' => 'student@amadeus-gds.demo',
                'password' => Hash::make('amadeus123'),
                'role' => 'student',
                'is_approved' => true,
                'office_id' => 'LON1A0980',
                'agent_id' => '1234AB',
            ]
        );

        $instructor = User::firstOrCreate(
            ['username' => 'instructor'],
            [
                'name' => 'Test Instructor',
                'email' => 'instructor@amadeus-gds.demo',
                'password' => Hash::make('amadeus123'),
                'role' => 'instructor',
                'is_approved' => true,
                'office_id' => 'LON1A0980',
                'agent_id' => 'IN01GS',
            ]
        );

        return ['student' => $student, 'instructor' => $instructor];
    }
}
