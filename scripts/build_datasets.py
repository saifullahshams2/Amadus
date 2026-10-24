#!/usr/bin/env python3
"""
Generates comprehensive airports, airlines, and countries datasets for the Amadeus GDS simulator.
Covers all continents, major commercial hubs, regional airports, and world airlines.
"""
import json
import os

AIRPORTS = [
    # Middle East
    {"iata": "DOH", "icao": "OTHH", "name": "Hamad International Airport", "city": "Doha", "country": "QA", "countryName": "Qatar", "tz": "Asia/Qatar"},
    {"iata": "DXB", "icao": "OMDB", "name": "Dubai International Airport", "city": "Dubai", "country": "AE", "countryName": "United Arab Emirates", "tz": "Asia/Dubai"},
    {"iata": "DWC", "icao": "OMDW", "name": "Al Maktoum International Airport", "city": "Dubai", "country": "AE", "countryName": "United Arab Emirates", "tz": "Asia/Dubai"},
    {"iata": "AUH", "icao": "OMAA", "name": "Zayed International Airport", "city": "Abu Dhabi", "country": "AE", "countryName": "United Arab Emirates", "tz": "Asia/Dubai"},
    {"iata": "SHJ", "icao": "OMSJ", "name": "Sharjah International Airport", "city": "Sharjah", "country": "AE", "countryName": "United Arab Emirates", "tz": "Asia/Dubai"},
    {"iata": "RUH", "icao": "OERK", "name": "King Khalid International Airport", "city": "Riyadh", "country": "SA", "countryName": "Saudi Arabia", "tz": "Asia/Riyadh"},
    {"iata": "JED", "icao": "OEJN", "name": "King Abdulaziz International Airport", "city": "Jeddah", "country": "SA", "countryName": "Saudi Arabia", "tz": "Asia/Riyadh"},
    {"iata": "DMM", "icao": "OEDF", "name": "King Fahd International Airport", "city": "Dammam", "country": "SA", "countryName": "Saudi Arabia", "tz": "Asia/Riyadh"},
    {"iata": "MED", "icao": "OEMA", "name": "Prince Mohammad bin Abdulaziz Airport", "city": "Medina", "country": "SA", "countryName": "Saudi Arabia", "tz": "Asia/Riyadh"},
    {"iata": "KWI", "icao": "OKBK", "name": "Kuwait International Airport", "city": "Kuwait City", "country": "KW", "countryName": "Kuwait", "tz": "Asia/Kuwait"},
    {"iata": "BAH", "icao": "OBBI", "name": "Bahrain International Airport", "city": "Manama", "country": "BH", "countryName": "Bahrain", "tz": "Asia/Bahrain"},
    {"iata": "MCT", "icao": "OOMS", "name": "Muscat International Airport", "city": "Muscat", "country": "OM", "countryName": "Oman", "tz": "Asia/Muscat"},
    {"iata": "SLL", "icao": "OOSA", "name": "Salalah International Airport", "city": "Salalah", "country": "OM", "countryName": "Oman", "tz": "Asia/Muscat"},
    {"iata": "AMM", "icao": "OJAI", "name": "Queen Alia International Airport", "city": "Amman", "country": "JO", "countryName": "Jordan", "tz": "Asia/Amman"},
    {"iata": "BEY", "icao": "OLBA", "name": "Beirut-Rafic Hariri International Airport", "city": "Beirut", "country": "LB", "countryName": "Lebanon", "tz": "Asia/Beirut"},
    {"iata": "CAI", "icao": "HECA", "name": "Cairo International Airport", "city": "Cairo", "country": "EG", "countryName": "Egypt", "tz": "Africa/Cairo"},
    {"iata": "HBE", "icao": "HEBA", "name": "Borg El Arab International Airport", "city": "Alexandria", "country": "EG", "countryName": "Egypt", "tz": "Africa/Cairo"},
    {"iata": "HRG", "icao": "HESH", "name": "Hurghada International Airport", "city": "Hurghada", "country": "EG", "countryName": "Egypt", "tz": "Africa/Cairo"},
    {"iata": "SSH", "icao": "HESN", "name": "Sharm El Sheikh International Airport", "city": "Sharm El Sheikh", "country": "EG", "countryName": "Egypt", "tz": "Africa/Cairo"},
    {"iata": "BGW", "icao": "ORBI", "name": "Baghdad International Airport", "city": "Baghdad", "country": "IQ", "countryName": "Iraq", "tz": "Asia/Baghdad"},
    {"iata": "EBL", "icao": "ORER", "name": "Erbil International Airport", "city": "Erbil", "country": "IQ", "countryName": "Iraq", "tz": "Asia/Baghdad"},
    {"iata": "BSR", "icao": "ORMM", "name": "Basra International Airport", "city": "Basra", "country": "IQ", "countryName": "Iraq", "tz": "Asia/Baghdad"},
    {"iata": "IKA", "icao": "OIIE", "name": "Tehran Imam Khomeini International Airport", "city": "Tehran", "country": "IR", "countryName": "Iran", "tz": "Asia/Tehran"},

    # United Kingdom & Ireland
    {"iata": "LHR", "icao": "EGLL", "name": "Heathrow Airport", "city": "London", "country": "GB", "countryName": "United Kingdom", "tz": "Europe/London"},
    {"iata": "LGW", "icao": "EGKK", "name": "Gatwick Airport", "city": "London", "country": "GB", "countryName": "United Kingdom", "tz": "Europe/London"},
    {"iata": "STN", "icao": "EGSS", "name": "Stansted Airport", "city": "London", "country": "GB", "countryName": "United Kingdom", "tz": "Europe/London"},
    {"iata": "LTN", "icao": "EGGW", "name": "Luton Airport", "city": "London", "country": "GB", "countryName": "United Kingdom", "tz": "Europe/London"},
    {"iata": "LCY", "icao": "EGLC", "name": "London City Airport", "city": "London", "country": "GB", "countryName": "United Kingdom", "tz": "Europe/London"},
    {"iata": "MAN", "icao": "EGCC", "name": "Manchester Airport", "city": "Manchester", "country": "GB", "countryName": "United Kingdom", "tz": "Europe/London"},
    {"iata": "BHX", "icao": "EGBB", "name": "Birmingham Airport", "city": "Birmingham", "country": "GB", "countryName": "United Kingdom", "tz": "Europe/London"},
    {"iata": "EDI", "icao": "EGPH", "name": "Edinburgh Airport", "city": "Edinburgh", "country": "GB", "countryName": "United Kingdom", "tz": "Europe/London"},
    {"iata": "GLA", "icao": "EGPF", "name": "Glasgow Airport", "city": "Glasgow", "country": "GB", "countryName": "United Kingdom", "tz": "Europe/London"},
    {"iata": "BFS", "icao": "EGAA", "name": "Belfast International Airport", "city": "Belfast", "country": "GB", "countryName": "United Kingdom", "tz": "Europe/London"},
    {"iata": "NCL", "icao": "EGNT", "name": "Newcastle Airport", "city": "Newcastle", "country": "GB", "countryName": "United Kingdom", "tz": "Europe/London"},
    {"iata": "BRS", "icao": "EGGD", "name": "Bristol Airport", "city": "Bristol", "country": "GB", "countryName": "United Kingdom", "tz": "Europe/London"},
    {"iata": "DUB", "icao": "EIDW", "name": "Dublin Airport", "city": "Dublin", "country": "IE", "countryName": "Ireland", "tz": "Europe/Dublin"},
    {"iata": "ORK", "icao": "EICK", "name": "Cork Airport", "city": "Cork", "country": "IE", "countryName": "Ireland", "tz": "Europe/Dublin"},
    {"iata": "SNN", "icao": "EINN", "name": "Shannon Airport", "city": "Shannon", "country": "IE", "countryName": "Ireland", "tz": "Europe/Dublin"},

    # Europe
    {"iata": "CDG", "icao": "LFPG", "name": "Charles de Gaulle Airport", "city": "Paris", "country": "FR", "countryName": "France", "tz": "Europe/Paris"},
    {"iata": "ORY", "icao": "LFPO", "name": "Orly Airport", "city": "Paris", "country": "FR", "countryName": "France", "tz": "Europe/Paris"},
    {"iata": "NCE", "icao": "LFMN", "name": "Nice Côte d'Azur Airport", "city": "Nice", "country": "FR", "countryName": "France", "tz": "Europe/Paris"},
    {"iata": "LYS", "icao": "LFLL", "name": "Lyon-Saint Exupéry Airport", "city": "Lyon", "country": "FR", "countryName": "France", "tz": "Europe/Paris"},
    {"iata": "MRS", "icao": "LFML", "name": "Marseille Provence Airport", "city": "Marseille", "country": "FR", "countryName": "France", "tz": "Europe/Paris"},
    {"iata": "FRA", "icao": "EDDF", "name": "Frankfurt Airport", "city": "Frankfurt", "country": "DE", "countryName": "Germany", "tz": "Europe/Berlin"},
    {"iata": "MUC", "icao": "EDDM", "name": "Munich Airport", "city": "Munich", "country": "DE", "countryName": "Germany", "tz": "Europe/Berlin"},
    {"iata": "BER", "icao": "EDDB", "name": "Berlin Brandenburg Airport", "city": "Berlin", "country": "DE", "countryName": "Germany", "tz": "Europe/Berlin"},
    {"iata": "HAM", "icao": "EDDH", "name": "Hamburg Airport", "city": "Hamburg", "country": "DE", "countryName": "Germany", "tz": "Europe/Berlin"},
    {"iata": "DUS", "icao": "EDDL", "name": "Düsseldorf Airport", "city": "Düsseldorf", "country": "DE", "countryName": "Germany", "tz": "Europe/Berlin"},
    {"iata": "AMS", "icao": "EHAM", "name": "Amsterdam Airport Schiphol", "city": "Amsterdam", "country": "NL", "countryName": "Netherlands", "tz": "Europe/Amsterdam"},
    {"iata": "BRU", "icao": "EBBR", "name": "Brussels Airport", "city": "Brussels", "country": "BE", "countryName": "Belgium", "tz": "Europe/Brussels"},
    {"iata": "MAD", "icao": "LEMD", "name": "Adolfo Suárez Madrid-Barajas Airport", "city": "Madrid", "country": "ES", "countryName": "Spain", "tz": "Europe/Madrid"},
    {"iata": "BCN", "icao": "LEBL", "name": "Josep Tarradellas Barcelona-El Prat Airport", "city": "Barcelona", "country": "ES", "countryName": "Spain", "tz": "Europe/Madrid"},
    {"iata": "AGP", "icao": "LEMG", "name": "Málaga Airport", "city": "Malaga", "country": "ES", "countryName": "Spain", "tz": "Europe/Madrid"},
    {"iata": "PMI", "icao": "LESJ", "name": "Palma de Mallorca Airport", "city": "Palma de Mallorca", "country": "ES", "countryName": "Spain", "tz": "Europe/Madrid"},
    {"iata": "LIS", "icao": "LPPT", "name": "Humberto Delgado Airport", "city": "Lisbon", "country": "PT", "countryName": "Portugal", "tz": "Europe/Lisbon"},
    {"iata": "OPO", "icao": "LPPR", "name": "Francisco Sá Carneiro Airport", "city": "Porto", "country": "PT", "countryName": "Portugal", "tz": "Europe/Lisbon"},
    {"iata": "FCO", "icao": "LIRF", "name": "Leonardo da Vinci-Fiumicino Airport", "city": "Rome", "country": "IT", "countryName": "Italy", "tz": "Europe/Rome"},
    {"iata": "MXP", "icao": "LIMC", "name": "Milan Malpensa Airport", "city": "Milan", "country": "IT", "countryName": "Italy", "tz": "Europe/Rome"},
    {"iata": "LIN", "icao": "LIML", "name": "Linate Airport", "city": "Milan", "country": "IT", "countryName": "Italy", "tz": "Europe/Rome"},
    {"iata": "VCE", "icao": "LIPZ", "name": "Venice Marco Polo Airport", "city": "Venice", "country": "IT", "countryName": "Italy", "tz": "Europe/Rome"},
    {"iata": "ZRH", "icao": "LSZH", "name": "Zurich Airport", "city": "Zurich", "country": "CH", "countryName": "Switzerland", "tz": "Europe/Zurich"},
    {"iata": "GVA", "icao": "LSGG", "name": "Geneva Airport", "city": "Geneva", "country": "CH", "countryName": "Switzerland", "tz": "Europe/Zurich"},
    {"iata": "VIE", "icao": "LOWW", "name": "Vienna International Airport", "city": "Vienna", "country": "AT", "countryName": "Austria", "tz": "Europe/Vienna"},
    {"iata": "ATH", "icao": "LGAV", "name": "Athens International Airport", "city": "Athens", "country": "GR", "countryName": "Greece", "tz": "Europe/Athens"},
    {"iata": "CPH", "icao": "EKCH", "name": "Copenhagen Airport", "city": "Copenhagen", "country": "DK", "countryName": "Denmark", "tz": "Europe/Copenhagen"},
    {"iata": "ARN", "icao": "ESSA", "name": "Stockholm Arlanda Airport", "city": "Stockholm", "country": "SE", "countryName": "Sweden", "tz": "Europe/Stockholm"},
    {"iata": "OSL", "icao": "ENGM", "name": "Oslo Airport Gardermoen", "city": "Oslo", "country": "NO", "countryName": "Norway", "tz": "Europe/Oslo"},
    {"iata": "HEL", "icao": "EFHK", "name": "Helsinki-Vantaa Airport", "city": "Helsinki", "country": "FI", "countryName": "Finland", "tz": "Europe/Helsinki"},
    {"iata": "WAW", "icao": "EPWA", "name": "Warsaw Chopin Airport", "city": "Warsaw", "country": "PL", "countryName": "Poland", "tz": "Europe/Warsaw"},
    {"iata": "PRG", "icao": "LKPR", "name": "Václav Havel Airport Prague", "city": "Prague", "country": "CZ", "countryName": "Czechia", "tz": "Europe/Prague"},
    {"iata": "BUD", "icao": "LHBP", "name": "Budapest Ferenc Liszt Airport", "city": "Budapest", "country": "HU", "countryName": "Hungary", "tz": "Europe/Budapest"},
    {"iata": "OTP", "icao": "LROP", "name": "Henri Coandă International Airport", "city": "Bucharest", "country": "RO", "countryName": "Romania", "tz": "Europe/Bucharest"},
    {"iata": "IST", "icao": "LTFM", "name": "Istanbul Airport", "city": "Istanbul", "country": "TR", "countryName": "Turkey", "tz": "Europe/Istanbul"},
    {"iata": "SAW", "icao": "LTFJ", "name": "Sabiha Gökçen International Airport", "city": "Istanbul", "country": "TR", "countryName": "Turkey", "tz": "Europe/Istanbul"},
    {"iata": "AYT", "icao": "LTAI", "name": "Antalya Airport", "city": "Antalya", "country": "TR", "countryName": "Turkey", "tz": "Europe/Istanbul"},
    {"iata": "ESB", "icao": "LTAC", "name": "Ankara Esenboğa Airport", "city": "Ankara", "country": "TR", "countryName": "Turkey", "tz": "Europe/Istanbul"},

    # North America
    {"iata": "JFK", "icao": "KJFK", "name": "John F. Kennedy International Airport", "city": "New York", "country": "US", "countryName": "United States", "tz": "America/New_York"},
    {"iata": "EWR", "icao": "KEWR", "name": "Newark Liberty International Airport", "city": "New York", "country": "US", "countryName": "United States", "tz": "America/New_York"},
    {"iata": "LGA", "icao": "KLGA", "name": "LaGuardia Airport", "city": "New York", "country": "US", "countryName": "United States", "tz": "America/New_York"},
    {"iata": "BOS", "icao": "KBOS", "name": "Logan International Airport", "city": "Boston", "country": "US", "countryName": "United States", "tz": "America/New_York"},
    {"iata": "ORD", "icao": "KORD", "name": "O'Hare International Airport", "city": "Chicago", "country": "US", "countryName": "United States", "tz": "America/Chicago"},
    {"iata": "MDW", "icao": "KMDW", "name": "Midway International Airport", "city": "Chicago", "country": "US", "countryName": "United States", "tz": "America/Chicago"},
    {"iata": "LAX", "icao": "KLAX", "name": "Los Angeles International Airport", "city": "Los Angeles", "country": "US", "countryName": "United States", "tz": "America/Los_Angeles"},
    {"iata": "SFO", "icao": "KSFO", "name": "San Francisco International Airport", "city": "San Francisco", "country": "US", "countryName": "United States", "tz": "America/Los_Angeles"},
    {"iata": "MIA", "icao": "KMIA", "name": "Miami International Airport", "city": "Miami", "country": "US", "countryName": "United States", "tz": "America/New_York"},
    {"iata": "FLL", "icao": "KFLL", "name": "Fort Lauderdale-Hollywood Airport", "city": "Fort Lauderdale", "country": "US", "countryName": "United States", "tz": "America/New_York"},
    {"iata": "MCO", "icao": "KMCO", "name": "Orlando International Airport", "city": "Orlando", "country": "US", "countryName": "United States", "tz": "America/New_York"},
    {"iata": "ATL", "icao": "KATL", "name": "Hartsfield-Jackson Atlanta International", "city": "Atlanta", "country": "US", "countryName": "United States", "tz": "America/New_York"},
    {"iata": "DFW", "icao": "KDFW", "name": "Dallas/Fort Worth International Airport", "city": "Dallas", "country": "US", "countryName": "United States", "tz": "America/Chicago"},
    {"iata": "IAH", "icao": "KIAH", "name": "George Bush Intercontinental Airport", "city": "Houston", "country": "US", "countryName": "United States", "tz": "America/Chicago"},
    {"iata": "SEA", "icao": "KSEA", "name": "Seattle-Tacoma International Airport", "city": "Seattle", "country": "US", "countryName": "United States", "tz": "America/Los_Angeles"},
    {"iata": "DEN", "icao": "KDEN", "name": "Denver International Airport", "city": "Denver", "country": "US", "countryName": "United States", "tz": "America/Denver"},
    {"iata": "LAS", "icao": "KLAS", "name": "Harry Reid International Airport", "city": "Las Vegas", "country": "US", "countryName": "United States", "tz": "America/Los_Angeles"},
    {"iata": "PHX", "icao": "KPHX", "name": "Phoenix Sky Harbor Airport", "city": "Phoenix", "country": "US", "countryName": "United States", "tz": "America/Phoenix"},
    {"iata": "IAD", "icao": "KIAD", "name": "Washington Dulles International Airport", "city": "Washington", "country": "US", "countryName": "United States", "tz": "America/New_York"},
    {"iata": "DCA", "icao": "KDCA", "name": "Ronald Reagan Washington National", "city": "Washington", "country": "US", "countryName": "United States", "tz": "America/New_York"},
    {"iata": "PHL", "icao": "KPHL", "name": "Philadelphia International Airport", "city": "Philadelphia", "country": "US", "countryName": "United States", "tz": "America/New_York"},
    {"iata": "YYZ", "icao": "CYYZ", "name": "Toronto Pearson International Airport", "city": "Toronto", "country": "CA", "countryName": "Canada", "tz": "America/Toronto"},
    {"iata": "YVR", "icao": "CYVR", "name": "Vancouver International Airport", "city": "Vancouver", "country": "CA", "countryName": "Canada", "tz": "America/Vancouver"},
    {"iata": "YUL", "icao": "CYUL", "name": "Montréal-Trudeau International Airport", "city": "Montreal", "country": "CA", "countryName": "Canada", "tz": "America/Toronto"},
    {"iata": "YYC", "icao": "CYYC", "name": "Calgary International Airport", "city": "Calgary", "country": "CA", "countryName": "Canada", "tz": "America/Edmonton"},
    {"iata": "MEX", "icao": "MMMX", "name": "Mexico City International Airport", "city": "Mexico City", "country": "MX", "countryName": "Mexico", "tz": "America/Mexico_City"},
    {"iata": "CUN", "icao": "MMUN", "name": "Cancún International Airport", "city": "Cancun", "country": "MX", "countryName": "Mexico", "tz": "America/Cancun"},

    # Asia & Pacific
    {"iata": "SIN", "icao": "WSSS", "name": "Singapore Changi Airport", "city": "Singapore", "country": "SG", "countryName": "Singapore", "tz": "Asia/Singapore"},
    {"iata": "BKK", "icao": "VTBS", "name": "Suvarnabhumi Airport", "city": "Bangkok", "country": "TH", "countryName": "Thailand", "tz": "Asia/Bangkok"},
    {"iata": "DMK", "icao": "VTBD", "name": "Don Mueang International Airport", "city": "Bangkok", "country": "TH", "countryName": "Thailand", "tz": "Asia/Bangkok"},
    {"iata": "HKT", "icao": "VTSP", "name": "Phuket International Airport", "city": "Phuket", "country": "TH", "countryName": "Thailand", "tz": "Asia/Bangkok"},
    {"iata": "KUL", "icao": "WMKK", "name": "Kuala Lumpur International Airport", "city": "Kuala Lumpur", "country": "MY", "countryName": "Malaysia", "tz": "Asia/Kuala_Lumpur"},
    {"iata": "HKG", "icao": "VHHH", "name": "Hong Kong International Airport", "city": "Hong Kong", "country": "HK", "countryName": "Hong Kong", "tz": "Asia/Hong_Kong"},
    {"iata": "HND", "icao": "RJTT", "name": "Tokyo Haneda Airport", "city": "Tokyo", "country": "JP", "countryName": "Japan", "tz": "Asia/Tokyo"},
    {"iata": "NRT", "icao": "RJAA", "name": "Tokyo Narita Airport", "city": "Tokyo", "country": "JP", "countryName": "Japan", "tz": "Asia/Tokyo"},
    {"iata": "KIX", "icao": "RJBB", "name": "Kansai International Airport", "city": "Osaka", "country": "JP", "countryName": "Japan", "tz": "Asia/Tokyo"},
    {"iata": "ICN", "icao": "RKSI", "name": "Incheon International Airport", "city": "Seoul", "country": "KR", "countryName": "South Korea", "tz": "Asia/Seoul"},
    {"iata": "GMP", "icao": "RKSS", "name": "Gimpo International Airport", "city": "Seoul", "country": "KR", "countryName": "South Korea", "tz": "Asia/Seoul"},
    {"iata": "TPE", "icao": "RCTP", "name": "Taiwan Taoyuan International Airport", "city": "Taipei", "country": "TW", "countryName": "Taiwan", "tz": "Asia/Taipei"},
    {"iata": "PEK", "icao": "ZBAA", "name": "Beijing Capital International Airport", "city": "Beijing", "country": "CN", "countryName": "China", "tz": "Asia/Shanghai"},
    {"iata": "PKX", "icao": "ZBAD", "name": "Beijing Daxing International Airport", "city": "Beijing", "country": "CN", "countryName": "China", "tz": "Asia/Shanghai"},
    {"iata": "PVG", "icao": "ZSPD", "name": "Shanghai Pudong International Airport", "city": "Shanghai", "country": "CN", "countryName": "China", "tz": "Asia/Shanghai"},
    {"iata": "SHA", "icao": "ZSSS", "name": "Shanghai Hongqiao International Airport", "city": "Shanghai", "country": "CN", "countryName": "China", "tz": "Asia/Shanghai"},
    {"iata": "CAN", "icao": "ZGGG", "name": "Guangzhou Baiyun International Airport", "city": "Guangzhou", "country": "CN", "countryName": "China", "tz": "Asia/Shanghai"},
    {"iata": "SZX", "icao": "ZGSZ", "name": "Shenzhen Bao'an International Airport", "city": "Shenzhen", "country": "CN", "countryName": "China", "tz": "Asia/Shanghai"},
    {"iata": "DEL", "icao": "VIDP", "name": "Indira Gandhi International Airport", "city": "Delhi", "country": "IN", "countryName": "India", "tz": "Asia/Kolkata"},
    {"iata": "BOM", "icao": "VABB", "name": "Chhatrapati Shivaji Maharaj Airport", "city": "Mumbai", "country": "IN", "countryName": "India", "tz": "Asia/Kolkata"},
    {"iata": "BLR", "icao": "VOBL", "name": "Kempegowda International Airport", "city": "Bangalore", "country": "IN", "countryName": "India", "tz": "Asia/Kolkata"},
    {"iata": "MAA", "icao": "VOMM", "name": "Chennai International Airport", "city": "Chennai", "country": "IN", "countryName": "India", "tz": "Asia/Kolkata"},
    {"iata": "HYD", "icao": "VOHS", "name": "Rajiv Gandhi International Airport", "city": "Hyderabad", "country": "IN", "countryName": "India", "tz": "Asia/Kolkata"},
    {"iata": "CCU", "icao": "VECC", "name": "Netaji Subhash Chandra Bose Airport", "city": "Kolkata", "country": "IN", "countryName": "India", "tz": "Asia/Kolkata"},
    {"iata": "COK", "icao": "VOCI", "name": "Cochin International Airport", "city": "Kochi", "country": "IN", "countryName": "India", "tz": "Asia/Kolkata"},
    {"iata": "KHI", "icao": "OPKC", "name": "Jinnah International Airport", "city": "Karachi", "country": "PK", "countryName": "Pakistan", "tz": "Asia/Karachi"},
    {"iata": "LHE", "icao": "OPLA", "name": "Allama Iqbal International Airport", "city": "Lahore", "country": "PK", "countryName": "Pakistan", "tz": "Asia/Karachi"},
    {"iata": "ISB", "icao": "OPIS", "name": "Islamabad International Airport", "city": "Islamabad", "country": "PK", "countryName": "Pakistan", "tz": "Asia/Karachi"},
    {"iata": "DAC", "icao": "VGHS", "name": "Hazrat Shahjalal International Airport", "city": "Dhaka", "country": "BD", "countryName": "Bangladesh", "tz": "Asia/Dhaka"},
    {"iata": "CMB", "icao": "VCBI", "name": "Bandaranaike International Airport", "city": "Colombo", "country": "LK", "countryName": "Sri Lanka", "tz": "Asia/Colombo"},
    {"iata": "MLE", "icao": "VRMM", "name": "Velana International Airport", "city": "Male", "country": "MV", "countryName": "Maldives", "tz": "Indian/Maldives"},
    {"iata": "CGK", "icao": "WIII", "name": "Soekarno-Hatta International Airport", "city": "Jakarta", "country": "ID", "countryName": "Indonesia", "tz": "Asia/Jakarta"},
    {"iata": "DPS", "icao": "WADD", "name": "Ngurah Rai (Bali) International Airport", "city": "Denpasar", "country": "ID", "countryName": "Indonesia", "tz": "Asia/Makassar"},
    {"iata": "MNL", "icao": "RPLL", "name": "Ninoy Aquino International Airport", "city": "Manila", "country": "PH", "countryName": "Philippines", "tz": "Asia/Manila"},
    {"iata": "HAN", "icao": "VVNB", "name": "Noi Bai International Airport", "city": "Hanoi", "country": "VN", "countryName": "Vietnam", "tz": "Asia/Ho_Chi_Minh"},
    {"iata": "SGN", "icao": "VVTS", "name": "Tan Son Nhat International Airport", "city": "Ho Chi Minh City", "country": "VN", "countryName": "Vietnam", "tz": "Asia/Ho_Chi_Minh"},
    {"iata": "SYD", "icao": "YSSY", "name": "Sydney Kingsford Smith Airport", "city": "Sydney", "country": "AU", "countryName": "Australia", "tz": "Australia/Sydney"},
    {"iata": "MEL", "icao": "YMML", "name": "Melbourne Airport", "city": "Melbourne", "country": "AU", "countryName": "Australia", "tz": "Australia/Melbourne"},
    {"iata": "BNE", "icao": "YBBN", "name": "Brisbane Airport", "city": "Brisbane", "country": "AU", "countryName": "Australia", "tz": "Australia/Brisbane"},
    {"iata": "PER", "icao": "YPPH", "name": "Perth Airport", "city": "Perth", "country": "AU", "countryName": "Australia", "tz": "Australia/Perth"},
    {"iata": "AKL", "icao": "NZAA", "name": "Auckland Airport", "city": "Auckland", "country": "NZ", "countryName": "New Zealand", "tz": "Pacific/Auckland"},

    # Africa & South America
    {"iata": "JNB", "icao": "FAOR", "name": "O. R. Tambo International Airport", "city": "Johannesburg", "country": "ZA", "countryName": "South Africa", "tz": "Africa/Johannesburg"},
    {"iata": "CPT", "icao": "FACT", "name": "Cape Town International Airport", "city": "Cape Town", "country": "ZA", "countryName": "South Africa", "tz": "Africa/Johannesburg"},
    {"iata": "NBO", "icao": "HKJK", "name": "Jomo Kenyatta International Airport", "city": "Nairobi", "country": "KE", "countryName": "Kenya", "tz": "Africa/Nairobi"},
    {"iata": "ADD", "icao": "HAAB", "name": "Addis Ababa Bole International Airport", "city": "Addis Ababa", "country": "ET", "countryName": "Ethiopia", "tz": "Africa/Addis_Ababa"},
    {"iata": "LOS", "icao": "DNMM", "name": "Murtala Muhammed International Airport", "city": "Lagos", "country": "NG", "countryName": "Nigeria", "tz": "Africa/Lagos"},
    {"iata": "ACC", "icao": "DGAA", "name": "Kotoka International Airport", "city": "Accra", "country": "GH", "countryName": "Ghana", "tz": "Africa/Accra"},
    {"iata": "CMN", "icao": "GMMN", "name": "Mohammed V International Airport", "city": "Casablanca", "country": "MA", "countryName": "Morocco", "tz": "Africa/Casablanca"},
    {"iata": "RAK", "icao": "GMMX", "name": "Marrakesh Menara Airport", "city": "Marrakesh", "country": "MA", "countryName": "Morocco", "tz": "Africa/Casablanca"},
    {"iata": "TUN", "icao": "DTTA", "name": "Tunis-Carthage International Airport", "city": "Tunis", "country": "TN", "countryName": "Tunisia", "tz": "Africa/Tunis"},
    {"iata": "ALG", "icao": "DAAG", "name": "Houari Boumediene Airport", "city": "Algiers", "country": "DZ", "countryName": "Algeria", "tz": "Africa/Algiers"},
    {"iata": "GRU", "icao": "SBGR", "name": "São Paulo/Guarulhos International Airport", "city": "Sao Paulo", "country": "BR", "countryName": "Brazil", "tz": "America/Sao_Paulo"},
    {"iata": "GIG", "icao": "SBGL", "name": "Rio de Janeiro/Galeão International Airport", "city": "Rio de Janeiro", "country": "BR", "countryName": "Brazil", "tz": "America/Sao_Paulo"},
    {"iata": "EZE", "icao": "SAEZ", "name": "Ministro Pistarini (Ezeiza) Airport", "city": "Buenos Aires", "country": "AR", "countryName": "Argentina", "tz": "America/Argentina/Buenos_Aires"},
    {"iata": "BOG", "icao": "SKBO", "name": "El Dorado International Airport", "city": "Bogota", "country": "CO", "countryName": "Colombia", "tz": "America/Bogota"},
    {"iata": "SCL", "icao": "SCEL", "name": "Arturo Merino Benítez International Airport", "city": "Santiago", "country": "CL", "countryName": "Chile", "tz": "America/Santiago"},
    {"iata": "LIM", "icao": "SPJC", "name": "Jorge Chávez International Airport", "city": "Lima", "country": "PE", "countryName": "Peru", "tz": "America/Lima"},
]

