<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsInstructor
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // 1. Unauthenticated requests
        if (! $user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Unauthenticated. Instructor role required.'], 401);
            }

            return redirect()->guest(route('landing'))->withErrors(['error' => 'Instructor access required.']);
        }

        // 2. Authenticated user lacks instructor role (e.g. student)
        if ($user->role !== 'instructor') {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Forbidden. Instructor access required.'], 403);
            }

            abort(403, 'Forbidden. Instructor access required.');
        }

        return $next($request);
    }
}
