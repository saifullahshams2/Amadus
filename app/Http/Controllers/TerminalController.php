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
        $user = Auth::user();

        return view('terminal.index', compact('user'));
    }

    public function execute(Request $request)
    {
        $command = $request->input('command');
        $state = $request->input('state', []);

        if (! is_string($command) || trim($command) === '') {
            return response()->json(['error' => 'Command string is required'], 400);
        }

        $userId = Auth::user()?->username ?? 'guest';

        $startTime = microtime(true);
        $execResult = $this->parser->execute($command, $state, $userId);
        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        // Record terminal activity for classroom spectating & learning analytics
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

        return response()->json([
            'outputLines' => $execResult->outputLines,
            'updatedState' => $execResult->updatedState,
            'isError' => $execResult->isError,
            'commandType' => $execResult->commandType,
        ]);
    }
}
