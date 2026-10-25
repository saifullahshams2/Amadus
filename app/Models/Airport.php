<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    protected $primaryKey = 'iata';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'iata',
        'icao',
        'name',
        'city',
        'country',
        'lat',
        'lon',
        'tz',
    ];
}
