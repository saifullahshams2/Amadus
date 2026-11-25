<?php

namespace App\Mail;

use App\Models\SmtpSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SmtpTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(protected SmtpSetting $settings) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return (new Envelope)
            ->from($this->settings->from_address, $this->settings->from_name ?? config('app.name'))
            ->subject('Amadus SMTP Test Email');
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(htmlString: '
            <p>This is a test email from the Amadus Training Simulator.</p>
            <p>If you received this message, your SMTP configuration is working correctly.</p>
        ');
    }
}
