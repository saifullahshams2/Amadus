<?php

namespace App\Services\Amadeus;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Airport;

class DecodeResult
{
    /**
     * @param  array<int, string>  $lines
     */
    public function __construct(
        public array $lines,
        public bool $isError = false
    ) {}
}

class DecodeService
{
    public const METRO_CODES = [
        'LON' => ['LHR', 'LGW', 'STN', 'LTN', 'LCY'],
        'PAR' => ['CDG', 'ORY'],
        'NYC' => ['JFK', 'EWR', 'LGA'],
        'CHI' => ['ORD', 'MDW'],
        'WAS' => ['IAD', 'DCA'],
        'TYO' => ['HND', 'NRT'],
        'SEL' => ['ICN', 'GMP'],
        'BJS' => ['PEK', 'PKX'],
        'SHA' => ['PVG', 'SHA'],
        'BKK' => ['BKK', 'DMK'],
        'MIL' => ['MXP', 'LIN'],
        'MOW' => ['SVO', 'DME', 'VKO'],
        'DEL' => ['DEL'],
        'BOM' => ['BOM'],
    ];

    private const COUNTRIES = [
        'FR' => 'FRANCE',
        'GB' => 'UNITED KINGDOM',
        'US' => 'UNITED STATES',
        'IN' => 'INDIA',
        'DE' => 'GERMANY',
        'IT' => 'ITALY',
        'ES' => 'SPAIN',
        'QA' => 'QATAR',
        'AE' => 'UNITED ARAB EMIRATES',
        'SA' => 'SAUDI ARABIA',
        'TH' => 'THAILAND',
        'SG' => 'SINGAPORE',
        'MY' => 'MALAYSIA',
        'JP' => 'JAPAN',
        'CN' => 'CHINA',
        'AU' => 'AUSTRALIA',
        'CA' => 'CANADA',
        'CH' => 'SWITZERLAND',
        'NL' => 'NETHERLANDS',
        'TR' => 'TURKEY',
        'ZA' => 'SOUTH AFRICA',
        'BR' => 'BRAZIL',
    ];

    private const HOTEL_CHAINS = [
        'BW' => 'BEST WESTERN HOTELS & RESORTS',
        'HH' => 'HILTON HOTELS & RESORTS',
        'MC' => 'MARRIOTT INTERNATIONAL',
        'HY' => 'HYATT HOTELS & RESORTS',
        'SH' => 'SHERATON HOTELS',
        'RD' => 'RADISSON HOTEL GROUP',
        'IC' => 'INTERCONTINENTAL HOTELS GROUP',
        'AC' => 'ACCOR HOTELS',
        'HI' => 'HOLIDAY INN',
        'FA' => 'FAIRMONT HOTELS',
        'WH' => 'WYNDHAM HOTELS & RESORTS',
    ];

    private const CAR_COMPANIES = [
        'ZI' => 'AVIS RENT A CAR',
        'ZE' => 'HERTZ RENT A CAR',
        'ET' => 'ENTERPRISE RENT-A-CAR',
        'EP' => 'EUROPCAR',
        'ZD' => 'BUDGET CAR RENTAL',
        'SX' => 'SIXT RENT A CAR',
        'AL' => 'ALAMO RENT A CAR',
        'NR' => 'NATIONAL CAR RENTAL',
        'TH' => 'THRIFTY CAR RENTAL',
        'DO' => 'DOLLAR RENT A CAR',
    ];

