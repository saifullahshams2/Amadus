<?php

namespace App\Http\Controllers;

use App\Models\TerminalActivityLog;
use App\Services\Amadeus\AmadeusParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TerminalController extends Controller
{
    public function __construct(
        protected AmadeusParser $parser
    ) {}

    public function index()
    {
        if (! Auth::check()) {
            return redirect()->route('landing');
        }

        $user = Auth::user();

        if ($user->role === 'student' && ! $user->is_approved) {
            return redirect()->route('auth.pending-approval');
        }

        return view('terminal.index', compact('user'));
    }

    public function execute(Request $request)
    {
        $user = Auth::user();

        // 1. If unapproved student tries to execute commands, reject
        if ($user && $user->role === 'student' && ! $user->is_approved) {
            return response()->json(['error' => 'Cadet account is awaiting instructor approval.'], 403);
        }

        // 2. Validate input command and state shape
        $validated = $request->validate([
            'command' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'array'],
        ]);

        $command = $validated['command'];
        $state = $validated['state'] ?? [];

        $userId = $user?->username ?? 'guest';

        $startTime = microtime(true);
        $execResult = $this->parser->execute($command, $state, $userId);
        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        // Record terminal activity only for authenticated users (prevents guest log flooding)
        if ($userId !== 'guest') {
            try {
                TerminalActivityLog::create([
                    'user_id' => $userId,
                    'command' => $command,
                    'output_lines' => $execResult->outputLines,
                    'is_error' => $execResult->isError,
                    'command_type' => $execResult->commandType,
                    'execution_time_ms' => $durationMs,
                ]);
            } catch (\Throwable $e) {
                // Silently ignore log write errors during high volume/testing
            }
        }

        return response()->json([
            'outputLines' => $execResult->outputLines,
            'updatedState' => $execResult->updatedState,
            'isError' => $execResult->isError,
            'commandType' => $execResult->commandType,
        ]);
    }
}