# Multi-airport city metro codes
METRO_CODES = {
    "LON": ["LHR", "LGW", "STN", "LTN", "LCY"],
    "PAR": ["CDG", "ORY"],
    "NYC": ["JFK", "EWR", "LGA"],
    "CHI": ["ORD", "MDW"],
    "WAS": ["IAD", "DCA"],
    "TYO": ["HND", "NRT"],
    "SEL": ["ICN", "GMP"],
    "BJS": ["PEK", "PKX"],
    "SHA": ["PVG", "SHA"],
    "BKK": ["BKK", "DMK"],
    "MIL": ["MXP", "LIN"],
    "SAO": ["GRU", "CGH"],
    "RIO": ["GIG", "SDU"],
    "MOW": ["SVO", "DME", "VKO"],
}

AIRLINES = [
    # Middle East
    {"iata": "QR", "icao": "QTR", "name": "Qatar Airways", "country": "Qatar", "callsign": "QATARI", "ticketPrefix": "157"},
    {"iata": "EK", "icao": "UAE", "name": "Emirates", "country": "United Arab Emirates", "callsign": "EMIRATES", "ticketPrefix": "176"},
    {"iata": "EY", "icao": "ETD", "name": "Etihad Airways", "country": "United Arab Emirates", "callsign": "ETIHAD", "ticketPrefix": "607"},
    {"iata": "FZ", "icao": "FDB", "name": "flydubai", "country": "United Arab Emirates", "callsign": "SKYDUBAI", "ticketPrefix": "141"},
    {"iata": "G9", "icao": "ABY", "name": "Air Arabia", "country": "United Arab Emirates", "callsign": "ARABIA", "ticketPrefix": "514"},
    {"iata": "SV", "icao": "SVA", "name": "Saudia", "country": "Saudi Arabia", "callsign": "SAUDIA", "ticketPrefix": "065"},
    {"iata": "XY", "icao": "KNE", "name": "flynas", "country": "Saudi Arabia", "callsign": "NAS EXPRESS", "ticketPrefix": "593"},
    {"iata": "KU", "icao": "KAC", "name": "Kuwait Airways", "country": "Kuwait", "callsign": "KUWAITI", "ticketPrefix": "229"},
    {"iata": "J9", "icao": "JZR", "name": "Jazeera Airways", "country": "Kuwait", "callsign": "JAZEERA", "ticketPrefix": "486"},
    {"iata": "GF", "icao": "GFA", "name": "Gulf Air", "country": "Bahrain", "callsign": "GULF AIR", "ticketPrefix": "072"},
    {"iata": "WY", "icao": "OMA", "name": "Oman Air", "country": "Oman", "callsign": "OMAN AIR", "ticketPrefix": "910"},
    {"iata": "OV", "icao": "OMS", "name": "SalamAir", "country": "Oman", "callsign": "MAZOON", "ticketPrefix": "734"},
    {"iata": "RJ", "icao": "RJA", "name": "Royal Jordanian", "country": "Jordan", "callsign": "JORDANIAN", "ticketPrefix": "512"},
    {"iata": "ME", "icao": "MEA", "name": "Middle East Airlines", "country": "Lebanon", "callsign": "CEDAR JET", "ticketPrefix": "076"},
    {"iata": "MS", "icao": "MSR", "name": "EgyptAir", "country": "Egypt", "callsign": "EGYPTAIR", "ticketPrefix": "077"},
    {"iata": "IA", "icao": "IAW", "name": "Iraqi Airways", "country": "Iraq", "callsign": "IRAQI", "ticketPrefix": "073"},

    # Europe
    {"iata": "BA", "icao": "BAW", "name": "British Airways", "country": "United Kingdom", "callsign": "SPEEDBIRD", "ticketPrefix": "125"},
    {"iata": "VS", "icao": "VIR", "name": "Virgin Atlantic", "country": "United Kingdom", "callsign": "VIRGIN", "ticketPrefix": "932"},
    {"iata": "U2", "icao": "EZY", "name": "easyJet", "country": "United Kingdom", "callsign": "EASY", "ticketPrefix": "888"},
    {"iata": "FR", "icao": "RYR", "name": "Ryanair", "country": "Ireland", "callsign": "RYANAIR", "ticketPrefix": "100"},
    {"iata": "EI", "icao": "EIN", "name": "Aer Lingus", "country": "Ireland", "callsign": "SHAMROCK", "ticketPrefix": "053"},
    {"iata": "AF", "icao": "AFR", "name": "Air France", "country": "France", "callsign": "AIRFRANS", "ticketPrefix": "057"},
    {"iata": "LH", "icao": "DLH", "name": "Lufthansa", "country": "Germany", "callsign": "LUFTHANSA", "ticketPrefix": "220"},
    {"iata": "EW", "icao": "EWG", "name": "Eurowings", "country": "Germany", "callsign": "EUROWINGS", "ticketPrefix": "104"},
    {"iata": "KL", "icao": "KLM", "name": "KLM Royal Dutch Airlines", "country": "Netherlands", "callsign": "KLM", "ticketPrefix": "074"},
    {"iata": "IB", "icao": "IBE", "name": "Iberia", "country": "Spain", "callsign": "IBERIA", "ticketPrefix": "075"},
    {"iata": "VY", "icao": "VLG", "name": "Vueling", "country": "Spain", "callsign": "VUELING", "ticketPrefix": "030"},
    {"iata": "UX", "icao": "AEA", "name": "Air Europa", "country": "Spain", "callsign": "EUROPA", "ticketPrefix": "996"},
    {"iata": "TP", "icao": "TAP", "name": "TAP Air Portugal", "country": "Portugal", "callsign": "AIR PORTUGAL", "ticketPrefix": "047"},
    {"iata": "AZ", "icao": "ITY", "name": "ITA Airways", "country": "Italy", "callsign": "ITARROW", "ticketPrefix": "055"},
    {"iata": "LX", "icao": "SWR", "name": "Swiss International Air Lines", "country": "Switzerland", "callsign": "SWISS", "ticketPrefix": "724"},
    {"iata": "OS", "icao": "AUA", "name": "Austrian Airlines", "country": "Austria", "callsign": "AUSTRIAN", "ticketPrefix": "257"},
    {"iata": "SN", "icao": "BEL", "name": "Brussels Airlines", "country": "Belgium", "callsign": "BEE-LINE", "ticketPrefix": "082"},
    {"iata": "SK", "icao": "SAS", "name": "Scandinavian Airlines (SAS)", "country": "Sweden", "callsign": "SCANDINAVIAN", "ticketPrefix": "117"},
    {"iata": "AY", "icao": "FIN", "name": "Finnair", "country": "Finland", "callsign": "FINNAIR", "ticketPrefix": "105"},
    {"iata": "DY", "icao": "NOZ", "name": "Norwegian Air Shuttle", "country": "Norway", "callsign": "NOR SHUTTLE", "ticketPrefix": "328"},
    {"iata": "LO", "icao": "LOT", "name": "LOT Polish Airlines", "country": "Poland", "callsign": "POLLOT", "ticketPrefix": "080"},
    {"iata": "OK", "icao": "CSA", "name": "Czech Airlines", "country": "Czechia", "callsign": "CSA LINES", "ticketPrefix": "064"},
    {"iata": "TK", "icao": "THY", "name": "Turkish Airlines", "country": "Turkey", "callsign": "TURKISH", "ticketPrefix": "235"},
    {"iata": "PC", "icao": "PGT", "name": "Pegasus Airlines", "country": "Turkey", "callsign": "SUNTURK", "ticketPrefix": "624"},
    {"iata": "A3", "icao": "AEE", "name": "Aegean Airlines", "country": "Greece", "callsign": "AEGEAN", "ticketPrefix": "390"},
    {"iata": "W6", "icao": "WZZ", "name": "Wizz Air", "country": "Hungary", "callsign": "WIZZ AIR", "ticketPrefix": "102"},
    {"iata": "RO", "icao": "ROT", "name": "TAROM", "country": "Romania", "callsign": "TAROM", "ticketPrefix": "281"},
    {"iata": "BT", "icao": "BTI", "name": "airBaltic", "country": "Latvia", "callsign": "AIR BALTIC", "ticketPrefix": "279"},

    # Americas
    {"iata": "AA", "icao": "AAL", "name": "American Airlines", "country": "United States", "callsign": "AMERICAN", "ticketPrefix": "001"},
    {"iata": "UA", "icao": "UAL", "name": "United Airlines", "country": "United States", "callsign": "UNITED", "ticketPrefix": "016"},
    {"iata": "DL", "icao": "DAL", "name": "Delta Air Lines", "country": "United States", "callsign": "DELTA", "ticketPrefix": "006"},
    {"iata": "WN", "icao": "SWA", "name": "Southwest Airlines", "country": "United States", "callsign": "SOUTHWEST", "ticketPrefix": "526"},
    {"iata": "B6", "icao": "JBU", "name": "JetBlue Airways", "country": "United States", "callsign": "JETBLUE", "ticketPrefix": "279"},
    {"iata": "AS", "icao": "ASA", "name": "Alaska Airlines", "country": "United States", "callsign": "ALASKA", "ticketPrefix": "027"},
    {"iata": "NK", "icao": "NKS", "name": "Spirit Airlines", "country": "United States", "callsign": "SPIRIT WINGS", "ticketPrefix": "487"},
    {"iata": "F9", "icao": "FFT", "name": "Frontier Airlines", "country": "United States", "callsign": "FRONTIER FLIGHT", "ticketPrefix": "422"},
    {"iata": "HA", "icao": "HAL", "name": "Hawaiian Airlines", "country": "United States", "callsign": "HAWAIIAN", "ticketPrefix": "173"},
    {"iata": "AC", "icao": "ACA", "name": "Air Canada", "country": "Canada", "callsign": "AIR CANADA", "ticketPrefix": "014"},
    {"iata": "WS", "icao": "WJA", "name": "WestJet", "country": "Canada", "callsign": "WESTJET", "ticketPrefix": "838"},
    {"iata": "AM", "icao": "AMX", "name": "Aeroméxico", "country": "Mexico", "callsign": "AEROMEXICO", "ticketPrefix": "139"},
    {"iata": "Y4", "icao": "VOI", "name": "Volaris", "country": "Mexico", "callsign": "VOLARIS", "ticketPrefix": "036"},
    {"iata": "VB", "icao": "VIV", "name": "Viva Aerobus", "country": "Mexico", "callsign": "AEROENLACES", "ticketPrefix": "040"},
    {"iata": "LA", "icao": "LAN", "name": "LATAM Airlines Group", "country": "Chile", "callsign": "LAN", "ticketPrefix": "045"},
    {"iata": "AV", "icao": "AVA", "name": "Avianca", "country": "Colombia", "callsign": "AVIANCA", "ticketPrefix": "134"},
    {"iata": "AR", "icao": "ARG", "name": "Aerolíneas Argentinas", "country": "Argentina", "callsign": "ARGENTINA", "ticketPrefix": "044"},
    {"iata": "CM", "icao": "CMP", "name": "Copa Airlines", "country": "Panama", "callsign": "COPA", "ticketPrefix": "230"},
    {"iata": "G3", "icao": "GLO", "name": "Gol Transportes Aéreos", "country": "Brazil", "callsign": "GOL", "ticketPrefix": "127"},
    {"iata": "AD", "icao": "AZU", "name": "Azul Brazilian Airlines", "country": "Brazil", "callsign": "AZUL", "ticketPrefix": "577"},

    # Asia & Pacific
    {"iata": "SQ", "icao": "SIA", "name": "Singapore Airlines", "country": "Singapore", "callsign": "SINGAPORE", "ticketPrefix": "618"},
    {"iata": "TR", "icao": "TGW", "name": "Scoot", "country": "Singapore", "callsign": "SCOOTER", "ticketPrefix": "668"},
    {"iata": "TG", "icao": "THA", "name": "Thai Airways", "country": "Thailand", "callsign": "THAI", "ticketPrefix": "217"},
    {"iata": "MH", "icao": "MAS", "name": "Malaysia Airlines", "country": "Malaysia", "callsign": "MALAYSIAN", "ticketPrefix": "232"},
    {"iata": "AK", "icao": "AXM", "name": "AirAsia", "country": "Malaysia", "callsign": "ASIAN EXPRESS", "ticketPrefix": "807"},
    {"iata": "CX", "icao": "CPA", "name": "Cathay Pacific", "country": "Hong Kong", "callsign": "CATHAY", "ticketPrefix": "160"},
    {"iata": "JL", "icao": "JAL", "name": "Japan Airlines", "country": "Japan", "callsign": "JAPANAIR", "ticketPrefix": "131"},
    {"iata": "NH", "icao": "ANA", "name": "All Nippon Airways (ANA)", "country": "Japan", "callsign": "ALL NIPPON", "ticketPrefix": "205"},
    {"iata": "KE", "icao": "KAL", "name": "Korean Air", "country": "South Korea", "callsign": "KOREANAIR", "ticketPrefix": "180"},
    {"iata": "OZ", "icao": "AAR", "name": "Asiana Airlines", "country": "South Korea", "callsign": "ASIANA", "ticketPrefix": "988"},
    {"iata": "CI", "icao": "CAL", "name": "China Airlines", "country": "Taiwan", "callsign": "DYNASTY", "ticketPrefix": "297"},
    {"iata": "BR", "icao": "EVA", "name": "EVA Air", "country": "Taiwan", "callsign": "EVA", "ticketPrefix": "695"},
    {"iata": "CA", "icao": "CCA", "name": "Air China", "country": "China", "callsign": "AIR CHINA", "ticketPrefix": "999"},
    {"iata": "CZ", "icao": "CSN", "name": "China Southern Airlines", "country": "China", "callsign": "CHINA SOUTHERN", "ticketPrefix": "784"},
    {"iata": "MU", "icao": "CES", "name": "China Eastern Airlines", "country": "China", "callsign": "CHINA EASTERN", "ticketPrefix": "781"},
    {"iata": "HU", "icao": "CHH", "name": "Hainan Airlines", "country": "China", "callsign": "HAINAN", "ticketPrefix": "880"},
    {"iata": "AI", "icao": "AIC", "name": "Air India", "country": "India", "callsign": "AIRINDIA", "ticketPrefix": "098"},
    {"iata": "6E", "icao": "IGO", "name": "IndiGo", "country": "India", "callsign": "IFLY", "ticketPrefix": "312"},
    {"iata": "UK", "icao": "VTI", "name": "Vistara", "country": "India", "callsign": "VISTARA", "ticketPrefix": "228"},
    {"iata": "SG", "icao": "SEJ", "name": "SpiceJet", "country": "India", "callsign": "SPICEJET", "ticketPrefix": "775"},
    {"iata": "PK", "icao": "PIA", "name": "Pakistan International Airlines", "country": "Pakistan", "callsign": "PAKISTAN", "ticketPrefix": "214"},
    {"iata": "BG", "icao": "BBC", "name": "Biman Bangladesh Airlines", "country": "Bangladesh", "callsign": "BANGLADESH", "ticketPrefix": "997"},
    {"iata": "UL", "icao": "ALK", "name": "SriLankan Airlines", "country": "Sri Lanka", "callsign": "SRILANKAN", "ticketPrefix": "603"},
    {"iata": "GA", "icao": "GIA", "name": "Garuda Indonesia", "country": "Indonesia", "callsign": "INDONESIA", "ticketPrefix": "126"},
    {"iata": "PR", "icao": "PAL", "name": "Philippine Airlines", "country": "Philippines", "callsign": "PHILIPPINE", "ticketPrefix": "079"},
    {"iata": "5J", "icao": "CEB", "name": "Cebu Pacific", "country": "Philippines", "callsign": "CEBU AIR", "ticketPrefix": "203"},
    {"iata": "VN", "icao": "HVN", "name": "Vietnam Airlines", "country": "Vietnam", "callsign": "VIET NAM AIRLINES", "ticketPrefix": "738"},
    {"iata": "QF", "icao": "QFA", "name": "Qantas", "country": "Australia", "callsign": "QANTAS", "ticketPrefix": "081"},
    {"iata": "VA", "icao": "VOZ", "name": "Virgin Australia", "country": "Australia", "callsign": "VELOCITY", "ticketPrefix": "795"},
    {"iata": "JQ", "icao": "JST", "name": "Jetstar Airways", "country": "Australia", "callsign": "JETSTAR", "ticketPrefix": "041"},
    {"iata": "NZ", "icao": "ANZ", "name": "Air New Zealand", "country": "New Zealand", "callsign": "NEW ZEALAND", "ticketPrefix": "086"},

    # Africa
    {"iata": "ET", "icao": "ETH", "name": "Ethiopian Airlines", "country": "Ethiopia", "callsign": "ETHIOPIAN", "ticketPrefix": "071"},
    {"iata": "KQ", "icao": "KQA", "name": "Kenya Airways", "country": "Kenya", "callsign": "KENYA", "ticketPrefix": "706"},
    {"iata": "SA", "icao": "SAA", "name": "South African Airways", "country": "South Africa", "callsign": "SPRINGBOK", "ticketPrefix": "083"},
    {"iata": "AT", "icao": "RAM", "name": "Royal Air Maroc", "country": "Morocco", "callsign": "ROYALAIR MAROC", "ticketPrefix": "147"},
    {"iata": "TU", "icao": "TAR", "name": "Tunisair", "country": "Tunisia", "callsign": "TUNAIR", "ticketPrefix": "199"},
    {"iata": "AH", "icao": "DAH", "name": "Air Algérie", "country": "Algeria", "callsign": "AIR ALGERIE", "ticketPrefix": "124"},
    {"iata": "RW", "icao": "RWD", "name": "RwandAir", "country": "Rwanda", "callsign": "RWANDAIR", "ticketPrefix": "459"},
]

