<?php

namespace App\Services\Amadeus;

use App\Models\Airline;

class TicketingResult
{
    public function __construct(
        public array $lines,
        public array $tickets = [],
        public bool $isError = false
    ) {}
}

class TicketingService
{
    public function issueTicketTTP(array $pnr, string $officeId): TicketingResult
    {
        if (empty($pnr['segments'])) {
            return new TicketingResult(['NEED ITINERARY TO ISSUE TICKET'], [], true);
        }

        if (empty($pnr['passengers'])) {
            return new TicketingResult(['NEED NAME ELEMENT TO ISSUE TICKET'], [], true);
        }

        if (empty($pnr['fop'])) {
            return new TicketingResult(['NEED FORM OF PAYMENT - ENTER FP CASH OR FP CC...'], [], true);
        }

        if (empty($pnr['tsts'])) {
            return new TicketingResult(['NO TST STORED - PRICE FIRST WITH FXP'], [], true);
        }

        if (empty($pnr['receivedFrom'])) {
            return new TicketingResult(['NEED RECEIVED FROM - ENTER RF <NAME>'], [], true);
        }

        $primaryCarrier = $pnr['segments'][0]['airlineCode'];
        $airline = Airline::find($primaryCarrier);
        $prefix = $airline?->ticket_prefix ?: '157';

        $newTickets = [];
        $outputLines = [];
        $dateStr = strtoupper(date('dM').substr(date('Y'), -2));

        foreach ($pnr['passengers'] as $idx => $pax) {
            $randomTicketSeq = (string) random_int(1000000000, 9999999999);
            $fullTicketNumber = "{$prefix}-{$randomTicketSeq}";

            $coupons = [];
            foreach ($pnr['segments'] as $cIdx => $seg) {
                $coupons[] = [
                    'couponNo' => $cIdx + 1,
                    'flight' => "{$seg['airlineCode']} {$seg['flightNumber']}",
                    'from' => $seg['departureAirport'],
                    'to' => $seg['arrivalAirport'],
                    'date' => $seg['departureDate'],
                    'class' => $seg['bookingClass'],
                    'status' => 'O',
                ];
            }

            $title = ! empty($pax['title']) ? ' '.$pax['title'] : '';
            $totalFare = ! empty($pnr['tsts'][0]) ? "{$pnr['tsts'][0]['currency']} ".number_format($pnr['tsts'][0]['total'], 2, '.', '') : 'N/A';

            $ticket = [
                'ticketNumber' => $fullTicketNumber,
                'passengerName' => trim("{$pax['lastName']}/{$pax['firstName']}{$title}"),
                'pnrLocator' => $pnr['recordLocator'] ?? 'ACTIVE',
                'issueDate' => $dateStr,
                'officeId' => $officeId,
                'fop' => $pnr['fop']['details'] ?? 'CASH',
                'totalFare' => $totalFare,
                'coupons' => $coupons,
            ];

            $newTickets[] = $ticket;
            $outputLines[] = "OK ETICKET / {$fullTicketNumber} / ".strtoupper($pax['lastName']).' '.strtoupper($pax['firstName']);
        }

        $count = count($newTickets);
        $outputLines[] = "TICKETING COMPLETED - {$count} ETKT(S) GENERATED";

        return new TicketingResult($outputLines, $newTickets);
    }

    public function displayTicketTWD(array $pnr, ?string $ticketNoArg = null): TicketingResult
    {
        if (empty($pnr['tickets'])) {
            return new TicketingResult(['NO ELECTRONIC TICKETS FOUND FOR THIS PNR'], [], true);
        }

        $ticket = $pnr['tickets'][0];
        if ($ticketNoArg) {
            foreach ($pnr['tickets'] as $t) {
                if (str_contains($t['ticketNumber'], $ticketNoArg)) {
                    $ticket = $t;
                    break;
                }
            }
        }

        $lines = [
            "ELECTRONIC TICKET RECORD: {$ticket['ticketNumber']}",
            'NAME: '.str_pad($ticket['passengerName'], 26)."RLOC: {$ticket['pnrLocator']}",
            "ISSUED: {$ticket['issueDate']}  OFFICE: {$ticket['officeId']}  FOP: {$ticket['fop']}",
            "TOTAL FARE: {$ticket['totalFare']}",
            '------------------------------------------------------------',
            'CPN FLIGHT  CLASS DATE   FROM  TO    STATUS',
        ];

        foreach ($ticket['coupons'] as $c) {
            $status = ($c['status'] === 'O') ? 'OPEN' : $c['status'];
            $lines[] = ' '.str_pad((string) $c['couponNo'], 2).' '.
                str_pad($c['flight'], 8).' '.
                $c['class'].'   '.
                str_pad($c['date'], 6).' '.
                str_pad($c['from'], 5).' '.
                str_pad($c['to'], 5).' '.
                $status;
        }

        $lines[] = '------------------------------------------------------------';
        $prefix = substr($ticket['ticketNumber'], 0, 3);
        $lines[] = "VALIDATING CARRIER: {$prefix}";

        return new TicketingResult($lines);
    }
}
