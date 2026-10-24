import { AIRPORTS_DATABASE, METRO_CODES } from '../data/airports';
import { AIRLINES_DATABASE } from '../data/airlines';
import { FlightScheduleItem, FlightInventorySeatClass } from '../../types/amadeus';

const MONTH_MAP: Record<string, number> = {
  JAN: 0, FEB: 1, MAR: 2, APR: 3, MAY: 4, JUN: 5,
  JUL: 6, AUG: 7, SEP: 8, OCT: 9, NOV: 10, DEC: 11
};

const DAY_NAMES = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'];

export interface AvailabilityResult {
  headerLine: string;
  outputLines: string[];
  schedules: Array<{
    lineNumber: number;
    schedule: FlightScheduleItem;
    departureDate: string;
  }>;
  isError?: boolean;
}

// Hub associations for airlines to produce ultra-realistic flights
const AIRLINE_HUBS: Record<string, string[]> = {
  QR: ['DOH'],
  EK: ['DXB'],
  EY: ['AUH'],
  SV: ['RUH', 'JED'],
  BA: ['LHR', 'LGW'],
  VS: ['LHR'],
  AF: ['CDG'],
  KL: ['AMS'],
  LH: ['FRA', 'MUC'],
  LX: ['ZRH'],
  OS: ['VIE'],
  IB: ['MAD'],
  TK: ['IST'],
  SQ: ['SIN'],
  TG: ['BKK'],
  MH: ['KUL'],
  CX: ['HKG'],
  JL: ['HND', 'NRT'],
  NH: ['HND', 'NRT'],
  KE: ['ICN'],
  AI: ['DEL', 'BOM'],
  AA: ['DFW', 'MIA', 'ORD', 'JFK', 'CLT'],
  UA: ['ORD', 'IAH', 'EWR', 'SFO', 'DEN'],
  DL: ['ATL', 'JFK', 'DTW', 'MSP', 'LAX'],
  AC: ['YYZ', 'YVR', 'YUL'],
  QF: ['SYD', 'MEL'],
  NZ: ['AKL'],
  ET: ['ADD'],
  MS: ['CAI'],
  RJ: ['AMM'],
  KU: ['KWI'],
  GF: ['BAH'],
  WY: ['MCT'],
};

export function parseDateString(dateStr: string): { day: number; month: number; monthStr: string } | null {
  const match = dateStr.match(/^(\d{1,2})([A-Z]{3})$/);
  if (!match) return null;
  const day = parseInt(match[1], 10);
  const monthStr = match[2];
  if (day < 1 || day > 31 || !(monthStr in MONTH_MAP)) return null;
  return { day, month: MONTH_MAP[monthStr], monthStr };
}

