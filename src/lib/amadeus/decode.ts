import { AIRPORTS_DATABASE, METRO_CODES, AirportData } from '../data/airports';
import { AIRLINES_DATABASE, AirlineData } from '../data/airlines';
import { AIRCRAFT_DATABASE, AircraftData } from '../data/aircraft';

export interface DecodeResult {
  lines: string[];
  isError?: boolean;
}

export function handleDAN(query: string): DecodeResult {
  const clean = query.trim().toUpperCase();
  if (!clean) {
    return { lines: ['DAN FORMAT: DAN <CITY OR AIRPORT NAME/CODE>'], isError: true };
  }

  // Check if it's an exact 3-letter IATA code
  if (clean.length === 3 && AIRPORTS_DATABASE[clean]) {
    const ap = AIRPORTS_DATABASE[clean];
    return {
      lines: [
        `AIRPORT/CITY DECODE FOR: ${clean}`,
        `${ap.iata}  ${ap.icao.padEnd(5)} ${ap.name.toUpperCase()}`,
        `     CITY: ${ap.city.toUpperCase()}   COUNTRY: ${ap.country.toUpperCase()}`,
        `     TIMEZONE: ${ap.tz}`,
      ]
    };
  }

  // Check if it's a metro city code
  if (clean.length === 3 && METRO_CODES[clean]) {
    const airports = METRO_CODES[clean].map(code => AIRPORTS_DATABASE[code]).filter(Boolean);
    const lines = [
      `METROPOLITAN AREA DECODE FOR: ${clean}`,
      `CITY: ${airports[0]?.city.toUpperCase() || clean} (MULTI-AIRPORT METRO)`,
      `AIRPORTS SERVING THIS AREA:`,
      ...airports.map(ap => `  ${ap.iata}  ${ap.icao.padEnd(5)} ${ap.name.toUpperCase()}`)
    ];
    return { lines };
  }

  // Search by city name or airport name
  const queryLower = clean.toLowerCase();
  const matches: AirportData[] = [];
  
  for (const code in AIRPORTS_DATABASE) {
    const ap = AIRPORTS_DATABASE[code];
    if (ap.city.toLowerCase() === queryLower || ap.name.toLowerCase().includes(queryLower) || ap.city.toLowerCase().includes(queryLower)) {
      matches.push(ap);
      if (matches.length >= 12) break; // Limit response to 12 matches like GDS screen
    }
  }

  if (matches.length === 0) {
    return { lines: [`NO MATCH FOUND FOR: ${clean}`], isError: true };
  }

  const lines = [
    `LOCATION ENCODE/DECODE FOR: ${clean}`,
    `CODE  ICAO   CITY             AIRPORT NAME`,
    `------------------------------------------------------------`,
    ...matches.map(ap => 
      `${ap.iata.padEnd(6)}${ap.icao.padEnd(7)}${ap.city.slice(0, 15).toUpperCase().padEnd(17)}${ap.name.slice(0, 28).toUpperCase()}`
    )
  ];

  return { lines };
}

export function handleDAC(query: string): DecodeResult {
  return handleDAN(query);
}

export function handleDNA(query: string): DecodeResult {
  const clean = query.trim().toUpperCase();
  if (!clean) {
    return { lines: ['DNA FORMAT: DNA <AIRLINE CODE OR NAME>'], isError: true };
  }

  // Exact 2-letter code
  if (clean.length === 2 && AIRLINES_DATABASE[clean]) {
    const al = AIRLINES_DATABASE[clean];
    return {
      lines: [
        `AIRLINE DECODE FOR: ${clean}`,
        `IATA: ${al.iata}   ICAO: ${al.icao.padEnd(4)} TICKET PREFIX: ${al.ticketPrefix}`,
        `NAME: ${al.name.toUpperCase()}`,
        `COUNTRY: ${al.country.toUpperCase() || 'UNKNOWN'}`,
        al.callsign ? `CALLSIGN: ${al.callsign.toUpperCase()}` : ''
      ].filter(Boolean)
    };
  }

  // Search by name
  const queryLower = clean.toLowerCase();
  const matches: AirlineData[] = [];

  for (const code in AIRLINES_DATABASE) {
    const al = AIRLINES_DATABASE[code];
    if (al.name.toLowerCase().includes(queryLower) || al.iata.toLowerCase() === queryLower || al.icao.toLowerCase() === queryLower) {
      matches.push(al);
      if (matches.length >= 10) break;
    }
  }

  if (matches.length === 0) {
    return { lines: [`NO AIRLINE FOUND FOR: ${clean}`], isError: true };
  }

  const lines = [
    `AIRLINE ENCODE/DECODE FOR: ${clean}`,
    `IATA  ICAO  NUM   AIRLINE NAME`,
    `------------------------------------------------------------`,
    ...matches.map(al => 
      `${al.iata.padEnd(6)}${al.icao.padEnd(6)}${al.ticketPrefix.padEnd(6)}${al.name.toUpperCase().slice(0, 40)}`
    )
  ];

  return { lines };
}

export function handleDNE(query: string): DecodeResult {
  const clean = query.trim().toUpperCase();
  if (!clean) {
    return { lines: ['DNE FORMAT: DNE <EQUIPMENT CODE> (E.G. DNE 77W)'], isError: true };
  }

  const eq = AIRCRAFT_DATABASE[clean];
  if (eq) {
    return {
      lines: [
        `EQUIPMENT DECODE FOR: ${clean}`,
        `CODE: ${eq.code}`,
        `AIRCRAFT: ${eq.name.toUpperCase()}`,
        `MANUFACTURER: ${eq.manufacturer.toUpperCase()}`,
        `CATEGORY: ${eq.category}`,
      ]
    };
  }

  // Search by name or partial
  const matches = Object.values(AIRCRAFT_DATABASE).filter(a => 
    a.name.toUpperCase().includes(clean) || a.code.includes(clean)
  );

  if (matches.length > 0) {
    return {
      lines: [
        `EQUIPMENT ENCODE/DECODE FOR: ${clean}`,
        `CODE   AIRCRAFT TYPE             CATEGORY`,
        `----------------------------------------------------`,
        ...matches.map(a => `${a.code.padEnd(7)}${a.name.padEnd(26)}${a.category}`)
      ]
    };
  }

  return { lines: [`EQUIPMENT CODE NOT FOUND: ${clean}`], isError: true };
}
