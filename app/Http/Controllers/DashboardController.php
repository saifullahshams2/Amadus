<?php

namespace App\Http\Controllers;

use App\Models\Pnr;
use App\Models\TerminalActivityLog;
use App\Models\User;
use App\Services\Amadeus\QueueService;
use App\Services\Training\ScenarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ScenarioService $scenarios,
        protected QueueService $queues
    ) {}

    /**
     * Personal Dashboard View.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        return $this->dashboardView($user, 'dashboard');
    }

    /**
     * Dedicated Dashboard Sub-page router.
     */
    public function section(Request $request, string $section): View|RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $allowedSections = ['workspace', 'drills'];
        if (! in_array($section, $allowedSections, true)) {
            return redirect()->route('dashboard');
        }

        return $this->dashboardView($user, $section);
    }

    /**
     * Compile and render the personal dashboard.
     */
    protected function dashboardView(User $user, string $currentSection = 'dashboard'): View
    {
        $username = $user->username;
        $officeId = $user->office_id ?? 'LON1A0980';

        // 1. Current Active PNR
        $activePnr = Pnr::where('user_id', $username)->orderBy('updated_at', 'desc')->first();

        // 2. Practice Scenarios with progress
        $scenarioList = $this->scenarios->getScenariosForUser($username);

        // 3. Learning Analytics & Recent Errors
        $recentLogs = TerminalActivityLog::where('user_id', $username)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $totalCommands = $recentLogs->count();
        $errorLogs = $recentLogs->where('is_error', true);
        $errorCount = $errorLogs->count();
        $successCount = $totalCommands - $errorCount;

        $accuracyRate = $totalCommands > 0
            ? (int) round(($successCount / $totalCommands) * 100)
            : 100;

        $efficiencyScore = min(100, max(50, (int) round(100 - ($accuracyRate < 100 ? (100 - $accuracyRate) * 0.8 : 0))));

        $completedCount = $scenarioList->where('status', 'submitted')->count();

        $recentErrorsWithHints = $errorLogs->take(5)->map(function (TerminalActivityLog $log) {
            $val = $this->analyzeSyntax($log->command);

            return [
                'command' => $log->command,
                'output' => implode(' | ', $log->output_lines ?? []),
                'time' => $log->created_at->diffForHumans(),
                'hint' => $val['correctionHint'] ?? 'Verify format in Amadeus quick reference (HE)',
            ];
        })->values();

        // 4. Active Queue Summary
        $queueSummary = $this->queues->getSummary($officeId, $username);

        return view('dashboard.index', [
            'user' => $user,
            'currentSection' => $currentSection,
            'activePnr' => $activePnr,
            'scenarios' => $scenarioList,
            'queueSummary' => $queueSummary,
            'analytics' => [
                'accuracyRate' => $accuracyRate,
                'efficiencyScore' => $efficiencyScore,
                'totalCommands' => $totalCommands,
                'completedCount' => $completedCount,
                'totalScenarios' => $scenarioList->count(),
                'completionRate' => $scenarioList->count() > 0 ? (int) round(($completedCount / $scenarioList->count()) * 100) : 0,
                'recentErrors' => $recentErrorsWithHints,
            ],
        ]);
    }

    /**
     * API: Get Current Active PNR for inspection drawer.
     */
    public function getActivePnrApi(Request $request): JsonResponse
    {
        $currentUser = Auth::user();
        if (! $currentUser) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $pnr = Pnr::where('user_id', $currentUser->username)->orderBy('updated_at', 'desc')->first();

        if (! $pnr) {
            return response()->json([
                'hasActivePnr' => false,
                'pnr' => null,
            ]);
        }

        $data = $pnr->data_json ?? [];

        return response()->json([
            'hasActivePnr' => true,
            'recordLocator' => $pnr->record_locator,
            'status' => $pnr->status,
            'officeId' => $pnr->office_id,
            'passengers' => $data['passengers'] ?? [],
            'segments' => $data['segments'] ?? [],
            'contacts' => $data['contacts'] ?? [],
            'ticketing' => $data['ticketing'] ?? null,
            'fop' => $data['fop'] ?? null,
            'receivedFrom' => $data['receivedFrom'] ?? null,
            'tst' => $data['tst'] ?? null,
            'rawLines' => $this->formatPnrToLines($pnr),
            'updatedAt' => $pnr->updated_at->toIso8601String(),
        ]);
    }

    /**
     * API: Live Syntax Validator with Actionable Hints.
     */
    public function validateSyntaxApi(Request $request): JsonResponse
    {
        $raw = $request->input('command', '');

        return response()->json($this->analyzeSyntax($raw));
    }

    /**
     * API: Submit a Practice Scenario for Automated Grading.
     */
    public function submitScenarioApi(Request $request, string $id): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $username = $user->username;

        // Everything that gets graded is resolved from server-side state. The
        // request may only name which of the caller's own stored PNRs to grade.
        $locator = $request->input('recordLocator');
        $pnrQuery = Pnr::where('user_id', $username);
        if (is_string($locator) && $locator !== '') {
            $pnrQuery->where('record_locator', strtoupper($locator));
        }

        $latestPnr = $pnrQuery->orderBy('updated_at', 'desc')->first();
        if (! $latestPnr) {
            return response()->json([
                'error' => 'No stored PNR found. Complete and store the booking in the terminal before submitting.',
            ], 422);
        }

        $pnrData = $latestPnr->data_json ?? [];

        $activity = TerminalActivityLog::where('user_id', $username)->orderBy('created_at')->get();
        $terminalLogs = $activity
            ->map(fn (TerminalActivityLog $log) => [
                'command' => $log->command,
                'is_error' => (bool) $log->is_error,
            ])
            ->all();
        $timeElapsed = (int) max(0, $activity->first()?->created_at?->diffInSeconds($latestPnr->updated_at ?? now()) ?? 0);

        $submission = $this->scenarios->submitScenario(
            $username,
            $id,
            $pnrData,
            $terminalLogs,
            $timeElapsed
        );

        return response()->json([
            'success' => true,
            'submission' => $submission,
            'grade' => $submission->auto_grade_results,
        ]);
    }

    /**
     * Helper: Analyze Amadeus cryptic syntax and generate correction hints.
     */
    protected function analyzeSyntax(string $raw): array
    {
        $input = strtoupper(trim($raw));
        if ($input === '') {
            return [
                'command' => $raw,
                'isValid' => false,
                'category' => 'Empty',
                'explanation' => 'No command entered',
                'correctionHint' => 'Type any Amadeus entry, e.g., AN15NOVLHRJFK or HE for help',
            ];
        }

        // 1. Availability (AN / SN)
        if (str_starts_with($input, 'AN') || str_starts_with($input, 'SN')) {
            $isAn = str_starts_with($input, 'AN');
            if (preg_match('/^(AN|SN)\d{1,2}[A-Z]{3}[A-Z]{6}(\/A[A-Z0-9]{2})?$/', $input)) {
                return [
                    'command' => $input,
                    'isValid' => true,
                    'category' => 'Availability',
                    'explanation' => ($isAn ? 'Neutral Flight Availability' : 'Timetable Schedule Display').' query is syntactically valid.',
                    'example' => 'AN15NOVLHRJFK or AN15NOVLHRJFK/ABA',
                ];
            }

            return [
                'command' => $input,
                'isValid' => false,
                'category' => 'Availability',
                'explanation' => 'Invalid availability format.',
                'correctionHint' => 'Syntax: AN<DATE><ORIG><DEST> (e.g. AN15NOVLHRJFK) or with airline: AN15NOVLHRJFK/ABA',
                'example' => 'AN15NOVLHRJFK',
            ];
        }

        // 2. Sell Segment (SS)
        if (str_starts_with($input, 'SS')) {
            if (preg_match('/^SS\d{1,2}[A-Z]\d{1,2}$/', $input)) {
                return [
                    'command' => $input,
                    'isValid' => true,
                    'category' => 'PNR Elements',
                    'explanation' => 'Sell seat entry format is valid.',
                    'example' => 'SS1Y1 (Sell 1 seat in Y class from line 1 of availability)',
                ];
            }

            return [
                'command' => $input,
                'isValid' => false,
                'category' => 'PNR Elements',
                'explanation' => 'Invalid sell command format.',
                'correctionHint' => 'Syntax: SS<SEATS><CLASS><LINE> (e.g. SS1Y1 or SS2J3)',
                'example' => 'SS1Y1',
            ];
        }

        // 3. Passenger Name (NM)
        if (str_starts_with($input, 'NM')) {
            if (preg_match('/^NM\d+[A-Z0-9\/\s\-]+$/', $input) && str_contains($input, '/')) {
                return [
                    'command' => $input,
                    'isValid' => true,
                    'category' => 'PNR Elements',
                    'explanation' => 'Passenger name entry format is valid.',
                    'example' => 'NM1SMITH/JOHN MR or NM1DOE/JANE MRS(INF/BABY/01JAN25)',
                ];
            }

            return [
                'command' => $input,
                'isValid' => false,
                'category' => 'PNR Elements',
                'explanation' => 'Invalid passenger name syntax.',
                'correctionHint' => 'Syntax: NM<COUNT><SURNAME>/<FIRSTNAME> <TITLE> (e.g. NM1DOE/JOHN MR)',
                'example' => 'NM1DOE/JOHN MR',
            ];
        }

        // 4. Contact (AP / APE)
        if (str_starts_with($input, 'AP')) {
            if (str_starts_with($input, 'APE ') || preg_match('/^AP\s+.+$/', $input)) {
                return [
                    'command' => $input,
                    'isValid' => true,
                    'category' => 'PNR Elements',
                    'explanation' => 'Contact element syntax is valid.',
                    'example' => 'AP LON 44-20-7123-4567-A or APE JOHN.DOE//GMAIL.COM',
                ];
            }

            return [
                'command' => $input,
                'isValid' => false,
                'category' => 'PNR Elements',
                'explanation' => 'Contact element requires a space and contact detail.',
                'correctionHint' => 'Syntax: AP <CITY> <PHONE>-<TYPE> or APE <EMAIL> (e.g. AP LON 44-20-7123-4567-A)',
                'example' => 'AP LON 44-20-7123-4567-A',
            ];
        }

        // 5. Ticketing (TK)
        if (str_starts_with($input, 'TK')) {
            if ($input === 'TKOK' || preg_match('/^TKTL\d{1,2}[A-Z]{3}(\/[A-Z0-9]{8,10})?$/', $input)) {
                return [
                    'command' => $input,
                    'isValid' => true,
                    'category' => 'PNR Elements',
                    'explanation' => 'Ticketing arrangement syntax is valid.',
                    'example' => 'TKTL15NOV/LON1A0980 or TKOK',
                ];
            }

            return [
                'command' => $input,
                'isValid' => false,
                'category' => 'PNR Elements',
                'explanation' => 'Invalid ticketing element syntax.',
                'correctionHint' => 'Syntax: TKTL<DATE>/<OFFICE> (e.g. TKTL15NOV/LON1A0980) or TKOK',
                'example' => 'TKTL15NOV/LON1A0980',
            ];
        }

        // 6. Received From (RF)
        if (str_starts_with($input, 'RF')) {
            if (preg_match('/^RF\s+.+$/', $input)) {
                return [
                    'command' => $input,
                    'isValid' => true,
                    'category' => 'PNR Elements',
                    'explanation' => 'Received from syntax is valid.',
                    'example' => 'RF PASSENGER',
                ];
            }

            return [
                'command' => $input,
                'isValid' => false,
                'category' => 'PNR Elements',
                'explanation' => 'Missing source in received from element.',
                'correctionHint' => 'Syntax: RF <NAME> (e.g. RF PASSENGER or RF MR SMITH)',
                'example' => 'RF PASSENGER',
            ];
        }

        // 7. Pricing (FXP / FXX / FXB / FQD)
        if (in_array($input, ['FXP', 'FXX', 'FXB'], true) || str_starts_with($input, 'FQD')) {
            return [
                'command' => $input,
                'isValid' => true,
                'category' => 'Pricing',
                'explanation' => 'Itinerary pricing / tariff display command recognized.',
                'example' => 'FXP (Price and store TST) or FQDDOHLHR/D15NOV/AQR',
            ];
        }

        // 8. Electronic Ticketing & Form of Payment (TTP / FP)
        if ($input === 'TTP' || str_starts_with($input, 'FP ') || str_starts_with($input, 'TWD')) {
            return [
                'command' => $input,
                'isValid' => true,
                'category' => 'Ticketing',
                'explanation' => 'Ticketing / payment command recognized.',
                'example' => 'FP CASH or TTP (Issue electronic tickets)',
            ];
        }

        // 9. Queues (QS / QD / QN / QI)
        if (in_array($input, ['QS', 'QN', 'QI', 'IG', 'IR', 'ET', 'ER'], true) || str_starts_with($input, 'QD')) {
            return [
                'command' => $input,
                'isValid' => true,
                'category' => 'Queues',
                'explanation' => 'Queue / Transaction transaction command recognized.',
                'example' => 'QS (Queue summary) or QD 1 (Display queue 1)',
            ];
        }

        // 10. Decode (DAN / DAC / DNA / DNE)
        if (str_starts_with($input, 'DAN') || str_starts_with($input, 'DAC') || str_starts_with($input, 'DNA') || str_starts_with($input, 'DNE') || $input === 'HE' || $input === 'HELP') {
            return [
                'command' => $input,
                'isValid' => true,
                'category' => 'Decode / Reference',
                'explanation' => 'IATA Decode / Help entry recognized.',
                'example' => 'DAN LONDON, DNA QR, DNE 77W',
            ];
        }

        return [
            'command' => $input,
            'isValid' => false,
            'category' => 'Unknown',
            'explanation' => "Command \"{$input}\" not recognized in standard Amadeus GDS format.",
            'correctionHint' => 'Type "HE" for full command reference.',
            'example' => 'AN15NOVLHRJFK',
        ];
    }

    /**
     * Helper: Format PNR model to standard Amadeus cryptic lines.
     */
    protected function formatPnrToLines(Pnr $pnr): array
    {
        $d = $pnr->data_json ?? [];
        $lines = ['--- RLR ---'];
        $lines[] = "RP/{$pnr->office_id}/".($d['creationAgent'] ?? '1234AB')."   {$pnr->record_locator}";

        foreach ($d['passengers'] ?? [] as $idx => $pax) {
            $lines[] = '  '.($idx + 1).".{$pax['lastName']}/{$pax['firstName']} ".($pax['title'] ?? '');
        }

        foreach ($d['segments'] ?? [] as $seg) {
            $lines[] = "  {$seg['segmentNumber']}  {$seg['airlineCode']} {$seg['flightNumber']} {$seg['bookingClass']} {$seg['departureDate']} {$seg['departureAirport']}{$seg['arrivalAirport']} HK{$seg['seats']}  {$seg['departureTime']} {$seg['arrivalTime']}";
        }

        foreach ($d['contacts'] ?? [] as $idx => $c) {
            $lines[] = '  '.($idx + 1)." AP {$c['detail']}";
        }

        if (! empty($d['ticketing'])) {
            $raw = is_array($d['ticketing']) ? ($d['ticketing']['raw'] ?? 'OK') : $d['ticketing'];
            $lines[] = "  TK {$raw}";
        }

        if (! empty($d['fop'])) {
            $lines[] = "  FP {$d['fop']['details']}";
        }

        if (! empty($d['receivedFrom'])) {
            $lines[] = "  RF {$d['receivedFrom']}";
        }

        if (! empty($d['tst'])) {
            $lines[] = "  TST 01 - FARE {$d['tst']['currency']} {$d['tst']['baseFare']} TOTAL {$d['tst']['totalAmount']}";
        }

        return $lines;
    }
}
