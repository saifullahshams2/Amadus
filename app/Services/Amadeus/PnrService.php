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
            'specialServiceRequests' => [],
            'tsts' => [],
            'tickets' => [],
        ];
    }

    public function generateRecordLocator(): string
    {
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $res = '';
        for ($i = 0; $i < 6; $i++) {
            $res .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $res;
    }

    public function validateMandatoryPrintElements(?array $pnr): PnrValidationResult
    {
        if (!$pnr) {
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
        if (!$pnr || (empty($pnr['passengers']) && empty($pnr['segments']) && empty($pnr['contacts']))) {
            return ['NO PNR PRESENT IN WORK AREA'];
        }

        $lines = [];
        $dateStr = strtoupper(date('dM') . substr(date('Y'), -2));
        $timeStr = gmdate('Hi') . 'Z';
        $rloc = $pnr['recordLocator'] ?? '---';

        $office = $pnr['creationOffice'] ?? 'LON1A0980';
        $agent = $pnr['creationAgent'] ?? '1234AB';

        $lines[] = '--- RLR ---';
        $lines[] = "RP/{$office}/{$agent}            {$dateStr}/{$timeStr}   {$rloc}";

        // 1. Passengers
        if (!empty($pnr['passengers'])) {
            foreach ($pnr['passengers'] as $idx => $pax) {
                $paxStr = "  " . ($idx + 1) . "." . strtoupper($pax['lastName']) . "/" . strtoupper($pax['firstName']);
                if (!empty($pax['title'])) {
                    $paxStr .= ' ' . strtoupper($pax['title']);
                }
                if (($pax['type'] ?? '') === 'CHD') {
                    $paxStr .= "(CHD/" . ($pax['dateOfBirth'] ?? '') . ")";
                }
                if (($pax['type'] ?? '') === 'INF') {
                    $paxStr .= "(INF/" . ($pax['accompaniedBy'] ?? '') . ")";
                }
                $lines[] = $paxStr;
            }
        }

        $elementCounter = 1;

        // 2. Flight Segments
        if (!empty($pnr['segments'])) {
            foreach ($pnr['segments'] as $seg) {
                $fltNo = str_pad($seg['flightNumber'], 3, ' ', STR_PAD_LEFT);
                $seats = $seg['seats'];
                $status = ($seg['status'] ?? 'HK') . $seats;
                $lines[] = "  {$seg['segmentNumber']}  {$seg['airlineCode']} {$fltNo} {$seg['bookingClass']} {$seg['departureDate']} {$seg['departureAirport']}{$seg['arrivalAirport']} {$status}  {$seg['departureTime']} {$seg['arrivalTime']}   *1A/E*";
                $elementCounter++;
            }
        }

        // 3. Contacts
        if (!empty($pnr['contacts'])) {
            foreach ($pnr['contacts'] as $c) {
                $type = ($c['type'] === 'EMAIL') ? 'APE' : 'AP';
                $lines[] = "  {$elementCounter} {$type} {$c['detail']}";
                $elementCounter++;
            }
        }

        // 4. Ticketing
        if (!empty($pnr['ticketing'])) {
            $raw = preg_replace('/^TK/', '', $pnr['ticketing']['raw'] ?? 'TKOK');
            $lines[] = "  {$elementCounter} TK {$raw}";
            $elementCounter++;
        }

        // 5. Special Service Requests
        if (!empty($pnr['specialServiceRequests'])) {
            foreach ($pnr['specialServiceRequests'] as $sr) {
                $lines[] = "  {$elementCounter} {$sr['type']} {$sr['code']} {$sr['text']}";
                $elementCounter++;
            }
        }

        // 6. Remarks
        if (!empty($pnr['remarks'])) {
            foreach ($pnr['remarks'] as $rem) {
                $lines[] = "  {$elementCounter} RM {$rem}";
                $elementCounter++;
            }
        }

        // 7. Issued Tickets (FA lines)
        if (!empty($pnr['tickets'])) {
            $carrier = $pnr['segments'][0]['airlineCode'] ?? '1A';
            foreach ($pnr['tickets'] as $tkt) {
                $lines[] = "  {$elementCounter} FA PAX {$tkt['ticketNumber']}/ET{$carrier}/{$dateStr}";
                $elementCounter++;
            }
        }

        // 8. Form of Payment
        if (!empty($pnr['fop'])) {
            $lines[] = "  {$elementCounter} FP {$pnr['fop']['details']}";
            $elementCounter++;
        }

        // 9. Commission
        if (!empty($pnr['commission'])) {
            $lines[] = "  {$elementCounter} FM {$pnr['commission']}";
            $elementCounter++;
        }

        // 10. Received From
        if (!empty($pnr['receivedFrom'])) {
            $lines[] = "  {$elementCounter} RF {$pnr['receivedFrom']}";
            $elementCounter++;
        }

        return $lines;
    }
}
