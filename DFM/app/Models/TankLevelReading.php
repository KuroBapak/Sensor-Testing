<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TankLevelReading extends Model
{
    protected $guarded = [];

    protected $casts = [
        'level_liters' => 'decimal:2',
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
        'timestamp' => 'datetime',
        'server_received_at' => 'datetime',
        'rtc_out_of_bounds' => 'boolean',
    ];

    public function tank(): BelongsTo
    {
        return $this->belongsTo(Tank::class, 'tank_id', 'tank_id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(HardwareDevice::class, 'device_id', 'device_id');
    }
}
