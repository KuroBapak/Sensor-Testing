<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'server_received_at' => 'datetime',
        'liters' => 'decimal:2',
    ];

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(HardwareDevice::class, 'device_id', 'device_id');
    }

    public function tank(): BelongsTo
    {
        return $this->belongsTo(Tank::class, 'tank_id', 'tank_id');
    }

    public function mainTank(): BelongsTo
    {
        return $this->belongsTo(Tank::class, 'main_tank_id', 'tank_id');
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(RfidTag::class, 'tag_id', 'tag_id');
    }
}
