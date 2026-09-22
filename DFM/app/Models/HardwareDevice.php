<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HardwareDevice extends Model
{
    protected $primaryKey = 'device_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public function tank()
    {
        return $this->belongsTo(Tank::class, 'tank_id', 'tank_id');
    }
}
