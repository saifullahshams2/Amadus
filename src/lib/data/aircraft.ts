export interface AircraftData {
  code: string;
  name: string;
  manufacturer: string;
  category: 'WIDEBODY' | 'NARROWBODY' | 'REGIONAL' | 'TURBOPROP';
}

export const AIRCRAFT_DATABASE: Record<string, AircraftData> = {
  // Airbus
  '318': { code: '318', name: 'Airbus A318', manufacturer: 'Airbus', category: 'NARROWBODY' },
  '319': { code: '319', name: 'Airbus A319', manufacturer: 'Airbus', category: 'NARROWBODY' },
  '320': { code: '320', name: 'Airbus A320-200', manufacturer: 'Airbus', category: 'NARROWBODY' },
  '32N': { code: '32N', name: 'Airbus A320neo', manufacturer: 'Airbus', category: 'NARROWBODY' },
  '321': { code: '321', name: 'Airbus A321-200', manufacturer: 'Airbus', category: 'NARROWBODY' },
  '32Q': { code: '32Q', name: 'Airbus A321neo', manufacturer: 'Airbus', category: 'NARROWBODY' },
  '32X': { code: '32X', name: 'Airbus A321XLR', manufacturer: 'Airbus', category: 'NARROWBODY' },
  '332': { code: '332', name: 'Airbus A330-200', manufacturer: 'Airbus', category: 'WIDEBODY' },
  '333': { code: '333', name: 'Airbus A330-300', manufacturer: 'Airbus', category: 'WIDEBODY' },
  '339': { code: '339', name: 'Airbus A330-900neo', manufacturer: 'Airbus', category: 'WIDEBODY' },
  '343': { code: '343', name: 'Airbus A340-300', manufacturer: 'Airbus', category: 'WIDEBODY' },
  '346': { code: '346', name: 'Airbus A340-600', manufacturer: 'Airbus', category: 'WIDEBODY' },
  '359': { code: '359', name: 'Airbus A350-900', manufacturer: 'Airbus', category: 'WIDEBODY' },
  '35K': { code: '35K', name: 'Airbus A350-1000', manufacturer: 'Airbus', category: 'WIDEBODY' },
  '388': { code: '388', name: 'Airbus A380-800', manufacturer: 'Airbus', category: 'WIDEBODY' },
  '221': { code: '221', name: 'Airbus A220-100', manufacturer: 'Airbus', category: 'NARROWBODY' },
  '223': { code: '223', name: 'Airbus A220-300', manufacturer: 'Airbus', category: 'NARROWBODY' },

  // Boeing
  '737': { code: '737', name: 'Boeing 737-700', manufacturer: 'Boeing', category: 'NARROWBODY' },
  '738': { code: '738', name: 'Boeing 737-800', manufacturer: 'Boeing', category: 'NARROWBODY' },
  '739': { code: '739', name: 'Boeing 737-900ER', manufacturer: 'Boeing', category: 'NARROWBODY' },
  '7M8': { code: '7M8', name: 'Boeing 737 MAX 8', manufacturer: 'Boeing', category: 'NARROWBODY' },
  '7M9': { code: '7M9', name: 'Boeing 737 MAX 9', manufacturer: 'Boeing', category: 'NARROWBODY' },
  '752': { code: '752', name: 'Boeing 757-200', manufacturer: 'Boeing', category: 'NARROWBODY' },
  '763': { code: '763', name: 'Boeing 767-300ER', manufacturer: 'Boeing', category: 'WIDEBODY' },
  '772': { code: '772', name: 'Boeing 777-200ER/LR', manufacturer: 'Boeing', category: 'WIDEBODY' },
  '773': { code: '773', name: 'Boeing 777-300', manufacturer: 'Boeing', category: 'WIDEBODY' },
  '77W': { code: '77W', name: 'Boeing 777-300ER', manufacturer: 'Boeing', category: 'WIDEBODY' },
  '779': { code: '779', name: 'Boeing 777-9', manufacturer: 'Boeing', category: 'WIDEBODY' },
  '788': { code: '788', name: 'Boeing 787-8 Dreamliner', manufacturer: 'Boeing', category: 'WIDEBODY' },
  '789': { code: '789', name: 'Boeing 787-9 Dreamliner', manufacturer: 'Boeing', category: 'WIDEBODY' },
  '78X': { code: '78X', name: 'Boeing 787-10 Dreamliner', manufacturer: 'Boeing', category: 'WIDEBODY' },
  '744': { code: '744', name: 'Boeing 747-400', manufacturer: 'Boeing', category: 'WIDEBODY' },
  '748': { code: '748', name: 'Boeing 747-8 Intercontinental', manufacturer: 'Boeing', category: 'WIDEBODY' },

  // Regional & Turboprop
  'E70': { code: 'E70', name: 'Embraer E170', manufacturer: 'Embraer', category: 'REGIONAL' },
  'E75': { code: 'E75', name: 'Embraer E175', manufacturer: 'Embraer', category: 'REGIONAL' },
  'E90': { code: 'E90', name: 'Embraer E190', manufacturer: 'Embraer', category: 'REGIONAL' },
  'E95': { code: 'E95', name: 'Embraer E195', manufacturer: 'Embraer', category: 'REGIONAL' },
  '295': { code: '295', name: 'Embraer E195-E2', manufacturer: 'Embraer', category: 'REGIONAL' },
  'CR7': { code: 'CR7', name: 'Bombardier CRJ-700', manufacturer: 'Bombardier', category: 'REGIONAL' },
  'CR9': { code: 'CR9', name: 'Bombardier CRJ-900', manufacturer: 'Bombardier', category: 'REGIONAL' },
  'CRK': { code: 'CRK', name: 'Bombardier CRJ-1000', manufacturer: 'Bombardier', category: 'REGIONAL' },
  'AT7': { code: 'AT7', name: 'ATR 72-600', manufacturer: 'ATR', category: 'TURBOPROP' },
  'AT4': { code: 'AT4', name: 'ATR 42-600', manufacturer: 'ATR', category: 'TURBOPROP' },
  'DH4': { code: 'DH4', name: 'De Havilland Dash 8-400', manufacturer: 'De Havilland', category: 'TURBOPROP' },
};
