<?php

namespace Database\Seeders;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Airport;
use App\Models\QueueItem;
use App\Models\Scenario;
use App\Models\Submission;
use App\Models\TerminalActivityLog;
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
            if (! empty($batch)) {
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
            if (! empty($batch)) {
                DB::table('airlines')->insert($batch);
            }
        }

        // 5. Seed Training Scenarios & Drills
        $scenarios = [
            [
                'id' => 'SCN-RT-01',
                'title' => 'Transatlantic Round-Trip Booking (LHR-JFK)',
                'category' => 'Round-trip',
                'description' => 'Create a complete, confirmed round-trip reservation for an adult passenger travelling from London Heathrow to New York JFK on British Airways.',
                'difficulty' => 'Beginner',
                'passenger_profile' => [
                    'names' => [
                        ['lastName' => 'SMITH', 'firstName' => 'JOHN', 'title' => 'MR', 'type' => 'ADT'],
                    ],
                    'contactPhone' => 'LON 44-20-7946-0123-A',
                    'contactEmail' => 'JOHN.SMITH//EMAIL.COM',
                ],
                'flight_segments' => [
                    ['origin' => 'LHR', 'destination' => 'JFK', 'date' => '15NOV', 'airline' => 'BA', 'class' => 'Y'],
                    ['origin' => 'JFK', 'destination' => 'LHR', 'date' => '22NOV', 'airline' => 'BA', 'class' => 'Y'],
                ],
                'validation_criteria' => [
                    'mandatoryPrint' => ['name' => true, 'itinerary' => true, 'contact' => true, 'ticketing' => true, 'receivedFrom' => true],
                    'minSegments' => 2,
                    'requireTst' => true,
                    'requireETicket' => false,
                ],
                'time_limit_minutes' => 25,
                'created_by' => 'instructor',
            ],
            [
                'id' => 'SCN-MC-02',
                'title' => 'Multi-City Itinerary with Infant-in-lap (DOH-LHR-CDG)',
                'category' => 'Infant-in-lap',
                'description' => 'Book a multi-city journey for an adult travelling with an infant in lap (under 2 years old) from Doha to London, continuing onward to Paris Charles de Gaulle.',
                'difficulty' => 'Intermediate',
                'passenger_profile' => [
                    'names' => [
                        ['lastName' => 'CONNOR', 'firstName' => 'SARAH', 'title' => 'MRS', 'type' => 'ADT', 'infantName' => 'TOMMY', 'dob' => '15JAN25'],
                    ],
                    'contactPhone' => 'DOH 974-4455-8899-H',
                    'contactEmail' => 'S.CONNOR//VOYAGE.COM',
                ],
                'flight_segments' => [
                    ['origin' => 'DOH', 'destination' => 'LHR', 'date' => '20NOV', 'airline' => 'QR', 'class' => 'J'],
                    ['origin' => 'LHR', 'destination' => 'CDG', 'date' => '25NOV', 'airline' => 'BA', 'class' => 'Y'],
                ],
                'validation_criteria' => [
                    'mandatoryPrint' => ['name' => true, 'itinerary' => true, 'contact' => true, 'ticketing' => true, 'receivedFrom' => true],
                    'minSegments' => 2,
                    'requireTst' => true,
                    'requireETicket' => false,
                ],
                'time_limit_minutes' => 35,
                'created_by' => 'instructor',
            ],
            [
                'id' => 'SCN-QE-03',
                'title' => 'Schedule Exchange & Reissue on Queue 7',
                'category' => 'Schedule Exchange',
                'description' => 'An involuntary schedule change occurred on flight QR 009. Access Queue 7, display the affected PNR, re-confirm the revised flight timing, update received from, and re-file.',
                'difficulty' => 'Advanced',
                'passenger_profile' => [
                    'names' => [
                        ['lastName' => 'AL-THANI', 'firstName' => 'HAMAD', 'title' => 'MR', 'type' => 'ADT'],
                    ],
                    'contactPhone' => 'DOH 974-5555-1234-A',
                ],
                'flight_segments' => [
                    ['origin' => 'DOH', 'destination' => 'LHR', 'date' => '18NOV', 'airline' => 'QR', 'class' => 'F'],
                ],
                'validation_criteria' => [
                    'mandatoryPrint' => ['name' => true, 'itinerary' => true, 'contact' => true, 'ticketing' => true, 'receivedFrom' => true],
                    'minSegments' => 1,
                    'expectedQueues' => [7],
                    'requireTst' => true,
                    'requireETicket' => false,
                ],
                'time_limit_minutes' => 30,
                'created_by' => 'instructor',
            ],
            [
                'id' => 'SCN-TK-04',
                'title' => 'Urgent Ticketing Time Limit on Queue 1',
                'category' => 'Urgent Ticketing',
                'description' => 'Retrieve an un-ticketed reservation on Queue 1 approaching cancellation deadline. Execute FXP to price, add form of payment (FP CASH), and issue 13-digit electronic tickets (TTP).',
                'difficulty' => 'Intermediate',
                'passenger_profile' => [
                    'names' => [
                        ['lastName' => 'DOE', 'firstName' => 'ALEX', 'title' => 'MR', 'type' => 'ADT'],
                    ],
                    'contactPhone' => 'LON 44-20-7111-2222-A',
                ],
                'flight_segments' => [
                    ['origin' => 'LHR', 'destination' => 'DXB', 'date' => '10DEC', 'airline' => 'EK', 'class' => 'Y'],
                ],
                'validation_criteria' => [
                    'mandatoryPrint' => ['name' => true, 'itinerary' => true, 'contact' => true, 'ticketing' => true, 'receivedFrom' => true],
                    'minSegments' => 1,
                    'expectedQueues' => [1],
                    'requireTst' => true,
                    'requireETicket' => true,
                ],
                'time_limit_minutes' => 20,
                'created_by' => 'instructor',
            ],
        ];

        foreach ($scenarios as $scn) {
            Scenario::updateOrCreate(['id' => $scn['id']], $scn);
        }

        // 6. Seed Simulated Queue Items
        $queueItems = [
            [
                'queue_number' => 1,
                'category' => 'TICKETING',
                'office_id' => 'LON1A0980',
                'target_user_id' => 'student',
                'pnr_locator' => 'K9L2M5',
                'pnr_summary' => ['passenger' => 'DOE/ALEX MR', 'itinerary' => 'LHR-DXB (EK 002)', 'urgency' => 'CRITICAL'],
                'message' => 'TTL EXPIRING IN 2 HOURS - ISSUE TTP IMMEDIATELY',
                'status' => 'PENDING',
            ],
            [
                'queue_number' => 7,
                'category' => 'SCHEDULE_CHANGE',
                'office_id' => 'LON1A0980',
                'target_user_id' => null,
                'pnr_locator' => 'W4R8P1',
                'pnr_summary' => ['passenger' => 'CONNOR/SARAH MRS', 'itinerary' => 'DOH-LHR (QR 009)', 'urgency' => 'HIGH'],
                'message' => 'FLIGHT QR 009 RETIMED: NEW DEPARTURE 08:45 -> 10:15',
                'status' => 'PENDING',
            ],
            [
                'queue_number' => 8,
                'category' => 'WAITLIST',
                'office_id' => 'LON1A0980',
                'target_user_id' => null,
                'pnr_locator' => 'J7X3Q9',
                'pnr_summary' => ['passenger' => 'MILLER/DAVID MR', 'itinerary' => 'LHR-JFK (BA 117)', 'urgency' => 'NORMAL'],
                'message' => 'CLASS J SEAT CONFIRMED FROM WAITLIST HL -> HK',
                'status' => 'PENDING',
            ],
            [
                'queue_number' => 9,
                'category' => 'GENERAL_MESSAGE',
                'office_id' => 'LON1A0980',
                'target_user_id' => null,
                'pnr_locator' => 'B2T6Y8',
                'pnr_summary' => ['passenger' => 'ZHAO/WEI MS', 'itinerary' => 'LHR-HKG (CX 250)', 'urgency' => 'NORMAL'],
                'message' => 'AIRLINE CONFIRMED SSR WCHR (WHEELCHAIR ASSIST)',
                'status' => 'PENDING',
            ],
        ];

        foreach ($queueItems as $qItem) {
            QueueItem::updateOrCreate(
                ['pnr_locator' => $qItem['pnr_locator'], 'queue_number' => $qItem['queue_number']],
                $qItem
            );
        }

        // 7. Seed Demo Student Submission & Grade
        Submission::updateOrCreate(
            [
                'scenario_id' => 'SCN-RT-01',
                'user_id' => 'student',
            ],
            [
                'status' => 'reviewed',
                'score' => 95,
                'auto_grade_results' => [
                    'score' => 95,
                    'maxScore' => 100,
                    'passed' => true,
                    'elementsPassedCount' => 5,
                    'elementsTotalCount' => 5,
                    'summary' => '5/5 mandatory PRINT elements met (95% score)',
                    'checks' => [
                        ['code' => 'NM', 'name' => 'Passenger Name Element (NM)', 'passed' => true, 'pointsAwarded' => 20, 'maxPoints' => 20, 'details' => '1 passenger registered'],
                        ['code' => 'SS', 'name' => 'Itinerary Segments (SS)', 'passed' => true, 'pointsAwarded' => 20, 'maxPoints' => 20, 'details' => '2 segments reserved (LHR-JFK-LHR)'],
                        ['code' => 'AP', 'name' => 'Contact Element (AP / APE)', 'passed' => true, 'pointsAwarded' => 15, 'maxPoints' => 15, 'details' => 'Agency phone contact added'],
                        ['code' => 'TK', 'name' => 'Ticketing Element (TK)', 'passed' => true, 'pointsAwarded' => 15, 'maxPoints' => 15, 'details' => 'Ticketing limit set: TKTL15NOV/LON1A0980'],
                        ['code' => 'RF', 'name' => 'Received From Element (RF)', 'passed' => true, 'pointsAwarded' => 15, 'maxPoints' => 15, 'details' => 'Received from PASSENGER validated'],
                        ['code' => 'FXP', 'name' => 'Fare Calculation & Stored TST', 'passed' => true, 'pointsAwarded' => 10, 'maxPoints' => 10, 'details' => 'Transitional Stored Ticket (TST) created'],
                    ],
                ],
                'manual_override_score' => 95,
                'instructor_feedback' => 'Exceptional accuracy on mandatory PRINT elements. Clean syntax throughout.',
                'terminal_logs' => [
                    ['command' => 'AN15NOVLHRJFK/ABA', 'output_lines' => ['1 BA 117 Y9 B9'], 'is_error' => false, 'command_type' => 'AVAILABILITY'],
                    ['command' => 'SS1Y1', 'output_lines' => ['1 BA 117 Y 15NOV LHRJFK HK1'], 'is_error' => false, 'command_type' => 'SELL'],
                    ['command' => 'AN22NOVJFKLHR/ABA', 'output_lines' => ['1 BA 178 Y9 B9'], 'is_error' => false, 'command_type' => 'AVAILABILITY'],
                    ['command' => 'SS1Y1', 'output_lines' => ['2 BA 178 Y 22NOV JFKLHR HK1'], 'is_error' => false, 'command_type' => 'SELL'],
                    ['command' => 'NM1SMITH/JOHN MR', 'output_lines' => ['1.SMITH/JOHN MR'], 'is_error' => false, 'command_type' => 'PNR_NAME'],
                    ['command' => 'AP LON 44-20-7946-0123-A', 'output_lines' => ['1 AP LON 44-20-7946-0123-A'], 'is_error' => false, 'command_type' => 'PNR_CONTACT'],
                    ['command' => 'TKTL15NOV/LON1A0980', 'output_lines' => ['TK TL15NOV/LON1A0980'], 'is_error' => false, 'command_type' => 'PNR_TICKETING'],
                    ['command' => 'RF PASSENGER', 'output_lines' => ['RF PASSENGER'], 'is_error' => false, 'command_type' => 'PNR_RECEIVED'],
                    ['command' => 'FXP', 'output_lines' => ['TST 01 STORED - TOTAL GBP 645.20'], 'is_error' => false, 'command_type' => 'PRICING'],
                    ['command' => 'ET', 'output_lines' => ['OK LON1A0980 1234AB K7G8X2'], 'is_error' => false, 'command_type' => 'ET'],
                ],
                'pnr_record_locator' => 'K7G8X2',
                'time_elapsed_seconds' => 482,
                'submitted_at' => now()->subDay(),
                'reviewed_at' => now()->subHours(12),
            ]
        );

        // 8. Seed Activity Logs for Student
        if (TerminalActivityLog::count() === 0) {
            $logs = [
                ['user_id' => 'student', 'command' => 'AN15NOVLHRJFK', 'output_lines' => ['1 BA 117 Y9 B9'], 'is_error' => false, 'command_type' => 'AVAILABILITY'],
                ['user_id' => 'student', 'command' => 'SS1Y1', 'output_lines' => ['1 BA 117 Y 15NOV LHRJFK HK1'], 'is_error' => false, 'command_type' => 'SELL'],
                ['user_id' => 'student', 'command' => 'NM1DOE/JANE MRS', 'output_lines' => ['1.DOE/JANE MRS'], 'is_error' => false, 'command_type' => 'PNR_NAME'],
                ['user_id' => 'student', 'command' => 'APLON', 'output_lines' => ['INVALID FORMAT: SPECIFY PHONE OR EMAIL WITH APE'], 'is_error' => true, 'command_type' => 'PNR_CONTACT'],
                ['user_id' => 'student', 'command' => 'AP LON 44-20-7123-4567-A', 'output_lines' => ['1 AP LON 44-20-7123-4567-A'], 'is_error' => false, 'command_type' => 'PNR_CONTACT'],
                ['user_id' => 'student', 'command' => 'FXP', 'output_lines' => ['TST 01 STORED'], 'is_error' => false, 'command_type' => 'PRICING'],
            ];

            foreach ($logs as $l) {
                TerminalActivityLog::create(array_merge($l, ['execution_time_ms' => 42, 'created_at' => now()]));
            }
        }
    }
}
