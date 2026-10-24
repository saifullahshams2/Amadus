import { PNR, StoredFareTST } from '../../types/amadeus';
import { AIRPORTS_DATABASE } from '../data/airports';
import { COUNTRIES_DATABASE } from '../data/airlines';

export interface PricingResult {
  lines: string[];
  tst?: StoredFareTST;
  isError?: boolean;
}

// Approximate base fares between regions in USD
const BASE_FARES: Record<string, number> = {
  Y: 650, // Economy Full
  B: 580,
  M: 490,
  H: 420,
  K: 360,
  L: 310,
  V: 260,
  W: 980, // Premium Economy
  E: 850,
  J: 2400, // Business
  C: 2100,
  D: 1800,
  I: 1450,
  F: 4800, // First
  A: 3900,
};

export function calculateFare(pnr: PNR, storeTst: boolean = true): PricingResult {
  if (pnr.segments.length === 0) {
    return { lines: ['NO ITINERARY TO PRICE'], isError: true };
  }

  if (pnr.passengers.length === 0) {
    return { lines: ['NEED NAME ELEMENT FOR PRICING'], isError: true };
  }

  const origCode = pnr.segments[0].departureAirport;
  const destCode = pnr.segments[0].arrivalAirport;
  const origAirport = AIRPORTS_DATABASE[origCode];
  const destAirport = AIRPORTS_DATABASE[destCode];

  let currency = 'USD';
  let currencyMultiplier = 1;

  if (origAirport) {
    const country = origAirport.country;
    if (COUNTRIES_DATABASE[country]?.currency) {
      currency = COUNTRIES_DATABASE[country].currency;
      if (currency === 'GBP') currencyMultiplier = 0.78;
      else if (currency === 'EUR') currencyMultiplier = 0.92;
      else if (currency === 'QAR') currencyMultiplier = 3.64;
      else if (currency === 'AED') currencyMultiplier = 3.67;
      else if (currency === 'SAR') currencyMultiplier = 3.75;
      else if (currency === 'INR') currencyMultiplier = 84.0;
      else if (currency === 'AUD') currencyMultiplier = 1.52;
      else if (currency === 'CAD') currencyMultiplier = 1.38;
      else if (currency === 'JPY') currencyMultiplier = 152.0;
    }
  }

  let totalBaseUsd = 0;
  for (const seg of pnr.segments) {
    const fare = BASE_FARES[seg.bookingClass] || 450;
    totalBaseUsd += fare;
  }

  const numPax = pnr.passengers.length;
  const baseFarePerPax = Math.round(totalBaseUsd * currencyMultiplier);
  const taxAirport = Math.round(45 * currencyMultiplier);
  const taxFuel = Math.round(85 * currencyMultiplier);
  const totalTaxPerPax = taxAirport + taxFuel;
  const grandTotalPerPax = baseFarePerPax + totalTaxPerPax;

  const firstPax = pnr.passengers[0];
  const tstNumber = (pnr.tsts.length || 0) + 1;
  const firstSeg = pnr.segments[0];

  const tst: StoredFareTST = {
    tstNumber,
    fareBasis: `${firstSeg.bookingClass}IF`,
    baseFare: baseFarePerPax,
    currency,
    taxes: [
      { code: 'AP', amount: taxAirport },
      { code: 'YQ', amount: taxFuel },
    ],
    total: grandTotalPerPax * numPax,
    passengerIds: pnr.passengers.map(p => p.id),
    segments: pnr.segments.map(s => s.segmentNumber),
    issuedAt: new Date().toISOString(),
  };

  const lines = [
    `--- TST 0000${tstNumber} ---`,
    `FARESTORED`,
    `1.${firstPax.lastName.toUpperCase()}/${firstPax.firstName.toUpperCase()} ${firstPax.title || ''}`,
    ` ${firstSeg.departureAirport} ${firstSeg.airlineCode} ${firstSeg.flightNumber.padStart(4, ' ')} ${firstSeg.bookingClass} ${firstSeg.departureDate} ${firstSeg.departureTime} OK ${firstSeg.bookingClass}IF`,
    ` ${firstSeg.arrivalAirport}`,
    `FARE       ${currency.padEnd(4)} ${String(baseFarePerPax.toFixed(2)).padStart(10, ' ')}`,
    `TAX        ${currency.padEnd(4)} ${String(taxAirport.toFixed(2)).padStart(8, ' ')}AP  ${currency} ${String(taxFuel.toFixed(2)).padStart(8, ' ')}YQ`,
    `TOTAL      ${currency.padEnd(4)} ${String(grandTotalPerPax.toFixed(2)).padStart(10, ' ')}`,
    numPax > 1 ? `GRAND TOTAL FOR ${numPax} PASSENGERS: ${currency} ${(grandTotalPerPax * numPax).toFixed(2)}` : '',
    storeTst ? `TST RETAINED - TICKET WITH TTP` : '',
  ].filter(Boolean);

  return {
    lines,
    tst: storeTst ? tst : undefined,
    isError: false,
  };
}

export function handleFQD(query: string): PricingResult {
  const match = query.match(/^([A-Z]{6})(?:\/D(\d{1,2}[A-Z]{3}))?(?:\/A([A-Z0-9]{2}))?/);
  if (!match) {
    return {
      lines: ['FORMAT: FQD <CITYPAIR> [/D<DATE>] [/A<CARRIER>] (E.G. FQDDOHLHR/D15NOV/AQR)'],
      isError: true,
    };
  }

  const orig = match[1].slice(0, 3);
  const dest = match[1].slice(3, 6);
  const dateStr = match[2] || '15NOV';
  const carrier = match[3] || 'BA';

  const lines = [
    `FQD ${orig}${dest}/D${dateStr}/A${carrier}                      ROE 1.000000`,
    `PUBLIC FARES FOR ${orig}-${dest} ON ${carrier}`,
    `LN FARE BASIS   OW/RT   PEN  DATES    DAYS  CUR      AMOUNT`,
    `------------------------------------------------------------`,
    `01 YIF          RT      NO   -        -     USD     1250.00`,
    `02 BIF          RT      NO   -        -     USD     1080.00`,
    `03 MIF          RT      YES  -        -     USD      890.00`,
    `04 HIF          RT      YES  -        -     USD      740.00`,
    `05 KIF          RT      YES  -        -     USD      620.00`,
    `06 LIF          RT      YES  -        -     USD      530.00`,
    `07 JFF          RT      NO   -        -     USD     3800.00`,
    `08 CFF          RT      NO   -        -     USD     3200.00`,
    `09 FFF          RT      NO   -        -     USD     7500.00`,
  ];

  return { lines, isError: false };
}
