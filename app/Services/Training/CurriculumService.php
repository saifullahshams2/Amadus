<?php

namespace App\Services\Training;

class CurriculumService
{
    public function getModules(): array
    {
        return [
            [
                'id' => 'mod-1',
                'number' => 1,
                'title' => 'System Access & IATA Decoding / Encoding',
                'description' => 'Master the cryptic basics: sign into work areas and lookup any world airport, city, airline, or aircraft equipment.',
                'badge' => 'IATA Specialist',
                'level' => 'Beginner',
                'lessons' => [
                    [
                        'id' => 'les-1-1',
                        'moduleId' => 'mod-1',
                        'moduleTitle' => 'System Access & IATA Decoding / Encoding',
                        'lessonNumber' => 1,
                        'title' => 'Work Area Sign-In and System Access',
                        'conceptSummary' => 'In Amadeus, every travel agent must sign into a work area (A through F) with their agent identification before performing GDS transactions.',
                        'scenarioStory' => 'Good morning! You just arrived at the travel agency desk. Your supervisor asks you to sign into Work Area A so you can start serving airline passengers.',
                        'keyCommands' => [
                            ['syntax' => 'JI*1234AB/GS', 'description' => 'Sign into work area with Agent ID and General Supervisor role', 'example' => 'JI*1234AB/GS'],
                            ['syntax' => 'JO', 'description' => 'Sign out of current work area', 'example' => 'JO'],
                            ['syntax' => 'HE', 'description' => 'Display quick GDS command help', 'example' => 'HE'],
                        ],
                        'objectives' => [
                            [
                                'id' => 'obj-1-1-1',
                                'title' => 'Sign In to the GDS',
                                'description' => 'Execute the sign-in command to activate your work terminal.',
                                'hint1' => 'The command starts with "JI" or "SI".',
                                'hint2' => 'Type "JI*1234AB/GS" or simply "JI".',
                                'solutionCommand' => 'JI*1234AB/GS',
                                'validate' => [
                                    'commandPattern' => '^(JI.*|SI.*)$',
                                    'expectedOutputContains' => ['OK SIGN-IN', 'WORK AREA'],
                                ],
                            ],
                            [
                                'id' => 'obj-1-1-2',
                                'title' => 'Open System Help',
                                'description' => 'Check the quick command reference on your terminal.',
                                'hint1' => 'Type the 2-letter abbreviation for Help.',
                                'hint2' => 'Type "HE" or "HELP".',
                                'solutionCommand' => 'HE',
                                'validate' => [
                                    'commandPattern' => '^(HE|HELP|\?)$',
                                    'expectedOutputContains' => ['QUICK COMMAND REFERENCE'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'id' => 'les-1-2',
                        'moduleId' => 'mod-1',
                        'moduleTitle' => 'System Access & IATA Decoding / Encoding',
                        'lessonNumber' => 2,
                        'title' => 'Decoding Locations, Airlines, & Aircraft',
                        'conceptSummary' => 'DAN decodes or encodes cities and airports; DNA decodes airlines; DNE decodes aircraft types.',
                        'scenarioStory' => 'A customer calls asking for flights to London, but they do not know the airport codes. Another client asks which airline is "QR", and what aircraft "77W" represents.',
                        'keyCommands' => [
                            ['syntax' => 'DAN <city or airport>', 'description' => 'Decode or encode airport/city', 'example' => 'DAN LONDON or DAN DOH'],
                            ['syntax' => 'DNA <airline code or name>', 'description' => 'Decode or encode airline', 'example' => 'DNA QR or DNA EMIRATES'],
                            ['syntax' => 'DNE <equipment code>', 'description' => 'Decode aircraft equipment', 'example' => 'DNE 77W or DNE 359'],
                        ],
                        'objectives' => [
                            [
                                'id' => 'obj-1-2-1',
                                'title' => 'Decode London Airports',
                                'description' => 'Look up all airports serving the London metropolitan area.',
                                'hint1' => 'Use the DAN command with the city name.',
                                'hint2' => 'Type "DAN LONDON".',
                                'solutionCommand' => 'DAN LONDON',
                                'validate' => [
                                    'commandPattern' => '^DAN\s+LONDON$',
                                    'expectedOutputContains' => ['LHR', 'HEATHROW'],
                                ],
                            ],
                            [
                                'id' => 'obj-1-2-2',
                                'title' => 'Decode Airline Code QR',
                                'description' => 'Find out which airline owns the 2-letter IATA code "QR".',
                                'hint1' => 'Use DNA followed by the 2-letter code.',
                                'hint2' => 'Type "DNA QR".',
                                'solutionCommand' => 'DNA QR',
                                'validate' => [
                                    'commandPattern' => '^DNA\s+QR$',
                                    'expectedOutputContains' => ['QATAR AIRWAYS', '157'],
                                ],
                            ],
                            [
                                'id' => 'obj-1-2-3',
                                'title' => 'Decode Aircraft Code 77W',
                                'description' => 'Decode the aircraft equipment code 77W.',
                                'hint1' => 'Use DNE followed by the equipment code.',
                                'hint2' => 'Type "DNE 77W".',
                                'solutionCommand' => 'DNE 77W',
                                'validate' => [
                                    'commandPattern' => '^DNE\s+77W$',
                                    'expectedOutputContains' => ['BOEING 777-300ER'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id' => 'mod-2',
                'number' => 2,
                'title' => 'Flight Availability & Schedules',
                'description' => 'Learn to search neutral flight availability (AN), timetable schedules (SN), and filter by airline.',
                'badge' => 'Schedule Master',
                'level' => 'Beginner',
                'lessons' => [
                    [
                        'id' => 'les-2-1',
                        'moduleId' => 'mod-2',
                        'moduleTitle' => 'Flight Availability & Schedules',
                        'lessonNumber' => 1,
                        'title' => 'Searching Neutral Flight Availability',
                        'conceptSummary' => 'AN (Availability Neutral) displays all scheduled flights with live seat bucket availability (F, J, Y classes) for a given date and route.',
                        'scenarioStory' => 'A traveler wants to fly from Doha (DOH) to London (LHR) on the 15th of November. You need to check available flight options and seat inventory.',
                        'keyCommands' => [
                            ['syntax' => 'AN<date><orig><dest>', 'description' => 'Neutral availability search', 'example' => 'AN15NOVDOHLHR'],
                            ['syntax' => 'AN<date><orig><dest>/A<carrier>', 'description' => 'Availability filtered by airline', 'example' => 'AN15NOVLONNYC/ABA'],
                            ['syntax' => 'MD', 'description' => 'Move Down (scroll to next page)', 'example' => 'MD'],
                        ],
                        'objectives' => [
                            [
                                'id' => 'obj-2-1-1',
                                'title' => 'Search Doha to London Flights',
                                'description' => 'Query neutral availability for travel from DOH to LHR on 15NOV.',
                                'hint1' => 'The command syntax is AN + Date + Origin + Destination.',
                                'hint2' => 'Type "AN15NOVDOHLHR".',
                                'solutionCommand' => 'AN15NOVDOHLHR',
                                'validate' => [
                                    'commandPattern' => '^AN15NOVDOHLHR$',
                                    'expectedOutputContains' => ['DOHLHR', 'J9 C9'],
                                ],
                            ],
                            [
                                'id' => 'obj-2-1-2',
                                'title' => 'Filter Availability by Qatar Airways',
                                'description' => 'Search the same route specifically for Qatar Airways flights using the /A carrier qualifier.',
                                'hint1' => 'Append /AQR to the availability entry.',
                                'hint2' => 'Type "AN15NOVDOHLHR/AQR".',
                                'solutionCommand' => 'AN15NOVDOHLHR/AQR',
                                'validate' => [
                                    'commandPattern' => '^AN15NOVDOHLHR/AQR$',
                                    'expectedOutputContains' => ['QR', 'DOHLHR'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id' => 'mod-3',
                'number' => 3,
                'title' => 'The Core PNR: Mandatory PRINT Elements',
                'description' => 'Master the golden rule of GDS: Phone (AP), Received From (RF), Itinerary (SS), Name (NM), Ticketing (TK).',
                'badge' => 'Booking Specialist',
                'level' => 'Intermediate',
                'lessons' => [
                    [
                        'id' => 'les-3-1',
                        'moduleId' => 'mod-3',
                        'moduleTitle' => 'The Core PNR: Mandatory PRINT Elements',
                        'lessonNumber' => 1,
                        'title' => 'Building a Confirmed PNR from Scratch',
                        'conceptSummary' => 'Without all 5 PRINT elements, Amadeus will reject the booking upon End Transaction (ET).',
                        'scenarioStory' => 'Mr. John Doe wants to book 1 Economy seat from London Heathrow (LHR) to New York (JFK) on 15NOV. Follow the complete reservation workflow to issue him a PNR.',
                        'keyCommands' => [
                            ['syntax' => 'AN<date><orig><dest>', 'description' => 'Search flights', 'example' => 'AN15NOVLHRJFK'],
                            ['syntax' => 'SS<seats><class><line>', 'description' => 'Short sell seat from line', 'example' => 'SS1Y1'],
                            ['syntax' => 'NM1<SURNAME>/<FIRSTNAME> [TITLE]', 'description' => 'Add passenger name', 'example' => 'NM1DOE/JOHN MR'],
                            ['syntax' => 'AP <phone>', 'description' => 'Agency or passenger contact', 'example' => 'AP LON 44-20-7946-0912-A'],
                            ['syntax' => 'TKTL<date>', 'description' => 'Ticketing time limit', 'example' => 'TKTL10NOV/LON1A0980'],
                            ['syntax' => 'RF <name>', 'description' => 'Received from person', 'example' => 'RF PASSENGER'],
                            ['syntax' => 'ET', 'description' => 'End Transaction and save PNR', 'example' => 'ET'],
                        ],
                        'objectives' => [
                            [
                                'id' => 'obj-3-1-1',
                                'title' => 'Step 1: Check Availability LHR to JFK',
                                'description' => 'Display available flights from LHR to JFK on 15NOV.',
                                'hint1' => 'Use AN with 15NOVLHRJFK.',
                                'hint2' => 'Type "AN15NOVLHRJFK".',
                                'solutionCommand' => 'AN15NOVLHRJFK',
                                'validate' => [
                                    'commandPattern' => '^AN15NOVLHRJFK.*$',
                                    'expectedOutputContains' => ['LHRJFK'],
                                ],
                            ],
                            [
                                'id' => 'obj-3-1-2',
                                'title' => 'Step 2: Sell 1 Seat in Y Class',
                                'description' => 'Book 1 seat in Y (Economy) class from Line 1 of the availability display.',
                                'hint1' => 'Syntax is SS + Seats (1) + Class (Y) + Line (1).',
                                'hint2' => 'Type "SS1Y1".',
                                'solutionCommand' => 'SS1Y1',
                                'validate' => [
                                    'commandPattern' => '^SS1[A-Z]1$',
                                    'pnrCheck' => 'has_segments',
                                ],
                            ],
                            [
                                'id' => 'obj-3-1-3',
                                'title' => 'Step 3: Enter Passenger Name',
                                'description' => 'Add passenger name: Mr. John Doe.',
                                'hint1' => 'Format: NM1SURNAME/FIRSTNAME MR.',
                                'hint2' => 'Type "NM1DOE/JOHN MR".',
                                'solutionCommand' => 'NM1DOE/JOHN MR',
                                'validate' => [
                                    'commandPattern' => '^NM1DOE/JOHN\s+MR$',
                                    'pnrCheck' => 'has_doe_john',
                                ],
                            ],
                            [
                                'id' => 'obj-3-1-4',
                                'title' => 'Step 4: Add Contact Element',
                                'description' => 'Add agency contact telephone number.',
                                'hint1' => 'Use the AP command.',
                                'hint2' => 'Type "AP LON 44-20-7946-0912-A".',
                                'solutionCommand' => 'AP LON 44-20-7946-0912-A',
                                'validate' => [
                                    'commandPattern' => '^AP\s+.*$',
                                    'pnrCheck' => 'has_contacts',
                                ],
                            ],
                            [
                                'id' => 'obj-3-1-5',
                                'title' => 'Step 5: Set Ticketing Arrangement',
                                'description' => 'Set ticketing time limit for 10NOV.',
                                'hint1' => 'Use TKTL followed by the date.',
                                'hint2' => 'Type "TKTL10NOV" or "TKTL10NOV/LON1A0980".',
                                'solutionCommand' => 'TKTL10NOV/LON1A0980',
                                'validate' => [
                                    'commandPattern' => '^TKTL.*$',
                                    'pnrCheck' => 'has_ticketing',
                                ],
                            ],
                            [
                                'id' => 'obj-3-1-6',
                                'title' => 'Step 6: Add Received From',
                                'description' => 'Indicate that instructions were received from the passenger.',
                                'hint1' => 'Use RF followed by PASSENGER.',
                                'hint2' => 'Type "RF PASSENGER".',
                                'solutionCommand' => 'RF PASSENGER',
                                'validate' => [
                                    'commandPattern' => '^RF\s+.*$',
                                    'pnrCheck' => 'has_rf',
                                ],
                            ],
                            [
                                'id' => 'obj-3-1-7',
                                'title' => 'Step 7: End Transaction (ET)',
                                'description' => 'Commit the PNR to obtain your 6-character Record Locator.',
                                'hint1' => 'Type the 2-letter End Transaction command.',
                                'hint2' => 'Type "ET".',
                                'solutionCommand' => 'ET',
                                'validate' => [
                                    'commandPattern' => '^(ET|ER)$',
                                    'expectedOutputContains' => ['RLR'],
                                    'pnrCheck' => 'has_locator',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id' => 'mod-4',
                'number' => 4,
                'title' => 'Fares, Pricing & TST Storage',
                'description' => 'Learn to quote tariffs (FQD), calculate itinerary fares (FXP), and store Transitional Stored Tickets (TST).',
                'badge' => 'Tariff Specialist',
                'level' => 'Intermediate',
                'lessons' => [
                    [
                        'id' => 'les-4-1',
                        'moduleId' => 'mod-4',
                        'moduleTitle' => 'Fares, Pricing & TST Storage',
                        'lessonNumber' => 1,
                        'title' => 'Pricing Active Itineraries with FXP',
                        'conceptSummary' => 'FXP prices the booked segments and creates a Transitional Stored Ticket (TST) record with base fare, taxes, and grand total.',
                        'scenarioStory' => 'Your client has a reserved flight. Now they want to know the exact total cost including airport taxes, and you must store the fare before ticketing.',
                        'keyCommands' => [
                            ['syntax' => 'FQD <orig><dest>/D<date>/A<carrier>', 'description' => 'Display published fares', 'example' => 'FQDDOHLHR/D15NOV/AQR'],
                            ['syntax' => 'FXP', 'description' => 'Price itinerary and store TST', 'example' => 'FXP'],
                            ['syntax' => 'FXX', 'description' => 'Price without storing TST', 'example' => 'FXX'],
                        ],
                        'objectives' => [
                            [
                                'id' => 'obj-4-1-1',
                                'title' => 'Check Published Fares with FQD',
                                'description' => 'View the fare quote display for DOH to LHR on Qatar Airways.',
                                'hint1' => 'Use FQD with city pair DOHLHR and carrier QR.',
                                'hint2' => 'Type "FQDDOHLHR/D15NOV/AQR".',
                                'solutionCommand' => 'FQDDOHLHR/D15NOV/AQR',
                                'validate' => [
                                    'commandPattern' => '^FQD.*$',
                                    'expectedOutputContains' => ['PUBLIC FARES'],
                                ],
                            ],
                            [
                                'id' => 'obj-4-1-2',
                                'title' => 'Price Itinerary and Store TST with FXP',
                                'description' => 'Price the current active reservation and generate a TST record.',
                                'hint1' => 'Type the 3-letter pricing and TST command.',
                                'hint2' => 'Type "FXP".',
                                'solutionCommand' => 'FXP',
                                'validate' => [
                                    'commandPattern' => '^FXP$',
                                    'expectedOutputContains' => ['TST', 'FARE', 'TOTAL'],
                                    'pnrCheck' => 'has_tst',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id' => 'mod-5',
                'number' => 5,
                'title' => 'Payment & Electronic Ticketing (TTP)',
                'description' => 'Add form of payment (FP) and issue live simulated 13-digit e-tickets (TTP).',
                'badge' => 'Certified Ticketing Agent',
                'level' => 'Advanced',
                'lessons' => [
                    [
                        'id' => 'les-5-1',
                        'moduleId' => 'mod-5',
                        'moduleTitle' => 'Payment & Electronic Ticketing (TTP)',
                        'lessonNumber' => 1,
                        'title' => 'Form of Payment and Ticket Issuance',
                        'conceptSummary' => 'TTP generates an electronic ticket document with 13-digit airline ticket numbers and open flight coupons.',
                        'scenarioStory' => 'The client pays in cash. Add the Form of Payment (FP), set Received From, and issue the electronic ticket.',
                        'keyCommands' => [
                            ['syntax' => 'FP CASH', 'description' => 'Set Form of Payment to Cash', 'example' => 'FP CASH'],
                            ['syntax' => 'FP CC<type><card>/<exp>', 'description' => 'Credit card payment', 'example' => 'FP CCVI4111111111111111/1226'],
                            ['syntax' => 'TTP', 'description' => 'Ticket Print / Issue e-ticket', 'example' => 'TTP'],
                            ['syntax' => 'TWD', 'description' => 'Display electronic ticket', 'example' => 'TWD'],
                        ],
                        'objectives' => [
                            [
                                'id' => 'obj-5-1-1',
                                'title' => 'Add Form of Payment (FP)',
                                'description' => 'Set the payment method to CASH.',
                                'hint1' => 'Type FP followed by CASH.',
                                'hint2' => 'Type "FP CASH".',
                                'solutionCommand' => 'FP CASH',
                                'validate' => [
                                    'commandPattern' => '^FP\s+CASH$',
                                    'pnrCheck' => 'has_fop_cash',
                                ],
                            ],
                            [
                                'id' => 'obj-5-1-2',
                                'title' => 'Add Received From',
                                'description' => 'Add Received From element before ticketing.',
                                'hint1' => 'Type RF PASSENGER.',
                                'hint2' => 'Type "RF PASSENGER".',
                                'solutionCommand' => 'RF PASSENGER',
                                'validate' => [
                                    'commandPattern' => '^RF\s+.*$',
                                    'pnrCheck' => 'has_rf',
                                ],
                            ],
                            [
                                'id' => 'obj-5-1-3',
                                'title' => 'Issue Electronic Ticket (TTP)',
                                'description' => 'Execute the electronic ticketing command to generate the 13-digit ticket numbers.',
                                'hint1' => 'Type TTP.',
                                'hint2' => 'Type "TTP".',
                                'solutionCommand' => 'TTP',
                                'validate' => [
                                    'commandPattern' => '^TTP$',
                                    'expectedOutputContains' => ['OK ETICKET'],
                                    'pnrCheck' => 'has_tickets',
                                ],
                            ],
                            [
                                'id' => 'obj-5-1-4',
                                'title' => 'Display Electronic Ticket Record (TWD)',
                                'description' => 'Inspect the newly issued ticket document.',
                                'hint1' => 'Type TWD.',
                                'hint2' => 'Type "TWD".',
                                'solutionCommand' => 'TWD',
                                'validate' => [
                                    'commandPattern' => '^TWD.*$',
                                    'expectedOutputContains' => ['ELECTRONIC TICKET RECORD', 'OPEN'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function findLesson(string $lessonId): ?array
    {
        foreach ($this->getModules() as $m) {
            foreach ($m['lessons'] as $l) {
                if ($l['id'] === $lessonId) {
                    return $l;
                }
            }
        }

        return null;
    }

    public function validateObjective(string $command, array $outputLines, ?array $pnr, array $validate): bool
    {
        // 1. Check command regex pattern
        if (! empty($validate['commandPattern'])) {
            if (! preg_match("/{$validate['commandPattern']}/i", trim($command))) {
                return false;
            }
        }

        // 2. Check expected output contains
        if (! empty($validate['expectedOutputContains'])) {
            $fullOutput = strtoupper(implode(' ', $outputLines));
            foreach ($validate['expectedOutputContains'] as $str) {
                if (! str_contains($fullOutput, strtoupper($str))) {
                    return false;
                }
            }
        }

        // 3. Check PNR state
        if (! empty($validate['pnrCheck'])) {
            $check = $validate['pnrCheck'];
            $pass = match ($check) {
                'has_segments' => count($pnr['segments'] ?? []) >= 1,
                'has_doe_john' => ! empty($pnr['passengers']) && array_filter($pnr['passengers'], fn ($p) => ($p['lastName'] ?? '') === 'DOE' && ($p['firstName'] ?? '') === 'JOHN'),
                'has_contacts' => count($pnr['contacts'] ?? []) >= 1,
                'has_ticketing' => ! empty($pnr['ticketing']),
                'has_rf' => ! empty($pnr['receivedFrom']),
                'has_locator' => ! empty($pnr['recordLocator']),
                'has_tst' => count($pnr['tsts'] ?? []) >= 1,
                'has_fop_cash' => ($pnr['fop']['details'] ?? '') === 'CASH',
                'has_tickets' => count($pnr['tickets'] ?? []) >= 1,
                default => true,
            };
            if (! $pass) {
                return false;
            }
        }

        return true;
    }
}
