<?php

namespace App\Services\Training;

use App\Models\Scenario;
use App\Models\Submission;
use Illuminate\Support\Collection;

class ScenarioService
{
    /**
     * Get all scenarios with submission state for a specific student.
     *
     * @return Collection<int, array>
     */
    public function getScenariosForStudent(string $userId): Collection
    {
        $scenarios = Scenario::orderBy('created_at', 'asc')->get();
        $submissions = Submission::where('user_id', $userId)->get()->keyBy('scenario_id');

        return $scenarios->map(function (Scenario $sc) use ($submissions) {
            $sub = $submissions->get($sc->id);

            return [
                'id' => $sc->id,
                'title' => $sc->title,
                'category' => $sc->category,
                'description' => $sc->description,
                'difficulty' => $sc->difficulty,
                'passenger_profile' => $sc->passenger_profile,
                'flight_segments' => $sc->flight_segments,
                'validation_criteria' => $sc->validation_criteria,
                'time_limit_minutes' => $sc->time_limit_minutes,
                'created_by' => $sc->created_by,
                'status' => $sub?->status ?? 'not_started',
                'score' => $sub?->final_score ?? 0,
                'auto_grade_results' => $sub?->auto_grade_results,
                'manual_override_score' => $sub?->manual_override_score,
                'instructor_feedback' => $sub?->instructor_feedback,
                'time_elapsed_seconds' => $sub?->time_elapsed_seconds ?? 0,
                'submitted_at' => $sub?->submitted_at?->toIso8601String(),
                'reviewed_at' => $sub?->reviewed_at?->toIso8601String(),
                'submission_id' => $sub?->id,
            ];
        });
    }

    /**
     * Automated grading engine evaluating mandatory PRINT elements and scenario criteria.
     *
     * @return array{score: int, maxScore: int, passed: bool, checks: array, summary: string, elementsPassedCount: int, elementsTotalCount: int}
     */
    public function autoGrade(Scenario $scenario, ?array $pnr, array $terminalLogs = []): array
    {
        $checks = [];
        $criteria = $scenario->validation_criteria ?? [];
        $mandatory = $criteria['mandatoryPrint'] ?? [
            'name' => true,
            'itinerary' => true,
            'contact' => true,
            'ticketing' => true,
            'receivedFrom' => true,
        ];

        // 1. Check: Name Element (NM)
        if (! empty($mandatory['name'])) {
            $passengers = $pnr['passengers'] ?? [];
            $paxPassed = count($passengers) > 0;
            $details = $paxPassed
                ? count($passengers).' passenger(s) correctly registered'
                : 'Missing passenger name element (NM)';

            // Check infant if scenario requires infant
            if ($scenario->category === 'Infant-in-lap' && $paxPassed) {
                $hasInfant = false;
                foreach ($passengers as $p) {
                    if (($p['type'] ?? '') === 'INF' || ! empty($p['infantName']) || str_contains(strtoupper($p['lastName'] ?? ''), 'INF')) {
                        $hasInfant = true;
                        break;
                    }
                }
                if (! $hasInfant) {
                    $paxPassed = false;
                    $details = 'Passenger name found but infant element (INF) is missing';
                }
            }

            $checks[] = [
                'code' => 'NM',
                'name' => 'Passenger Name Element (NM)',
                'passed' => $paxPassed,
                'pointsAwarded' => $paxPassed ? 20 : 0,
                'maxPoints' => 20,
                'details' => $details,
            ];
        }

        // 2. Check: Itinerary Segments (SS)
        if (! empty($mandatory['itinerary'])) {
            $segments = $pnr['segments'] ?? [];
            $minSegs = (int) ($criteria['minSegments'] ?? 1);
            $segPassed = count($segments) >= $minSegs;
            $details = $segPassed
                ? count($segments)." segment(s) reserved (required: {$minSegs})"
                : 'Insufficient booked segments ('.count($segments)."/{$minSegs})";

            $checks[] = [
                'code' => 'SS',
                'name' => 'Itinerary Segments (SS)',
                'passed' => $segPassed,
                'pointsAwarded' => $segPassed ? 20 : 0,
                'maxPoints' => 20,
                'details' => $details,
            ];
        }

        // 3. Check: Contact / Phone Element (AP/APE)
        if (! empty($mandatory['contact']) || ! empty($mandatory['phone'])) {
            $contacts = $pnr['contacts'] ?? [];
            $contactPassed = count($contacts) > 0;
            $details = $contactPassed
                ? count($contacts).' agency/traveler contact(s) added'
                : 'Missing contact element (AP phone or APE email)';

            $checks[] = [
                'code' => 'AP',
                'name' => 'Contact Element (AP / APE)',
                'passed' => $contactPassed,
                'pointsAwarded' => $contactPassed ? 15 : 0,
                'maxPoints' => 15,
                'details' => $details,
            ];
        }

        // 4. Check: Ticketing Arrangement (TK)
        if (! empty($mandatory['ticketing'])) {
            $tk = $pnr['ticketing'] ?? null;
            $tkPassed = ! empty($tk);
            $details = $tkPassed
                ? 'Ticketing arrangement set: '.(is_array($tk) ? ($tk['raw'] ?? 'OK') : $tk)
                : 'Missing ticketing arrangement element (TKTL or TKOK)';

            $checks[] = [
                'code' => 'TK',
                'name' => 'Ticketing Element (TK)',
                'passed' => $tkPassed,
                'pointsAwarded' => $tkPassed ? 15 : 0,
                'maxPoints' => 15,
                'details' => $details,
            ];
        }

        // 5. Check: Received From (RF)
        if (! empty($mandatory['receivedFrom'])) {
            $rf = $pnr['receivedFrom'] ?? null;
            $rfPassed = ! empty($rf);
            $details = $rfPassed
                ? "Received from element validated: {$rf}"
                : 'Missing received-from element (RF)';

            $checks[] = [
                'code' => 'RF',
                'name' => 'Received From Element (RF)',
                'passed' => $rfPassed,
                'pointsAwarded' => $rfPassed ? 15 : 0,
                'maxPoints' => 15,
                'details' => $details,
            ];
        }

        // 6. Check: Pricing / Stored TST (FXP)
        if (! empty($criteria['requireTst'])) {
            $hasTst = ! empty($pnr['tst']) || (($pnr['tstNumber'] ?? 0) > 0);
            $checks[] = [
                'code' => 'FXP',
                'name' => 'Fare Calculation & Stored TST',
                'passed' => $hasTst,
                'pointsAwarded' => $hasTst ? 10 : 0,
                'maxPoints' => 10,
                'details' => $hasTst ? 'Transitional Stored Ticket (TST) created' : 'FXP pricing not executed',
            ];
        }

        // 7. Check: Electronic Ticketing (TTP)
        if (! empty($criteria['requireETicket'])) {
            $isTicketed = ($pnr['status'] ?? '') === 'TICKETED' || ! empty($pnr['eTickets']);
            $checks[] = [
                'code' => 'TTP',
                'name' => 'Electronic Ticket Issue (TTP)',
                'passed' => $isTicketed,
                'pointsAwarded' => $isTicketed ? 5 : 0,
                'maxPoints' => 5,
                'details' => $isTicketed ? 'Electronic ticket generated successfully' : 'TTP ticket issue incomplete',
            ];
        }

        // Calculate scores
        $totalMax = array_sum(array_column($checks, 'maxPoints'));
        $totalAwarded = array_sum(array_column($checks, 'pointsAwarded'));

        // Normalize to 100
        $normalizedScore = ($totalMax > 0) ? (int) round(($totalAwarded / $totalMax) * 100) : 0;

        // Command efficiency deduction / bonus
        if (! empty($terminalLogs)) {
            $totalCommands = count($terminalLogs);
            $errors = count(array_filter($terminalLogs, fn ($l) => ! empty($l['is_error'])));
            $errorRate = $totalCommands > 0 ? ($errors / $totalCommands) : 0;

            if ($errorRate > 0.40) {
                $normalizedScore = max(0, $normalizedScore - 10);
            } elseif ($errorRate === 0 && $totalCommands >= 5 && $normalizedScore >= 80) {
                $normalizedScore = min(100, $normalizedScore + 5);
            }
        }

        $passedCount = count(array_filter($checks, fn ($c) => $c['passed']));
        $totalCount = count($checks);
        $passed = ($normalizedScore >= 70);

        return [
            'score' => $normalizedScore,
            'maxScore' => 100,
            'passed' => $passed,
            'elementsPassedCount' => $passedCount,
            'elementsTotalCount' => $totalCount,
            'checks' => $checks,
            'summary' => "{$passedCount}/{$totalCount} validation criteria met ({$normalizedScore}% score)",
        ];
    }

