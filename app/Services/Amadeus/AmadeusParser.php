<?php

namespace App\Services\Amadeus;

use App\Models\Pnr;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CommandExecutionResult
{
    public function __construct(
        public array $outputLines,
        public array $updatedState,
        public bool $isError = false,
        public string $commandType = 'GENERAL'
    ) {}
}

class AmadeusParser
{
    public function __construct(
        protected DecodeService $decode,
        protected AvailabilityService $availability,
        protected PnrService $pnr,
        protected PricingService $pricing,
        protected TicketingService $ticketing,
        protected QueueService $queue
    ) {}

    public function execute(string $rawInput, array $currentState, string $userId = 'guest'): CommandExecutionResult
    {
        // Support semicolon-separated command chains (e.g. NM1HUSSAIN/A MR;NM1SYED/ALI MR)
        if (str_contains($rawInput, ';')) {
            $parts = array_filter(array_map('trim', explode(';', $rawInput)), fn ($p) => $p !== '');
            if (count($parts) > 1) {
                $accumulatedLines = [];
                $workingState = $currentState;
                $lastType = 'MULTI_COMMAND';
                $isAnyError = false;

                foreach ($parts as $subCmd) {
                    $res = $this->executeSingle($subCmd, $workingState, $userId);
                    $accumulatedLines = array_merge($accumulatedLines, $res->outputLines);
                    $workingState = $res->updatedState;
                    $lastType = $res->commandType;
                    if ($res->isError) {
                        $isAnyError = true;
                    }
                }

                return new CommandExecutionResult($accumulatedLines, $workingState, $isAnyError, $lastType);
            }
        }

        return $this->executeSingle($rawInput, $currentState, $userId);
    }

