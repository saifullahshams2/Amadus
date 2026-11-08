<?php

namespace App\Services\Amadeus;

use App\Models\Airline;
use App\Models\Airport;

class FlightInfoResult
{
    /**
     * @param  array<int, string>  $lines
     */
    public function __construct(
        public array $lines,
        public bool $isError = false
    ) {}
}

class FlightInfoService
{
    /**
     * Handle DO (Flight Information) command.
     */
    public function handleDO(string $input, array $state): FlightInfoResult
    {
        $clean = strtoupper(trim($input));
        $rest = trim(substr($clean, 2));

        if ($rest === '' || $clean === 'HE DO') {
            return new FlightInfoResult([
                '--- FLIGHT INFORMATION (DO) ---',
                'BY FLIGHT DETAILS : DO <AIR><FLT> /<DATE> /<PAIR> (E.G. DO TG774 /23DEC /BKKLAX)',
                'BY PNR SEGMENT    : DO <SEGMENT_NUMBER> (E.G. DO 2)',
                'BY DISPLAY LINE   : DO <LINE_NUMBER> (E.G. DO 21)',
            ]);
        }

        // 1. Numeric reference: DO 2 or DO 21
        if (is_numeric($rest)) {
            $num = (int) $rest;

            // Check if referring to an active PNR segment
            if (! empty($state['activePnr']['segments'])) {
                foreach ($state['activePnr']['segments'] as $seg) {
                    if (($seg['segmentNumber'] ?? 0) === $num) {
                        return $this->formatFlightInfo(
                            $seg['airlineCode'],
                            $seg['flightNumber'] ?: '101',
                            $seg['departureDate'] ?: 'TODAY',
                            $seg['departureAirport'],
                            $seg['arrivalAirport'],
                            $seg['departureTime'] ?: '1000',
                            $seg['arrivalTime'] ?: '1400',
                            $seg['equipment'] ?? '77W'
                        );
                    }
                }
            }

            // Check if referring to availability display line
            if (! empty($state['lastAvailabilityDisplay']['lines'])) {
                foreach ($state['lastAvailabilityDisplay']['lines'] as $line) {
                    if (($line['lineNumber'] ?? 0) === $num) {
                        return $this->formatFlightInfo(
                            $line['carrier'] ?? 'BA',
                            $line['flightNumber'] ?? '117',
                            $line['date'] ?? '15DEC',
                            $line['origin'] ?? 'LHR',
                            $line['destination'] ?? 'JFK',
                            $line['depTime'] ?? '0915',
                            $line['arrTime'] ?? '1230',
                            $line['aircraft'] ?? '77W'
                        );
                    }
                }
            }

            // Fallback for standalone line reference
            return $this->formatFlightInfo('BA', (string) (100 + $num), '15DEC', 'LHR', 'JFK', '1100', '1430', '77W');
        }

        // 2. Full flight info: DO TG774 /23DEC /BKKLAX or DOTG774/23DEC/BKKLAX
        $cleanNormalized = str_replace(' ', '', $rest);
        if (preg_match('/^([A-Z0-9]{2})(\d{1,4})(?:\/(\d{1,2}[A-Z]{3}))?(?:\/([A-Z]{6}))?$/', $cleanNormalized, $m)) {
            $carrier = $m[1];
            $flt = $m[2];
            $date = ! empty($m[3]) ? $m[3] : strtoupper(date('dM'));
            $pair = ! empty($m[4]) ? $m[4] : 'DELLON';
            $orig = substr($pair, 0, 3);
            $dest = substr($pair, 3, 3);

            return $this->formatFlightInfo($carrier, $flt, $date, $orig, $dest);
        }

        return new FlightInfoResult(["DO: INVALID FLIGHT INFO FORMAT: \"{$rest}\""], true);
    }

    private function formatFlightInfo(
        string $carrier,
        string $flt,
        string $date,
        string $orig,
        string $dest,
        string $dep = '1030',
        string $arr = '1545',
        string $equip = '77W'
    ): FlightInfoResult {
        $al = Airline::find($carrier);
        $alName = $al ? strtoupper($al->name) : "CARRIER {$carrier}";
        $fltPadded = str_pad($flt, 4, '0', STR_PAD_LEFT);

        return new FlightInfoResult([
            "--- FLIGHT OPERATIONAL INFORMATION: {$carrier} {$fltPadded} / {$date} ---",
            "CARRIER       : {$carrier} - {$alName}",
            "ROUTING       : {$orig} -> {$dest} (NON-STOP SCHEDULED)",
            "TIMINGS       : DEPARTURE {$dep} LOCAL / ARRIVAL {$arr} LOCAL",
            "EQUIPMENT     : BOEING {$equip} / JET AIRCRAFT",
            'MEAL SERVICE  : COMPLIMENTARY HOT MEAL & BEVERAGE SERVICE',
            'TERMINAL INFO : DEPARTURE TERMINAL 3 / ARRIVAL TERMINAL 2',
            'FLIGHT STATUS : ON SCHEDULE - ON-TIME PERFORMANCE 94%',
            'ELECTRONIC TKT: ETKT ELIGIBLE (INTERLINE PARTICIPATING)',
        ]);
    }

