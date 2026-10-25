<?php

namespace App\Services\Amadeus;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Airport;

class DecodeResult
{
    public function __construct(
        public array $lines,
        public bool $isError = false
    ) {}
}

class DecodeService
{
    public const METRO_CODES = [
        'LON' => ['LHR', 'LGW', 'STN', 'LTN', 'LCY'],
        'PAR' => ['CDG', 'ORY'],
        'NYC' => ['JFK', 'EWR', 'LGA'],
        'CHI' => ['ORD', 'MDW'],
        'WAS' => ['IAD', 'DCA'],
        'TYO' => ['HND', 'NRT'],
        'SEL' => ['ICN', 'GMP'],
        'BJS' => ['PEK', 'PKX'],
        'SHA' => ['PVG', 'SHA'],
        'BKK' => ['BKK', 'DMK'],
        'MIL' => ['MXP', 'LIN'],
        'MOW' => ['SVO', 'DME', 'VKO'],
    ];

    public function handleDAN(string $query): DecodeResult
    {
        $clean = strtoupper(trim($query));
        if ($clean === '') {
            return new DecodeResult(['DAN FORMAT: DAN <CITY OR AIRPORT NAME/CODE>'], true);
        }

        // Check if it's an exact 3-letter IATA code
        if (strlen($clean) === 3) {
            $ap = Airport::find($clean);
            if ($ap) {
                return new DecodeResult([
                    "AIRPORT/CITY DECODE FOR: {$clean}",
                    "{$ap->iata}  " . str_pad($ap->icao ?? '', 5) . " " . strtoupper($ap->name),
                    "     CITY: " . strtoupper($ap->city) . "   COUNTRY: " . strtoupper($ap->country),
                    "     TIMEZONE: {$ap->tz}",
                ]);
            }
        }

        // Check if it's a metro code
        if (strlen($clean) === 3 && isset(self::METRO_CODES[$clean])) {
            $codes = self::METRO_CODES[$clean];
            $airports = Airport::whereIn('iata', $codes)->get();
            $city = $airports->first()?->city ? strtoupper($airports->first()->city) : $clean;

            $lines = [
                "METROPOLITAN AREA DECODE FOR: {$clean}",
                "CITY: {$city} (MULTI-AIRPORT METRO)",
                "AIRPORTS SERVING THIS AREA:",
            ];
            foreach ($airports as $ap) {
                $lines[] = "  {$ap->iata}  " . str_pad($ap->icao ?? '', 5) . " " . strtoupper($ap->name);
            }
            return new DecodeResult($lines);
        }

        // Search by city or airport name
        $matches = Airport::where('city', 'like', "%{$clean}%")
            ->orWhere('name', 'like', "%{$clean}%")
            ->limit(12)
            ->get();

        if ($matches->isEmpty()) {
            return new DecodeResult(["NO MATCH FOUND FOR: {$clean}"], true);
        }

        $lines = [
            "LOCATION ENCODE/DECODE FOR: {$clean}",
            "CODE  ICAO   CITY             AIRPORT NAME",
            "------------------------------------------------------------",
        ];

        foreach ($matches as $ap) {
            $codeStr = str_pad($ap->iata, 6);
            $icaoStr = str_pad($ap->icao ?? '', 7);
            $cityStr = str_pad(substr(strtoupper($ap->city), 0, 15), 17);
            $nameStr = substr(strtoupper($ap->name), 0, 28);
            $lines[] = "{$codeStr}{$icaoStr}{$cityStr}{$nameStr}";
        }

        return new DecodeResult($lines);
    }

    public function handleDAC(string $query): DecodeResult
    {
        return $this->handleDAN($query);
    }

    public function handleDNA(string $query): DecodeResult
    {
        $clean = strtoupper(trim($query));
        if ($clean === '') {
            return new DecodeResult(['DNA FORMAT: DNA <AIRLINE CODE OR NAME>'], true);
        }

        if (strlen($clean) === 2) {
            $al = Airline::find($clean);
            if ($al) {
                $lines = [
                    "AIRLINE DECODE FOR: {$clean}",
                    "IATA: {$al->iata}   ICAO: " . str_pad($al->icao ?? '', 4) . " TICKET PREFIX: {$al->ticket_prefix}",
                    "NAME: " . strtoupper($al->name),
                    "COUNTRY: " . strtoupper($al->country ?: 'UNKNOWN'),
                ];
                if ($al->callsign) {
                    $lines[] = "CALLSIGN: " . strtoupper($al->callsign);
                }
                return new DecodeResult($lines);
            }
        }

        $matches = Airline::where('name', 'like', "%{$clean}%")
            ->orWhere('iata', $clean)
            ->orWhere('icao', $clean)
            ->limit(10)
            ->get();

        if ($matches->isEmpty()) {
            return new DecodeResult(["NO AIRLINE FOUND FOR: {$clean}"], true);
        }

        $lines = [
            "AIRLINE ENCODE/DECODE FOR: {$clean}",
            "IATA  ICAO  NUM   AIRLINE NAME",
            "------------------------------------------------------------",
        ];

        foreach ($matches as $al) {
            $lines[] = str_pad($al->iata, 6) .
                str_pad($al->icao ?? '', 6) .
                str_pad($al->ticket_prefix ?? '', 6) .
                substr(strtoupper($al->name), 0, 40);
        }

        return new DecodeResult($lines);
    }

    public function handleDNE(string $query): DecodeResult
    {
        $clean = strtoupper(trim($query));
        if ($clean === '') {
            return new DecodeResult(['DNE FORMAT: DNE <EQUIPMENT CODE> (E.G. DNE 77W)'], true);
        }

        $eq = Aircraft::find($clean);
        if ($eq) {
            return new DecodeResult([
                "EQUIPMENT DECODE FOR: {$clean}",
                "CODE: {$eq->code}",
                "AIRCRAFT: " . strtoupper($eq->name),
                "MANUFACTURER: " . strtoupper($eq->manufacturer),
                "CATEGORY: {$eq->category}",
            ]);
        }

        $matches = Aircraft::where('name', 'like', "%{$clean}%")
            ->orWhere('code', 'like', "%{$clean}%")
            ->get();

        if ($matches->isNotEmpty()) {
            $lines = [
                "EQUIPMENT ENCODE/DECODE FOR: {$clean}",
                "CODE   AIRCRAFT TYPE             CATEGORY",
                "----------------------------------------------------",
            ];
            foreach ($matches as $a) {
                $lines[] = str_pad($a->code, 7) . str_pad($a->name, 26) . $a->category;
            }
            return new DecodeResult($lines);
        }

        return new DecodeResult(["EQUIPMENT CODE NOT FOUND: {$clean}"], true);
    }
}
