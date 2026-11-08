<?php

namespace App\Services\Amadeus;

use App\Models\Airline;
use App\Models\Airport;

class AvailabilityResult
{
    /**
     * @param  array<int, string>  $outputLines
     * @param  array<int, array>  $schedules
     */
    public function __construct(
        public string $headerLine,
        public array $outputLines,
        public array $schedules,
        public bool $isError = false,
        public ?string $origin = null,
        public ?string $destination = null,
        public ?string $date = null,
        public ?string $returnDate = null,
        public ?string $carrier = null,
        public ?string $via = null,
        public ?string $class = null,
        public ?string $time = null
    ) {}
}

class AvailabilityService
{
    private const MONTH_MAP = [
        'JAN' => 1, 'FEB' => 2, 'MAR' => 3, 'APR' => 4, 'MAY' => 5, 'JUN' => 6,
        'JUL' => 7, 'AUG' => 8, 'SEP' => 9, 'OCT' => 10, 'NOV' => 11, 'DEC' => 12,
    ];

    private const DAY_NAMES = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'];

    public const ALLIANCES = [
        '*A' => ['LH', 'SQ', 'TG', 'UA', 'AC', 'TK', 'AI', 'OS', 'LX'],
        '*O' => ['BA', 'AA', 'QR', 'CX', 'JL', 'QF', 'AY', 'IB', 'MH'],
        '*S' => ['AF', 'KL', 'DL', 'KE', 'SV', 'AR', 'UX', 'CI', 'VN'],
    ];

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
        '9W' => ['DEL', 'BOM'],
        'AK' => ['KUL', 'TRZ'],
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

        // Strip leading slash if 7-day search (e.g. AN/17DECBOMNBO/AKQ)
        $isSevenDay = false;
        if (str_starts_with($clean, '/')) {
            $isSevenDay = true;
            $clean = ltrim($clean, '/');
        }

        // Parse modifiers separated by slash (e.g. /AEK, /ASQ,AI,TG, /A-AI,IT, /XAUH, /CV, /KY, /B4, /O)
        $slashParts = explode('/', $clean);
        $primary = array_shift($slashParts);

        $carrierFilter = null;
        $carriersList = [];
        $excludedCarriers = [];
        $viaFilter = null;
        $classFilter = null;
        $seatCount = 1;
        $timeFilter = null;
        $onlineOnly = false;

        foreach ($slashParts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }

            if (str_starts_with($part, 'A-')) {
                // Excluded carriers: /A-AI,IT
                $excludedCarriers = explode(',', substr($part, 2));
            } elseif (str_starts_with($part, 'A')) {
                // Carrier filter: /AEK or /ASQ,AI,TG or /AYY
                $cList = explode(',', substr($part, 1));
                if (count($cList) > 1) {
                    $carriersList = $cList;
                } else {
                    $carrierFilter = $cList[0];
                }
            } elseif (str_starts_with($part, 'X-')) {
                // Exclude connecting point: /X-FRA
                // Handled implicitly by avoiding via
            } elseif (str_starts_with($part, 'X')) {
                // Transit point(s): /XBKK or /XBKKLAX
                $viaFilter = substr($part, 1);
            } elseif (str_starts_with($part, 'C') || str_starts_with($part, 'K')) {
                // Class/RBD filter: /CM or /KY
                $classFilter = substr($part, 1, 1);
            } elseif (str_starts_with($part, 'B')) {
                // Seats required: /B4
                $seatCount = (int) substr($part, 1);
            } elseif ($part === 'O') {
                $onlineOnly = true;
            } elseif (preg_match('/^\d{4}$/', $part)) {
                $timeFilter = $part;
            }
        }

        // Check for carrier-preferred display (e.g. ANTG15SEPDELBKK -> carrier TG)
        if (preg_match('/^([A-Z0-9]{2})(\d{1,2}[A-Z]{3})([A-Z]{3})([A-Z]{3})/', $primary, $prefM) && Airline::find($prefM[1])) {
            $carrierFilter = $prefM[1];
            $primary = substr($primary, 2);
        }

        // Check for alliance display (e.g. AN*A15SEPDELLON -> alliance *A)
        if (preg_match('/^(\*[AOS])(\d{1,2}[A-Z]{3})([A-Z]{3})([A-Z]{3})/', $primary, $allM)) {
            $allianceCode = $allM[1];
            if (isset(self::ALLIANCES[$allianceCode])) {
                $carriersList = self::ALLIANCES[$allianceCode];
            }
            $primary = substr($primary, 2);
        }

        // Regex for primary:
        // [DATE: 1-2 digits + 3 letters][ORIG: 3 chars][DEST: 3 chars][optional TIME: 4 digits][optional *RETURN_DATE or *ONWARD_PAIR]
        // or [DATE][ORIG][DEST][optional *RETURN][optional TIME]
        $pattern1 = '/^(\d{1,2}[A-Z]{3})?([A-Z]{3})([A-Z]{3})(\d{4})?(?:\*([0-9A-Z\/]+))?$/';
        $pattern2 = '/^(\d{1,2}[A-Z]{3})?([A-Z]{3})([A-Z]{3})(?:\*([0-9A-Z\/]+))?(\d{4})?$/';

        if (preg_match($pattern1, $primary, $matches) && ! empty($matches[2])) {
            $dateStr = $matches[1] ?? '';
            $orig = $matches[2];
            $dest = $matches[3];
            $timeInPrimary = ! empty($matches[4]) ? $matches[4] : null;
            $returnSpec = ! empty($matches[5]) ? $matches[5] : null;
        } elseif (preg_match($pattern2, $primary, $matches) && ! empty($matches[2])) {
            $dateStr = $matches[1] ?? '';
            $orig = $matches[2];
            $dest = $matches[3];
            $returnSpec = ! empty($matches[4]) ? $matches[4] : null;
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

        $prefixTitle = match ($displayType) {
            'SN' => 'SN ',
            'AA' => 'AA ',
            default => '',
        };
        $sevenDayTitle = $isSevenDay ? ' [7-DAY WINDOW]' : '';
        $timeTitle = $timeFilter ? "  {$timeFilter}" : '';
        $headerLine = "{$prefixTitle}{$formattedDate}-{$dayName}  {$orig}{$dest}{$timeTitle}{$sevenDayTitle}                                                ";

        // Determine carrier pool
        $carrierPool = [];
        if (! empty($carriersList)) {
            $carrierPool = $carriersList;
        } elseif ($carrierFilter && $carrierFilter !== 'YY') {
            $carrierPool = [$carrierFilter];
        }

        // Generate Outbound Flights (lines 1..6)
        $outboundResult = $this->generateScheduleLines(
            orig: $orig,
            dest: $dest,
            formattedDate: $formattedDate,
            day: $day,
            startLineNum: 1,
            maxLines: 6,
            carrierFilter: $carrierFilter,
            carriersList: $carrierPool,
            excludedCarriers: $excludedCarriers,
            viaFilter: $viaFilter,
            classFilter: $classFilter,
            timeFilter: $timeFilter,
            seatCount: $seatCount,
            isArrivalSort: ($displayType === 'AA')
        );

        $allOutputLines = $outboundResult['outputLines'];
        $allSchedules = $outboundResult['schedules'];

        // Dual City Pair / Return Sector (e.g. *16DEC, *25DECLONNYC/AAA, or *)
        $retDate = null;
        if ($returnSpec !== null) {
            // Default return is same day if just *
            $retDateStr = $dateStr;
            $retOrig = $dest;
            $retDest = $orig;
            $retCarrier = $carrierFilter;

            if ($returnSpec !== '') {
                if (preg_match('/^(\d{1,2}[A-Z]{3})([A-Z]{3})([A-Z]{3})(?:\/A([A-Z0-9]{2}))?$/', $returnSpec, $retM)) {
                    $retDateStr = $retM[1];
                    $retOrig = $retM[2];
                    $retDest = $retM[3];
                    $retCarrier = $retM[4] ?? $carrierFilter;
                } elseif (preg_match('/^(\d{1,2}[A-Z]{3})$/', $returnSpec, $retM)) {
                    $retDateStr = $retM[1];
                }
            }

            $inboundCalc = $this->calculateDate($retDateStr);
            if (! $inboundCalc['isError']) {
                $retFormattedDate = $inboundCalc['formattedDate'];
                $retDayName = $inboundCalc['dayName'];
                $retDay = $inboundCalc['day'];
                $retDate = $retFormattedDate;

                $allOutputLines[] = '';
                $allOutputLines[] = "{$prefixTitle}{$retFormattedDate}-{$retDayName}  {$retOrig}{$retDest}                                                        ";

                $inboundResult = $this->generateScheduleLines(
                    orig: $retOrig,
                    dest: $retDest,
                    formattedDate: $retFormattedDate,
                    day: $retDay,
                    startLineNum: 11,
                    maxLines: 6,
                    carrierFilter: $retCarrier,
                    carriersList: $carrierPool,
                    excludedCarriers: $excludedCarriers,
                    viaFilter: $viaFilter,
                    classFilter: $classFilter,
                    timeFilter: $timeFilter,
                    seatCount: $seatCount,
                    isArrivalSort: ($displayType === 'AA')
                );

                $allOutputLines = array_merge($allOutputLines, $inboundResult['outputLines']);
                $allSchedules = array_merge($allSchedules, $inboundResult['schedules']);
            }
        }

        return new AvailabilityResult(
            headerLine: $headerLine,
            outputLines: $allOutputLines,
            schedules: $allSchedules,
            origin: $orig,
            destination: $dest,
            date: $formattedDate,
            returnDate: $retDate,
            carrier: $carrierFilter,
            via: $viaFilter,
            class: $classFilter,
            time: $timeFilter
        );
    }

    /**
     * Apply Availability Change (AC / ACR) entry.
     */
    public function applyChange(string $cmd, ?array $currentDisplay): AvailabilityResult
    {
        $clean = strtoupper(trim($cmd));

        if (! $currentDisplay || empty($currentDisplay['lines'])) {
            return new AvailabilityResult('', ['NO PREVIOUS AVAILABILITY DISPLAY TO MODIFY (AC)'], [], true);
        }

        $orig = $currentDisplay['origin'] ?? 'DEL';
        $dest = $currentDisplay['destination'] ?? 'LON';
        $date = $currentDisplay['date'] ?? strtoupper(date('dM'));
        $retDate = $currentDisplay['returnDate'] ?? null;
        $carrier = $currentDisplay['carrier'] ?? null;

        // 1. ACR<DATE> / ACR<DATE>/A<CARRIER> - Return change
        if (str_starts_with($clean, 'ACR')) {
            $rest = substr($clean, 3);
            $parts = explode('/', $rest);
            $newRetDate = $parts[0] ?: $date;
            $newCarrier = isset($parts[1]) ? ltrim($parts[1], 'A') : $carrier;

            $query = "{$date}{$orig}{$dest}*{$newRetDate}";
            if ($newCarrier) {
                $query .= "/A{$newCarrier}";
            }

            return $this->search($query, 'AN');
        }

        $rest = trim(substr($clean, 2));

        // 2. AC/A<CARRIER> or AC/AYY
        if (str_starts_with($rest, '/A')) {
            $newCarrier = substr($rest, 2);
            $query = "{$date}{$orig}{$dest}";
            if ($retDate) {
                $query .= "*{$retDate}";
            }
            if ($newCarrier !== 'YY') {
                $query .= "/A{$newCarrier}";
            }

            return $this->search($query, 'AN');
        }

        // 3. AC // <DEST> - Destination change
        if (str_starts_with($rest, '//')) {
            $newDest = trim(substr($rest, 2));
            $query = "{$date}{$orig}{$newDest}";
            if ($carrier) {
                $query .= "/A{$carrier}";
            }

            return $this->search($query, 'AN');
        }

        // 4. AC <ORIG> - Origin change (3 chars city)
        if (preg_match('/^[A-Z]{3}$/', $rest)) {
            $newOrig = $rest;
            $query = "{$date}{$newOrig}{$dest}";
            if ($carrier) {
                $query .= "/A{$carrier}";
            }

            return $this->search($query, 'AN');
        }

        // 5. AC <+DAYS> / AC <-DAYS> (e.g. AC 5, AC +5, AC -3)
        if (preg_match('/^([+-]?\d+)$/', $rest, $dayM)) {
            $offset = (int) $dayM[1];
            $calc = $this->calculateDate($date);
            $day = $calc['day'];
            $monStr = $calc['monthStr'];
            $mon = self::MONTH_MAP[$monStr] ?? (int) date('m');
            $ts = mktime(0, 0, 0, $mon, $day, (int) date('Y'));
            $newTs = strtotime("{$offset} days", $ts);
            $newDateStr = strtoupper(date('dM', $newTs));

            $query = "{$newDateStr}{$orig}{$dest}";
            if ($retDate) {
                $query .= "*{$retDate}";
            }
            if ($carrier) {
                $query .= "/A{$carrier}";
            }

            return $this->search($query, 'AN');
        }

        // 6. AC <DATE> (e.g. AC 15DEC)
        if (preg_match('/^(\d{1,2}[A-Z]{3})$/', $rest, $dM)) {
            $newDate = $dM[1];
            $query = "{$newDate}{$orig}{$dest}";
            if ($retDate) {
                $query .= "*{$retDate}";
            }
            if ($carrier) {
                $query .= "/A{$carrier}";
            }

            return $this->search($query, 'AN');
        }

        // Fallback default search
        return $this->search("{$date}{$orig}{$dest}", 'AN');
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
        array $carriersList,
        array $excludedCarriers,
        ?string $viaFilter,
        ?string $classFilter,
        ?string $timeFilter,
        int $seatCount = 1,
        bool $isArrivalSort = false
    ): array {
        // Carrier selection
        $carriersToUse = [];
        if (! empty($carriersList)) {
            $carriersToUse = $carriersList;
        } elseif ($carrierFilter && Airline::find($carrierFilter)) {
            $carriersToUse[] = $carrierFilter;
        } elseif ($carrierFilter && $carrierFilter !== 'YY') {
            $carriersToUse[] = $carrierFilter;
        } else {
            foreach (self::AIRLINE_HUBS as $carrier => $hubs) {
                if (in_array($orig, $hubs, true) || in_array($dest, $hubs, true)) {
                    $carriersToUse[] = $carrier;
                }
            }
            if (empty($carriersToUse)) {
                $carriersToUse = ['BA', 'QR', 'EK', 'LH', 'AF', 'UA', 'AA', 'SQ', 'TG', 'AI'];
            }
        }

        // Apply carrier exclusions (/A-AI,IT)
        if (! empty($excludedCarriers)) {
            $carriersToUse = array_values(array_filter($carriersToUse, fn ($c) => ! in_array($c, $excludedCarriers, true)));
            if (empty($carriersToUse)) {
                $carriersToUse = ['BA', 'LH', 'AF'];
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

        // If timeFilter is set, rotate times so closest time comes first
        if ($timeFilter) {
            $filterInt = (int) $timeFilter;
            $bestIdx = 0;
            $smallestDiff = 99999;
            foreach ($times as $idx => $t) {
                $compareInt = $isArrivalSort ? (int) $t['arr'] : (int) $t['dep'];
                $diff = ($compareInt >= $filterInt) ? ($compareInt - $filterInt) : ($compareInt + 2400 - $filterInt);
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
                        $cls['availableSeats'] = max(9, $seatCount);
                        $hasReqClass = true;
                        break;
                    }
                }
                if (! $hasReqClass) {
                    $classes[] = ['classCode' => $classFilter, 'availableSeats' => max(9, $seatCount), 'cabin' => 'ECONOMY'];
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
