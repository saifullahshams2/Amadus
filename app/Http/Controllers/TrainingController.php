<?php

namespace App\Http\Controllers;

use App\Models\LessonProgress;
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
            'moduleId' => 'required|string',
            'completedObjectives' => 'required|array',
            'isComplete' => 'nullable|boolean',
        ]);

        $lesson = $this->curriculum->findLesson($validated['lessonId']);
        $totalObjectives = count($lesson['objectives'] ?? []);
        $completedCount = count($validated['completedObjectives']);
        $calculatedScore = $totalObjectives > 0
            ? (int) round(($completedCount / $totalObjectives) * 100)
            : 100;

        $progress = LessonProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'lesson_id' => $validated['lessonId'],
            ],
            [
                'module_id' => $validated['moduleId'],
                'status' => ($validated['isComplete'] ?? false) ? 'completed' : 'in_progress',
                'completed_objectives_json' => $validated['completedObjectives'],
                'score' => $calculatedScore,
                'completed_at' => ($validated['isComplete'] ?? false) ? now() : null,
            ]
        );

        return response()->json(['success' => true, 'progress' => $progress]);
    }
}
