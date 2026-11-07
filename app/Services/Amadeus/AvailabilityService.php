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

    public function search(string $query, string $displayType = 'AN'): AvailabilityResult
    {
        $clean = strtoupper(trim($query));

        // Parse modifiers separated by slash (e.g. /AEK, /XAUH, /CV, /1200)
        $slashParts = explode('/', $clean);
        $primary = array_shift($slashParts);

        $carrierFilter = null;
        $viaFilter = null;
        $classFilter = null;
        $timeFilter = null;

        foreach ($slashParts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }

            if (str_starts_with($part, 'A')) {
                $carrierFilter = substr($part, 1);
            } elseif (str_starts_with($part, 'X')) {
                $viaFilter = substr($part, 1);
            } elseif (str_starts_with($part, 'C')) {
                $classFilter = substr($part, 1);
            } elseif (preg_match('/^\d{4}$/', $part)) {
                $timeFilter = $part;
            }
        }

        // Regex for: [DATE: 1-2 digits + 3 letters][ORIG: 3 chars][DEST: 3 chars][optional TIME: 4 digits][optional *RETURN_DATE: *DDMMM]
        // or [DATE][ORIG][DEST][optional *RETURN_DATE][optional TIME]
        $pattern1 = '/^(\d{1,2}[A-Z]{3})?([A-Z]{3})([A-Z]{3})(\d{4})?(?:\*(\d{1,2}[A-Z]{3}))?$/';
        $pattern2 = '/^(\d{1,2}[A-Z]{3})?([A-Z]{3})([A-Z]{3})(?:\*(\d{1,2}[A-Z]{3}))?(\d{4})?$/';

        if (preg_match($pattern1, $primary, $matches) && ! empty($matches[2])) {
            $dateStr = $matches[1] ?? '';
            $orig = $matches[2];
            $dest = $matches[3];
            $timeInPrimary = ! empty($matches[4]) ? $matches[4] : null;
            $returnDateStr = ! empty($matches[5]) ? $matches[5] : null;
        } elseif (preg_match($pattern2, $primary, $matches) && ! empty($matches[2])) {
            $dateStr = $matches[1] ?? '';
            $orig = $matches[2];
            $dest = $matches[3];
            $returnDateStr = ! empty($matches[4]) ? $matches[4] : null;
            $timeInPrimary = ! empty($matches[5]) ? $matches[5] : null;
        } else {
            return new AvailabilityResult(
                '',
                ['FORMAT: AN[DATE]<ORIGIN><DESTINATION>[TIME][*RETURNDATE][/A<AIRLINE>][/X<VIA>][/C<CLASS>]'],
                [],
                true
            );
        }

        if ($timeInPrimary) {
            $timeFilter = $timeInPrimary;
        }

        $validOrig = Airport::find($orig) ?? (isset(DecodeService::METRO_CODES[$orig]));
        $validDest = Airport::find($dest) ?? (isset(DecodeService::METRO_CODES[$dest]));

        if (! $validOrig) {
            return new AvailabilityResult('', ["INVALID ORIGIN CITY/AIRPORT CODE: {$orig}"], [], true);
        }
        if (! $validDest) {
            return new AvailabilityResult('', ["INVALID DESTINATION CITY/AIRPORT CODE: {$dest}"], [], true);
        }

        // Outbound date calculation
        $outboundCalc = $this->calculateDate($dateStr);
        if ($outboundCalc['isError']) {
            return new AvailabilityResult('', [$outboundCalc['errorMessage']], [], true);
        }
        $formattedDate = $outboundCalc['formattedDate'];
        $dayName = $outboundCalc['dayName'];
        $day = $outboundCalc['day'];

        $prefixTitle = ($displayType === 'SN') ? 'SN ' : '';
        $timeTitle = $timeFilter ? "  {$timeFilter}" : '';
        $headerLine = "{$prefixTitle}{$formattedDate}-{$dayName}  {$orig}{$dest}{$timeTitle}                                                        ";

        // Generate Outbound Flights (lines 1..6)
        $outboundResult = $this->generateScheduleLines(
            orig: $orig,
            dest: $dest,
            formattedDate: $formattedDate,
            day: $day,
            startLineNum: 1,
            maxLines: 6,
            carrierFilter: $carrierFilter,
            viaFilter: $viaFilter,
            classFilter: $classFilter,
            timeFilter: $timeFilter
        );

        $allOutputLines = $outboundResult['outputLines'];
        $allSchedules = $outboundResult['schedules'];

        // If return date specified (e.g. AN12MARDOHDXB*15MAR)
        if ($returnDateStr) {
            $inboundCalc = $this->calculateDate($returnDateStr);
            if (! $inboundCalc['isError']) {
                $retFormattedDate = $inboundCalc['formattedDate'];
                $retDayName = $inboundCalc['dayName'];
                $retDay = $inboundCalc['day'];

                $allOutputLines[] = '';
                $allOutputLines[] = "{$prefixTitle}{$retFormattedDate}-{$retDayName}  {$dest}{$orig}                                                        ";

                $inboundResult = $this->generateScheduleLines(
                    orig: $dest,
                    dest: $orig,
                    formattedDate: $retFormattedDate,
                    day: $retDay,
                    startLineNum: 11,
                    maxLines: 6,
                    carrierFilter: $carrierFilter,
                    viaFilter: $viaFilter,
                    classFilter: $classFilter,
                    timeFilter: $timeFilter
                );

                $allOutputLines = array_merge($allOutputLines, $inboundResult['outputLines']);
                $allSchedules = array_merge($allSchedules, $inboundResult['schedules']);
            }
        }

        return new AvailabilityResult($headerLine, $allOutputLines, $allSchedules);
    }

    private function calculateDate(string $dateStr): array
    {
        if ($dateStr !== '') {
            if (! preg_match('/^(\d{1,2})([A-Z]{3})$/', $dateStr, $dMatch)) {
                return ['isError' => true, 'errorMessage' => "INVALID DATE FORMAT: {$dateStr}. EXPECTED FORMAT: DDMMM (E.G. 15NOV)"];
            }
            $day = (int) $dMatch[1];
            $monthStr = $dMatch[2];
            if (! isset(self::MONTH_MAP[$monthStr]) || $day < 1 || $day > 31) {
                return ['isError' => true, 'errorMessage' => "INVALID DATE: {$dateStr}"];
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

        return [
            'isError' => false,
            'day' => $day,
            'monthStr' => $monthStr,
            'dayName' => $dayName,
            'formattedDate' => $formattedDate,
        ];
    }

    private function generateScheduleLines(
        string $orig,
        string $dest,
        string $formattedDate,
        int $day,
        int $startLineNum,
        int $maxLines,
        ?string $carrierFilter,
        ?string $viaFilter,
        ?string $classFilter,
        ?string $timeFilter
    ): array {
        // Carrier selection
        $carriersToUse = [];
        if ($carrierFilter && Airline::find($carrierFilter)) {
            $carriersToUse[] = $carrierFilter;
        } elseif ($carrierFilter) {
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

        // If timeFilter is set (e.g. 1200), rotate times so departure >= timeFilter comes first
        if ($timeFilter) {
            $filterInt = (int) $timeFilter;
            $bestIdx = 0;
            $smallestDiff = 99999;
            foreach ($times as $idx => $t) {
                $depInt = (int) $t['dep'];
                $diff = ($depInt >= $filterInt) ? ($depInt - $filterInt) : ($depInt + 2400 - $filterInt);
                if ($diff < $smallestDiff) {
                    $smallestDiff = $diff;
                    $bestIdx = $idx;
                }
            }
            $times = array_merge(array_slice($times, $bestIdx), array_slice($times, 0, $bestIdx));
        }

        $schedules = [];
        $outputLines = [];
        $seed = (ord($orig[0]) + ord($dest[0]) + $day) % 99;
        $lineNum = $startLineNum;

        $actualOrig = isset(DecodeService::METRO_CODES[$orig]) ? DecodeService::METRO_CODES[$orig][0] : $orig;
        $actualDest = isset(DecodeService::METRO_CODES[$dest]) ? DecodeService::METRO_CODES[$dest][0] : $dest;

        $stops = $viaFilter ? 1 : 0;
        $route = $viaFilter ? "/{$actualOrig} {$viaFilter} {$actualDest}" : "/{$actualOrig} {$actualDest}";

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
                ['classCode' => 'V', 'availableSeats' => 4, 'cabin' => 'ECONOMY'],
            ];

            // If classFilter is requested, ensure it is available in class array
            if ($classFilter) {
                $hasReqClass = false;
                foreach ($classes as &$cls) {
                    if ($cls['classCode'] === $classFilter) {
                        $cls['availableSeats'] = 9;
                        $hasReqClass = true;
                        break;
                    }
                }
                if (! $hasReqClass) {
                    $classes[] = ['classCode' => $classFilter, 'availableSeats' => 9, 'cabin' => 'ECONOMY'];
                }
            }

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
                'stops' => $stops,
                'classes' => $classes,
            ];

            $schedules[] = [
                'lineNumber' => $lineNum,
                'schedule' => $scheduleItem,
                'departureDate' => $formattedDate,
            ];

            $linePrefix = str_pad((string) $lineNum, 2, ' ', STR_PAD_LEFT);
            $carrierFlt = $carrier.' '.str_pad($flightNumber, 3, ' ', STR_PAD_LEFT);

            if ($classFilter) {
                $topClasses = "{$classFilter}9 J9 C9 D9 I9 W9 Y9";
                $bottomClasses = 'B9 M9 H9 K9 L9 V9 Q9 S9';
            } else {
                $topClasses = 'J9 C9 D9 I9 W9 Y9 B9';
                $bottomClasses = 'M9 H9 K9 L9 V9 Q9 S9';
            }

            $offset = isset($t['offset']) ? $t['offset'] : '  ';
            $timesStr = "{$t['dep']}   {$t['arr']}{$offset}";
            $tailStr = "{$t['eq']} {$stops}     /E";

            $l1 = " {$linePrefix}   {$carrierFlt}  {$topClasses} {$route}  {$timesStr}  {$tailStr}";
            $l2 = "             {$bottomClasses}";

            $outputLines[] = $l1;
            $outputLines[] = $l2;

            $lineNum++;
            if (($lineNum - $startLineNum) >= $maxLines) {
                break;
            }
        }

        return [
            'outputLines' => $outputLines,
            'schedules' => $schedules,
        ];
    }
}
