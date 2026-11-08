<?php

namespace App\Services\Amadeus;

class ProfileResult
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

class ProfileService
{
    /**
     * Handle Customer Profile transactions.
     */
    public function handle(string $input, array $state): ProfileResult
    {
        $clean = strtoupper(trim($input));

        // 1. PM: Enter Profile Mode
        if ($clean === 'PM') {
            $state['profileMode'] = true;
            if (empty($state['activeProfile'])) {
                $state['activeProfile'] = [
                    'type' => 'TRAVELLER',
                    'name' => null,
                    'company' => null,
                    'nickname' => null,
                    'followUp' => [],
                    'status' => 'IN_CREATION',
                ];
            }

            return new ProfileResult([
                '--- AMADEUS CUSTOMER PROFILE MODE (PM) ---',
                'PROFILE WORK AREA ACTIVATED',
                'ENTER: NM1<NAME> FOR TRAVELLER PROFILE OR PCN/<COMPANY> FOR COMPANY PROFILE',
                'ENTER: PIN/<NICKNAME> TO ASSIGN PROFILE SEARCH INDEX',
                'ENTER: PER TO SAVE PROFILE | PEE OR PME TO EXIT PROFILE MODE',
            ], $state);
        }

        // 2. PME / PEE: Exit Profile Mode
        if ($clean === 'PME' || $clean === 'PEE') {
            $state['profileMode'] = false;
            $msg = ($clean === 'PEE') ? 'IGNORED AND EXITED PROFILE MODE' : 'SAVED AND EXITED PROFILE MODE';

            return new ProfileResult([
                "{$clean} - {$msg}",
                'ACTIVE WORK AREA RESTORED TO TERMINAL SESSION',
            ], $state);
        }

        // 3. PI: Profile Ignore
        if ($clean === 'PI') {
            $state['activeProfile'] = null;

            return new ProfileResult([
                'PI - ACTIVE PROFILE IGNORED',
                'PROFILE BUFFER CLEARED',
            ], $state);
        }

        // 4. PER: Save Profile
        if ($clean === 'PER') {
            $p = $state['activeProfile'] ?? [];
            $name = $p['name'] ?? ($p['company'] ?? 'UNNAMED');
            $p['status'] = 'STORED';
            $state['activeProfile'] = $p;

            // Also keep in profiles collection in state if available
            $profiles = $state['profilesList'] ?? [];
            $profiles[] = $p;
            $state['profilesList'] = $profiles;

            return new ProfileResult([
                'PER - PROFILE FILED AND RECORDED IN CENTRAL DATABASE',
                "PROFILE NAME : {$name}",
                'INDEX/NICK   : '.($p['nickname'] ?? 'NONE'),
                'STATUS       : ACTIVE / SYNCHRONISED',
            ], $state);
        }

        // 5. PD: Redisplay active profile
        if ($clean === 'PD') {
            $p = $state['activeProfile'] ?? null;
            if (! $p || empty($p['name']) && empty($p['company'])) {
                return new ProfileResult(['PD: NO ACTIVE PROFILE IN WORK AREA'], $state, true);
            }

            return new ProfileResult($this->formatProfileDisplay($p), $state);
        }

        // 6. PCN/<COMPANY>: Create Company Profile
        if (str_starts_with($clean, 'PCN/')) {
            $companyName = trim(substr($clean, 4));
            $state['profileMode'] = true;
            $state['activeProfile'] = [
                'type' => 'COMPANY',
                'name' => null,
                'company' => $companyName,
                'nickname' => null,
                'followUp' => [],
                'status' => 'IN_CREATION',
            ];

            return new ProfileResult([
                "PCN/ - COMPANY PROFILE CREATED: {$companyName}",
                'ENTER PIN/<NICKNAME> TO ADD SEARCH KEY',
                'ENTER PER TO FILE PROFILE',
            ], $state);
        }

        // 7. PIN/<NICKNAME>: Add nickname to active profile
        if (str_starts_with($clean, 'PIN/')) {
            $nick = trim(substr($clean, 4));
            if (empty($state['activeProfile'])) {
                $state['activeProfile'] = ['type' => 'TRAVELLER', 'status' => 'IN_CREATION'];
            }
            $state['activeProfile']['nickname'] = $nick;

            return new ProfileResult([
                "PIN/ - PROFILE INDEX KEY ADDED: {$nick}",
                'RETRIEVAL KEY REGISTERED FOR PDI/<NICKNAME>',
            ], $state);
        }

        // 8. PFO/<TEXT>/<DATE>: Profile follow-up entry
        if (str_starts_with($clean, 'PFO/')) {
            $text = trim(substr($clean, 4));
            if (empty($state['activeProfile'])) {
                $state['activeProfile'] = ['type' => 'TRAVELLER', 'status' => 'IN_CREATION'];
            }
            $state['activeProfile']['followUp'][] = $text;

            return new ProfileResult([
                "PFO/ - FOLLOW-UP ELEMENT RECORDED: {$text}",
            ], $state);
        }

        // 9. PDN/ <COMPANY> or PDN/-<TRAVELLER>: Display profile by name
        if (str_starts_with($clean, 'PDN/')) {
            $query = trim(substr($clean, 4));
            $isTraveller = str_starts_with($query, '-');
            $cleanName = ltrim($query, '- ');

            $foundProfile = [
                'type' => $isTraveller ? 'TRAVELLER' : 'COMPANY',
                'name' => $isTraveller ? $cleanName : null,
                'company' => ! $isTraveller ? $cleanName : 'GLOBAL ENTERPRISES',
                'nickname' => $cleanName,
                'followUp' => ['VISA EXPIRY MONITORING ACTIVE'],
                'status' => 'ACTIVE',
            ];
            $state['activeProfile'] = $foundProfile;

            return new ProfileResult($this->formatProfileDisplay($foundProfile), $state);
        }

        // 10. PDI/<NICKNAME>: Retrieve profile by nickname
        if (str_starts_with($clean, 'PDI/')) {
            $nick = trim(substr($clean, 4));
            $foundProfile = [
                'type' => 'TRAVELLER',
                'name' => 'BROWN/BAKER MR',
                'company' => 'BLUE SEAS INTL',
                'nickname' => $nick,
                'followUp' => ['US VISA VALID UNTIL 2028'],
                'status' => 'ACTIVE',
            ];
            $state['activeProfile'] = $foundProfile;

            return new ProfileResult($this->formatProfileDisplay($foundProfile), $state);
        }

        // 11. PC/-1: Create profile from Pax 1 of active PNR
        if ($clean === 'PC/-1' || $clean === 'PC/1') {
            $passengers = $state['activePnr']['passengers'] ?? [];
            if (empty($passengers)) {
                return new ProfileResult(['PC/-1: NO PASSENGERS IN ACTIVE PNR TO CREATE PROFILE'], $state, true);
            }
            $pax = $passengers[0];
            $paxName = "{$pax['lastName']}/{$pax['firstName']} ".($pax['title'] ?? '');

            $profile = [
                'type' => 'TRAVELLER',
                'name' => $paxName,
                'company' => 'CORPORATE ACCOUNT',
                'nickname' => $pax['lastName'],
                'followUp' => [],
                'status' => 'IN_CREATION',
            ];
            $state['activeProfile'] = $profile;
            $state['profileMode'] = true;

            return new ProfileResult([
                "PC/-1: PROFILE GENERATED FROM PASSENGER 1: {$paxName}",
                'PROFILE WORK AREA ACTIVATED - ENTER PER TO FILE',
            ], $state);
        }

        // 12. PT*: Create PNR from displayed profile
        if ($clean === 'PT*' || $clean === 'PT') {
            $p = $state['activeProfile'] ?? null;
            if (! $p || empty($p['name'])) {
                return new ProfileResult(['PT*: NO ACTIVE TRAVELLER PROFILE PRESENT TO TRANSFER TO PNR'], $state, true);
            }

            // Transfer passenger name into active PNR
            $paxList = $state['activePnr']['passengers'] ?? [];
            $nameParts = explode('/', $p['name']);
            $last = $nameParts[0] ?? 'PASSENGER';
            $firstWithTitle = $nameParts[1] ?? 'TRAVELER MR';
            $fParts = explode(' ', trim($firstWithTitle));
            $first = $fParts[0] ?? 'TRAVELER';
            $title = $fParts[1] ?? 'MR';

            $paxList[] = [
                'id' => (string) (count($paxList) + 1).'.1',
                'lastName' => $last,
                'firstName' => $first,
                'title' => $title,
                'type' => 'ADT',
            ];
            $state['activePnr']['passengers'] = $paxList;

            // Add contact from profile
            $state['activePnr']['contacts'][] = [
                'id' => count($state['activePnr']['contacts'] ?? []) + 1,
                'type' => 'PROFILE_PHONE',
                'detail' => 'CTCB +44 207 946 0192',
            ];

            return new ProfileResult([
                'PT* - PNR GENERATED FROM DISPLAYED CUSTOMER PROFILE',
                "PASSENGER TRANSFERRED : {$last}/{$first} {$title}",
                'CORPORATE CONTACT ADDED: CTCB +44 207 946 0192',
                'ENTER AVAILABILITY (AN) TO SELECT FLIGHT SEGMENTS',
            ], $state);
        }

        // 13. PLT // <COMPANY>: List all travellers associated to company
        if (str_starts_with($clean, 'PLT')) {
            $comp = trim(preg_replace('/^PLT\s*\/?\/?\s*/', '', $clean) ?? 'CORPORATE');

            return new ProfileResult([
                "--- ASSOCIATED TRAVELLERS FOR COMPANY: {$comp} (PLT) ---",
                '1. BROWN / BAKER MR      (INDEX: BBROW / DIR)',
                '2. SMITH / ALEXANDER MR   (INDEX: ASMIT / MGR)',
                '3. CHEN / MEI MS          (INDEX: MCHEN / ENG)',
                'TOTAL ASSOCIATED TRAVELLERS: 3',
                'USE "PDN/-<SURNAME>" TO ACCESS INDIVIDUAL TRAVELLER PROFILE',
            ], $state);
        }

        // If in profile mode and entering NM1...
        if (! empty($state['profileMode']) && str_starts_with($clean, 'NM')) {
            $travellerName = trim(substr($clean, 3));
            $state['activeProfile']['type'] = 'TRAVELLER';
            $state['activeProfile']['name'] = $travellerName;

            return new ProfileResult([
                "TRAVELLER PROFILE NAME ASSIGNED: {$travellerName}",
                'ENTER PIN/<NICKNAME> TO ADD RETRIEVAL INDEX',
                'ENTER PER TO FILE PROFILE',
            ], $state);
        }

        return new ProfileResult(["PROFILES: UNRECOGNIZED PROFILE COMMAND: \"{$clean}\""], $state, true);
    }

    private function formatProfileDisplay(array $profile): array
    {
        $type = $profile['type'] ?? 'TRAVELLER';
        $title = ($type === 'COMPANY') ? "COMPANY: {$profile['company']}" : "TRAVELLER: {$profile['name']}";
        $lines = [
            "--- AMADEUS CUSTOMER PROFILE DISPLAY ({$type}) ---",
            "RECORD        : {$title}",
            'SEARCH INDEX  : '.($profile['nickname'] ?? 'NONE'),
            'COMPANY ACC   : '.($profile['company'] ?? 'STANDARD'),
            'STATUS        : '.($profile['status'] ?? 'ACTIVE'),
            'CREDIT / FORM : FP CCVI4111111111111111/EXP 1228',
            'SEAT PREF     : NON-SMOKING AISLE (ST/A)',
            'MEAL PREF     : SPECIAL MEAL MOML / HNML',
        ];

        if (! empty($profile['followUp'])) {
            $lines[] = 'FOLLOW-UP     : '.implode('; ', $profile['followUp']);
        }

        return $lines;
    }
}
