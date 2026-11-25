<?php

namespace App\Http\Controllers;

use App\Mail\SmtpTestMail;
use App\Models\Pnr;
use App\Models\QueueItem;
use App\Models\SmtpSetting;
use App\Models\Submission;
use App\Models\TerminalActivityLog;
use App\Services\MailSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(protected MailSettingsService $mailSettings) {}

    /**
     * Profile main page with inline name, display name and email forms.
     */
    public function index(Request $request): View
    {
        return $this->profileView($request, 'index');
    }

    /**
     * Change Password sub-page.
     */
    public function password(Request $request): View
    {
        return $this->profileView($request, 'password');
    }

    /**
     * SMTP settings sub-page.
     */
    public function smtp(Request $request): View
    {
        return $this->profileView($request, 'smtp');
    }

    /**
     * Update the login username and cascade references.
     */
    public function updateName(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:60', 'regex:/^[a-zA-Z0-9._-]+$/', 'unique:users,username,'.$user->id],
            'current_password' => ['required', 'current_password'],
        ], [
            'username.regex' => 'The user name may only contain letters, numbers, dots, dashes and underscores.',
            'current_password.current_password' => 'The current password is incorrect.',
        ]);

        $newUsername = strtolower(trim($validated['username']));
        $oldUsername = $user->username;

        if ($newUsername !== $oldUsername) {
            DB::transaction(function () use ($user, $oldUsername, $newUsername) {
                $user->update(['username' => $newUsername]);

                Pnr::where('user_id', $oldUsername)->update(['user_id' => $newUsername]);
                Submission::where('user_id', $oldUsername)->update(['user_id' => $newUsername]);
                TerminalActivityLog::where('user_id', $oldUsername)->update(['user_id' => $newUsername]);
                QueueItem::where('target_user_id', $oldUsername)->update(['target_user_id' => $newUsername]);
            });
        }

        return redirect()->route('profile.index')->with('status', 'User name updated successfully.');
    }

    /**
     * Update the display name.
     */
    public function updateDisplayName(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
        ]);

        Auth::user()->update(['name' => trim($validated['name'])]);

        return redirect()->route('profile.index')->with('status', 'Display name updated successfully.');
    }

    /**
     * Update the account email address.
     */
    public function updateEmail(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:190', 'unique:users,email,'.$user->id],
            'current_password' => ['required', 'current_password'],
        ], [
            'current_password.current_password' => 'The current password is incorrect.',
        ]);

        $user->update(['email' => strtolower(trim($validated['email']))]);

        return redirect()->route('profile.index')->with('status', 'Email address updated successfully.');
    }

    /**
     * Change the account password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'max:64', 'confirmed', 'different:current_password'],
        ], [
            'current_password.current_password' => 'The current password is incorrect.',
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.password')->with('status', 'Password changed successfully.');
    }

    /**
     * Store the global SMTP configuration.
     */
    public function updateSmtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
            'host' => ['nullable', 'string', 'max:190', 'required_if:enabled,1'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'username' => ['nullable', 'string', 'max:190'],
            'password' => ['nullable', 'string', 'max:190'],
            'encryption' => ['nullable', 'in:tls,ssl,none'],
            'from_address' => ['nullable', 'email', 'max:190', 'required_if:enabled,1'],
            'from_name' => ['nullable', 'string', 'max:120'],
        ], [
            'host.required_if' => 'The SMTP host is required when SMTP is enabled.',
            'from_address.required_if' => 'The from address is required when SMTP is enabled.',
        ]);

        $settings = SmtpSetting::current();

        $settings->fill([
            'enabled' => $validated['enabled'],
            'host' => $validated['host'] ?? null,
            'port' => $validated['port'] ?? 587,
            'username' => $validated['username'] ?? null,
            'encryption' => $validated['encryption'] ?? 'tls',
            'from_address' => $validated['from_address'] ?? null,
            'from_name' => $validated['from_name'] ?? null,
        ]);

        // Only overwrite the stored secret when a new value was entered.
        if (filled($validated['password'] ?? null)) {
            $settings->password = $validated['password'];
        }

        if (! $settings->enabled) {
            $settings->password = $settings->password;
        }

        $settings->save();

        $this->mailSettings->apply();

        return redirect()->route('profile.smtp')->with('status', 'SMTP settings saved successfully.');
    }

    /**
     * Send a test email using the stored SMTP configuration.
     */
    public function sendTestEmail(Request $request): RedirectResponse
    {
        $settings = SmtpSetting::current();

        if (! $settings->isConfigured()) {
            return back()->withErrors(['smtp' => 'SMTP is not enabled or not fully configured yet.']);
        }

        $recipient = $request->input('recipient', Auth::user()->email) ?: Auth::user()->email;

        $validated = $request->validate([
            'recipient' => ['nullable', 'email', 'max:190'],
        ]);

        $this->mailSettings->apply();

        try {
            Mail::send((new SmtpTestMail($settings))->to($recipient));
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['smtp' => 'Test email failed: '.$e->getMessage()]);
        }

        return back()->with('status', 'Test email sent successfully to '.$recipient.'.');
    }

    /**
     * Render a profile sub-page with shared view data.
     */
    protected function profileView(Request $request, string $page): View|RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        return view('profile.'.$page, [
            'user' => $user,
            'profileSection' => $page,
            'smtp' => $page === 'smtp' ? SmtpSetting::current() : null,
            'pnrCount' => Pnr::where('user_id', $user->username)->count(),
        ]);
    }
}