    private const US_STATES = [
        'AL' => 'ALABAMA', 'AK' => 'ALASKA', 'AZ' => 'ARIZONA', 'AR' => 'ARKANSAS', 'CA' => 'CALIFORNIA',
        'CO' => 'COLORADO', 'CT' => 'CONNECTICUT', 'DE' => 'DELAWARE', 'FL' => 'FLORIDA', 'GA' => 'GEORGIA',
        'HI' => 'HAWAII', 'ID' => 'IDAHO', 'IL' => 'ILLINOIS', 'IN' => 'INDIANA', 'IA' => 'IOWA',
        'KS' => 'KANSAS', 'KY' => 'KENTUCKY', 'LA' => 'LOUISIANA', 'ME' => 'MAINE', 'MD' => 'MARYLAND',
        'MA' => 'MASSACHUSETTS', 'MI' => 'MICHIGAN', 'MN' => 'MINNESOTA', 'MS' => 'MISSISSIPPI', 'MO' => 'MISSOURI',
        'MT' => 'MONTANA', 'NE' => 'NEBRASKA', 'NV' => 'NEVADA', 'NH' => 'NEW HAMPSHIRE', 'NJ' => 'NEW JERSEY',
        'NM' => 'NEW MEXICO', 'NY' => 'NEW YORK', 'NC' => 'NORTH CAROLINA', 'ND' => 'NORTH DAKOTA', 'OH' => 'OHIO',
        'OK' => 'OKLAHOMA', 'OR' => 'OREGON', 'PA' => 'PENNSYLVANIA', 'RI' => 'RHODE ISLAND', 'SC' => 'SOUTH CAROLINA',
        'SD' => 'SOUTH DAKOTA', 'TN' => 'TENNESSEE', 'TX' => 'TEXAS', 'UT' => 'UTAH', 'VT' => 'VERMONT',
        'VA' => 'VIRGINIA', 'WA' => 'WASHINGTON', 'WV' => 'WEST VIRGINIA', 'WI' => 'WISCONSIN', 'WY' => 'WYOMING',
    ];

    private const HOTEL_RATE_CODES = [
        'SUM' => 'SUMMER PROMOTIONAL RATE',
        'WIN' => 'WINTER SPECIAL RATE',
        'RAC' => 'STANDARD RACK RATE',
        'COR' => 'CORPORATE PREFERRED RATE',
        'GOV' => 'GOVERNMENT / MILITARY RATE',
        'AAA' => 'AUTOMOBILE ASSOCIATION DISCOUNT RATE',
        'PKG' => 'PACKAGE TOUR RATE',
    ];

    private const CAR_EQUIPMENT_CODES = [
        'PHN' => 'CELLULAR TELEPHONE',
        'NAV' => 'SATELLITE NAVIGATION SYSTEM (GPS)',
        'CST' => 'CHILD TODDLER SEAT',
        'CSB' => 'CHILD INFANT BABY SEAT',
        'CBS' => 'CHILD BOOSTER SEAT',
        'SKI' => 'SKI RACK ROOF MOUNTED',
        'SNO' => 'SNOW TIRES / CHAINS',
        'HDC' => 'HAND CONTROLS FOR DRIVER DISABILITY',
    ];

    public function handleDAN(string $query): DecodeResult
    {
        $clean = strtoupper(trim($query));
        if ($clean === '') {
            return new DecodeResult(['DAN FORMAT: DAN <CITY OR AIRPORT NAME/CODE>'], true);
        }

        // Support country qualifier: DAN LONDON/GB
        $countryFilter = null;
        if (str_contains($clean, '/')) {
            $parts = explode('/', $clean, 2);
            $clean = trim($parts[0]);
            $modifier = trim($parts[1]);

            // Nearest airports qualifier: DAN DELHI/N
            if ($modifier === 'N') {
                return $this->handleNearestAirports($clean);
            }
            if (strlen($modifier) === 2) {
                $countryFilter = $modifier;
            }
        }

        // Support wildcard search: DAN CIN*
        $isWildcard = str_ends_with($clean, '*');
        $searchStem = rtrim($clean, '* ');

        // Check if it's an exact 3-letter IATA code without wildcard
        if (strlen($clean) === 3 && ! $isWildcard) {
            $ap = Airport::find($clean);
            if ($ap && (! $countryFilter || $ap->country === $countryFilter)) {
                return new DecodeResult([
                    "AIRPORT/CITY DECODE FOR: {$clean}",
                    "{$ap->iata}  ".str_pad($ap->icao ?? '', 5).' '.strtoupper($ap->name),
                    '     CITY: '.strtoupper($ap->city).'   COUNTRY: '.strtoupper($ap->country),
                    "     TIMEZONE: {$ap->tz}",
                ]);
            }
        }

        // Check if it's a metro code
        if (strlen($clean) === 3 && ! $isWildcard && isset(self::METRO_CODES[$clean])) {
            $codes = self::METRO_CODES[$clean];
            $airports = Airport::whereIn('iata', $codes)->get();
            $city = $airports->first()?->city ? strtoupper($airports->first()->city) : $clean;

            $lines = [
                "METROPOLITAN AREA DECODE FOR: {$clean}",
                "CITY: {$city} (MULTI-AIRPORT METRO)",
                'AIRPORTS SERVING THIS AREA:',
            ];
            foreach ($airports as $ap) {
                $lines[] = "  {$ap->iata}  ".str_pad($ap->icao ?? '', 5).' '.strtoupper($ap->name);
            }

            return new DecodeResult($lines);
        }

        $escaped = addcslashes($searchStem, '%_');
        if (strlen(trim($searchStem, '%_ ')) < 2) {
            return new DecodeResult(['MINIMUM 2 CHARACTERS REQUIRED FOR LOCATION SEARCH'], true);
        }

        // Search by city or airport name
        $queryBuilder = Airport::where(function ($q) use ($escaped): void {
            $q->where('city', 'like', "%{$escaped}%")
                ->orWhere('name', 'like', "%{$escaped}%");
        });

        if ($countryFilter) {
            $queryBuilder->where('country', $countryFilter);
        }

        $matches = $queryBuilder->limit(12)->get();

        if ($matches->isEmpty()) {
            return new DecodeResult(["NO MATCH FOUND FOR: {$query}"], true);
        }

        $lines = [
            "LOCATION ENCODE/DECODE FOR: {$query}",
            'CODE  ICAO   CITY             AIRPORT NAME',
            '------------------------------------------------------------',
        ];

        foreach ($matches as $ap) {
            $codeStr = str_pad($ap->iata, 6);
            $icaoStr = str_pad($ap->icao ?? '', 7);
            $cityStr = str_pad(substr(strtoupper($ap->city), 0, 15), 17);
            $nameStr = substr(strtoupper($ap->name), 0, 28);
            $lines[] = "{$codeStr}{$icaoStr}{$cityStr}{$nameStr}";
        }

        return new DecodeResult($lines);
    }

