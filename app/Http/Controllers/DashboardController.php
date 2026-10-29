<?php

namespace App\Http\Controllers;

use App\Models\LessonProgress;
use App\Models\Pnr;
use App\Models\QueueItem;
use App\Models\Scenario;
use App\Models\Submission;
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
     * Unified Dashboard View.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('landing');
        }

        // 1. Strict separation: Authenticated students are strictly restricted to student dashboard
        if ($user->role === 'student') {
            if ($request->query('role') === 'instructor') {
                abort(403, 'Forbidden. Students cannot access the instructor dashboard.');
            }

            return $this->studentView($request, $user, 'dashboard');
        }

        // 2. Strict separation: Authenticated instructors are strictly restricted to instructor dashboard
        if ($user->role === 'instructor') {
            return $this->instructorView($request, $user, 'dashboard');
        }

        return redirect()->route('landing');
    }

    /**
     * Dedicated Dashboard Sub-page router.
     */
    public function section(Request $request, string $section): View|RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('landing');
        }

        if ($user->role === 'student') {
            $allowedStudentSections = ['workspace', 'drills', 'validator', 'analytics'];
            if (! in_array($section, $allowedStudentSections, true)) {
                return redirect()->route('dashboard');
            }

            return $this->studentView($request, $user, $section);
        }

        if ($user->role === 'instructor') {
            $allowedInstructorSections = ['classroom', 'students', 'builder', 'gradebook', 'queues', 'analytics'];
            if (! in_array($section, $allowedInstructorSections, true)) {
                return redirect()->route('dashboard');
            }

            return $this->instructorView($request, $user, $section);
        }

        return redirect()->route('landing');
    }

    /**
     * Role switching endpoint. Unauthenticated guests are redirected to main page.
     */
    public function switchRole(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('landing');
        }

        abort(403, 'Forbidden. Role switching is not permitted for authenticated accounts.');
    }

    /**
     * Compile and render Student Dashboard.
     */
    protected function studentView(Request $request, ?User $user, string $currentSection = 'dashboard'): View
    {
        $username = $user?->username ?? 'student';
        $officeId = $user?->office_id ?? 'LON1A0980';

        // 1. Current Active PNR
        $activePnr = Pnr::where('user_id', $username)->orderBy('updated_at', 'desc')->first();

        // 2. Assigned Scenarios with student progress
        $scenarioList = $this->scenarios->getScenariosForStudent($username);

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

        $completedCount = $scenarioList->where('status', 'reviewed')->count()
            + $scenarioList->where('status', 'submitted')->count();

        $recentErrorsWithHints = $errorLogs->take(5)->map(function (TerminalActivityLog $log) {
            $val = $this->analyzeSyntax($log->command);

            return [
                'command' => $log->command,
                'output' => implode(' | ', $log->output_lines ?? []),
                'time' => $log->created_at->diffForHumans(),
                'hint' => $val['correctionHint'] ?? 'Verify format in Amadeus quick reference (HE)',
            ];
        })->values();

        // 5. Active Queue Summary
        $queueSummary = $this->queues->getSummary($officeId, $username);

        return view('dashboard.student', [
            'user' => $user,
            'activeRole' => 'student',
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
     * Compile and render Instructor Dashboard.
     */
    protected function instructorView(Request $request, ?User $user, string $currentSection = 'dashboard'): View
    {
        $officeId = $user?->office_id ?? 'LON1A0980';

        // 1. Enrolled Students with Live Session Status
        $students = User::where('role', 'student')->where('is_approved', true)->get();
        $pendingApprovals = User::where('role', 'student')->where('is_approved', false)->orderBy('created_at', 'desc')->get();

        $studentStatuses = $students->map(function (User $st) {
            $recentLogs = TerminalActivityLog::where('user_id', $st->username)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            $lastLog = $recentLogs->first();
            $lastActiveDiff = $lastLog ? now()->diffInMinutes($lastLog->created_at) : 999;
            $recentErrors = $recentLogs->take(5)->where('is_error', true)->count();

            // Determine status: Online, In Terminal, Idle, Error Loop
            if ($lastActiveDiff <= 2) {
                $status = ($recentErrors >= 3) ? 'Error Loop' : 'In Terminal';
            } elseif ($lastActiveDiff <= 15) {
                $status = 'Online';
            } else {
                $status = 'Idle';
            }

            $submissionsCount = Submission::where('user_id', $st->username)->whereIn('status', ['submitted', 'reviewed'])->count();
            $activePnr = Pnr::where('user_id', $st->username)->orderBy('updated_at', 'desc')->first();

            return [
                'id' => $st->id,
                'username' => $st->username,
                'name' => $st->name,
                'email' => $st->email,
                'office_id' => $st->office_id,
                'agent_id' => $st->agent_id,
                'status' => $status,
                'last_command' => $lastLog?->command ?? 'None',
                'last_active' => $lastLog ? $lastLog->created_at->diffForHumans() : 'Never',
                'total_commands' => TerminalActivityLog::where('user_id', $st->username)->count(),
                'submissions_count' => $submissionsCount,
                'active_pnr_locator' => $activePnr?->record_locator,
                'error_rate' => $recentLogs->count() > 0 ? (int) round(($recentLogs->where('is_error', true)->count() / $recentLogs->count()) * 100) : 0,
            ];
        });

        // 2. Scenarios list for builder overview
        $scenarios = Scenario::orderBy('created_at', 'desc')->get();

        // 3. Submissions & Gradebook
        $submissions = Submission::with(['scenario', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // 4. Queue Summary & Simulated Queue Items
        $queueSummary = $this->queues->getSummary($officeId);
        $pendingQueueItems = QueueItem::pending()->where('office_id', $officeId)->orderBy('created_at', 'desc')->limit(20)->get();

        // 5. Recent PNR audit trail
        $recentPnrs = Pnr::where('office_id', $officeId)->orderBy('updated_at', 'desc')->limit(20)->get();
        $allPnrs = Pnr::orderBy('updated_at', 'desc')->limit(100)->get();
        $progress = LessonProgress::all();

        return view('dashboard.instructor', [
            'user' => $user,
            'activeRole' => 'instructor',
            'currentSection' => $currentSection,
            'students' => $studentStatuses,
            'pendingApprovals' => $pendingApprovals,
            'scenarios' => $scenarios,
            'submissions' => $submissions,
            'queueSummary' => $queueSummary,
            'pendingQueueItems' => $pendingQueueItems,
            'recentPnrs' => $recentPnrs,
            'allPnrs' => $allPnrs,
            'progress' => $progress,
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

        // Only instructors can inspect another user's active PNR via ?user=
        $userId = ($currentUser->role === 'instructor' && $request->filled('user'))
            ? $request->query('user')
            : $currentUser->username;

        $pnr = Pnr::where('user_id', $userId)->orderBy('updated_at', 'desc')->first();

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
        $res = $this->analyzeSyntax($raw);

        return response()->json($res);
    }

    /**
     * API: Submit a Scenario for Automated Grading.
     */
    public function submitScenarioApi(Request $request, string $id): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        if ($user->role === 'student' && ! $user->is_approved) {
            return response()->json(['error' => 'Cadet account requires instructor approval.'], 403);
        }

        $username = $user->username;

        $pnrData = $request->input('pnrData');
        if (! $pnrData) {
            $latestPnr = Pnr::where('user_id', $username)->orderBy('updated_at', 'desc')->first();
            $pnrData = $latestPnr?->data_json ?? [];
        }

        $terminalLogs = $request->input('terminalLogs', []);
        $timeElapsed = (int) $request->input('timeElapsedSeconds', 0);

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
     * API: Spectate a Student's Live Terminal Activity.
     */
    public function spectateStudentApi(Request $request, string $username): JsonResponse
    {
        $student = User::where('username', $username)->first();
        if (! $student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $logs = TerminalActivityLog::where('user_id', $username)
            ->orderBy('created_at', 'asc')
            ->limit(100)
            ->get();

        $activePnr = Pnr::where('user_id', $username)->orderBy('updated_at', 'desc')->first();

        return response()->json([
            'student' => [
                'username' => $student->username,
                'name' => $student->name,
                'office_id' => $student->office_id,
                'agent_id' => $student->agent_id,
            ],
            'activePnrLocator' => $activePnr?->record_locator,
            'logs' => $logs->map(fn (TerminalActivityLog $l) => [
                'id' => $l->id,
                'command' => $l->command,
                'output_lines' => $l->output_lines,
                'is_error' => $l->is_error,
                'command_type' => $l->command_type,
                'execution_time_ms' => $l->execution_time_ms,
                'timestamp' => $l->created_at->format('H:i:s'),
                'time_ago' => $l->created_at->diffForHumans(),
            ]),
        ]);
    }

    /**
     * API: Create New Scenario via Scenario Builder.
     */
    public function createScenarioApi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|string|unique:scenarios,id',
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'difficulty' => 'required|string|in:Beginner,Intermediate,Advanced',
            'description' => 'required|string',
            'passenger_profile' => 'required|array',
            'flight_segments' => 'required|array',
            'validation_criteria' => 'required|array',
            'time_limit_minutes' => 'required|integer|min:5|max:120',
        ]);

        $validated['created_by'] = Auth::user()?->username ?? 'instructor';

        $scenario = Scenario::create($validated);

        return response()->json([
            'success' => true,
            'scenario' => $scenario,
        ], 201);
    }

    /**
     * API / Web: Delete Scenario & Drill.
     */
    public function deleteScenario(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'instructor') {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Instructor access required.'], 403);
            }

            abort(403, 'Forbidden. Instructor access required.');
        }

        $deleted = $this->scenarios->deleteScenario($id);

        if (! $deleted) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['error' => "Scenario '{$id}' not found."], 404);
            }

            return redirect()->back()->withErrors(['error' => "Scenario '{$id}' not found."]);
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => "Scenario '{$id}' deleted successfully.",
                'deleted_id' => $id,
            ]);
        }

        return redirect()->back()->with('success', "Scenario '{$id}' deleted successfully.");
    }

    /**
     * API: Override Submission Score & Add Feedback.
     */
    public function gradeSubmissionApi(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'score' => 'required|integer|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $submission = $this->scenarios->overrideGrade(
            $id,
            $validated['score'],
            $validated['feedback'] ?? null
        );

        return response()->json([
            'success' => true,
            'submission' => $submission,
        ]);
    }

    /**
     * API: Broadcast PNR to Student Queues.
     */
    public function broadcastQueueApi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'queue_number' => 'required|integer|in:1,7,8,9,87',
            'category' => 'required|string',
            'pnr_locator' => 'required|string|size:6',
            'message' => 'required|string',
            'target_user_id' => 'nullable|string',
            'passenger' => 'required|string',
            'itinerary' => 'required|string',
            'urgency' => 'required|string|in:NORMAL,HIGH,CRITICAL',
        ]);

        $officeId = Auth::user()?->office_id ?? 'LON1A0980';

        $item = $this->queues->broadcastToQueue(
            $validated['queue_number'],
            $validated['category'],
            $validated['pnr_locator'],
            [
                'passenger' => $validated['passenger'],
                'itinerary' => $validated['itinerary'],
                'urgency' => $validated['urgency'],
            ],
            $validated['message'],
            $validated['target_user_id'] ?: null,
            $officeId
        );

        return response()->json([
            'success' => true,
            'queueItem' => $item,
        ], 201);
    }

    /**
     * API: Reset Sandbox per Student or Whole Batch.
     */
    public function resetSandboxApi(Request $request): JsonResponse
    {
        $targetUserId = $request->input('target_user_id'); // null resets all
        $officeId = Auth::user()?->office_id ?? 'LON1A0980';

        $res = $this->queues->resetSandbox($targetUserId, $officeId);

        return response()->json([
            'success' => true,
            'message' => $targetUserId ? "Sandbox reset successfully for {$targetUserId}" : 'Entire classroom sandbox reset successfully',
            'cleared' => $res,
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
