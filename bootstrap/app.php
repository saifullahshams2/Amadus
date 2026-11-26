<?php

use App\Http\Middleware\EnsureAppIsInstalled;
use App\Http\Middleware\SecurityHeadersMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Only trust forwarded headers from explicitly configured proxy IPs/CIDRs.
        // Left empty when the app is directly exposed, so client-supplied
        // X-Forwarded-* headers cannot spoof IPs, hosts, or schemes.
        $trustedProxies = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('TRUSTED_PROXIES', ''))
        )));

        if ($trustedProxies !== []) {
            $middleware->trustProxies(at: $trustedProxies);
        }

        $middleware->validateCsrfTokens(except: [
            // Stateless external webhooks should be listed here if needed
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));

        // Re-validate authenticated sessions against the current password hash so
        // password changes/resets invalidate every other existing session.
        $middleware->authenticateSessions();

        $middleware->append(SecurityHeadersMiddleware::class);
        $middleware->append(EnsureAppIsInstalled::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
