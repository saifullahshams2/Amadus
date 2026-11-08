<?php

namespace App\Services\Amadeus;

use App\Models\Airline;
use App\Models\Airport;

class AisResult
{
    /**
     * @param  array<int, string>  $lines
     */
    public function __construct(
        public array $lines,
        public array $updatedState = [],
        public bool $isError = false
    ) {}
}

class AisService
{
    /**
     * Handle AIS requests (GG...) and Office Briefing (GD, GMD).
     */
    public function handle(string $input, array $currentState): AisResult
    {
        $clean = strtoupper(trim($input));
        $state = $currentState;

        // 1. Office Briefing: GD / GMD
        if ($clean === 'GD' || $clean === 'HE GD') {
            $briefing = $state['officeBriefing'] ?? [
                'OFFICE BRIEFING: LON1A0980 (AMADUS TRAINING ACADEMY)',
                '1. SYSTEM MAINTENANCE SCHEDULED SUNDAY 0200Z-0400Z',
                '2. PLEASE ENSURE TICKETING TIME LIMITS ARE MONITORED VIA Q1/Q8',
                '3. NEW FARE FILINGS ACTIVE FOR BA / QR / LH EFFECTIVE IMMEDIATELY',
                '4. USE "GMD <TEXT>" TO UPDATE OFFICE BRIEFING MESSAGE',
            ];

            return new AisResult($briefing, $state);
        }

        if (str_starts_with($clean, 'GMD')) {
            $newText = trim(substr($clean, 3));
            if ($newText === '') {
                return new AisResult([
                    'GMD - EDIT OFFICE BRIEFING PAGE',
                    'FORMAT: GMD <BRIEFING TEXT TO APPEND OR UPDATE>',
                ], $state);
            }

            $currentBriefing = $state['officeBriefing'] ?? [
                'OFFICE BRIEFING: LON1A0980 (AMADUS TRAINING ACADEMY)',
            ];
            $currentBriefing[] = date('dM Hi').'Z - '.$newText;
            $state['officeBriefing'] = $currentBriefing;

            return new AisResult([
                'OFFICE BRIEFING UPDATED SUCCESSFULLY',
                'ENTER "GD" TO DISPLAY UPDATED BRIEFING PAGE',
            ], $state);
        }

        // 2. Weather: GG WEA <CITY>
        if (preg_match('/^GG\s*WEA\s*([A-Z]{3})$/', $clean, $weaM)) {
            $city = $weaM[1];
            $ap = Airport::find($city);
            $cityName = $ap ? strtoupper($ap->city) : $city;

            return new AisResult([
                "--- AMADEUS WEATHER FORECAST: {$cityName} ({$city}) ---",
                'CONDITIONS : PARTLY CLOUDY / CLEAR VISIBILITY',
                'TEMP       : 22 C / 72 F',
                'WIND       : 12 KTS / 240 DEGREES',
                'BAROMETER  : 1014 HPA / 29.95 INHG',
                'FORECAST   : DRY WITH LIGHT WINDS OVER NEXT 24 HOURS',
            ], $state);
        }

        // 3. Airline information: GG AIR <CARRIER>
        if (preg_match('/^GG\s*AIR\s*([A-Z0-9]{2})(?:\s+([A-Z]{2}))?(?:\s+(\d+))?$/', $clean, $airM)) {
            $code = $airM[1];
            $country = $airM[2] ?? null;
            $section = $airM[3] ?? null;

            $al = Airline::find($code);
            $name = $al ? strtoupper($al->name) : "CARRIER {$code}";

            if ($section === '0') {
                return new AisResult([
                    "--- AIS: CONSULATES & EMBASSIES FOR {$name} ({$code}) ---",
                    'LOCATION: EMBASSY / CONSULATE GENERAL SERVICES',
                    'CONTACT : VISA & IMMIGRATION LIAISON DESK',
                    'TEL     : +44 (0)20 7946 0990 / HOURS: 0900-1600 LOCAL',
                ], $state);
            }

            if ($section === '6' || $country) {
                $targetCountry = $country ?: 'GB';

                return new AisResult([
                    "--- AIS: {$name} CONTACT NUMBERS IN {$targetCountry} ---",
                    'RESERVATIONS & HELPDESK : +44 208 745 7777 / 24 HRS',
                    'TICKETING DESK (LHR)    : TERMINAL 3 & 5 SALES COUNTER',
                    'AGENCY SUPPORT LINE     : 0844 493 0787 (MON-FRI 0830-1730)',
                    'BAGGAGE TRACING         : 0344 493 0785',
                ], $state);
            }

            return new AisResult([
                "--- AIS: {$name} ({$code}) AIRLINE INFORMATION ---",
                "IATA: {$code}   ICAO: ".strtoupper($al->icao ?? 'ZZZ').'   TICKET PREFIX: '.($al->ticket_prefix ?? '000'),
                'HEADQUARTERS : '.strtoupper($al->country ?? 'INTERNATIONAL'),
                'ACCESS LEVEL : COMPLETE ACCESS (/) & E-TICKETING CAPABLE',
                'GLOBAL HUBS  : '.(($code === 'UL') ? 'CMB (COLOMBO BANDARANAIKE)' : 'GLOBAL NETWORK'),
                'RESERVATIONS : DIRECT SELL & REAL-TIME INVENTORY CONFIRMATION',
            ], $state);
        }

        // 4. Participating carrier access: GG PCA <CARRIER>
        if (preg_match('/^GG\s*PCA\s*([A-Z0-9]{2})$/', $clean, $pcaM)) {
            $carrier = $pcaM[1];
            $al = Airline::find($carrier);
            $name = $al ? strtoupper($al->name) : "CARRIER {$carrier}";

            return new AisResult([
                "--- PARTICIPATING CARRIER ACCESS (GG PCA {$carrier}) ---",
                "CARRIER : {$carrier} - {$name}",
                'ACCESS LEVEL    : (/) AMADEUS COMPLETE ACCESS (LAST SEAT AVAILABILITY)',
                'SELL FUNCTION   : (.) ACCESS SELL - HK CONFIRMED INSTANTLY',
                'E-TICKETING     : INTERLINE & DIRECT ETKT ELIGIBLE (FA / FHE)',
                'SEAT MAPS       : INTERACTIVE GRAPHICAL SEATMAP ACTIVE (SM)',
                'SPECIAL SERVICE : SSR / OSI AUTO-TRANSMISSION SUPPORTED',
            ], $state);
        }

        // 5. Carrier lists: GG PCAL or GG PCAL AIR <x> or GG PCAL COU <x>
        if (preg_match('/^GG\s*PCAL(?:\s*(AIR|COU)\s*([A-Z]))?$/', $clean, $pcalM)) {
            $type = $pcalM[1] ?? 'AIR';
            $letter = $pcalM[2] ?? 'A';

            $query = ($type === 'AIR') ? Airline::where('name', 'like', "{$letter}%") : Airline::where('country', 'like', "{$letter}%");
            $list = $query->limit(8)->get();

            $lines = [
                "--- PARTICIPATING CARRIERS SORTED BY {$type} ({$letter}) ---",
                'CODE  ICAO  AIRLINE NAME                     COUNTRY',
                '------------------------------------------------------------',
            ];
            foreach ($list as $c) {
                $lines[] = str_pad($c->iata, 6).
                    str_pad($c->icao ?? '', 6).
                    str_pad(substr(strtoupper($c->name), 0, 32), 33).
                    strtoupper($c->country ?: 'INTL');
            }
            if ($list->isEmpty()) {
                $lines[] = "NO PARTICIPATING CARRIERS MATCHING '{$letter}'";
            }

            return new AisResult($lines, $state);
        }

        // 6. Airport information: GG APT <AIRPORT>
        if (preg_match('/^GG\s*APT\s*([A-Z]{3})$/', $clean, $aptM)) {
            $code = $aptM[1];
            $ap = Airport::find($code);
            if ($ap) {
                return new AisResult([
                    "--- AIS: AIRPORT DIRECTORY ({$code}) ---",
                    "NAME     : {$ap->name}",
                    "CITY     : {$ap->city}   COUNTRY: {$ap->country}",
                    "ICAO     : {$ap->icao}   TIMEZONE: {$ap->tz}",
                    'RUNWAYS  : OPERATIONAL WITH CAT-III ILS',
                    'CUSTOMS  : 24-HOUR IMMIGRATION & CUSTOMS POSTED',
                    'TRANSIT  : MCT DOM-DOM 45M / DOM-INT 60M / INT-INT 75M',
                ], $state);
            }

            return new AisResult(["AIRPORT {$code} NOT FOUND IN AIS DATABASE"], $state, true);
        }

        // 7. Country information: GG COU <COUNTRY>
        if (preg_match('/^GG\s*COU\s*([A-Z]{2,})$/', $clean, $couM)) {
            $target = $couM[1];

            return new AisResult([
                "--- AIS: COUNTRY INFORMATION PAGE ({$target}) ---",
                "COUNTRY CODE : {$target}",
                'REGIONAL GDS : AMADEUS CENTRAL RESERVATION SYSTEM',
                'CAPITAL/HUB  : PRIMARY METROPOLITAN HUBS ACCESSIBLE VIA AN/SN',
                'CURRENCY/ROE : LOCAL CURRENCY AVAILABLE UNDER FQC / ROE DISPLAY',
                'DOCUMENTATION: CHECK TIMATIC (TIFA/TIFV) FOR ENTRY REQUIREMENTS',
            ], $state);
        }

        // 8. Car and Hotel information: GG CAR <CODE> / GG HTL <CODE>
        if (preg_match('/^GG\s*(CAR|HTL)\s*([A-Z0-9]{2})$/', $clean, $chM)) {
            $type = ($chM[1] === 'CAR') ? 'CAR RENTAL COMPANY' : 'HOTEL CHAIN';
            $code = $chM[2];

            return new AisResult([
                "--- AIS: {$type} PROFILE ({$code}) ---",
                "PROVIDER CODE   : {$code}",
                'CONNECTIVITY    : AMADEUS COMPLETE ACCESS (/) WITH INSTANT VOUCHER',
                'BEST RATE GUAR. : BEST AVAILABLE RATE (BAR) CERTIFIED',
                'AVAILABILITY    : SEARCH WITH '.($chM[1] === 'CAR' ? 'CA <CITY> <DATE>' : 'HA <CITY> <DATE>'),
                'BOOKING POLICY  : REAL-TIME CONFIRMATION AT SELL TIME',
            ], $state);
        }

        // 9. Amadeus Guidelines & Training: GG AMA IN ...
        if (str_starts_with($clean, 'GG AMA IN') || str_starts_with($clean, 'GGAMA IN')) {
            if (str_contains($clean, 'LCL')) {
                return new AisResult([
                    '--- AIS: AIRLINE LOCAL CONTACT NUMBERS (INDIA/GLOBAL) ---',
                    'AI (AIR INDIA)        : 1800 180 1407 / +91 11 2462 4074',
                    '9W (JET AIRWAYS)      : 1800 225 522 / +91 22 3989 3333',
                    'BA (BRITISH AIRWAYS)  : +91 124 412 0715 / +44 208 745 7777',
                    'LH (LUFTHANSA)        : +91 124 488 8888',
                    'EK (EMIRATES)         : +91 22 3377 3377',
                    'QR (QATAR AIRWAYS)    : +91 22 6230 6000',
                ], $state);
            }

            if (str_contains($clean, 'USE.161')) {
                return new AisResult([
                    '--- AIS: PARTICIPATING AIRLINE QUEUE ADDRESSES (GG AMA IN USE.161) ---',
                    'AI (AIR INDIA)    : DELAI0100 / Q0 C0',
                    'BA (BRITISH AIR)  : LONBA0800 / Q1 C0',
                    'LH (LUFTHANSA)    : FRALH0800 / Q1 C0',
                    'EK (EMIRATES)     : DXBEK0800 / Q1 C0',
                    'QR (QATAR AIR)    : DOHQR0800 / Q1 C0',
                    'NOTE: SEND QUEUE MESSAGES USING QE/<OFFICE>/<Q>C<CAT>',
                ], $state);
            }

            if (str_contains($clean, 'USE')) {
                return new AisResult([
                    '--- AIS: USEFUL INFORMATION PAGES (GG AMA IN USE) ---',
                    'GG AMA IN USE.161 : AIRLINE QUEUE ADDRESSES',
                    'GG AMA IN LCL     : AIRLINE LOCAL RESERVATION NUMBERS',
                    'GG AMA IN TKT     : GENERAL TICKETING & RESERVATION RULES',
                    'GG AMA IN ETT     : E-TICKETING LOCAL GUIDELINES',
                    'GG AMA IN ALC     : AMADEUS LEARNING CITY ACCESS DETAILS',
                ], $state);
            }

            if (str_contains($clean, 'TR')) {
                return new AisResult([
                    '--- AIS: AMADEUS TRAINING SCHEDULES (GG AMA IN TR) ---',
                    'DELHI (NORTH)     : TRN - BASIC & ADVANCED GDS WORKSHOPS',
                    'MUMBAI (WEST)     : TRW - E-TICKETING & REISSUE WORKSHOPS',
                    'KOLKATA (EAST)    : TRE - RESERVATIONS & FARES CERTIFICATION',
                    'CHENNAI (SOUTH)   : TRS - HOTEL & CAR DISTRIBUTION SEMINAR',
                    'GLOBAL/ONLINE     : TRA - WEBINARS VIA WWW.ALC.AMADEUS.COM',
                ], $state);
            }

            if (str_contains($clean, 'ALC')) {
                return new AisResult([
                    '--- AIS: AMADEUS LEARNING CITY (GG AMA IN ALC) ---',
                    'WEBSITE : WWW.ALC.AMADEUS.COM',
                    'FEATURES: INTERACTIVE COURSES, QUIZZES, SYSTEM UPDATES',
                    'LOGIN   : USE YOUR AMADEUS AGENT SINE AND OFFICE ID',
                ], $state);
            }

            if (str_contains($clean, 'ETT') || str_contains($clean, 'TKT') || str_contains($clean, 'IND')) {
                return new AisResult([
                    '--- AIS: TICKETING & RESERVATION GUIDELINES ---',
                    '1. PASSENGER NAMES MUST MATCH PASSPORT IDENTIFICATION EXACTLY.',
                    '2. ALWAYS ENTER TICKETING TIME LIMIT VIA TKTL AND OSI.',
                    '3. MANDATORY ELEMENTS: PHONE (AP), RECEIVED (RF), ITIN, NAME, TKT.',
                    '4. VOID/REFUND ACTIONS MUST BE COMPLETED WITHIN BSP TIME LIMIT.',
                ], $state);
            }
        }

        // 10. Action Codes: GG CODE / GG CODE W / GGCODE.64
        if (str_starts_with($clean, 'GG CODE') || str_starts_with($clean, 'GGCODE')) {
            return new AisResult([
                '--- AIS: AMADEUS ACTION CODES (GG CODE) ---',
                'INPUT CODES  : NN (NEED), SS (SOLD), LL (WAITLIST), ST (STRETCHER)',
                'STATUS CODES : HK (CONFIRMED), HL (WAITLISTED), HN (NEED CONFIRMATION)',
                '               UC (UNABLE/CLOSED), HX (CANCELLED BY AIRLINE)',
                '               TK (SCHEDULE CHANGED - NEW TIMING), NO (NO ACTION TAKEN)',
                'DIRECT ACCESS: LK (HOLDING CONFIRMED), NK (PENDING RECORD LOCATOR)',
                'PASSIVE CODES: GK (CONFIRMED OUTSIDE AMADEUS), GL (WAITLISTED OUTSIDE)',
            ], $state);
        }

        // 11. Alliances: GG ALLIANCE
        if ($clean === 'GG ALLIANCE') {
            return new AisResult([
                '--- AIS: GLOBAL AIRLINE ALLIANCES (GG ALLIANCE) ---',
                'STAR ALLIANCE (*A) : LH, SQ, TG, UA, AC, NH, TK, OS, LX, MS, ET, AI',
                'ONEWORLD      (*O) : BA, AA, QR, CX, JL, QF, AY, IB, MH, RJ',
                'SKYTEAM       (*S) : AF, KL, DL, KE, SV, AR, UX, ME, VN, CI',
                'SEARCH ENTRY: AN*A<DATE><ORIG><DEST> (E.G. AN*A15SEPDELLON)',
            ], $state);
        }

        // Default AIS Main Menu / Announcement: GG AIS / HE AIS
        return new AisResult([
            '--- AMADEUS INFORMATION SYSTEM (AIS) MAIN MENU ---',
            'GG AIS            : LATEST SYSTEM ANNOUNCEMENTS & RELEASES',
            'GG AIR <CODE>     : AIRLINE PROFILES & CONTACTS (E.G. GG AIR UL)',
            'GG APT <CODE>     : AIRPORT SPECIFICATIONS & RUNWAY DETAILS',
            'GG WEA <CODE>     : CURRENT CITY WEATHER & 24H FORECAST',
            'GG PCA <CODE>     : PARTICIPATING CARRIER ACCESS LEVELS',
            'GG CODE           : AMADEUS INPUT AND OUTPUT ACTION CODES',
            'GG ALLIANCE       : GLOBAL AIRLINE ALLIANCES MEMBERSHIP',
            'GG AMA IN USE     : OPERATIONAL PAGES & AIRLINE QUEUE ADDRESSES',
        ], $state);
    }
}
