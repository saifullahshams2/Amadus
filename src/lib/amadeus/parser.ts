import { WorkAreaState, PNR, FlightSegment, Passenger } from '../../types/amadeus';
import { handleDAN, handleDAC, handleDNA, handleDNE } from './decode';
import { generateSchedules } from './availability';
import { createEmptyPnr, validateMandatoryPrintElements, formatPnrDisplay, generateRecordLocator } from './pnr';
import { calculateFare, handleFQD } from './pricing';
import { issueTicketTTP, displayTicketTWD } from './ticketing';
import { getDb } from '../db';

export interface CommandExecutionResult {
  outputLines: string[];
  updatedState: WorkAreaState;
  isError?: boolean;
  commandType: string;
}

export async function executeCrypticCommand(
  rawInput: string,
  currentState: WorkAreaState,
  userId: string = 'guest'
): Promise<CommandExecutionResult> {
  const input = rawInput.trim().toUpperCase();
  const state: WorkAreaState = JSON.parse(JSON.stringify(currentState));

  // Initialize PNR if not present
  if (!state.activePnr) {
    state.activePnr = createEmptyPnr(state.officeId, state.agentId);
  }

  // 1. HELP / HE
  if (input === 'HE' || input.startsWith('HELP') || input === '?') {
    return {
      outputLines: [
        '--- AMADEUS GDS QUICK COMMAND REFERENCE ---',
        'AN<DATE><ORIG><DEST>   : NEUTRAL AVAILABILITY (E.G. AN15NOVLHRJFK)',
        'SS<SEATS><CLASS><LINE> : SELL SEAT FROM DISPLAY (E.G. SS1Y1)',
        'NM1<SURNAME>/<NAME> MR : PASSENGER NAME (E.G. NM1DOE/JOHN MR)',
        'AP <PHONE> / APE <EMAIL>: CONTACT (E.G. AP DOH 974-555-1234-A)',
        'TKTL<DATE> / TKOK      : TICKETING (E.G. TKTL15NOV/LON1A0980)',
        'RF <NAME>              : RECEIVED FROM (E.G. RF PASSENGER)',
        'FXP / FXX / FXB        : PRICE ITINERARY & STORE TST',
        'FP CASH / FP CC...     : FORM OF PAYMENT',
        'ET / ER                : END TRANSACTION / END AND REDISPLAY',
        'RT <LOCATOR> / RT      : RETRIEVE PNR',
        'TTP / TWD              : ISSUE E-TICKET / DISPLAY TICKET',
        'DAN / DNA / DNE        : DECODE AIRPORT, AIRLINE, AIRCRAFT',
        'IG / IR                : IGNORE CHANGES / IGNORE & REDISPLAY',
      ],
      updatedState: state,
      commandType: 'HELP',
    };
  }

  // 2. SIGN IN / SIGN OUT
  if (input.startsWith('JI') || input === 'SI') {
    state.isSignedIn = true;
    return {
      outputLines: [`OK SIGN-IN TO WORK AREA ${state.area} / OFFICE ${state.officeId} / AGENT ${state.agentId}`],
      updatedState: state,
      commandType: 'SIGN_IN',
    };
  }
  if (input === 'JO' || input === 'SO') {
    state.isSignedIn = false;
    return {
      outputLines: [`SIGNED OUT OF AMADEUS GDS - WORK AREA ${state.area}`],
      updatedState: state,
      commandType: 'SIGN_OUT',
    };
  }

  // 3. ENCODE & DECODE: DAN, DAC, DNA, DNE
  if (input.startsWith('DAN')) {
    const query = input.slice(3).trim();
    const res = handleDAN(query);
    return { outputLines: res.lines, updatedState: state, isError: res.isError, commandType: 'DECODE_DAN' };
  }
  if (input.startsWith('DAC')) {
    const query = input.slice(3).trim();
    const res = handleDAC(query);
    return { outputLines: res.lines, updatedState: state, isError: res.isError, commandType: 'DECODE_DAC' };
  }
  if (input.startsWith('DNA')) {
    const query = input.slice(3).trim();
    const res = handleDNA(query);
    return { outputLines: res.lines, updatedState: state, isError: res.isError, commandType: 'DECODE_DNA' };
  }
  if (input.startsWith('DNE')) {
    const query = input.slice(3).trim();
    const res = handleDNE(query);
    return { outputLines: res.lines, updatedState: state, isError: res.isError, commandType: 'DECODE_DNE' };
  }

  // 4. FLIGHT AVAILABILITY: AN, SN
  if (input.startsWith('AN') || input.startsWith('SN')) {
    const query = input.slice(2).trim();
    // Regex for: <date?><orig 3><dest 3>[/A<carrier>][/C<cabin>]
    // e.g. 15NOVLHRJFK or LHRJFK or 15NOVLONNYC/ABA
    const match = query.match(/^(\d{1,2}[A-Z]{3})?([A-Z]{3})([A-Z]{3})(?:\/A([A-Z0-9]{2}))?/);
    if (!match) {
      return {
        outputLines: ['FORMAT: AN[DATE]<ORIGIN><DESTINATION>[/A<AIRLINE>] (E.G. AN15NOVLHRJFK OR AN20DECDXBLHR/AEK)'],
        updatedState: state,
        isError: true,
        commandType: 'AVAILABILITY_ERROR',
      };
    }

    const dateStr = match[1] || '';
    const orig = match[2];
    const dest = match[3];
    const carrierFilter = match[4];

    const result = generateSchedules(orig, dest, dateStr, carrierFilter);
    if (result.isError) {
      return {
        outputLines: result.outputLines,
        updatedState: state,
        isError: true,
        commandType: 'AVAILABILITY_ERROR',
      };
    }

    state.lastAvailabilityDisplay = {
      origin: orig,
      destination: dest,
      date: dateStr,
      lines: result.schedules,
    };

    return {
      outputLines: [result.headerLine, ...result.outputLines],
      updatedState: state,
      commandType: 'AVAILABILITY',
    };
  }

  // 5. PAGING: MD (Move Down), MU (Move Up), MT (Move Top), MB (Move Bottom)
  if (input === 'MD' || input === 'MU' || input === 'MT' || input === 'MB') {
    if (!state.lastAvailabilityDisplay) {
      return {
        outputLines: ['NO PREVIOUS AVAILABILITY DISPLAY TO PAGE'],
        updatedState: state,
        isError: true,
        commandType: 'PAGING',
      };
    }
    // Re-render availability with notice
    return {
      outputLines: [
        `PAGE ${input === 'MD' ? '2 OF 2' : '1 OF 2'} (END OF DISPLAY)`,
        `NO FURTHER FLIGHTS IN THIS PERIOD`,
      ],
      updatedState: state,
      commandType: 'PAGING',
    };
  }

  // 6. SHORT SELL: SS
  // Syntax: SS<seats><class><line> e.g. SS1Y1, SS2J3, SS1M2
  if (input.startsWith('SS')) {
    const match = input.match(/^SS(\d+)([A-Z])(\d+)$/);
    if (!match) {
      return {
        outputLines: ['FORMAT: SS<SEATS><CLASS><LINE> (E.G. SS1Y1 OR SS2J3)'],
        updatedState: state,
        isError: true,
        commandType: 'SELL_SEAT_ERROR',
      };
    }

    const seats = parseInt(match[1], 10);
    const bookingClass = match[2];
    const lineNumber = parseInt(match[3], 10);

    if (!state.lastAvailabilityDisplay || !state.lastAvailabilityDisplay.lines) {
      return {
        outputLines: ['NO ACTIVE AVAILABILITY DISPLAY. CHECK FLIGHTS FIRST WITH AN...'],
        updatedState: state,
        isError: true,
        commandType: 'SELL_SEAT_ERROR',
      };
    }

    const selectedLine = state.lastAvailabilityDisplay.lines.find(l => l.lineNumber === lineNumber);
    if (!selectedLine) {
      return {
        outputLines: [`LINE NUMBER ${lineNumber} NOT FOUND IN ACTIVE AVAILABILITY DISPLAY`],
        updatedState: state,
        isError: true,
        commandType: 'SELL_SEAT_ERROR',
      };
    }

    const sched = selectedLine.schedule;
    const segmentNumber = (state.activePnr?.segments.length || 0) + 1;

    const newSegment: FlightSegment = {
      segmentNumber,
      airlineCode: sched.airlineCode,
      flightNumber: sched.flightNumber,
      bookingClass,
      departureAirport: sched.departureAirport,
      arrivalAirport: sched.arrivalAirport,
      departureDate: selectedLine.departureDate,
      departureTime: sched.departureTime,
      arrivalTime: sched.arrivalTime,
      arrivalDayOffset: sched.arrivalDayOffset,
      status: 'HK',
      seats,
      equipment: sched.equipment,
    };

    if (!state.activePnr) {
      state.activePnr = createEmptyPnr(state.officeId, state.agentId);
    }
    state.activePnr.segments.push(newSegment);

    const fltPad = sched.flightNumber.padStart(3, ' ');
    const lineResponse = ` ${segmentNumber}  ${sched.airlineCode} ${fltPad} ${bookingClass} ${selectedLine.departureDate} ${sched.departureAirport}${sched.arrivalAirport} HK${seats}  ${sched.departureTime} ${sched.arrivalTime}   *1A/E*`;

    return {
      outputLines: [lineResponse],
      updatedState: state,
      commandType: 'SELL_SEAT',
    };
  }

  // 7. PASSENGER NAME: NM
  // Syntax: NM1DOE/JOHN MR or NM2SMITH/BOB MR/ALICE MRS or child NM1...
  if (input.startsWith('NM')) {
    const rawName = input.slice(2).trim();
    // Child pattern: NM1DOE/TIMMY(CHD/05MAY15)
    // Infant pattern: NM1DOE/BABY(INF/01JAN24)
    // Standard pattern: NM1SURNAME/FIRSTNAME [TITLE]
    const chdMatch = rawName.match(/^1([A-Z]+)\/([A-Z]+)\(CHD\/(\d{1,2}[A-Z]{3}\d{2})\)$/);
    const infMatch = rawName.match(/^1([A-Z]+)\/([A-Z]+)\(INF\/(\d{1,2}[A-Z]{3}\d{2})\)$/);
    const stdMatch = rawName.match(/^(\d+)([A-Z]+)\/([A-Z]+)(?:\s+([A-Z]+))?$/);

    if (!state.activePnr) {
      state.activePnr = createEmptyPnr(state.officeId, state.agentId);
    }

    if (chdMatch) {
      const pId = `${state.activePnr.passengers.length + 1}.1`;
      state.activePnr.passengers.push({
        id: pId,
        type: 'CHD',
        lastName: chdMatch[1],
        firstName: chdMatch[2],
        dateOfBirth: chdMatch[3],
      });
      return {
        outputLines: [`  ${pId}.${chdMatch[1]}/${chdMatch[2]}(CHD/${chdMatch[3]})`],
        updatedState: state,
        commandType: 'NAME_ELEMENT',
      };
    }

    if (infMatch) {
      const pId = `${state.activePnr.passengers.length + 1}.1`;
      state.activePnr.passengers.push({
        id: pId,
        type: 'INF',
        lastName: infMatch[1],
        firstName: infMatch[2],
        dateOfBirth: infMatch[3],
        accompaniedBy: '1.1',
      });
      return {
        outputLines: [`  ${pId}.${infMatch[1]}/${infMatch[2]}(INF/${infMatch[3]})`],
        updatedState: state,
        commandType: 'NAME_ELEMENT',
      };
    }

    if (stdMatch) {
      const count = parseInt(stdMatch[1], 10);
      const lastName = stdMatch[2];
      const firstName = stdMatch[3];
      const title = stdMatch[4] || '';

      const pId = `${state.activePnr.passengers.length + 1}.1`;
      state.activePnr.passengers.push({
        id: pId,
        type: 'ADT',
        lastName,
        firstName,
        title,
      });

      return {
        outputLines: [`  ${pId}.${lastName}/${firstName} ${title}`.trim()],
        updatedState: state,
        commandType: 'NAME_ELEMENT',
      };
    }

    return {
      outputLines: [
        'INVALID NAME FORMAT. EXAMPLES:',
        '  NM1DOE/JOHN MR',
        '  NM1SMITH/EMILY MRS',
        '  NM1BROWN/TIM(CHD/15MAY16)',
      ],
      updatedState: state,
      isError: true,
      commandType: 'NAME_ELEMENT_ERROR',
    };
  }

  // 8. CONTACT / PHONE / EMAIL: AP, APE
  if (input.startsWith('APE')) {
    const email = input.slice(3).trim();
    if (!state.activePnr) state.activePnr = createEmptyPnr(state.officeId, state.agentId);
    state.activePnr.contacts.push({
      id: state.activePnr.contacts.length + 1,
      type: 'EMAIL',
      detail: email,
    });
    return {
      outputLines: [`OK APE ${email}`],
      updatedState: state,
      commandType: 'CONTACT_ELEMENT',
    };
  }
  if (input.startsWith('AP')) {
    const contact = input.slice(2).trim();
    if (!contact) {
      return {
        outputLines: ['FORMAT: AP <PHONE> (E.G. AP DOH 974-555-1234-A)'],
        updatedState: state,
        isError: true,
        commandType: 'CONTACT_ERROR',
      };
    }
    if (!state.activePnr) state.activePnr = createEmptyPnr(state.officeId, state.agentId);
    state.activePnr.contacts.push({
      id: state.activePnr.contacts.length + 1,
      type: 'PHONE',
      detail: contact,
    });
    return {
      outputLines: [`OK AP ${contact}`],
      updatedState: state,
      commandType: 'CONTACT_ELEMENT',
    };
  }

  // 9. TICKETING STATUS: TKTL, TKOK, TKXL
  if (input.startsWith('TK')) {
    if (!state.activePnr) state.activePnr = createEmptyPnr(state.officeId, state.agentId);
    if (input === 'TKOK') {
      state.activePnr.ticketing = { type: 'TKOK', raw: 'TKOK' };
      return { outputLines: ['OK TKOK'], updatedState: state, commandType: 'TICKETING_ELEMENT' };
    }
    if (input.startsWith('TKTL')) {
      const rest = input.slice(4).trim();
      state.activePnr.ticketing = { type: 'TKTL', raw: input, date: rest };
      return { outputLines: [`OK ${input}`], updatedState: state, commandType: 'TICKETING_ELEMENT' };
    }
    if (input === 'TKXL') {
      state.activePnr.ticketing = { type: 'TKXL', raw: 'TKXL' };
      return { outputLines: ['OK TKXL'], updatedState: state, commandType: 'TICKETING_ELEMENT' };
    }
  }

  // 10. RECEIVED FROM: RF
  if (input.startsWith('RF')) {
    const name = input.slice(2).trim();
    if (!name) {
      return {
        outputLines: ['FORMAT: RF <NAME> (E.G. RF PASSENGER OR RF MR SMITH)'],
        updatedState: state,
        isError: true,
        commandType: 'RF_ERROR',
      };
    }
    if (!state.activePnr) state.activePnr = createEmptyPnr(state.officeId, state.agentId);
    state.activePnr.receivedFrom = name;
    return {
      outputLines: [`RF ${name} - RETAINED`],
      updatedState: state,
      commandType: 'RECEIVED_FROM',
    };
  }

  // 11. REMARKS: RM, RC
  if (input.startsWith('RM') || input.startsWith('RC')) {
    const remark = input.slice(2).trim();
    if (!state.activePnr) state.activePnr = createEmptyPnr(state.officeId, state.agentId);
    state.activePnr.remarks.push(remark);
    return {
      outputLines: [`OK RM ${remark}`],
      updatedState: state,
      commandType: 'REMARK_ELEMENT',
    };
  }

  // 12. SPECIAL SERVICE REQUESTS: SR, SSR, OSI
  if (input.startsWith('SR') || input.startsWith('SSR')) {
    const raw = input.replace(/^SSR?/, '').trim();
    if (!state.activePnr) state.activePnr = createEmptyPnr(state.officeId, state.agentId);
    state.activePnr.specialServiceRequests.push({
      type: 'SSR',
      code: raw.slice(0, 4),
      text: raw,
    });
    return {
      outputLines: [`OK SSR ${raw} HK1`],
      updatedState: state,
      commandType: 'SSR_ELEMENT',
    };
  }
  if (input.startsWith('OSI')) {
    const raw = input.slice(3).trim();
    if (!state.activePnr) state.activePnr = createEmptyPnr(state.officeId, state.agentId);
    state.activePnr.specialServiceRequests.push({
      type: 'OSI',
      code: 'OSI',
      text: raw,
    });
    return {
      outputLines: [`OK OSI ${raw}`],
      updatedState: state,
      commandType: 'OSI_ELEMENT',
    };
  }

  // 13. FARE QUOTE DISPLAY: FQD
  if (input.startsWith('FQD')) {
    const query = input.slice(3).trim();
    const res = handleFQD(query);
    return { outputLines: res.lines, updatedState: state, isError: res.isError, commandType: 'FQD' };
  }

  // 14. PRICING & TST: FXP, FXX, FXB
  if (input === 'FXP' || input === 'FXX' || input === 'FXB') {
    if (!state.activePnr) {
      return { outputLines: ['NO ACTIVE PNR TO PRICE'], updatedState: state, isError: true, commandType: 'PRICING_ERROR' };
    }
    const storeTst = (input === 'FXP' || input === 'FXB');
    const res = calculateFare(state.activePnr, storeTst);
    if (res.isError) {
      return { outputLines: res.lines, updatedState: state, isError: true, commandType: 'PRICING_ERROR' };
    }
    if (res.tst && storeTst) {
      state.activePnr.tsts.push(res.tst);
    }
    return {
      outputLines: res.lines,
      updatedState: state,
      commandType: input,
    };
  }

  // 15. FORM OF PAYMENT: FP
  if (input.startsWith('FP')) {
    const fopText = input.slice(2).trim();
    if (!state.activePnr) state.activePnr = createEmptyPnr(state.officeId, state.agentId);
    state.activePnr.fop = {
      type: fopText.startsWith('CC') ? 'CC' : 'CASH',
      details: fopText,
    };
    return {
      outputLines: [`OK FP ${fopText}`],
      updatedState: state,
      commandType: 'FORM_OF_PAYMENT',
    };
  }

  // 16. COMMISSION: FM
  if (input.startsWith('FM')) {
    const comm = input.slice(2).trim();
    if (!state.activePnr) state.activePnr = createEmptyPnr(state.officeId, state.agentId);
    state.activePnr.commission = comm;
    return {
      outputLines: [`OK FM ${comm}`],
      updatedState: state,
      commandType: 'COMMISSION',
    };
  }

  // 17. END TRANSACTION: ET, ER
  if (input === 'ET' || input === 'ER') {
    const validation = validateMandatoryPrintElements(state.activePnr);
    if (!validation.isValid) {
      return {
        outputLines: validation.errors,
        updatedState: state,
        isError: true,
        commandType: 'ET_ERROR',
      };
    }

    // Allocate record locator if new
    if (!state.activePnr!.recordLocator) {
      state.activePnr!.recordLocator = generateRecordLocator();
    }

    const rloc = state.activePnr!.recordLocator;
    const nowIso = new Date().toISOString();

    // Commit to database
    try {
      const db = getDb();
      await db.execute(
        `INSERT INTO pnrs (record_locator, user_id, office_id, status, data_json, created_at, updated_at)
         VALUES ($1, $2, $3, $4, $5, $6, $7)
         ON CONFLICT(record_locator) DO UPDATE SET
         data_json = EXCLUDED.data_json,
         status = EXCLUDED.status,
         updated_at = EXCLUDED.updated_at;`,
        [rloc, userId, state.officeId, state.activePnr!.tickets.length > 0 ? 'TICKETED' : 'CONFIRMED', JSON.stringify(state.activePnr), nowIso, nowIso]
      );
    } catch (dbErr) {
      console.error('Failed to commit PNR to DB:', dbErr);
    }

    if (input === 'ER') {
      const lines = formatPnrDisplay(state.activePnr);
      return {
        outputLines: lines,
        updatedState: state,
        commandType: 'ER',
      };
    }

    // Standard ET response
    const firstPax = state.activePnr!.passengers[0];
    const firstSeg = state.activePnr!.segments[0];
    const etLines = [
      `--- RLR ---`,
      `1.${firstPax.lastName.toUpperCase()}/${firstPax.firstName.toUpperCase()}`,
      ` 1  ${firstSeg.airlineCode} ${firstSeg.flightNumber.padStart(3, ' ')} ${firstSeg.bookingClass} ${firstSeg.departureDate} ${firstSeg.departureAirport}${firstSeg.arrivalAirport} HK${firstSeg.seats}  ${firstSeg.departureTime} ${firstSeg.arrivalTime}`,
      `${state.officeId}/${state.agentId}  ${rloc}`,
    ];

    return {
      outputLines: etLines,
      updatedState: state,
      commandType: 'ET',
    };
  }

  // 18. IGNORE: IG, IR
  if (input === 'IG') {
    state.activePnr = createEmptyPnr(state.officeId, state.agentId);
    return {
      outputLines: ['IGNORED'],
      updatedState: state,
      commandType: 'IG',
    };
  }
  if (input === 'IR') {
    if (state.activePnr?.recordLocator) {
      // Re-fetch from DB
      try {
        const db = getDb();
        const rows = await db.query(`SELECT data_json FROM pnrs WHERE record_locator = $1`, [state.activePnr.recordLocator]);
        if (rows.length > 0) {
          state.activePnr = JSON.parse(rows[0].data_json);
          return {
            outputLines: formatPnrDisplay(state.activePnr),
            updatedState: state,
            commandType: 'IR',
          };
        }
      } catch (e) {}
    }
    state.activePnr = createEmptyPnr(state.officeId, state.agentId);
    return {
      outputLines: ['IGNORED'],
      updatedState: state,
      commandType: 'IR',
    };
  }

  // 19. RETRIEVE PNR: RT
  if (input === 'RT') {
    const lines = formatPnrDisplay(state.activePnr);
    return {
      outputLines: lines,
      updatedState: state,
      commandType: 'RT',
    };
  }
  if (input.startsWith('RT')) {
    const arg = input.slice(2).trim();
    // Record locator (6 characters) or Surname
    const db = getDb();
    if (arg.length === 6) {
      const rows = await db.query(`SELECT data_json FROM pnrs WHERE record_locator = $1`, [arg]);
      if (rows.length > 0) {
        state.activePnr = JSON.parse(rows[0].data_json);
        return {
          outputLines: formatPnrDisplay(state.activePnr),
          updatedState: state,
          commandType: 'RT_LOCATOR',
        };
      }
    }

    // Search by surname
    const allPnrs = await db.query(`SELECT data_json FROM pnrs ORDER BY updated_at DESC LIMIT 50`);
    for (const row of allPnrs) {
      const p = JSON.parse(row.data_json) as PNR;
      if (p.passengers.some(pax => pax.lastName.toUpperCase() === arg)) {
        state.activePnr = p;
        return {
          outputLines: formatPnrDisplay(state.activePnr),
          updatedState: state,
          commandType: 'RT_SURNAME',
        };
      }
    }

    return {
      outputLines: [`NO PNR FOUND MATCHING: ${arg}`],
      updatedState: state,
      isError: true,
      commandType: 'RT_NOT_FOUND',
    };
  }

  // 20. CANCEL ELEMENT / SEGMENT: XE
  if (input.startsWith('XE')) {
    const lineNum = parseInt(input.slice(2).trim(), 10);
    if (isNaN(lineNum)) {
      return {
        outputLines: ['FORMAT: XE<LINE NUMBER> (E.G. XE 1)'],
        updatedState: state,
        isError: true,
        commandType: 'XE_ERROR',
      };
    }
    if (!state.activePnr) {
      return { outputLines: ['NO ACTIVE PNR'], updatedState: state, isError: true, commandType: 'XE_ERROR' };
    }
    // Remove segment if index matches
    if (lineNum <= state.activePnr.segments.length) {
      state.activePnr.segments.splice(lineNum - 1, 1);
      // Renumber remaining segments
      state.activePnr.segments.forEach((s, idx) => { s.segmentNumber = idx + 1; });
      return {
        outputLines: [`SEGMENT ${lineNum} CANCELLED`],
        updatedState: state,
        commandType: 'XE',
      };
    }
    return {
      outputLines: [`ELEMENT ${lineNum} CANCELLED`],
      updatedState: state,
      commandType: 'XE',
    };
  }

  // 21. ISSUE TICKET: TTP
  if (input === 'TTP') {
    if (!state.activePnr) {
      return { outputLines: ['NO ACTIVE PNR TO TICKET'], updatedState: state, isError: true, commandType: 'TTP_ERROR' };
    }
    const tktRes = issueTicketTTP(state.activePnr, state.officeId);
    if (tktRes.isError) {
      return { outputLines: tktRes.lines, updatedState: state, isError: true, commandType: 'TTP_ERROR' };
    }
    if (tktRes.tickets) {
      state.activePnr.tickets.push(...tktRes.tickets);
      state.activePnr.ticketing = { type: 'TKOK', raw: 'TKOK' };

      // Update PNR in DB
      if (state.activePnr.recordLocator) {
        try {
          const db = getDb();
          await db.execute(
            `UPDATE pnrs SET status = 'TICKETED', data_json = $1, updated_at = $2 WHERE record_locator = $3`,
            [JSON.stringify(state.activePnr), new Date().toISOString(), state.activePnr.recordLocator]
          );
        } catch (e) {}
      }
    }
    return {
      outputLines: tktRes.lines,
      updatedState: state,
      commandType: 'TTP',
    };
  }

  // 22. DISPLAY TICKET: TWD
  if (input.startsWith('TWD')) {
    if (!state.activePnr) {
      return { outputLines: ['NO ACTIVE PNR FOR TICKET DISPLAY'], updatedState: state, isError: true, commandType: 'TWD_ERROR' };
    }
    const arg = input.slice(3).trim();
    const res = displayTicketTWD(state.activePnr, arg || undefined);
    return {
      outputLines: res.lines,
      updatedState: state,
      isError: res.isError,
      commandType: 'TWD',
    };
  }

  // UNRECOGNIZED COMMAND
  return {
    outputLines: [
      `INVALID ENTRY: "${input}"`,
      'TYPE "HE" OR "HELP" FOR AMADEUS COMMAND REFERENCE',
    ],
    updatedState: state,
    isError: true,
    commandType: 'UNKNOWN_COMMAND',
  };
}