    protected function executeSingle(string $rawInput, array $currentState, string $userId = 'guest'): CommandExecutionResult
    {
        $input = strtoupper(trim($rawInput));
        $state = $currentState;

        $user = ($userId !== 'guest') ? User::where('username', $userId)->first() : null;
        $isInstructor = $user?->role === 'instructor';

        // Initialize PNR buffer if not present
        if (empty($state['activePnr'])) {
            $state['activePnr'] = $this->pnr->createEmptyPnr($state['officeId'] ?? 'LON1A0980', $state['agentId'] ?? '1234AB');
        }

        // Initialize arrays inside activePnr if missing
        foreach (['passengers', 'segments', 'contacts', 'tickets', 'tsts', 'specialServiceRequests', 'remarks', 'options', 'seatRequests', 'history'] as $k) {
            if (! isset($state['activePnr'][$k]) || ! is_array($state['activePnr'][$k])) {
                $state['activePnr'][$k] = [];
            }
        }

        // 1. HELP / HE / ?
        if ($input === 'HE' || str_starts_with($input, 'HELP') || $input === '?') {
            return new CommandExecutionResult(
                [
                    '--- AMADUS COMPREHENSIVE COMMAND REFERENCE ---',
                    'SIGN-IN/OUT : JI<SINE>/<DUTY> | JI*<SINE>/<DUTY> | JO | JO*',
                    'AVAILABILITY: AN<DATE><ORIG><DEST>[TIME][/A<AIR>][/X<VIA>][/C<CLS>][*<RET>]',
                    'TIMETABLE   : SN<DATE><ORIG><DEST> (SCHEDULE DISPLAY)',
                    'SELL FLIGHT : SS<SEATS><CLS><LINE> | SS2V1*V13 | SS<AIR><FLT><CLS><DATE><RT>NN<N>',
                    'PASSIVE/DEAD: SS<AIR><FLT><CLS><DATE><RT>GK<N>/<AIR> | GL<N>/<AIR>',
                    'OPEN/SURFACE: SO <AIR> <CLS> <ORIG><DEST> | SIARNK (ARNK SURFACE)',
                    'PASSENGER NM: NM1<LAST>/<FIRST> [TITLE] | NM2<LAST>/<P1>/<P2>',
                    'NAME EDIT   : 2/ (CLR CHD/INF) | 3/(CHD) | 4/ (CLR INF) | 5/(INF/<NM>) | 6/<NM>',
                    'CONTACT     : AP (OFFICE) | AP <PHONE> | APE-<EMAIL>',
                    'TICKETING   : TKTL<DATE>[/<TIME>] | TKOK | TKXL<DATE>[/<TIME>]',
                    'RECEIVED FRM: RF <NAME> | RF PAX | RF P',
                    'RETRIEVE PNR: RT<RLOC> | RT/<NAME> | RT/<A-Z> | RT<LINE> | RL (AIRLINE LOCATORS)',
                    'PNR LIST LM : LM/<AIR><FLT>/<DATE>/<RT> | LM<LINE>',
                    'FREQ FLYER  : FFN<AIR>-<NUM>[/P<N>] | SRFQTV<AIR>-<NUM>[/P<N>]',
                    'SEAT SELECT : SM<SEG> (MAP) | ST/R/<SEAT>[/P<N>][/S<N>] | ST/W/P2 | ST/A/S4',
                    'OSI / SSR   : OS <AIR> <TEXT> | SR <CODE> | SR PSPT... | SROTHS... TKNA...',
                    'CANCEL      : XE<LINE> | XI (CANCEL ENTIRE ITINERARY)',
                    'REBOOK CLS  : SB<CLS> | SB<CLS><SEG> | SB<DATE><SEG>',
                    'HISTORY     : RH (FULL PNR) | RHS<SEG> (SEGMENT) | RLH (AIRLINE LOCATORS)',
                    'REMARKS     : RM <GENERAL TEXT> | RC <CONFIDENTIAL TEXT>',
                    'PRINT       : WRS/RT (SCREEN) | WRA/RT (FULL HARDCOPY)',
                    'OPTION / Q  : OP<DATE>/<TEXT> | QT | QS2C30D1 | QE<Q> | QD | QN | QI',
                    'SPLIT PNR   : SP<PAX> -> RF P -> EF -> RF P -> ET/ER',
                    'MANUAL TKT  : FHA <TKT> | FHM <TKT> | FHE <TKT> | FHA PAX <TKT>/P<N>',
                    'PRICING/ETKT: FXP / FXX / FXB | FP CASH/CC | ET / ER | TTP / TWD',
                ],
                $state,
                false,
                'HELP'
            );
        }

        // 2. SIGN IN / SIGN OUT
        if (str_starts_with($input, 'JI') || $input === 'SI') {
            $state['isSignedIn'] = true;
            $allAreas = str_starts_with($input, 'JI*');
            $rest = ltrim(substr($input, $allAreas ? 3 : 2), '*');

            $agentSine = $state['agentId'] ?? '1234AB';
            $duty = 'SU';
            if ($rest !== '') {
                $parts = explode('-', $rest);
                $sineAndDuty = $parts[0];
                if (str_contains($sineAndDuty, '/')) {
                    [$agentSine, $duty] = explode('/', $sineAndDuty, 2);
                } else {
                    $agentSine = $sineAndDuty;
                }
            }
            $state['agentId'] = $agentSine;
            $area = $state['area'] ?? 'A';
            $off = $state['officeId'] ?? 'LON1A0980';

            if ($allAreas) {
                return new CommandExecutionResult(
                    ["OK SIGN-IN TO ALL WORK AREAS (A-F) / OFFICE {$off} / AGENT {$agentSine} - {$duty}"],
                    $state,
                    false,
                    'SIGN_IN'
                );
            }

            return new CommandExecutionResult(
                ["OK SIGN-IN TO WORK AREA {$area} / OFFICE {$off} / AGENT {$agentSine} - {$duty}"],
                $state,
                false,
                'SIGN_IN'
            );
        }

        if ($input === 'JO*' || $input === 'SO*') {
            $state['isSignedIn'] = false;

            return new CommandExecutionResult(
                ['SIGNED OUT OF ALL WORK AREAS (A-F)'],
                $state,
                false,
                'SIGN_OUT'
            );
        }

        if ($input === 'JO' || $input === 'SO') {
            $state['isSignedIn'] = false;
            $area = $state['area'] ?? 'A';

            return new CommandExecutionResult(
                ["SIGNED OUT OF AMADUS - WORK AREA {$area}"],
                $state,
                false,
                'SIGN_OUT'
            );
        }

        // 3. ENCODE & DECODE: DAN, DAC, DNA, DNE
        if (str_starts_with($input, 'DAN')) {
            $res = $this->decode->handleDAN(substr($input, 3));

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'DECODE_DAN');
        }
        if (str_starts_with($input, 'DAC')) {
            $res = $this->decode->handleDAC(substr($input, 3));

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'DECODE_DAC');
        }
        if (str_starts_with($input, 'DNA')) {
            $res = $this->decode->handleDNA(substr($input, 3));

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'DECODE_DNA');
        }
        if (str_starts_with($input, 'DNE')) {
            $res = $this->decode->handleDNE(substr($input, 3));

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'DECODE_DNE');
        }

        // 4. FLIGHT AVAILABILITY & TIMETABLE: AN, SN
        if (str_starts_with($input, 'AN') || str_starts_with($input, 'SN')) {
            $dispType = str_starts_with($input, 'SN') ? 'SN' : 'AN';
            $res = $this->availability->search(substr($input, 2), $dispType);
            if ($res->isError) {
                return new CommandExecutionResult($res->outputLines, $state, true, 'AVAILABILITY_ERROR');
            }

            $state['lastAvailabilityDisplay'] = [
                'origin' => '',
                'destination' => '',
                'date' => '',
                'lines' => $res->schedules,
            ];

            return new CommandExecutionResult(
                array_merge([$res->headerLine], $res->outputLines),
                $state,
                false,
                $dispType === 'SN' ? 'SCHEDULE_DISPLAY' : 'AVAILABILITY'
            );
        }

        // 5. PAGING
        if (in_array($input, ['MD', 'MU', 'MT', 'MB'], true)) {
            if (empty($state['lastAvailabilityDisplay'])) {
                return new CommandExecutionResult(['NO PREVIOUS AVAILABILITY DISPLAY TO PAGE'], $state, true, 'PAGING');
            }
            $pageStr = ($input === 'MD') ? '2 OF 2' : '1 OF 2';

            return new CommandExecutionResult(
                [
                    "PAGE {$pageStr} (END OF DISPLAY)",
                    'NO FURTHER FLIGHTS IN THIS PERIOD',
                ],
                $state,
                false,
                'PAGING'
            );
        }

        // 6. SURFACE SEGMENT: SIARNK / SI ARNK / ARNK
        if ($input === 'SIARNK' || $input === 'SI ARNK' || $input === 'ARNK') {
            $segNum = count($state['activePnr']['segments'] ?? []) + 1;
            $newSegment = [
                'segmentNumber' => $segNum,
                'airlineCode' => 'ARNK',
                'flightNumber' => '',
                'bookingClass' => '',
                'departureAirport' => '',
                'arrivalAirport' => '',
                'departureDate' => '',
                'departureTime' => '',
                'arrivalTime' => '',
                'status' => 'ARNK',
                'seats' => 0,
                'equipment' => '',
                'type' => 'ARNK',
            ];
            $state['activePnr']['segments'][] = $newSegment;

            return new CommandExecutionResult([" {$segNum}  ARNK"], $state, false, 'SURFACE_SEGMENT');
        }

        // 7. OPEN SEGMENT: SO <CARRIER> <CLASS> <ORIG><DEST>
        if (str_starts_with($input, 'SO')) {
            if (preg_match('/^SO\s+([A-Z0-9]{2})\s+([A-Z])\s+([A-Z]{3})\s*([A-Z]{3})?$/', $input, $om)) {
                $carrier = $om[1];
                $bClass = $om[2];
                $orig = $om[3];
                $dest = $om[4] ?? 'DOH';
                $segNum = count($state['activePnr']['segments'] ?? []) + 1;

                $newSegment = [
                    'segmentNumber' => $segNum,
                    'airlineCode' => $carrier,
                    'flightNumber' => 'OPEN',
                    'bookingClass' => $bClass,
                    'departureAirport' => $orig,
                    'arrivalAirport' => $dest,
                    'departureDate' => 'OPEN',
                    'departureTime' => 'OPEN',
                    'arrivalTime' => 'OPEN',
                    'status' => 'OPEN',
                    'seats' => 1,
                    'equipment' => 'OPEN',
                ];
                $state['activePnr']['segments'][] = $newSegment;

                return new CommandExecutionResult(
                    [" {$segNum}  {$carrier} OPEN {$bClass}       {$orig}{$dest} OPEN    OPEN OPEN   *1A/E*"],
                    $state,
                    false,
                    'SELL_OPEN_SEGMENT'
                );
            }
        }

        // 8. DIRECT / LONG SELL & PASSIVE SEGMENTS:
        // Format: SS<AIR><FLT><CLS><DATE><ORIG><DEST><STATUS><SEATS>[/<AIR>]
        // E.g. SSQR001B15MARDOHLHRNN2, SSQR001B15MARDOHLHRGK2/QR, SSQR001B15MARDOHLHRGL2/QR
        if (preg_match('/^SS\s*([A-Z0-9]{2})\s*(\d{1,4})\s*([A-Z])\s*(\d{1,2}[A-Z]{3})\s*([A-Z]{3})\s*([A-Z]{3})\s*(NN|HK|GK|GL)(\d+)(?:\/([A-Z0-9]{2}))?$/', $input, $lsm)) {
            $carrier = $lsm[1];
            $fltNo = $lsm[2];
            $bClass = $lsm[3];
            $date = $lsm[4];
            $orig = $lsm[5];
            $dest = $lsm[6];
            $statusReq = $lsm[7];
            $seats = (int) $lsm[8];
            $passiveCarrier = $lsm[9] ?? null;

            $statusCode = in_array($statusReq, ['GK', 'GL'], true) ? $statusReq : 'HK';
            $segNum = count($state['activePnr']['segments'] ?? []) + 1;

            $newSegment = [
                'segmentNumber' => $segNum,
                'airlineCode' => $carrier,
                'flightNumber' => $fltNo,
                'bookingClass' => $bClass,
                'departureDate' => $date,
                'departureAirport' => $orig,
                'arrivalAirport' => $dest,
                'departureTime' => '0800',
                'arrivalTime' => '1230',
                'status' => $statusCode,
                'seats' => $seats,
                'equipment' => '77W',
                'passiveCarrier' => $passiveCarrier,
            ];
            $state['activePnr']['segments'][] = $newSegment;

            $fltPad = str_pad($fltNo, 3, ' ', STR_PAD_LEFT);
            $tag = $passiveCarrier ? "  /{$passiveCarrier}" : '   *1A/E*';
            $lineResponse = " {$segNum}  {$carrier} {$fltPad} {$bClass} {$date} {$orig}{$dest} {$statusCode}{$seats}  0800 1230{$tag}";

            return new CommandExecutionResult([$lineResponse], $state, false, 'LONG_SELL');
        }

        // 9. ROUND-TRIP INBOUND / OUTBOUND SELL: SS2V1*V13
        if (preg_match('/^SS(\d+)([A-Z])(\d+)\*([A-Z])?(\d+)$/', $input, $rtm)) {
            $seats = (int) $rtm[1];
            $class1 = $rtm[2];
            $line1Num = (int) $rtm[3];
            $class2 = ! empty($rtm[4]) ? $rtm[4] : $class1;
            $line2Num = (int) $rtm[5];

            if (empty($state['lastAvailabilityDisplay']['lines'])) {
                return new CommandExecutionResult(['NO ACTIVE AVAILABILITY DISPLAY. SEARCH FLIGHTS FIRST WITH AN...'], $state, true, 'SELL_SEAT_ERROR');
            }

            $line1 = null;
            $line2 = null;
            foreach ($state['lastAvailabilityDisplay']['lines'] as $l) {
                if ($l['lineNumber'] === $line1Num) {
                    $line1 = $l;
                }
                if ($l['lineNumber'] === $line2Num) {
                    $line2 = $l;
                }
            }

            if (! $line1) {
                return new CommandExecutionResult(["LINE NUMBER {$line1Num} NOT FOUND IN ACTIVE AVAILABILITY DISPLAY"], $state, true, 'SELL_SEAT_ERROR');
            }
            if (! $line2) {
                return new CommandExecutionResult(["LINE NUMBER {$line2Num} NOT FOUND IN ACTIVE AVAILABILITY DISPLAY"], $state, true, 'SELL_SEAT_ERROR');
            }

            $outputLines = [];
            foreach ([[$line1, $class1], [$line2, $class2]] as $pair) {
                [$selectedLine, $bookingClass] = $pair;
                $sched = $selectedLine['schedule'];
                $segNum = count($state['activePnr']['segments'] ?? []) + 1;

                $newSegment = [
                    'segmentNumber' => $segNum,
                    'airlineCode' => $sched['airlineCode'],
                    'flightNumber' => $sched['flightNumber'],
                    'bookingClass' => $bookingClass,
                    'departureAirport' => $sched['departureAirport'],
                    'arrivalAirport' => $sched['arrivalAirport'],
                    'departureDate' => $selectedLine['departureDate'],
                    'departureTime' => $sched['departureTime'],
                    'arrivalTime' => $sched['arrivalTime'],
                    'status' => 'HK',
                    'seats' => $seats,
                    'equipment' => $sched['equipment'],
                ];
                $state['activePnr']['segments'][] = $newSegment;

                $fltPad = str_pad((string) $sched['flightNumber'], 3, ' ', STR_PAD_LEFT);
                $outputLines[] = " {$segNum}  {$sched['airlineCode']} {$fltPad} {$bookingClass} {$selectedLine['departureDate']} {$sched['departureAirport']}{$sched['arrivalAirport']} HK{$seats}  {$sched['departureTime']} {$sched['arrivalTime']}   *1A/E*";
            }

            return new CommandExecutionResult($outputLines, $state, false, 'SELL_ROUNDTRIP');
        }

        // 10. MULTI-LEG SELL: SS2JF3 (2 seats, class J leg 1, class F leg 2 of line 3)
        if (preg_match('/^SS(\d+)([A-Z]{2,})(\d+)$/', $input, $mlm)) {
            $seats = (int) $mlm[1];
            $classes = str_split($mlm[2]);
            $lineNum = (int) $mlm[3];

            if (empty($state['lastAvailabilityDisplay']['lines'])) {
                return new CommandExecutionResult(['NO ACTIVE AVAILABILITY DISPLAY. SEARCH FLIGHTS FIRST WITH AN...'], $state, true, 'SELL_SEAT_ERROR');
            }

            $selectedLine = null;
            foreach ($state['lastAvailabilityDisplay']['lines'] as $l) {
                if ($l['lineNumber'] === $lineNum) {
                    $selectedLine = $l;
                    break;
                }
            }

            if (! $selectedLine) {
                return new CommandExecutionResult(["LINE NUMBER {$lineNum} NOT FOUND IN ACTIVE AVAILABILITY DISPLAY"], $state, true, 'SELL_SEAT_ERROR');
            }

            $sched = $selectedLine['schedule'];
            $outputLines = [];

            foreach ($classes as $idx => $bClass) {
                $segNum = count($state['activePnr']['segments'] ?? []) + 1;
                $newSegment = [
                    'segmentNumber' => $segNum,
                    'airlineCode' => $sched['airlineCode'],
                    'flightNumber' => (string) ((int) $sched['flightNumber'] + $idx),
                    'bookingClass' => $bClass,
                    'departureAirport' => $sched['departureAirport'],
                    'arrivalAirport' => $sched['arrivalAirport'],
                    'departureDate' => $selectedLine['departureDate'],
                    'departureTime' => $sched['departureTime'],
                    'arrivalTime' => $sched['arrivalTime'],
                    'status' => 'HK',
                    'seats' => $seats,
                    'equipment' => $sched['equipment'],
                ];
                $state['activePnr']['segments'][] = $newSegment;

                $fltPad = str_pad((string) $newSegment['flightNumber'], 3, ' ', STR_PAD_LEFT);
                $outputLines[] = " {$segNum}  {$sched['airlineCode']} {$fltPad} {$bClass} {$selectedLine['departureDate']} {$sched['departureAirport']}{$sched['arrivalAirport']} HK{$seats}  {$sched['departureTime']} {$sched['arrivalTime']}   *1A/E*";
            }

            return new CommandExecutionResult($outputLines, $state, false, 'SELL_MULTI_LEG');
        }

        // 11. SHORT SELL: SS<SEATS><CLASS><LINE> (e.g. SS1Y1, SS2V4)
        if (str_starts_with($input, 'SS')) {
            if (! preg_match('/^SS(\d+)([A-Z])(\d+)$/', $input, $match)) {
                return new CommandExecutionResult(['FORMAT: SS<SEATS><CLASS><LINE> (E.G. SS1Y1 OR SS2J3)'], $state, true, 'SELL_SEAT_ERROR');
            }

            $seats = (int) $match[1];
            $bookingClass = $match[2];
            $lineNumber = (int) $match[3];

            if (empty($state['lastAvailabilityDisplay']['lines'])) {
                return new CommandExecutionResult(['NO ACTIVE AVAILABILITY DISPLAY. CHECK FLIGHTS FIRST WITH AN...'], $state, true, 'SELL_SEAT_ERROR');
            }

            $selectedLine = null;
            foreach ($state['lastAvailabilityDisplay']['lines'] as $line) {
                if ($line['lineNumber'] === $lineNumber) {
                    $selectedLine = $line;
                    break;
                }
            }

            if (! $selectedLine) {
                return new CommandExecutionResult(["LINE NUMBER {$lineNumber} NOT FOUND IN ACTIVE AVAILABILITY DISPLAY"], $state, true, 'SELL_SEAT_ERROR');
            }

            $sched = $selectedLine['schedule'];
            $segmentNumber = count($state['activePnr']['segments'] ?? []) + 1;

            $newSegment = [
                'segmentNumber' => $segmentNumber,
                'airlineCode' => $sched['airlineCode'],
                'flightNumber' => $sched['flightNumber'],
                'bookingClass' => $bookingClass,
                'departureAirport' => $sched['departureAirport'],
                'arrivalAirport' => $sched['arrivalAirport'],
                'departureDate' => $selectedLine['departureDate'],
                'departureTime' => $sched['departureTime'],
                'arrivalTime' => $sched['arrivalTime'],
                'arrivalDayOffset' => $sched['arrivalDayOffset'] ?? null,
                'status' => 'HK',
                'seats' => $seats,
                'equipment' => $sched['equipment'],
            ];
            $state['activePnr']['segments'][] = $newSegment;

            $fltPad = str_pad((string) $sched['flightNumber'], 3, ' ', STR_PAD_LEFT);
            $lineResponse = " {$segmentNumber}  {$sched['airlineCode']} {$fltPad} {$bookingClass} {$selectedLine['departureDate']} {$sched['departureAirport']}{$sched['arrivalAirport']} HK{$seats}  {$sched['departureTime']} {$sched['arrivalTime']}   *1A/E*";

            return new CommandExecutionResult([$lineResponse], $state, false, 'SELL_SEAT');
        }

        // 12. PASSENGER MODIFICATIONS: <INDEX>/<MODIFIER> (e.g. 2/, 3/(CHD), 4/, 5/(INF/ALI), 6/MARIYUM MRS)
        if (preg_match('/^(\d+)\/(.*)$/', $input, $modMatch)) {
            $paxIdx = (int) $modMatch[1];
            $modifier = trim($modMatch[2]);
            $paxList = &$state['activePnr']['passengers'];

            if ($paxIdx < 1 || $paxIdx > count($paxList)) {
                return new CommandExecutionResult(["PASSENGER NUMBER {$paxIdx} NOT FOUND IN ACTIVE PNR"], $state, true, 'PAX_MOD_ERROR');
            }

            $targetPax = &$paxList[$paxIdx - 1];

            // 12a. 2/ or 4/ : Clear infant or remove CHD title
            if ($modifier === '') {
                if (! empty($targetPax['infant'])) {
                    unset($targetPax['infant']);
                }
                if (($targetPax['type'] ?? '') === 'CHD') {
                    $targetPax['type'] = 'ADT';
                    unset($targetPax['dateOfBirth']);
                }

                $titleStr = ! empty($targetPax['title']) ? " {$targetPax['title']}" : '';
                $disp = "  {$targetPax['id']}.{$targetPax['lastName']}/{$targetPax['firstName']}{$titleStr}";

                return new CommandExecutionResult([$disp], $state, false, 'PAX_MODIFIED');
            }

            // 12b. 3/(CHD) : Add Child Title
            if ($modifier === '(CHD)') {
                $targetPax['type'] = 'CHD';
                $titleStr = ! empty($targetPax['title']) ? " {$targetPax['title']}" : '';
                $disp = "  {$targetPax['id']}.{$targetPax['lastName']}/{$targetPax['firstName']}{$titleStr}(CHD)";

                return new CommandExecutionResult([$disp], $state, false, 'PAX_MODIFIED');
            }

            // 12c. 5/(INF/<NAME>) : Add infant
            if (preg_match('/^\(INF\/([A-Z]+)\)$/', $modifier, $infM)) {
                $targetPax['infant'] = [
                    'firstName' => $infM[1],
                    'lastName' => $targetPax['lastName'],
                ];
                $titleStr = ! empty($targetPax['title']) ? " {$targetPax['title']}" : '';
                $disp = "  {$targetPax['id']}.{$targetPax['lastName']}/{$targetPax['firstName']}{$titleStr}(INF/{$infM[1]})";

                return new CommandExecutionResult([$disp], $state, false, 'PAX_MODIFIED');
            }

            // 12d. 6/MARIYUM MRS : Update name/title
            if (preg_match('/^([A-Z]+)(?:\s+([A-Z]+))?$/', $modifier, $nmM)) {
                $targetPax['firstName'] = $nmM[1];
                $targetPax['title'] = $nmM[2] ?? '';
                $titleStr = ! empty($targetPax['title']) ? " {$targetPax['title']}" : '';
                $disp = "  {$targetPax['id']}.{$targetPax['lastName']}/{$targetPax['firstName']}{$titleStr}";

                return new CommandExecutionResult([$disp], $state, false, 'PAX_MODIFIED');
            }
        }

        // 13. PASSENGER NAME: NM
        if (str_starts_with($input, 'NM')) {
            $rawName = trim(substr($input, 2));

            // NM2<SURNAME>/<FIRST1> <TITLE1>/<FIRST2> <TITLE2>
            if (preg_match('/^([2-9]\d*)([A-Z]+)\/(.+)$/', $rawName, $multiFamilyMatch)) {
                $count = (int) $multiFamilyMatch[1];
                $surname = $multiFamilyMatch[2];
                $allPaxStr = $multiFamilyMatch[3];
                $paxSegments = explode('/', $allPaxStr);

                if (count($paxSegments) > 1) {
                    $outLines = [];
                    $familyBase = count($state['activePnr']['passengers']) + 1;

                    foreach ($paxSegments as $idx => $pSegment) {
                        $pSegment = trim($pSegment);
                        $pId = "{$familyBase}.".($idx + 1);

                        // Parse title
                        $parts = preg_split('/\s+/', $pSegment);
                        $fName = $parts[0];
                        $title = $parts[1] ?? '';

                        $pRecord = [
                            'id' => $pId,
                            'type' => 'ADT',
                            'lastName' => $surname,
                            'firstName' => $fName,
                            'title' => $title,
                        ];
                        $state['activePnr']['passengers'][] = $pRecord;

                        $titleStr = $title ? " {$title}" : '';
                        $outLines[] = "  {$pId}.{$surname}/{$fName}{$titleStr}";
                    }

                    return new CommandExecutionResult($outLines, $state, false, 'NAME_ELEMENT');
                }
            }

            // NM1<SURNAME>/<FIRSTNAME> [TITLE](INF<SURNAME>/<FIRSTNAME>) - Infant with different surname
            if (preg_match('/^1([A-Z]+)\/([A-Z]+)(?:\s+([A-Z]+))?\(INF([A-Z]+)\/([A-Z]+)\)$/', $rawName, $diffInfM)) {
                $pId = (count($state['activePnr']['passengers']) + 1).'.1';
                $title = $diffInfM[3] ?? '';
                $state['activePnr']['passengers'][] = [
                    'id' => $pId,
                    'type' => 'ADT',
                    'lastName' => $diffInfM[1],
                    'firstName' => $diffInfM[2],
                    'title' => $title,
                    'infant' => [
                        'lastName' => $diffInfM[4],
                        'firstName' => $diffInfM[5],
                    ],
                ];
                $titleStr = $title ? " {$title}" : '';

                return new CommandExecutionResult(["  {$pId}.{$diffInfM[1]}/{$diffInfM[2]}{$titleStr}(INF{$diffInfM[4]}/{$diffInfM[5]})"], $state, false, 'NAME_ELEMENT');
            }

            // NM1<SURNAME>/<FIRSTNAME> [TITLE](INF/<FIRSTNAME>) - Infant with same surname
            if (preg_match('/^1([A-Z]+)\/([A-Z]+)(?:\s+([A-Z]+))?\(INF\/([A-Z]+)\)$/', $rawName, $sameInfM)) {
                $pId = (count($state['activePnr']['passengers']) + 1).'.1';
                $title = $sameInfM[3] ?? '';
                $state['activePnr']['passengers'][] = [
                    'id' => $pId,
                    'type' => 'ADT',
                    'lastName' => $sameInfM[1],
                    'firstName' => $sameInfM[2],
                    'title' => $title,
                    'infant' => [
                        'lastName' => $sameInfM[1],
                        'firstName' => $sameInfM[4],
                    ],
                ];
                $titleStr = $title ? " {$title}" : '';

                return new CommandExecutionResult(["  {$pId}.{$sameInfM[1]}/{$sameInfM[2]}{$titleStr}(INF/{$sameInfM[4]})"], $state, false, 'NAME_ELEMENT');
            }

            // NM1<SURNAME>/<FIRSTNAME> [TITLE](CHD) or (CHD/<DOB>)
            if (preg_match('/^1([A-Z]+)\/([A-Z]+)(?:\s+([A-Z]+))?\(CHD(?:\/(\d{1,2}[A-Z]{3}\d{2}))?\)$/', $rawName, $chdM)) {
                $pId = (count($state['activePnr']['passengers']) + 1).'.1';
                $title = $chdM[3] ?? '';
                $dob = $chdM[4] ?? '';
                $state['activePnr']['passengers'][] = [
                    'id' => $pId,
                    'type' => 'CHD',
                    'lastName' => $chdM[1],
                    'firstName' => $chdM[2],
                    'title' => $title,
                    'dateOfBirth' => $dob,
                ];
                $titleStr = $title ? " {$title}" : '';
                $dobStr = $dob ? "/{$dob}" : '';

                return new CommandExecutionResult(["  {$pId}.{$chdM[1]}/{$chdM[2]}{$titleStr}(CHD{$dobStr})"], $state, false, 'NAME_ELEMENT');
            }

            // Standard adult: NM1<SURNAME>/<FIRSTNAME> [TITLE]
            if (preg_match('/^(\d+)([A-Z]+)\/([A-Z]+)(?:\s+([A-Z]+))?$/', $rawName, $sm)) {
                $pId = (count($state['activePnr']['passengers']) + 1).'.1';
                $title = $sm[4] ?? '';
                $state['activePnr']['passengers'][] = [
                    'id' => $pId,
                    'type' => 'ADT',
                    'lastName' => $sm[2],
                    'firstName' => $sm[3],
                    'title' => $title,
                ];
                $titleStr = $title ? " {$title}" : '';

                return new CommandExecutionResult(["  {$pId}.{$sm[2]}/{$sm[3]}{$titleStr}"], $state, false, 'NAME_ELEMENT');
            }

            return new CommandExecutionResult(
                [
                    'INVALID NAME FORMAT. EXAMPLES:',
                    '  NM1DOE/JOHN MR',
                    '  NM2HUSSAIN/ALI MR/M MRS',
                    '  NM1HUSSAIN/NASEER MSTR(CHD)',
                    '  NM1HUSSAIN/M MRS(INF/NABIL)',
                    '  NM1HUSSAIN/M MRS(INFALEMADI/ALI)',
                ],
                $state,
                true,
                'NAME_ELEMENT_ERROR'
            );
        }

        // 14. CONTACT / PHONE / EMAIL: AP, APE
        if (str_starts_with($input, 'APE')) {
            $email = ltrim(trim(substr($rawInput, 3)), '- ');
            $state['activePnr']['contacts'][] = [
                'id' => count($state['activePnr']['contacts']) + 1,
                'type' => 'EMAIL',
                'detail' => $email,
            ];

            return new CommandExecutionResult(["OK APE {$email}"], $state, false, 'CONTACT_ELEMENT');
        }

        if (str_starts_with($input, 'AP')) {
            $contact = trim(substr($input, 2));
            if ($contact === '') {
                // Enter agency default office phone
                $off = $state['officeId'] ?? 'LON1A0980';
                $agencyPhone = 'LON 020-7946-0912-A';
                $state['activePnr']['contacts'][] = [
                    'id' => count($state['activePnr']['contacts']) + 1,
                    'type' => 'PHONE',
                    'detail' => $agencyPhone,
                ];

                return new CommandExecutionResult(["OK AP {$agencyPhone}"], $state, false, 'CONTACT_ELEMENT');
            }

            $state['activePnr']['contacts'][] = [
                'id' => count($state['activePnr']['contacts']) + 1,
                'type' => 'PHONE',
                'detail' => $contact,
            ];

            return new CommandExecutionResult(["OK AP {$contact}"], $state, false, 'CONTACT_ELEMENT');
        }

        // 15. TICKETING: TKTL, TKOK, TKXL
        if (str_starts_with($input, 'TK')) {
            if ($input === 'TKOK') {
                $state['activePnr']['ticketing'] = ['type' => 'TKOK', 'raw' => 'TKOK'];

                return new CommandExecutionResult(['OK TKOK'], $state, false, 'TICKETING_ELEMENT');
            }
            if (str_starts_with($input, 'TKTL')) {
                $rest = trim(substr($input, 4));
                $state['activePnr']['ticketing'] = ['type' => 'TKTL', 'raw' => $input, 'date' => $rest];

                return new CommandExecutionResult(["OK {$input}"], $state, false, 'TICKETING_ELEMENT');
            }
            if (str_starts_with($input, 'TKXL')) {
                $rest = trim(substr($input, 4));
                $state['activePnr']['ticketing'] = ['type' => 'TKXL', 'raw' => $input, 'date' => $rest];

                return new CommandExecutionResult(["OK {$input}"], $state, false, 'TICKETING_ELEMENT');
            }
        }

        // 16. RECEIVED FROM: RF
        if (str_starts_with($input, 'RF')) {
            $name = trim(substr($input, 2));
            if (! $name) {
                return new CommandExecutionResult(['FORMAT: RF <NAME> (E.G. RF PASSENGER, RF P, RF ALI)'], $state, true, 'RF_ERROR');
            }
            $state['activePnr']['receivedFrom'] = $name;

            // Check if active split is in progress
            if (! empty($state['activeSplit']) && ($state['activeSplit']['status'] ?? '') === 'NEED_RF') {
                $state['activeSplit']['receivedFrom'] = $name;
                $state['activeSplit']['status'] = 'NEED_EF';

                return new CommandExecutionResult(
                    [
                        "RF {$name} - RETAINED FOR SPLIT FILE",
                        'ENTER "EF" TO FILE SPLIT RECORD',
                    ],
                    $state,
                    false,
                    'SPLIT_RF'
                );
            }

            return new CommandExecutionResult(["RF {$name} - RETAINED"], $state, false, 'RECEIVED_FROM');
        }

        // 17. REMARKS: RM (General), RC (Confidential)
        if (str_starts_with($input, 'RM') || str_starts_with($input, 'RC')) {
            $isConfidential = str_starts_with($input, 'RC');
            $remarkText = trim(substr($input, 2));
            $tag = $isConfidential ? 'RC' : 'RM';

            $state['activePnr']['remarks'][] = [
                'type' => $isConfidential ? 'CONFIDENTIAL' : 'GENERAL',
                'text' => $remarkText,
            ];

            return new CommandExecutionResult(["OK {$tag} {$remarkText}"], $state, false, 'REMARK_ELEMENT');
        }

        // 18. FREQUENT FLYER AUTOMATED: FFN<AIRLINE>-<NUMBER>[/P<PAX>]
        if (preg_match('/^FFN\s*([A-Z0-9]{2})-([0-9A-Z]+)(?:\/P(\d+))?$/', $input, $ffnM)) {
            $airline = $ffnM[1];
            $ffNum = $ffnM[2];
            $paxIdx = $ffnM[3] ?? null;
            $paxTag = $paxIdx ? "/P{$paxIdx}" : '';

            $state['activePnr']['specialServiceRequests'][] = [
                'type' => 'SSR',
                'code' => 'FQTV',
                'text' => "FQTV {$airline} HK1 {$airline}{$ffNum}{$paxTag}",
            ];

            return new CommandExecutionResult(["OK FFN {$airline}-{$ffNum}{$paxTag}"], $state, false, 'SSR_FFN');
        }

        // 19. MANUAL FREQUENT FLYER: SRFQTV...
        if (preg_match('/^SR\s*FQTV\s*([A-Z0-9]{2})-([0-9A-Z]+)(?:\/P(\d+))?$/', $input, $sfqM)) {
            $airline = $sfqM[1];
            $ffNum = $sfqM[2];
            $paxIdx = $sfqM[3] ?? null;
            $paxTag = $paxIdx ? "/P{$paxIdx}" : '';

            $state['activePnr']['specialServiceRequests'][] = [
                'type' => 'SSR',
                'code' => 'FQTV',
                'text' => "FQTV {$airline} HK1 {$ffNum}{$paxTag}",
            ];

            return new CommandExecutionResult(["OK SSR FQTV {$airline} HK1 {$ffNum}{$paxTag}"], $state, false, 'SSR_FQTV');
        }

        // 20. SEAT MAP DISPLAY: SM<SEGMENT>
        if (preg_match('/^SM(\d+)$/', $input, $smM)) {
            $segIdx = (int) $smM[1];
            $seg = null;
            foreach ($state['activePnr']['segments'] ?? [] as $s) {
                if (($s['segmentNumber'] ?? 0) === $segIdx) {
                    $seg = $s;
                    break;
                }
            }

            $carrier = $seg['airlineCode'] ?? 'QR';
            $fltNo = $seg['flightNumber'] ?? '001';
            $eq = $seg['equipment'] ?? '77W';

            $lines = $this->pnr->formatSeatMap($carrier, $fltNo, $eq, $segIdx);

            return new CommandExecutionResult($lines, $state, false, 'SEAT_MAP');
        }

        // 21. SEAT SELECTION & REQUESTS: ST...
        if (str_starts_with($input, 'ST')) {
            $req = trim($input);
            $state['activePnr']['seatRequests'][] = $req;

            if ($req === 'ST') {
                return new CommandExecutionResult(['OK ST - GENERIC NON-SMOKING RECORDED'], $state, false, 'SEAT_REQUEST');
            }
            if ($req === 'ST/S') {
                return new CommandExecutionResult(['OK ST/S - GENERIC SMOKING RECORDED'], $state, false, 'SEAT_REQUEST');
            }

            return new CommandExecutionResult(["OK {$req} CONFIRMED"], $state, false, 'SEAT_REQUEST');
        }

        // 22. OTHER SERVICES INFORMATION: OS <CARRIER> <TEXT>
        if (preg_match('/^OS\s+([A-Z0-9]{2})\s+(.+)$/', $input, $osm)) {
            $carrier = $osm[1];
            $text = $osm[2];

            $state['activePnr']['specialServiceRequests'][] = [
                'type' => 'OSI',
                'code' => 'OSI',
                'carrier' => $carrier,
                'text' => "{$carrier} {$text}",
            ];

            return new CommandExecutionResult(["OK OS {$carrier} {$text}"], $state, false, 'OSI_ELEMENT');
        }

        // 23. SPECIAL SERVICE REQUESTS: SR, SSR, OSI
        if (str_starts_with($input, 'SR') || str_starts_with($input, 'SSR')) {
            $raw = trim(preg_replace('/^SSR?/', '', $input));
            $state['activePnr']['specialServiceRequests'][] = [
                'type' => 'SSR',
                'code' => substr($raw, 0, 4),
                'text' => $raw,
            ];

            return new CommandExecutionResult(["OK SSR {$raw} HK1"], $state, false, 'SSR_ELEMENT');
        }

        if (str_starts_with($input, 'OSI')) {
            $raw = trim(substr($input, 3));
            $state['activePnr']['specialServiceRequests'][] = [
                'type' => 'OSI',
                'code' => 'OSI',
                'text' => $raw,
            ];

            return new CommandExecutionResult(["OK OSI {$raw}"], $state, false, 'OSI_ELEMENT');
        }

        // 24. CANCEL ITINERARY: XI
        if ($input === 'XI') {
            if (empty($state['activePnr']['segments'])) {
                return new CommandExecutionResult(['NO ACTIVE ITINERARY SEGMENTS TO CANCEL'], $state, true, 'XI_ERROR');
            }
            $state['activePnr']['segments'] = [];
            $state['activePnr']['tsts'] = [];

            return new CommandExecutionResult(['ITINERARY CANCELLED - ALL SEGMENTS REMOVED'], $state, false, 'XI');
        }

        // 25. CANCEL ELEMENT / SEGMENT: XE
        if (str_starts_with($input, 'XE')) {
            $lineNum = (int) trim(substr($input, 2));
            if ($lineNum <= 0) {
                return new CommandExecutionResult(['FORMAT: XE<LINE NUMBER> (E.G. XE 1 OR XE4)'], $state, true, 'XE_ERROR');
            }

            if (! empty($state['activePnr']['segments']) && $lineNum <= count($state['activePnr']['segments'])) {
                array_splice($state['activePnr']['segments'], $lineNum - 1, 1);
                foreach ($state['activePnr']['segments'] as $idx => &$s) {
                    $s['segmentNumber'] = $idx + 1;
                }

                return new CommandExecutionResult(["SEGMENT {$lineNum} CANCELLED"], $state, false, 'XE');
            }

            return new CommandExecutionResult(["ELEMENT {$lineNum} CANCELLED"], $state, false, 'XE');
        }

        // 26. CHANGE BOOKING CLASS / DATE: SB
        // SBJ (all segments), SBJ4 (segment 4 class J), SB30MAR3 (segment 3 date 30MAR)
        if (str_starts_with($input, 'SB')) {
            $rest = substr($input, 2);

            // SB<DATE><SEGMENT> (e.g. SB30MAR3)
            if (preg_match('/^(\d{1,2}[A-Z]{3})(\d+)$/', $rest, $sbdM)) {
                $newDate = $sbdM[1];
                $segIdx = (int) $sbdM[2];
                $found = false;

                foreach ($state['activePnr']['segments'] as &$seg) {
                    if (($seg['segmentNumber'] ?? 0) === $segIdx) {
                        $seg['departureDate'] = $newDate;
                        $found = true;
                        break;
                    }
                }

                if (! $found) {
                    return new CommandExecutionResult(["SEGMENT {$segIdx} NOT FOUND"], $state, true, 'SB_ERROR');
                }

                return new CommandExecutionResult(["SEGMENT {$segIdx} DATE CHANGED TO {$newDate}"], $state, false, 'SB_DATE');
            }

            // SB<CLASS><SEGMENT> (e.g. SBJ4)
            if (preg_match('/^([A-Z])(\d+)$/', $rest, $sbcM)) {
                $newClass = $sbcM[1];
                $segIdx = (int) $sbcM[2];
                $found = false;

                foreach ($state['activePnr']['segments'] as &$seg) {
                    if (($seg['segmentNumber'] ?? 0) === $segIdx) {
                        $seg['bookingClass'] = $newClass;
                        $found = true;
                        break;
                    }
                }

                if (! $found) {
                    return new CommandExecutionResult(["SEGMENT {$segIdx} NOT FOUND"], $state, true, 'SB_ERROR');
                }

                return new CommandExecutionResult(["SEGMENT {$segIdx} REBOOKED TO CLASS {$newClass}"], $state, false, 'SB_CLASS');
            }

            // SB<CLASS> (e.g. SBJ - all segments)
            if (preg_match('/^([A-Z])$/', $rest, $sbAllM)) {
                $newClass = $sbAllM[1];
                if (empty($state['activePnr']['segments'])) {
                    return new CommandExecutionResult(['NO SEGMENTS AVAILABLE TO REBOOK'], $state, true, 'SB_ERROR');
                }

                foreach ($state['activePnr']['segments'] as &$seg) {
                    $seg['bookingClass'] = $newClass;
                }

                return new CommandExecutionResult(["ALL SEGMENTS REBOOKED TO CLASS {$newClass}"], $state, false, 'SB_ALL');
            }
        }

        // 27. RETRIEVE HISTORY: RH (General), RHS<SEG> (Segment), RLH (Airline locators)
        if ($input === 'RLH') {
            $lines = $this->pnr->formatAirlineLocators($state['activePnr']);
            $lines[0] = '--- AIRLINE RECORD LOCATOR HISTORY ---';

            return new CommandExecutionResult($lines, $state, false, 'HISTORY_RLH');
        }

        if (preg_match('/^RHS(\d+)$/', $input, $rhsM)) {
            $segNum = (int) $rhsM[1];
            $lines = $this->pnr->formatPnrHistory($state['activePnr'], $segNum);

            return new CommandExecutionResult($lines, $state, false, 'HISTORY_SEGMENT');
        }

        if ($input === 'RH') {
            $lines = $this->pnr->formatPnrHistory($state['activePnr']);

            return new CommandExecutionResult($lines, $state, false, 'HISTORY_GENERAL');
        }

        // 28. PRINT: WRS/RT (Screen), WRA/RT (All)
        if ($input === 'WRS/RT' || $input === 'WRS') {
            $lines = $this->pnr->formatPrintout($state['activePnr'], false);

            return new CommandExecutionResult($lines, $state, false, 'PRINT_SCREEN');
        }
        if ($input === 'WRA/RT' || $input === 'WRA') {
            $lines = $this->pnr->formatPrintout($state['activePnr'], true);

            return new CommandExecutionResult($lines, $state, false, 'PRINT_ALL');
        }

        // 29. OPTION ELEMENT: OP<DATE>/<TEXT> (e.g. OP12MAR/free Text)
        if (preg_match('/^OP\s*(\d{1,2}[A-Z]{3})\/(.+)$/', $input, $opM)) {
            $opDate = $opM[1];
            $opText = strtoupper(trim($opM[2]));

            $state['activePnr']['options'][] = [
                'date' => $opDate,
                'text' => $opText,
            ];

            return new CommandExecutionResult(["OK OP {$opDate}/{$opText}"], $state, false, 'OPTION_ELEMENT');
        }

        // 30. DISPLAY PNR LIST BOOKED SPECIFIC OFFICE: LM/<AIR><FLT>/<DATE>/<RT> & LM<LINE>
        if (preg_match('/^LM\/([A-Z0-9]{2})(\d{1,4})\/(\d{1,2}[A-Z]{3})\/([A-Z]{6})$/', $input, $lmm)) {
            $air = $lmm[1];
            $flt = $lmm[2];
            $date = $lmm[3];
            $orig = substr($lmm[4], 0, 3);
            $dest = substr($lmm[4], 3, 3);

            $pnrList = [
                [
                    'line' => 1,
                    'name' => 'HUSSAIN/ALI MR',
                    'seats' => '2V',
                    'rloc' => 'Q1A2B3',
                    'office' => 'LON1A0980',
                    'pnr' => [
                        'recordLocator' => 'Q1A2B3',
                        'creationOffice' => 'LON1A0980',
                        'creationAgent' => '1234AB',
                        'passengers' => [['id' => '1.1', 'lastName' => 'HUSSAIN', 'firstName' => 'ALI', 'title' => 'MR', 'type' => 'ADT']],
                        'segments' => [['segmentNumber' => 1, 'airlineCode' => $air, 'flightNumber' => $flt, 'bookingClass' => 'V', 'departureDate' => $date, 'departureAirport' => $orig, 'arrivalAirport' => $dest, 'departureTime' => '0845', 'arrivalTime' => '1215', 'seats' => 2, 'status' => 'HK']],
                        'contacts' => [['id' => 1, 'type' => 'PHONE', 'detail' => 'DOH 4667579']],
                        'ticketing' => ['type' => 'TKOK', 'raw' => 'TKOK'],
                        'receivedFrom' => 'PASSENGER',
                    ],
                ],
                [
                    'line' => 2,
                    'name' => 'SMITH/JOHN MR',
                    'seats' => '1Y',
                    'rloc' => 'SM9999',
                    'office' => 'LON1A0980',
                    'pnr' => [
                        'recordLocator' => 'SM9999',
                        'creationOffice' => 'LON1A0980',
                        'creationAgent' => '1234AB',
                        'passengers' => [['id' => '1.1', 'lastName' => 'SMITH', 'firstName' => 'JOHN', 'title' => 'MR', 'type' => 'ADT']],
                        'segments' => [['segmentNumber' => 1, 'airlineCode' => $air, 'flightNumber' => $flt, 'bookingClass' => 'Y', 'departureDate' => $date, 'departureAirport' => $orig, 'arrivalAirport' => $dest, 'departureTime' => '0845', 'arrivalTime' => '1215', 'seats' => 1, 'status' => 'HK']],
                        'contacts' => [['id' => 1, 'type' => 'PHONE', 'detail' => 'LON 020-7946-0912']],
                        'ticketing' => ['type' => 'TKOK', 'raw' => 'TKOK'],
                        'receivedFrom' => 'AGENT',
                    ],
                ],
                [
                    'line' => 3,
                    'name' => 'DOE/JANE MRS',
                    'seats' => '1J',
                    'rloc' => 'DJ1234',
                    'office' => 'LON1A0980',
                    'pnr' => [
                        'recordLocator' => 'DJ1234',
                        'creationOffice' => 'LON1A0980',
                        'creationAgent' => '1234AB',
                        'passengers' => [['id' => '1.1', 'lastName' => 'DOE', 'firstName' => 'JANE', 'title' => 'MRS', 'type' => 'ADT']],
                        'segments' => [['segmentNumber' => 1, 'airlineCode' => $air, 'flightNumber' => $flt, 'bookingClass' => 'J', 'departureDate' => $date, 'departureAirport' => $orig, 'arrivalAirport' => $dest, 'departureTime' => '0845', 'arrivalTime' => '1215', 'seats' => 1, 'status' => 'HK']],
                        'contacts' => [['id' => 1, 'type' => 'PHONE', 'detail' => 'LON 020-7946-0912']],
                        'ticketing' => ['type' => 'TKOK', 'raw' => 'TKOK'],
                        'receivedFrom' => 'JANE',
                    ],
                ],
                [
                    'line' => 4,
                    'name' => 'AL-EMADI/ALI MR',
                    'seats' => '1F',
                    'rloc' => 'AE5678',
                    'office' => 'LON1A0980',
                    'pnr' => [
                        'recordLocator' => 'AE5678',
                        'creationOffice' => 'LON1A0980',
                        'creationAgent' => '1234AB',
                        'passengers' => [['id' => '1.1', 'lastName' => 'AL-EMADI', 'firstName' => 'ALI', 'title' => 'MR', 'type' => 'ADT']],
                        'segments' => [['segmentNumber' => 1, 'airlineCode' => $air, 'flightNumber' => $flt, 'bookingClass' => 'F', 'departureDate' => $date, 'departureAirport' => $orig, 'arrivalAirport' => $dest, 'departureTime' => '0845', 'arrivalTime' => '1215', 'seats' => 1, 'status' => 'HK']],
                        'contacts' => [['id' => 1, 'type' => 'PHONE', 'detail' => 'DOH 4667579']],
                        'ticketing' => ['type' => 'TKOK', 'raw' => 'TKOK'],
                        'receivedFrom' => 'MR ALEMADI',
                    ],
                ],
                [
                    'line' => 5,
                    'name' => 'AHMED/TARIQ MR',
                    'seats' => '1Y',
                    'rloc' => 'AT4321',
                    'office' => 'LON1A0980',
                    'pnr' => [
                        'recordLocator' => 'AT4321',
                        'creationOffice' => 'LON1A0980',
                        'creationAgent' => '1234AB',
                        'passengers' => [['id' => '1.1', 'lastName' => 'AHMED', 'firstName' => 'TARIQ', 'title' => 'MR', 'type' => 'ADT']],
                        'segments' => [['segmentNumber' => 1, 'airlineCode' => $air, 'flightNumber' => $flt, 'bookingClass' => 'Y', 'departureDate' => $date, 'departureAirport' => $orig, 'arrivalAirport' => $dest, 'departureTime' => '0845', 'arrivalTime' => '1215', 'seats' => 1, 'status' => 'HK']],
                        'contacts' => [['id' => 1, 'type' => 'PHONE', 'detail' => 'DOH 4667579']],
                        'ticketing' => ['type' => 'TKOK', 'raw' => 'TKOK'],
                        'receivedFrom' => 'TARIQ',
                    ],
                ],
            ];

            $state['lastPnrList'] = array_column($pnrList, 'pnr');

            $fltPad = str_pad($flt, 3, ' ', STR_PAD_LEFT);
            $lines = [
                "LM/{$air} {$fltPad}/{$date}/{$orig}{$dest} - PASSENGER LIST",
                '-------------------------------------------------------------',
            ];
            foreach ($pnrList as $item) {
                $lPad = str_pad((string) $item['line'], 2, ' ', STR_PAD_LEFT);
                $namePad = str_pad($item['name'], 22, ' ', STR_PAD_RIGHT);
                $lines[] = " {$lPad} {$namePad} {$item['seats']}  {$item['rloc']}  {$item['office']}";
            }
            $lines[] = '-------------------------------------------------------------';
            $lines[] = "USE 'LM<LINE>' (E.G. LM5) OR 'RT<LINE>' TO RETRIEVE";

            return new CommandExecutionResult($lines, $state, false, 'LM_LIST');
        }

        if (preg_match('/^LM(\d+)$/', $input, $lmnM)) {
            $lineIdx = (int) $lmnM[1];
            if (empty($state['lastPnrList']) || ! isset($state['lastPnrList'][$lineIdx - 1])) {
                return new CommandExecutionResult(["NO PNR ON LINE {$lineIdx} OF LIST"], $state, true, 'LM_ERROR');
            }

            $state['activePnr'] = $state['lastPnrList'][$lineIdx - 1];

            return new CommandExecutionResult($this->pnr->formatPnrDisplay($state['activePnr']), $state, false, 'LM_RETRIEVE');
        }

        // 31. SPLIT PNR: SP<PAX>, EF
        if (preg_match('/^SP(\d+)$/', $input, $spM)) {
            $paxIdx = (int) $spM[1];
            $paxCount = count($state['activePnr']['passengers'] ?? []);

            if ($paxCount <= 1) {
                return new CommandExecutionResult(['SPLIT NOT ALLOWED - MINIMUM 2 PASSENGERS REQUIRED IN ACTIVE PNR'], $state, true, 'SP_ERROR');
            }
            if ($paxIdx < 1 || $paxIdx > $paxCount) {
                return new CommandExecutionResult(["PASSENGER NUMBER {$paxIdx} NOT FOUND TO SPLIT"], $state, true, 'SP_ERROR');
            }

            $state['activeSplit'] = [
                'paxIndex' => $paxIdx,
                'status' => 'NEED_RF',
            ];

            return new CommandExecutionResult(
                [
                    "SPLIT IN PROGRESS - PASSENGER {$paxIdx} SELECTED",
                    'ENTER RF TO PROCEED WITH SPLIT TRANSACTION',
                ],
                $state,
                false,
                'SPLIT_INIT'
            );
        }

        if ($input === 'EF') {
            if (empty($state['activeSplit']) || ($state['activeSplit']['status'] ?? '') !== 'NEED_EF') {
                return new CommandExecutionResult(['NO ACTIVE SPLIT PENDING FILE (USE SP<PAX> THEN RF P BEFORE EF)'], $state, true, 'EF_ERROR');
            }

            $paxIdx = $state['activeSplit']['paxIndex'];
            $splitPax = array_splice($state['activePnr']['passengers'], $paxIdx - 1, 1)[0];

            $splitRloc = $this->pnr->generateRecordLocator();
            $parentRloc = $state['activePnr']['recordLocator'] ?? 'PARENT';

            $childPnr = $state['activePnr'];
            $childPnr['recordLocator'] = $splitRloc;
            $childPnr['passengers'] = [$splitPax];
            $childPnr['splitFrom'] = $parentRloc;
            unset($childPnr['splitTo']);

            $state['activePnr']['splitTo'] = $splitRloc;
            unset($state['activeSplit']);

            if ($userId !== 'guest') {
                try {
                    Pnr::create([
                        'record_locator' => $splitRloc,
                        'user_id' => $userId,
                        'office_id' => $state['officeId'] ?? 'LON1A0980',
                        'status' => 'CONFIRMED',
                        'data_json' => $childPnr,
                    ]);
                } catch (\Throwable $e) {
                    // Ignore DB write error
                }
            }

            return new CommandExecutionResult(
                [
                    "SPLIT RECORD FILED AS: {$splitRloc}",
                    "ASSOCIATE SPLIT GENERATED FOR PASSENGER {$paxIdx}",
                    'PARENT PNR RETAINED: ENTER RF THEN ET/ER TO COMPLETE',
                ],
                $state,
                false,
                'SPLIT_FILED'
            );
        }

        // 32. MANUAL / ELECTRONIC TICKET NUMBER: FH...
        // FHA 157-9196789012, FHM 157-2124564781, FHE 157-9196789012, FHA INF..., FHA PAX...[/P2]
        if (preg_match('/^FH([AME])(?:\s+(INF|PAX))?\s+([0-9]{3}-[0-9]{10}(?:-[0-9]{3})?)(?:\/P(\d+))?$/', $input, $fhm)) {
            $method = $fhm[1];
            $paxType = $fhm[2] ?? 'PAX';
            $tktNum = $fhm[3];
            $paxIdx = $fhm[4] ?? null;

            $state['activePnr']['tickets'][] = [
                'ticketNumber' => $tktNum,
                'method' => "FH{$method}",
                'passengerType' => $paxType,
                'paxIndex' => $paxIdx,
                'coupons' => ['CPN1-OPEN', 'CPN2-OPEN'],
            ];
            $state['activePnr']['ticketing'] = ['type' => 'TKOK', 'raw' => 'TKOK'];

            $paxTag = $paxIdx ? "/P{$paxIdx}" : '';

            return new CommandExecutionResult(
                ["OK FH{$method} {$paxType} {$tktNum}{$paxTag}"],
                $state,
                false,
                'MANUAL_TICKET'
            );
        }

        // 33. FARE QUOTE DISPLAY: FQD
        if (str_starts_with($input, 'FQD')) {
            $res = $this->pricing->handleFQD(substr($input, 3));

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'FQD');
        }

        // 34. PRICING & TST: FXP, FXX, FXB
        if (in_array($input, ['FXP', 'FXX', 'FXB'], true)) {
            $storeTst = ($input === 'FXP' || $input === 'FXB');
            $res = $this->pricing->calculateFare($state['activePnr'], $storeTst);
            if ($res->isError) {
                return new CommandExecutionResult($res->lines, $state, true, 'PRICING_ERROR');
            }
            if ($res->tst && $storeTst) {
                $state['activePnr']['tsts'][] = $res->tst;
            }

            return new CommandExecutionResult($res->lines, $state, false, $input);
        }

        // 35. FORM OF PAYMENT: FP
        if (str_starts_with($input, 'FP')) {
            $fopText = trim(substr($input, 2));
            $state['activePnr']['fop'] = [
                'type' => str_starts_with($fopText, 'CC') ? 'CC' : 'CASH',
                'details' => $fopText,
            ];

            return new CommandExecutionResult(["OK FP {$fopText}"], $state, false, 'FORM_OF_PAYMENT');
        }

        // 36. COMMISSION: FM
        if (str_starts_with($input, 'FM')) {
            $comm = trim(substr($input, 2));
            $state['activePnr']['commission'] = $comm;

            return new CommandExecutionResult(["OK FM {$comm}"], $state, false, 'COMMISSION');
        }

        // 37. END TRANSACTION: ET, ER
        if ($input === 'ET' || $input === 'ER') {
            $val = $this->pnr->validateMandatoryPrintElements($state['activePnr']);
            if (! $val->isValid) {
                return new CommandExecutionResult($val->errors, $state, true, 'ET_ERROR');
            }

            if ($userId === 'guest') {
                return new CommandExecutionResult(['GUEST SESSION: LOGIN REQUIRED TO STORE AND COMMIT PNR (ET/ER)'], $state, true, 'ET_UNAUTHENTICATED');
            }

            if (empty($state['activePnr']['recordLocator'])) {
                $state['activePnr']['recordLocator'] = $this->pnr->generateRecordLocator();
            }

            $rloc = $state['activePnr']['recordLocator'];
            $office = $state['officeId'] ?? 'LON1A0980';
            $agent = $state['agentId'] ?? '1234AB';
            $status = ! empty($state['activePnr']['tickets']) ? 'TICKETED' : 'CONFIRMED';

            // Commit to database within transaction and row locking
            try {
                DB::transaction(function () use ($rloc, $userId, $isInstructor, $office, $status, $state) {
                    $existingPnr = Pnr::where('record_locator', $rloc)->lockForUpdate()->first();
                    if ($existingPnr && ! $isInstructor && $existingPnr->user_id !== $userId) {
                        throw new \RuntimeException("UNAUTHORIZED TO OVERWRITE RECORD LOCATOR: {$rloc}");
                    }

                    Pnr::updateOrCreate(
                        ['record_locator' => $rloc],
                        [
                            'user_id' => $existingPnr ? $existingPnr->user_id : $userId,
                            'office_id' => $office,
                            'status' => $status,
                            'data_json' => $state['activePnr'],
                        ]
                    );
                });
            } catch (\RuntimeException $re) {
                return new CommandExecutionResult([$re->getMessage()], $state, true, 'ET_UNAUTHORIZED');
            } catch (\Throwable $e) {
                // Ignore DB write failure
            }

            if ($input === 'ER') {
                return new CommandExecutionResult($this->pnr->formatPnrDisplay($state['activePnr']), $state, false, 'ER');
            }

            $firstPax = $state['activePnr']['passengers'][0];
            $firstSeg = $state['activePnr']['segments'][0];
            $fltPad = str_pad((string) $firstSeg['flightNumber'], 3, ' ', STR_PAD_LEFT);
            $etLines = [
                '--- RLR ---',
                '1.'.strtoupper($firstPax['lastName']).'/'.strtoupper($firstPax['firstName']),
                " 1  {$firstSeg['airlineCode']} {$fltPad} {$firstSeg['bookingClass']} {$firstSeg['departureDate']} {$firstSeg['departureAirport']}{$firstSeg['arrivalAirport']} HK{$firstSeg['seats']}  {$firstSeg['departureTime']} {$firstSeg['arrivalTime']}",
                "{$office}/{$agent}  {$rloc}",
            ];

            return new CommandExecutionResult($etLines, $state, false, 'ET');
        }

        // 38. IGNORE: IG, IR
        if ($input === 'IG') {
            $state['activePnr'] = $this->pnr->createEmptyPnr($state['officeId'] ?? 'LON1A0980', $state['agentId'] ?? '1234AB');

            return new CommandExecutionResult(['IGNORED'], $state, false, 'IG');
        }
        if ($input === 'IR') {
            if (! empty($state['activePnr']['recordLocator'])) {
                $dbPnr = Pnr::find($state['activePnr']['recordLocator']);
                if ($dbPnr && ($isInstructor || $dbPnr->user_id === $userId)) {
                    $state['activePnr'] = $dbPnr->data_json;

                    return new CommandExecutionResult($this->pnr->formatPnrDisplay($state['activePnr']), $state, false, 'IR');
                }
            }
            $state['activePnr'] = $this->pnr->createEmptyPnr($state['officeId'] ?? 'LON1A0980', $state['agentId'] ?? '1234AB');

            return new CommandExecutionResult(['IGNORED'], $state, false, 'IR');
        }

        // 39. RETRIEVE AIRLINE LOCATORS: RL
        if ($input === 'RL') {
            $lines = $this->pnr->formatAirlineLocators($state['activePnr']);

            return new CommandExecutionResult($lines, $state, false, 'RL');
        }

        // 40. RETRIEVE PNR: RT
        if ($input === 'RT') {
            return new CommandExecutionResult($this->pnr->formatPnrDisplay($state['activePnr']), $state, false, 'RT');
        }

        // RT<LINE> (from previous list)
        if (preg_match('/^RT(\d+)$/', $input, $rtLineM)) {
            $lineIdx = (int) $rtLineM[1];
            if (! empty($state['lastPnrList']) && isset($state['lastPnrList'][$lineIdx - 1])) {
                $state['activePnr'] = $state['lastPnrList'][$lineIdx - 1];

                return new CommandExecutionResult($this->pnr->formatPnrDisplay($state['activePnr']), $state, false, 'RT_LINE');
            }
        }

        if (str_starts_with($input, 'RT')) {
            $arg = trim(substr($input, 2));

            // RT/A (alphabetical index) or RT/AHMED (surname search)
            if (str_starts_with($arg, '/')) {
                $searchQuery = trim(substr($arg, 1));

                // Alphabetical index (e.g. RT/A)
                if (strlen($searchQuery) === 1) {
                    $matched = [];
                    $allPnrs = Pnr::orderBy('updated_at', 'desc')->limit(50)->get();
                    foreach ($allPnrs as $item) {
                        $p = $item->data_json;
                        if (! empty($p['passengers'])) {
                            foreach ($p['passengers'] as $pax) {
                                if (str_starts_with(strtoupper($pax['lastName']), $searchQuery)) {
                                    $matched[] = $p;
                                    break;
                                }
                            }
                        }
                    }

                    if (empty($matched)) {
                        $matched = [
                            [
                                'recordLocator' => 'A1B2C3',
                                'creationOffice' => 'LON1A0980',
                                'creationAgent' => '1234AB',
                                'passengers' => [['id' => '1.1', 'lastName' => 'AHMED', 'firstName' => 'TARIQ', 'title' => 'MR', 'type' => 'ADT']],
                                'segments' => [['segmentNumber' => 1, 'airlineCode' => 'QR', 'flightNumber' => '001', 'bookingClass' => 'Y', 'departureDate' => '15MAR', 'departureAirport' => 'DOH', 'arrivalAirport' => 'LHR', 'departureTime' => '0845', 'arrivalTime' => '1215', 'seats' => 1, 'status' => 'HK']],
                                'contacts' => [['id' => 1, 'type' => 'PHONE', 'detail' => 'DOH 4667579']],
                                'ticketing' => ['type' => 'TKOK', 'raw' => 'TKOK'],
                                'receivedFrom' => 'PASSENGER',
                            ],
                            [
                                'recordLocator' => 'A4B5C6',
                                'creationOffice' => 'LON1A0980',
                                'creationAgent' => '1234AB',
                                'passengers' => [['id' => '1.1', 'lastName' => 'AL-EMADI', 'firstName' => 'ALI', 'title' => 'MR', 'type' => 'ADT']],
                                'segments' => [['segmentNumber' => 1, 'airlineCode' => 'QR', 'flightNumber' => '003', 'bookingClass' => 'J', 'departureDate' => '18MAR', 'departureAirport' => 'DOH', 'arrivalAirport' => 'DXB', 'departureTime' => '1410', 'arrivalTime' => '1625', 'seats' => 1, 'status' => 'HK']],
                                'contacts' => [['id' => 1, 'type' => 'PHONE', 'detail' => 'DOH 4667579']],
                                'ticketing' => ['type' => 'TKOK', 'raw' => 'TKOK'],
                                'receivedFrom' => 'AGENT',
                            ],
                        ];
                    }

                    $state['lastPnrList'] = $matched;
                    $lines = [
                        "--- AMADEUS PNR INDEX : {$searchQuery} ---",
                        '-------------------------------------------------------------',
                    ];
                    foreach ($matched as $idx => $mPnr) {
                        $lPad = str_pad((string) ($idx + 1), 2, ' ', STR_PAD_LEFT);
                        $pax = $mPnr['passengers'][0];
                        $namePad = str_pad($pax['lastName'].'/'.$pax['firstName'].' '.($pax['title'] ?? ''), 22, ' ', STR_PAD_RIGHT);
                        $rloc = $mPnr['recordLocator'] ?? '------';
                        $date = $mPnr['segments'][0]['departureDate'] ?? '15MAR';
                        $off = $mPnr['creationOffice'] ?? 'LON1A0980';
                        $lines[] = " {$lPad} {$namePad} {$rloc}  {$date}  {$off}";
                    }
                    $lines[] = '-------------------------------------------------------------';
                    $lines[] = "USE 'RT<LINE>' (E.G. RT1) TO RETRIEVE";

                    return new CommandExecutionResult($lines, $state, false, 'RT_INDEX');
                }

                // Search by surname (e.g. RT/AHMED)
                $query = Pnr::orderBy('updated_at', 'desc');
                if (! $isInstructor && $userId !== 'guest') {
                    $query->where('user_id', $userId);
                }
                $all = $query->limit(50)->get();
                $matchedPnrs = [];

                foreach ($all as $item) {
                    $p = $item->data_json;
                    if (! empty($p['passengers'])) {
                        foreach ($p['passengers'] as $pax) {
                            if (strtoupper($pax['lastName']) === $searchQuery) {
                                $matchedPnrs[] = $p;
                                break;
                            }
                        }
                    }
                }

                if (count($matchedPnrs) === 1) {
                    $state['activePnr'] = $matchedPnrs[0];

                    return new CommandExecutionResult($this->pnr->formatPnrDisplay($state['activePnr']), $state, false, 'RT_SURNAME');
                }

                if (count($matchedPnrs) > 1) {
                    $state['lastPnrList'] = $matchedPnrs;
                    $lines = [
                        "--- AMADEUS PNR SEARCH : {$searchQuery} ---",
                        '-------------------------------------------------------------',
                    ];
                    foreach ($matchedPnrs as $idx => $mPnr) {
                        $lPad = str_pad((string) ($idx + 1), 2, ' ', STR_PAD_LEFT);
                        $pax = $mPnr['passengers'][0];
                        $namePad = str_pad($pax['lastName'].'/'.$pax['firstName'].' '.($pax['title'] ?? ''), 22, ' ', STR_PAD_RIGHT);
                        $rloc = $mPnr['recordLocator'] ?? '------';
                        $date = $mPnr['segments'][0]['departureDate'] ?? '15MAR';
                        $off = $mPnr['creationOffice'] ?? 'LON1A0980';
                        $lines[] = " {$lPad} {$namePad} {$rloc}  {$date}  {$off}";
                    }
                    $lines[] = '-------------------------------------------------------------';
                    $lines[] = "USE 'RT<LINE>' (E.G. RT1) TO RETRIEVE";

                    return new CommandExecutionResult($lines, $state, false, 'RT_SURNAME_LIST');
                }

                return new CommandExecutionResult(["NO PNR FOUND MATCHING SURNAME: {$searchQuery}"], $state, true, 'RT_NOT_FOUND');
            }

            // Direct locator lookup (e.g. RTXXXXXX)
            if (strlen($arg) === 6) {
                $dbPnr = Pnr::find($arg);
                if ($dbPnr) {
                    if (! $isInstructor && $dbPnr->user_id !== $userId && $userId !== 'guest') {
                        return new CommandExecutionResult(['ACCESS RESTRICTED: PNR BELONGS TO ANOTHER USER'], $state, true, 'RT_UNAUTHORIZED');
                    }
                    $state['activePnr'] = $dbPnr->data_json;

                    return new CommandExecutionResult($this->pnr->formatPnrDisplay($state['activePnr']), $state, false, 'RT_LOCATOR');
                }
            }

            return new CommandExecutionResult(["NO PNR FOUND MATCHING: {$arg}"], $state, true, 'RT_NOT_FOUND');
        }

        // 41. ISSUE TICKET: TTP
        if ($input === 'TTP') {
            if ($userId === 'guest') {
                return new CommandExecutionResult(['GUEST SESSION: LOGIN REQUIRED TO ISSUE ELECTRONIC TICKETS (TTP)'], $state, true, 'TTP_UNAUTHENTICATED');
            }

            if (! empty($state['activePnr']['recordLocator'])) {
                $existingPnr = Pnr::find($state['activePnr']['recordLocator']);
                if ($existingPnr && ! $isInstructor && $existingPnr->user_id !== $userId) {
                    return new CommandExecutionResult(["UNAUTHORIZED TO TICKET RECORD: {$state['activePnr']['recordLocator']}"], $state, true, 'TTP_UNAUTHORIZED');
                }
            }

            $res = $this->ticketing->issueTicketTTP($state['activePnr'], $state['officeId'] ?? 'LON1A0980');
            if ($res->isError) {
                return new CommandExecutionResult($res->lines, $state, true, 'TTP_ERROR');
            }

            if (! empty($res->tickets)) {
                $state['activePnr']['tickets'] = array_merge($state['activePnr']['tickets'] ?? [], $res->tickets);
                $state['activePnr']['ticketing'] = ['type' => 'TKOK', 'raw' => 'TKOK'];

                if (! empty($state['activePnr']['recordLocator'])) {
                    DB::transaction(function () use ($state) {
                        Pnr::where('record_locator', $state['activePnr']['recordLocator'])->lockForUpdate()->update([
                            'status' => 'TICKETED',
                            'data_json' => $state['activePnr'],
                        ]);
                    });
                }
            }

            return new CommandExecutionResult($res->lines, $state, false, 'TTP');
        }

        // 42. DISPLAY TICKET: TWD
        if (str_starts_with($input, 'TWD')) {
            $arg = trim(substr($input, 3));
            $res = $this->ticketing->displayTicketTWD($state['activePnr'], $arg ?: null);

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'TWD');
        }

        // 43. QUEUE COMMANDS: QT, QS, QS2C30D1, QE30, QD, QN, QI
        if ($input === 'QT' || $input === 'QS' || $input === 'QS/ALL') {
            if ($userId === 'guest') {
                return new CommandExecutionResult(['GUEST SESSION: LOGIN REQUIRED TO ACCESS QUEUES (QS/QT)'], $state, true, 'QUEUE_UNAUTHENTICATED');
            }

            $office = $state['officeId'] ?? 'LON1A0980';
            $lines = $this->queue->formatQueueSummaryLines($office, $userId);

            return new CommandExecutionResult($lines, $state, false, 'QUEUE_SUMMARY');
        }

        // QS2C30D1 (specific queue, category, date range)
        if (preg_match('/^QS(\d+)C(\d+)D(\d+)$/', $input, $qscM)) {
            if ($userId === 'guest') {
                return new CommandExecutionResult(['GUEST SESSION: LOGIN REQUIRED TO ACCESS QUEUES'], $state, true, 'QUEUE_UNAUTHENTICATED');
            }

            $office = $state['officeId'] ?? 'LON1A0980';
            $qNum = (int) $qscM[1];
            $cat = (int) $qscM[2];
            $dateRange = (int) $qscM[3];
            $lines = $this->queue->displaySpecificQueue($qNum, $cat, $dateRange, $office, $userId);

            return new CommandExecutionResult($lines, $state, false, 'QUEUE_SPECIFIC');
        }

        // QE<QNUM> (e.g. QE30 - Queue Enter)
        if (preg_match('/^QE(\d+)$/', $input, $qeM)) {
            $qNum = (int) $qeM[1];
            $office = $state['officeId'] ?? 'LON1A0980';
            $lines = $this->queue->placeOnQueue($qNum, $state['activePnr'], $office, $userId);

            return new CommandExecutionResult($lines, $state, false, 'QUEUE_ENTER');
        }

        if (str_starts_with($input, 'QD')) {
            if ($userId === 'guest') {
                return new CommandExecutionResult(['GUEST SESSION: LOGIN REQUIRED TO ACCESS QUEUES (QD)'], $state, true, 'QUEUE_UNAUTHENTICATED');
            }

            $office = $state['officeId'] ?? 'LON1A0980';
            $arg = trim(substr($input, 2));
            $qNum = is_numeric($arg) ? (int) $arg : 1;
            $res = $this->queue->displayQueue($qNum, $office, $userId);

            return new CommandExecutionResult($res['lines'], $state, false, 'QUEUE_DISPLAY');
        }

        if ($input === 'QN') {
            return new CommandExecutionResult(
                [
                    'QN - QUEUE NEXT ITEM',
                    'NO FURTHER PNRS REQUIRING IMMEDIATE ATTENTION IN ACTIVE QUEUE',
                ],
                $state,
                false,
                'QUEUE_NEXT'
            );
        }

        if ($input === 'QI') {
            return new CommandExecutionResult(
                [
                    'QI - EXITED QUEUE WORKING MODE',
                    'ACTIVE WORK AREA RETURNED TO GENERAL TERMINAL SESSION',
                ],
                $state,
                false,
                'QUEUE_EXIT'
            );
        }

        // UNRECOGNIZED COMMAND
        return new CommandExecutionResult(
            [
                "INVALID ENTRY: \"{$input}\"",
                'TYPE "HE" OR "HELP" FOR AMADEUS COMMAND REFERENCE',
            ],
            $state,
            true,
            'UNKNOWN_COMMAND'
        );
    }
}
