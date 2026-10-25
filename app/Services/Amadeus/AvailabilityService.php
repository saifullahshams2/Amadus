<?php

namespace App\Services\Amadeus;

use App\Models\Airline;
use App\Models\Airport;

class AvailabilityResult
{
    public function __construct(
        public string $headerLine,
        public array $outputLines,
        public array $schedules,
        public bool $isError = false
    ) {}
}

class AvailabilityService
{
    private const MONTH_MAP = [
        'JAN' => 1, 'FEB' => 2, 'MAR' => 3, 'APR' => 4, 'MAY' => 5, 'JUN' => 6,
        'JUL' => 7, 'AUG' => 8, 'SEP' => 9, 'OCT' => 10, 'NOV' => 11, 'DEC' => 12,
    ];

    private const DAY_NAMES = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'];

    private const AIRLINE_HUBS = [
        'QR' => ['DOH'],
        'EK' => ['DXB'],
        'EY' => ['AUH'],
        'SV' => ['RUH', 'JED'],
        'BA' => ['LHR', 'LGW'],
        'VS' => ['LHR'],
        'AF' => ['CDG'],
        'KL' => ['AMS'],
        'LH' => ['FRA', 'MUC'],
        'LX' => ['ZRH'],
        'OS' => ['VIE'],
        'IB' => ['MAD'],
        'TK' => ['IST'],
        'SQ' => ['SIN'],
        'TG' => ['BKK'],
        'MH' => ['KUL'],
        'CX' => ['HKG'],
        'JL' => ['HND', 'NRT'],
        'NH' => ['HND', 'NRT'],
        'KE' => ['ICN'],
        'AI' => ['DEL', 'BOM'],
        'AA' => ['DFW', 'MIA', 'ORD', 'JFK', 'CLT'],
        'UA' => ['ORD', 'IAH', 'EWR', 'SFO', 'DEN'],
        'DL' => ['ATL', 'JFK', 'DTW', 'MSP', 'LAX'],
        'AC' => ['YYZ', 'YVR', 'YUL'],
        'QF' => ['SYD', 'MEL'],
        'NZ' => ['AKL'],
        'ET' => ['ADD'],
        'MS' => ['CAI'],
        'RJ' => ['AMM'],
        'KU' => ['KWI'],
        'GF' => ['BAH'],
        'WY' => ['MCT'],
    ];

