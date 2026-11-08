<?php

namespace App\Services\Amadeus;

use App\Models\Pnr;

class PnrValidationResult
{
    public function __construct(
        public bool $isValid,
        public array $errors = []
    ) {}
}

class PnrService
{
    public function createEmptyPnr(string $officeId = 'LON1A0980', string $agentId = '1234AB'): array
    {
        return [
            'creationOffice' => $officeId,
            'creationAgent' => $agentId,
            'createdAt' => date('c'),
            'passengers' => [],
            'segments' => [],
            'contacts' => [],
            'remarks' => [],
            'options' => [],
            'seatRequests' => [],
            'specialServiceRequests' => [],
            'tsts' => [],
            'tickets' => [],
            'history' => [
                strtoupper(date('dM')).' '.gmdate('Hi')."Z CR-CREATION {$officeId}/{$agentId}",
            ],
        ];
    }

    public function generateRecordLocator(): string
    {
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        do {
            $res = '';
            for ($i = 0; $i < 6; $i++) {
                $res .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (Pnr::where('record_locator', $res)->exists());

        return $res;
    }

    public function validateMandatoryPrintElements(?array $pnr): PnrValidationResult
    {
        if (! $pnr) {
            return new PnrValidationResult(false, ['NO ACTIVE PNR IN WORK AREA']);
        }

        $errors = [];

        // P - Phone / Contact
        if (empty($pnr['contacts'])) {
            $errors[] = 'NEED CONTACT ELEMENT - ENTER AP <PHONE> OR APE <EMAIL>';
        }

        // R - Received From
        if (empty($pnr['receivedFrom'])) {
            $errors[] = 'NEED RECEIVED FROM - ENTER RF <NAME>';
        }

        // I - Itinerary
        if (empty($pnr['segments'])) {
            $errors[] = 'CHECK CONTINUITY - NO ITINERARY SEGMENTS BOOKED';
        }

        // N - Name
        if (empty($pnr['passengers'])) {
            $errors[] = 'NEED NAME ELEMENT - ENTER NM1<SURNAME>/<FIRSTNAME> [TITLE]';
        }

        // T - Ticketing
        if (empty($pnr['ticketing'])) {
            $errors[] = 'CHECK TICKETING ARRANGEMENT - ENTER TKTL<DATE> OR TKOK';
        }

        return new PnrValidationResult(empty($errors), $errors);
    }

    public function formatPnrDisplay(?array $pnr): array
    {
        if (! $pnr || (empty($pnr['passengers']) && empty($pnr['segments']) && empty($pnr['contacts']))) {
            return ['NO PNR PRESENT IN WORK AREA'];
        }

        $lines = [];
        $dateStr = strtoupper(date('dM').substr(date('Y'), -2));
        $timeStr = gmdate('Hi').'Z';
        $rloc = $pnr['recordLocator'] ?? '---';

        $office = $pnr['creationOffice'] ?? 'LON1A0980';
        $agent = $pnr['creationAgent'] ?? '1234AB';

        $lines[] = '--- RLR ---';
        $lines[] = "RP/{$office}/{$agent}            {$dateStr}/{$timeStr}   {$rloc}";

        // 1. Passengers
        if (! empty($pnr['passengers'])) {
            foreach ($pnr['passengers'] as $idx => $pax) {
                $paxNum = $idx + 1;
                $paxId = $pax['id'] ?? "{$paxNum}.1";
                $paxStr = '  '.$paxId.'.'.strtoupper($pax['lastName']).'/'.strtoupper($pax['firstName']);

                if (! empty($pax['title'])) {
                    $paxStr .= ' '.strtoupper($pax['title']);
                }

                if (($pax['type'] ?? '') === 'CHD') {
                    $paxStr .= ! empty($pax['dateOfBirth']) ? "(CHD/{$pax['dateOfBirth']})" : '(CHD)';
                }

                if (! empty($pax['infant'])) {
                    $inf = $pax['infant'];
                    if (! empty($inf['lastName']) && strtoupper($inf['lastName']) !== strtoupper($pax['lastName'])) {
                        $paxStr .= '(INF'.strtoupper($inf['lastName']).'/'.strtoupper($inf['firstName']).')';
                    } else {
                        $paxStr .= '(INF/'.strtoupper($inf['firstName']).')';
                    }
                } elseif (($pax['type'] ?? '') === 'INF') {
                    $paxStr .= '(INF/'.($pax['accompaniedBy'] ?? '').')';
                }

                $lines[] = $paxStr;
            }
        }

        $elementCounter = 1;

        // 2. Flight Segments
        if (! empty($pnr['segments'])) {
            foreach ($pnr['segments'] as $seg) {
                if (($seg['type'] ?? '') === 'ARNK' || ($seg['airlineCode'] ?? '') === 'ARNK') {
                    $lines[] = "  {$seg['segmentNumber']}  ARNK";
                    $elementCounter++;

                    continue;
                }

                if (($seg['status'] ?? '') === 'OPEN' || ($seg['flightNumber'] ?? '') === 'OPEN') {
                    $depArr = "{$seg['departureAirport']}{$seg['arrivalAirport']}";
                    $lines[] = "  {$seg['segmentNumber']}  {$seg['airlineCode']} OPEN {$seg['bookingClass']}       {$depArr} OPEN    OPEN OPEN   *1A/E*";
                    $elementCounter++;

                    continue;
                }

                $fltNo = str_pad((string) ($seg['flightNumber'] ?? '001'), 3, ' ', STR_PAD_LEFT);
                $seats = $seg['seats'] ?? 1;
                $statusCode = $seg['status'] ?? 'HK';
                $statusStr = $statusCode.$seats;
                $passiveTag = ! empty($seg['passiveCarrier']) ? "  /{$seg['passiveCarrier']}" : '   *1A/E*';
                $depDate = $seg['departureDate'] ?? '15MAR';
                $depApt = $seg['departureAirport'] ?? 'DOH';
                $arrApt = $seg['arrivalAirport'] ?? 'LHR';
                $depTime = $seg['departureTime'] ?? '0800';
                $arrTime = $seg['arrivalTime'] ?? '1230';

                $lines[] = "  {$seg['segmentNumber']}  {$seg['airlineCode']} {$fltNo} {$seg['bookingClass']} {$depDate} {$depApt}{$arrApt} {$statusStr}  {$depTime} {$arrTime}{$passiveTag}";
                $elementCounter++;
            }
        }

        // 3. Contacts
        if (! empty($pnr['contacts'])) {
            foreach ($pnr['contacts'] as $c) {
                $type = ($c['type'] === 'EMAIL') ? 'APE' : 'AP';
                $lines[] = "  {$elementCounter} {$type} {$c['detail']}";
                $elementCounter++;
            }
        }

        // 4. Ticketing
        if (! empty($pnr['ticketing'])) {
            $raw = preg_replace('/^TK/', '', $pnr['ticketing']['raw'] ?? 'TKOK');
            $lines[] = "  {$elementCounter} TK {$raw}";
            $elementCounter++;
        }

        // 5. Special Service Requests (SSR / OSI)
        if (! empty($pnr['specialServiceRequests'])) {
            foreach ($pnr['specialServiceRequests'] as $sr) {
                $prefix = $sr['type'] ?? 'SSR';
                $code = $sr['code'] ?? '';
                $text = $sr['text'] ?? '';
                $lines[] = "  {$elementCounter} {$prefix} {$code} {$text}";
                $elementCounter++;
            }
        }

        // 6. Seat Requests
        if (! empty($pnr['seatRequests'])) {
            foreach ($pnr['seatRequests'] as $seatReq) {
                $reqText = is_array($seatReq) ? ($seatReq['text'] ?? 'ST') : $seatReq;
                $lines[] = "  {$elementCounter} ST {$reqText}";
                $elementCounter++;
            }
        }

        // 7. Options
        if (! empty($pnr['options'])) {
            foreach ($pnr['options'] as $opt) {
                $lines[] = "  {$elementCounter} OP {$opt['date']}/{$opt['text']}";
                $elementCounter++;
            }
        }

        // 8. Remarks (General RM and Confidential RC)
        if (! empty($pnr['remarks'])) {
            foreach ($pnr['remarks'] as $rem) {
                if (is_array($rem)) {
                    $tag = ($rem['type'] ?? 'GENERAL') === 'CONFIDENTIAL' ? 'RC' : 'RM';
                    $text = $rem['text'] ?? '';
                } else {
                    $tag = 'RM';
                    $text = $rem;
                }
                $lines[] = "  {$elementCounter} {$tag} {$text}";
                $elementCounter++;
            }
        }

        // Split Cross-References
        if (! empty($pnr['splitTo'])) {
            $lines[] = "  {$elementCounter} RM SPLIT TO {$pnr['splitTo']}";
            $elementCounter++;
        }
        if (! empty($pnr['splitFrom'])) {
            $lines[] = "  {$elementCounter} RM SPLIT FROM {$pnr['splitFrom']}";
            $elementCounter++;
        }

        // 9. Issued Tickets (FA lines & FH lines)
        if (! empty($pnr['tickets'])) {
            $carrier = $pnr['segments'][0]['airlineCode'] ?? '1A';
            foreach ($pnr['tickets'] as $tkt) {
                $paxAssoc = ! empty($tkt['paxIndex']) ? "/P{$tkt['paxIndex']}" : '';
                $tktMethod = $tkt['method'] ?? 'FA';
                if (str_starts_with($tktMethod, 'FH')) {
                    $paxType = $tkt['passengerType'] ?? 'PAX';
                    $lines[] = "  {$elementCounter} {$tktMethod} {$paxType} {$tkt['ticketNumber']}{$paxAssoc}";
                } else {
                    $lines[] = "  {$elementCounter} FA PAX {$tkt['ticketNumber']}/ET{$carrier}/{$dateStr}{$paxAssoc}";
                }
                $elementCounter++;
            }
        }

        // 10. Form of Payment
        if (! empty($pnr['fop'])) {
            $lines[] = "  {$elementCounter} FP {$pnr['fop']['details']}";
            $elementCounter++;
        }

        // 11. Commission
        if (! empty($pnr['commission'])) {
            $lines[] = "  {$elementCounter} FM {$pnr['commission']}";
            $elementCounter++;
        }

        // 12. Received From
        if (! empty($pnr['receivedFrom'])) {
            $lines[] = "  {$elementCounter} RF {$pnr['receivedFrom']}";
            $elementCounter++;
        }

        return $lines;
    }

    public function formatSeatMap(string $carrier, string $flightNumber, string $equipment, int $segmentNumber): array
    {
        $eqName = ($equipment === '77W') ? 'BOEING 777-300ER' : (($equipment === '359') ? 'AIRBUS A350-900' : 'AIRBUS A320');

        return [
            "--- SEAT MAP : {$carrier} {$flightNumber} - {$eqName} - SEGMENT {$segmentNumber} ---",
            '     A B C   D E F G   H J K',
            ' 20  . . .   . . . .   . . .',
            ' 21  . . X   . . . .   X . .',
            ' 22  . . .   X X . .   . . .',
            ' 23  . . .   . . . .   . . .',
            ' 24  . X .   . . . .   . X .',
            ' 25  . . .   . . . .   . . .',
            '( . AVAILABLE   X OCCUPIED )',
            'USE ST/R/<SEAT> (E.G. ST/R/20K) TO ASSIGN SEAT',
        ];
    }

    public function formatPnrHistory(array $pnr, ?int $segmentNumber = null): array
    {
        $rloc = $pnr['recordLocator'] ?? 'ACTIVE';
        $office = $pnr['creationOffice'] ?? 'LON1A0980';
        $agent = $pnr['creationAgent'] ?? '1234AB';
        $dateStr = strtoupper(date('dM'));
        $timeStr = gmdate('Hi').'Z';

        if ($segmentNumber !== null) {
            $lines = ["--- PNR HISTORY : SEGMENT {$segmentNumber} (RLOC: {$rloc}) ---"];
            if (! empty($pnr['segments'])) {
                foreach ($pnr['segments'] as $s) {
                    if (($s['segmentNumber'] ?? 0) === $segmentNumber) {
                        $lines[] = " 01 SS {$s['airlineCode']} {$s['flightNumber']} {$s['bookingClass']} {$s['departureDate']} {$s['departureAirport']}{$s['arrivalAirport']} HK{$s['seats']}  {$office}/{$agent}";
                        $lines[] = ' 02 STATUS HK CONFIRMED VIA AMADEUS INVENTORY';
                    }
                }
            }
            if (count($lines) === 1) {
                $lines[] = " NO HISTORY RECORDED FOR SEGMENT {$segmentNumber}";
            }

            return $lines;
        }

        $lines = ["--- PNR HISTORY : {$rloc} ---"];
        if (! empty($pnr['history'])) {
            foreach ($pnr['history'] as $idx => $histItem) {
                $idxPad = str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT);
                $lines[] = " {$idxPad} {$histItem}";
            }
        } else {
            $lines[] = " 01 {$dateStr} {$timeStr} CR-CREATION {$office}/{$agent}";
        }

        // Add history milestones
        $counter = count($lines);
        if (! empty($pnr['passengers'])) {
            foreach ($pnr['passengers'] as $p) {
                $cPad = str_pad((string) $counter, 2, '0', STR_PAD_LEFT);
                $lines[] = " {$cPad} NM1{$p['lastName']}/{$p['firstName']}";
                $counter++;
            }
        }
        if (! empty($pnr['segments'])) {
            foreach ($pnr['segments'] as $s) {
                $cPad = str_pad((string) $counter, 2, '0', STR_PAD_LEFT);
                $lines[] = " {$cPad} SS {$s['airlineCode']} {$s['flightNumber']} {$s['bookingClass']} {$s['departureDate']} {$s['departureAirport']}{$s['arrivalAirport']} HK{$s['seats']}";
                $counter++;
            }
        }
        if (! empty($pnr['receivedFrom'])) {
            $cPad = str_pad((string) $counter, 2, '0', STR_PAD_LEFT);
            $lines[] = " {$cPad} RF {$pnr['receivedFrom']}";
        }

        return $lines;
    }

    public function formatAirlineLocators(array $pnr): array
    {
        $rloc = $pnr['recordLocator'] ?? 'ABC123';
        $lines = [
            '--- RECORD LOCATOR DISPLAY (RL) ---',
            "1A / {$rloc}   (AMADEUS CENTRAL SYSTEM)",
        ];

        $carriers = [];
        if (! empty($pnr['segments'])) {
            foreach ($pnr['segments'] as $s) {
                $carrier = $s['airlineCode'] ?? '';
                if ($carrier && ! in_array($carrier, ['ARNK', 'OPEN', '1A'], true) && ! in_array($carrier, $carriers, true)) {
                    $carriers[] = $carrier;
                }
            }
        }

        if (empty($carriers)) {
            $carriers = ['QR'];
        }

        foreach ($carriers as $c) {
            $hostLocator = $c.substr(md5($rloc.$c), 0, 4);
            $lines[] = "{$c} / {$hostLocator}   (HOST SYSTEM CONFIRMED)";
        }

        return $lines;
    }

    public function formatPartialPnrDisplay(array $pnr, string $type): array
    {
        if (empty($pnr['passengers']) && empty($pnr['segments'])) {
            return ['NO PNR PRESENT IN WORK AREA'];
        }

        $rloc = $pnr['recordLocator'] ?? 'ACTIVE';
        $lines = ["--- PNR PARTIAL DISPLAY ({$type}) : {$rloc} ---"];

        // RTI: Itinerary only
        if ($type === 'RTI') {
            if (empty($pnr['segments'])) {
                $lines[] = 'NO ITINERARY SEGMENTS BOOKED';
            } else {
                foreach ($pnr['segments'] as $seg) {
                    $lines[] = "  {$seg['segmentNumber']}  {$seg['airlineCode']} {$seg['flightNumber']} {$seg['bookingClass']} {$seg['departureDate']} {$seg['departureAirport']}{$seg['arrivalAirport']} HK{$seg['seats']} {$seg['departureTime']} {$seg['arrivalTime']}";
                }
            }

            return $lines;
        }

        // RTG: General facts (OSI/SSR)
        if ($type === 'RTG') {
            $facts = $pnr['specialServiceRequests'] ?? [];
            if (empty($facts)) {
                $lines[] = 'NO GENERAL FACTS (OSI / SSR) PRESENT';
            } else {
                foreach ($facts as $idx => $f) {
                    $lines[] = '  '.($idx + 1)." {$f['type']} {$f['code']} {$f['text']}";
                }
            }

            return $lines;
        }

        // RTR: Remarks only
        if ($type === 'RTR') {
            $remarks = $pnr['remarks'] ?? [];
            if (empty($remarks)) {
                $lines[] = 'NO REMARKS RECORDED';
            } else {
                foreach ($remarks as $idx => $r) {
                    $tag = is_array($r) ? ($r['type'] ?? 'RM') : 'RM';
                    $text = is_array($r) ? ($r['text'] ?? '') : $r;
                    $lines[] = '  '.($idx + 1)." {$tag} {$text}";
                }
            }

            return $lines;
        }

        // RTF,K,I: Fare, Ticketing, Itinerary
        if ($type === 'RTF,K,I' || $type === 'RTF') {
            $lines[] = '--- FARE, TICKETING & ITINERARY ---';
            if (! empty($pnr['tsts'])) {
                $t = end($pnr['tsts']);
                $lines[] = "TST {$t['tstNumber']} - {$t['fareBasis']} TOTAL: {$t['currency']} {$t['total']}";
            }
            if (! empty($pnr['ticketing'])) {
                $lines[] = "TKT: {$pnr['ticketing']['raw']}";
            }
            if (! empty($pnr['segments'])) {
                foreach ($pnr['segments'] as $seg) {
                    $lines[] = "  {$seg['segmentNumber']}  {$seg['airlineCode']} {$seg['flightNumber']} {$seg['bookingClass']} {$seg['departureDate']} {$seg['departureAirport']}{$seg['arrivalAirport']}";
                }
            }

            return $lines;
        }

        // RT/P<N>: Specific passenger only
        if (preg_match('/^RT\/P(\d+)$/', $type, $pM)) {
            $pNum = (int) $pM[1];
            $paxList = $pnr['passengers'] ?? [];
            if (! isset($paxList[$pNum - 1])) {
                return ["RT/P{$pNum}: PASSENGER {$pNum} NOT FOUND IN PNR"];
            }
            $p = $paxList[$pNum - 1];
            $lines[] = "PASSENGER {$pNum}: {$p['lastName']}/{$p['firstName']} ".($p['title'] ?? '');
            if (! empty($p['tickets'])) {
                $lines[] = "TICKET: {$p['tickets']}";
            }

            return $lines;
        }

        return $this->formatPnrDisplay($pnr);
    }

    public function formatItineraryPrint(array $pnr, string $format, ?string $lang = null, ?int $paxIdx = null): array
    {
        $rloc = $pnr['recordLocator'] ?? 'ACTIVE';
        $isFrench = ($lang === 'FR');

        $headerTitle = match ($format) {
            'IBD' => $isFrench ? 'AFFICHAGE DE BASE DU VOYAGE (IBD)' : 'ITINERARY BASIC DISPLAY (IBD)',
            'IBP' => $isFrench ? 'IMPRESSION DE BASE DU VOYAGE (IBP)' : 'ITINERARY BASIC PRINT (IBP)',
            'IED' => $isFrench ? 'AFFICHAGE ETENDU DU VOYAGE (IED)' : 'ITINERARY EXTENDED DISPLAY (IED)',
            'IEP' => $isFrench ? 'IMPRESSION ETENDUE DU VOYAGE (IEP)' : 'ITINERARY EXTENDED PRINT (IEP)',
            'IEPJ' => $isFrench ? 'ITINERAIRE CONJOINT POUR TOUS LES PASSAGERS (IEPJ)' : 'JOINT ITINERARY PRINT FOR ALL PASSENGERS (IEPJ)',
            default => 'ITINERARY DOCUMENT',
        };

        $lines = [
            "*** {$headerTitle} ***",
            $isFrench ? "DOSSIER PASSAGER (RLOC) : {$rloc}" : "BOOKING REFERENCE (RLOC) : {$rloc}",
            $isFrench ? 'EMIS PAR : AMADEUS VOYAGES / LON1A0980' : 'ISSUED BY : AMADEUS TRAVEL ACADEMY / LON1A0980',
            '------------------------------------------------------------',
        ];

        // Passenger name
        $paxList = $pnr['passengers'] ?? [];
        if ($paxIdx && isset($paxList[$paxIdx - 1])) {
            $p = $paxList[$paxIdx - 1];
            $lines[] = ($isFrench ? 'PASSAGER : ' : 'PASSENGER : ').strtoupper($p['lastName']).'/'.strtoupper($p['firstName']).' '.($p['title'] ?? '');
        } else {
            foreach ($paxList as $idx => $p) {
                $lines[] = 'PASSENGER '.($idx + 1).' : '.strtoupper($p['lastName']).'/'.strtoupper($p['firstName']).' '.($p['title'] ?? '');
            }
        }
        $lines[] = '------------------------------------------------------------';

        // Segments
        foreach ($pnr['segments'] ?? [] as $seg) {
            $flt = $seg['airlineCode'].$seg['flightNumber'];
            $route = "{$seg['departureAirport']} -> {$seg['arrivalAirport']}";
            $date = $seg['departureDate'];
            $time = "{$seg['departureTime']} / {$seg['arrivalTime']}";
            $lines[] = "FLIGHT {$flt} - {$date} - {$route} ({$time}) CLS: {$seg['bookingClass']}";
            if ($format === 'IED' || $format === 'IEP') {
                $lines[] = $isFrench ? '  ENREGISTREMENT: 2H AVANT LE DEPART / TERMINAL VERIFIE' : '  CHECK-IN: 2 HOURS PRIOR TO DEPARTURE / TERMINAL CONFIRMED';
                $lines[] = $isFrench ? '  BAGAGE AUTORISE: 1 PIECE 23KG INCLUSE' : '  BAGGAGE ALLOWANCE: 1 PIECE 23KG INCLUDED';
            }
        }

        $lines[] = '------------------------------------------------------------';
        $lines[] = $isFrench ? 'MERCI D AVOIR CHOISI NOTRE COMPAGNIE - BON VOYAGE' : 'THANK YOU FOR CHOOSING AMADEUS - HAVE A PLEASANT FLIGHT';

        return $lines;
    }

    public function copyPnr(array $originalPnr, string $modifier): array
    {
        $newPnr = $originalPnr;
        $newPnr['recordLocator'] = null;
        $newPnr['tickets'] = [];
        $newPnr['history'] = [
            strtoupper(date('dM')).' '.gmdate('Hi').'Z CR-COPY FROM '.($originalPnr['recordLocator'] ?? 'ORIGINAL'),
        ];

        // Specific segments copy: RRN/S2,6
        if (preg_match('/S([0-9,]+)/', $modifier, $sM)) {
            $wantedSegs = array_map('intval', explode(',', $sM[1]));
            $newPnr['segments'] = array_values(array_filter(
                $originalPnr['segments'] ?? [],
                fn ($s) => in_array($s['segmentNumber'] ?? 0, $wantedSegs, true)
            ));
        }

        // Change class: RRN/CY
        if (preg_match('/C([A-Z])/', $modifier, $cM)) {
            $newClass = $cM[1];
            foreach ($newPnr['segments'] as &$s) {
                $s['bookingClass'] = $newClass;
            }
        }

        // Date shift forward: RRN/DP7
        if (preg_match('/DP(\d+)/', $modifier, $dpM)) {
            $days = (int) $dpM[1];
            foreach ($newPnr['segments'] as &$s) {
                $s['departureDate'] = date('dM', strtotime("+{$days} days"));
            }
        }

        // Date shift backward: RRN/DM3
        if (preg_match('/DM(\d+)/', $modifier, $dmM)) {
            $days = (int) $dmM[1];
            foreach ($newPnr['segments'] as &$s) {
                $s['departureDate'] = date('dM', strtotime("-{$days} days"));
            }
        }

        // Passenger count: RRN/4
        if (preg_match('/^\/?(\d+)$/', $modifier, $paxM)) {
            $pCount = (int) $paxM[1];
            $basePax = $originalPnr['passengers'][0] ?? ['lastName' => 'SMITH', 'firstName' => 'JOHN', 'title' => 'MR', 'type' => 'ADT'];
            $newPaxList = [];
            for ($i = 1; $i <= $pCount; $i++) {
                $newPaxList[] = [
                    'id' => "{$i}.1",
                    'lastName' => $basePax['lastName'],
                    'firstName' => "PAX{$i}",
                    'title' => 'MR',
                    'type' => 'ADT',
                ];
            }
            $newPnr['passengers'] = $newPaxList;
        }

        // Passenger data only: RRP/P2
        if (str_starts_with($modifier, 'P')) {
            $newPnr['segments'] = [];
        }

        // Itinerary segments only: RRI
        if ($modifier === 'I') {
            $newPnr['passengers'] = [];
        }

        return $newPnr;
    }

    public function formatPrintout(array $pnr, bool $full = false): array
    {
        $printerName = 'PRT01 (LON1A0980)';
        $title = $full ? 'FULL HARDCOPY PRINTOUT (WRA)' : 'SCREEN PRINTOUT (WRS)';
        $pnrDisplay = $this->formatPnrDisplay($pnr);

        return array_merge(
            [
                "PRINT JOB SENT TO DEFAULT PRINTER: {$printerName}",
                "*** {$title} ***",
                '------------------------------------------------------------',
            ],
            $pnrDisplay,
            [
                '------------------------------------------------------------',
                $full ? 'ALL PAGES PRINTED SUCCESSFULLY' : 'PAGE 1 OF 1 PRINTED SUCCESSFULLY',
            ]
        );
    }
}
