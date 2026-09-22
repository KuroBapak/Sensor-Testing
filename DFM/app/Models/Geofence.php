<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Geofence extends Model
{
    protected $guarded = [];

    protected $casts = [
        'coordinates' => 'json',
    ];
}
