#!/usr/bin/env node
/**
 * Real HTTP End-to-End Test Suite against the Next.js Server
 */
const http = require('http');
const { spawn } = require('child_process');

const PORT = 3008;
const BASE_URL = `http://localhost:${PORT}`;

function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

function postJson(urlPath, data, cookie = '') {
  return new Promise((resolve, reject) => {
    const payload = JSON.stringify(data);
    const req = http.request(
      `${BASE_URL}${urlPath}`,
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Content-Length': Buffer.byteLength(payload),
          ...(cookie ? { 'Cookie': cookie } : {}),
        },
      },
      res => {
        let body = '';
        res.on('data', chunk => { body += chunk; });
        res.on('end', () => {
          let json = null;
          try { json = JSON.parse(body); } catch (e) { json = body; }
          const setCookie = res.headers['set-cookie'];
          resolve({ status: res.statusCode, data: json, setCookie });
        });
      }
    );
    req.on('error', reject);
    req.write(payload);
    req.end();
  });
}

function getJson(urlPath, cookie = '') {
  return new Promise((resolve, reject) => {
    const req = http.request(
      `${BASE_URL}${urlPath}`,
      {
        method: 'GET',
        headers: {
          ...(cookie ? { 'Cookie': cookie } : {}),
        },
      },
      res => {
        let body = '';
        res.on('data', chunk => { body += chunk; });
        res.on('end', () => {
          let json = null;
          try { json = JSON.parse(body); } catch (e) { json = body; }
          resolve({ status: res.statusCode, data: json });
        });
      }
    );
    req.on('error', reject);
    req.end();
  });
}

