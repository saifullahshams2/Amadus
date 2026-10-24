import { PNR, Passenger, FlightSegment, ContactElement, TicketingElement, FormOfPayment } from '../../types/amadeus';

export interface PnrValidationResult {
  isValid: boolean;
  errors: string[];
}

export function createEmptyPnr(officeId: string, agentId: string): PNR {
  return {
    creationOffice: officeId,
    creationAgent: agentId,
    createdAt: new Date().toISOString(),
    passengers: [],
    segments: [],
    contacts: [],
    remarks: [],
    specialServiceRequests: [],
    tsts: [],
    tickets: [],
  };
}

export function generateRecordLocator(): string {
  const chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'; // exclude 0/O, 1/I to avoid confusion
  let result = '';
  for (let i = 0; i < 6; i++) {
    result += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  return result;
}

/**
 * Validates the mandatory 5 PRINT elements required for End Transaction (ET)
 */
export function validateMandatoryPrintElements(pnr: PNR | null): PnrValidationResult {
  if (!pnr) {
    return { isValid: false, errors: ['NO ACTIVE PNR IN WORK AREA'] };
  }

  const errors: string[] = [];

  // P - Phone / Contact
  if (pnr.contacts.length === 0) {
    errors.push('NEED CONTACT ELEMENT - ENTER AP <PHONE> OR APE <EMAIL>');
  }

  // R - Received From
  if (!pnr.receivedFrom) {
    errors.push('NEED RECEIVED FROM - ENTER RF <NAME>');
  }

  // I - Itinerary
  if (pnr.segments.length === 0) {
    errors.push('CHECK CONTINUITY - NO ITINERARY SEGMENTS BOOKED');
  }

  // N - Name
  if (pnr.passengers.length === 0) {
    errors.push('NEED NAME ELEMENT - ENTER NM1<SURNAME>/<FIRSTNAME> [TITLE]');
  }

  // T - Ticketing
  if (!pnr.ticketing) {
    errors.push('CHECK TICKETING ARRANGEMENT - ENTER TKTL<DATE> OR TKOK');
  }

  return {
    isValid: errors.length === 0,
    errors,
  };
}

/**
 * Formats a PNR into authentic Amadeus cryptic terminal output
 */
export function formatPnrDisplay(pnr: PNR | null): string[] {
  if (!pnr || (pnr.passengers.length === 0 && pnr.segments.length === 0 && pnr.contacts.length === 0)) {
    return ['NO PNR PRESENT IN WORK AREA'];
  }

  const lines: string[] = [];

  const now = new Date();
  const dateStr = `${String(now.getDate()).padStart(2, '0')}${['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'][now.getMonth()]}${String(now.getFullYear()).slice(-2)}`;
  const timeStr = `${String(now.getHours()).padStart(2, '0')}${String(now.getMinutes()).padStart(2, '0')}Z`;
  const rloc = pnr.recordLocator || '---';

  lines.push(`--- RLR ---`);
  lines.push(`RP/${pnr.creationOffice}/${pnr.creationAgent}            ${dateStr}/${timeStr}   ${rloc}`);

  // 1. Passengers
  if (pnr.passengers.length > 0) {
    pnr.passengers.forEach((pax, idx) => {
      let paxStr = `  ${idx + 1}.${pax.lastName.toUpperCase()}/${pax.firstName.toUpperCase()}`;
      if (pax.title) paxStr += ` ${pax.title.toUpperCase()}`;
      if (pax.type === 'CHD') paxStr += `(CHD/${pax.dateOfBirth || ''})`;
      if (pax.type === 'INF') paxStr += `(INF/${pax.accompaniedBy || ''})`;
      lines.push(paxStr);
    });
  }

  let elementCounter = (pnr.passengers.length > 0 ? 1 : 1);

  // 2. Flight Segments
  if (pnr.segments.length > 0) {
    pnr.segments.forEach(seg => {
      const fltNo = seg.flightNumber.padStart(3, ' ');
      const seats = seg.seats;
      const status = `${seg.status}${seats}`;
      const line = `  ${seg.segmentNumber}  ${seg.airlineCode} ${fltNo} ${seg.bookingClass} ${seg.departureDate} ${seg.departureAirport}${seg.arrivalAirport} ${status}  ${seg.departureTime} ${seg.arrivalTime}   *1A/E*`;
      lines.push(line);
      elementCounter++;
    });
  }

  // 3. Contacts
  pnr.contacts.forEach(c => {
    lines.push(`  ${elementCounter++} ${c.type === 'EMAIL' ? 'APE' : 'AP'} ${c.detail}`);
  });

  // 4. Ticketing
  if (pnr.ticketing) {
    lines.push(`  ${elementCounter++} TK ${pnr.ticketing.raw.replace(/^TK/, '')}`);
  }

  // 5. Special Service Requests (SSR / OSI)
  pnr.specialServiceRequests.forEach(sr => {
    lines.push(`  ${elementCounter++} ${sr.type} ${sr.code} ${sr.text}`);
  });

  // 6. Remarks
  pnr.remarks.forEach(rem => {
    lines.push(`  ${elementCounter++} RM ${rem}`);
  });

  // 7. Issued Tickets (FA lines)
  pnr.tickets.forEach(tkt => {
    const carrier = pnr.segments[0]?.airlineCode || '1A';
    lines.push(`  ${elementCounter++} FA PAX ${tkt.ticketNumber}/ET${carrier}/${dateStr}`);
  });

  // 8. Form of Payment
  if (pnr.fop) {
    lines.push(`  ${elementCounter++} FP ${pnr.fop.details}`);
  }

  // 9. Commission
  if (pnr.commission) {
    lines.push(`  ${elementCounter++} FM ${pnr.commission}`);
  }

  // 10. Received From
  if (pnr.receivedFrom) {
    lines.push(`  ${elementCounter++} RF ${pnr.receivedFrom}`);
  }

  return lines;
}
