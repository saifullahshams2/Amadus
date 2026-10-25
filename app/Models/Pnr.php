<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pnr extends Model
{
    protected $primaryKey = 'record_locator';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'record_locator',
        'user_id',
        'office_id',
        'status',
        'data_json',
    ];

    protected $casts = [
        'data_json' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'username');
    }
}
