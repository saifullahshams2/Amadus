<?php

namespace App\Http\Controllers;

use App\Services\Installer\InstallerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InstallerController extends Controller
{
    public function __construct(
        protected InstallerService $installerService
    ) {}

    /**
     * Display the installation wizard.
     */
    public function show(): View|RedirectResponse
    {
        if ($this->installerService->isInstalled()) {
            return redirect()->route('landing')->with('info', 'Application is already installed and installer is locked.');
        }

        $requirements = $this->installerService->checkRequirements();

        return view('installer.index', [
            'requirements' => $requirements,
            'defaultDriver' => config('database.default', 'sqlite'),
        ]);
    }

    /**
     * Return requirements check as JSON for dynamic verification.
     */
    public function requirements(): JsonResponse
    {
        if ($this->installerService->isInstalled()) {
            return response()->json(['error' => 'Application is already installed and installer is locked.'], 403);
        }

        return response()->json($this->installerService->checkRequirements());
    }

    /**
     * Test database connectivity dynamically from installer wizard.
     */
    public function testDatabase(Request $request): JsonResponse
    {
        if ($this->installerService->isInstalled()) {
            return response()->json(['error' => 'Application is already installed and installer is locked.'], 403);
        }

        $validated = $request->validate([
            'driver' => 'required|string|in:sqlite,mysql',
            'database_path' => 'nullable|string',
            'host' => 'nullable|string',
            'port' => 'nullable|numeric',
            'database' => 'nullable|string',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
        ]);

        $result = $this->installerService->testDatabase($validated);

        if (! $result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    /**
     * Process installation, configure database, migrate, seed reference data, and create instructor account.
     */
    public function install(Request $request): JsonResponse|RedirectResponse
    {
        if ($this->installerService->isInstalled()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Application is already installed.'], 400);
            }

            return redirect()->route('landing')->with('warning', 'Application is already installed.');
        }

        $validated = $request->validate([
            // Database parameters (defaults to current driver if omitted for testing compatibility)
            'database_driver' => 'nullable|string|in:sqlite,mysql',
            'driver' => 'nullable|string|in:sqlite,mysql',
            'db_path' => 'nullable|string',
            'database_path' => 'nullable|string',
            'db_host' => 'nullable|string',
            'host' => 'nullable|string',
            'db_port' => 'nullable|numeric',
            'port' => 'nullable|numeric',
            'db_database' => 'nullable|string|max:100',
            'database' => 'nullable|string|max:100',
            'db_username' => 'nullable|string|max:100',
            'db_password' => 'nullable|string',

            // Instructor account credentials (ONLY instructor is created)
            'name' => 'required|string|max:255',
            'username' => 'required|string|min:3|max:50|alpha_dash',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6|confirmed',
            'office_id' => 'nullable|string|max:10',
            'agent_id' => 'nullable|string|max:10',
        ]);

        $requirements = $this->installerService->checkRequirements();
        if (! $requirements['satisfied']) {
            $errorMessage = 'Server environment does not satisfy all installation requirements.';
            if ($request->wantsJson()) {
                return response()->json(['error' => $errorMessage, 'requirements' => $requirements], 422);
            }

            return back()->withInput()->withErrors(['requirements' => $errorMessage]);
        }

        // 1. Determine & test database configuration
        $dbDriver = $validated['database_driver'] ?? $validated['driver'] ?? config('database.default', 'sqlite');
        $dbConfig = [
            'driver' => $dbDriver,
            'database_path' => $validated['db_path'] ?? $validated['database_path'] ?? 'database/database.sqlite',
            'host' => $validated['db_host'] ?? $validated['host'] ?? '127.0.0.1',
            'port' => $validated['db_port'] ?? $validated['port'] ?? 3306,
            'database' => $validated['db_database'] ?? $validated['database'] ?? 'amadeus_gds',
            'username' => $validated['db_username'] ?? 'root',
            'password' => $validated['db_password'] ?? '',
        ];

        // Test database connectivity
        $testResult = $this->installerService->testDatabase($dbConfig);
        if (! $testResult['success']) {
            $dbError = $testResult['error'] ?? 'Database connection failed.';
            if ($request->wantsJson()) {
                return response()->json(['error' => $dbError, 'database_error' => true], 422);
            }

            return back()->withInput()->withErrors(['database' => $dbError]);
        }

        // 2. Configure database environment & runtime connection
        $this->installerService->configureDatabase($dbConfig);

        // Schedule .env update to run on application termination so the HTTP response finishes cleanly
        // before any server reload triggers (critical when running under `php artisan serve`).
        app()->terminating(function () use ($dbConfig) {
            $this->installerService->updateDatabaseEnv($dbConfig);
        });

        // 3. Run database migrations & seed reference catalog
        $this->installerService->runMigrationsAndSeeders();

        // 4. Create the primary flight instructor account (only account created)
        $instructor = $this->installerService->createInstructor([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'office_id' => ! empty($validated['office_id']) ? strtoupper(trim($validated['office_id'])) : 'LON1A0980',
            'agent_id' => ! empty($validated['agent_id']) ? strtoupper(trim($validated['agent_id'])) : 'IN01GS',
        ]);

        // 5. Mark installation as completed (locks the installer)
        $this->installerService->markInstalled($instructor, $dbDriver);

        // 6. Authenticate the new instructor
        Auth::login($instructor, true);
        $request->session()->regenerate();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Amadeus GDS Platform installed successfully. Installer is now locked.',
                'database' => [
                    'driver' => $dbDriver,
                ],
                'instructor' => [
                    'id' => $instructor->id,
                    'username' => $instructor->username,
                    'email' => $instructor->email,
                    'role' => $instructor->role,
                    'office_id' => $instructor->office_id,
                    'agent_id' => $instructor->agent_id,
                ],
                'redirect' => '/dashboard',
            ]);
        }

        return redirect('/dashboard')->with('success', 'Installation completed successfully! Welcome, Instructor '.$instructor->name.'.');
    }
}
