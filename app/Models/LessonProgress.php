<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LessonProgress extends Model
{
    protected $table = 'lesson_progress';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'lesson_id',
        'module_id',
        'status',
        'completed_objectives_json',
        'score',
        'attempts',
        'completed_at',
    ];

    protected $casts = [
        'completed_objectives_json' => 'array',
        'completed_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