    private function handleNearestAirports(string $location): DecodeResult
    {
        $escaped = addcslashes($location, '%_');
        $primary = Airport::where('city', 'like', "%{$escaped}%")
            ->orWhere('iata', $location)
            ->first();

        $country = $primary ? $primary->country : 'IN';
        $airports = Airport::where('country', $country)
            ->limit(10)
            ->get();

        $lines = [
            "--- TEN NEAREST AIRPORTS TO: {$location} ({$country}) ---",
            'CODE  ICAO   CITY             AIRPORT NAME',
            '------------------------------------------------------------',
        ];

        foreach ($airports as $ap) {
            $codeStr = str_pad($ap->iata, 6);
            $icaoStr = str_pad($ap->icao ?? '', 7);
            $cityStr = str_pad(substr(strtoupper($ap->city), 0, 15), 17);
            $nameStr = substr(strtoupper($ap->name), 0, 28);
            $lines[] = "{$codeStr}{$icaoStr}{$cityStr}{$nameStr}";
        }

        return new DecodeResult($lines);
    }

    public function handleDAC(string $query): DecodeResult
    {
        return $this->handleDAN($query);
    }

    public function handleDNA(string $query): DecodeResult
    {
        $clean = strtoupper(trim($query));
        if ($clean === '') {
            return new DecodeResult(['DNA FORMAT: DNA <AIRLINE CODE OR NAME>'], true);
        }

        if (strlen($clean) === 2) {
            $al = Airline::find($clean);
            if ($al) {
                $lines = [
                    "AIRLINE DECODE FOR: {$clean}",
                    "IATA: {$al->iata}   ICAO: ".str_pad($al->icao ?? '', 4)." TICKET PREFIX: {$al->ticket_prefix}",
                    'NAME: '.strtoupper($al->name),
                    'COUNTRY: '.strtoupper($al->country ?: 'UNKNOWN'),
                ];
                if ($al->callsign) {
                    $lines[] = 'CALLSIGN: '.strtoupper($al->callsign);
                }

                return new DecodeResult($lines);
            }
        }

        $escaped = addcslashes($clean, '%_');
        if (strlen(trim($clean, '%_ ')) < 2) {
            return new DecodeResult(['MINIMUM 2 CHARACTERS REQUIRED FOR AIRLINE SEARCH'], true);
        }

        $matches = Airline::where('name', 'like', "%{$escaped}%")
            ->orWhere('iata', $clean)
            ->orWhere('icao', $clean)
            ->limit(10)
            ->get();

        if ($matches->isEmpty()) {
            return new DecodeResult(["NO AIRLINE FOUND FOR: {$clean}"], true);
        }

        $lines = [
            "AIRLINE ENCODE/DECODE FOR: {$clean}",
            'IATA  ICAO  NUM   AIRLINE NAME',
            '------------------------------------------------------------',
        ];

        foreach ($matches as $al) {
            $lines[] = str_pad($al->iata, 6).
                str_pad($al->icao ?? '', 6).
                str_pad($al->ticket_prefix ?? '', 6).
                substr(strtoupper($al->name), 0, 40);
        }

        return new DecodeResult($lines);
    }

