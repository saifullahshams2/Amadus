import airlinesJson from './airlines_full.json';

export interface AirlineData {
  iata: string;
  icao: string;
  name: string;
  country: string;
  callsign: string;
  ticketPrefix: string;
}

export const AIRLINES_DATABASE: Record<string, AirlineData> = airlinesJson as any;

export interface CountryInfo {
  code: string;
  name: string;
  currency: string;
}

export const COUNTRIES_DATABASE: Record<string, CountryInfo> = {
  QA: { code: 'QA', name: 'Qatar', currency: 'QAR' },
  AE: { code: 'AE', name: 'United Arab Emirates', currency: 'AED' },
  SA: { code: 'SA', name: 'Saudi Arabia', currency: 'SAR' },
  KW: { code: 'KW', name: 'Kuwait', currency: 'KWD' },
  BH: { code: 'BH', name: 'Bahrain', currency: 'BHD' },
  OM: { code: 'OM', name: 'Oman', currency: 'OMR' },
  GB: { code: 'GB', name: 'United Kingdom', currency: 'GBP' },
  US: { code: 'US', name: 'United States', currency: 'USD' },
  FR: { code: 'FR', name: 'France', currency: 'EUR' },
  DE: { code: 'DE', name: 'Germany', currency: 'EUR' },
  IT: { code: 'IT', name: 'Italy', currency: 'EUR' },
  ES: { code: 'ES', name: 'Spain', currency: 'EUR' },
  NL: { code: 'NL', name: 'Netherlands', currency: 'EUR' },
  CH: { code: 'CH', name: 'Switzerland', currency: 'CHF' },
  TR: { code: 'TR', name: 'Turkey', currency: 'TRY' },
  EG: { code: 'EG', name: 'Egypt', currency: 'EGP' },
  JO: { code: 'JO', name: 'Jordan', currency: 'JOD' },
  LB: { code: 'LB', name: 'Lebanon', currency: 'USD' },
  SG: { code: 'SG', name: 'Singapore', currency: 'SGD' },
  MY: { code: 'MY', name: 'Malaysia', currency: 'MYR' },
  TH: { code: 'TH', name: 'Thailand', currency: 'THB' },
  JP: { code: 'JP', name: 'Japan', currency: 'JPY' },
  KR: { code: 'KR', name: 'South Korea', currency: 'KRW' },
  CN: { code: 'CN', name: 'China', currency: 'CNY' },
  IN: { code: 'IN', name: 'India', currency: 'INR' },
  PK: { code: 'PK', name: 'Pakistan', currency: 'PKR' },
  AU: { code: 'AU', name: 'Australia', currency: 'AUD' },
  NZ: { code: 'NZ', name: 'New Zealand', currency: 'NZD' },
  CA: { code: 'CA', name: 'Canada', currency: 'CAD' },
  ZA: { code: 'ZA', name: 'South Africa', currency: 'ZAR' },
  BR: { code: 'BR', name: 'Brazil', currency: 'BRL' },
};