    /**
     * Submit a scenario for grading.
     */
    public function submitScenario(
        string $userId,
        string $scenarioId,
        ?array $pnrData,
        array $terminalLogs = [],
        int $timeElapsed = 0
    ): Submission {
        $scenario = Scenario::findOrFail($scenarioId);
        $gradeResult = $this->autoGrade($scenario, $pnrData, $terminalLogs);

        return Submission::updateOrCreate(
            [
                'scenario_id' => $scenarioId,
                'user_id' => $userId,
            ],
            [
                'status' => 'submitted',
                'score' => $gradeResult['score'],
                'auto_grade_results' => $gradeResult,
                'terminal_logs' => $terminalLogs,
                'pnr_record_locator' => $pnrData['record_locator'] ?? ($pnrData['recordLocator'] ?? null),
                'pnr_snapshot' => $pnrData,
                'time_elapsed_seconds' => $timeElapsed,
                'submitted_at' => now(),
            ]
        );
    }

    /**
     * Manual score override and inline feedback by instructor.
     */
    public function overrideGrade(
        string $submissionId,
        int $newScore,
        ?string $feedback = null
    ): Submission {
        $sub = Submission::findOrFail($submissionId);
        $sub->update([
            'manual_override_score' => max(0, min(100, $newScore)),
            'instructor_feedback' => $feedback,
            'status' => 'reviewed',
            'reviewed_at' => now(),
        ]);

        return $sub;
    }

    /**
     * Delete a scenario and all associated student submissions.
     */
    public function deleteScenario(string $scenarioId): bool
    {
        $scenario = Scenario::find($scenarioId);
        if (! $scenario) {
            return false;
        }

        // Purge associated student submissions
        Submission::where('scenario_id', $scenarioId)->delete();

        return (bool) $scenario->delete();
    }
}
