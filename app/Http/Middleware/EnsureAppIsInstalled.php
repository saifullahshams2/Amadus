<?php

namespace App\Http\Middleware;

use App\Services\Installer\InstallerService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAppIsInstalled
{
    /**
     * Whether testing environment bypasses installation checks.
     */
    public static bool $bypassInTesting = true;

    public function __construct(
        protected InstallerService $installerService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('testing') && static::$bypassInTesting) {
            return $next($request);
        }

        $isInstalled = $this->installerService->isInstalled();
        $isInstallRoute = $request->is('install') || $request->is('install/*');

        // If installed and trying to visit installer, redirect away and lock access
        if ($isInstalled && $isInstallRoute) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Application is already installed and installer is locked.'], 403);
            }

            return redirect()->route('landing')->with('info', 'Application is already installed and installer is locked.');
        }

        // If not installed and trying to access regular application pages, redirect to installer
        if (! $isInstalled && ! $isInstallRoute) {
            // Exempt health check, static files or debug endpoints if needed
            if ($request->is('up') || $request->is('_debugbar*') || $request->is('*.png') || $request->is('*.ico') || $request->is('*.svg') || $request->is('*.css') || $request->is('*.js') || $request->is('*.jpg') || $request->is('*.webp')) {
                return $next($request);
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Application is not installed. Please run setup at /install.',
                    'install_url' => url('/install'),
                ], 503);
            }

            return redirect('/install');
        }

        return $next($request);
    }
}
