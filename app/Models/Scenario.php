<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scenario extends Model
{
    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'title',
        'category',
        'description',
        'difficulty',
        'passenger_profile',
        'flight_segments',
        'validation_criteria',
        'time_limit_minutes',
        'created_by',
    ];

    protected $casts = [
        'passenger_profile' => 'array',
        'flight_segments' => 'array',
        'validation_criteria' => 'array',
        'time_limit_minutes' => 'integer',
    ];

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'scenario_id', 'id');
    }
}
