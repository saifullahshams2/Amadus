<?php

namespace App\Services;

use App\Models\SmtpSetting;
use Illuminate\Support\Facades\Mail;

class MailSettingsService
{
    /**
     * IPv4 / IPv6 ranges that must never be dialled by the SMTP client.
     */
    protected const BLOCKED_CIDRS = [
        '0.0.0.0/8', '10.0.0.0/8', '100.64.0.0/10', '127.0.0.0/8',
        '169.254.0.0/16', '172.16.0.0/12', '192.0.0.0/24', '192.0.2.0/24',
        '192.168.0.0/16', '198.18.0.0/15', '198.51.100.0/24', '203.0.113.0/24',
        '224.0.0.0/4', '240.0.0.0/4', '::1/128', 'fc00::/7', 'fe80::/10',
        'ff00::/8',
    ];

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

        // Never dial known-internal addresses. Unresolvable hostnames are allowed
        // here so they fail visibly as connection errors (generic to the caller).
        if ($this->isBlockedHost((string) $settings->host)) {
            return;
        }

        $encryption = $settings->encryption ?: 'tls';
        $scheme = $encryption === 'ssl' ? 'smtps' : 'smtp';

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.scheme' => $scheme,
            'mail.mailers.smtp.host' => $settings->host,
            'mail.mailers.smtp.port' => $settings->port,
            'mail.mailers.smtp.username' => $settings->username,
            'mail.mailers.smtp.password' => $settings->password,
            // Laravel 13 maps these into Symfony's Dsn options.
            'mail.mailers.smtp.require_tls' => $encryption === 'tls',
            'mail.mailers.smtp.auto_tls' => $encryption === 'tls',
            'mail.mailers.smtp.timeout' => 10,
            'mail.from.address' => $settings->from_address,
            'mail.from.name' => $settings->from_name ?? config('app.name'),
        ]);

        Mail::forgetMailers();
    }

    /**
     * Whether the host resolves only to public IP addresses. Rejects private,
     * loopback and reserved ranges. Used before any SMTP connection is made.
     */
    public function isPublicHost(string $host): bool
    {
        $host = trim($host);

        if ($host === '') {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return $this->isPublicIp($host);
        }

        $addresses = $this->resolveHost($host);

        if ($addresses === []) {
            return false;
        }

        foreach ($addresses as $address) {
            if (! $this->isPublicIp($address)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether a host is definitely internal (IP literal or resolves to a
     * private/reserved address). Unresolvable hostnames are not treated as
     * blocked here; they are rejected later at connection time.
     */
    public function isBlockedHost(string $host): bool
    {
        $host = trim($host);

        if ($host === '') {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return ! $this->isPublicIp($host);
        }

        foreach ($this->resolveHost($host) as $address) {
            if (! $this->isPublicIp($address)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve a hostname to its IPv4/IPv6 addresses.
     *
     * @return list<string>
     */
    protected function resolveHost(string $host): array
    {
        $addresses = gethostbynamel($host) ?: [];

        if ($addresses === [] && function_exists('dns_get_record')) {
            $records = @dns_get_record($host, DNS_AAAA) ?: [];
            foreach ($records as $record) {
                if (! empty($record['ipv6'])) {
                    $addresses[] = $record['ipv6'];
                }
            }
        }

        return array_values(array_filter($addresses));
    }

    /**
     * Determine whether an IP address is public (not internal/reserved).
     */
    public function isPublicIp(string $ip): bool
    {
        // Normalise IPv4-mapped IPv6 (::ffff:127.0.0.1).
        if (str_starts_with(strtolower($ip), '::ffff:') && filter_var(substr($ip, 7), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ip = substr($ip, 7);
        }

        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }

        foreach (self::BLOCKED_CIDRS as $cidr) {
            if ($this->ipInCidr($ip, $cidr)) {
                return false;
            }
        }

        return true;
    }

    /**
     * CIDR membership test for IPv4 and IPv6.
     */
    protected function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $maskBits] = explode('/', $cidr, 2);

        $ipBin = inet_pton($ip);
        $subnetBin = inet_pton($subnet);

        if ($ipBin === false || $subnetBin === false || strlen($ipBin) !== strlen($subnetBin)) {
            return false;
        }

        $maskBits = (int) $maskBits;
        $wholeBytes = intdiv($maskBits, 8);
        $remainingBits = $maskBits % 8;

        if ($wholeBytes > 0 && substr($ipBin, 0, $wholeBytes) !== substr($subnetBin, 0, $wholeBytes)) {
            return false;
        }

        if ($remainingBits === 0) {
            return true;
        }

        $mask = chr((0xFF << (8 - $remainingBits)) & 0xFF);

        return (ord($ipBin[$wholeBytes]) & ord($mask)) === (ord($subnetBin[$wholeBytes]) & ord($mask));
    }
}
