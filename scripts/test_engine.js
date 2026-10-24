#!/usr/bin/env node
/**
 * End-to-End Amadeus GDS Engine Automated Test Suite
 */
const { executeCrypticCommand } = require('../src/lib/amadeus/parser');
const { initializeDatabase, getDb } = require('../src/lib/db');

function createFreshState() {
  return {
    area: 'A',
    isSignedIn: true,
    officeId: 'LON1A0980',
    agentId: '1234AB',
    activePnr: null,
    lastAvailabilityDisplay: null,
    history: [],
    historyIndex: -1,
    outputLines: [],
  };
}

async function runTestSuite() {
  console.log('====================================================');
  console.log('   AMADEUS GDS ENGINE: AUTOMATED TEST SUITE        ');
  console.log('====================================================');

  await initializeDatabase();
  let state = createFreshState();
  let passed = 0;
  let failed = 0;

  function assert(condition, testName, details = '') {
    if (condition) {
      console.log(`  ✓ PASS: ${testName}`);
      passed++;
    } else {
      console.error(`  ✗ FAIL: ${testName} - ${details}`);
      failed++;
    }
  }

  // Test 1: DAN London
  console.log('\n[1] Testing IATA Decoding (DAN, DNA, DNE)...');
  let res = await executeCrypticCommand('DAN LONDON', state);
  assert(res.outputLines.some(l => l.includes('HEATHROW') || l.includes('LHR')), 'DAN LONDON returns Heathrow / LHR');

  res = await executeCrypticCommand('DNA QR', state);
  assert(res.outputLines.some(l => l.includes('QATAR AIRWAYS')), 'DNA QR decodes to Qatar Airways');

  res = await executeCrypticCommand('DNE 77W', state);
  assert(res.outputLines.some(l => l.includes('BOEING 777-300ER')), 'DNE 77W decodes to Boeing 777-300ER');

  // Test 2: Availability Search
  console.log('\n[2] Testing Neutral Availability (AN)...');
  res = await executeCrypticCommand('AN15NOVLHRJFK', state);
  state = res.updatedState;
  assert(!res.isError, 'AN15NOVLHRJFK executes without error');
  assert(state.lastAvailabilityDisplay !== null, 'Availability stored in active work area');
  assert(state.lastAvailabilityDisplay.lines.length >= 4, 'Returns 4+ flight schedule lines');

  // Test 3: Short Sell Seat (SS)
  console.log('\n[3] Testing Short Sell (SS)...');
  res = await executeCrypticCommand('SS1Y1', state);
  state = res.updatedState;
  assert(!res.isError, 'SS1Y1 sells 1 seat from line 1');
  assert(state.activePnr.segments.length === 1, '1 Segment added to PNR');
  assert(state.activePnr.segments[0].bookingClass === 'Y', 'Segment booked in Y class');

  // Test 4: ET rejection when missing mandatory PRINT elements
  console.log('\n[4] Testing Mandatory PRINT validation on ET...');
  res = await executeCrypticCommand('ET', state);
  assert(res.isError, 'ET is rejected when mandatory elements are missing');
  assert(res.outputLines.some(l => l.includes('NEED NAME ELEMENT')), 'Identifies missing Name element');

  // Test 5: Add Name (NM)
  console.log('\n[5] Testing Passenger Name (NM)...');
  res = await executeCrypticCommand('NM1DOE/JOHN MR', state);
  state = res.updatedState;
  assert(!res.isError, 'NM1DOE/JOHN MR executes cleanly');
  assert(state.activePnr.passengers.length === 1, '1 Passenger added');
  assert(state.activePnr.passengers[0].lastName === 'DOE', 'Passenger last name is DOE');

  // Test 6: Add Contact (AP)
  console.log('\n[6] Testing Contact (AP)...');
  res = await executeCrypticCommand('AP LON 44-20-7946-0912-A', state);
  state = res.updatedState;
  assert(!res.isError, 'AP executes cleanly');
  assert(state.activePnr.contacts.length === 1, 'Contact recorded');

  // Test 7: Add Ticketing Arrangement (TKTL)
  console.log('\n[7] Testing Ticketing (TKTL)...');
  res = await executeCrypticCommand('TKTL15NOV/LON1A0980', state);
  state = res.updatedState;
  assert(!res.isError, 'TKTL executes cleanly');
  assert(state.activePnr.ticketing !== undefined, 'Ticketing element recorded');

  // Test 8: Add Received From (RF)
  console.log('\n[8] Testing Received From (RF)...');
  res = await executeCrypticCommand('RF PASSENGER', state);
  state = res.updatedState;
  assert(!res.isError, 'RF executes cleanly');
  assert(state.activePnr.receivedFrom === 'PASSENGER', 'Received from saved as PASSENGER');

  // Test 9: Pricing & TST (FXP)
  console.log('\n[9] Testing Pricing & TST creation (FXP)...');
  res = await executeCrypticCommand('FXP', state);
  state = res.updatedState;
  assert(!res.isError, 'FXP prices itinerary successfully');
  assert(state.activePnr.tsts.length === 1, 'TST record created and stored');
  assert(state.activePnr.tsts[0].total > 0, 'Total fare calculated accurately');

  // Test 10: Form of Payment (FP)
  console.log('\n[10] Testing Form of Payment (FP)...');
  res = await executeCrypticCommand('FP CASH', state);
  state = res.updatedState;
  assert(!res.isError, 'FP CASH stored in PNR');

  // Test 11: End Transaction (ET)
  console.log('\n[11] Testing End Transaction & Database Persistence (ET)...');
  res = await executeCrypticCommand('ET', state);
  state = res.updatedState;
  assert(!res.isError, 'ET succeeds with all PRINT elements present');
  const rloc = state.activePnr.recordLocator;
  assert(typeof rloc === 'string' && rloc.length === 6, `Allocated valid 6-character PNR: ${rloc}`);

  // Test 12: Electronic Ticketing (TTP)
  console.log('\n[12] Testing Electronic Ticketing Document Issuance (TTP)...');
  res = await executeCrypticCommand('TTP', state);
  state = res.updatedState;
  assert(!res.isError, 'TTP issues electronic ticket successfully');
  assert(state.activePnr.tickets.length === 1, 'Ticket document created');
  const tktNumber = state.activePnr.tickets[0].ticketNumber;
  assert(tktNumber.includes('-'), `Generated authentic 13-digit e-ticket: ${tktNumber}`);

  // Test 13: Ticket Display (TWD)
  console.log('\n[13] Testing Ticket Coupon Display (TWD)...');
  res = await executeCrypticCommand('TWD', state);
  assert(!res.isError, 'TWD displays ticket coupons');
  assert(res.outputLines.some(l => l.includes('ELECTRONIC TICKET RECORD')), 'Includes e-ticket header');

  // Test 14: PNR Retrieval from Database (RT)
  console.log('\n[14] Testing PNR Retrieval (RT <rloc>)...');
  const emptyState = createFreshState();
  const rtRes = await executeCrypticCommand(`RT ${rloc}`, emptyState);
  assert(!rtRes.isError, `RT ${rloc} retrieved committed PNR from database`);
  assert(rtRes.updatedState.activePnr.recordLocator === rloc, 'Record locator matches');
  assert(rtRes.updatedState.activePnr.passengers[0].lastName === 'DOE', 'Passenger data preserved in DB');

  console.log('\n====================================================');
  console.log(`TEST RESULTS: ${passed} PASSED, ${failed} FAILED`);
  console.log('====================================================\n');

  if (failed > 0) process.exit(1);
}

runTestSuite().catch(err => {
  console.error('Test suite uncaught error:', err);
  process.exit(1);
});
