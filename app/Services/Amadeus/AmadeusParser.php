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
        protected QueueService $queue,
        protected HelpService $help,
        protected AisService $ais,
        protected TimaticService $timatic,
        protected MathDateService $mathDate,
        protected FlightInfoService $flightInfo,
        protected ProfileService $profile,
        protected HotelCarService $hotelCar
    ) {}

    public function execute(string $rawInput, array $currentState, string $userId = 'guest'): CommandExecutionResult
    {
        // Support semicolon-separated command chains (e.g. NM1HUSSAIN/A MR;NM1SYED/ALI MR)
        // Exception: DF calculation commands use semicolons for addition (e.g. DF 889; 1733; 669)
        if (str_contains($rawInput, ';') && ! preg_match('/^DF\s/i', trim($rawInput))) {
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

        // Bind office and agent identity to authenticated user so client state cannot spoof tenant identity
        if ($user) {
            if (! empty($user->office_id)) {
                $state['officeId'] = $user->office_id;
            }
            if (! empty($user->agent_id)) {
                $state['agentId'] = $user->agent_id;
            }
        }

        // Track last command for HE/
        $lastExecutedCmd = $state['lastCommand'] ?? null;
        $state['lastCommand'] = $input;

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
        if ($input === 'HE' || str_starts_with($input, 'HE ') || str_starts_with($input, 'HELP') || $input === 'HE/' || $input === 'HELP/' || $input === '?') {
            $res = $this->help->handleHelp($input, $lastExecutedCmd);

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'HELP');
        }

        // 2. SIGN IN / WORK AREA JUMP / SIGN OUT
        if (preg_match('/^JX([A-F])$/', $input, $jxM)) {
            $area = $jxM[1];
            $state['area'] = $area;
            $state['isSignedIn'] = true;
            $off = $state['officeId'] ?? 'LON1A0980';

            return new CommandExecutionResult(["OK SIGN-IN TO EXTENDED WORK AREA {$area} / OFFICE {$off}"], $state, false, 'JUMP_AREA');
        }

        if (preg_match('/^JM([A-F])$/', $input, $jmM)) {
            $area = $jmM[1];
            $state['area'] = $area;

            return new CommandExecutionResult(["JUMP MOVE TO WORK AREA {$area} CONFIRMED"], $state, false, 'JUMP_AREA');
        }

        if ($input === 'JD') {
            $cur = $state['area'] ?? 'A';
            $lines = [
                '--- AMADEUS WORK AREA STATUS (JD) ---',
                'AREA  STATUS    AGENT   CURRENT',
                '-----------------------------------',
                ' A    SIGNED-IN 1234AB  '.($cur === 'A' ? '*' : ' '),
                ' B    SIGNED-IN 1234AB  '.($cur === 'B' ? '*' : ' '),
                ' C    SIGNED-IN 1234AB  '.($cur === 'C' ? '*' : ' '),
                ' D    AVAILABLE         '.($cur === 'D' ? '*' : ' '),
                ' E    AVAILABLE         '.($cur === 'E' ? '*' : ' '),
                ' F    AVAILABLE         '.($cur === 'F' ? '*' : ' '),
            ];

            return new CommandExecutionResult($lines, $state, false, 'JUMP_DISPLAY');
        }

        if ($input === 'JB') {
            return new CommandExecutionResult([
                'GOOD DAY - AMADEUS CENTRAL RESERVATION SYSTEM ACTIVE',
                'SYSTEM TIME : '.gmdate('dM Hi').'Z / CENTRAL PROCESSOR NORMAL',
                'OFFICE      : '.($state['officeId'] ?? 'LON1A0980').' / AGENT: '.($state['agentId'] ?? '1234AB'),
            ], $state, false, 'SIGN_BROADCAST');
        }

        if (str_starts_with($input, 'JI') || $input === 'SI') {
            $state['isSignedIn'] = true;
            $allAreas = str_starts_with($input, 'JI*');
            $rest = ltrim(substr($input, $allAreas ? 3 : 2), '* ');

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

        if (preg_match('/^JO([A-F])$/', $input, $joAreaM)) {
            $area = $joAreaM[1];

            return new CommandExecutionResult(["SIGNED OUT OF SPECIFIC WORK AREA {$area}"], $state, false, 'SIGN_OUT');
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

        // 3. AIS & OFFICE BRIEFING: GG..., GD, GMD...
        if (str_starts_with($input, 'GG') || $input === 'GD' || str_starts_with($input, 'GMD')) {
            $res = $this->ais->handle($input, $state);

            return new CommandExecutionResult($res->lines, $res->updatedState ?: $state, $res->isError, 'AIS');
        }

        // 4. TIMATIC: TI...
        if ($input === 'TIFV' || $input === 'TIFH' || $input === 'TIFA' || $input === 'TINEWS' || $input === 'TIRULES' || $input === 'TIRGL' || str_starts_with($input, 'TIRV') || str_starts_with($input, 'TILCC') || str_starts_with($input, 'TIDFT') || str_starts_with($input, 'TIRGL/')) {
            $res = $this->timatic->handle($input, $state);

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'TIMATIC');
        }

        // 5. MATH & DATE CONVERSIONS: DF, DD, D<DATE>
        if (str_starts_with($input, 'DF')) {
            $res = $this->mathDate->handleDF($input);

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'MATH_DF');
        }

        if (str_starts_with($input, 'DD') || preg_match('/^D\d{1,2}[A-Z]{3}/', $input)) {
            $res = $this->mathDate->handleDD($input);

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'DATE_DD');
        }

        // 6. ENCODE & DECODE: DAN, DAC, DNA, DNE, DC, DNH, DNC, DNS, DNN, CE
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
        if (str_starts_with($input, 'DC')) {
            $res = $this->decode->handleDC(substr($input, 2));

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'DECODE_DC');
        }
        if (str_starts_with($input, 'DNH')) {
            $res = $this->decode->handleDNH(substr($input, 3));

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'DECODE_DNH');
        }
        if (str_starts_with($input, 'DNC')) {
            $res = $this->decode->handleDNC(substr($input, 3));

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'DECODE_DNC');
        }
        if (str_starts_with($input, 'DNS')) {
            $res = $this->decode->handleDNS(substr($input, 3));

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'DECODE_DNS');
        }
        if (str_starts_with($input, 'DNN')) {
            $res = $this->decode->handleDNN(substr($input, 3));

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'DECODE_DNN');
        }
        if (str_starts_with($input, 'CE')) {
            $res = $this->decode->handleCE(substr($input, 2));

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'DECODE_CE');
        }

        // 7. FLIGHT INFO, MCT, CONNECT POINTS: DO, DM, DMI, DRT
        if (str_starts_with($input, 'DO')) {
            $res = $this->flightInfo->handleDO($input, $state);

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'FLIGHT_INFO_DO');
        }
        if (str_starts_with($input, 'DM')) {
            $res = $this->flightInfo->handleDM($input, $state);

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'MCT_DM');
        }
        if (str_starts_with($input, 'DRT')) {
            $res = $this->flightInfo->handleDRT($input);

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'CONNECT_POINTS_DRT');
        }

        // 8. HOTEL & CAR RESERVATIONS
        if (preg_match('/^(HL|HA|HS|HF|HP|HPR|HRC|HT|HR|CL|CA|CS|CR)/', $input)) {
            $res = $this->hotelCar->handle($input, $state);

            return new CommandExecutionResult($res->lines, $res->updatedState, $res->isError, 'HOTEL_CAR');
        }

        // 9. CUSTOMER PROFILES: PM, PME, PEE, PI, PER, PD, PT*, PT, PCN/, PIN/, PFO/, PDN/, PDI/, PC/-1, PLT, or NM in profile mode
        if ($input === 'PM' || $input === 'PME' || $input === 'PEE' || $input === 'PI' || $input === 'PER' || $input === 'PD' || $input === 'PT*' || $input === 'PT' || str_starts_with($input, 'PCN/') || str_starts_with($input, 'PIN/') || str_starts_with($input, 'PFO/') || str_starts_with($input, 'PDN/') || str_starts_with($input, 'PDI/') || str_starts_with($input, 'PC/') || str_starts_with($input, 'PLT') || (! empty($state['profileMode']) && str_starts_with($input, 'NM'))) {
            $res = $this->profile->handle($input, $state);

            return new CommandExecutionResult($res->lines, $res->updatedState, $res->isError, 'PROFILES');
        }

        // 10. AVAILABILITY CHANGE ENTRIES: ACW, ACL, AC FQ, AC SN, AC..., ACR...
        if (str_starts_with($input, 'ACW')) {
            return new CommandExecutionResult([
                '--- TICKETLESS ACCESS DISPLAY (AIR ASIA / LCC) ---',
                '01 AK  623  V4  KULTRZ  0830  1000  A320  TICKETLESS CONFIRMED',
                'DIRECT SELL ACTIVE VIA SS1V1',
            ], $state, false, 'TICKETLESS_DISPLAY');
        }

        if (str_starts_with($input, 'ACL')) {
            $line = substr($input, 3);

            return new CommandExecutionResult([
                "ACL{$line} - CONVERTED TO DIRECT ACCESS LINKED DISPLAY",
                'LAST-SEAT AVAILABILITY OBTAINED IN REAL-TIME FROM AIRLINE INVENTORY',
            ], $state, false, 'DIRECT_ACCESS_AVAILABILITY');
        }

        if ($input === 'AC FQ') {
            $orig = $state['lastAvailabilityDisplay']['origin'] ?? 'DEL';
            $dest = $state['lastAvailabilityDisplay']['destination'] ?? 'LON';
            $date = $state['lastAvailabilityDisplay']['date'] ?? '15NOV';
            $res = $this->pricing->handleFQD("{$orig}{$dest}/D{$date}");

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'FARE_DISPLAY');
        }

        if ($input === 'AC SN') {
            $orig = $state['lastAvailabilityDisplay']['origin'] ?? 'DEL';
            $dest = $state['lastAvailabilityDisplay']['destination'] ?? 'LON';
            $date = $state['lastAvailabilityDisplay']['date'] ?? '15DEC';
            $res = $this->availability->search("{$date}{$orig}{$dest}", 'SN');

            return new CommandExecutionResult(array_merge([$res->headerLine], $res->outputLines), $state, $res->isError, 'SCHEDULE_DISPLAY');
        }

        if (str_starts_with($input, 'AC') || str_starts_with($input, 'ACR')) {
            $res = $this->availability->applyChange($input, $state['lastAvailabilityDisplay'] ?? null);
            if ($res->isError) {
                return new CommandExecutionResult($res->outputLines, $state, true, 'AVAILABILITY_ERROR');
            }

            $state['lastAvailabilityDisplay'] = [
                'origin' => $res->origin,
                'destination' => $res->destination,
                'date' => $res->date,
                'returnDate' => $res->returnDate,
                'carrier' => $res->carrier,
                'lines' => $res->schedules,
            ];

            return new CommandExecutionResult(
                array_merge([$res->headerLine], $res->outputLines),
                $state,
                false,
                'AVAILABILITY'
            );
        }

        // 11. FLIGHT AVAILABILITY & TIMETABLES: AN, SN, TN, AA
        if (str_starts_with($input, 'AN') || str_starts_with($input, 'SN') || str_starts_with($input, 'TN') || str_starts_with($input, 'AA')) {
            $dispType = 'AN';
            $arg = substr($input, 2);
            if (str_starts_with($input, 'SN')) {
                $dispType = 'SN';
            } elseif (str_starts_with($input, 'TN')) {
                $dispType = 'SN';
            } elseif (str_starts_with($input, 'AA')) {
                $dispType = 'AA';
            }

            $res = $this->availability->search($arg, $dispType);
            if ($res->isError) {
                return new CommandExecutionResult($res->outputLines, $state, true, 'AVAILABILITY_ERROR');
            }

            $state['lastAvailabilityDisplay'] = [
                'origin' => $res->origin,
                'destination' => $res->destination,
                'date' => $res->date,
                'returnDate' => $res->returnDate,
                'carrier' => $res->carrier,
                'lines' => $res->schedules,
            ];

            return new CommandExecutionResult(
                array_merge([$res->headerLine], $res->outputLines),
                $state,
                false,
                $dispType === 'SN' ? 'SCHEDULE_DISPLAY' : 'AVAILABILITY'
            );
        }

        // 12. PAGING / SCROLLING (MU, MD, MT, MB, MN, MY, MUF, MDF, MNF, MYF, MUS, MDS, MNS, MYS)
        if (in_array($input, ['MD', 'MU', 'MT', 'MB', 'MN', 'MY', 'MUF', 'MDF', 'MNF', 'MYF', 'MUS', 'MDS', 'MNS', 'MYS'], true)) {
            if (empty($state['lastAvailabilityDisplay'])) {
                return new CommandExecutionResult(['NO PREVIOUS AVAILABILITY DISPLAY TO PAGE'], $state, true, 'PAGING');
            }

            // Next day / yesterday shift
            if ($input === 'MN' || $input === 'MNF' || $input === 'MNS') {
                $res = $this->availability->applyChange('AC 1', $state['lastAvailabilityDisplay']);
                $state['lastAvailabilityDisplay']['lines'] = $res->schedules;
                $state['lastAvailabilityDisplay']['date'] = $res->date;

                return new CommandExecutionResult(array_merge([$res->headerLine], $res->outputLines), $state, false, 'AVAILABILITY');
            }
            if ($input === 'MY' || $input === 'MYF' || $input === 'MYS') {
                $res = $this->availability->applyChange('AC -1', $state['lastAvailabilityDisplay']);
                $state['lastAvailabilityDisplay']['lines'] = $res->schedules;
                $state['lastAvailabilityDisplay']['date'] = $res->date;

                return new CommandExecutionResult(array_merge([$res->headerLine], $res->outputLines), $state, false, 'AVAILABILITY');
            }

            $pageStr = in_array($input, ['MD', 'MDF', 'MDS'], true) ? '2 OF 2' : '1 OF 2';

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

        // 13. DIRECT ACCESS (1XX): 19WAD..., 19W MN/MY/MD/MU, 1AIRT..., 19WFQD...
        if (preg_match('/^1([A-Z0-9]{2})AD(.*)$/', $input, $daM)) {
            $carrier = $daM[1];
            $res = $this->availability->search($daM[2]."/A{$carrier}", 'AN');
            $lines = [
                "--- DIRECT ACCESS LINK ACTIVE: {$carrier} (1{$carrier}AD) ---",
                'SEATS SOLD IN LK STATUS (HOLDING CONFIRMED)',
            ];

            return new CommandExecutionResult(array_merge($lines, $res->outputLines), $state, false, 'DIRECT_ACCESS_AVAILABILITY');
        }

        if (preg_match('/^1([A-Z0-9]{2})\s+(MN|MY|MD|MU)$/', $input, $dasM)) {
            return new CommandExecutionResult([
                "1{$dasM[1]} {$dasM[2]} - DIRECT ACCESS PAGE UPDATED",
                'DISPLAYING NEXT FLIGHTS DIRECT FROM AIRLINE INVENTORY',
            ], $state, false, 'DIRECT_ACCESS_SCROLL');
        }

        if (preg_match('/^1([A-Z0-9]{2})RT\s+([A-Z0-9]+)$/', $input, $dartM)) {
            return new CommandExecutionResult([
                "--- 1{$dartM[1]} DIRECT ACCESS PNR RETRIEVAL: {$dartM[2]} ---",
                "HOST RECORD LOCATOR : {$dartM[2]} (AIRLINE DIRECT)",
                'STATUS              : CONFIRMED (HK)',
            ], $state, false, 'DIRECT_ACCESS_PNR');
        }

        if (preg_match('/^1([A-Z0-9]{2})FQD(.*)$/', $input, $dafqM)) {
            $res = $this->pricing->handleFQD($dafqM[2]."/A{$dafqM[1]}");

            return new CommandExecutionResult(array_merge(["--- 1{$dafqM[1]} DIRECT ACCESS FARES ---"], $res->lines), $state, false, 'DIRECT_ACCESS_FQD');
        }

        // 14. SURFACE SEGMENT: SIARNK / SI ARNK / ARNK
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

        // 15. OPEN SEGMENT: SO <CARRIER> <CLASS> <DATE><ORIG><DEST>
        if (str_starts_with($input, 'SO')) {
            $rest = trim(substr($input, 2));

            // SO QR B LHRDOH or SOLHCFRABOM or SOLHC24DECFRABOM or SOYYCFRABOM
            if (preg_match('/^([A-Z0-9]{2})\s*([A-Z])\s*(\d{1,2}[A-Z]{3})?([A-Z]{3})\s*([A-Z]{3})?$/', $rest, $om)) {
                $carrier = $om[1];
                $bClass = $om[2];
                $date = ! empty($om[3]) ? $om[3] : 'OPEN';
                $orig = $om[4];
                $dest = $om[5] ?? 'BOM';
                $segNum = count($state['activePnr']['segments'] ?? []) + 1;

                $newSegment = [
                    'segmentNumber' => $segNum,
                    'airlineCode' => $carrier,
                    'flightNumber' => 'OPEN',
                    'bookingClass' => $bClass,
                    'departureAirport' => $orig,
                    'arrivalAirport' => $dest,
                    'departureDate' => $date,
                    'departureTime' => 'OPEN',
                    'arrivalTime' => 'OPEN',
                    'status' => 'OPEN',
                    'seats' => 1,
                    'equipment' => 'OPEN',
                ];
                $state['activePnr']['segments'][] = $newSegment;

                return new CommandExecutionResult(
                    [" {$segNum}  {$carrier} OPEN {$bClass}       {$orig}{$dest} {$date}    OPEN OPEN   *1A/E*"],
                    $state,
                    false,
                    'SELL_OPEN_SEGMENT'
                );
            }
        }

        // 16. INFORMATION SEGMENT (SI): SISV219Y20MAYJEDISTHK1/0815 1645
        if (preg_match('/^SI([A-Z0-9]{2})(\d{1,4})([A-Z])(\d{1,2}[A-Z]{3})([A-Z]{3})([A-Z]{3})HK(\d+)\/(\d{4})\s*(\d{4})$/', $input, $sim)) {
            $segNum = count($state['activePnr']['segments'] ?? []) + 1;
            $newSegment = [
                'segmentNumber' => $segNum,
                'airlineCode' => $sim[1],
                'flightNumber' => $sim[2],
                'bookingClass' => $sim[3],
                'departureDate' => $sim[4],
                'departureAirport' => $sim[5],
                'arrivalAirport' => $sim[6],
                'departureTime' => $sim[8],
                'arrivalTime' => $sim[9],
                'status' => 'HK',
                'seats' => (int) $sim[7],
                'equipment' => '77W',
                'isInfo' => true,
            ];
            $state['activePnr']['segments'][] = $newSegment;

            return new CommandExecutionResult([
                " {$segNum}  {$sim[1]} {$sim[2]} {$sim[3]} {$sim[4]} {$sim[5]}{$sim[6]} HK{$sim[7]}  {$sim[8]} {$sim[9]} (INFO ONLY)",
            ], $state, false, 'INFO_SEGMENT');
        }

        // 17. GHOST / PASSIVE SEGMENT: SSSQ401Y20DECDELSINGK1/YKABH
        if (preg_match('/^SS([A-Z0-9]{2})(\d{1,4})([A-Z])(\d{1,2}[A-Z]{3})([A-Z]{3})([A-Z]{3})(GK|GL)(\d+)\/([A-Z0-9]+)$/', $input, $gsm)) {
            $segNum = count($state['activePnr']['segments'] ?? []) + 1;
            $newSegment = [
                'segmentNumber' => $segNum,
                'airlineCode' => $gsm[1],
                'flightNumber' => $gsm[2],
                'bookingClass' => $gsm[3],
                'departureDate' => $gsm[4],
                'departureAirport' => $gsm[5],
                'arrivalAirport' => $gsm[6],
                'departureTime' => '1000',
                'arrivalTime' => '1530',
                'status' => $gsm[7],
                'seats' => (int) $gsm[8],
                'equipment' => '77W',
                'passiveLocator' => $gsm[9],
            ];
            $state['activePnr']['segments'][] = $newSegment;

            return new CommandExecutionResult([
                " {$segNum}  {$gsm[1]} {$gsm[2]} {$gsm[3]} {$gsm[4]} {$gsm[5]}{$gsm[6]} {$gsm[7]}{$gsm[8]}  1000 1530  /{$gsm[9]}",
            ], $state, false, 'GHOST_SEGMENT');
        }

        // 18. LONG SELL WITH STG... : STG316M03AUGDELBKK1 or STG316C03AUGDELBKKPE1
        if (preg_match('/^S([A-Z0-9]{2})(\d{1,4})([A-Z])(\d{1,2}[A-Z]{3})([A-Z]{3})([A-Z]{3})(PE)?(\d+)$/', $input, $stgM)) {
            $carrier = $stgM[1];
            $flt = $stgM[2];
            $bClass = $stgM[3];
            $date = $stgM[4];
            $orig = $stgM[5];
            $dest = $stgM[6];
            $isWaitlist = ! empty($stgM[7]);
            $seats = (int) $stgM[8];
            $status = $isWaitlist ? 'PE' : 'HK';

            $segNum = count($state['activePnr']['segments'] ?? []) + 1;
            $state['activePnr']['segments'][] = [
                'segmentNumber' => $segNum,
                'airlineCode' => $carrier,
                'flightNumber' => $flt,
                'bookingClass' => $bClass,
                'departureDate' => $date,
                'departureAirport' => $orig,
                'arrivalAirport' => $dest,
                'departureTime' => '0930',
                'arrivalTime' => '1445',
                'status' => $status,
                'seats' => $seats,
                'equipment' => '77W',
            ];

            return new CommandExecutionResult([
                " {$segNum}  {$carrier} {$flt} {$bClass} {$date} {$orig}{$dest} {$status}{$seats}  0930 1445   *1A/E*",
            ], $state, false, 'LONG_SELL');
        }

        // 19. DIRECT / LONG SELL & PASSIVE SEGMENTS:
        // Format: SS<AIR><FLT><CLS><DATE><ORIG><DEST><STATUS><SEATS>[/<AIR>]
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

        // 20. ROUND-TRIP INBOUND / OUTBOUND SELL: SS2V1*V13 or SS1Y1*F13
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
                return new CommandExecutionResult(["LINE NUMBER {$line1Num} NOT FOUND IN DISPLAY"], $state, true, 'SELL_SEAT_ERROR');
            }
            if (! $line2) {
                return new CommandExecutionResult(["LINE NUMBER {$line2Num} NOT FOUND IN DISPLAY"], $state, true, 'SELL_SEAT_ERROR');
            }

            $s1 = $line1['schedule'];
            $s2 = $line2['schedule'];

            $segNum1 = count($state['activePnr']['segments'] ?? []) + 1;
            $segNum2 = $segNum1 + 1;

            $state['activePnr']['segments'][] = [
                'segmentNumber' => $segNum1,
                'airlineCode' => $s1['airlineCode'],
                'flightNumber' => $s1['flightNumber'],
                'bookingClass' => $class1,
                'departureDate' => $line1['departureDate'],
                'departureAirport' => $s1['departureAirport'],
                'arrivalAirport' => $s1['arrivalAirport'],
                'departureTime' => $s1['departureTime'],
                'arrivalTime' => $s1['arrivalTime'],
                'status' => 'HK',
                'seats' => $seats,
                'equipment' => $s1['equipment'],
            ];

            $state['activePnr']['segments'][] = [
                'segmentNumber' => $segNum2,
                'airlineCode' => $s2['airlineCode'],
                'flightNumber' => $s2['flightNumber'],
                'bookingClass' => $class2,
                'departureDate' => $line2['departureDate'],
                'departureAirport' => $s2['departureAirport'],
                'arrivalAirport' => $s2['arrivalAirport'],
                'departureTime' => $s2['departureTime'],
                'arrivalTime' => $s2['arrivalTime'],
                'status' => 'HK',
                'seats' => $seats,
                'equipment' => $s2['equipment'],
            ];

            $out1 = " {$segNum1}  {$s1['airlineCode']} ".str_pad($s1['flightNumber'], 3, ' ', STR_PAD_LEFT)." {$class1} {$line1['departureDate']} {$s1['departureAirport']}{$s1['arrivalAirport']} HK{$seats}  {$s1['departureTime']} {$s1['arrivalTime']}   *1A/E*";
            $out2 = " {$segNum2}  {$s2['airlineCode']} ".str_pad($s2['flightNumber'], 3, ' ', STR_PAD_LEFT)." {$class2} {$line2['departureDate']} {$s2['departureAirport']}{$s2['arrivalAirport']} HK{$seats}  {$s2['departureTime']} {$s2['arrivalTime']}   *1A/E*";

            return new CommandExecutionResult([$out1, $out2], $state, false, 'SELL_ROUNDTRIP');
        }

        // 21. MULTI-LEG SELL: SS2JF3
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
                return new CommandExecutionResult(["LINE NUMBER {$lineNum} NOT FOUND IN DISPLAY"], $state, true, 'SELL_SEAT_ERROR');
            }

            $sched = $selectedLine['schedule'];
            $createdLines = [];

            foreach ($classes as $idx => $cls) {
                $segNum = count($state['activePnr']['segments'] ?? []) + 1;
                $state['activePnr']['segments'][] = [
                    'segmentNumber' => $segNum,
                    'airlineCode' => $sched['airlineCode'],
                    'flightNumber' => (string) ((int) $sched['flightNumber'] + $idx),
                    'bookingClass' => $cls,
                    'departureDate' => $selectedLine['departureDate'],
                    'departureAirport' => ($idx === 0) ? $sched['departureAirport'] : 'AUH',
                    'arrivalAirport' => ($idx === 0) ? 'AUH' : $sched['arrivalAirport'],
                    'departureTime' => $sched['departureTime'],
                    'arrivalTime' => $sched['arrivalTime'],
                    'status' => 'HK',
                    'seats' => $seats,
                    'equipment' => $sched['equipment'],
                ];

                $fltStr = str_pad((string) ((int) $sched['flightNumber'] + $idx), 3, ' ', STR_PAD_LEFT);
                $origStr = ($idx === 0) ? $sched['departureAirport'] : 'AUH';
                $destStr = ($idx === 0) ? 'AUH' : $sched['arrivalAirport'];
                $createdLines[] = " {$segNum}  {$sched['airlineCode']} {$fltStr} {$cls} {$selectedLine['departureDate']} {$origStr}{$destStr} HK{$seats}  {$sched['departureTime']} {$sched['arrivalTime']}   *1A/E*";
            }

            return new CommandExecutionResult($createdLines, $state, false, 'SELL_MULTI_LEG');
        }

        // 22. STANDARD & VARIATION SELLS:
        // SS1Y3, SS1Y1/PE (Waitlist), SS1K21 (Direct access), SS2C1/P1,3 (Pax assoc), SS1C3 /UM5 (UMNR), SS1Y1/ST (Stretcher)
        if (str_starts_with($input, 'SS')) {
            // Check for modifier tags (/PE, /ST, /UM<age>, /P<pax>)
            $statusToUse = 'HK';
            $isWaitlist = false;
            $isStretcher = false;
            $umnrAge = null;
            $paxAssoc = null;

            $baseInput = $input;
            if (str_contains($input, '/PE')) {
                $statusToUse = 'PE';
                $isWaitlist = true;
                $baseInput = str_replace('/PE', '', $baseInput);
            }
            if (str_contains($input, '/ST')) {
                $isStretcher = true;
                $baseInput = str_replace('/ST', '', $baseInput);
            }
            if (preg_match('/\/UM(\d+)/', $input, $umM)) {
                $umnrAge = $umM[1];
                $baseInput = preg_replace('/\/UM\d+/', '', $baseInput);
            }
            if (preg_match('/\/P([0-9,]+)/', $input, $pM)) {
                $paxAssoc = $pM[1];
                $baseInput = preg_replace('/\/P[0-9,]+/', '', $baseInput);
            }

            $baseInput = trim(preg_replace('/\s+/', '', $baseInput));

            if (preg_match('/^SS(\d+)([A-Z])(\d+)$/', $baseInput, $match)) {
                $seats = (int) $match[1];
                $class = $match[2];
                $lineNumber = (int) $match[3];

                if (empty($state['lastAvailabilityDisplay']['lines'])) {
                    // Fallback create flight segment directly
                    $segNum = count($state['activePnr']['segments'] ?? []) + 1;
                    $state['activePnr']['segments'][] = [
                        'segmentNumber' => $segNum,
                        'airlineCode' => 'BA',
                        'flightNumber' => '117',
                        'bookingClass' => $class,
                        'departureDate' => '15DEC',
                        'departureAirport' => 'LHR',
                        'arrivalAirport' => 'JFK',
                        'departureTime' => '0915',
                        'arrivalTime' => '1230',
                        'status' => $statusToUse,
                        'seats' => $seats,
                        'equipment' => '77W',
                    ];

                    $out = " {$segNum}  BA 117 {$class} 15DEC LHRJFK {$statusToUse}{$seats}  0915 1230   *1A/E*";

                    return new CommandExecutionResult([$out], $state, false, 'SELL_SEAT');
                }

                $selectedLine = null;
                foreach ($state['lastAvailabilityDisplay']['lines'] as $line) {
                    if ($line['lineNumber'] === $lineNumber) {
                        $selectedLine = $line;
                        break;
                    }
                }

                if (! $selectedLine) {
                    return new CommandExecutionResult(["LINE NUMBER {$lineNumber} NOT FOUND IN DISPLAY"], $state, true, 'SELL_SEAT_ERROR');
                }

                $sched = $selectedLine['schedule'];
                $segNum = count($state['activePnr']['segments'] ?? []) + 1;

                $newSegment = [
                    'segmentNumber' => $segNum,
                    'airlineCode' => $sched['airlineCode'],
                    'flightNumber' => $sched['flightNumber'],
                    'bookingClass' => $class,
                    'departureDate' => $selectedLine['departureDate'],
                    'departureAirport' => $sched['departureAirport'],
                    'arrivalAirport' => $sched['arrivalAirport'],
                    'departureTime' => $sched['departureTime'],
                    'arrivalTime' => $sched['arrivalTime'],
                    'status' => $statusToUse,
                    'seats' => $seats,
                    'equipment' => $sched['equipment'],
                    'passengerAssociation' => $paxAssoc,
                ];

                $state['activePnr']['segments'][] = $newSegment;

                // Add UMNR or STCR SSR if requested
                if ($umnrAge) {
                    $state['activePnr']['specialServiceRequests'][] = [
                        'type' => 'SSR',
                        'code' => 'UMNR',
                        'text' => "UMNR {$sched['airlineCode']} HK1/AGE{$umnrAge}",
                    ];
                }
                if ($isStretcher) {
                    $state['activePnr']['specialServiceRequests'][] = [
                        'type' => 'SSR',
                        'code' => 'STCR',
                        'text' => "STCR {$sched['airlineCode']} HK1/STRETCHER PASSENGER",
                    ];
                }

                $fltStr = str_pad($sched['flightNumber'], 3, ' ', STR_PAD_LEFT);
                $lineResponse = " {$segNum}  {$sched['airlineCode']} {$fltStr} {$class} {$selectedLine['departureDate']} {$sched['departureAirport']}{$sched['arrivalAirport']} {$statusToUse}{$seats}  {$sched['departureTime']} {$sched['arrivalTime']}   *1A/E*";

                return new CommandExecutionResult([$lineResponse], $state, false, 'SELL_SEAT');
            }
        }

        // 23. PASSENGER NAME MODIFICATIONS (2/, 3/(CHD), 4/, 5/(INF), 6/NAME)
        if (preg_match('/^(\d+)\/(.*)$/', $input, $modMatch)) {
            $paxIdx = (int) $modMatch[1];
            $modifier = trim($modMatch[2]);
            $paxList = &$state['activePnr']['passengers'];

            if ($paxIdx >= 1 && $paxIdx <= count($paxList)) {
                $targetPax = &$paxList[$paxIdx - 1];

                // 23a. 2/ or 4/ : Clear infant or remove CHD title
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

                // 23b. 3/(CHD) : Add Child Title
                if ($modifier === '(CHD)') {
                    $targetPax['type'] = 'CHD';
                    $titleStr = ! empty($targetPax['title']) ? " {$targetPax['title']}" : '';
                    $disp = "  {$targetPax['id']}.{$targetPax['lastName']}/{$targetPax['firstName']}{$titleStr}(CHD)";

                    return new CommandExecutionResult([$disp], $state, false, 'PAX_MODIFIED');
                }

                // 23c. 5/(INF/<NAME>) : Add infant
                if (preg_match('/^\(INF\/([A-Z]+)\)$/', $modifier, $infM)) {
                    $targetPax['infant'] = [
                        'firstName' => $infM[1],
                        'lastName' => $targetPax['lastName'],
                    ];
                    $titleStr = ! empty($targetPax['title']) ? " {$targetPax['title']}" : '';
                    $disp = "  {$targetPax['id']}.{$targetPax['lastName']}/{$targetPax['firstName']}{$titleStr}(INF/{$infM[1]})";

                    return new CommandExecutionResult([$disp], $state, false, 'PAX_MODIFIED');
                }

                // 23d. 6/MARIYUM MRS : Update name/title
                if (preg_match('/^([A-Z]+)(?:\s+([A-Z]+))?$/', $modifier, $nmM)) {
                    $targetPax['firstName'] = $nmM[1];
                    $targetPax['title'] = $nmM[2] ?? '';
                    $titleStr = ! empty($targetPax['title']) ? " {$targetPax['title']}" : '';
                    $disp = "  {$targetPax['id']}.{$targetPax['lastName']}/{$targetPax['firstName']}{$titleStr}";

                    return new CommandExecutionResult([$disp], $state, false, 'PAX_MODIFIED');
                }
            }
        }

        // 24. PASSENGER NAMES: NM...
        if (str_starts_with($input, 'NM')) {
            $rawName = trim(substr($input, 2));

            // Support multiple names entered in one command line:
            // e.g. NM1SEN/JAYA MS 1SETH/RADHA MS
            $nameTokens = preg_split('/\s+(?=\d+[A-Z]+)/', $rawName);
            if (count($nameTokens) > 1) {
                $accumulated = [];
                foreach ($nameTokens as $token) {
                    $res = $this->executeSingle("NM{$token}", $state, $userId);
                    $accumulated = array_merge($accumulated, $res->outputLines);
                    $state = $res->updatedState;
                }

                return new CommandExecutionResult($accumulated, $state, false, 'NAME_ELEMENT');
            }

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

            // NM1<SURNAME>/<FIRSTNAME> [TITLE](INF<SURNAME>/<FIRSTNAME>[/<DOB>]) - Infant with different surname
            if (preg_match('/^1([A-Z]+)\/([A-Z]+)(?:\s+([A-Z]+))?\(INF([A-Z]+)\/([A-Z]+)(?:\/(\d{1,2}[A-Z]{3}\d{2}))?\)$/', $rawName, $diffInfM)) {
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
                        'dateOfBirth' => $diffInfM[6] ?? null,
                    ],
                ];
                $titleStr = $title ? " {$title}" : '';
                $dobStr = ! empty($diffInfM[6]) ? "/{$diffInfM[6]}" : '';

                return new CommandExecutionResult(["  {$pId}.{$diffInfM[1]}/{$diffInfM[2]}{$titleStr}(INF{$diffInfM[4]}/{$diffInfM[5]}{$dobStr})"], $state, false, 'NAME_ELEMENT');
            }

            // NM1<SURNAME>/<FIRSTNAME> [TITLE](INF/<FIRSTNAME>[/<DOB>]) - Infant with same surname
            if (preg_match('/^1([A-Z]+)\/([A-Z]+)(?:\s+([A-Z]+))?\(INF\/([A-Z]+)(?:\/(\d{1,2}[A-Z]{3}\d{2}))?\)$/', $rawName, $sameInfM)) {
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
                        'dateOfBirth' => $sameInfM[5] ?? null,
                    ],
                ];
                $titleStr = $title ? " {$title}" : '';
                $dobStr = ! empty($sameInfM[5]) ? "/{$sameInfM[5]}" : '';

                return new CommandExecutionResult(["  {$pId}.{$sameInfM[1]}/{$sameInfM[2]}{$titleStr}(INF/{$sameInfM[4]}{$dobStr})"], $state, false, 'NAME_ELEMENT');
            }

            // NM1<SURNAME>/<FIRSTNAME> [TITLE](INS) - Infant requiring a seat
            if (preg_match('/^1([A-Z]+)\/([A-Z]+)(?:\s+([A-Z]+))?\(INS\)$/', $rawName, $insM)) {
                $pId = (count($state['activePnr']['passengers']) + 1).'.1';
                $state['activePnr']['passengers'][] = [
                    'id' => $pId,
                    'type' => 'INS',
                    'lastName' => $insM[1],
                    'firstName' => $insM[2],
                    'title' => 'INF',
                ];

                return new CommandExecutionResult(["  {$pId}.{$insM[1]}/{$insM[2]}(INS)"], $state, false, 'NAME_ELEMENT');
            }

            // NM1<SURNAME>/<FIRSTNAME> [TITLE](IDSTCR) - Stretcher passenger
            if (preg_match('/^1([A-Z]+)\/([A-Z]+)(?:\s+([A-Z]+))?\s*\(IDSTCR\)$/', $rawName, $stcrM)) {
                $pId = (count($state['activePnr']['passengers']) + 1).'.1';
                $title = $stcrM[3] ?? 'MR';
                $state['activePnr']['passengers'][] = [
                    'id' => $pId,
                    'type' => 'STCR',
                    'lastName' => $stcrM[1],
                    'firstName' => $stcrM[2],
                    'title' => $title,
                ];

                return new CommandExecutionResult(["  {$pId}.{$stcrM[1]}/{$stcrM[2]} {$title} (IDSTCR)"], $state, false, 'NAME_ELEMENT');
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
                    '  NM1ROY/ASHOK MR',
                    '  NM2SEN/RAHUL MR/JAYA MRS',
                    '  NM1SINGH/NISHA MRS(INF/ANU/02FEB09)',
                ],
                $state,
                true,
                'NAME_ELEMENT_ERROR'
            );
        }

        // 25. CONTACT ELEMENTS: APE, APM, AP
        if (str_starts_with($input, 'APE')) {
            $email = ltrim(trim(substr($rawInput, 3)), '- ');
            $state['activePnr']['contacts'][] = [
                'id' => count($state['activePnr']['contacts']) + 1,
                'type' => 'EMAIL',
                'detail' => $email,
            ];

            return new CommandExecutionResult(["OK APE {$email}"], $state, false, 'CONTACT_ELEMENT');
        }

        if (str_starts_with($input, 'APM')) {
            $mobile = ltrim(trim(substr($rawInput, 3)), '- ');
            $state['activePnr']['contacts'][] = [
                'id' => count($state['activePnr']['contacts']) + 1,
                'type' => 'MOBILE',
                'detail' => $mobile,
            ];

            return new CommandExecutionResult(["OK APM - {$mobile}"], $state, false, 'CONTACT_ELEMENT');
        }

        if (str_starts_with($input, 'AP')) {
            $contact = trim(substr($input, 2));
            if ($contact === '') {
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

        // 26. TICKETING ARRANGEMENTS: TKOK, TKTL, TKXL, TKDO, TKIN
        if (str_starts_with($input, 'TK')) {
            if ($input === 'TKOK') {
                $state['activePnr']['ticketing'] = ['type' => 'TKOK', 'raw' => 'TKOK'];

                return new CommandExecutionResult(['OK TKOK'], $state, false, 'TICKETING_ELEMENT');
            }
            if (str_starts_with($input, 'TKDO')) {
                $rest = trim(substr($input, 4));
                $state['activePnr']['ticketing'] = ['type' => 'TKDO', 'raw' => $input, 'date' => $rest];
                $this->queue->placeOnOfficeQueue(8, 3, $state['activePnr'], $user?->office_id ?? ($state['officeId'] ?? 'LON1A0980'), $userId);

                return new CommandExecutionResult(["OK {$input} - AUTO QUEUED TO Q8C3 (DOMESTIC TICKETING)"], $state, false, 'TICKETING_ELEMENT');
            }
            if (str_starts_with($input, 'TKIN')) {
                $rest = trim(substr($input, 4));
                $state['activePnr']['ticketing'] = ['type' => 'TKIN', 'raw' => $input, 'date' => $rest];
                $this->queue->placeOnOfficeQueue(8, 5, $state['activePnr'], $user?->office_id ?? ($state['officeId'] ?? 'LON1A0980'), $userId);

                return new CommandExecutionResult(["OK {$input} - AUTO QUEUED TO Q8C5 (INTL TICKETING)"], $state, false, 'TICKETING_ELEMENT');
            }
            if (str_starts_with($input, 'TKTL')) {
                $rest = trim(substr($input, 4));
                $state['activePnr']['ticketing'] = ['type' => 'TKTL', 'raw' => $input, 'date' => $rest];

                return new CommandExecutionResult(["OK {$input}"], $state, false, 'TICKETING_ELEMENT');
            }
            if (str_starts_with($input, 'TKXL')) {
                $rest = ltrim(trim(substr($input, 4)), '/');
                $state['activePnr']['ticketing'] = ['type' => 'TKXL', 'raw' => $input, 'date' => $rest];

                return new CommandExecutionResult(["OK {$input}"], $state, false, 'TICKETING_ELEMENT');
            }
        }

        // 27. RECEIVED FROM: RF
        if (str_starts_with($input, 'RF')) {
            $name = trim(substr($input, 2));
            if (! $name) {
                return new CommandExecutionResult(['FORMAT: RF <NAME> (E.G. RF PASSENGER, RF P, RF ALI)'], $state, true, 'RF_ERROR');
            }

            $state['activePnr']['receivedFrom'] = $name;

            if (! empty($state['activeSplit']) && ($state['activeSplit']['status'] ?? '') === 'NEED_RF') {
                $state['activeSplit']['status'] = 'NEED_EF';

                return new CommandExecutionResult(
                    [
                        "OK RF {$name}",
                        'SPLIT PENDING - ENTER EF TO FILE SPLIT PNR',
                    ],
                    $state,
                    false,
                    'SPLIT_RF'
                );
            }

            return new CommandExecutionResult(["OK RF {$name}"], $state, false, 'RECEIVED_FROM');
        }

        // 28. OPTIONAL ELEMENTS & REMARKS: AB, AM, RM, RC, RIR, OP
        if (str_starts_with($input, 'AB//') || str_starts_with($input, 'AM//')) {
            $type = str_starts_with($input, 'AB') ? 'BILLING_ADDRESS' : 'MAILING_ADDRESS';
            $state['activePnr']['remarks'][] = [
                'type' => $type,
                'text' => substr($input, 4),
            ];

            return new CommandExecutionResult(["OK {$input}"], $state, false, 'ADDRESS_ELEMENT');
        }

        if (str_starts_with($input, 'RIR')) {
            $text = trim(substr($input, 3));
            $state['activePnr']['remarks'][] = ['type' => 'ITINERARY', 'text' => $text];

            return new CommandExecutionResult(["OK RIR {$text}"], $state, false, 'REMARK_RIR');
        }

        if (str_starts_with($input, 'RM') || str_starts_with($input, 'RC')) {
            $isConfidential = str_starts_with($input, 'RC');
            $remText = trim(substr($input, 2));

            $state['activePnr']['remarks'][] = [
                'type' => $isConfidential ? 'CONFIDENTIAL' : 'GENERAL',
                'text' => $remText,
            ];

            $tag = $isConfidential ? 'RC' : 'RM';

            return new CommandExecutionResult(["OK {$tag} {$remText}"], $state, false, 'REMARK');
        }

        if (preg_match('/^OP\s*(\d{1,2}[A-Z]{3})\/(.+)$/', $input, $opM)) {
            $opDate = $opM[1];
            $opText = strtoupper(trim($opM[2]));

            $state['activePnr']['options'][] = [
                'date' => $opDate,
                'text' => $opText,
            ];

            return new CommandExecutionResult(["OK OP {$opDate}/{$opText}"], $state, false, 'OPTION_ELEMENT');
        }

        // 29. FREQUENT FLYER: FFA, FFD, FFN, SR FQTV
        if (preg_match('/^FFA\s*([A-Z0-9]{2})-([0-9A-Z]+)$/', $input, $ffaM)) {
            return new CommandExecutionResult([
                "FFA - PNR INITIALIZED FROM FREQUENT FLYER PROFILE: {$ffaM[1]}-{$ffaM[2]}",
                'TRAVELLER: BROWN/BAKER MR (TIER: PLATINUM)',
            ], $state, false, 'FFA');
        }

        if (preg_match('/^FFD\s*([A-Z0-9]{2})(?:-([0-9A-Z]+))?$/', $input, $ffdM)) {
            $carrier = $ffdM[1];
            $num = $ffdM[2] ?? null;
            if ($num) {
                return new CommandExecutionResult([
                    "FFD: {$carrier}-{$num} PROFILE CONFIRMED: BROWN/BAKER MR",
                ], $state, false, 'FFD');
            }

            return new CommandExecutionResult([
                "--- FREQUENT FLYER AGREEMENTS: {$carrier} ---",
                'PARTNER CARRIERS: KL, AF, AY, 9W, DL, VS',
                'POINTS ACCRUAL & TIER STATUS RECIPROCAL ACROSS ALLIANCES',
            ], $state, false, 'FFD');
        }

        if (preg_match('/^FFN\s*([A-Z0-9]{2})-([0-9A-Z]+)(?:,([A-Z0-9,]+))?(?:\/P(\d+))?$/', $input, $ffnM)) {
            $airline = $ffnM[1];
            $ffNum = $ffnM[2];
            $partners = $ffnM[3] ?? '';
            $paxIdx = $ffnM[4] ?? null;

            $paxTag = $paxIdx ? "/P{$paxIdx}" : '';
            $partnerTag = $partners ? ",{$partners}" : '';

            $state['activePnr']['specialServiceRequests'][] = [
                'type' => 'SSR',
                'code' => 'FQTV',
                'text' => "FQTV {$airline} HK1/{$ffNum}{$partnerTag}{$paxTag}",
            ];

            return new CommandExecutionResult(["OK FFN {$airline}-{$ffNum}{$partnerTag}{$paxTag}"], $state, false, 'SSR_FFN');
        }

        if (preg_match('/^(?:SR|FFNSR)\s*FQTV\s*([A-Z0-9]{2})-([0-9A-Z]+)(?:\/P(\d+))?$/', $input, $sfqM)) {
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

        // 30. SEAT MAP DISPLAY: SM<SEGMENT> or SM <FLIGHT>/<CLS>/<DATE><PAIR>
        if (preg_match('/^SM\s*([A-Z0-9]{2})(\d{1,4})\/([A-Z])\/(\d{1,2}[A-Z]{3})([A-Z]{6})$/', $input, $smFltM)) {
            $lines = $this->pnr->formatSeatMap($smFltM[1], $smFltM[2], '77W', 1);

            return new CommandExecutionResult($lines, $state, false, 'SEAT_MAP');
        }

        if (preg_match('/^SM(\d+)$/', $input, $smM)) {
            $segIdx = (int) $smM[1];
            $carrier = 'BA';
            $fltNo = '117';
            $eq = '77W';
            foreach ($state['activePnr']['segments'] ?? [] as $s) {
                if (($s['segmentNumber'] ?? 0) === $segIdx) {
                    $carrier = $s['airlineCode'];
                    $fltNo = $s['flightNumber'] ?: '101';
                    $eq = $s['equipment'] ?: '77W';
                    break;
                }
            }
            $lines = $this->pnr->formatSeatMap($carrier, $fltNo, $eq, $segIdx);

            return new CommandExecutionResult($lines, $state, false, 'SEAT_MAP');
        }

        // 31. SEAT SELECTION & REQUESTS: ST...
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

        // 32. EXTENDED SECURITY: ES..., ESD, ESX, RTE, RHE
        if (str_starts_with($input, 'ES')) {
            if ($input === 'ESD') {
                return new CommandExecutionResult(['ESD - EXTENDED SECURITY AGREEMENT CANCELLED'], $state, false, 'ES_CANCEL');
            }
            if (preg_match('/^ESX\s*RT\/(.+)$/', $input, $esxM)) {
                return new CommandExecutionResult([
                    "--- PNR RETRIEVED UNDER EXTENDED SECURITY: {$esxM[1]} ---",
                    'RECORD ACCESSIBLE - EXTENDED SECURITY AUTHORIZED',
                ], $state, false, 'ES_RETRIEVE');
            }
            if (preg_match('/^ES\s*([A-Z0-9]+)-([RBT])$/', $input, $esM)) {
                $type = match ($esM[2]) {
                    'R' => 'READ ONLY',
                    'B' => 'READ AND WRITE',
                    'T' => 'READ AND TICKET',
                    default => 'GENERAL',
                };

                return new CommandExecutionResult([
                    "OK ES {$esM[1]}-{$esM[2]} ({$type} SECURITY AGREEMENT ESTABLISHED)",
                ], $state, false, 'ES_SET');
            }
        }

        if ($input === 'RTE') {
            return new CommandExecutionResult([
                '--- EXTENDED SECURITY AGREEMENT FOR CURRENT PNR (RTE) ---',
                'SECURITY : LON1A0980 (BRANCH OFFICE READ/WRITE B)',
            ], $state, false, 'RTE');
        }

        if ($input === 'RHE') {
            return new CommandExecutionResult([
                '--- EXTENDED SECURITY HISTORY (RHE) ---',
                '01 '.strtoupper(date('dM Hi')).'Z LON1A0980/1234AB ES-B CREATED',
            ], $state, false, 'RHE');
        }

        // 33. OSI & SSR: OS, OSI, SR
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

        // 34. PNR CANCELLATION & MODIFICATIONS: XI, XE, DL, 8/HK, 7/P1,4
        if ($input === 'XI') {
            if (empty($state['activePnr']['segments'])) {
                return new CommandExecutionResult(['NO ACTIVE ITINERARY SEGMENTS TO CANCEL'], $state, true, 'XI_ERROR');
            }
            $state['activePnr']['segments'] = [];
            $state['activePnr']['tsts'] = [];

            return new CommandExecutionResult(['ITINERARY CANCELLED - ALL SEGMENTS REMOVED'], $state, false, 'XI');
        }

        if (str_starts_with($input, 'DL')) {
            $item = trim(substr($input, 2));

            return new CommandExecutionResult(["ELEMENT {$item} DELETED (INACTIVE STATUS CODES REMOVED)"], $state, false, 'DL');
        }

        if (preg_match('/^(\d+)\/HK$/', $input, $hkM)) {
            $segNum = (int) $hkM[1];

            return new CommandExecutionResult(["SEGMENT {$segNum} STATUS ACCEPTED AS HK (CONFIRMED)"], $state, false, 'ACCEPT_STATUS');
        }

        if (preg_match('/^(\d+)\/P([0-9,]+)$/', $input, $pAssocM)) {
            $line = (int) $pAssocM[1];
            $paxAssoc = $pAssocM[2];

            return new CommandExecutionResult(["LINE {$line} ASSOCIATED TO PASSENGER(S) {$paxAssoc}"], $state, false, 'PASSENGER_ASSOC');
        }

        if (str_starts_with($input, 'XE')) {
            $arg = trim(substr($input, 2));

            // Range deletion: XE 4-8
            if (preg_match('/^(\d+)-(\d+)$/', $arg, $rangeM)) {
                $start = (int) $rangeM[1];
                $end = (int) $rangeM[2];
                $segs = &$state['activePnr']['segments'];
                $segs = array_values(array_filter($segs, fn ($s) => ($s['segmentNumber'] < $start || $s['segmentNumber'] > $end)));
                foreach ($segs as $idx => &$s) {
                    $s['segmentNumber'] = $idx + 1;
                }

                return new CommandExecutionResult(["SEGMENTS {$start}-{$end} CANCELLED"], $state, false, 'XE');
            }

            // Comma-separated deletion: XE 4,6
            if (str_contains($arg, ',')) {
                $targets = array_map('intval', explode(',', $arg));
                $segs = &$state['activePnr']['segments'];
                $segs = array_values(array_filter($segs, fn ($s) => ! in_array($s['segmentNumber'], $targets, true)));
                foreach ($segs as $idx => &$s) {
                    $s['segmentNumber'] = $idx + 1;
                }

                return new CommandExecutionResult(["SEGMENTS {$arg} CANCELLED"], $state, false, 'XE');
            }

            // Single line deletion
            $lineNum = (int) $arg;
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

        // 35. REBOOK CLASS / DATE: SB
        if (str_starts_with($input, 'SB')) {
            $rest = substr($input, 2);

            // SB<DATE><SEGMENT> (e.g. SB30MAR3 or SB25DEC7)
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

            // SB<CLASS><SEGMENT> (e.g. SBJ4 or SB6)
            if (preg_match('/^([A-Z])?(\d+)$/', $rest, $sbcM)) {
                $newClass = ! empty($sbcM[1]) ? $sbcM[1] : 'Y';
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

            // SB<CLASS> (e.g. SBY - all segments)
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

        // 36. PRINTING: IBD, IBP, IED, IEP, IEPJ, WRS, WRA
        if (preg_match('/^IBD(?:\/P(\d+))?$/', $input, $ibdM)) {
            $pIdx = isset($ibdM[1]) ? (int) $ibdM[1] : null;
            $lines = $this->pnr->formatItineraryPrint($state['activePnr'], 'IBD', null, $pIdx);

            return new CommandExecutionResult($lines, $state, false, 'PRINT_ITIN');
        }

        if ($input === 'IBP') {
            $lines = $this->pnr->formatItineraryPrint($state['activePnr'], 'IBP');

            return new CommandExecutionResult($lines, $state, false, 'PRINT_ITIN');
        }

        if (preg_match('/^IED(?:\/P(\d+))?$/', $input, $iedM)) {
            $pIdx = isset($iedM[1]) ? (int) $iedM[1] : null;
            $lines = $this->pnr->formatItineraryPrint($state['activePnr'], 'IED', null, $pIdx);

            return new CommandExecutionResult($lines, $state, false, 'PRINT_ITIN');
        }

        if (str_starts_with($input, 'IEP')) {
            $isFrench = str_contains($input, 'LP FR');
            $pIdx = null;
            if (preg_match('/\/P(\d+)$/', $input, $pM)) {
                $pIdx = (int) $pM[1];
            }
            $format = str_contains($input, 'IEPJ') ? 'IEPJ' : 'IEP';
            $lines = $this->pnr->formatItineraryPrint($state['activePnr'], $format, $isFrench ? 'FR' : null, $pIdx);

            return new CommandExecutionResult($lines, $state, false, 'PRINT_ITIN');
        }

        if ($input === 'WRS/RT' || $input === 'WRS') {
            $lines = $this->pnr->formatPrintout($state['activePnr'], false);

            return new CommandExecutionResult($lines, $state, false, 'PRINT_SCREEN');
        }

        if ($input === 'WRA/RT' || $input === 'WRA') {
            $lines = $this->pnr->formatPrintout($state['activePnr'], true);

            return new CommandExecutionResult($lines, $state, false, 'PRINT_ALL');
        }

        if ($input === 'WRA/RH') {
            $lines = $this->pnr->formatPnrHistory($state['activePnr']);

            return new CommandExecutionResult($lines, $state, false, 'PRINT_HISTORY');
        }

        // 37. SPLIT & NHP: SP, EF, NHP (3/2, 3/P2)
        if (preg_match('/^SP\s*([0-9,]+)$/', $input, $spM)) {
            $paxIdxs = array_map('intval', explode(',', $spM[1]));
            $paxCount = count($state['activePnr']['passengers'] ?? []);

            if ($paxCount <= 1) {
                return new CommandExecutionResult(['SPLIT NOT ALLOWED - MINIMUM 2 PASSENGERS REQUIRED IN ACTIVE PNR'], $state, true, 'SP_ERROR');
            }

            $state['activeSplit'] = [
                'paxIndex' => $paxIdxs[0],
                'allIndexes' => $paxIdxs,
                'status' => 'NEED_RF',
            ];

            return new CommandExecutionResult(
                [
                    "SPLIT IN PROGRESS - PASSENGER(S) {$spM[1]} SELECTED",
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
                        'office_id' => $user?->office_id ?? ($state['officeId'] ?? 'LON1A0980'),
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

        // NHP segment / seat association (e.g. 3/2 or 3/P2)
        if (preg_match('/^(\d+)\/(\d+)$/', $input, $nhpSeatsM)) {
            $seg = $nhpSeatsM[1];
            $seats = $nhpSeatsM[2];

            return new CommandExecutionResult([
                "NHP: SEGMENT {$seg} SEAT COUNT ADJUSTED TO {$seats} (SPLIT CONDITION PREPARED)",
            ], $state, false, 'NHP');
        }

        // 38. COPY PNR: RRN, RRA, RRP, RRI
        if (str_starts_with($input, 'RRN') || $input === 'RRA' || str_starts_with($input, 'RRP') || $input === 'RRI') {
            $modifier = ltrim(substr($input, 3), '/');
            if ($input === 'RRA') {
                $modifier = 'A';
            } elseif ($input === 'RRI') {
                $modifier = 'I';
            } elseif (str_starts_with($input, 'RRP')) {
                $modifier = substr($input, 3);
            }

            $state['activePnr'] = $this->pnr->copyPnr($state['activePnr'], $modifier);

            return new CommandExecutionResult(
                array_merge(["OK COPY PNR ({$input})"], $this->pnr->formatPnrDisplay($state['activePnr'])),
                $state,
                false,
                'COPY_PNR'
            );
        }

        // 39. PNR HISTORY & RECALL: RH, HERH.148, RHS, RLH, RPD
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

        if ($input === 'HERH.148' || $input === 'RH/ALL' || $input === 'RH') {
            $lines = $this->pnr->formatPnrHistory($state['activePnr']);

            return new CommandExecutionResult($lines, $state, false, 'HISTORY_GENERAL');
        }

        if (str_starts_with($input, 'RPD')) {
            return new CommandExecutionResult([
                "--- PNR RECALL DISPLAY ({$input}) ---",
                'STATUS  : HISTORICAL RECALLED FROM AMADEUS ARCHIVE',
                'DETAILS : SEGMENTS & TICKETING DETAILS RESTORED TO VIEW',
            ], $state, false, 'PNR_RECALL');
        }

        // 40. TICKET NUMBER TRANSMISSION: FH...
        if (preg_match('/^FH([AME])(?:\s+(INF|PAX))?\s+([0-9]{3}-[0-9]{10}(?:-[0-9]{1,3})?)(?:\/P(\d+))?$/', $input, $fhm)) {
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

        // 41. FARES & BEST PRICER & PRICING
        if ($input === 'RTLC') {
            return new CommandExecutionResult([
                'RTLC - TICKETLESS BOOKING SYNCHRONISED',
                'AIR ASIA / HOST INVENTORY CONFIRMED (HK)',
                'HOST RECORD LOCATOR APPENDED TO PNR RECORD',
            ], $state, false, 'TICKETLESS_SYNC');
        }

        if (str_starts_with($input, 'FXX/D') || str_contains($input, '/DF-')) {
            $res = $this->pricing->handleFareDiagnostic($input, $state['activePnr']);

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'FARE_DIAGNOSTIC');
        }

        if (str_starts_with($input, 'FXA') || str_starts_with($input, 'FXU') || str_starts_with($input, 'FXZ') || $input === 'FXL') {
            $res = $this->pricing->handleBestPricer($input, $state['activePnr']);
            if ($res->updatedSegments) {
                $state['activePnr']['segments'] = $res->updatedSegments;
            }

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'BEST_PRICER');
        }

        if ($input === 'TQT') {
            $res = $this->pricing->handleTQT($state['activePnr']);

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'TQT');
        }

        if (str_starts_with($input, 'FQC') || str_starts_with($input, 'FQA') || str_starts_with($input, 'FQN') || str_starts_with($input, 'FQR') || str_starts_with($input, 'FQP') || str_starts_with($input, 'FQM')) {
            $res = $this->pricing->handleFareAuxiliary($input);

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'FARE_AUXILIARY');
        }

        if (str_starts_with($input, 'FQD')) {
            $res = $this->pricing->handleFQD(substr($input, 3));

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'FQD');
        }

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

        // 42. FORM OF PAYMENT & COMMISSION
        if (str_starts_with($input, 'FP')) {
            $fopText = trim(substr($input, 2));
            $state['activePnr']['fop'] = [
                'type' => str_starts_with($fopText, 'CC') ? 'CC' : 'CASH',
                'details' => $fopText,
            ];

            return new CommandExecutionResult(["OK FP {$fopText}"], $state, false, 'FORM_OF_PAYMENT');
        }

        if (str_starts_with($input, 'FM')) {
            $comm = trim(substr($input, 2));
            $state['activePnr']['commission'] = $comm;

            return new CommandExecutionResult(["OK FM {$comm}"], $state, false, 'COMMISSION');
        }

        // 43. END TRANSACTION: ET, ER, ERK, ETK
        if ($input === 'ET' || $input === 'ER' || $input === 'ERK' || $input === 'ETK') {
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
            $office = $user?->office_id ?? ($state['officeId'] ?? 'LON1A0980');
            $agent = $user?->agent_id ?? ($state['agentId'] ?? '1234AB');
            $status = ! empty($state['activePnr']['tickets']) ? 'TICKETED' : 'CONFIRMED';

            // Commit to database within transaction
            try {
                DB::transaction(function () use ($rloc, $userId, $user, $isInstructor, $office, $status, $state) {
                    $existingPnr = Pnr::where('record_locator', $rloc)->lockForUpdate()->first();
                    if ($existingPnr) {
                        if (! $isInstructor && $existingPnr->user_id !== $userId) {
                            throw new \RuntimeException("UNAUTHORIZED TO OVERWRITE RECORD LOCATOR: {$rloc}");
                        }

                        $userOffice = $user?->office_id ?: 'LON1A0980';
                        if ($isInstructor && $user && $existingPnr->office_id !== $userOffice) {
                            throw new \RuntimeException("ACCESS RESTRICTED: PNR BELONGS TO ANOTHER OFFICE: {$rloc}");
                        }
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

            if ($input === 'ER' || $input === 'ERK') {
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

        // 44. IGNORE: IG, IR
        if ($input === 'IG') {
            $effectiveOffice = $user?->office_id ?? ($state['officeId'] ?? 'LON1A0980');
            $effectiveAgent = $user?->agent_id ?? ($state['agentId'] ?? '1234AB');
            $state['activePnr'] = $this->pnr->createEmptyPnr($effectiveOffice, $effectiveAgent);

            return new CommandExecutionResult(['IGNORED'], $state, false, 'IG');
        }
        if ($input === 'IR') {
            if (! empty($state['activePnr']['recordLocator'])) {
                $dbPnr = Pnr::find($state['activePnr']['recordLocator']);
                if ($dbPnr) {
                    $userOffice = $user?->office_id ?: 'LON1A0980';
                    $allowed = $isInstructor
                        ? ($user && $dbPnr->office_id === $userOffice)
                        : ($dbPnr->user_id === $userId);

                    if ($allowed) {
                        $state['activePnr'] = $dbPnr->data_json;

                        return new CommandExecutionResult($this->pnr->formatPnrDisplay($state['activePnr']), $state, false, 'IR');
                    }
                }
            }
            $effectiveOffice = $user?->office_id ?? ($state['officeId'] ?? 'LON1A0980');
            $effectiveAgent = $user?->agent_id ?? ($state['agentId'] ?? '1234AB');
            $state['activePnr'] = $this->pnr->createEmptyPnr($effectiveOffice, $effectiveAgent);

            return new CommandExecutionResult(['IGNORED'], $state, false, 'IR');
        }

        // 45. RECORD LOCATORS: RL, RLN
        if ($input === 'RL') {
            $lines = $this->pnr->formatAirlineLocators($state['activePnr']);

            return new CommandExecutionResult($lines, $state, false, 'RL');
        }

        if ($input === 'RLN') {
            $rloc = $state['activePnr']['recordLocator'] ?? '234567';
            $numLocator = '123456789012';

            return new CommandExecutionResult([
                '--- NUMERIC RECORD LOCATOR (RLN) ---',
                "AMADEUS ALPHA LOCATOR : {$rloc}",
                "NUMERIC RECORD LOCATOR : {$numLocator}",
            ], $state, false, 'RLN');
        }

        // 46. PNR CLAIM (RO): RO CO ABC123, RO AA 042/..., RO 3, RO 0
        if (str_starts_with($input, 'RO')) {
            $arg = trim(substr($input, 2));
            if ($arg === '0') {
                return new CommandExecutionResult(['RO 0 - RETURNED TO SIMILAR NAME LIST'], $state, false, 'RO_LIST');
            }
            if (is_numeric($arg)) {
                return new CommandExecutionResult([
                    "RO {$arg} - PNR RETRIEVED FROM LIST LINE {$arg}",
                    'RESPONSIBILITY TRANSFER PENDING - ENTER ET OR ER TO COMPLETE CLAIM',
                ], $state, false, 'RO_RETRIEVE');
            }

            return new CommandExecutionResult([
                "--- RO: PNR CLAIM RECORD RETRIEVED ({$arg}) ---",
                'AIRLINE RECORD LOCATOR VERIFIED: ACTIVE IN AIRLINE INVENTORY',
                'CLAIM PENDING: ENTER ER OR ET TO ACCEPT OWNERSHIP',
            ], $state, false, 'RO_CLAIM');
        }

        // 47. MULTI-LIST (LM / LP) & OFFICE SEARCH (LPO)
        if (str_starts_with($input, 'HELPO') || $input === 'HE LPO') {
            return new CommandExecutionResult([
                '--- HELP: SEARCH BY OFFICE (HE LPO) ---',
                'LPO/A(AF,BA)B(CPH) : SEARCH BY CARRIERS AND DESTINATION',
                'LPO/W(*X)          : HIGHLIGHT SEARCH COMMAND',
                'LPO/N(HTL)         : SEARCH HOTEL SEGMENTS',
                'LPO/N(CCR)         : SEARCH CAR SEGMENTS',
                'LPS/PS             : PASSENGER LIST STATUS TABLE',
            ], $state, false, 'HELP_LPO');
        }

        if (str_starts_with($input, 'LPO') || str_starts_with($input, 'LPS') || str_starts_with($input, 'LPX')) {
            return new CommandExecutionResult([
                '--- OFFICE SEARCH RESULTS (LPO/LPS) ---',
                'LN LOCATOR  PASSENGER NAME      FLIGHT  DATE   STATUS',
                '-----------------------------------------------------',
                '01 Q1A2B3   HUSSAIN/ALI MR      QR001   12MAR  HK1',
                '02 SM9999   SMITH/JOHN MR       BA117   15MAR  HK1',
                '03 DJ1234   DOE/JANE MRS        LH761   18MAR  HK1',
                'USE "LPS<LINE>" (E.G. LPS1) OR "RT<LOC>" TO RETRIEVE',
            ], $state, false, 'LPO_SEARCH');
        }

        if (preg_match('/^(?:LM|LP)\/([A-Z0-9]{2})(\d{1,4})\/(\d{1,2}[A-Z]{3})\/([A-Z]{6})$/', $input, $lmm)) {
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
                    'seats' => '1Y',
                    'rloc' => 'DJ1234',
                    'office' => 'LON1A0980',
                    'pnr' => [
                        'recordLocator' => 'DJ1234',
                        'creationOffice' => 'LON1A0980',
                        'creationAgent' => '1234AB',
                        'passengers' => [['id' => '1.1', 'lastName' => 'DOE', 'firstName' => 'JANE', 'title' => 'MRS', 'type' => 'ADT']],
                        'segments' => [['segmentNumber' => 1, 'airlineCode' => $air, 'flightNumber' => $flt, 'bookingClass' => 'Y', 'departureDate' => $date, 'departureAirport' => $orig, 'arrivalAirport' => $dest, 'departureTime' => '0845', 'arrivalTime' => '1215', 'seats' => 1, 'status' => 'HK']],
                        'contacts' => [['id' => 1, 'type' => 'PHONE', 'detail' => 'LON 020-7946-0912']],
                        'ticketing' => ['type' => 'TKOK', 'raw' => 'TKOK'],
                        'receivedFrom' => 'AGENT',
                    ],
                ],
                [
                    'line' => 4,
                    'name' => 'KHAN/IMRAN MR',
                    'seats' => '1K',
                    'rloc' => 'KI5678',
                    'office' => 'LON1A0980',
                    'pnr' => [
                        'recordLocator' => 'KI5678',
                        'creationOffice' => 'LON1A0980',
                        'creationAgent' => '1234AB',
                        'passengers' => [['id' => '1.1', 'lastName' => 'KHAN', 'firstName' => 'IMRAN', 'title' => 'MR', 'type' => 'ADT']],
                        'segments' => [['segmentNumber' => 1, 'airlineCode' => $air, 'flightNumber' => $flt, 'bookingClass' => 'K', 'departureDate' => $date, 'departureAirport' => $orig, 'arrivalAirport' => $dest, 'departureTime' => '0845', 'arrivalTime' => '1215', 'seats' => 1, 'status' => 'HK']],
                        'contacts' => [['id' => 1, 'type' => 'PHONE', 'detail' => 'LON 020-7946-0912']],
                        'ticketing' => ['type' => 'TKOK', 'raw' => 'TKOK'],
                        'receivedFrom' => 'AGENT',
                    ],
                ],
                [
                    'line' => 5,
                    'name' => 'AHMED/TARIQ MR',
                    'seats' => '1Y',
                    'rloc' => 'Q1W2E3',
                    'office' => 'LON1A0980',
                    'pnr' => [
                        'recordLocator' => 'Q1W2E3',
                        'creationOffice' => 'LON1A0980',
                        'creationAgent' => '1234AB',
                        'passengers' => [['id' => '1.1', 'lastName' => 'AHMED', 'firstName' => 'TARIQ', 'title' => 'MR', 'type' => 'ADT']],
                        'segments' => [['segmentNumber' => 1, 'airlineCode' => $air, 'flightNumber' => $flt, 'bookingClass' => 'Y', 'departureDate' => $date, 'departureAirport' => $orig, 'arrivalAirport' => $dest, 'departureTime' => '0845', 'arrivalTime' => '1215', 'seats' => 1, 'status' => 'HK']],
                        'contacts' => [['id' => 1, 'type' => 'PHONE', 'detail' => 'DOH 4667579']],
                        'ticketing' => ['type' => 'TKOK', 'raw' => 'TKOK'],
                        'receivedFrom' => 'PASSENGER',
                    ],
                ],
            ];

            $state['lastPnrList'] = array_column($pnrList, 'pnr');

            $lines = [
                "--- MULTI-LIST: {$air}{$flt} / {$date} / {$orig}{$dest} ---",
                'LN  PASSENGER NAME       ST  RLOC    OFFICE',
                '---------------------------------------------',
            ];
            foreach ($pnrList as $item) {
                $lines[] = ' '.str_pad((string) $item['line'], 2, ' ', STR_PAD_LEFT).'  '.str_pad($item['name'], 20).' '.str_pad($item['seats'], 3).' '.str_pad($item['rloc'], 7).' '.$item['office'];
            }
            $lines[] = '---------------------------------------------';
            $lines[] = "USE 'LP<LINE>' (E.G. LP1) TO RETRIEVE PNR";

            return new CommandExecutionResult($lines, $state, false, 'LM_SEARCH');
        }

        if (preg_match('/^(?:LM|LP)(\d+)$/', $input, $lmnM)) {
            $lineIdx = (int) $lmnM[1];
            if ($lineIdx === 0) {
                return new CommandExecutionResult(['RETURNED TO PREVIOUS LIST'], $state, false, 'LM_LIST');
            }
            if (empty($state['lastPnrList']) || ! isset($state['lastPnrList'][$lineIdx - 1])) {
                return new CommandExecutionResult(["LINE NUMBER {$lineIdx} NOT FOUND IN LIST"], $state, true, 'LM_ERROR');
            }

            $state['activePnr'] = $state['lastPnrList'][$lineIdx - 1];

            return new CommandExecutionResult($this->pnr->formatPnrDisplay($state['activePnr']), $state, false, 'LM_RETRIEVE');
        }

        // 48. PNR RETRIEVAL: RT...
        if ($input === 'RT') {
            return new CommandExecutionResult($this->pnr->formatPnrDisplay($state['activePnr']), $state, false, 'RT');
        }

        if ($input === 'RT0') {
            if (! empty($state['lastPnrList'])) {
                $lines = ['--- RETURN TO PREVIOUS SEARCH LIST (RT0) ---'];
                foreach ($state['lastPnrList'] as $idx => $mPnr) {
                    $lPad = str_pad((string) ($idx + 1), 2, ' ', STR_PAD_LEFT);
                    $pax = $mPnr['passengers'][0] ?? ['lastName' => 'DOE', 'firstName' => 'JOHN'];
                    $namePad = str_pad($pax['lastName'].'/'.$pax['firstName'].' '.($pax['title'] ?? ''), 22, ' ', STR_PAD_RIGHT);
                    $rloc = $mPnr['recordLocator'] ?? '------';
                    $lines[] = " {$lPad} {$namePad} {$rloc}";
                }

                return new CommandExecutionResult($lines, $state, false, 'RT_LIST');
            }

            return new CommandExecutionResult(['NO ACTIVE LIST TO RETURN TO'], $state, true, 'RT_ERROR');
        }

        if ($input === 'RTI' || $input === 'RTG' || $input === 'RTR' || $input === 'RTF,K,I' || $input === 'RTF' || preg_match('/^RT\/P\d+$/', $input)) {
            $lines = $this->pnr->formatPartialPnrDisplay($state['activePnr'], $input);

            return new CommandExecutionResult($lines, $state, false, 'RT_PARTIAL');
        }

        if (preg_match('/^RTSVI(\d+)$/', $input, $sviM)) {
            $seg = $sviM[1];
            $res = $this->flightInfo->handleDO("DO {$seg}", $state);

            return new CommandExecutionResult($res->lines, $state, false, 'RT_FLIGHT_INFO');
        }

        if ($input === 'RTQ') {
            return new CommandExecutionResult([
                "--- QUEUES CONTAINING ACTIVE PNR ({$state['activePnr']['recordLocator']}) ---",
                'Q01 C00 - TICKETING TIME LIMIT',
            ], $state, false, 'RTQ');
        }

        if ($input === 'RT AXR' || $input === 'RTAXR') {
            $splitTo = $state['activePnr']['splitTo'] ?? 'Q8A9Z1';

            return new CommandExecutionResult([
                '--- ASSOCIATED SPLIT RECORDS (AXR) ---',
                'PARENT RECORD : '.($state['activePnr']['recordLocator'] ?? 'PARENT'),
                "CHILD SPLIT   : {$splitTo} (CONFIRMED)",
            ], $state, false, 'RT_AXR');
        }

        // RT<LINE> (from previous list)
        if (preg_match('/^RT(\d+)$/', $input, $rtLineM)) {
            $lineIdx = (int) $rtLineM[1];
            if (! empty($state['lastPnrList']) && isset($state['lastPnrList'][$lineIdx - 1])) {
                $targetPnr = $state['lastPnrList'][$lineIdx - 1];
                $rloc = $targetPnr['recordLocator'] ?? null;
                if ($rloc) {
                    $dbPnr = Pnr::find($rloc);
                    if ($dbPnr) {
                        if ($userId === 'guest') {
                            return new CommandExecutionResult(['SIGN IN REQUIRED TO RETRIEVE A STORED PNR'], $state, true, 'RT_UNAUTHENTICATED');
                        }

                        if (! $isInstructor && $dbPnr->user_id !== $userId) {
                            return new CommandExecutionResult(['ACCESS RESTRICTED: PNR BELONGS TO ANOTHER USER'], $state, true, 'RT_UNAUTHORIZED');
                        }

                        $userOffice = $user?->office_id ?: 'LON1A0980';
                        if ($isInstructor && $user && $dbPnr->office_id !== $userOffice) {
                            return new CommandExecutionResult(['ACCESS RESTRICTED: PNR BELONGS TO ANOTHER OFFICE'], $state, true, 'RT_UNAUTHORIZED');
                        }
                    }
                }
                $state['activePnr'] = $targetPnr;

                return new CommandExecutionResult($this->pnr->formatPnrDisplay($state['activePnr']), $state, false, 'RT_LINE');
            }
        }

        if (str_starts_with($input, 'RT')) {
            $arg = trim(substr($input, 2));

            // RT by flight: RTLH761/14SEPDELFRA-SEN
            if (preg_match('/^([A-Z0-9]{2})(\d{1,4})\/(\d{1,2}[A-Z]{3})([A-Z]{6})-([A-Z]+)$/', $arg, $rtfM)) {
                return new CommandExecutionResult([
                    "--- PNR RETRIEVAL BY FLIGHT: {$rtfM[1]}{$rtfM[2]} / {$rtfM[3]} / {$rtfM[5]} ---",
                    'RECORD LOCATOR : A8B9C0',
                    "PASSENGER      : {$rtfM[5]}/A MR",
                    'STATUS         : HK1 CONFIRMED',
                ], $state, false, 'RT_FLIGHT');
            }

            // RT by ticket number: RTTKT/016-1234567890
            if (str_starts_with($arg, 'TKT/')) {
                $tkt = substr($arg, 4);

                return new CommandExecutionResult([
                    "--- PNR RETRIEVAL BY TICKET: {$tkt} ---",
                    'RECORD LOCATOR : TK9876',
                    'PASSENGER      : TRAVELER/ECHO MR',
                    'TICKET STATUS  : ISSUED / OPEN FOR USE',
                ], $state, false, 'RT_TICKET');
            }

            // RT/A (alphabetical index) or RT/AHMED (surname search) or RT/SINGH/J or RT/SINGH/J*A
            if (str_starts_with($arg, '/')) {
                $searchQuery = trim(substr($arg, 1));
                $searchQuery = preg_replace('/\/\w+/', '', $searchQuery);
                $searchQuery = preg_replace('/\*A\s*$/i', '', $searchQuery);
                $searchQuery = trim($searchQuery);

                // Alphabetical index (e.g. RT/A)
                if (strlen($searchQuery) === 1) {
                    $matched = [];
                    $indexQuery = Pnr::orderBy('updated_at', 'desc');
                    if (! $isInstructor) {
                        $indexQuery->where('user_id', $userId);
                    } else {
                        $indexQuery->where('office_id', $user?->office_id ?: 'LON1A0980');
                    }
                    $allPnrs = $indexQuery->limit(50)->get();
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

                // Search by surname (e.g. RT/AHMED or RT/SINGH)
                $query = Pnr::orderBy('updated_at', 'desc');
                if (! $isInstructor) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('office_id', $user?->office_id ?: 'LON1A0980');
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

                // Default simulated retrieval if search query not in DB yet
                $mockPnr = [
                    'recordLocator' => 'S1N2G3',
                    'creationOffice' => 'LON1A0980',
                    'creationAgent' => '1234AB',
                    'passengers' => [['id' => '1.1', 'lastName' => $searchQuery, 'firstName' => 'JASPAL', 'title' => 'MR', 'type' => 'ADT']],
                    'segments' => [['segmentNumber' => 1, 'airlineCode' => 'AI', 'flightNumber' => '101', 'bookingClass' => 'Y', 'departureDate' => '15DEC', 'departureAirport' => 'DEL', 'arrivalAirport' => 'LHR', 'departureTime' => '0700', 'arrivalTime' => '1130', 'seats' => 1, 'status' => 'HK']],
                    'contacts' => [['id' => 1, 'type' => 'PHONE', 'detail' => 'DEL 9818568329']],
                    'ticketing' => ['type' => 'TKOK', 'raw' => 'TKOK'],
                    'receivedFrom' => 'PASSENGER',
                ];
                $state['activePnr'] = $mockPnr;

                return new CommandExecutionResult($this->pnr->formatPnrDisplay($state['activePnr']), $state, false, 'RT_SURNAME');
            }

            // Direct locator lookup (e.g. RTXXXXXX or 12-digit numeric)
            if (strlen($arg) === 6 || strlen($arg) === 12) {
                $dbPnr = Pnr::find($arg);
                if ($dbPnr) {
                    if ($userId === 'guest') {
                        return new CommandExecutionResult(['SIGN IN REQUIRED TO RETRIEVE A STORED PNR'], $state, true, 'RT_UNAUTHENTICATED');
                    }

                    if (! $isInstructor && $dbPnr->user_id !== $userId) {
                        return new CommandExecutionResult(['ACCESS RESTRICTED: PNR BELONGS TO ANOTHER USER'], $state, true, 'RT_UNAUTHORIZED');
                    }

                    $userOffice = $user?->office_id ?: 'LON1A0980';
                    if ($isInstructor && $user && $dbPnr->office_id !== $userOffice) {
                        return new CommandExecutionResult(['ACCESS RESTRICTED: PNR BELONGS TO ANOTHER OFFICE'], $state, true, 'RT_UNAUTHORIZED');
                    }
                    $state['activePnr'] = $dbPnr->data_json;

                    return new CommandExecutionResult($this->pnr->formatPnrDisplay($state['activePnr']), $state, false, 'RT_LOCATOR');
                }

                // If 6 chars but not in DB, create/display simulator PNR
                $simPnr = [
                    'recordLocator' => $arg,
                    'creationOffice' => 'LON1A0980',
                    'creationAgent' => '1234AB',
                    'passengers' => [['id' => '1.1', 'lastName' => 'SMITH', 'firstName' => 'JOHN', 'title' => 'MR', 'type' => 'ADT']],
                    'segments' => [['segmentNumber' => 1, 'airlineCode' => 'BA', 'flightNumber' => '117', 'bookingClass' => 'Y', 'departureDate' => '15DEC', 'departureAirport' => 'LHR', 'arrivalAirport' => 'JFK', 'departureTime' => '0915', 'arrivalTime' => '1230', 'seats' => 1, 'status' => 'HK']],
                    'contacts' => [['id' => 1, 'type' => 'PHONE', 'detail' => 'LON 020-7946-0912']],
                    'ticketing' => ['type' => 'TKOK', 'raw' => 'TKOK'],
                    'receivedFrom' => 'AGENT',
                ];
                $state['activePnr'] = $simPnr;

                return new CommandExecutionResult($this->pnr->formatPnrDisplay($state['activePnr']), $state, false, 'RT_LOCATOR');
            }

            return new CommandExecutionResult(["NO PNR FOUND MATCHING: {$arg}"], $state, true, 'RT_NOT_FOUND');
        }

        // 49. ISSUE TICKET: TTP
        if ($input === 'TTP') {
            if ($userId === 'guest') {
                return new CommandExecutionResult(['GUEST SESSION: LOGIN REQUIRED TO ISSUE ELECTRONIC TICKETS (TTP)'], $state, true, 'TTP_UNAUTHENTICATED');
            }

            if (! empty($state['activePnr']['recordLocator'])) {
                $existingPnr = Pnr::find($state['activePnr']['recordLocator']);
                if ($existingPnr) {
                    if (! $isInstructor && $existingPnr->user_id !== $userId) {
                        return new CommandExecutionResult(["UNAUTHORIZED TO TICKET RECORD: {$state['activePnr']['recordLocator']}"], $state, true, 'TTP_UNAUTHORIZED');
                    }

                    $userOffice = $user?->office_id ?: 'LON1A0980';
                    if ($isInstructor && $user && $existingPnr->office_id !== $userOffice) {
                        return new CommandExecutionResult(["ACCESS RESTRICTED: PNR BELONGS TO ANOTHER OFFICE: {$state['activePnr']['recordLocator']}"], $state, true, 'TTP_UNAUTHORIZED');
                    }
                }
            }

            $effectiveOffice = $user?->office_id ?? ($state['officeId'] ?? 'LON1A0980');
            $res = $this->ticketing->issueTicketTTP($state['activePnr'], $effectiveOffice);
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

        // 50. DISPLAY TICKET: TWD
        if (str_starts_with($input, 'TWD')) {
            $arg = trim(substr($input, 3));
            $res = $this->ticketing->displayTicketTWD($state['activePnr'], $arg ?: null);

            return new CommandExecutionResult($res->lines, $state, $res->isError, 'TWD');
        }

        // 51. QUEUE COMMANDS: QT, QTQ, QS, QS97C0D1, QE8C1, QE..., QD..., QDEF, QDIE, QU, QN, QI
        if ($input === 'QTQ') {
            $office = $user?->office_id ?? ($state['officeId'] ?? 'LON1A0980');
            $lines = $this->queue->formatQueueListLines($office);

            return new CommandExecutionResult($lines, $state, false, 'QUEUE_LIST');
        }

        if ($input === 'QT' || $input === 'QS' || $input === 'QS/ALL') {
            if ($userId === 'guest') {
                return new CommandExecutionResult(['GUEST SESSION: LOGIN REQUIRED TO ACCESS QUEUES (QS/QT)'], $state, true, 'QUEUE_UNAUTHENTICATED');
            }

            $office = $user?->office_id ?? ($state['officeId'] ?? 'LON1A0980');
            $lines = $this->queue->formatQueueSummaryLines($office, $userId);

            return new CommandExecutionResult($lines, $state, false, 'QUEUE_SUMMARY');
        }

        if (preg_match('/^QS(\d+)C(\d+)D(\d+)$/', $input, $qscM)) {
            if ($userId === 'guest') {
                return new CommandExecutionResult(['GUEST SESSION: LOGIN REQUIRED TO ACCESS QUEUES'], $state, true, 'QUEUE_UNAUTHENTICATED');
            }

            $office = $user?->office_id ?? ($state['officeId'] ?? 'LON1A0980');
            $qNum = (int) $qscM[1];
            $cat = (int) $qscM[2];
            $dateRange = (int) $qscM[3];
            $lines = $this->queue->displaySpecificQueue($qNum, $cat, $dateRange, $office, $userId);

            return new CommandExecutionResult($lines, $state, false, 'QUEUE_SPECIFIC');
        }

        if (preg_match('/^QE\/([A-Z0-9]+)\/(\d+)C(\d+)$/', $input, $qeOffM)) {
            if ($userId === 'guest') {
                return new CommandExecutionResult(['GUEST SESSION: LOGIN REQUIRED TO ACCESS QUEUES (QE)'], $state, true, 'QUEUE_UNAUTHENTICATED');
            }

            $targetOff = $qeOffM[1];
            $qNum = (int) $qeOffM[2];
            $cat = (int) $qeOffM[3];
            $lines = $this->queue->placeOnOfficeQueue($qNum, $cat, $state['activePnr'], $targetOff, $userId);

            return new CommandExecutionResult($lines, $state, false, 'QUEUE_ENTER');
        }

        if (preg_match('/^QE(\d+)C(\d+)$/', $input, $qeCatM)) {
            if ($userId === 'guest') {
                return new CommandExecutionResult(['GUEST SESSION: LOGIN REQUIRED TO ACCESS QUEUES (QE)'], $state, true, 'QUEUE_UNAUTHENTICATED');
            }

            $qNum = (int) $qeCatM[1];
            $cat = (int) $qeCatM[2];
            $office = $user?->office_id ?? ($state['officeId'] ?? 'LON1A0980');
            $lines = $this->queue->placeOnOfficeQueue($qNum, $cat, $state['activePnr'], $office, $userId);

            return new CommandExecutionResult($lines, $state, false, 'QUEUE_ENTER');
        }

        if (preg_match('/^QE(\d+)$/', $input, $qeM)) {
            if ($userId === 'guest') {
                return new CommandExecutionResult(['GUEST SESSION: LOGIN REQUIRED TO ACCESS QUEUES (QE)'], $state, true, 'QUEUE_UNAUTHENTICATED');
            }

            $qNum = (int) $qeM[1];
            $office = $user?->office_id ?? ($state['officeId'] ?? 'LON1A0980');
            $lines = $this->queue->placeOnQueue($qNum, $state['activePnr'], $office, $userId);

            return new CommandExecutionResult($lines, $state, false, 'QUEUE_ENTER');
        }

        if (str_starts_with($input, 'QE/')) {
            if ($userId === 'guest') {
                return new CommandExecutionResult(['GUEST SESSION: LOGIN REQUIRED TO ACCESS QUEUES (QE)'], $state, true, 'QUEUE_UNAUTHENTICATED');
            }

            return new CommandExecutionResult([
                'QE/ - FREE FLOW QUEUE MESSAGE TRANSMITTED',
                'MESSAGE DELIVERED TO TARGET OFFICE QUEUE BANK',
            ], $state, false, 'QUEUE_MESSAGE');
        }

        if ($input === 'QDEF') {
            return new CommandExecutionResult([
                'QDEF - END OF TRANSACTION & EXITED QUEUE MODE',
                'ACTIVE PNR FILED AND NEXT QUEUE ITEM ACCESSIBLE VIA QN',
            ], $state, false, 'QUEUE_EOT_EXIT');
        }

        if ($input === 'QDIE') {
            return new CommandExecutionResult([
                'QDIE - IGNORED PNR AND EXITED QUEUE MODE',
                'ACTIVE WORK AREA RETURNED TO GENERAL TERMINAL SESSION',
            ], $state, false, 'QUEUE_IGNORE_EXIT');
        }

        if ($input === 'QU') {
            return new CommandExecutionResult([
                'QU - REDISPLAY QUEUE MESSAGE HEADER',
                'QUEUE 01 CATEGORY 00 - TICKETING TIME LIMIT DEADLINE APPROACHING',
            ], $state, false, 'QUEUE_REDISPLAY');
        }

        if (str_starts_with($input, 'QD')) {
            if ($userId === 'guest') {
                return new CommandExecutionResult(['GUEST SESSION: LOGIN REQUIRED TO ACCESS QUEUES (QD)'], $state, true, 'QUEUE_UNAUTHENTICATED');
            }

            $office = $user?->office_id ?? ($state['officeId'] ?? 'LON1A0980');
            $arg = trim(substr($input, 2));

            // Delay with date/time: QD09DEC/1200
            if (str_contains($arg, '/')) {
                return new CommandExecutionResult([
                    "OK DELAYED TO {$arg}",
                    'ITEM SCHEDULED FOR RE-QUEUE ON SPECIFIED DATE & TIME',
                ], $state, false, 'QUEUE_DELAY');
            }

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
