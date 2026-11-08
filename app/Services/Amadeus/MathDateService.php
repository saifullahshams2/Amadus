<?php

namespace App\Services\Amadeus;

use App\Models\Airport;
use DateTime;
use DateTimeZone;

class MathDateResult
{
    /**
     * @param  array<int, string>  $lines
     */
    public function __construct(
        public array $lines,
        public bool $isError = false
    ) {}
}

class MathDateService
{
    private const MONTH_MAP = [
        'JAN' => 1, 'FEB' => 2, 'MAR' => 3, 'APR' => 4, 'MAY' => 5, 'JUN' => 6,
        'JUL' => 7, 'AUG' => 8, 'SEP' => 9, 'OCT' => 10, 'NOV' => 11, 'DEC' => 12,
    ];

    private const DAY_NAMES = ['SUNDAY', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY'];

    /**
     * Handle DF calculation entries.
     */
    public function handleDF(string $input): MathDateResult
    {
        $raw = trim(substr(trim($input), 2));
        if ($raw === '') {
            return new MathDateResult([
                'DF - MATHEMATICAL CALCULATIONS',
                'ADDITION      : DF 889; 1733; 669 OR DF 889+1733+669',
                'SUBTRACTION   : DF 1955-889',
                'MULTIPLICATION: DF 1604*33',
                'DIVISION      : DF 1509/34',
            ], true);
        }

        // 1. Division: DF 1509/34
        if (str_contains($raw, '/')) {
            $parts = explode('/', $raw);
            if (count($parts) === 2 && is_numeric(trim($parts[0])) && is_numeric(trim($parts[1]))) {
                $n1 = (float) trim($parts[0]);
                $n2 = (float) trim($parts[1]);
                if ($n2 == 0.0) {
                    return new MathDateResult(['DF: ERROR - DIVISION BY ZERO'], true);
                }
                $ans = $n1 / $n2;
                $formatted = (floor($ans) == $ans) ? (string) (int) $ans : number_format($ans, 2, '.', '');

                return new MathDateResult([
                    "DF: {$n1} / {$n2} = {$formatted}",
                ]);
            }
        }

        // 2. Multiplication: DF 1604*33
        if (str_contains($raw, '*')) {
            $parts = explode('*', $raw);
            if (count($parts) === 2 && is_numeric(trim($parts[0])) && is_numeric(trim($parts[1]))) {
                $n1 = (float) trim($parts[0]);
                $n2 = (float) trim($parts[1]);
                $ans = $n1 * $n2;
                $formatted = (floor($ans) == $ans) ? (string) (int) $ans : number_format($ans, 2, '.', '');

                return new MathDateResult([
                    "DF: {$n1} * {$n2} = {$formatted}",
                ]);
            }
        }

        // 3. Subtraction: DF 1955-889
        if (str_contains($raw, '-')) {
            $parts = explode('-', $raw);
            if (count($parts) === 2 && is_numeric(trim($parts[0])) && is_numeric(trim($parts[1]))) {
                $n1 = (float) trim($parts[0]);
                $n2 = (float) trim($parts[1]);
                $ans = $n1 - $n2;
                $formatted = (floor($ans) == $ans) ? (string) (int) $ans : number_format($ans, 2, '.', '');

                return new MathDateResult([
                    "DF: {$n1} - {$n2} = {$formatted}",
                ]);
            }
        }

        // 4. Addition: separated by semicolon or plus
        $addParts = preg_split('/[;+]/', $raw);
        if ($addParts && count($addParts) >= 2) {
            $sum = 0;
            $items = [];
            foreach ($addParts as $p) {
                $pClean = trim($p);
                if (! is_numeric($pClean)) {
                    return new MathDateResult(["DF: INVALID NUMERIC VALUE '{$pClean}'"], true);
                }
                $sum += (float) $pClean;
                $items[] = $pClean;
            }
            $formatted = (floor($sum) == $sum) ? (string) (int) $sum : number_format($sum, 2, '.', '');
            $expr = implode(' + ', $items);

            return new MathDateResult([
                "DF: {$expr} = {$formatted}",
            ]);
        }

        return new MathDateResult(["DF: UNRECOGNIZED CALCULATION FORMAT: \"{$raw}\""], true);
    }

    /**
     * Handle DD date and time conversion entries.
     */
    public function handleDD(string $input): MathDateResult
    {
        $clean = strtoupper(trim($input));
        $rest = trim(substr($clean, str_starts_with($clean, 'DD') ? 2 : 1));

        if ($rest === '' || $clean === 'HE DD') {
            $utc = gmdate('dMY Hi').'Z';

            return new MathDateResult([
                '--- AMADEUS DATE & TIME (DD) ---',
                "CURRENT SYSTEM TIME (UTC) : {$utc}",
                'LOCAL TIME BY CITY CODE   : DD <CITY> (E.G. DD SIN)',
                'TIME DIFFERENCE           : DD <CITY1>/<CITY2> (E.G. DD LON/LAX)',
                'UTC CONVERSION            : DD ZZZ<TIME>/<CITY> (E.G. DD ZZZ1500/DEL)',
                'DAY OF WEEK               : DD<DATE> (E.G. DD25DEC08)',
                'DATE OFFSET CALCULATION   : D<DATE>/<DAYS> (E.G. D22APR13/30)',
            ]);
        }

        // 1. UTC to Local conversion: DD ZZZ1500/DEL
        if (preg_match('/^ZZZ(\d{4})\/([A-Z]{3})$/', $rest, $zzzM)) {
            $timeStr = $zzzM[1];
            $city = $zzzM[2];
            $hours = (int) substr($timeStr, 0, 2);
            $mins = (int) substr($timeStr, 2, 2);

            $ap = Airport::find($city);
            $tzName = $ap?->tz ?: 'UTC';

            try {
                $dt = new DateTime("today {$hours}:{$mins}:00", new DateTimeZone('UTC'));
                $dt->setTimezone(new DateTimeZone($tzName));
                $localTime = $dt->format('Hi');
                $localDate = strtoupper($dt->format('dM'));

                return new MathDateResult([
                    "DD: UTC {$timeStr}Z IN {$city} ({$tzName}) = {$localTime} LOCAL ({$localDate})",
                ]);
            } catch (\Throwable) {
                return new MathDateResult(["DD: UNABLE TO RESOLVE TIMEZONE FOR {$city}"], true);
            }
        }

        // 2. Time difference between two cities: DD LON/LAX
        if (preg_match('/^([A-Z]{3})\/([A-Z]{3})$/', $rest, $diffM)) {
            $c1 = $diffM[1];
            $c2 = $diffM[2];

            $ap1 = Airport::find($c1) ?? (isset(DecodeService::METRO_CODES[$c1]) ? Airport::find(DecodeService::METRO_CODES[$c1][0]) : null);
            $ap2 = Airport::find($c2) ?? (isset(DecodeService::METRO_CODES[$c2]) ? Airport::find(DecodeService::METRO_CODES[$c2][0]) : null);

            $tz1 = $ap1?->tz ?: 'UTC';
            $tz2 = $ap2?->tz ?: 'UTC';

            try {
                $now = new DateTime('now', new DateTimeZone('UTC'));

                $dt1 = clone $now;
                $dt1->setTimezone(new DateTimeZone($tz1));

                $dt2 = clone $now;
                $dt2->setTimezone(new DateTimeZone($tz2));

                $offsetDiffHours = ($dt2->getOffset() - $dt1->getOffset()) / 3600;
                $sign = $offsetDiffHours >= 0 ? '+' : '';

                return new MathDateResult([
                    "--- TIME DIFFERENCE: {$c1} VS {$c2} ---",
                    "{$c1} ({$tz1}) : ".strtoupper($dt1->format('dM Hi')).' LOCAL',
                    "{$c2} ({$tz2}) : ".strtoupper($dt2->format('dM Hi')).' LOCAL',
                    "DIFFERENCE : {$c2} IS {$sign}{$offsetDiffHours} HOURS RELATIVE TO {$c1}",
                ]);
            } catch (\Throwable) {
                return new MathDateResult(["DD: ERROR COMPUTING TIME DIFFERENCE FOR {$c1}/{$c2}"], true);
            }
        }

        // 3. Date offset calculation: D22APR13/30 or DD22APR/30
        if (preg_match('/^(\d{1,2}[A-Z]{3}(?:\d{2})?)\/(\d+)$/', $rest, $offM)) {
            $baseDate = $offM[1];
            $days = (int) $offM[2];

            // Parse DDMMM or DDMMMYY
            if (preg_match('/^(\d{1,2})([A-Z]{3})(\d{2})?$/', $baseDate, $bm)) {
                $day = (int) $bm[1];
                $mon = $bm[2];
                $yr = ! empty($bm[3]) ? (int) ('20'.$bm[3]) : (int) date('Y');
                if (isset(self::MONTH_MAP[$mon])) {
                    $monthNum = self::MONTH_MAP[$mon];
                    $ts = mktime(0, 0, 0, $monthNum, $day, $yr);
                    $newTs = strtotime("+{$days} days", $ts);
                    $newDate = strtoupper(date('dM', $newTs));
                    $newDay = strtoupper(date('D', $newTs));
                    $newYear = date('Y', $newTs);

                    return new MathDateResult([
                        "DD: {$baseDate} + {$days} DAYS = {$newDay} {$newDate} ({$newYear})",
                    ]);
                }
            }
        }

        // 4. Day of the week for date: DD25DEC08 or DD25DEC
        if (preg_match('/^(\d{1,2})([A-Z]{3})(\d{2})?$/', $rest, $dateM)) {
            $day = (int) $dateM[1];
            $mon = $dateM[2];
            $yr = ! empty($dateM[3]) ? (int) ('20'.$dateM[3]) : (int) date('Y');

            if (isset(self::MONTH_MAP[$mon]) && $day >= 1 && $day <= 31) {
                $monthNum = self::MONTH_MAP[$mon];
                $ts = mktime(0, 0, 0, $monthNum, $day, $yr);
                $dayName = self::DAY_NAMES[(int) date('w', $ts)];
                $fDate = str_pad((string) $day, 2, '0', STR_PAD_LEFT).$mon.date('y', $ts);

                return new MathDateResult([
                    "DD: {$fDate} IS {$dayName} (DAY ".(date('z', $ts) + 1).' OF YEAR '.date('Y', $ts).')',
                ]);
            }
        }

        // 5. Single city time lookup: DD SIN
        if (preg_match('/^[A-Z]{3}$/', $rest)) {
            $city = $rest;
            $ap = Airport::find($city) ?? (isset(DecodeService::METRO_CODES[$city]) ? Airport::find(DecodeService::METRO_CODES[$city][0]) : null);
            $tzName = $ap?->tz ?: 'UTC';

            try {
                $now = new DateTime('now', new DateTimeZone($tzName));
                $cityName = $ap ? strtoupper($ap->city) : $city;

                return new MathDateResult([
                    "DD {$city}: ".strtoupper($now->format('D dM Hi'))." LOCAL TIME ({$cityName} / {$tzName})",
                    'UTC OFFSET : '.$now->format('P'),
                ]);
            } catch (\Throwable) {
                return new MathDateResult(["DD: INVALID LOCATION CODE {$city}"], true);
            }
        }

        return new MathDateResult(["DD: UNRECOGNIZED DATE/TIME ENTRY \"{$rest}\""], true);
    }
}