export function generateSchedules(
  origin: string,
  destination: string,
  dateStr: string,
  airlineFilter?: string
): AvailabilityResult {
  const origClean = origin.toUpperCase();
  const destClean = destination.toUpperCase();

  // Resolve metro codes or exact codes
  const validOrig = AIRPORTS_DATABASE[origClean] || METRO_CODES[origClean];
  const validDest = AIRPORTS_DATABASE[destClean] || METRO_CODES[destClean];

  if (!validOrig) {
    return {
      headerLine: '',
      outputLines: [`INVALID ORIGIN CITY/AIRPORT CODE: ${origClean}`],
      schedules: [],
      isError: true,
    };
  }

  if (!validDest) {
    return {
      headerLine: '',
      outputLines: [`INVALID DESTINATION CITY/AIRPORT CODE: ${destClean}`],
      schedules: [],
      isError: true,
    };
  }

  // Parse departure date
  let day = 15;
  let monthStr = 'NOV';
  let dayName = 'FR';

  if (dateStr) {
    const parsed = parseDateString(dateStr);
    if (!parsed) {
      return {
        headerLine: '',
        outputLines: [`INVALID DATE FORMAT: ${dateStr}. EXPECTED FORMAT: DDMMM (E.G. 15NOV)`],
        schedules: [],
        isError: true,
      };
    }
    day = parsed.day;
    monthStr = parsed.monthStr;
    const year = new Date().getFullYear();
    const d = new Date(year, parsed.month, day);
    dayName = DAY_NAMES[d.getDay()];
  } else {
    const now = new Date();
    day = now.getDate();
    monthStr = Object.keys(MONTH_MAP)[now.getMonth()];
    dayName = DAY_NAMES[now.getDay()];
  }

  const formattedDate = `${String(day).padStart(2, '0')}${monthStr}`;
  const headerLine = `${formattedDate}-${dayName}  ${origClean}${destClean}                                                        `;

  // Select carriers that operate between these points or hub connections
  const carriersToUse: string[] = [];
  if (airlineFilter && AIRLINES_DATABASE[airlineFilter]) {
    carriersToUse.push(airlineFilter);
  } else {
    // Pick carriers based on hubs
    for (const [carrier, hubs] of Object.entries(AIRLINE_HUBS)) {
      if (hubs.includes(origClean) || hubs.includes(destClean)) {
        carriersToUse.push(carrier);
      }
    }
    if (carriersToUse.length === 0) {
      carriersToUse.push('BA', 'QR', 'EK', 'LH', 'AF', 'UA', 'AA', 'SQ');
    }
  }

  // Ensure we have at least 4-7 flight options
  const schedules: Array<{ lineNumber: number; schedule: FlightScheduleItem; departureDate: string }> = [];
  const outputLines: string[] = [];

  const times = [
    { dep: '0630', arr: '1000', eq: '320' },
    { dep: '0845', arr: '1215', eq: '77W' },
    { dep: '1120', arr: '1450', eq: '359' },
    { dep: '1410', arr: '1740', eq: '789' },
    { dep: '1655', arr: '2025', eq: '388' },
    { dep: '1930', arr: '2300', eq: '77W' },
    { dep: '2215', arr: '0630', eq: '35K', offset: '+1' },
  ];

  let lineNum = 1;
  const seed = (origClean.charCodeAt(0) + destClean.charCodeAt(0) + day) % 99;

  for (let i = 0; i < times.length; i++) {
    const carrier = carriersToUse[i % carriersToUse.length];
    const flightNumber = String(((seed * 37 + i * 43) % 899) + 101);
    const t = times[i];

    // Standard Amadeus class buckets
    const classes: FlightInventorySeatClass[] = [
      { classCode: 'F', availableSeats: 4, cabin: 'FIRST' },
      { classCode: 'A', availableSeats: 2, cabin: 'FIRST' },
      { classCode: 'J', availableSeats: 9, cabin: 'BUSINESS' },
      { classCode: 'C', availableSeats: 9, cabin: 'BUSINESS' },
      { classCode: 'D', availableSeats: 7, cabin: 'BUSINESS' },
      { classCode: 'I', availableSeats: 4, cabin: 'BUSINESS' },
      { classCode: 'W', availableSeats: 9, cabin: 'PREMIUM_ECONOMY' },
      { classCode: 'E', availableSeats: 6, cabin: 'PREMIUM_ECONOMY' },
      { classCode: 'Y', availableSeats: 9, cabin: 'ECONOMY' },
      { classCode: 'B', availableSeats: 9, cabin: 'ECONOMY' },
      { classCode: 'M', availableSeats: 9, cabin: 'ECONOMY' },
      { classCode: 'H', availableSeats: 7, cabin: 'ECONOMY' },
      { classCode: 'K', availableSeats: 5, cabin: 'ECONOMY' },
      { classCode: 'L', availableSeats: 4, cabin: 'ECONOMY' },
      { classCode: 'V', availableSeats: 2, cabin: 'ECONOMY' },
    ];

    const actualOrig = METRO_CODES[origClean] ? METRO_CODES[origClean][0] : origClean;
    const actualDest = METRO_CODES[destClean] ? METRO_CODES[destClean][0] : destClean;

    const scheduleItem: FlightScheduleItem = {
      airlineCode: carrier,
      flightNumber,
      departureAirport: actualOrig,
      arrivalAirport: actualDest,
      daysOfOperation: '1234567',
      departureTime: t.dep,
      arrivalTime: t.arr,
      arrivalDayOffset: t.offset,
      equipment: t.eq,
      stops: 0,
      classes,
    };

    schedules.push({
      lineNumber: lineNum,
      schedule: scheduleItem,
      departureDate: formattedDate,
    });

    // Format line in Amadeus standard representation:
    //  1   BA 117  J9 C9 D9 R9 I9 W9 E9 /LHR 4 JFK 7  0825   1125  77W 0 GS /E
    //              T9 Y9 B9 H9 K9 M9 L9
    const linePrefix = String(lineNum).padStart(2, ' ');
    const carrierFlt = `${carrier} ${flightNumber.padStart(3, ' ')}`;
    const topClasses = `J9 C9 D9 I9 W9 Y9 B9`;
    const bottomClasses = `M9 H9 K9 L9 V9 Q9 S9`;
    const route = `/${actualOrig} ${actualDest}`;
    const timesStr = `${t.dep}   ${t.arr}${t.offset ? t.offset : '  '}`;
    const tailStr = `${t.eq} 0     /E`;

    const l1 = ` ${linePrefix}   ${carrierFlt}  ${topClasses} ${route}  ${timesStr}  ${tailStr}`;
    const l2 = `             ${bottomClasses}`;

    outputLines.push(l1);
    outputLines.push(l2);

    lineNum++;
    if (lineNum > 6) break; // Amadeus standard displays 6-7 items per screen page
  }

  return {
    headerLine,
    outputLines,
    schedules,
    isError: false,
  };
}