    /**
     * Handle DM (Minimum Connecting Time) and DMI commands.
     */
    public function handleDM(string $input, array $state): FlightInfoResult
    {
        $clean = strtoupper(trim($input));

        // DMI: Check MCT and segment continuity in current active PNR
        if ($clean === 'DMI') {
            $segments = $state['activePnr']['segments'] ?? [];
            if (empty($segments)) {
                return new FlightInfoResult(['DMI: NO ACTIVE ITINERARY SEGMENTS TO VALIDATE MCT'], true);
            }
            if (count($segments) === 1) {
                return new FlightInfoResult([
                    '--- DMI: MCT & CONTINUITY AUDIT ---',
                    'SEGMENT 1: POINT-TO-POINT FLIGHT (NO TRANSFER REQUIRED)',
                    'AUDIT RESULT: ITINERARY CONTINUITY VALID - NO MCT VIOLATIONS DETECTED',
                ]);
            }

            $lines = [
                '--- DMI: MCT & CONTINUITY AUDIT FOR ACTIVE PNR ---',
            ];
            $hasMctIssue = false;
            for ($i = 0; $i < count($segments) - 1; $i++) {
                $s1 = $segments[$i];
                $s2 = $segments[$i + 1];
                $connectPoint = $s1['arrivalAirport'] ?? '---';
                $nextOrig = $s2['departureAirport'] ?? '---';

                if ($connectPoint === $nextOrig) {
                    $lines[] = "CONNECT AT {$connectPoint}: {$s1['airlineCode']}{$s1['flightNumber']} -> {$s2['airlineCode']}{$s2['flightNumber']} (VALID MCT 60M OK)";
                } else {
                    $lines[] = "SURFACE/DISCONTINUITY: {$s1['arrivalAirport']} TO {$s2['departureAirport']} (REQUIRE ARNK SEGMENT)";
                    $hasMctIssue = true;
                }
            }
            $lines[] = $hasMctIssue ? 'WARNING: ITINERARY CONTAINS SURFACE BREAK' : 'AUDIT RESULT: ALL MINIMUM CONNECTING TIMES SATISFIED (HK)';

            return new FlightInfoResult($lines);
        }

        $rest = trim(substr($clean, 2));
        if ($rest === '' || $clean === 'HE DM') {
            return new FlightInfoResult([
                '--- MINIMUM CONNECTING TIME (DM) ---',
                'BY AIRPORT CODE          : DM <AIRPORT> (E.G. DM FRA)',
                'INTER-TERMINAL / AIRPORT : DM <APT1>-<APT2> (E.G. DM ORYW-CDG2B)',
                'BY CARRIER AT AIRPORT    : DM <AIR1>/ <APT> /<AIR2> (E.G. DM BA/ LHR /AA)',
                'BY DISPLAY LINE          : DM <LINE> (E.G. DM 1)',
                'BY PNR SEGMENTS          : DM <SEG1>/<SEG2> (E.G. DM 2/4)',
                'PNR CONTINUITY AUDIT     : DMI (CHECKS MCT IN ACTIVE PNR)',
            ]);
        }

        // DM FRA
        if (preg_match('/^[A-Z]{3}$/', $rest)) {
            $ap = Airport::find($rest);
            $name = $ap ? strtoupper($ap->name) : $rest;

            return new FlightInfoResult([
                "--- MINIMUM CONNECTING TIMES AT: {$rest} ({$name}) ---",
                'DOMESTIC TO DOMESTIC (D-D)       : 0045 (45 MINS)',
                'DOMESTIC TO INTERNATIONAL (D-I)  : 0060 (60 MINS)',
                'INTERNATIONAL TO DOMESTIC (I-D)  : 0075 (75 MINS)',
                'INTERNATIONAL TO INT (I-I)       : 0060 (60 MINS)',
                'STANDARD CONNECTIVITY APPLIES FOR ALL ONLINE/INTERLINE TRANSFERS',
            ]);
        }

        // Inter-airport connection: DM ORYW-CDG2B or DM BA/ LHR-LGW /AA
        if (str_contains($rest, '-')) {
            return new FlightInfoResult([
                "--- INTER-AIRPORT TRANSFER MCT: {$rest} ---",
                'GROUND TRANSPORTATION REQUIRED BETWEEN TERMINALS/AIRPORTS',
                'MINIMUM CONNECTING TIME : 0300 (3 HOURS)',
                'BAGGAGE RE-CHECK        : PASSENGER MUST RECLAIM AND RE-CHECK BAGGAGE',
                'CUSTOMS CLEARANCE       : MANDATORY PASSPORT & BORDER ENTRY REQUIRED',
            ]);
        }

        // Carrier-specific: DM BA/ LHR /AA or DM 2/4
        if (str_contains($rest, '/')) {
            $parts = explode('/', $rest);
            if (count($parts) >= 2 && is_numeric(trim($parts[0])) && is_numeric(trim($parts[1]))) {
                return new FlightInfoResult([
                    "--- MCT AUDIT FOR PNR SEGMENTS {$parts[0]} & {$parts[1]} ---",
                    'TRANSIT TIME ALLOTTED : 02H 15M',
                    'REQUIRED MCT          : 01H 00M',
                    'STATUS                : OK - MEETS MINIMUM CONNECTING TIME REQUIREMENT',
                ]);
            }

            return new FlightInfoResult([
                "--- CARRIER CONNECTING TIME (DM {$rest}) ---",
                'ONLINE CONNECTION (SAME CARRIER)       : 0045 (45 MINS)',
                'INTERLINE CONNECTION (PARTNER CARRIER) : 0060 (60 MINS)',
                'STATUS : VALID MCT CONFIRMED UNDER BSP INTERLINE AGREEMENT',
            ]);
        }

        if (is_numeric($rest)) {
            return new FlightInfoResult([
                "--- MCT FOR DISPLAY LINE {$rest} ---",
                'DEPARTURE / ARRIVAL SECTOR MEETS MCT STANDARD (0060)',
            ]);
        }

        return new FlightInfoResult(["DM: UNRECOGNIZED MCT QUERY: \"{$rest}\""], true);
    }

