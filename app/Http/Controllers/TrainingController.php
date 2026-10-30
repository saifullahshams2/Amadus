<?php

namespace App\Http\Controllers;

use App\Models\LessonProgress;
use App\Models\Pnr;
use App\Models\TerminalActivityLog;
use App\Services\Training\CurriculumService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrainingController extends Controller
{
    public function __construct(
        protected CurriculumService $curriculum
    ) {}

    public function modules()
    {
        return response()->json([
            'modules' => $this->curriculum->getModules(),
        ]);
    }

    public function progress(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $records = LessonProgress::where('user_id', $user->username)->get();

        return response()->json(['records' => $records]);
    }

    public function saveProgress(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $userId = $user->username;
        $validated = $request->validate([
            'lessonId' => 'required|string',
            'completedObjectives' => 'required|array',
            'isComplete' => 'nullable|boolean',
        ]);

        $lesson = $this->curriculum->findLesson($validated['lessonId']);

        if ($lesson === null) {
            return response()->json([
                'error' => 'Unknown lesson identifier.',
                'lessonId' => $validated['lessonId'],
            ], 422);
        }

        $objectives = $lesson['objectives'] ?? [];
        $objectiveIds = array_map(fn (array $objective) => $objective['id'], $objectives);

        // The client may claim objectives, but only objectives that exist in the
        // lesson and are backed by this cadet's own terminal activity are credited.
        $claimedObjectiveIds = array_values(array_intersect(
            $objectiveIds,
            array_map('strval', $validated['completedObjectives'])
        ));

        $terminalLogs = TerminalActivityLog::where('user_id', $userId)
            ->where('is_error', false)
            ->get(['command', 'output_lines']);

        $latestPnr = Pnr::where('user_id', $userId)->orderByDesc('updated_at')->first();
        $pnrData = $latestPnr?->data_json;

        $verifiedObjectiveIds = [];

        foreach ($objectives as $objective) {
            if (! in_array($objective['id'], $claimedObjectiveIds, true)) {
                continue;
            }

            foreach ($terminalLogs as $terminalLog) {
                $isSatisfied = $this->curriculum->validateObjective(
                    (string) $terminalLog->command,
                    (array) $terminalLog->output_lines,
                    $pnrData,
                    $objective['validate'] ?? []
                );

                if ($isSatisfied) {
                    $verifiedObjectiveIds[] = $objective['id'];
                    break;
                }
            }
        }

        $existingProgress = LessonProgress::where('user_id', $userId)
            ->where('lesson_id', $lesson['id'])
            ->first();

        // Progress is monotonic: objectives already credited for this lesson stay
        // credited, so a sandbox log reset never erases completed coursework.
        $previouslyCreditedIds = $existingProgress
            ? array_values(array_intersect(
                $objectiveIds,
                array_map('strval', (array) $existingProgress->completed_objectives_json)
            ))
            : [];

        $completedObjectives = array_values(array_unique(array_merge(
            $previouslyCreditedIds,
            $verifiedObjectiveIds
        )));

        $totalObjectives = count($objectiveIds);
        $calculatedScore = $totalObjectives > 0
            ? (int) round((count($completedObjectives) / $totalObjectives) * 100)
            : 0;
        $calculatedScore = max(0, min(100, $calculatedScore));

        $isComplete = $totalObjectives > 0 && count($completedObjectives) === $totalObjectives;

        $progress = LessonProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'lesson_id' => $lesson['id'],
            ],
            [
                'module_id' => $lesson['moduleId'],
                'status' => $isComplete ? 'completed' : 'in_progress',
                'completed_objectives_json' => $completedObjectives,
                'score' => $calculatedScore,
                'completed_at' => $isComplete ? ($existingProgress?->completed_at ?? now()) : null,
            ]
        );

        return response()->json(['success' => true, 'progress' => $progress]);
    }
}