COUNTRIES = {
    "QA": {"code": "QA", "name": "Qatar", "currency": "QAR"},
    "AE": {"code": "AE", "name": "United Arab Emirates", "currency": "AED"},
    "SA": {"code": "SA", "name": "Saudi Arabia", "currency": "SAR"},
    "KW": {"code": "KW", "name": "Kuwait", "currency": "KWD"},
    "BH": {"code": "BH", "name": "Bahrain", "currency": "BHD"},
    "OM": {"code": "OM", "name": "Oman", "currency": "OMR"},
    "GB": {"code": "GB", "name": "United Kingdom", "currency": "GBP"},
    "US": {"code": "US", "name": "United States", "currency": "USD"},
    "FR": {"code": "FR", "name": "France", "currency": "EUR"},
    "DE": {"code": "DE", "name": "Germany", "currency": "EUR"},
    "IT": {"code": "IT", "name": "Italy", "currency": "EUR"},
    "ES": {"code": "ES", "name": "Spain", "currency": "EUR"},
    "NL": {"code": "NL", "name": "Netherlands", "currency": "EUR"},
    "CH": {"code": "CH", "name": "Switzerland", "currency": "CHF"},
    "TR": {"code": "TR", "name": "Turkey", "currency": "TRY"},
    "EG": {"code": "EG", "name": "Egypt", "currency": "EGP"},
    "JO": {"code": "JO", "name": "Jordan", "currency": "JOD"},
    "LB": {"code": "LB", "name": "Lebanon", "currency": "USD"},
    "SG": {"code": "SG", "name": "Singapore", "currency": "SGD"},
    "MY": {"code": "MY", "name": "Malaysia", "currency": "MYR"},
    "TH": {"code": "TH", "name": "Thailand", "currency": "THB"},
    "JP": {"code": "JP", "name": "Japan", "currency": "JPY"},
    "KR": {"code": "KR", "name": "South Korea", "currency": "KRW"},
    "CN": {"code": "CN", "name": "China", "currency": "CNY"},
    "IN": {"code": "IN", "name": "India", "currency": "INR"},
    "PK": {"code": "PK", "name": "Pakistan", "currency": "PKR"},
    "AU": {"code": "AU", "name": "Australia", "currency": "AUD"},
    "NZ": {"code": "NZ", "name": "New Zealand", "currency": "NZD"},
    "CA": {"code": "CA", "name": "Canada", "currency": "CAD"},
    "ZA": {"code": "ZA", "name": "South Africa", "currency": "ZAR"},
    "BR": {"code": "BR", "name": "Brazil", "currency": "BRL"},
}

