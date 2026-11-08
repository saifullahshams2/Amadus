<?php

namespace App\Services\Amadeus;

use App\Models\Airport;

class PricingResult
{
    /**
     * @param  array<int, string>  $lines
     */
    public function __construct(
        public array $lines,
        public ?array $tst = null,
        public bool $isError = false,
        public ?array $updatedSegments = null
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
        $baseFarePerPax = (int) round($totalBaseUsd * $currencyMultiplier);
        $taxAirport = (int) round(45 * $currencyMultiplier);
        $taxFuel = (int) round(85 * $currencyMultiplier);
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

        $paxTitle = ! empty($firstPax['title']) ? ' '.$firstPax['title'] : '';
        $fltNum = str_pad($firstSeg['flightNumber'], 4, ' ', STR_PAD_LEFT);

        $lines = [
            "--- TST 0000{$tstNumber} ---",
            'FARESTORED',
            '1.'.strtoupper($firstPax['lastName']).'/'.strtoupper($firstPax['firstName']).$paxTitle,
            " {$firstSeg['departureAirport']} {$firstSeg['airlineCode']} {$fltNum} {$firstSeg['bookingClass']} {$firstSeg['departureDate']} {$firstSeg['departureTime']} OK {$firstSeg['bookingClass']}IF",
            " {$firstSeg['arrivalAirport']}",
            'FARE       '.str_pad($currency, 4).' '.str_pad(number_format($baseFarePerPax, 2, '.', ''), 10, ' ', STR_PAD_LEFT),
            'TAX        '.str_pad($currency, 4).' '.str_pad(number_format($taxAirport, 2, '.', ''), 8, ' ', STR_PAD_LEFT)."AP  {$currency} ".str_pad(number_format($taxFuel, 2, '.', ''), 8, ' ', STR_PAD_LEFT).'YQ',
            'TOTAL      '.str_pad($currency, 4).' '.str_pad(number_format($grandTotalPerPax, 2, '.', ''), 10, ' ', STR_PAD_LEFT),
        ];

        if ($numPax > 1) {
            $totalFormatted = number_format($grandTotalPerPax * $numPax, 2, '.', '');
            $lines[] = "GRAND TOTAL FOR {$numPax} PASSENGERS: {$currency} {$totalFormatted}";
        }

        if ($storeTst) {
            $lines[] = 'TST RETAINED - TICKET WITH TTP';
        }

        return new PricingResult($lines, $storeTst ? $tst : null);
    }

    public function handleFQD(string $query): PricingResult
    {
        $clean = strtoupper(trim($query));
        if (! preg_match('/^([A-Z]{6})(?:\/D(\d{1,2}[A-Z]{3}))?(?:\/A([A-Z0-9]{2}))?/', $clean, $matches)) {
            return new PricingResult([
                'FORMAT: FQD <CITYPAIR> [/D<DATE>] [/A<CARRIER>] (E.G. FQDDOHLHR/D15NOV/AQR)',
            ], null, true);
        }

        $orig = substr($matches[1], 0, 3);
        $dest = substr($matches[1], 3, 3);
        $dateStr = $matches[2] ?? '15NOV';
        $carrier = $matches[3] ?? 'BA';

        $lines = [
            "FQD {$orig}{$dest}/D{$dateStr}/A{$carrier}                      ROE 1.000000",
            "PUBLIC FARES FOR {$orig}-{$dest} ON {$carrier}",
            'LN FARE BASIS   OW/RT   PEN  DATES    DAYS  CUR      AMOUNT',
            '------------------------------------------------------------',
            '01 YIF          RT      NO   -        -     USD     1250.00',
            '02 BIF          RT      NO   -        -     USD     1080.00',
            '03 MIF          RT      YES  -        -     USD      890.00',
            '04 HIF          RT      YES  -        -     USD      740.00',
            '05 KIF          RT      YES  -        -     USD      620.00',
            '06 LIF          RT      YES  -        -     USD      530.00',
            '07 JFF          RT      NO   -        -     USD     3800.00',
            '08 CFF          RT      NO   -        -     USD     3200.00',
            '09 FFF          RT      NO   -        -     USD     7500.00',
        ];

        return new PricingResult($lines);
    }

