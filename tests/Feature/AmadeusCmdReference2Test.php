<?php

namespace Tests\Feature;

use App\Models\Pnr;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AmadeusCmdReference2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->seedTestUsers();
    }

    private function getInitialState(): array
    {
        return [
            'area' => 'A',
            'officeId' => 'LON1A0980',
            'agentId' => '1234AB',
            'isSignedIn' => true,
            'activePnr' => null,
            'lastAvailabilityDisplay' => null,
            'lastPnrList' => null,
            'history' => [],
            'outputLines' => [],
        ];
    }

    public function test_01_work_area_jump_and_spaced_signin_commands(): void
    {
        $state = $this->getInitialState();

        // 1. JXC (Jump to area C)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'JXC', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertFalse($res->json('isError'));
        $this->assertEquals('C', $res->json('updatedState.area'));

        // 2. JMB (Move to area B)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'JMB', 'state' => $res->json('updatedState')]);
        $res->assertStatus(200);
        $this->assertEquals('B', $res->json('updatedState.area'));

        // 3. JD (Work areas status display)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'JD', 'state' => $res->json('updatedState')]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'AMADEUS WORK AREA STATUS'));

        // 4. JMD (Direct jump to area D)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'JMD', 'state' => $res->json('updatedState')]);
        $res->assertStatus(200);
        $this->assertEquals('D', $res->json('updatedState.area'));

        // 5. JOB (Sign out area B)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'JOB', 'state' => $res->json('updatedState')]);
        $res->assertStatus(200);
        $this->assertFalse($res->json('isError'));
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'SPECIFIC WORK AREA B'));

        // 6. JI 1234AA/AS (Sign in with space)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'JI 1234AA/AS', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertFalse($res->json('isError'));
        $this->assertTrue($res->json('updatedState.isSignedIn'));
        $this->assertEquals('1234AA', $res->json('updatedState.agentId'));
    }

    public function test_02_ais_and_office_briefing_commands(): void
    {
        $state = $this->getInitialState();

        // 1. GG AIS (AIS Main Directory)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'GG AIS', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'AMADEUS INFORMATION SYSTEM'));

        // 2. GG AIR UL (Airline Info)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'GG AIR UL', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'SRILANKAN AIRLINES'));

        // 3. GG WEA LAX (Weather info)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'GG WEA LAX', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'WEATHER FORECAST'));

        // 4. GG PCA EK (Participating Carrier Access)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'GG PCA EK', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'PARTICIPATING CARRIER ACCESS'));

        // 5. GG PCAL (List of PCA carriers)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'GG PCAL', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'PARTICIPATING CARRIERS'));

        // 6. GG ALLIANCE
        $res = $this->postJson('/api/terminal/execute', ['command' => 'GG ALLIANCE', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'STAR ALLIANCE'));

        // 7. GD (Office Briefing)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'GD', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'OFFICE BRIEFING'));

        // 8. GMD text (Update briefing)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'GMD ALL STAFF MEETING AT 1500HRS', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'BRIEFING UPDATED'));
    }

    public function test_03_help_topics(): void
    {
        $state = $this->getInitialState();

        // 1. HE SIGN
        $res = $this->postJson('/api/terminal/execute', ['command' => 'HE SIGN', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'HELP: SIGN-IN'));

        // 2. HE MEAL CODES
        $res = $this->postJson('/api/terminal/execute', ['command' => 'HE MEAL CODES', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'SPECIAL MEAL CODES'));

        // 3. HE STEPS
        $res = $this->postJson('/api/terminal/execute', ['command' => 'HE STEPS', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'STEP-BY-STEP RESERVATION WORKFLOW'));

        // 4. HE/ (Help on previous command)
        $state['lastCommand'] = 'AN15DECFRALON';
        $res = $this->postJson('/api/terminal/execute', ['command' => 'HE/', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'HE AN') || str_contains($lines, 'AVAILABILITY'));
    }

    public function test_04_timatic_commands(): void
    {
        $state = $this->getInitialState();

        // 1. TIFV (Visa Mask)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'TIFV', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'TIMATIC VISA'));

        // 2. TIFH (Health Mask)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'TIFH', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'HEALTH INFORMATION'));

        // 3. TIFA (Combined Mask)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'TIFA', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'FULL VISA & HEALTH MASK'));

        // 4. TINEWS
        $res = $this->postJson('/api/terminal/execute', ['command' => 'TINEWS', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'TIMATIC TRAVEL NEWS') || str_contains($lines, 'TINEWS'));

        // 5. TIRGL/SCHS (Schengen list)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'TIRGL/SCHS', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'SCHENGEN MEMBER STATES') || str_contains($lines, 'SCHS'));
    }

    public function test_05_math_and_date_conversions(): void
    {
        $state = $this->getInitialState();

        // 1. DF 889; 1733; 669 (Addition)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'DF 889; 1733; 669', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, '3291'));

        // 2. DF 1955-889 (Subtraction)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'DF 1955-889', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, '1066'));

        // 3. DF 1604*33 (Multiplication)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'DF 1604*33', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, '52932'));

        // 4. DD SIN (Singapore local time)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'DD SIN', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'LOCAL TIME'));

        // 5. DD LON/LAX (Time difference)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'DD LON/LAX', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'TIME DIFFERENCE'));

        // 6. DD25DEC08 (Day of week)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'DD25DEC08', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'THURSDAY'));

        // 7. D22APR13/30 (Date offset)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'D22APR13/30', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'DAYS ='));
    }

    public function test_06_decode_and_encode_commands(): void
    {
        $state = $this->getInitialState();

        // 1. DC FRANCE -> FR
        $res = $this->postJson('/api/terminal/execute', ['command' => 'DC FRANCE', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'FR'));

        // 2. DC FR -> FRANCE
        $res = $this->postJson('/api/terminal/execute', ['command' => 'DC FR', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'FRANCE'));

        // 3. DNH BEST WESTERN -> BW
        $res = $this->postJson('/api/terminal/execute', ['command' => 'DNH BEST WESTERN', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'BW'));

        // 4. DNC AVIS -> ZI
        $res = $this->postJson('/api/terminal/execute', ['command' => 'DNC AVIS', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'ZI'));

        // 5. DNS FLORIDA -> FL
        $res = $this->postJson('/api/terminal/execute', ['command' => 'DNS FLORIDA', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'FL'));

        // 6. DNN SUM
        $res = $this->postJson('/api/terminal/execute', ['command' => 'DNN SUM', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'SUMMER PROMOTIONAL RATE'));

        // 7. CE PHN
        $res = $this->postJson('/api/terminal/execute', ['command' => 'CE PHN', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'CELLULAR TELEPHONE'));

        // 8. DAN LONDON/GB
        $res = $this->postJson('/api/terminal/execute', ['command' => 'DAN LONDON/GB', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'LON'));

        // 9. DNE IL9
        $res = $this->postJson('/api/terminal/execute', ['command' => 'DNE IL9', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'ILYUSHIN'));
    }

    public function test_07_flight_info_mct_and_connect_points(): void
    {
        $state = $this->getInitialState();

        // 1. DO TG774 /23DEC /BKKLAX
        $res = $this->postJson('/api/terminal/execute', ['command' => 'DO TG774 /23DEC /BKKLAX', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'FLIGHT OPERATIONAL INFORMATION'));

        // 2. DM FRA (MCT)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'DM FRA', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'MINIMUM CONNECTING TIMES AT: FRA'));

        // 3. DM BA/ LHR /AA (Interline MCT)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'DM BA/ LHR /AA', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'LHR'));

        // 4. DRT DEL BJS (Connect points)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'DRT DEL BJS', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'CONNECTING POINTS FOR: DEL TO BJS'));
    }

    public function test_08_hotel_and_car_system(): void
    {
        $state = $this->getInitialState();

        // 1. HL LON (Hotel list)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'HL LON', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'HOTEL LIST DISPLAY'));

        // 2. HS 1 (Sell hotel room)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'HS 1', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'HOTEL SEGMENT'));

        // 3. CL LAX (Car list)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'CL LAX', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'CAR RENTAL DIRECTORY'));

        // 4. CS 1 (Sell car)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'CS 1', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'CAR SEGMENT'));
    }

    public function test_09_customer_profile_system(): void
    {
        $state = $this->getInitialState();

        // 1. PM (Start profile mode)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'PM', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'CUSTOMER PROFILE MODE'));
        $state = $res->json('updatedState');

        // 2. NM1BROWN/BAKER MR (Profile passenger)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'NM1BROWN/BAKER MR', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'TRAVELLER PROFILE NAME ASSIGNED'));
        $state = $res->json('updatedState');

        // 3. PIN/NICK (Nickname in profile)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'PIN/NICK', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'NICKNAME'));
        $state = $res->json('updatedState');

        // 4. PER (Save profile)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'PER', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'PROFILE FILED'));
        $state = $res->json('updatedState');

        // 5. PT* (Transfer profile to PNR)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'PT*', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'PNR GENERATED FROM DISPLAYED CUSTOMER PROFILE') || str_contains($lines, 'PT*'));

        // 6. PME (Exit profile mode)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'PME', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'EXITED PROFILE MODE'));
    }

    public function test_10_availability_variations_and_changes(): void
    {
        $state = $this->getInitialState();

        // 1. ANTG15SEPDELBKK (Carrier-preferred display)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'ANTG15SEPDELBKK', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertNotEmpty($res->json('updatedState.lastAvailabilityDisplay.lines'));
        $state = $res->json('updatedState');

        // 2. AC 5 (Change date +5 days)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'AC 5', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, '20SEP'));
        $state = $res->json('updatedState');

        // 3. AC/ABA (Change carrier)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'AC/ABA', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'BA'));
        $state = $res->json('updatedState');

        // 4. AC FQ (Switch to Fare Quote)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'AC FQ', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'PUBLIC FARES FOR'));

        // 5. AC SN (Switch to Timetable)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'AC SN', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'SN'));

        // 6. AC // KUL (Change destination)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'AC // KUL', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'KUL'));
    }

    public function test_11_direct_access_commands(): void
    {
        $state = $this->getInitialState();

        // 1. 19WAD23DECSINJKT (Direct access availability)
        $res = $this->postJson('/api/terminal/execute', ['command' => '19WAD23DECSINJKT', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'DIRECT ACCESS LINK ACTIVE: 9W'));

        // 2. 1AIRT H55PW (Direct access PNR retrieval)
        $res = $this->postJson('/api/terminal/execute', ['command' => '1AIRT H55PW', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'DIRECT ACCESS PNR RETRIEVAL: H55PW'));

        // 3. 19WFQDDELLON (Direct access fare display)
        $res = $this->postJson('/api/terminal/execute', ['command' => '19WFQDDELLON', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'DIRECT ACCESS FARES'));
    }

    public function test_12_segment_sell_variations(): void
    {
        $state = $this->getInitialState();

        // First establish availability
        $res = $this->postJson('/api/terminal/execute', ['command' => 'AN15DECFRALON', 'state' => $state]);
        $state = $res->json('updatedState');

        // 1. SS1Y1/PE (Waitlist sell)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SS1Y1/PE', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('PE', $res->json('updatedState.activePnr.segments.0.status'));
        $state = $res->json('updatedState');

        // 2. STG316M03AUGDELBKK1 (Long sell)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'STG316M03AUGDELBKK1', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertCount(2, $res->json('updatedState.activePnr.segments'));
        $state = $res->json('updatedState');

        // 3. SOLHC24DECFRABOM (Open segment)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SOLHC24DECFRABOM', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertCount(3, $res->json('updatedState.activePnr.segments'));
        $this->assertEquals('OPEN', $res->json('updatedState.activePnr.segments.2.status'));
        $state = $res->json('updatedState');

        // 4. SISV219Y20MAYJEDISTHK1/0815 1645 (Info segment)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SISV219Y20MAYJEDISTHK1/0815 1645', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertCount(4, $res->json('updatedState.activePnr.segments'));
        $state = $res->json('updatedState');

        // 5. SSSQ401Y20DECDELSINGK1/YKABH (Ghost segment)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SSSQ401Y20DECDELSINGK1/YKABH', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertCount(5, $res->json('updatedState.activePnr.segments'));
        $this->assertEquals('GK', $res->json('updatedState.activePnr.segments.4.status'));
    }

    public function test_13_name_element_variations(): void
    {
        $state = $this->getInitialState();

        // 1. Multiple surnames space-separated in one command: NM1SEN/JAYA MS 1SETH/RADHA MS
        $res = $this->postJson('/api/terminal/execute', ['command' => 'NM1SEN/JAYA MS 1SETH/RADHA MS', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertCount(2, $res->json('updatedState.activePnr.passengers'));
        $this->assertEquals('SEN', $res->json('updatedState.activePnr.passengers.0.lastName'));
        $this->assertEquals('SETH', $res->json('updatedState.activePnr.passengers.1.lastName'));
        $state = $res->json('updatedState');

        // 2. Infant with DOB: NM1SINGH/NISHA MRS(INF/ANU/02FEB09)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'NM1SINGH/NISHA MRS(INF/ANU/02FEB09)', 'state' => $state]);
        $res->assertStatus(200);
        $passengers = $res->json('updatedState.activePnr.passengers');
        $this->assertCount(3, $passengers);
        $this->assertEquals('ANU', $passengers[2]['infant']['firstName']);
        $this->assertEquals('02FEB09', $passengers[2]['infant']['dateOfBirth']);
        $state = $res->json('updatedState');

        // 3. Infant with seat: NM1BROWN/JANE(INS)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'NM1BROWN/JANE(INS)', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertCount(4, $res->json('updatedState.activePnr.passengers'));
        $this->assertEquals('INS', $res->json('updatedState.activePnr.passengers.3.type'));
        $state = $res->json('updatedState');

        // 4. Stretcher passenger: NM1LEWIS/JOHN MR (IDSTCR)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'NM1LEWIS/JOHN MR (IDSTCR)', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertCount(5, $res->json('updatedState.activePnr.passengers'));
        $this->assertEquals('STCR', $res->json('updatedState.activePnr.passengers.4.type'));
    }

    public function test_14_ticketing_and_contact_variations(): void
    {
        $state = $this->getInitialState();

        // 1. Mobile contact APM - DEL 9818568329
        $res = $this->postJson('/api/terminal/execute', ['command' => 'APM - DEL 9818568329', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertCount(1, $res->json('updatedState.activePnr.contacts'));
        $this->assertEquals('MOBILE', $res->json('updatedState.activePnr.contacts.0.type'));
        $state = $res->json('updatedState');

        // 2. TKDO01JAN (Ticketing date override)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'TKDO01JAN', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('TKDO', $res->json('updatedState.activePnr.ticketing.type'));
        $state = $res->json('updatedState');

        // 3. TKIN05JAN (Invoice date)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'TKIN05JAN', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('TKIN', $res->json('updatedState.activePnr.ticketing.type'));
        $state = $res->json('updatedState');

        // 4. TKXL/06APR (Auto-cancel date)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'TKXL/06APR', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('TKXL', $res->json('updatedState.activePnr.ticketing.type'));
    }

    public function test_15_extended_security_and_ticket_numbers(): void
    {
        $state = $this->getInitialState();

        // 1. ES DEL1A0900-B
        $res = $this->postJson('/api/terminal/execute', ['command' => 'ES DEL1A0900-B', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'SECURITY AGREEMENT ESTABLISHED'));

        // 2. ESD (Cancel extended security)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'ESD', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'EXTENDED SECURITY AGREEMENT CANCELLED'));

        // 3. FHE 016-1234567890-10/P2 (Conjunction ticket transmission)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'FHE 016-1234567890-10/P2', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, '016-1234567890-10'));

        // 4. RTLC (Ticketless booking sync)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RTLC', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'TICKETLESS BOOKING SYNCHRONISED'));
    }

    public function test_16_fares_best_pricer_and_tqt(): void
    {
        $state = $this->getInitialState();

        // Setup basic PNR with flight and passenger
        $res = $this->postJson('/api/terminal/execute', ['command' => 'AN15DECFRALON', 'state' => $state]);
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SS1Y1', 'state' => $res->json('updatedState')]);
        $res = $this->postJson('/api/terminal/execute', ['command' => 'NM1SMITH/JOHN MR', 'state' => $res->json('updatedState')]);
        $state = $res->json('updatedState');

        // 1. FXX/D F-Y2RT (Fare diagnostic)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'FXX/D F-Y2RT', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'AMADEUS FARE DIAGNOSTIC'));

        // 2. FXA (Best pricer inform)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'FXA', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'BEST PRICER'));

        // 3. FXL (Best pricer lowest available)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'FXL', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'BEST PRICER'));

        // 4. FXP (Store TST)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'FXP', 'state' => $state]);
        $res->assertStatus(200);
        $state = $res->json('updatedState');

        // 5. TQT (Display stored TST)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'TQT', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'STORED TST DISPLAY'));

        // 6. FQC (Currency conversion)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'FQC', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'FARE QUOTE CURRENCY') || str_contains($lines, 'FQC'));
    }

    public function test_17_pnr_retrieval_and_claim(): void
    {
        $state = $this->getInitialState();

        // 1. RTLH761/14SEPDELFRA-SEN (Retrieve by flight & surname)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RTLH761/14SEPDELFRA-SEN', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'PNR RETRIEVAL BY FLIGHT: LH761'));

        // 2. RTTKT/016-1234567890 (Retrieve by ticket number)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RTTKT/016-1234567890', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'PNR RETRIEVAL BY TICKET: 016-1234567890'));

        // 3. RLN (Past date locator)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RLN', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'NUMERIC RECORD LOCATOR'));

        // 4. RT AXR (Air Rail display)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RT AXR', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'ASSOCIATED SPLIT RECORDS'));

        // 5. RO CO ABC123 (Claim non-Amadeus PNR)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RO CO ABC123', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'PNR CLAIM RECORD RETRIEVED'));
    }

    public function test_18_partial_displays_printing_and_copy(): void
    {
        $state = $this->getInitialState();

        // Setup active PNR
        $res = $this->postJson('/api/terminal/execute', ['command' => 'AN15DECFRALON', 'state' => $state]);
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SS1Y1', 'state' => $res->json('updatedState')]);
        $res = $this->postJson('/api/terminal/execute', ['command' => 'NM1SMITH/JOHN MR', 'state' => $res->json('updatedState')]);
        $res = $this->postJson('/api/terminal/execute', ['command' => 'AP LON 020-7946-0912', 'state' => $res->json('updatedState')]);
        $res = $this->postJson('/api/terminal/execute', ['command' => 'TKOK', 'state' => $res->json('updatedState')]);
        $state = $res->json('updatedState');

        // 1. RTI (Itinerary partial display)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RTI', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'PARTIAL DISPLAY (RTI)'));

        // 2. IBD/P1 (Print Boarding Pass)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'IBD/P1', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'ITINERARY BASIC DISPLAY (IBD)'));

        // 3. IEP/LP FR/P1 (E-ticket receipt in French)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'IEP/LP FR/P1', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'IMPRESSION ETENDUE DU VOYAGE') || str_contains($lines, 'DOSSIER PASSAGER'));

        // 4. RRN/4 (Copy PNR)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RRN/4', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'OK COPY PNR'));

        // 5. RRN/CY (Copy segments and change booking class)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RRN/CY', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'OK COPY PNR') || str_contains($lines, 'COPY PNR'));

        // 6. HERH.148 (History help)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'HERH.148', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'PNR HISTORY') || str_contains($lines, 'HERH.148'));
    }

    public function test_19_queues_and_multi_list(): void
    {
        $student = User::where('username', 'student')->first();
        $this->actingAs($student);

        $state = $this->getInitialState();

        // 1. QTQ (Total queue count)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'QTQ', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'CONFIGURED QUEUES'));

        // 2. QS97C0D1 (Queue start category with date range)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'QS97C0D1', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'QUEUE 97 CATEGORY 0 DATE RANGE 1'));

        // 3. QU (Queue update / next)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'QU', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'QU -') || str_contains($lines, 'REDISPLAY QUEUE MESSAGE HEADER'));

        // 4. QDEF (Queue sign-off)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'QDEF', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'QDEF') || str_contains($lines, 'EXITED QUEUE MODE'));

        // 5. QE/DEL1A0900/0C0 (Place on office queue)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'QE/DEL1A0900/0C0', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'PLACED ON QUEUE'));
    }
}