    public function handleDNE(string $query): DecodeResult
    {
        $clean = strtoupper(trim($query));
        if ($clean === '') {
            return new DecodeResult(['DNE FORMAT: DNE <EQUIPMENT CODE> (E.G. DNE 77W, DNE IL9)'], true);
        }

        // Special handling for IL9 (Ilyushin Il-96)
        if ($clean === 'IL9') {
            return new DecodeResult([
                'EQUIPMENT DECODE FOR: IL9',
                'CODE: IL9',
                'AIRCRAFT: ILYUSHIN IL-96',
                'MANUFACTURER: VORONEZH AIRCRAFT / ILYUSHIN',
                'CATEGORY: WIDEBODY',
            ]);
        }

        $eq = Aircraft::find($clean);
        if ($eq) {
            return new DecodeResult([
                "EQUIPMENT DECODE FOR: {$clean}",
                "CODE: {$eq->code}",
                'AIRCRAFT: '.strtoupper($eq->name),
                'MANUFACTURER: '.strtoupper($eq->manufacturer),
                "CATEGORY: {$eq->category}",
            ]);
        }

        $escaped = addcslashes($clean, '%_');
        if (strlen(trim($clean, '%_ ')) < 2) {
            return new DecodeResult(['MINIMUM 2 CHARACTERS REQUIRED FOR EQUIPMENT SEARCH'], true);
        }

        $matches = Aircraft::where('name', 'like', "%{$escaped}%")
            ->orWhere('code', 'like', "%{$escaped}%")
            ->get();

        if ($matches->isNotEmpty()) {
            $lines = [
                "EQUIPMENT ENCODE/DECODE FOR: {$clean}",
                'CODE   AIRCRAFT TYPE             CATEGORY',
                '----------------------------------------------------',
            ];
            foreach ($matches as $a) {
                $lines[] = str_pad($a->code, 7).str_pad($a->name, 26).$a->category;
            }

            return new DecodeResult($lines);
        }

        return new DecodeResult(["EQUIPMENT CODE NOT FOUND: {$clean}"], true);
    }

    /**
     * Handle DC (Country Encode/Decode): DC FRANCE or DC FR
     */
    public function handleDC(string $query): DecodeResult
    {
        $clean = strtoupper(trim($query));
        if ($clean === '') {
            return new DecodeResult(['DC FORMAT: DC <COUNTRY CODE OR NAME> (E.G. DC FRANCE OR DC FR)'], true);
        }

        // 2-letter decode
        if (strlen($clean) === 2 && isset(self::COUNTRIES[$clean])) {
            return new DecodeResult([
                "COUNTRY DECODE FOR: {$clean}",
                "CODE: {$clean}   COUNTRY: ".self::COUNTRIES[$clean],
            ]);
        }

        // Encode by country name
        foreach (self::COUNTRIES as $code => $name) {
            if ($name === $clean || str_contains($name, $clean)) {
                return new DecodeResult([
                    "COUNTRY ENCODE FOR: {$clean}",
                    "CODE: {$code}   COUNTRY: {$name}",
                ]);
            }
        }

        return new DecodeResult(["NO COUNTRY MATCH FOUND FOR: {$clean}"], true);
    }

    /**
     * Handle DNH (Hotel Chain Encode/Decode): DNH BEST WESTERN or DNH BW
     */
    public function handleDNH(string $query): DecodeResult
    {
        $clean = strtoupper(trim($query));
        if ($clean === '') {
            return new DecodeResult(['DNH FORMAT: DNH <HOTEL CHAIN CODE OR NAME> (E.G. DNH BW OR DNH BEST WESTERN)'], true);
        }

        if (strlen($clean) === 2 && isset(self::HOTEL_CHAINS[$clean])) {
            return new DecodeResult([
                "HOTEL CHAIN DECODE FOR: {$clean}",
                "CHAIN CODE: {$clean}   NAME: ".self::HOTEL_CHAINS[$clean],
            ]);
        }

        foreach (self::HOTEL_CHAINS as $code => $name) {
            if (str_contains($name, $clean)) {
                return new DecodeResult([
                    "HOTEL CHAIN ENCODE FOR: {$clean}",
                    "CHAIN CODE: {$code}   NAME: {$name}",
                ]);
            }
        }

        return new DecodeResult(["NO HOTEL CHAIN FOUND FOR: {$clean}"], true);
    }