def generate_typescript():
    os.makedirs("src/lib/data", exist_ok=True)
    
    # Write airports.ts
    with open("src/lib/data/airports.ts", "w", encoding="utf-8") as f:
        f.write("export interface AirportData {\n")
        f.write("  iata: string;\n")
        f.write("  icao: string;\n")
        f.write("  name: string;\n")
        f.write("  city: string;\n")
        f.write("  country: string;\n")
        f.write("  countryName: string;\n")
        f.write("  tz: string;\n")
        f.write("}\n\n")
        
        f.write("export const AIRPORTS_DATABASE: Record<string, AirportData> = {\n")
        for ap in AIRPORTS:
            f.write(f"  '{ap['iata']}': {json.dumps(ap)},\n")
        f.write("};\n\n")
        
        f.write("export const METRO_CODES: Record<string, string[]> = ")
        f.write(json.dumps(METRO_CODES, indent=2))
        f.write(";\n")

    # Write airlines.ts
    with open("src/lib/data/airlines.ts", "w", encoding="utf-8") as f:
        f.write("export interface AirlineData {\n")
        f.write("  iata: string;\n")
        f.write("  icao: string;\n")
        f.write("  name: string;\n")
        f.write("  country: string;\n")
        f.write("  callsign: string;\n")
        f.write("  ticketPrefix: string;\n")
        f.write("}\n\n")
        
        f.write("export const AIRLINES_DATABASE: Record<string, AirlineData> = {\n")
        for al in AIRLINES:
            f.write(f"  '{al['iata']}': {json.dumps(al)},\n")
        f.write("};\n\n")
        
        f.write("export const COUNTRIES_DATABASE: Record<string, { code: string; name: string; currency: string }> = ")
        f.write(json.dumps(COUNTRIES, indent=2))
        f.write(";\n")

    print(f"Generated {len(AIRPORTS)} airports, {len(AIRLINES)} airlines, {len(COUNTRIES)} countries.")

if __name__ == "__main__":
    generate_typescript()