    /**
     * Handle Fare Diagnostic: FXX/D F-Y2RT or FQPLONSINTYO/S/DF-Y2RT
     */
    public function handleFareDiagnostic(string $input, array $pnr): PricingResult
    {
        $clean = strtoupper(trim($input));
        $fareBasis = 'Y2RT';
        if (preg_match('/F-([A-Z0-9]+)/', $clean, $fbM)) {
            $fareBasis = $fbM[1];
        }

        return new PricingResult([
            "--- AMADEUS FARE DIAGNOSTIC: {$fareBasis} ---",
            "TARGET FARE BASIS: {$fareBasis}",
            'RULE AUDIT       : RULE 001 - STANDARD SEASONAL FARE',
            'ELIGIBILITY      : FLIGHT ROUTING SATISFIES MAXIMUM PERMITTED MILEAGE (MPM)',
            'MIN / MAX STAY   : 3 DAYS MINIMUM / 12 MONTHS MAXIMUM (QUALIFIED)',
            'PENALTY / CHG    : NON-REFUNDABLE / REBOOKING PERMITTED WITH USD 150 FEE',
            'BOOKING CODE     : PRIMARY RBD REQUIRED: Y (CURRENT SEGMENTS COMPLIANT)',
            'DIAGNOSTIC STATUS: FARE APPLICABLE TO CURRENT ITINERARY',
        ]);
    }

    /**
     * Handle Best Pricer: FXA, FXU2, FXZ2, FXL
     */
    public function handleBestPricer(string $input, array $pnr): PricingResult
    {
        $clean = strtoupper(trim($input));
        $segments = $pnr['segments'] ?? [];

        if (empty($segments)) {
            return new PricingResult(['FXA: NO ITINERARY SEGMENTS TO PRICE'], null, true);
        }

        // 1. FXU<LINE>: Select cheaper fare, rebook classes, do not create TST
        if (preg_match('/^FXU(\d+)$/', $clean, $uM)) {
            $line = $uM[1];
            $updatedSegs = $segments;
            foreach ($updatedSegs as &$s) {
                $s['bookingClass'] = 'V'; // Rebook to lowest class V
            }

            return new PricingResult([
                "FXU{$line} - ITINERARY REBOOKED TO LOWER CLASS V WITHOUT TST",
                'ALL SEGMENTS UPDATED TO CLASS V (STATUS: HK)',
                'ENTER "FXP" TO PRICE AND STORE NEW TST',
            ], null, false, $updatedSegs);
        }

        // 2. FXZ<LINE>: Display lowest possible fare and advise seat availability
        if (preg_match('/^FXZ(\d+)$/', $clean, $zM)) {
            $line = $zM[1];

            return new PricingResult([
                "--- BEST PRICER: LOWEST POSSIBLE FARE ADVISORY (FXZ{$line}) ---",
                'LOWEST FARE BASIS : VIF (SAVINGS: 40% OFF CURRENT CLASS)',
                'TOTAL FARE        : USD 390.00 + TAXES USD 130.00 = USD 520.00',
                'SEAT AVAILABILITY : SEATS AVAILABLE ON REQUESTED FLIGHTS IN V CLASS (V4)',
                'RECOMMENDED ENTRY : FXU1 TO CONFIRM REBOOKING',
            ]);
        }

        // 3. FXL: Best pricer without auto rebooking
        if ($clean === 'FXL') {
            return new PricingResult([
                '--- BEST PRICER: MANUAL REBOOKING LIST (FXL) ---',
                'LOWEST ALTERNATIVE FARE: USD 520.00 (V CLASS)',
                'AUTOMATIC REBOOKING SUPPRESSED FOR STANDARD ACCESS CARRIER',
                'MANUALLY REBOOK SEGMENTS USING SB ENTRY (E.G. SBV)',
            ]);
        }

        // Default FXA: Display list of lower fares
        return new PricingResult([
            '--- BEST PRICER: LOWER FARES AVAILABLE (FXA) ---',
            'LN FARE BASIS   CLS   BASE FARE   TAXES    TOTAL FARE   SAVINGS',
            '-----------------------------------------------------------------',
            '01 VIF          V     USD 260.00  130.00   USD 390.00   USD 260.00',
            '02 LIF          L     USD 310.00  130.00   USD 440.00   USD 210.00',
            '03 KIF          K     USD 360.00  130.00   USD 490.00   USD 160.00',
            'ENTER "FXU1" TO REBOOK TO LOWEST FARE ON LINE 1',
        ]);
    }