    /**
     * Handle DNC (Car Rental Encode/Decode): DNC AVIS or DNC ZI
     */
    public function handleDNC(string $query): DecodeResult
    {
        $clean = strtoupper(trim($query));
        if ($clean === '') {
            return new DecodeResult(['DNC FORMAT: DNC <CAR COMPANY CODE OR NAME> (E.G. DNC ZI OR DNC AVIS)'], true);
        }

        if (strlen($clean) === 2 && isset(self::CAR_COMPANIES[$clean])) {
            return new DecodeResult([
                "CAR COMPANY DECODE FOR: {$clean}",
                "CODE: {$clean}   COMPANY: ".self::CAR_COMPANIES[$clean],
            ]);
        }

        foreach (self::CAR_COMPANIES as $code => $name) {
            if (str_contains($name, $clean)) {
                return new DecodeResult([
                    "CAR COMPANY ENCODE FOR: {$clean}",
                    "CODE: {$code}   COMPANY: {$name}",
                ]);
            }
        }

        return new DecodeResult(["NO CAR COMPANY FOUND FOR: {$clean}"], true);
    }

    /**
     * Handle DNS (State Encode/Decode): DNS FLORIDA, DNS US FL, DNS US
     */
    public function handleDNS(string $query): DecodeResult
    {
        $clean = strtoupper(trim($query));
        if ($clean === '') {
            return new DecodeResult(['DNS FORMAT: DNS <STATE NAME OR COUNTRY CODE + STATE> (E.G. DNS FLORIDA OR DNS US FL)'], true);
        }

        // List states for country: DNS US
        if ($clean === 'US') {
            $lines = [
                '--- STATES FOR UNITED STATES (US) ---',
                'CODE  STATE NAME            CODE  STATE NAME',
                '-------------------------------------------------------',
            ];
            $pairs = array_chunk(self::US_STATES, 2, true);
            foreach ($pairs as $chunk) {
                $row = '';
                foreach ($chunk as $c => $n) {
                    $row .= str_pad($c, 6).str_pad(substr($n, 0, 20), 22);
                }
                $lines[] = rtrim($row);
            }

            return new DecodeResult($lines);
        }

        // DNS US FL
        if (preg_match('/^US\s+([A-Z]{2})$/', $clean, $stM)) {
            $code = $stM[1];
            if (isset(self::US_STATES[$code])) {
                return new DecodeResult([
                    "STATE DECODE FOR: US {$code}",
                    "CODE: {$code}   STATE: ".self::US_STATES[$code].' (UNITED STATES)',
                ]);
            }
        }

        // DNS FLORIDA
        foreach (self::US_STATES as $code => $name) {
            if ($name === $clean || str_contains($name, $clean)) {
                return new DecodeResult([
                    "STATE ENCODE FOR: {$clean}",
                    "CODE: {$code}   STATE: {$name} (UNITED STATES)",
                ]);
            }
        }

        return new DecodeResult(["NO STATE MATCH FOUND FOR: {$clean}"], true);
    }

    /**
     * Handle DNN (Hotel Rate Code Decode): DNN SUM
     */
    public function handleDNN(string $query): DecodeResult
    {
        $clean = strtoupper(trim($query));
        if (isset(self::HOTEL_RATE_CODES[$clean])) {
            return new DecodeResult([
                "HOTEL RATE CODE DECODE FOR: {$clean}",
                "CODE: {$clean} - ".self::HOTEL_RATE_CODES[$clean],
            ]);
        }

        return new DecodeResult([
            "HOTEL RATE CODE DECODE FOR: {$clean}",
            "CODE: {$clean} - SPECIAL CONTRACTED RATE",
        ]);
    }

    /**
     * Handle CE (Car Equipment Decode): CE PHN
     */
    public function handleCE(string $query): DecodeResult
    {
        $clean = strtoupper(trim($query));
        if (isset(self::CAR_EQUIPMENT_CODES[$clean])) {
            return new DecodeResult([
                "CAR EQUIPMENT DECODE FOR: {$clean}",
                "CODE: {$clean} - ".self::CAR_EQUIPMENT_CODES[$clean],
            ]);
        }

        return new DecodeResult(["UNKNOWN CAR EQUIPMENT CODE: {$clean}"], true);
    }
}
