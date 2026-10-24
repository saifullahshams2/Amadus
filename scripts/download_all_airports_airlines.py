#!/usr/bin/env python3
"""
Downloads and parses the complete global airports and airlines datasets from OpenFlights.
Generates full JSON database files and indexes for the Amadeus GDS simulator.
"""
import csv
import json
import os
import urllib.request

AIRPORTS_URL = 'https://raw.githubusercontent.com/jpatokal/openflights/master/data/airports.dat'
AIRLINES_URL = 'https://raw.githubusercontent.com/jpatokal/openflights/master/data/airlines.dat'
COUNTRIES_URL = 'https://raw.githubusercontent.com/jpatokal/openflights/master/data/countries.dat'

def run():
    os.makedirs('src/lib/data', exist_ok=True)
    
    print("Downloading global airports dataset...")
    airports = {}
    city_map = {} # city_lower -> list of airport objects
    
    with urllib.request.urlopen(AIRPORTS_URL, timeout=15) as resp:
        lines = resp.read().decode('utf-8', errors='ignore').splitlines()
        reader = csv.reader(lines)
        for row in reader:
            if len(row) < 12:
                continue
            iata = row[4].strip().upper()
            icao = row[5].strip().upper()
            name = row[1].strip()
            city = row[2].strip()
            country = row[3].strip()
            
            # Keep airports with a valid 3-letter IATA code
            if len(iata) == 3 and iata != '\\N' and iata.isalpha():
                try:
                    lat = float(row[6])
                    lon = float(row[7])
                except:
                    lat, lon = 0.0, 0.0
                tz = row[11].strip() if len(row) > 11 else 'UTC'
                if tz == '\\N':
                    tz = 'UTC'
                
                airport_obj = {
                    "iata": iata,
                    "icao": icao if icao != '\\N' else '',
                    "name": name,
                    "city": city,
                    "country": country,
                    "lat": lat,
                    "lon": lon,
                    "tz": tz
                }
                airports[iata] = airport_obj
                
                # City mapping for DAN lookups
                c_key = city.lower()
                if c_key:
                    if c_key not in city_map:
                        city_map[c_key] = []
                    city_map[c_key].append(iata)

    print(f"Loaded {len(airports)} active IATA airports worldwide.")

    print("Downloading global airlines dataset...")
    airlines = {}
    airline_name_map = {} # name_lower -> airline
    
    # Well-known ticket prefixes for major airlines
    TICKET_PREFIXES = {
        "QR": "157", "EK": "176", "EY": "607", "SV": "065", "BA": "125",
        "AF": "057", "LH": "220", "AA": "001", "UA": "016", "DL": "006",
        "SQ": "618", "CX": "160", "TK": "235", "KL": "074", "IB": "075",
        "AZ": "055", "LX": "724", "OS": "257", "SN": "082", "SK": "117",
        "AY": "105", "AC": "014", "QF": "081", "JL": "131", "NH": "205",
        "KE": "180", "OZ": "988", "CI": "297", "BR": "695", "AI": "098",
        "MS": "077", "RJ": "512", "KU": "229", "GF": "072", "WY": "910",
        "ET": "071", "KQ": "706", "SA": "083", "AT": "147", "ME": "076",
        "VN": "738", "TG": "217", "MH": "232", "GA": "126", "PR": "079",
        "LA": "045", "AV": "134", "AM": "139", "AR": "044", "CM": "230",
        "WN": "526", "B6": "279", "AS": "027", "WS": "838", "NZ": "086",
    }

    with urllib.request.urlopen(AIRLINES_URL, timeout=15) as resp:
        lines = resp.read().decode('utf-8', errors='ignore').splitlines()
        reader = csv.reader(lines)
        for row in reader:
            if len(row) < 8:
                continue
            iata = row[3].strip().upper()
            icao = row[4].strip().upper()
            name = row[1].strip()
            country = row[6].strip()
            callsign = row[5].strip()
            active = row[7].strip().upper()
            
            # Keep airlines with valid 2-character IATA
            if len(iata) == 2 and iata != '\\N' and active == 'Y':
                airline_obj = {
                    "iata": iata,
                    "icao": icao if icao != '\\N' else '',
                    "name": name,
                    "country": country if country != '\\N' else '',
                    "callsign": callsign if callsign != '\\N' else '',
                    "ticketPrefix": TICKET_PREFIXES.get(iata, f"{abs(hash(iata)) % 900 + 100}")
                }
                airlines[iata] = airline_obj
                airline_name_map[name.lower()] = iata

    print(f"Loaded {len(airlines)} active IATA commercial airlines worldwide.")

    # Save compact JSON files
    with open('src/lib/data/airports_full.json', 'w', encoding='utf-8') as f:
        json.dump(airports, f)
    
    with open('src/lib/data/airlines_full.json', 'w', encoding='utf-8') as f:
        json.dump(airlines, f)

    # Save TypeScript wrapper with instant indexing
    with open('src/lib/data/airports.ts', 'w', encoding='utf-8') as f:
        f.write("import airportsJson from './airports_full.json';\n\n")
        f.write("export interface AirportData {\n")
        f.write("  iata: string;\n")
        f.write("  icao: string;\n")
        f.write("  name: string;\n")
        f.write("  city: string;\n")
        f.write("  country: string;\n")
        f.write("  lat: number;\n")
        f.write("  lon: number;\n")
        f.write("  tz: string;\n")
        f.write("}\n\n")
        f.write("export const AIRPORTS_DATABASE: Record<string, AirportData> = airportsJson as any;\n\n")
        f.write("export const METRO_CODES: Record<string, string[]> = {\n")
        f.write("  'LON': ['LHR', 'LGW', 'STN', 'LTN', 'LCY'],\n")
        f.write("  'PAR': ['CDG', 'ORY'],\n")
        f.write("  'NYC': ['JFK', 'EWR', 'LGA'],\n")
        f.write("  'CHI': ['ORD', 'MDW'],\n")
        f.write("  'WAS': ['IAD', 'DCA'],\n")
        f.write("  'TYO': ['HND', 'NRT'],\n")
        f.write("  'SEL': ['ICN', 'GMP'],\n")
        f.write("  'BJS': ['PEK', 'PKX'],\n")
        f.write("  'SHA': ['PVG', 'SHA'],\n")
        f.write("  'BKK': ['BKK', 'DMK'],\n")
        f.write("  'MIL': ['MXP', 'LIN'],\n")
        f.write("  'MOW': ['SVO', 'DME', 'VKO'],\n")
        f.write("};\n")

    with open('src/lib/data/airlines.ts', 'w', encoding='utf-8') as f:
        f.write("import airlinesJson from './airlines_full.json';\n\n")
        f.write("export interface AirlineData {\n")
        f.write("  iata: string;\n")
        f.write("  icao: string;\n")
        f.write("  name: string;\n")
        f.write("  country: string;\n")
        f.write("  callsign: string;\n")
        f.write("  ticketPrefix: string;\n")
        f.write("}\n\n")
        f.write("export const AIRLINES_DATABASE: Record<string, AirlineData> = airlinesJson as any;\n")

    print("Successfully generated all airports and airlines databases!")

if __name__ == '__main__':
    run()