    /**
     * Handle FQC (Currency & ROE), FQA (Baggage), FQN, FQR, FQP, FQM
     */
    public function handleFareAuxiliary(string $input): PricingResult
    {
        $clean = strtoupper(trim($input));

        // 1. FQC: Currency and Rate of Exchange
        if (str_starts_with($clean, 'FQC')) {
            $rest = trim(substr($clean, 3));

            return new PricingResult([
                "--- FARE QUOTE CURRENCY & ROE: {$rest} (FQC) ---",
                'IATA BANKERS RATE OF EXCHANGE (ROE) EFFECTIVE CURRENT MONTH:',
                'USD 1.00 = GBP 0.781200 / EUR 0.923400 / INR 84.120000',
                'USD 1.00 = QAR 3.640000 / AED 3.672500 / SAR 3.750000',
                'ROUNDING RULES APPLIED PER IATA RESOLUTION 024A',
            ]);
        }

        // 2. FQA: Excess baggage
        if (str_starts_with($clean, 'FQA')) {
            return new PricingResult([
                '--- FARE QUOTE EXCESS BAGGAGE CHARGES (FQA) ---',
                'PIECE SYSTEM (USA/CANADA)  : USD 150.00 PER ADDITIONAL 23KG BAG',
                'WEIGHT SYSTEM (INTL)       : 1.5% OF HIGHEST ONE-WAY NORMAL DIRECT ADT FARE',
                'CALCULATED RATE PER KG     : USD 35.00 / KG EXCESS WEIGHT',
            ]);
        }

        // 3. FQN: Fare Quote Notes / Rules
        if (str_starts_with($clean, 'FQN')) {
            return new PricingResult([
                '--- FARE QUOTE RULES & CONDITIONS (FQN) ---',
                'CATEGORY 04 : FLIGHT APPLICATION - VALID ONLY ON FLIGHTS SPECIFIED',
                'CATEGORY 05 : ADVANCE RESERVATION / TICKETING - 7 DAYS ADVANCE',
                'CATEGORY 16 : PENALTIES - NON-REFUNDABLE IN CASE OF CANCEL',
                'CATEGORY 19 : DISCOUNTS - CHILDREN 25% / INFANTS 90%',
            ]);
        }

        // 4. FQR: Fare Quote Routing
        if (str_starts_with($clean, 'FQR')) {
            return new PricingResult([
                '--- FARE QUOTE ROUTING DIRECTORY (FQR) ---',
                'ROUTING 001 : NON-STOP PERMITTED',
                'ROUTING 002 : TRANSFER PERMITTED VIA ONE HUB CARRIER POINT',
                'MAXIMUM PERMITTED MILEAGE (MPM) MUST NOT BE EXCEEDED BY >25%',
            ]);
        }

        // 5. FQP: Fare Quote Pricing
        if (str_starts_with($clean, 'FQP')) {
            return new PricingResult([
                '--- FARE QUOTE SPECIFIC ITINERARY PRICING (FQP) ---',
                'PRICING COMPLIANT WITH HISTORICAL/PUBLISHED TARIFF',
                'BASE FARE : USD 650.00 / SURCHARGES : USD 130.00 / TOTAL : USD 780.00',
            ]);
        }

        // 6. FQM: Fare Quote Mileage
        if (str_starts_with($clean, 'FQM')) {
            return new PricingResult([
                '--- FARE QUOTE MILEAGE AUDIT (FQM) ---',
                'TICKETED POINT MILEAGE (TPM) : 3450 MILES',
                'MAXIMUM PERMITTED MILEAGE (MPM): 4140 MILES',
                'SURCHARGE BAND : WITHIN DIRECT ROUTE TOLERANCE (0% SURCHARGE)',
            ]);
        }

        return new PricingResult(["UNKNOWN FARE AUXILIARY ENTRY: {$clean}"], null, true);
    }

    /**
     * Display stored TST breakdown (TQT)
     */
    public function handleTQT(array $pnr): PricingResult
    {
        $tsts = $pnr['tsts'] ?? [];
        if (empty($tsts)) {
            return new PricingResult(['TQT: NO STORED TST PRESENT IN ACTIVE PNR'], null, true);
        }

        $tst = end($tsts);
        $lines = [
            "--- STORED TST DISPLAY (TST {$tst['tstNumber']}) ---",
            "FARE BASIS : {$tst['fareBasis']}",
            "BASE FARE  : {$tst['currency']} ".number_format($tst['baseFare'], 2, '.', ''),
        ];
        foreach ($tst['taxes'] as $tax) {
            $lines[] = "TAX {$tax['code']}    : {$tst['currency']} ".number_format($tax['amount'], 2, '.', '');
        }
        $lines[] = "TOTAL      : {$tst['currency']} ".number_format($tst['total'], 2, '.', '');
        $lines[] = 'STATUS     : TST VALID / UNTICKETED';

        return new PricingResult($lines);
    }
}
