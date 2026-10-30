<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect('/');
        }

        return view('auth.login');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect('/');
        }

        return view('auth.register');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = trim($credentials['username']);
        $user = User::where('username', $loginInput)
            ->orWhere('email', strtolower($loginInput))
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Invalid username or password'], 401);
            }

            return back()->withInput()->withErrors(['username' => 'Invalid credentials']);
        }

        // Check if student cadet account has been approved by instructor
        if ($user->role === 'student' && ! $user->is_approved) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'Your cadet account is awaiting flight instructor approval before access can be granted.',
                    'pending_approval' => true,
                ], 403);
            }

            return back()->withInput()->withErrors([
                'username' => 'Your cadet account is pending instructor approval. Please contact your flight instructor.',
            ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'redirect' => '/dashboard',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'officeId' => $user->office_id,
                    'agentId' => $user->agent_id,
                ],
            ]);
        }

        return redirect()->intended('/dashboard');
    }

    public function register(Request $request)
    {
        $passwordRules = $request->has('password_confirmation')
            ? 'required|string|min:6|confirmed'
            : 'required|string|min:6';

        // Normalise before validating so the unique rules run against the value
        // that will actually be stored (usernames and emails are lower-cased).
        $request->merge([
            'username' => is_string($request->input('username')) ? strtolower(trim($request->input('username'))) : $request->input('username'),
            'email' => is_string($request->input('email')) ? strtolower(trim($request->input('email'))) : $request->input('email'),
        ]);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'username' => 'required|string|min:3|max:50|alpha_dash|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => $passwordRules,
        ]);

        $cleanUsername = strtolower(trim($validated['username']));

        $user = User::create([
            'username' => $cleanUsername,
            'name' => ! empty($validated['name']) ? trim($validated['name']) : $cleanUsername,
            'email' => strtolower(trim($validated['email'])),
            'password' => Hash::make($validated['password']),
            'role' => 'student', // Strictly enforced as student
            'is_approved' => false, // Requires instructor approval
            'office_id' => 'LON1A0980',
            'agent_id' => '1234AB',
        ]);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'pending_approval' => true,
                'message' => 'Cadet registration received. Your account is pending instructor approval.',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'is_approved' => false,
                ],
            ], 201);
        }

        return redirect('/pending-approval')->with('registered_username', $user->username);
    }

    public function pendingApproval(Request $request)
    {
        return view('auth.pending-approval');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['success' => true]);
        }

        return redirect('/')->with('info', 'You have been logged out.');
    }

    public function me(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['user' => null]);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'officeId' => $user->office_id,
                'agentId' => $user->agent_id,
            ],
        ]);
    }
}
