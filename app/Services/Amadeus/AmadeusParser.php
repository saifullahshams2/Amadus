<?php

namespace App\Services\Amadeus;

use App\Models\Pnr;

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
        protected TicketingService $ticketing
    ) {}

    public function execute(string $rawInput, array $currentState, string $userId = 'guest'): CommandExecutionResult
    {
        $input = strtoupper(trim($rawInput));
        $state = $currentState;

        // Initialize PNR if not present
        if (empty($state['activePnr'])) {
            $state['activePnr'] = $this->pnr->createEmptyPnr($state['officeId'] ?? 'LON1A0980', $state['agentId'] ?? '1234AB');
        }

        // 1. HELP / HE
        if ($input === 'HE' || str_starts_with($input, 'HELP') || $input === '?') {
            return new CommandExecutionResult(
                [
                    '--- AMADEUS GDS QUICK COMMAND REFERENCE ---',
                    'AN<DATE><ORIG><DEST>   : NEUTRAL AVAILABILITY (E.G. AN15NOVLHRJFK)',
                    'SS<SEATS><CLASS><LINE> : SELL SEAT FROM DISPLAY (E.G. SS1Y1)',
                    'NM1<SURNAME>/<NAME> MR : PASSENGER NAME (E.G. NM1DOE/JOHN MR)',
                    'AP <PHONE> / APE <EMAIL>: CONTACT (E.G. AP DOH 974-555-1234-A)',
                    'TKTL<DATE> / TKOK      : TICKETING (E.G. TKTL15NOV/LON1A0980)',
                    'RF <NAME>              : RECEIVED FROM (E.G. RF PASSENGER)',
                    'FXP / FXX / FXB        : PRICE ITINERARY & STORE TST',
                    'FP CASH / FP CC...     : FORM OF PAYMENT',
                    'ET / ER                : END TRANSACTION / END AND REDISPLAY',
                    'RT <LOCATOR> / RT      : RETRIEVE PNR',
                    'TTP / TWD              : ISSUE E-TICKET / DISPLAY TICKET',
                    'DAN / DNA / DNE        : DECODE AIRPORT, AIRLINE, AIRCRAFT',
                    'IG / IR                : IGNORE CHANGES / IGNORE & REDISPLAY',
                ],
                $state,
                false,
                'HELP'
            );
        }

        // 2. SIGN IN / SIGN OUT
        if (str_starts_with($input, 'JI') || $input === 'SI') {
            $state['isSignedIn'] = true;
            $area = $state['area'] ?? 'A';
            $off = $state['officeId'] ?? 'LON1A0980';
            $agt = $state['agentId'] ?? '1234AB';
            return new CommandExecutionResult(
                ["OK SIGN-IN TO WORK AREA {$area} / OFFICE {$off} / AGENT {$agt}"],
                $state,
                false,
                'SIGN_IN'
            );
        }
        if ($input === 'JO' || $input === 'SO') {
            $state['isSignedIn'] = false;
            $area = $state['area'] ?? 'A';
            return new CommandExecutionResult(
                ["SIGNED OUT OF AMADEUS GDS - WORK AREA {$area}"],
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

        // 4. FLIGHT AVAILABILITY: AN, SN
        if (str_starts_with($input, 'AN') || str_starts_with($input, 'SN')) {
            $res = $this->availability->search(substr($input, 2));
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
                'AVAILABILITY'
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
                    "NO FURTHER FLIGHTS IN THIS PERIOD",
                ],
                $state,
                false,
                'PAGING'
            );
        }

        // 6. SHORT SELL: SS
        if (str_starts_with($input, 'SS')) {
            if (!preg_match('/^SS(\d+)([A-Z])(\d+)$/', $input, $match)) {
                return new CommandExecutionResult(['FORMAT: SS<SEATS><CLASS><LINE> (E.G. SS1Y1 OR SS2J3)'], $state, true, 'SELL_SEAT_ERROR');
            }

            $seats = (int)$match[1];
            $bookingClass = $match[2];
            $lineNumber = (int)$match[3];

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

            if (!$selectedLine) {
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

            $fltPad = str_pad($sched['flightNumber'], 3, ' ', STR_PAD_LEFT);
            $lineResponse = " {$segmentNumber}  {$sched['airlineCode']} {$fltPad} {$bookingClass} {$selectedLine['departureDate']} {$sched['departureAirport']}{$sched['arrivalAirport']} HK{$seats}  {$sched['departureTime']} {$sched['arrivalTime']}   *1A/E*";

            return new CommandExecutionResult([$lineResponse], $state, false, 'SELL_SEAT');
        }

        // 7. PASSENGER NAME: NM
        if (str_starts_with($input, 'NM')) {
            $rawName = trim(substr($input, 2));
            $chdMatch = preg_match('/^1([A-Z]+)\/([A-Z]+)\(CHD\/(\d{1,2}[A-Z]{3}\d{2})\)$/', $rawName, $cm);
            $infMatch = preg_match('/^1([A-Z]+)\/([A-Z]+)\(INF\/(\d{1,2}[A-Z]{3}\d{2})\)$/', $rawName, $im);
            $stdMatch = preg_match('/^(\d+)([A-Z]+)\/([A-Z]+)(?:\s+([A-Z]+))?$/', $rawName, $sm);

            if ($chdMatch) {
                $pId = (count($state['activePnr']['passengers']) + 1) . '.1';
                $state['activePnr']['passengers'][] = [
                    'id' => $pId,
                    'type' => 'CHD',
                    'lastName' => $cm[1],
                    'firstName' => $cm[2],
                    'dateOfBirth' => $cm[3],
                ];
                return new CommandExecutionResult(["  {$pId}.{$cm[1]}/{$cm[2]}(CHD/{$cm[3]})"], $state, false, 'NAME_ELEMENT');
            }

            if ($infMatch) {
                $pId = (count($state['activePnr']['passengers']) + 1) . '.1';
                $state['activePnr']['passengers'][] = [
                    'id' => $pId,
                    'type' => 'INF',
                    'lastName' => $im[1],
                    'firstName' => $im[2],
                    'dateOfBirth' => $im[3],
                    'accompaniedBy' => '1.1',
                ];
                return new CommandExecutionResult(["  {$pId}.{$im[1]}/{$im[2]}(INF/{$im[3]})"], $state, false, 'NAME_ELEMENT');
            }

            if ($stdMatch) {
                $pId = (count($state['activePnr']['passengers']) + 1) . '.1';
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
                    '  NM1SMITH/EMILY MRS',
                    '  NM1BROWN/TIM(CHD/15MAY16)',
                ],
                $state,
                true,
                'NAME_ELEMENT_ERROR'
            );
        }

        // 8. CONTACT / PHONE / EMAIL: AP, APE
        if (str_starts_with($input, 'APE')) {
            $email = trim(substr($input, 3));
            $state['activePnr']['contacts'][] = [
                'id' => count($state['activePnr']['contacts']) + 1,
                'type' => 'EMAIL',
                'detail' => $email,
            ];
            return new CommandExecutionResult(["OK APE {$email}"], $state, false, 'CONTACT_ELEMENT');
        }
        if (str_starts_with($input, 'AP')) {
            $contact = trim(substr($input, 2));
            if (!$contact) {
                return new CommandExecutionResult(['FORMAT: AP <PHONE> (E.G. AP DOH 974-555-1234-A)'], $state, true, 'CONTACT_ERROR');
            }
            $state['activePnr']['contacts'][] = [
                'id' => count($state['activePnr']['contacts']) + 1,
                'type' => 'PHONE',
                'detail' => $contact,
            ];
            return new CommandExecutionResult(["OK AP {$contact}"], $state, false, 'CONTACT_ELEMENT');
        }

        // 9. TICKETING: TKTL, TKOK, TKXL
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
            if ($input === 'TKXL') {
                $state['activePnr']['ticketing'] = ['type' => 'TKXL', 'raw' => 'TKXL'];
                return new CommandExecutionResult(['OK TKXL'], $state, false, 'TICKETING_ELEMENT');
            }
        }

        // 10. RECEIVED FROM: RF
        if (str_starts_with($input, 'RF')) {
            $name = trim(substr($input, 2));
            if (!$name) {
                return new CommandExecutionResult(['FORMAT: RF <NAME> (E.G. RF PASSENGER OR RF MR SMITH)'], $state, true, 'RF_ERROR');
            }
            $state['activePnr']['receivedFrom'] = $name;
            return new CommandExecutionResult(["RF {$name} - RETAINED"], $state, false, 'RECEIVED_FROM');
        }

        // 11. REMARKS: RM, RC
        if (str_starts_with($input, 'RM') || str_starts_with($input, 'RC')) {
            $remark = trim(substr($input, 2));
            $state['activePnr']['remarks'][] = $remark;
            return new CommandExecutionResult(["OK RM {$remark}"], $state, false, 'REMARK_ELEMENT');
        }

        // 12. SPECIAL SERVICE REQUESTS: SR, SSR, OSI
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

        // 13. FARE QUOTE DISPLAY: FQD
        if (str_starts_with($input, 'FQD')) {
            $res = $this->pricing->handleFQD(substr($input, 3));
            return new CommandExecutionResult($res->lines, $state, $res->isError, 'FQD');
        }

        // 14. PRICING & TST: FXP, FXX, FXB
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

        // 15. FORM OF PAYMENT: FP
        if (str_starts_with($input, 'FP')) {
            $fopText = trim(substr($input, 2));
            $state['activePnr']['fop'] = [
                'type' => str_starts_with($fopText, 'CC') ? 'CC' : 'CASH',
                'details' => $fopText,
            ];
            return new CommandExecutionResult(["OK FP {$fopText}"], $state, false, 'FORM_OF_PAYMENT');
        }

        // 16. COMMISSION: FM
        if (str_starts_with($input, 'FM')) {
            $comm = trim(substr($input, 2));
            $state['activePnr']['commission'] = $comm;
            return new CommandExecutionResult(["OK FM {$comm}"], $state, false, 'COMMISSION');
        }

        // 17. END TRANSACTION: ET, ER
        if ($input === 'ET' || $input === 'ER') {
            $val = $this->pnr->validateMandatoryPrintElements($state['activePnr']);
            if (!$val->isValid) {
                return new CommandExecutionResult($val->errors, $state, true, 'ET_ERROR');
            }

            if (empty($state['activePnr']['recordLocator'])) {
                $state['activePnr']['recordLocator'] = $this->pnr->generateRecordLocator();
            }

            $rloc = $state['activePnr']['recordLocator'];
            $office = $state['officeId'] ?? 'LON1A0980';
            $agent = $state['agentId'] ?? '1234AB';
            $status = !empty($state['activePnr']['tickets']) ? 'TICKETED' : 'CONFIRMED';

            // Commit to database
            try {
                Pnr::updateOrCreate(
                    ['record_locator' => $rloc],
                    [
                        'user_id' => $userId,
                        'office_id' => $office,
                        'status' => $status,
                        'data_json' => $state['activePnr'],
                    ]
                );
            } catch (\Throwable $e) {
                // Ignore db write failure for guest
            }

            if ($input === 'ER') {
                return new CommandExecutionResult($this->pnr->formatPnrDisplay($state['activePnr']), $state, false, 'ER');
            }

            $firstPax = $state['activePnr']['passengers'][0];
            $firstSeg = $state['activePnr']['segments'][0];
            $fltPad = str_pad($firstSeg['flightNumber'], 3, ' ', STR_PAD_LEFT);
            $etLines = [
                '--- RLR ---',
                "1." . strtoupper($firstPax['lastName']) . "/" . strtoupper($firstPax['firstName']),
                " 1  {$firstSeg['airlineCode']} {$fltPad} {$firstSeg['bookingClass']} {$firstSeg['departureDate']} {$firstSeg['departureAirport']}{$firstSeg['arrivalAirport']} HK{$firstSeg['seats']}  {$firstSeg['departureTime']} {$firstSeg['arrivalTime']}",
                "{$office}/{$agent}  {$rloc}",
            ];

            return new CommandExecutionResult($etLines, $state, false, 'ET');
        }

        // 18. IGNORE: IG, IR
        if ($input === 'IG') {
            $state['activePnr'] = $this->pnr->createEmptyPnr($state['officeId'] ?? 'LON1A0980', $state['agentId'] ?? '1234AB');
            return new CommandExecutionResult(['IGNORED'], $state, false, 'IG');
        }
        if ($input === 'IR') {
            if (!empty($state['activePnr']['recordLocator'])) {
                $dbPnr = Pnr::find($state['activePnr']['recordLocator']);
                if ($dbPnr) {
                    $state['activePnr'] = $dbPnr->data_json;
                    return new CommandExecutionResult($this->pnr->formatPnrDisplay($state['activePnr']), $state, false, 'IR');
                }
            }
            $state['activePnr'] = $this->pnr->createEmptyPnr($state['officeId'] ?? 'LON1A0980', $state['agentId'] ?? '1234AB');
            return new CommandExecutionResult(['IGNORED'], $state, false, 'IR');
        }

        // 19. RETRIEVE PNR: RT
        if ($input === 'RT') {
            return new CommandExecutionResult($this->pnr->formatPnrDisplay($state['activePnr']), $state, false, 'RT');
        }
        if (str_starts_with($input, 'RT')) {
            $arg = trim(substr($input, 2));
            if (strlen($arg) === 6) {
                $dbPnr = Pnr::find($arg);
                if ($dbPnr) {
                    $state['activePnr'] = $dbPnr->data_json;
                    return new CommandExecutionResult($this->pnr->formatPnrDisplay($state['activePnr']), $state, false, 'RT_LOCATOR');
                }
            }

            // Search by surname
            $all = Pnr::orderBy('updated_at', 'desc')->limit(50)->get();
            foreach ($all as $item) {
                $p = $item->data_json;
                if (!empty($p['passengers'])) {
                    foreach ($p['passengers'] as $pax) {
                        if (strtoupper($pax['lastName']) === $arg) {
                            $state['activePnr'] = $p;
                            return new CommandExecutionResult($this->pnr->formatPnrDisplay($state['activePnr']), $state, false, 'RT_SURNAME');
                        }
                    }
                }
            }

            return new CommandExecutionResult(["NO PNR FOUND MATCHING: {$arg}"], $state, true, 'RT_NOT_FOUND');
        }

        // 20. CANCEL ELEMENT / SEGMENT: XE
        if (str_starts_with($input, 'XE')) {
            $lineNum = (int)trim(substr($input, 2));
            if ($lineNum <= 0) {
                return new CommandExecutionResult(['FORMAT: XE<LINE NUMBER> (E.G. XE 1)'], $state, true, 'XE_ERROR');
            }

            if (!empty($state['activePnr']['segments']) && $lineNum <= count($state['activePnr']['segments'])) {
                array_splice($state['activePnr']['segments'], $lineNum - 1, 1);
                foreach ($state['activePnr']['segments'] as $idx => &$s) {
                    $s['segmentNumber'] = $idx + 1;
                }
                return new CommandExecutionResult(["SEGMENT {$lineNum} CANCELLED"], $state, false, 'XE');
            }

            return new CommandExecutionResult(["ELEMENT {$lineNum} CANCELLED"], $state, false, 'XE');
        }

        // 21. ISSUE TICKET: TTP
        if ($input === 'TTP') {
            $res = $this->ticketing->issueTicketTTP($state['activePnr'], $state['officeId'] ?? 'LON1A0980');
            if ($res->isError) {
                return new CommandExecutionResult($res->lines, $state, true, 'TTP_ERROR');
            }

            if (!empty($res->tickets)) {
                $state['activePnr']['tickets'] = array_merge($state['activePnr']['tickets'] ?? [], $res->tickets);
                $state['activePnr']['ticketing'] = ['type' => 'TKOK', 'raw' => 'TKOK'];

                if (!empty($state['activePnr']['recordLocator'])) {
                    Pnr::where('record_locator', $state['activePnr']['recordLocator'])->update([
                        'status' => 'TICKETED',
                        'data_json' => $state['activePnr'],
                    ]);
                }
            }

            return new CommandExecutionResult($res->lines, $state, false, 'TTP');
        }

        // 22. DISPLAY TICKET: TWD
        if (str_starts_with($input, 'TWD')) {
            $arg = trim(substr($input, 3));
            $res = $this->ticketing->displayTicketTWD($state['activePnr'], $arg ?: null);
            return new CommandExecutionResult($res->lines, $state, $res->isError, 'TWD');
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
