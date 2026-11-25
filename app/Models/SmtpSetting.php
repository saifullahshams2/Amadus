<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmtpSetting extends Model
{
    protected $fillable = [
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_address',
        'from_name',
        'enabled',
    ];

    protected $casts = [
        'port' => 'integer',
        'enabled' => 'boolean',
        'password' => 'encrypted',
    ];

    /**
     * Singleton accessor for the global SMTP configuration row.
     */
    public static function current(): self
    {
        return static::query()->firstOrNew();
    }

    /**
     * Whether a usable SMTP configuration is stored and enabled.
     */
    public function isConfigured(): bool
    {
        return $this->enabled && filled($this->host) && filled($this->from_address);
    }
}
