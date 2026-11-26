<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): JsonResponse|RedirectResponse
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = trim($credentials['username']);
        $user = User::where('username', $loginInput)
            ->orWhere('email', strtolower($loginInput))
            ->first();

        // Always run a hash check so a missing account is not distinguishable by
        // response timing from a wrong password.
        $hash = $user?->password ?? '$2y$12$rk4Tp22ASWiQZKsmuVnsqu8bvxZCpLsvXwYogF/0sK3UGSCHv9rbq';
        $passwordValid = Hash::check($credentials['password'], $hash);

        if (! $user || ! $passwordValid) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Invalid username or password'], 401);
            }

            return back()->withInput($request->only('username'))->withErrors(['username' => 'Invalid credentials']);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'redirect' => '/dashboard',
                'user' => $this->userPayload($user),
            ]);
        }

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('login')->with('info', 'You have been logged out.');
    }

    public function me(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['user' => null]);
        }

        return response()->json(['user' => $this->userPayload($user)]);
    }

    /**
     * @return array{id: int, username: string, email: string, officeId: string, agentId: string}
     */
    protected function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'officeId' => $user->office_id,
            'agentId' => $user->agent_id,
        ];
    }
}