    /**
     * Handle DRT (Display Connection Points).
     */
    public function handleDRT(string $input): FlightInfoResult
    {
        $clean = strtoupper(trim($input));
        $rest = trim(substr($clean, 3));

        if ($rest === '' || $clean === 'HE DRT') {
            return new FlightInfoResult([
                '--- DISPLAY CONNECTION POINTS (DRT) ---',
                'NEUTRAL CONNECT POINTS    : DRT <ORIG> <DEST> (E.G. DRT DEL BJS)',
                'DATE AND AIRLINE          : DRT <DATE> <PAIR> / A<AL> (E.G. DRT 19AUG DELBJS / ATG)',
                'EXCLUDE AIRLINE           : DRT <DATE> <PAIR> / A-<AL> (E.G. DRT 19AUG DELBJS / A-TG)',
                'SPECIFIC TRANSIT POINT    : DRT <DATE> <PAIR> / X<VIA> (E.G. DRT 19AUG DEL BJS / XHKG)',
                'CARRIER PREFERRED POINTS  : DRT <AL> <DATE> <PAIR> (E.G. DRT QF 15AUG DEL HNL)',
            ]);
        }

        // Parse origin and destination
        $orig = 'DEL';
        $dest = 'BJS';

        if (preg_match('/([A-Z]{3})\s*([A-Z]{3})/', $rest, $pairM)) {
            $orig = $pairM[1];
            $dest = $pairM[2];
        }

        $transitPoints = ['BKK (BANGKOK)', 'HKG (HONG KONG)', 'SIN (SINGAPORE)', 'KUL (KUALA LUMPUR)', 'DOH (DOHA)'];

        if (str_contains($rest, 'XHKG')) {
            $transitPoints = ['HKG (HONG KONG) - CONNECT VIA CATHAY PACIFIC / AIR CHINA'];
        }

        $lines = [
            "--- CONNECTING POINTS FOR: {$orig} TO {$dest} (DRT) ---",
            'PRIMARY ROUTING GATEWAYS:',
        ];
        foreach ($transitPoints as $idx => $pt) {
            $lines[] = '  '.($idx + 1).". {$pt}";
        }
        $lines[] = 'ALL CONNECT POINTS COMPLY WITH IATA MAXIMUM PERMITTED MILEAGE (MPM)';

        return new FlightInfoResult($lines);
    }
}
