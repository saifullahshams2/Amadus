<?php

namespace App\Services\Amadeus;

use App\Models\Airport;

class PricingResult
{
    public function __construct(
        public array $lines,
        public ?array $tst = null,
        public bool $isError = false
    ) {}
}

class PricingService
{
    private const BASE_FARES = [
        'Y' => 650,
        'B' => 580,
        'M' => 490,
        'H' => 420,
        'K' => 360,
        'L' => 310,
        'V' => 260,
        'W' => 980,
        'E' => 850,
        'J' => 2400,
        'C' => 2100,
        'D' => 1800,
        'I' => 1450,
        'F' => 4800,
        'A' => 3900,
    ];

    private const CURRENCY_MAP = [
        'QA' => ['cur' => 'QAR', 'mult' => 3.64],
        'AE' => ['cur' => 'AED', 'mult' => 3.67],
        'SA' => ['cur' => 'SAR', 'mult' => 3.75],
        'KW' => ['cur' => 'KWD', 'mult' => 0.31],
        'GB' => ['cur' => 'GBP', 'mult' => 0.78],
        'US' => ['cur' => 'USD', 'mult' => 1.00],
        'FR' => ['cur' => 'EUR', 'mult' => 0.92],
        'DE' => ['cur' => 'EUR', 'mult' => 0.92],
        'IT' => ['cur' => 'EUR', 'mult' => 0.92],
        'ES' => ['cur' => 'EUR', 'mult' => 0.92],
        'NL' => ['cur' => 'EUR', 'mult' => 0.92],
        'IN' => ['cur' => 'INR', 'mult' => 84.0],
        'AU' => ['cur' => 'AUD', 'mult' => 1.52],
        'CA' => ['cur' => 'CAD', 'mult' => 1.38],
        'JP' => ['cur' => 'JPY', 'mult' => 152.0],
    ];

