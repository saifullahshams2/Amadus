<?php

namespace Tests\Feature;

use App\Models\Pnr;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AmadeusCmdReferenceTest extends TestCase
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

    public function test_01_sign_in_and_sign_out_commands(): void
    {
        $state = $this->getInitialState();

        // JI0001AA/SU
        $res = $this->postJson('/api/terminal/execute', ['command' => 'JI0001AA/SU', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertFalse($res->json('isError'));
        $this->assertTrue(str_contains(implode(' ', $res->json('outputLines')), '0001AA'));

        // JI*0001AA/SU (all areas)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'JI*0001AA/SU', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertFalse($res->json('isError'));
        $this->assertTrue(str_contains(implode(' ', $res->json('outputLines')), 'ALL WORK AREAS'));

        // JI0001AA/SU-Password
        $res = $this->postJson('/api/terminal/execute', ['command' => 'JI0001AA/SU-Password', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertFalse($res->json('isError'));

        // JO
        $res = $this->postJson('/api/terminal/execute', ['command' => 'JO', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertFalse($res->json('isError'));
        $this->assertFalse($res->json('updatedState.isSignedIn'));

        // JO*
        $res = $this->postJson('/api/terminal/execute', ['command' => 'JO*', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertFalse($res->json('isError'));
        $this->assertTrue(str_contains(implode(' ', $res->json('outputLines')), 'ALL WORK AREAS'));
    }

    public function test_02_availability_display_variations(): void
    {
        $state = $this->getInitialState();

        // AN01MARDOHDXB
        $res = $this->postJson('/api/terminal/execute', ['command' => 'AN01MARDOHDXB', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertFalse($res->json('isError'));
        $this->assertNotEmpty($res->json('updatedState.lastAvailabilityDisplay.lines'));

        // AN01MARDOHDXB1200 (specified time)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'AN01MARDOHDXB1200', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertFalse($res->json('isError'));

        // AN01MARDOHDXB/AEK (specified airline)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'AN01MARDOHDXB/AEK', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertFalse($res->json('isError'));
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'EK'));

        // AN01MARDOHDXB/XAUH (via connection)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'AN01MARDOHDXB/XAUH', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertFalse($res->json('isError'));
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'AUH'));

        // AN01MARDOHDXB/CV (specified class)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'AN01MARDOHDXB/CV', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertFalse($res->json('isError'));

        // Combination: AN01MARDOHDXB1200/XAUH/AGF/CM
        $res = $this->postJson('/api/terminal/execute', ['command' => 'AN01MARDOHDXB1200/XAUH/AGF/CM', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertFalse($res->json('isError'));

        // Availability In-bound and Out-bound: AN12MARDOHDXB*15MAR
        $res = $this->postJson('/api/terminal/execute', ['command' => 'AN12MARDOHDXB*15MAR', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertFalse($res->json('isError'));
        $lines = $res->json('updatedState.lastAvailabilityDisplay.lines');
        $this->assertGreaterThanOrEqual(7, count($lines));
        $lineNumbers = array_column($lines, 'lineNumber');
        $this->assertContains(1, $lineNumbers);
        $this->assertContains(13, $lineNumbers);

        // Schedule display: SN01MARDOHDXB
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SN01MARDOHDXB', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertFalse($res->json('isError'));
        $this->assertEquals('SCHEDULE_DISPLAY', $res->json('commandType'));
    }

    public function test_03_name_elements_and_modifications(): void
    {
        $state = $this->getInitialState();

        // 1. Single adult: NM1HUSSAIN/ALI MR
        $res = $this->postJson('/api/terminal/execute', ['command' => 'NM1HUSSAIN/ALI MR', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertCount(1, $res->json('updatedState.activePnr.passengers'));

        // 2. Same family name for 2 adults: NM2HUSSAIN/ALI MR/M MRS
        $state = $this->getInitialState();
        $res = $this->postJson('/api/terminal/execute', ['command' => 'NM2HUSSAIN/ALI MR/M MRS', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertCount(2, $res->json('updatedState.activePnr.passengers'));

        // 3. Chained multi-names with semicolon: NM1HUSSAIN/A MR;NM1SYED/ALI MR
        $state = $this->getInitialState();
        $res = $this->postJson('/api/terminal/execute', ['command' => 'NM1HUSSAIN/A MR;NM1SYED/ALI MR', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertCount(2, $res->json('updatedState.activePnr.passengers'));

        // 4. Child passenger without DOB: NM1HUSSAIN/NASEER MSTR(CHD)
        $state = $this->getInitialState();
        $res = $this->postJson('/api/terminal/execute', ['command' => 'NM1HUSSAIN/NASEER MSTR(CHD)', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('CHD', $res->json('updatedState.activePnr.passengers.0.type'));

        // 5. Infant with same family name: NM1HUSSAIN/M MRS(INF/NABIL)
        $state = $this->getInitialState();
        $res = $this->postJson('/api/terminal/execute', ['command' => 'NM1HUSSAIN/M MRS(INF/NABIL)', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('NABIL', $res->json('updatedState.activePnr.passengers.0.infant.firstName'));

        // 6. Infant with different family name: NM1HUSSAIN/M MRS(INFALEMADI/ALI)
        $state = $this->getInitialState();
        $res = $this->postJson('/api/terminal/execute', ['command' => 'NM1HUSSAIN/M MRS(INFALEMADI/ALI)', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('ALEMADI', $res->json('updatedState.activePnr.passengers.0.infant.lastName'));
        $this->assertEquals('ALI', $res->json('updatedState.activePnr.passengers.0.infant.firstName'));

        // Setup 6 passengers for modification tests
        $state = $this->getInitialState();
        $state['activePnr'] = [
            'creationOffice' => 'LON1A0980',
            'creationAgent' => '1234AB',
            'passengers' => [
                ['id' => '1.1', 'lastName' => 'DOE', 'firstName' => 'JOHN', 'title' => 'MR', 'type' => 'ADT'],
                ['id' => '2.1', 'lastName' => 'SMITH', 'firstName' => 'TIM', 'title' => 'MSTR', 'type' => 'CHD'],
                ['id' => '3.1', 'lastName' => 'BROWN', 'firstName' => 'BOB', 'title' => 'MSTR', 'type' => 'ADT'],
                ['id' => '4.1', 'lastName' => 'GREEN', 'firstName' => 'SARAH', 'title' => 'MRS', 'type' => 'ADT', 'infant' => ['firstName' => 'BABY', 'lastName' => 'GREEN']],
                ['id' => '5.1', 'lastName' => 'WHITE', 'firstName' => 'LISA', 'title' => 'MS', 'type' => 'ADT'],
                ['id' => '6.1', 'lastName' => 'HUSSAIN', 'firstName' => 'OLDNAME', 'title' => 'MS', 'type' => 'ADT'],
            ],
            'segments' => [],
            'contacts' => [],
            'remarks' => [],
            'specialServiceRequests' => [],
            'tsts' => [],
            'tickets' => [],
        ];

        // 7. Remove child title from passenger 2: 2/
        $res = $this->postJson('/api/terminal/execute', ['command' => '2/', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('ADT', $res->json('updatedState.activePnr.passengers.1.type'));
        $state = $res->json('updatedState');

        // 8. Add child title to passenger 3: 3/(CHD)
        $res = $this->postJson('/api/terminal/execute', ['command' => '3/(CHD)', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('CHD', $res->json('updatedState.activePnr.passengers.2.type'));
        $state = $res->json('updatedState');

        // 9. Remove infant from passenger 4: 4/
        $res = $this->postJson('/api/terminal/execute', ['command' => '4/', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertArrayNotHasKey('infant', $res->json('updatedState.activePnr.passengers.3'));
        $state = $res->json('updatedState');

        // 10. Add infant to passenger 5: 5/(INF/ALI)
        $res = $this->postJson('/api/terminal/execute', ['command' => '5/(INF/ALI)', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('ALI', $res->json('updatedState.activePnr.passengers.4.infant.firstName'));
        $state = $res->json('updatedState');

        // 11. Update name of passenger 6: 6/MARIYUM MRS
        $res = $this->postJson('/api/terminal/execute', ['command' => '6/MARIYUM MRS', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('MARIYUM', $res->json('updatedState.activePnr.passengers.5.firstName'));
        $this->assertEquals('MRS', $res->json('updatedState.activePnr.passengers.5.title'));
    }

    public function test_04_segment_sell_variations(): void
    {
        $state = $this->getInitialState();

        // 1. Long sell direct: SSQR001B15MARDOHLHRNN2
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SSQR001B15MARDOHLHRNN2', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertCount(1, $res->json('updatedState.activePnr.segments'));
        $seg = $res->json('updatedState.activePnr.segments.0');
        $this->assertEquals('QR', $seg['airlineCode']);
        $this->assertEquals('001', $seg['flightNumber']);
        $this->assertEquals('B', $seg['bookingClass']);
        $this->assertEquals(2, $seg['seats']);

        // 2. Dead segment as OK status: SSQR001B15MARDOHLHRGK2/QR
        $state = $this->getInitialState();
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SSQR001B15MARDOHLHRGK2/QR', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('GK', $res->json('updatedState.activePnr.segments.0.status'));

        // 3. Dead segment as WL status: SSQR001B15MARDOHLHRGL2/QR
        $state = $this->getInitialState();
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SSQR001B15MARDOHLHRGL2/QR', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('GL', $res->json('updatedState.activePnr.segments.0.status'));

        // 4. Open segment: SO QR B LHRDOH
        $state = $this->getInitialState();
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SO QR B LHRDOH', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('OPEN', $res->json('updatedState.activePnr.segments.0.status'));
        $this->assertEquals('QR', $res->json('updatedState.activePnr.segments.0.airlineCode'));

        // 5. Surface segment: SIARNK
        $state = $this->getInitialState();
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SIARNK', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('ARNK', $res->json('updatedState.activePnr.segments.0.airlineCode'));

        // 6. Roundtrip sell from display: SS2V1*V13
        $state = $this->getInitialState();
        $avRes = $this->postJson('/api/terminal/execute', ['command' => 'AN12MARDOHDXB*15MAR', 'state' => $state]);
        $state = $avRes->json('updatedState');

        $res = $this->postJson('/api/terminal/execute', ['command' => 'SS2V1*V13', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertCount(2, $res->json('updatedState.activePnr.segments'));
        $this->assertEquals(2, $res->json('updatedState.activePnr.segments.0.seats'));
        $this->assertEquals(2, $res->json('updatedState.activePnr.segments.1.seats'));

        // 7. Multi-leg sell: SS2JF3
        $state = $this->getInitialState();
        $avRes = $this->postJson('/api/terminal/execute', ['command' => 'AN01MARDOHDXB', 'state' => $state]);
        $state = $avRes->json('updatedState');

        $res = $this->postJson('/api/terminal/execute', ['command' => 'SS2JF3', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertCount(2, $res->json('updatedState.activePnr.segments'));
        $this->assertEquals('J', $res->json('updatedState.activePnr.segments.0.bookingClass'));
        $this->assertEquals('F', $res->json('updatedState.activePnr.segments.1.bookingClass'));
    }

    public function test_05_phone_ticketing_and_received_from_elements(): void
    {
        $state = $this->getInitialState();

        // AP 4667579
        $res = $this->postJson('/api/terminal/execute', ['command' => 'AP 4667579', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('4667579', $res->json('updatedState.activePnr.contacts.0.detail'));

        // AP DOH 4667579
        $res = $this->postJson('/api/terminal/execute', ['command' => 'AP DOH 4667579', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('DOH 4667579', $res->json('updatedState.activePnr.contacts.0.detail'));

        // AP alone (office default number)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'AP', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertCount(1, $res->json('updatedState.activePnr.contacts'));

        // APE-abc@abc.com
        $res = $this->postJson('/api/terminal/execute', ['command' => 'APE-abc@abc.com', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('abc@abc.com', $res->json('updatedState.activePnr.contacts.0.detail'));

        // TKOK
        $res = $this->postJson('/api/terminal/execute', ['command' => 'TKOK', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('TKOK', $res->json('updatedState.activePnr.ticketing.type'));

        // TKTL12MAR/1200
        $res = $this->postJson('/api/terminal/execute', ['command' => 'TKTL12MAR/1200', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('TKTL', $res->json('updatedState.activePnr.ticketing.type'));

        // TKXL12MAR/1200
        $res = $this->postJson('/api/terminal/execute', ['command' => 'TKXL12MAR/1200', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('TKXL', $res->json('updatedState.activePnr.ticketing.type'));

        // RF PAX
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RF PAX', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('PAX', $res->json('updatedState.activePnr.receivedFrom'));

        // RF P
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RF P', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('P', $res->json('updatedState.activePnr.receivedFrom'));

        // RF ALI
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RF ALI', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('ALI', $res->json('updatedState.activePnr.receivedFrom'));

        // RF MR ALEMADI
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RF MR ALEMADI', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('MR ALEMADI', $res->json('updatedState.activePnr.receivedFrom'));
    }

    public function test_06_retrieve_pnr_and_airline_locators(): void
    {
        $student = User::where('username', 'student')->first();
        $this->actingAs($student);

        // Seed a confirmed PNR
        $pnrData = [
            'recordLocator' => 'Q1W2E3',
            'creationOffice' => 'LON1A0980',
            'creationAgent' => '1234AB',
            'passengers' => [
                ['id' => '1.1', 'lastName' => 'AHMED', 'firstName' => 'TARIQ', 'title' => 'MR', 'type' => 'ADT'],
            ],
            'segments' => [
                ['segmentNumber' => 1, 'airlineCode' => 'QR', 'flightNumber' => '001', 'bookingClass' => 'Y', 'departureDate' => '15MAR', 'departureAirport' => 'DOH', 'arrivalAirport' => 'LHR', 'departureTime' => '0845', 'arrivalTime' => '1215', 'seats' => 1, 'status' => 'HK'],
            ],
            'contacts' => [['id' => 1, 'type' => 'PHONE', 'detail' => 'DOH 4667579']],
            'ticketing' => ['type' => 'TKOK', 'raw' => 'TKOK'],
            'receivedFrom' => 'AHMED',
        ];

        Pnr::create([
            'record_locator' => 'Q1W2E3',
            'user_id' => 'student',
            'office_id' => 'LON1A0980',
            'status' => 'CONFIRMED',
            'data_json' => $pnrData,
        ]);

        $state = $this->getInitialState();

        // 1. RTXXXXXX (locator)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RTQ1W2E3', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('Q1W2E3', $res->json('updatedState.activePnr.recordLocator'));

        // 2. RT/AHMED (surname search)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RT/AHMED', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('Q1W2E3', $res->json('updatedState.activePnr.recordLocator'));

        // 3. RT/A (alphabetical index)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RT/A', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertNotEmpty($res->json('updatedState.lastPnrList'));
        $state = $res->json('updatedState');

        // 4. RT1 / RT4 (retrieve line from list)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RT1', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertNotEmpty($res->json('updatedState.activePnr.recordLocator'));

        // 5. RL (Airline locators)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RL', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'RECORD LOCATOR DISPLAY'));
    }

    public function test_07_lm_flight_pnr_list_and_line_retrieval(): void
    {
        $state = $this->getInitialState();

        // LM/QR001/12MAR/DOHLHR
        $res = $this->postJson('/api/terminal/execute', ['command' => 'LM/QR001/12MAR/DOHLHR', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertCount(5, $res->json('updatedState.lastPnrList'));
        $state = $res->json('updatedState');

        // LM5
        $res = $this->postJson('/api/terminal/execute', ['command' => 'LM5', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertNotEmpty($res->json('updatedState.activePnr.recordLocator'));
        $this->assertEquals('AHMED', $res->json('updatedState.activePnr.passengers.0.lastName'));
    }

    public function test_08_frequent_flyer_commands(): void
    {
        $state = $this->getInitialState();

        // FFNQR-1111111111
        $res = $this->postJson('/api/terminal/execute', ['command' => 'FFNQR-1111111111', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertCount(1, $res->json('updatedState.activePnr.specialServiceRequests'));
        $state = $res->json('updatedState');

        // FFNQR-1111111111/P2
        $res = $this->postJson('/api/terminal/execute', ['command' => 'FFNQR-1111111111/P2', 'state' => $state]);
        $res->assertStatus(200);
        $ssr = $res->json('updatedState.activePnr.specialServiceRequests.1');
        $this->assertTrue(str_contains($ssr['text'], '/P2'));
        $state = $res->json('updatedState');

        // SRFQTVEK-EK111111111
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SRFQTVEK-EK111111111', 'state' => $state]);
        $res->assertStatus(200);

        // SRFQTVEK-EK111111111/P3
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SRFQTVEK-EK111111111/P3', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, '/P3'));
    }

    public function test_09_seat_selection_and_seat_map(): void
    {
        $state = $this->getInitialState();
        $state['activePnr']['segments'] = [
            ['segmentNumber' => 1, 'airlineCode' => 'QR', 'flightNumber' => '001', 'bookingClass' => 'Y', 'departureDate' => '15MAR', 'departureAirport' => 'DOH', 'arrivalAirport' => 'LHR', 'seats' => 1, 'status' => 'HK', 'equipment' => '77W'],
            ['segmentNumber' => 2, 'airlineCode' => 'QR', 'flightNumber' => '002', 'bookingClass' => 'Y', 'departureDate' => '16MAR', 'departureAirport' => 'LHR', 'arrivalAirport' => 'DOH', 'seats' => 1, 'status' => 'HK', 'equipment' => '77W'],
            ['segmentNumber' => 3, 'airlineCode' => 'QR', 'flightNumber' => '003', 'bookingClass' => 'Y', 'departureDate' => '17MAR', 'departureAirport' => 'DOH', 'arrivalAirport' => 'DXB', 'seats' => 1, 'status' => 'HK', 'equipment' => '77W'],
        ];

        // SM3 (display seat map for segment 3)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SM3', 'state' => $state]);
        $res->assertStatus(200);
        $lines = implode(' ', $res->json('outputLines'));
        $this->assertTrue(str_contains($lines, 'SEAT MAP'));
        $this->assertTrue(str_contains($lines, 'SEGMENT 3'));

        // ST/R/20K
        $res = $this->postJson('/api/terminal/execute', ['command' => 'ST/R/20K', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertFalse($res->json('isError'));

        // ST/R/20K/P4
        $res = $this->postJson('/api/terminal/execute', ['command' => 'ST/R/20K/P4', 'state' => $state]);
        $res->assertStatus(200);

        // ST/R/20K/S4
        $res = $this->postJson('/api/terminal/execute', ['command' => 'ST/R/20K/S4', 'state' => $state]);
        $res->assertStatus(200);

        // ST/S (generic smoking)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'ST/S', 'state' => $state]);
        $res->assertStatus(200);

        // ST (generic non-smoking)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'ST', 'state' => $state]);
        $res->assertStatus(200);

        // ST/W/P2 (window pax 2)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'ST/W/P2', 'state' => $state]);
        $res->assertStatus(200);

        // ST/A/S4 (aisle segment 4)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'ST/A/S4', 'state' => $state]);
        $res->assertStatus(200);

        // ST/B/S5/P3 (bulkhead)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'ST/B/S5/P3', 'state' => $state]);
        $res->assertStatus(200);
    }

    public function test_10_osi_and_ssr_commands(): void
    {
        $state = $this->getInitialState();

        // OS EK PAX IS VIP
        $res = $this->postJson('/api/terminal/execute', ['command' => 'OS EK PAX IS VIP', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertCount(1, $res->json('updatedState.activePnr.specialServiceRequests'));

        // OS YY PAX IS VIP
        $res = $this->postJson('/api/terminal/execute', ['command' => 'OS YY PAX IS VIP', 'state' => $state]);
        $res->assertStatus(200);

        // SR VGML
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SR VGML', 'state' => $state]);
        $res->assertStatus(200);

        // SR VGML LH/P2
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SR VGML LH/P2', 'state' => $state]);
        $res->assertStatus(200);

        // SR VGML LH/S4
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SR VGML LH/S4', 'state' => $state]);
        $res->assertStatus(200);

        // Passport APIS: Example 1, 2, 3, 4
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SR PSPT EKHK1-A123456-PK-04FEB67-MINHAS/ZAFARMR-M-H/P1', 'state' => $state]);
        $res->assertStatus(200);
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SR PSPT EKHK1-A123456-PK-04FEB67-MINHAS/AMRS-F-H/P2', 'state' => $state]);
        $res->assertStatus(200);
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SR PSPT EKHK1-A123456-PK-04FEB67-MINHAS/TALAL-MI/P2', 'state' => $state]);
        $res->assertStatus(200);
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SR PSPT EKHK1-A123456-PK-04FEB67-MINHAS/HALA-FI/P2', 'state' => $state]);
        $res->assertStatus(200);

        // Domestic Airlines TKNA: SROTHS GA- TKNA 126-4401123456
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SROTHS GA- TKNA 126-4401123456', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertTrue(str_contains(implode(' ', $res->json('outputLines')), 'TKNA'));
    }

    public function test_11_cancellation_commands(): void
    {
        $state = $this->getInitialState();
        $state['activePnr']['segments'] = [
            ['segmentNumber' => 1, 'airlineCode' => 'QR', 'flightNumber' => '001', 'bookingClass' => 'Y', 'departureDate' => '15MAR', 'departureAirport' => 'DOH', 'arrivalAirport' => 'LHR', 'seats' => 1, 'status' => 'HK'],
            ['segmentNumber' => 2, 'airlineCode' => 'QR', 'flightNumber' => '002', 'bookingClass' => 'Y', 'departureDate' => '16MAR', 'departureAirport' => 'LHR', 'arrivalAirport' => 'DOH', 'seats' => 1, 'status' => 'HK'],
            ['segmentNumber' => 3, 'airlineCode' => 'QR', 'flightNumber' => '003', 'bookingClass' => 'Y', 'departureDate' => '17MAR', 'departureAirport' => 'DOH', 'arrivalAirport' => 'DXB', 'seats' => 1, 'status' => 'HK'],
            ['segmentNumber' => 4, 'airlineCode' => 'QR', 'flightNumber' => '004', 'bookingClass' => 'Y', 'departureDate' => '18MAR', 'departureAirport' => 'DXB', 'arrivalAirport' => 'DOH', 'seats' => 1, 'status' => 'HK'],
        ];

        // XE4 (cancel segment 4)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'XE4', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertCount(3, $res->json('updatedState.activePnr.segments'));
        $state = $res->json('updatedState');

        // XI (cancel itinerary)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'XI', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertCount(0, $res->json('updatedState.activePnr.segments'));
    }

    public function test_12_change_booking_class_and_date_sb(): void
    {
        $state = $this->getInitialState();
        $state['activePnr']['segments'] = [
            ['segmentNumber' => 1, 'airlineCode' => 'QR', 'flightNumber' => '001', 'bookingClass' => 'B', 'departureDate' => '15MAR', 'departureAirport' => 'DOH', 'arrivalAirport' => 'LHR', 'seats' => 1, 'status' => 'HK'],
            ['segmentNumber' => 2, 'airlineCode' => 'QR', 'flightNumber' => '002', 'bookingClass' => 'B', 'departureDate' => '16MAR', 'departureAirport' => 'LHR', 'arrivalAirport' => 'DOH', 'seats' => 1, 'status' => 'HK'],
            ['segmentNumber' => 3, 'airlineCode' => 'QR', 'flightNumber' => '003', 'bookingClass' => 'B', 'departureDate' => '17MAR', 'departureAirport' => 'DOH', 'arrivalAirport' => 'DXB', 'seats' => 1, 'status' => 'HK'],
            ['segmentNumber' => 4, 'airlineCode' => 'QR', 'flightNumber' => '004', 'bookingClass' => 'B', 'departureDate' => '18MAR', 'departureAirport' => 'DXB', 'arrivalAirport' => 'DOH', 'seats' => 1, 'status' => 'HK'],
        ];

        // SBJ4 (change segment 4 to J)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SBJ4', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('J', $res->json('updatedState.activePnr.segments.3.bookingClass'));
        $state = $res->json('updatedState');

        // SB30MAR3 (change date for segment 3)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SB30MAR3', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('30MAR', $res->json('updatedState.activePnr.segments.2.departureDate'));
        $state = $res->json('updatedState');

        // SBJ (change all segments to J)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SBJ', 'state' => $state]);
        $res->assertStatus(200);
        foreach ($res->json('updatedState.activePnr.segments') as $seg) {
            $this->assertEquals('J', $seg['bookingClass']);
        }
    }

    public function test_13_history_remarks_print_and_option(): void
    {
        $state = $this->getInitialState();
        $state['activePnr']['recordLocator'] = 'TST123';
        $state['activePnr']['segments'] = [
            ['segmentNumber' => 4, 'airlineCode' => 'QR', 'flightNumber' => '004', 'bookingClass' => 'B', 'departureDate' => '18MAR', 'departureAirport' => 'DXB', 'arrivalAirport' => 'DOH', 'seats' => 1, 'status' => 'HK'],
        ];

        // RH (General history)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RH', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertTrue(str_contains(implode(' ', $res->json('outputLines')), 'PNR HISTORY'));

        // RHS4 (Segment 4 history)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RHS4', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertTrue(str_contains(implode(' ', $res->json('outputLines')), 'SEGMENT 4'));

        // RLH (Airline locator history)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RLH', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertTrue(str_contains(implode(' ', $res->json('outputLines')), 'AIRLINE RECORD LOCATOR HISTORY'));

        // RM please confirm segment 4 (general remark)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RM please confirm segment 4', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('GENERAL', $res->json('updatedState.activePnr.remarks.0.type'));

        // RC charge extra QAR400.00 (confidential remark)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RC charge extra QAR400.00', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('CONFIDENTIAL', $res->json('updatedState.activePnr.remarks.0.type'));

        // WRS/RT (screen print)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'WRS/RT', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertTrue(str_contains(implode(' ', $res->json('outputLines')), 'PRINTOUT (WRS)'));

        // WRA/RT (full print)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'WRA/RT', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertTrue(str_contains(implode(' ', $res->json('outputLines')), 'PRINTOUT (WRA)'));

        // OP12MAR/free Text (option element)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'OP12MAR/free Text', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('12MAR', $res->json('updatedState.activePnr.options.0.date'));
        $this->assertEquals('FREE TEXT', $res->json('updatedState.activePnr.options.0.text'));
    }

    public function test_14_queue_commands(): void
    {
        $student = User::where('username', 'student')->first();
        $this->actingAs($student);

        $state = $this->getInitialState();

        // QT
        $res = $this->postJson('/api/terminal/execute', ['command' => 'QT', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertTrue(str_contains(implode(' ', $res->json('outputLines')), 'QUEUE BANK SUMMARY'));

        // QS2C30D1
        $res = $this->postJson('/api/terminal/execute', ['command' => 'QS2C30D1', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertTrue(str_contains(implode(' ', $res->json('outputLines')), 'QUEUE 2 CATEGORY 30'));

        // QE30 (Place on queue 30)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'QE30', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertTrue(str_contains(implode(' ', $res->json('outputLines')), 'QUEUE 30'));

        // QI (Ignore queue)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'QI', 'state' => $state]);
        $res->assertStatus(200);

        // QN (Next item)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'QN', 'state' => $state]);
        $res->assertStatus(200);

        // QD (Delay item)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'QD', 'state' => $state]);
        $res->assertStatus(200);
    }

    public function test_15_split_pnr_lifecycle(): void
    {
        $student = User::where('username', 'student')->first();
        $this->actingAs($student);

        $state = $this->getInitialState();
        $state['activePnr']['recordLocator'] = 'PAR123';
        $state['activePnr']['passengers'] = [
            ['id' => '1.1', 'lastName' => 'DOE', 'firstName' => 'JOHN', 'title' => 'MR', 'type' => 'ADT'],
            ['id' => '2.1', 'lastName' => 'SMITH', 'firstName' => 'JANE', 'title' => 'MRS', 'type' => 'ADT'],
            ['id' => '3.1', 'lastName' => 'ALEMADI', 'firstName' => 'ALI', 'title' => 'MR', 'type' => 'ADT'],
        ];
        $state['activePnr']['segments'] = [
            ['segmentNumber' => 1, 'airlineCode' => 'QR', 'flightNumber' => '001', 'bookingClass' => 'Y', 'departureDate' => '15MAR', 'departureAirport' => 'DOH', 'arrivalAirport' => 'LHR', 'seats' => 3, 'status' => 'HK'],
        ];

        // 1. SP3
        $res = $this->postJson('/api/terminal/execute', ['command' => 'SP3', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals(3, $res->json('updatedState.activeSplit.paxIndex'));
        $state = $res->json('updatedState');

        // 2. RF P (for split)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RF P', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('NEED_EF', $res->json('updatedState.activeSplit.status'));
        $state = $res->json('updatedState');

        // 3. EF (file split PNR)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'EF', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertNotEmpty($res->json('updatedState.activePnr.splitTo'));
        $this->assertCount(2, $res->json('updatedState.activePnr.passengers'));
        $splitRloc = $res->json('updatedState.activePnr.splitTo');
        $this->assertDatabaseHas('pnrs', ['record_locator' => $splitRloc]);
        $state = $res->json('updatedState');

        // 4. RF P (for parent)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'RF P', 'state' => $state]);
        $res->assertStatus(200);
    }

    public function test_16_manual_ticket_number_fh_entries(): void
    {
        $state = $this->getInitialState();

        // FHA 157-9196789012
        $res = $this->postJson('/api/terminal/execute', ['command' => 'FHA 157-9196789012', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('157-9196789012', $res->json('updatedState.activePnr.tickets.0.ticketNumber'));
        $this->assertEquals('FHA', $res->json('updatedState.activePnr.tickets.0.method'));

        // FHM 157-2124564781
        $res = $this->postJson('/api/terminal/execute', ['command' => 'FHM 157-2124564781', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('FHM', $res->json('updatedState.activePnr.tickets.0.method'));

        // FHE 157-9196789012
        $res = $this->postJson('/api/terminal/execute', ['command' => 'FHE 157-9196789012', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('FHE', $res->json('updatedState.activePnr.tickets.0.method'));

        // FHA INF 157-9196789012
        $res = $this->postJson('/api/terminal/execute', ['command' => 'FHA INF 157-9196789012', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('INF', $res->json('updatedState.activePnr.tickets.0.passengerType'));

        // FHA PAX 157-9196789012
        $res = $this->postJson('/api/terminal/execute', ['command' => 'FHA PAX 157-9196789012', 'state' => $state]);
        $res->assertStatus(200);

        // FHA PAX 157-9196789012/P2
        $res = $this->postJson('/api/terminal/execute', ['command' => 'FHA PAX 157-9196789012/P2', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals(2, $res->json('updatedState.activePnr.tickets.0.paxIndex'));

        // FHA PAX 157-9196789012-013 (conjunction)
        $res = $this->postJson('/api/terminal/execute', ['command' => 'FHA PAX 157-9196789012-013', 'state' => $state]);
        $res->assertStatus(200);
        $this->assertEquals('157-9196789012-013', $res->json('updatedState.activePnr.tickets.0.ticketNumber'));
    }
}
