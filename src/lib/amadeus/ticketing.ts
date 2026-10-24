import { PNR, IssuedTicket } from '../../types/amadeus';
import { AIRLINES_DATABASE } from '../data/airlines';

export interface TicketingResult {
  lines: string[];
  tickets?: IssuedTicket[];
  isError?: boolean;
}

export function issueTicketTTP(pnr: PNR, officeId: string): TicketingResult {
  if (pnr.segments.length === 0) {
    return { lines: ['NEED ITINERARY TO ISSUE TICKET'], isError: true };
  }

  if (pnr.passengers.length === 0) {
    return { lines: ['NEED NAME ELEMENT TO ISSUE TICKET'], isError: true };
  }

  if (!pnr.fop) {
    return { lines: ['NEED FORM OF PAYMENT - ENTER FP CASH OR FP CC...'], isError: true };
  }

  if (pnr.tsts.length === 0) {
    return { lines: ['NO TST STORED - PRICE FIRST WITH FXP'], isError: true };
  }

  if (!pnr.receivedFrom) {
    return { lines: ['NEED RECEIVED FROM - ENTER RF <NAME>'], isError: true };
  }

  const primaryCarrier = pnr.segments[0].airlineCode;
  const airline = AIRLINES_DATABASE[primaryCarrier];
  const prefix = airline?.ticketPrefix || '157';

  const newTickets: IssuedTicket[] = [];
  const outputLines: string[] = [];

  const now = new Date();
  const dateStr = `${String(now.getDate()).padStart(2, '0')}${['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'][now.getMonth()]}${String(now.getFullYear()).slice(-2)}`;

  pnr.passengers.forEach((pax, idx) => {
    // Generate 10 random digits for e-ticket number
    const randomTicketSeq = String(Math.floor(1000000000 + Math.random() * 9000000000));
    const fullTicketNumber = `${prefix}-${randomTicketSeq}`;

    const coupons = pnr.segments.map((seg, cIdx) => ({
      couponNo: cIdx + 1,
      flight: `${seg.airlineCode} ${seg.flightNumber}`,
      from: seg.departureAirport,
      to: seg.arrivalAirport,
      date: seg.departureDate,
      class: seg.bookingClass,
      status: 'O' as const, // Open for use
    }));

    const ticket: IssuedTicket = {
      ticketNumber: fullTicketNumber,
      passengerName: `${pax.lastName}/${pax.firstName} ${pax.title || ''}`.trim(),
      pnrLocator: pnr.recordLocator || 'ACTIVE',
      issueDate: dateStr,
      officeId,
      fop: pnr.fop?.details || 'CASH',
      totalFare: pnr.tsts[0] ? `${pnr.tsts[0].currency} ${pnr.tsts[0].total.toFixed(2)}` : 'N/A',
      coupons,
    };

    newTickets.push(ticket);

    outputLines.push(
      `OK ETICKET / ${fullTicketNumber} / ${pax.lastName.toUpperCase()} ${pax.firstName.toUpperCase()}`
    );
  });

  outputLines.push(`TICKETING COMPLETED - ${newTickets.length} ETKT(S) GENERATED`);

  return {
    lines: outputLines,
    tickets: newTickets,
    isError: false,
  };
}

export function displayTicketTWD(pnr: PNR, ticketNoArg?: string): TicketingResult {
  if (pnr.tickets.length === 0) {
    return { lines: ['NO ELECTRONIC TICKETS FOUND FOR THIS PNR'], isError: true };
  }

  const ticket = ticketNoArg
    ? pnr.tickets.find(t => t.ticketNumber.includes(ticketNoArg)) || pnr.tickets[0]
    : pnr.tickets[0];

  const lines = [
    `ELECTRONIC TICKET RECORD: ${ticket.ticketNumber}`,
    `NAME: ${ticket.passengerName}               RLOC: ${ticket.pnrLocator}`,
    `ISSUED: ${ticket.issueDate}  OFFICE: ${ticket.officeId}  FOP: ${ticket.fop}`,
    `TOTAL FARE: ${ticket.totalFare}`,
    `------------------------------------------------------------`,
    `CPN FLIGHT  CLASS DATE   FROM  TO    STATUS`,
    ...ticket.coupons.map(c => 
      ` ${c.couponNo}  ${c.flight.padEnd(8)} ${c.class}   ${c.date.padEnd(6)} ${c.from}   ${c.to}   ${c.status === 'O' ? 'OPEN' : c.status}`
    ),
    `------------------------------------------------------------`,
    `VALIDATING CARRIER: ${ticket.ticketNumber.slice(0, 3)}`,
  ];

  return { lines, isError: false };
}