async function run() {
  console.log('====================================================');
  console.log('   STARTING AMADEUS GDS INTEGRATION TEST SUITE      ');
  console.log('====================================================');

  console.log(`Starting Next.js server on port ${PORT}...`);
  const server = spawn('npx', ['next', 'start', '-p', String(PORT)], {
    stdio: 'inherit',
    env: { ...process.env, PORT: String(PORT) },
  });

  // Give server 3 seconds to spin up
  await sleep(3500);

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

  try {
    // 1. Health check & Home Page
    console.log('\n[1] Testing Home Page...');
    const home = await getJson('/');
    assert(home.status === 200, 'Home page returns HTTP 200');

    // 2. Authentication: Student Login
    console.log('\n[2] Testing Authentication API...');
    const loginRes = await postJson('/api/auth/login', {
      username: 'student',
      password: 'amadeus123',
    });
    assert(loginRes.status === 200, 'Student login succeeded (HTTP 200)');
    assert(loginRes.data.user.username === 'student', 'User is student');
    assert(!!loginRes.setCookie, 'Received session cookie');
    const studentCookie = loginRes.setCookie ? loginRes.setCookie[0].split(';')[0] : '';

    // 3. IATA Decoding via Terminal API
    console.log('\n[3] Testing Terminal Cryptic Execution (DAN, DNA, DNE)...');
    let termState = {
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

    let exec = await postJson('/api/terminal/execute', { command: 'DAN LONDON', state: termState }, studentCookie);
    assert(exec.status === 200, 'DAN LONDON executed successfully');
    assert(exec.data.outputLines.some(l => l.includes('HEATHROW') || l.includes('LHR')), 'Output contains Heathrow');

    exec = await postJson('/api/terminal/execute', { command: 'DNA QR', state: termState }, studentCookie);
    assert(exec.data.outputLines.some(l => l.includes('QATAR AIRWAYS')), 'DNA QR decodes Qatar Airways');

    exec = await postJson('/api/terminal/execute', { command: 'DNE 77W', state: termState }, studentCookie);
    assert(exec.data.outputLines.some(l => l.includes('BOEING 777-300ER')), 'DNE 77W decodes Boeing 777-300ER');

    // 4. Flight Availability & Short Sell
    console.log('\n[4] Testing Availability (AN) and Short Sell (SS)...');
    exec = await postJson('/api/terminal/execute', { command: 'AN15NOVLHRJFK', state: termState }, studentCookie);
    termState = exec.data.updatedState;
    assert(exec.data.outputLines.some(l => l.includes('LHRJFK')), 'Availability returned LHR-JFK flights');

    exec = await postJson('/api/terminal/execute', { command: 'SS1Y1', state: termState }, studentCookie);
    termState = exec.data.updatedState;
    assert(termState.activePnr.segments.length === 1, 'Short sell booked 1 segment in Y class');

    // 5. Complete Mandatory PRINT Elements
    console.log('\n[5] Testing Mandatory PRINT PNR Creation...');
    exec = await postJson('/api/terminal/execute', { command: 'NM1ALEXANDER/LEO MR', state: termState }, studentCookie);
    termState = exec.data.updatedState;
    assert(termState.activePnr.passengers.length === 1, 'Passenger name added');

    exec = await postJson('/api/terminal/execute', { command: 'AP LON 44-20-7946-0912-A', state: termState }, studentCookie);
    termState = exec.data.updatedState;
    assert(termState.activePnr.contacts.length === 1, 'Contact phone added');

    exec = await postJson('/api/terminal/execute', { command: 'TKTL15NOV/LON1A0980', state: termState }, studentCookie);
    termState = exec.data.updatedState;
    assert(termState.activePnr.ticketing !== undefined, 'Ticketing element added');

    exec = await postJson('/api/terminal/execute', { command: 'RF PASSENGER', state: termState }, studentCookie);
    termState = exec.data.updatedState;
    assert(termState.activePnr.receivedFrom === 'PASSENGER', 'Received from added');

    // 6. Pricing & TST (FXP)
    console.log('\n[6] Testing Fare Pricing & TST (FXP)...');
    exec = await postJson('/api/terminal/execute', { command: 'FXP', state: termState }, studentCookie);
    termState = exec.data.updatedState;
    assert(termState.activePnr.tsts.length === 1, 'TST stored with calculated fare');

    // 7. Form of Payment & End Transaction (ET)
    console.log('\n[7] Testing Form of Payment (FP) & End Transaction (ET)...');
    exec = await postJson('/api/terminal/execute', { command: 'FP CASH', state: termState }, studentCookie);
    termState = exec.data.updatedState;

    exec = await postJson('/api/terminal/execute', { command: 'ET', state: termState }, studentCookie);
    termState = exec.data.updatedState;
    const rloc = termState.activePnr.recordLocator;
    assert(typeof rloc === 'string' && rloc.length === 6, `ET committed PNR with Record Locator: ${rloc}`);

    // 8. Electronic Ticket Issuance (TTP)
    console.log('\n[8] Testing E-Ticket Issuance (TTP) & Display (TWD)...');
    exec = await postJson('/api/terminal/execute', { command: 'TTP', state: termState }, studentCookie);
    termState = exec.data.updatedState;
    assert(termState.activePnr.tickets.length === 1, 'TTP issued e-ticket document');

    exec = await postJson('/api/terminal/execute', { command: 'TWD', state: termState }, studentCookie);
    assert(exec.data.outputLines.some(l => l.includes('ELECTRONIC TICKET RECORD')), 'TWD displays ticket coupons');

    // 9. Instructor Hub & Student PNR Audit
    console.log('\n[9] Testing Instructor Operations Hub API...');
    const instLogin = await postJson('/api/auth/login', {
      username: 'instructor',
      password: 'amadeus123',
    });
    const instructorCookie = instLogin.setCookie ? instLogin.setCookie[0].split(';')[0] : '';
    const instData = await getJson('/api/instructor/students', instructorCookie);
    assert(instData.status === 200, 'Instructor Hub API accessible');
    assert(instData.data.students.length >= 1, 'Instructor views enrolled student roster');
    assert(instData.data.pnrs.some(p => p.record_locator === rloc), `Instructor audits student PNR ${rloc}`);

    console.log('\n====================================================');
    console.log(`TEST SUITE FINISHED: ${passed} PASSED, ${failed} FAILED`);
    console.log('====================================================\n');
  } catch (err) {
    console.error('Test execution failed:', err);
    failed++;
  } finally {
    server.kill();
    process.exit(failed > 0 ? 1 : 0);
  }
}

run();
