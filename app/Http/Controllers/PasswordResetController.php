<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Display the form to request a password reset link.
     */
    public function showForgotPassword()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.forgot-password');
    }

    /**
     * Send a password reset link to the given user.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
        ]);

        $input = trim($request->input('email'));

        // Allow looking up by email or username
        $user = filter_var($input, FILTER_VALIDATE_EMAIL)
            ? User::where('email', strtolower($input))->first()
            : User::where('username', strtolower($input))->first();

        if (! $user) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'We could not find an account with that email address or callsign.',
                ], 404);
            }

            return back()->withInput()->withErrors([
                'email' => 'We could not find an account with that email address or callsign.',
            ]);
        }

        $token = Password::broker()->createToken($user);
        $user->sendPasswordResetNotification($token);
        $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset link dispatched successfully.',
                'email' => $user->email,
                'reset_url' => $resetUrl,
            ]);
        }

        return back()->with([
            'status' => 'A password reset link has been dispatched to your email address.',
            'reset_link' => $resetUrl,
        ]);
    }

    /**
     * Display the password reset view for the given token.
     */
    public function showResetPassword(Request $request, string $token)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', old('email', '')),
        ]);
    }

    /**
     * Reset the user's password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Your password has been successfully reset. Please sign in.',
                ]);
            }

            return redirect()->route('login')->with('info', 'Your password has been successfully reset. You may now sign in with your new credentials.');
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => __($status),
            ], 422);
        }

        return back()->withInput($request->only('email'))->withErrors([
            'email' => __($status),
        ]);
    }
}
