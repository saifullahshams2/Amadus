import airportsJson from './airports_full.json';

export interface AirportData {
  iata: string;
  icao: string;
  name: string;
  city: string;
  country: string;
  lat: number;
  lon: number;
  tz: string;
}

export const AIRPORTS_DATABASE: Record<string, AirportData> = airportsJson as any;

export const METRO_CODES: Record<string, string[]> = {
  'LON': ['LHR', 'LGW', 'STN', 'LTN', 'LCY'],
  'PAR': ['CDG', 'ORY'],
  'NYC': ['JFK', 'EWR', 'LGA'],
  'CHI': ['ORD', 'MDW'],
  'WAS': ['IAD', 'DCA'],
  'TYO': ['HND', 'NRT'],
  'SEL': ['ICN', 'GMP'],
  'BJS': ['PEK', 'PKX'],
  'SHA': ['PVG', 'SHA'],
  'BKK': ['BKK', 'DMK'],
  'MIL': ['MXP', 'LIN'],
  'MOW': ['SVO', 'DME', 'VKO'],
};
