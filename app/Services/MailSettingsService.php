<?php

namespace App\Services;

use App\Models\SmtpSetting;
use Illuminate\Support\Facades\Mail;

class MailSettingsService
{
    /**
     * Apply the stored SMTP configuration to the runtime mail config.
     * Falls back to the .env defaults when no enabled settings exist.
     */
    public function apply(): void
    {
        $settings = SmtpSetting::current();

        if (! $settings->isConfigured()) {
            return;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $settings->host,
            'mail.mailers.smtp.port' => $settings->port,
            'mail.mailers.smtp.username' => $settings->username,
            'mail.mailers.smtp.password' => $settings->password,
            'mail.mailers.smtp.encryption' => $settings->encryption === 'none' ? null : $settings->encryption,
            'mail.from.address' => $settings->from_address,
            'mail.from.name' => $settings->from_name ?? config('app.name'),
        ]);

        Mail::forgetMailers();
    }
}
