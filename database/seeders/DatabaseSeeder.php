<?php

namespace Database\Seeders;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Airport;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Demo Users
        User::updateOrCreate(
            ['username' => 'student'],
            [
                'name' => 'Demo Student',
                'email' => 'student@amadeus-gds.demo',
                'password' => Hash::make('amadeus123'),
                'role' => 'student',
                'office_id' => 'LON1A0980',
                'agent_id' => '1234AB',
            ]
        );

        User::updateOrCreate(
            ['username' => 'instructor'],
            [
                'name' => 'Lead Flight Instructor',
                'email' => 'instructor@amadeus-gds.demo',
                'password' => Hash::make('amadeus123'),
                'role' => 'instructor',
                'office_id' => 'LON1A0980',
                'agent_id' => 'IN01GS',
            ]
        );

        // 2. Seed Aircraft Equipment
        $aircraftList = [
            ['code' => '318', 'name' => 'Airbus A318', 'manufacturer' => 'Airbus', 'category' => 'NARROWBODY'],
            ['code' => '319', 'name' => 'Airbus A319', 'manufacturer' => 'Airbus', 'category' => 'NARROWBODY'],
            ['code' => '320', 'name' => 'Airbus A320-200', 'manufacturer' => 'Airbus', 'category' => 'NARROWBODY'],
            ['code' => '32N', 'name' => 'Airbus A320neo', 'manufacturer' => 'Airbus', 'category' => 'NARROWBODY'],
            ['code' => '321', 'name' => 'Airbus A321-200', 'manufacturer' => 'Airbus', 'category' => 'NARROWBODY'],
            ['code' => '32Q', 'name' => 'Airbus A321neo', 'manufacturer' => 'Airbus', 'category' => 'NARROWBODY'],
            ['code' => '32X', 'name' => 'Airbus A321XLR', 'manufacturer' => 'Airbus', 'category' => 'NARROWBODY'],
            ['code' => '332', 'name' => 'Airbus A330-200', 'manufacturer' => 'Airbus', 'category' => 'WIDEBODY'],
            ['code' => '333', 'name' => 'Airbus A330-300', 'manufacturer' => 'Airbus', 'category' => 'WIDEBODY'],
            ['code' => '339', 'name' => 'Airbus A330-900neo', 'manufacturer' => 'Airbus', 'category' => 'WIDEBODY'],
            ['code' => '343', 'name' => 'Airbus A340-300', 'manufacturer' => 'Airbus', 'category' => 'WIDEBODY'],
            ['code' => '346', 'name' => 'Airbus A340-600', 'manufacturer' => 'Airbus', 'category' => 'WIDEBODY'],
            ['code' => '359', 'name' => 'Airbus A350-900', 'manufacturer' => 'Airbus', 'category' => 'WIDEBODY'],
            ['code' => '35K', 'name' => 'Airbus A350-1000', 'manufacturer' => 'Airbus', 'category' => 'WIDEBODY'],
            ['code' => '388', 'name' => 'Airbus A380-800', 'manufacturer' => 'Airbus', 'category' => 'WIDEBODY'],
            ['code' => '221', 'name' => 'Airbus A220-100', 'manufacturer' => 'Airbus', 'category' => 'NARROWBODY'],
            ['code' => '223', 'name' => 'Airbus A220-300', 'manufacturer' => 'Airbus', 'category' => 'NARROWBODY'],
            ['code' => '737', 'name' => 'Boeing 737-700', 'manufacturer' => 'Boeing', 'category' => 'NARROWBODY'],
            ['code' => '738', 'name' => 'Boeing 737-800', 'manufacturer' => 'Boeing', 'category' => 'NARROWBODY'],
            ['code' => '739', 'name' => 'Boeing 737-900ER', 'manufacturer' => 'Boeing', 'category' => 'NARROWBODY'],
            ['code' => '7M8', 'name' => 'Boeing 737 MAX 8', 'manufacturer' => 'Boeing', 'category' => 'NARROWBODY'],
            ['code' => '7M9', 'name' => 'Boeing 737 MAX 9', 'manufacturer' => 'Boeing', 'category' => 'NARROWBODY'],
            ['code' => '752', 'name' => 'Boeing 757-200', 'manufacturer' => 'Boeing', 'category' => 'NARROWBODY'],
            ['code' => '763', 'name' => 'Boeing 767-300ER', 'manufacturer' => 'Boeing', 'category' => 'WIDEBODY'],
            ['code' => '772', 'name' => 'Boeing 777-200ER/LR', 'manufacturer' => 'Boeing', 'category' => 'WIDEBODY'],
            ['code' => '773', 'name' => 'Boeing 777-300', 'manufacturer' => 'Boeing', 'category' => 'WIDEBODY'],
            ['code' => '77W', 'name' => 'Boeing 777-300ER', 'manufacturer' => 'Boeing', 'category' => 'WIDEBODY'],
            ['code' => '779', 'name' => 'Boeing 777-9', 'manufacturer' => 'Boeing', 'category' => 'WIDEBODY'],
            ['code' => '788', 'name' => 'Boeing 787-8 Dreamliner', 'manufacturer' => 'Boeing', 'category' => 'WIDEBODY'],
            ['code' => '789', 'name' => 'Boeing 787-9 Dreamliner', 'manufacturer' => 'Boeing', 'category' => 'WIDEBODY'],
            ['code' => '78X', 'name' => 'Boeing 787-10 Dreamliner', 'manufacturer' => 'Boeing', 'category' => 'WIDEBODY'],
            ['code' => '744', 'name' => 'Boeing 747-400', 'manufacturer' => 'Boeing', 'category' => 'WIDEBODY'],
            ['code' => '748', 'name' => 'Boeing 747-8 Intercontinental', 'manufacturer' => 'Boeing', 'category' => 'WIDEBODY'],
            ['code' => 'E70', 'name' => 'Embraer E170', 'manufacturer' => 'Embraer', 'category' => 'REGIONAL'],
            ['code' => 'E75', 'name' => 'Embraer E175', 'manufacturer' => 'Embraer', 'category' => 'REGIONAL'],
            ['code' => 'E90', 'name' => 'Embraer E190', 'manufacturer' => 'Embraer', 'category' => 'REGIONAL'],
            ['code' => 'E95', 'name' => 'Embraer E195', 'manufacturer' => 'Embraer', 'category' => 'REGIONAL'],
            ['code' => '295', 'name' => 'Embraer E195-E2', 'manufacturer' => 'Embraer', 'category' => 'REGIONAL'],
            ['code' => 'CR7', 'name' => 'Bombardier CRJ-700', 'manufacturer' => 'Bombardier', 'category' => 'REGIONAL'],
            ['code' => 'CR9', 'name' => 'Bombardier CRJ-900', 'manufacturer' => 'Bombardier', 'category' => 'REGIONAL'],
            ['code' => 'CRK', 'name' => 'Bombardier CRJ-1000', 'manufacturer' => 'Bombardier', 'category' => 'REGIONAL'],
            ['code' => 'AT7', 'name' => 'ATR 72-600', 'manufacturer' => 'ATR', 'category' => 'TURBOPROP'],
            ['code' => 'AT4', 'name' => 'ATR 42-600', 'manufacturer' => 'ATR', 'category' => 'TURBOPROP'],
            ['code' => 'DH4', 'name' => 'De Havilland Dash 8-400', 'manufacturer' => 'De Havilland', 'category' => 'TURBOPROP'],
        ];

        Aircraft::upsert($aircraftList, ['code'], ['name', 'manufacturer', 'category']);

        // 3. Seed Airports from JSON (if not already seeded)
        if (Airport::count() === 0 && file_exists(database_path('data/airports_full.json'))) {
            $json = json_decode(file_get_contents(database_path('data/airports_full.json')), true);
            $batch = [];
            foreach ($json as $code => $ap) {
                $batch[] = [
                    'iata' => $ap['iata'] ?? $code,
                    'icao' => $ap['icao'] ?? null,
                    'name' => $ap['name'] ?? '',
                    'city' => $ap['city'] ?? '',
                    'country' => $ap['country'] ?? '',
                    'lat' => $ap['lat'] ?? null,
                    'lon' => $ap['lon'] ?? null,
                    'tz' => $ap['tz'] ?? null,
                ];
                if (count($batch) >= 500) {
                    DB::table('airports')->insert($batch);
                    $batch = [];
                }
            }
            if (!empty($batch)) {
                DB::table('airports')->insert($batch);
            }
        }

        // 4. Seed Airlines from JSON (if not already seeded)
        if (Airline::count() === 0 && file_exists(database_path('data/airlines_full.json'))) {
            $json = json_decode(file_get_contents(database_path('data/airlines_full.json')), true);
            $batch = [];
            foreach ($json as $code => $al) {
                $batch[] = [
                    'iata' => $al['iata'] ?? $code,
                    'icao' => $al['icao'] ?? null,
                    'name' => $al['name'] ?? '',
                    'country' => $al['country'] ?? null,
                    'callsign' => $al['callsign'] ?? null,
                    'ticket_prefix' => $al['ticketPrefix'] ?? null,
                ];
                if (count($batch) >= 500) {
                    DB::table('airlines')->insert($batch);
                    $batch = [];
                }
            }
            if (!empty($batch)) {
                DB::table('airlines')->insert($batch);
            }
        }
    }
}
