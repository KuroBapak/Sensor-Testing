<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tank extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'tank_id';

    protected $guarded = [];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function hardwareDevices(): HasMany
    {
        return $this->hasMany(HardwareDevice::class, 'tank_id', 'tank_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'tank_id', 'tank_id');
    }

    public function levelReadings(): HasMany
    {
        return $this->hasMany(TankLevelReading::class, 'tank_id', 'tank_id');
    }

    /**
     * Latest level reading (for current fuel level).
     */
    public function latestReading(): HasOne
    {
        return $this->hasOne(TankLevelReading::class, 'tank_id', 'tank_id')
            ->latestOfMany('timestamp');
    }

    public function rfidTags(): HasMany
    {
        return $this->hasMany(RfidTag::class, 'tank_id', 'tank_id');
    }

    public function anomalyLogs(): HasMany
    {
        return $this->hasMany(AnomalyLog::class, 'tank_id', 'tank_id');
    }
}
