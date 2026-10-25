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
        $userId = Auth::user()?->username ?? 'guest';
        $records = LessonProgress::where('user_id', $userId)->get();
        return response()->json(['records' => $records]);
    }

    public function saveProgress(Request $request)
    {
        $userId = Auth::user()?->username ?? 'guest';
        $validated = $request->validate([
            'lessonId' => 'required|string',
            'moduleId' => 'required|string',
            'completedObjectives' => 'required|array',
            'score' => 'nullable|integer',
            'isComplete' => 'nullable|boolean',
        ]);

        $progress = LessonProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'lesson_id' => $validated['lessonId'],
            ],
            [
                'module_id' => $validated['moduleId'],
                'status' => ($validated['isComplete'] ?? false) ? 'completed' : 'in_progress',
                'completed_objectives_json' => $validated['completedObjectives'],
                'score' => $validated['score'] ?? 100,
                'completed_at' => ($validated['isComplete'] ?? false) ? now() : null,
            ]
        );

        return response()->json(['success' => true, 'progress' => $progress]);
    }
}
