<?php

namespace App\Services\Amadeus;

class TimaticResult
{
    /**
     * @param  array<int, string>  $lines
     */
    public function __construct(
        public array $lines,
        public bool $isError = false
    ) {}
}

class TimaticService
{
    /**
     * Handle Timatic commands.
     */
    public function handle(string $input, array $state): TimaticResult
    {
        $clean = strtoupper(trim($input));

        // 1. TIFV: Mask for Visa Information
        if ($clean === 'TIFV') {
            return new TimaticResult([
                '--- TIMATIC VISA INFORMATION MASK (TIFV) ---',
                'DESTINATION / TRANSIT COUNTRY : [    ]  NATIONALITY : [  ]',
                'ALIEN RESIDENCE               : [    ]  PASSENGER TYPE: [ADT]',
                'EMBARKATION POINT             : [   ]  DATE OF TRAVEL: ['.strtoupper(date('dMY')).']',
                'DOCUMENT TYPE                 : [PASSPORT] EXPIRY: [       ]',
                'USE ENTER TO SUBMIT COMPLETED MASK',
            ]);
        }

        // 2. TIFH: Mask for Health Information
        if ($clean === 'TIFH') {
            return new TimaticResult([
                '--- TIMATIC HEALTH INFORMATION MASK (TIFH) ---',
                'DESTINATION COUNTRY : [    ]  NATIONALITY : [  ]',
                'YELLOW FEVER TRANSIT: [Y/N]   MALARIA PROPHYLAXIS: [Y/N]',
                'COVID-19 STATUS     : [FULLY VACCINATED] VACCINE TYPE: [WHO APPROVED]',
                'USE ENTER TO SUBMIT COMPLETED MASK',
            ]);
        }

        // 3. TIFA: Mask for Visa and Health Information
        if ($clean === 'TIFA') {
            return new TimaticResult([
                '--- TIMATIC FULL VISA & HEALTH MASK (TIFA) ---',
                'DESTINATION COUNTRY : [    ]  TRANSIT COUNTRY : [    ]',
                'NATIONALITY         : [  ]   RESIDENCE       : [    ]',
                'DOCUMENTS HELD      : [NORMAL PASSPORT / VALID MIN 6 MOS]',
                'HEALTH CERTIFICATES : [YELLOW FEVER / ROUTINE VACCINES]',
                'USE ENTER TO TRANSMIT REQUEST TO TIMATIC SERVER',
            ]);
        }

        // 4. TIRV/NA<NATIONALITY>/S<SEGMENTS> - Reference from active PNR
        if (str_starts_with($clean, 'TIRV')) {
            $nationality = 'IN';
            if (preg_match('/NA([A-Z]{2})/', $clean, $nM)) {
                $nationality = $nM[1];
            }

            return new TimaticResult([
                "--- TIMATIC PNR AUTO-CHECK (NATIONALITY: {$nationality}) ---",
                'PASSPORT: VALID PASSPORT REQUIRED FOR ALL PASSENGERS (MIN 6 MOS VALIDITY)',
                'VISA    : VISA REQUIRED FOR ENTRY UNLESS HOLDING DIPLOMATIC/SERVICE PASSPORT',
                'HEALTH  : NO MANDATORY VACCINATIONS REQUIRED FOR DESTINATION POINT',
                'CUSTOMS : CURRENCY DECLARATION REQUIRED EXCEEDING USD 10,000 OR EQUIVALENT',
                'WARNING : TIMATIC INFORMATION IS ADVISORY; PASSENGER RESPONSIBLE FOR PAPERS',
            ]);
        }

        // 5. TINEWS: News items index
        if ($clean === 'TINEWS') {
            return new TimaticResult([
                '--- TIMATIC TRAVEL NEWS & BULLETINS (TINEWS) ---',
                '1. EUROPEAN UNION: ETIAS IMPLEMENTATION TIMELINE UPDATE',
                '2. UNITED KINGDOM: ELECTRONIC TRAVEL AUTHORISATION (ETA) EXPANSION',
                '3. UNITED STATES : VISA WAIVER PROGRAM (ESTA) PASSPORT VALIDITY CRITERIA',
                '4. SCHENGEN AREA : 90/180 DAY SHORT-STAY CALCULATOR RULES',
                'ENTER "TINEWS <NUM>" FOR FULL TEXT',
            ]);
        }

        // 6. TIRULES: Rules, terms and definitions
        if ($clean === 'TIRULES') {
            return new TimaticResult([
                '--- TIMATIC RULES, TERMS & DEFINITIONS (TIRULES) ---',
                'DIRECT TRANSIT   : REMAINING AIRSIDE WITHOUT CLEARING BORDER CONTROL',
                'TRANSIT WITHOUT VISA (TWOV) : ALLOWED FOR SELECT NATIONALITIES WITHIN 24H',
                'RETURN TICKET    : CONFIRMED RETURN OR ONWARD TRANSPORTATION REQUIRED',
                'SUFFICIENT FUNDS : CASH, CREDIT CARDS, OR SPONSORSHIP EVIDENCE MANDATORY',
            ]);
        }

        // 7. TILCC/<COUNTRY>: List of airports for a specific country
        if (preg_match('/^TILCC\/([A-Z]+)$/', $clean, $lccM)) {
            $country = $lccM[1];

            return new TimaticResult([
                "--- TIMATIC AIRPORT DIRECTORY: {$country} (TILCC) ---",
                'PRIMARY INTERNATIONAL PORTS OF ENTRY:',
                'FRA : FRANKFURT AM MAIN AIRPORT (TERMINALS 1 & 2)',
                'MUC : MUNICH FRANZ JOSEF STRAUSS AIRPORT (TERMINAL 2 STAR ALLIANCE)',
                'BER : BERLIN BRANDENBURG (WILLY BRANDT)',
                'HAM : HAMBURG HELMUT SCHMIDT',
                'DUS : DUSSELDORF AIRPORT',
                'CUSTOMS & BORDER CONTROL OPERATE 24/7 AT ALL LISTED PORTS',
            ]);
        }

        // 8. TIDFT/<COUNTRY>/CS: Customs check
        if (preg_match('/^TIDFT\/([A-Z]{2,})\/CS$/', $clean, $dftM)) {
            $country = $dftM[1];

            return new TimaticResult([
                "--- TIMATIC CUSTOMS & DUTY FREE REGULATIONS: {$country} (TIDFT) ---",
                'IMPORT ALLOWANCES FOR PASSENGERS 18 YEARS AND OLDER:',
                'TOBACCO PRODUCTS : 200 CIGARETTES OR 50 CIGARS OR 250G TOBACCO',
                'ALCOHOLIC BEV.   : 1 LITRE SPIRITS (>22%) OR 2 LITRES WINE/BEER',
                'CURRENCY IMPORT  : LOCAL OR FOREIGN CURRENCY EXCEEDING USD 10,000 MUST BE DECLARED',
                'PROHIBITED ITEMS : NARCOTICS, COUNTERFEIT GOODS, WEAPONS WITHOUT PERMIT',
            ]);
        }

        // 9. TIRGL: Country groups
        if ($clean === 'TIRGL') {
            return new TimaticResult([
                '--- TIMATIC COUNTRY GROUP CODES (TIRGL) ---',
                'SCHS : SCHENGEN STATES (29 EUROPEAN MEMBER COUNTRIES)',
                'EU   : EUROPEAN UNION MEMBER STATES',
                'GCC  : GULF COOPERATION COUNCIL (QA, AE, SA, KW, BH, OM)',
                'ASEA : ASSOCIATION OF SOUTHEAST ASIAN NATIONS',
                'ENTER "TIRGL/<GROUPCODE>" TO EXPAND MEMBER COUNTRIES (E.G. TIRGL/SCHS)',
            ]);
        }

        // 10. TIRGL/SCHS: Schengen states
        if ($clean === 'TIRGL/SCHS') {
            return new TimaticResult([
                '--- TIMATIC SCHENGEN MEMBER STATES (TIRGL/SCHS) ---',
                'AUSTRIA, BELGIUM, BULGARIA, CROATIA, CZECH REPUBLIC, DENMARK, ESTONIA,',
                'FINLAND, FRANCE, GERMANY, GREECE, HUNGARY, ICELAND, ITALY, LATVIA,',
                'LIECHTENSTEIN, LITHUANIA, LUXEMBOURG, MALTA, NETHERLANDS, NORWAY,',
                'POLAND, PORTUGAL, ROMANIA, SLOVAKIA, SLOVENIA, SPAIN, SWEDEN, SWITZERLAND.',
                'MAXIMUM STAY: 90 DAYS WITHIN ANY 180-DAY PERIOD FOR NON-EU CITIZENS',
            ]);
        }

        // Default: HE TI / TI main screen
        return new TimaticResult([
            '--- AMADEUS TIMATIC TRAVEL INFORMATION SYSTEM (HE TI) ---',
            'TIFV                 : VISA REQUIREMENTS QUESTIONNAIRE',
            'TIFH                 : HEALTH & VACCINATION INFORMATION',
            'TIFA                 : COMBINED VISA AND HEALTH MASK',
            'TIRV/NA<XX>/S<SEG>   : AUTO-CHECK TIMATIC FOR ACTIVE PNR SEGMENTS',
            'TINEWS               : LATEST TRAVEL RESTRICTIONS & IMMIGRATION NEWS',
            'TIRULES              : TIMATIC TERMS AND DOCUMENT DEFINITIONS',
            'TILCC/<COUNTRY>      : AIRPORTS AND ENTRY PORTS BY COUNTRY',
            'TIDFT/<COUNTRY>/CS   : CUSTOMS, CURRENCY AND DUTY-FREE RULES',
            'TIRGL/SCHS           : SCHENGEN MEMBER STATES & DURATION LIMITS',
        ]);
    }
}
