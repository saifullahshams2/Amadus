<?php

namespace App\Http\Controllers;

use App\Mail\SmtpTestMail;
use App\Models\LessonProgress;
use App\Models\Pnr;
use App\Models\QueueItem;
use App\Models\Scenario;
use App\Models\SmtpSetting;
use App\Models\Submission;
use App\Models\TerminalActivityLog;
use App\Models\User;
use App\Services\MailSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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
        $this->authorizeSmtpOwner();

        return $this->profileView($request, 'smtp');
    }

    /**
     * Update the login username and cascade references.
     */
    public function updateName(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:60', 'regex:/^[a-zA-Z0-9._-]+$/'],
            'current_password' => ['required', 'current_password'],
        ], [
            'username.regex' => 'The user name may only contain letters, numbers, dots, dashes and underscores.',
            'current_password.current_password' => 'The current password is incorrect.',
        ]);

        $newUsername = strtolower(trim($validated['username']));
        $oldUsername = $user->username;

        // Reserved sentinels are used internally ('guest' session identity,
        // 'system' scenario owner) and must never be claimable.
        if (in_array($newUsername, ['guest', 'system'], true)) {
            return back()->withInput()->withErrors([
                'username' => 'That user name is reserved.',
            ]);
        }

        // Case-insensitive uniqueness (SQLite/MySQL collation differences).
        $taken = User::where('id', '!=', $user->id)
            ->whereRaw('LOWER(username) = ?', [$newUsername])
            ->exists();

        if ($taken) {
            return back()->withInput()->withErrors([
                'username' => 'The user name has already been taken.',
            ]);
        }

        if ($newUsername !== $oldUsername) {
            DB::transaction(function () use ($user, $oldUsername, $newUsername) {
                $user->update(['username' => $newUsername]);

                Pnr::where('user_id', $oldUsername)->update(['user_id' => $newUsername]);
                Submission::where('user_id', $oldUsername)->update(['user_id' => $newUsername]);
                TerminalActivityLog::where('user_id', $oldUsername)->update(['user_id' => $newUsername]);
                QueueItem::where('target_user_id', $oldUsername)->update(['target_user_id' => $newUsername]);
                LessonProgress::where('user_id', $oldUsername)->update(['user_id' => $newUsername]);
                Scenario::where('created_by', $oldUsername)->update(['created_by' => $newUsername]);
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

        // Rotate the persistent login token and invalidate every other active
        // session (AuthenticateSession compares the stored password hash).
        $user->forceFill(['remember_token' => Str::random(60)])->save();

        Auth::logoutOtherDevices($validated['password']);
        $request->session()->regenerate();

        return redirect()->route('profile.password')->with('status', 'Password changed successfully. Other devices have been signed out.');
    }

    /**
     * Store the global SMTP configuration.
     */
    public function updateSmtp(Request $request): RedirectResponse
    {
        $this->authorizeSmtpOwner();

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

        if ((int) $validated['enabled'] === 1) {
            $host = trim((string) ($validated['host'] ?? ''));

            if ($this->mailSettings->isBlockedHost($host)) {
                return back()->withInput()->withErrors([
                    'host' => 'The SMTP host resolves to a private, loopback or reserved address and is not allowed.',
                ]);
            }

            if (! in_array((int) ($validated['port'] ?? 587), [25, 465, 587, 2525], true)) {
                return back()->withInput()->withErrors([
                    'port' => 'The SMTP port must be one of 25, 465, 587 or 2525.',
                ]);
            }
        }

        $settings = SmtpSetting::current();

        $hostChanged = $settings->exists
            && ($settings->host !== ($validated['host'] ?? null) || $settings->username !== ($validated['username'] ?? null));

        $settings->fill([
            'enabled' => $validated['enabled'],
            'host' => $validated['host'] ?? null,
            'port' => $validated['port'] ?? 587,
            'username' => $validated['username'] ?? null,
            'encryption' => $validated['encryption'] ?? 'tls',
            'from_address' => $validated['from_address'] ?? null,
            'from_name' => $validated['from_name'] ?? null,
        ]);

        // Only overwrite the stored secret when a new value was entered. When the
        // host or username changes without a new secret, drop the stored one so
        // an old credential is never replayed against a different server.
        if (filled($validated['password'] ?? null)) {
            $settings->password = $validated['password'];
        } elseif ($hostChanged) {
            $settings->password = null;
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
        $this->authorizeSmtpOwner();

        $settings = SmtpSetting::current();

        if (! $settings->isConfigured()) {
            return back()->withErrors(['smtp' => 'SMTP is not enabled or not fully configured yet.']);
        }

        if ($this->mailSettings->isBlockedHost((string) $settings->host)) {
            return back()->withErrors(['smtp' => 'SMTP host is not permitted.']);
        }

        $recipient = $request->input('recipient', Auth::user()->email) ?: Auth::user()->email;

        $request->validate([
            'recipient' => ['nullable', 'email', 'max:190'],
        ]);

        $this->mailSettings->apply();

        try {
            Mail::send((new SmtpTestMail($settings))->to($recipient));
        } catch (\Throwable $e) {
            // Never reflect raw transport errors (banner/host/port oracles).
            report($e);

            return back()->withErrors(['smtp' => 'Test email could not be sent. Verify the SMTP host, port and credentials.']);
        }

        return back()->with('status', 'Test email sent successfully to '.$recipient.'.');
    }

    /**
     * Only the installer-provisioned account owner may manage global SMTP.
     */
    protected function authorizeSmtpOwner(): void
    {
        $user = Auth::user();

        if (! $user) {
            abort(401);
        }

        if ($this->isOwner($user)) {
            return;
        }

        abort(403, 'Only the account owner can manage SMTP settings.');
    }

    /**
     * Determine whether the user is the account owner.
     *
     * Ownership is resolved by numeric id so no account identity needs to be
     * stored on disk. The account is the one provisioned by the installer
     * (`owner_user_id` in the lock marker), falling back to the oldest account.
     */
    public function isOwner(User $user): bool
    {
        $ownerId = null;

        $installedPath = storage_path('installed');
        if (is_file($installedPath)) {
            $payload = json_decode((string) file_get_contents($installedPath), true);
            $ownerId = $payload['owner_user_id'] ?? null;

            // Ignore a stale marker that points at an account which no longer exists.
            if (! is_numeric($ownerId) || ! User::whereKey((int) $ownerId)->exists()) {
                $ownerId = null;
            }
        }

        if ($ownerId === null) {
            $ownerId = User::query()->orderBy('id')->value('id');
        }

        return $ownerId !== null && (int) $user->getKey() === (int) $ownerId;
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
            'isOwner' => $this->isOwner($user),
        ]);
    }
}
