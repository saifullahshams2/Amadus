<?php

namespace App\Http\Controllers;

use App\Models\TerminalActivityLog;
use App\Services\Amadeus\AmadeusParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TerminalController extends Controller
{
    public function __construct(
        protected AmadeusParser $parser
    ) {}

    public function index()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        return view('terminal.index', compact('user'));
    }

    public function execute(Request $request)
    {
        $user = Auth::user();

        // 1. Validate input command and state shape
        $validated = $request->validate([
            'command' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'array'],
        ]);

        $command = $validated['command'];
        $state = is_array($validated['state'] ?? null) ? $validated['state'] : [];

        // Bound the client state payload so it cannot be abused to exhaust
        // worker resources or introduce deeply nested structures.
        if (count($state) > 40 || strlen((string) json_encode($state)) > 200_000) {
            throw ValidationException::withMessages([
                'state' => 'The terminal state payload is too large.',
            ]);
        }

        $state = $this->normaliseState($state);

        $userId = $user?->username ?? 'guest';

        // Bind office and agent identity to authenticated user so client state cannot spoof tenant identity
        if ($user) {
            if (! empty($user->office_id)) {
                $state['officeId'] = $user->office_id;
            }
            if (! empty($user->agent_id)) {
                $state['agentId'] = $user->agent_id;
            }
        }

        $startTime = microtime(true);

        try {
            $execResult = $this->parser->execute($command, $state, $userId);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            // Malformed client state must not surface as an unhandled HTTP 500.
            report($e);

            return response()->json([
                'outputLines' => ['INVALID TERMINAL STATE - REQUEST COULD NOT BE PROCESSED'],
                'updatedState' => $state,
                'isError' => true,
                'commandType' => 'STATE_ERROR',
            ]);
        }

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

    /**
     * Normalise the client-supplied state so nested elements are the expected type.
     *
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    protected function normaliseState(array $state): array
    {
        $pnrArrayKeys = [
            'passengers', 'segments', 'contacts', 'tickets', 'tsts',
            'specialServiceRequests', 'remarks', 'options', 'seatRequests', 'history',
        ];

        foreach ($pnrArrayKeys as $key) {
            if (array_key_exists($key, $state)) {
                if (! is_array($state[$key])) {
                    $state[$key] = [];
                }
            }
        }

        if (array_key_exists('activePnr', $state) && $state['activePnr'] !== [] && $state['activePnr'] !== null) {
            if (! is_array($state['activePnr'])) {
                $state['activePnr'] = [];
            } else {
                foreach ($pnrArrayKeys as $key) {
                    if (! array_key_exists($key, $state['activePnr']) || ! is_array($state['activePnr'][$key])) {
                        $state['activePnr'][$key] = [];
                    }
                }

                foreach (['passengers', 'segments', 'contacts', 'tickets', 'tsts'] as $key) {
                    $state['activePnr'][$key] = array_values(array_filter(
                        $state['activePnr'][$key],
                        fn ($element) => is_array($element)
                    ));
                }
            }
        }

        // Scalar/array expectations for the remaining top-level state keys.
        foreach (['activeProfile', 'officeBriefing'] as $key) {
            if (array_key_exists($key, $state) && ! is_array($state[$key]) && $state[$key] !== null) {
                $state[$key] = [];
            }
        }

        foreach (['profilesList', 'lastPnrList'] as $key) {
            if (array_key_exists($key, $state) && ! is_array($state[$key])) {
                $state[$key] = [];
            } elseif (isset($state[$key])) {
                $state[$key] = array_values(array_filter($state[$key], fn ($element) => is_array($element)));
            }
        }

        if (array_key_exists('lastAvailabilityDisplay', $state) && ! is_array($state['lastAvailabilityDisplay']) && $state['lastAvailabilityDisplay'] !== null) {
            $state['lastAvailabilityDisplay'] = null;
        }

        if (array_key_exists('activeSplit', $state) && ! is_array($state['activeSplit']) && $state['activeSplit'] !== null) {
            $state['activeSplit'] = null;
        }

        if (array_key_exists('lastCommand', $state) && ! is_string($state['lastCommand'])) {
            $state['lastCommand'] = '';
        }

        return $state;
    }
}