    public function search(string $query): AvailabilityResult
    {
        $clean = strtoupper(trim($query));
        // Regex for: [DATE: 1-2 digits + 3 letters][ORIG: 3 chars][DEST: 3 chars][/A<carrier>]
        if (! preg_match('/^(\d{1,2}[A-Z]{3})?([A-Z]{3})([A-Z]{3})(?:\/A([A-Z0-9]{2}))?/', $clean, $matches)) {
            return new AvailabilityResult(
                '',
                ['FORMAT: AN[DATE]<ORIGIN><DESTINATION>[/A<AIRLINE>] (E.G. AN15NOVLHRJFK OR AN20DECDXBLHR/AEK)'],
                [],
                true
            );
        }

        $dateStr = $matches[1] ?? '';
        $orig = $matches[2];
        $dest = $matches[3];
        $carrierFilter = $matches[4] ?? null;

        $validOrig = Airport::find($orig) ?? (isset(DecodeService::METRO_CODES[$orig]));
        $validDest = Airport::find($dest) ?? (isset(DecodeService::METRO_CODES[$dest]));

        if (! $validOrig) {
            return new AvailabilityResult('', ["INVALID ORIGIN CITY/AIRPORT CODE: {$orig}"], [], true);
        }
        if (! $validDest) {
            return new AvailabilityResult('', ["INVALID DESTINATION CITY/AIRPORT CODE: {$dest}"], [], true);
        }

        // Date calculation
        $day = 15;
        $monthStr = 'NOV';
        $dayName = 'FR';

        if ($dateStr !== '') {
            if (! preg_match('/^(\d{1,2})([A-Z]{3})$/', $dateStr, $dMatch)) {
                return new AvailabilityResult('', ["INVALID DATE FORMAT: {$dateStr}. EXPECTED FORMAT: DDMMM (E.G. 15NOV)"], [], true);
            }
            $day = (int) $dMatch[1];
            $monthStr = $dMatch[2];
            if (! isset(self::MONTH_MAP[$monthStr]) || $day < 1 || $day > 31) {
                return new AvailabilityResult('', ["INVALID DATE: {$dateStr}"], [], true);
            }
            $monthNum = self::MONTH_MAP[$monthStr];
            $year = (int) date('Y');
            $ts = mktime(0, 0, 0, $monthNum, $day, $year);
            $dayName = self::DAY_NAMES[(int) date('w', $ts)];
        } else {
            $day = (int) date('j');
            $monthStr = strtoupper(date('M'));
            $dayName = self::DAY_NAMES[(int) date('w')];
        }

        $formattedDate = str_pad((string) $day, 2, '0', STR_PAD_LEFT).$monthStr;
        $headerLine = "{$formattedDate}-{$dayName}  {$orig}{$dest}                                                        ";

        // Carrier selection
        $carriersToUse = [];
        if ($carrierFilter && Airline::find($carrierFilter)) {
            $carriersToUse[] = $carrierFilter;
        } else {
            foreach (self::AIRLINE_HUBS as $carrier => $hubs) {
                if (in_array($orig, $hubs, true) || in_array($dest, $hubs, true)) {
                    $carriersToUse[] = $carrier;
                }
            }
            if (empty($carriersToUse)) {
                $carriersToUse = ['BA', 'QR', 'EK', 'LH', 'AF', 'UA', 'AA', 'SQ'];
            }
        }

        $times = [
            ['dep' => '0630', 'arr' => '1000', 'eq' => '320'],
            ['dep' => '0845', 'arr' => '1215', 'eq' => '77W'],
            ['dep' => '1120', 'arr' => '1450', 'eq' => '359'],
            ['dep' => '1410', 'arr' => '1740', 'eq' => '789'],
            ['dep' => '1655', 'arr' => '2025', 'eq' => '388'],
            ['dep' => '1930', 'arr' => '2300', 'eq' => '77W'],
            ['dep' => '2215', 'arr' => '0630', 'eq' => '35K', 'offset' => '+1'],
        ];

        $schedules = [];
        $outputLines = [];
        $seed = (ord($orig[0]) + ord($dest[0]) + $day) % 99;
        $lineNum = 1;

        $actualOrig = isset(DecodeService::METRO_CODES[$orig]) ? DecodeService::METRO_CODES[$orig][0] : $orig;
        $actualDest = isset(DecodeService::METRO_CODES[$dest]) ? DecodeService::METRO_CODES[$dest][0] : $dest;

        for ($i = 0; $i < count($times); $i++) {
            $carrier = $carriersToUse[$i % count($carriersToUse)];
            $flightNumber = (string) ((($seed * 37 + $i * 43) % 899) + 101);
            $t = $times[$i];

            $classes = [
                ['classCode' => 'F', 'availableSeats' => 4, 'cabin' => 'FIRST'],
                ['classCode' => 'A', 'availableSeats' => 2, 'cabin' => 'FIRST'],
                ['classCode' => 'J', 'availableSeats' => 9, 'cabin' => 'BUSINESS'],
                ['classCode' => 'C', 'availableSeats' => 9, 'cabin' => 'BUSINESS'],
                ['classCode' => 'D', 'availableSeats' => 7, 'cabin' => 'BUSINESS'],
                ['classCode' => 'I', 'availableSeats' => 4, 'cabin' => 'BUSINESS'],
                ['classCode' => 'W', 'availableSeats' => 9, 'cabin' => 'PREMIUM_ECONOMY'],
                ['classCode' => 'E', 'availableSeats' => 6, 'cabin' => 'PREMIUM_ECONOMY'],
                ['classCode' => 'Y', 'availableSeats' => 9, 'cabin' => 'ECONOMY'],
                ['classCode' => 'B', 'availableSeats' => 9, 'cabin' => 'ECONOMY'],
                ['classCode' => 'M', 'availableSeats' => 9, 'cabin' => 'ECONOMY'],
                ['classCode' => 'H', 'availableSeats' => 7, 'cabin' => 'ECONOMY'],
                ['classCode' => 'K', 'availableSeats' => 5, 'cabin' => 'ECONOMY'],
                ['classCode' => 'L', 'availableSeats' => 4, 'cabin' => 'ECONOMY'],
                ['classCode' => 'V', 'availableSeats' => 2, 'cabin' => 'ECONOMY'],
            ];

            $scheduleItem = [
                'airlineCode' => $carrier,
                'flightNumber' => $flightNumber,
                'departureAirport' => $actualOrig,
                'arrivalAirport' => $actualDest,
                'daysOfOperation' => '1234567',
                'departureTime' => $t['dep'],
                'arrivalTime' => $t['arr'],
                'arrivalDayOffset' => $t['offset'] ?? null,
                'equipment' => $t['eq'],
                'stops' => 0,
                'classes' => $classes,
            ];

            $schedules[] = [
                'lineNumber' => $lineNum,
                'schedule' => $scheduleItem,
                'departureDate' => $formattedDate,
            ];

            $linePrefix = str_pad((string) $lineNum, 2, ' ', STR_PAD_LEFT);
            $carrierFlt = $carrier.' '.str_pad($flightNumber, 3, ' ', STR_PAD_LEFT);
            $topClasses = 'J9 C9 D9 I9 W9 Y9 B9';
            $bottomClasses = 'M9 H9 K9 L9 V9 Q9 S9';
            $route = "/{$actualOrig} {$actualDest}";
            $offset = isset($t['offset']) ? $t['offset'] : '  ';
            $timesStr = "{$t['dep']}   {$t['arr']}{$offset}";
            $tailStr = "{$t['eq']} 0     /E";

            $l1 = " {$linePrefix}   {$carrierFlt}  {$topClasses} {$route}  {$timesStr}  {$tailStr}";
            $l2 = "             {$bottomClasses}";

            $outputLines[] = $l1;
            $outputLines[] = $l2;

            $lineNum++;
            if ($lineNum > 6) {
                break;
            }
        }

        return new AvailabilityResult($headerLine, $outputLines, $schedules);
    }
}
