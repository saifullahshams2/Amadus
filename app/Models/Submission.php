<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Submission extends Model
{
    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'scenario_id',
        'user_id',
        'status',
        'score',
        'auto_grade_results',
        'manual_override_score',
        'instructor_feedback',
        'terminal_logs',
        'pnr_record_locator',
        'pnr_snapshot',
        'time_elapsed_seconds',
        'submitted_at',
        'reviewed_at',
    ];

    protected $casts = [
        'auto_grade_results' => 'array',
        'terminal_logs' => 'array',
        'pnr_snapshot' => 'array',
        'score' => 'integer',
        'manual_override_score' => 'integer',
        'time_elapsed_seconds' => 'integer',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function scenario(): BelongsTo
    {
        return $this->belongsTo(Scenario::class, 'scenario_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'username');
    }

    /**
     * Get final score considering manual override if present.
     */
    public function getFinalScoreAttribute(): int
    {
        return $this->manual_override_score !== null ? $this->manual_override_score : $this->score;
    }
}
