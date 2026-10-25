<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QueueItem extends Model
{
    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'queue_number',
        'category',
        'office_id',
        'target_user_id',
        'pnr_locator',
        'pnr_summary',
        'message',
        'status',
    ];

    protected $casts = [
        'queue_number' => 'integer',
        'pnr_summary' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeForStudent(Builder $query, string $officeId, ?string $userId = null): Builder
    {
        return $query->where('office_id', $officeId)
            ->where(function (Builder $q) use ($userId): void {
                $q->whereNull('target_user_id');
                if ($userId) {
                    $q->orWhere('target_user_id', $userId);
                }
            });
    }
}
