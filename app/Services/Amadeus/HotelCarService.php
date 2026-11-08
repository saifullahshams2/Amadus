<?php

namespace App\Services\Amadeus;

class HotelCarResult
{
    /**
     * @param  array<int, string>  $lines
     */
    public function __construct(
        public array $lines,
        public array $updatedState,
        public bool $isError = false
    ) {}
}

class HotelCarService
{
    /**
     * Handle Hotel and Car commands.
     */
    public function handle(string $input, array $state): HotelCarResult
    {
        $clean = strtoupper(trim($input));

        // 1. Hotel List Display: HL <CITY>
        if (str_starts_with($clean, 'HL')) {
            $city = trim(substr($clean, 2));
            if ($city === '') {
                $city = 'LON';
            }

            return new HotelCarResult([
                "--- AMADEUS HOTEL LIST DISPLAY: {$city} (HL) ---",
                'LN CHAIN CODE PROPERTY NAME                      DIST  LOC  PHONE',
                '----------------------------------------------------------------------',
                '01  HH   0123 HILTON PARK LANE                   0.5M  DT   +44 207 208 4000',
                '02  MC   0456 MARRIOTT GROSVENOR SQUARE          0.8M  DT   +44 207 493 1232',
                '03  RD   0789 RADISSON BLU EDWARDIAN             1.2M  DT   +44 207 629 7474',
                '04  BW   0912 BEST WESTERN PREMIER               2.5M  W    +44 207 373 7878',
                '05  IC   0345 INTERCONTINENTAL LONDON PARK LANE  0.4M  DT   +44 207 409 3131',
                'ENTER "HA <CITY> <DATE>" FOR REAL-TIME ROOM RATES & AVAILABILITY',
            ], $state);
        }

        // 2. Hotel Availability: HA <CITY> <DATE>
        if (str_starts_with($clean, 'HA')) {
            $parts = explode(' ', trim(substr($clean, 2)));
            $city = $parts[0] ?: 'LON';
            $date = $parts[1] ?? '15DEC';

            return new HotelCarResult([
                "--- HOTEL AVAILABILITY: {$city} / IN: {$date} / 1 NIGHT (HA) ---",
                'LN PROPERTY                    ROOM/RATE TYPE   CUR   DAILY RATE  STATUS',
                '------------------------------------------------------------------------',
                '01 HILTON PARK LANE (HH)       A1D DELUXE KING   GBP     285.00    OPEN',
                '02 MARRIOTT GROSVENOR SQ (MC)  B1K EXEC SUITE    GBP     340.00    OPEN',
                '03 RADISSON BLU (RD)           A1Q STANDARD DBL  GBP     195.00    OPEN',
                '04 BEST WESTERN PREMIER (BW)   ROH RUN OF HOUSE  GBP     145.00    OPEN',
                'BAR GUARANTEE APPLIES - ENTER "HS <LN>" TO SELL ROOM INTO PNR',
            ], $state);
        }

        // 3. Hotel Sell: HS <LINE>
        if (str_starts_with($clean, 'HS')) {
            $line = trim(substr($clean, 2)) ?: '1';
            $segNum = count($state['activePnr']['segments'] ?? []) + 1;

            $hotelSegment = [
                'segmentNumber' => $segNum,
                'airlineCode' => 'HTL',
                'flightNumber' => 'HH01',
                'bookingClass' => 'A1D',
                'departureAirport' => 'LON',
                'arrivalAirport' => 'LON',
                'departureDate' => '15DEC',
                'departureTime' => '1400',
                'arrivalTime' => '1100',
                'seats' => 1,
                'status' => 'HK',
                'equipment' => 'HOTEL',
                'isPassive' => false,
                'rawDetails' => 'HILTON PARK LANE / A1D DELUXE KING / 1 NIGHT / CONFIRMED',
            ];
            $state['activePnr']['segments'][] = $hotelSegment;

            return new HotelCarResult([
                "HS - HOTEL SEGMENT {$segNum} CONFIRMED (HK1)",
                'PROPERTY : HILTON PARK LANE LONDON (HH)',
                'ROOM TYPE: A1D DELUXE KING - NON-SMOKING',
                'RATE     : GBP 285.00 PER NIGHT (BEST AVAILABLE RATE)',
                'STATUS   : HK1 (CONFIRMED / GUARANTEED BY CC)',
            ], $state);
        }

        // 4. Hotel Features / Policy / Pricing / Terms: HF, HP, HPR, HRC, HT, HR
        if (str_starts_with($clean, 'HF') || str_starts_with($clean, 'HP') || str_starts_with($clean, 'HT') || str_starts_with($clean, 'HR')) {
            $cmd = substr($clean, 0, 2);

            return new HotelCarResult([
                "--- AMADEUS HOTEL PROPERTY INFORMATION ({$cmd}) ---",
                'PROPERTY     : HILTON PARK LANE LONDON',
                'CHECK-IN     : 15:00 LOCAL / CHECK-OUT: 12:00 LOCAL',
                'CANCELLATION : FREE CANCELLATION UP TO 24 HOURS PRIOR TO ARRIVAL',
                'GUARANTEE    : CREDIT CARD GUARANTEE REQUIRED AT TIME OF BOOKING',
                'AMENITIES    : COMPLIMENTARY WI-FI, FITNESS CENTER, EXECUTIVE LOUNGE',
                'POINTS OF REF: 14 MILES FROM LHR AIRPORT / 0.2 MILES TO HYDE PARK',
            ], $state);
        }

        // 5. Car List Display: CL <CITY>
        if (str_starts_with($clean, 'CL')) {
            $city = trim(substr($clean, 2)) ?: 'LAX';

            return new HotelCarResult([
                "--- AMADEUS CAR RENTAL DIRECTORY: {$city} (CL) ---",
                'LN COMPANY CODE NAME                     LOC  AIRPORT SHUTTLE',
                '-----------------------------------------------------------------',
                '01  ZI   AVIS RENT A CAR                 ON   TERMINAL COURTESY BUS',
                '02  ZE   HERTZ RENT A CAR                ON   FREQUENT SHUTTLE BUS',
                '03  ET   ENTERPRISE RENT-A-CAR           OFF  5 MINS SHUTTLE',
                '04  ZD   BUDGET CAR RENTAL               ON   AIRPORT SHUTTLE',
                '05  SX   SIXT RENT A CAR                 OFF  DEDICATED VAN SERVICE',
                'ENTER "CA <CITY> <DATE>" FOR CAR AVAILABILITY AND VEHICLE RATES',
            ], $state);
        }

        // 6. Car Availability Display: CA <CITY> <DATE>
        if (str_starts_with($clean, 'CA')) {
            $parts = explode(' ', trim(substr($clean, 2)));
            $city = $parts[0] ?: 'LAX';
            $date = $parts[1] ?? '15DEC';

            return new HotelCarResult([
                "--- CAR AVAILABILITY DISPLAY: {$city} / {$date} (CA) ---",
                'LN COMPANY  VEHICLE CLASS          DAILY RATE  CUR  UNL-MILE  STATUS',
                '--------------------------------------------------------------------',
                '01 AVIS(ZI) ECAR ECONOMY 4-DR         48.00    USD    YES      AVAILABLE',
                '02 AVIS(ZI) ICAR INTERMEDIATE SEDAN   59.00    USD    YES      AVAILABLE',
                '03 HERTZ(ZE)FCAR FULL SIZE SEDAN      68.00    USD    YES      AVAILABLE',
                '04 HERTZ(ZE)IFAR INTERMEDIATE SUV     79.00    USD    YES      AVAILABLE',
                'ENTER "CS <LN>" TO SELL VEHICLE RESERVATION INTO ACTIVE PNR',
            ], $state);
        }

        // 7. Car Sell: CS <LINE>
        if (str_starts_with($clean, 'CS')) {
            $segNum = count($state['activePnr']['segments'] ?? []) + 1;
            $carSegment = [
                'segmentNumber' => $segNum,
                'airlineCode' => 'CCR',
                'flightNumber' => 'ZI01',
                'bookingClass' => 'ICAR',
                'departureAirport' => 'LAX',
                'arrivalAirport' => 'LAX',
                'departureDate' => '15DEC',
                'departureTime' => '1200',
                'arrivalTime' => '1200',
                'seats' => 1,
                'status' => 'HK',
                'equipment' => 'CAR',
                'isPassive' => false,
                'rawDetails' => 'AVIS RENT A CAR / ICAR INTERMEDIATE SEDAN / LAX AIRPORT',
            ];
            $state['activePnr']['segments'][] = $carSegment;

            return new HotelCarResult([
                "CS - CAR SEGMENT {$segNum} CONFIRMED (HK1)",
                'COMPANY  : AVIS RENT A CAR (ZI) - LOS ANGELES INTL AIRPORT (LAX)',
                'CATEGORY : ICAR - INTERMEDIATE 4-DR AUTOMATIC A/C',
                'RATE     : USD 59.00 PER DAY / UNLIMITED MILEAGE INCLUDED',
                'STATUS   : HK1 (CONFIRMED / VOUCHER ISSUANCE AT COUNTER)',
            ], $state);
        }

        // 8. Car Rate / Terms: CR
        if (str_starts_with($clean, 'CR')) {
            return new HotelCarResult([
                '--- CAR RENTAL TERMS & INCLUSIONS (CR) ---',
                'RATE INCLUSIONS : UNLIMITED MILEAGE, COLLISION DAMAGE WAIVER (CDW)',
                'DRIVER REQ.     : MINIMUM AGE 21 YEARS WITH VALID LICENSE & CREDIT CARD',
                'ADDITIONAL FEES : CONCESSION RECOVERY FEE & AP TAXES INCLUDED IN TOTAL',
            ], $state);
        }

        return new HotelCarResult(["UNRECOGNIZED HOTEL/CAR COMMAND: \"{$clean}\""], $state, true);
    }
}
