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
            return redirect('/dashboard')->with('info', 'Application is already installed and configured.');
        }

        $requirements = $this->installerService->checkRequirements();

        return view('installer.index', [
            'requirements' => $requirements,
        ]);
    }

    /**
     * Return requirements check as JSON for dynamic verification.
     */
    public function requirements(): JsonResponse
    {
        return response()->json($this->installerService->checkRequirements());
    }

    /**
     * Process installation, migrate database, seed reference data, and create instructor account.
     */
    public function install(Request $request): JsonResponse|RedirectResponse
    {
        if ($this->installerService->isInstalled()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Application is already installed.'], 400);
            }

            return redirect('/dashboard')->with('warning', 'Application is already installed.');
        }

        $validated = $request->validate([
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

        // Run migrations & seeders
        $this->installerService->runMigrationsAndSeeders();

        // Create the primary instructor account
        $instructor = $this->installerService->createInstructor([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'office_id' => ! empty($validated['office_id']) ? strtoupper(trim($validated['office_id'])) : 'LON1A0980',
            'agent_id' => ! empty($validated['agent_id']) ? strtoupper(trim($validated['agent_id'])) : 'IN01GS',
        ]);

        // Mark installation as completed
        $this->installerService->markInstalled($instructor);

        // Authenticate the new instructor
        Auth::login($instructor, true);
        $request->session()->regenerate();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Amadeus GDS Platform installed successfully.',
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