    public function calculateFare(array $pnr, bool $storeTst = true): PricingResult
    {
        if (empty($pnr['segments'])) {
            return new PricingResult(['NO ITINERARY TO PRICE'], null, true);
        }

        if (empty($pnr['passengers'])) {
            return new PricingResult(['NEED NAME ELEMENT FOR PRICING'], null, true);
        }

        $origCode = $pnr['segments'][0]['departureAirport'];
        $origAirport = Airport::find($origCode);

        $currency = 'USD';
        $currencyMultiplier = 1.0;

        if ($origAirport && isset(self::CURRENCY_MAP[$origAirport->country])) {
            $info = self::CURRENCY_MAP[$origAirport->country];
            $currency = $info['cur'];
            $currencyMultiplier = $info['mult'];
        }

        $totalBaseUsd = 0;
        foreach ($pnr['segments'] as $seg) {
            $cls = $seg['bookingClass'] ?? 'Y';
            $fare = self::BASE_FARES[$cls] ?? 450;
            $totalBaseUsd += $fare;
        }

        $numPax = count($pnr['passengers']);
        $baseFarePerPax = (int)round($totalBaseUsd * $currencyMultiplier);
        $taxAirport = (int)round(45 * $currencyMultiplier);
        $taxFuel = (int)round(85 * $currencyMultiplier);
        $totalTaxPerPax = $taxAirport + $taxFuel;
        $grandTotalPerPax = $baseFarePerPax + $totalTaxPerPax;

        $firstPax = $pnr['passengers'][0];
        $tstNumber = count($pnr['tsts'] ?? []) + 1;
        $firstSeg = $pnr['segments'][0];

        $tst = [
            'tstNumber' => $tstNumber,
            'fareBasis' => "{$firstSeg['bookingClass']}IF",
            'baseFare' => $baseFarePerPax,
            'currency' => $currency,
            'taxes' => [
                ['code' => 'AP', 'amount' => $taxAirport],
                ['code' => 'YQ', 'amount' => $taxFuel],
            ],
            'total' => $grandTotalPerPax * $numPax,
            'passengerIds' => array_column($pnr['passengers'], 'id'),
            'segments' => array_column($pnr['segments'], 'segmentNumber'),
            'issuedAt' => date('c'),
        ];

        $paxTitle = !empty($firstPax['title']) ? ' ' . $firstPax['title'] : '';
        $fltNum = str_pad($firstSeg['flightNumber'], 4, ' ', STR_PAD_LEFT);

        $lines = [
            "--- TST 0000{$tstNumber} ---",
            "FARESTORED",
            "1." . strtoupper($firstPax['lastName']) . "/" . strtoupper($firstPax['firstName']) . $paxTitle,
            " {$firstSeg['departureAirport']} {$firstSeg['airlineCode']} {$fltNum} {$firstSeg['bookingClass']} {$firstSeg['departureDate']} {$firstSeg['departureTime']} OK {$firstSeg['bookingClass']}IF",
            " {$firstSeg['arrivalAirport']}",
            "FARE       " . str_pad($currency, 4) . " " . str_pad(number_format($baseFarePerPax, 2, '.', ''), 10, ' ', STR_PAD_LEFT),
            "TAX        " . str_pad($currency, 4) . " " . str_pad(number_format($taxAirport, 2, '.', ''), 8, ' ', STR_PAD_LEFT) . "AP  {$currency} " . str_pad(number_format($taxFuel, 2, '.', ''), 8, ' ', STR_PAD_LEFT) . "YQ",
            "TOTAL      " . str_pad($currency, 4) . " " . str_pad(number_format($grandTotalPerPax, 2, '.', ''), 10, ' ', STR_PAD_LEFT),
        ];

        if ($numPax > 1) {
            $totalFormatted = number_format($grandTotalPerPax * $numPax, 2, '.', '');
            $lines[] = "GRAND TOTAL FOR {$numPax} PASSENGERS: {$currency} {$totalFormatted}";
        }

        if ($storeTst) {
            $lines[] = "TST RETAINED - TICKET WITH TTP";
        }

        return new PricingResult($lines, $storeTst ? $tst : null);
    }

    public function handleFQD(string $query): PricingResult
    {
        $clean = strtoupper(trim($query));
        if (!preg_match('/^([A-Z]{6})(?:\/D(\d{1,2}[A-Z]{3}))?(?:\/A([A-Z0-9]{2}))?/', $clean, $matches)) {
            return new PricingResult([
                'FORMAT: FQD <CITYPAIR> [/D<DATE>] [/A<CARRIER>] (E.G. FQDDOHLHR/D15NOV/AQR)'
            ], null, true);
        }

        $orig = substr($matches[1], 0, 3);
        $dest = substr($matches[1], 3, 3);
        $dateStr = $matches[2] ?? '15NOV';
        $carrier = $matches[3] ?? 'BA';

        $lines = [
            "FQD {$orig}{$dest}/D{$dateStr}/A{$carrier}                      ROE 1.000000",
            "PUBLIC FARES FOR {$orig}-{$dest} ON {$carrier}",
            "LN FARE BASIS   OW/RT   PEN  DATES    DAYS  CUR      AMOUNT",
            "------------------------------------------------------------",
            "01 YIF          RT      NO   -        -     USD     1250.00",
            "02 BIF          RT      NO   -        -     USD     1080.00",
            "03 MIF          RT      YES  -        -     USD      890.00",
            "04 HIF          RT      YES  -        -     USD      740.00",
            "05 KIF          RT      YES  -        -     USD      620.00",
            "06 LIF          RT      YES  -        -     USD      530.00",
            "07 JFF          RT      NO   -        -     USD     3800.00",
            "08 CFF          RT      NO   -        -     USD     3200.00",
            "09 FFF          RT      NO   -        -     USD     7500.00",
        ];

        return new PricingResult($lines);
    }
}
