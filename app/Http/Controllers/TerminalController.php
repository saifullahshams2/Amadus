<?php

namespace App\Http\Controllers;

use App\Services\Amadeus\AmadeusParser;
use App\Services\Training\CurriculumService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TerminalController extends Controller
{
    public function __construct(
        protected AmadeusParser $parser,
        protected CurriculumService $curriculum
    ) {}

    public function index()
    {
        $modules = $this->curriculum->getModules();
        $user = Auth::user();
        return view('terminal.index', compact('modules', 'user'));
    }

    public function execute(Request $request)
    {
        $command = $request->input('command');
        $state = $request->input('state', []);
        $activeLessonId = $request->input('activeLessonId');
        $currentObjectiveIndex = $request->input('currentObjectiveIndex');

        if (!is_string($command) || trim($command) === '') {
            return response()->json(['error' => 'Command string is required'], 400);
        }

        $userId = Auth::user()?->username ?? 'guest';

        $execResult = $this->parser->execute($command, $state, $userId);

        // Training objective validation
        $objectiveCompleted = false;
        $nextObjectiveIndex = $currentObjectiveIndex;

        if ($activeLessonId && is_int($currentObjectiveIndex)) {
            $lesson = $this->curriculum->findLesson($activeLessonId);
            if ($lesson && isset($lesson['objectives'][$currentObjectiveIndex])) {
                $obj = $lesson['objectives'][$currentObjectiveIndex];
                $passed = $this->curriculum->validateObjective(
                    $command,
                    $execResult->outputLines,
                    $execResult->updatedState['activePnr'] ?? null,
                    $obj['validate'] ?? []
                );

                if ($passed && !$execResult->isError) {
                    $objectiveCompleted = true;
                    $nextObjectiveIndex = $currentObjectiveIndex + 1;
                }
            }
        }

        return response()->json([
            'outputLines' => $execResult->outputLines,
            'updatedState' => $execResult->updatedState,
            'isError' => $execResult->isError,
            'commandType' => $execResult->commandType,
            'objectiveCompleted' => $objectiveCompleted,
            'nextObjectiveIndex' => $nextObjectiveIndex,
        ]);
    }
}
