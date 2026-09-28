<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnomalyLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'anomaly_time' => 'datetime',
        'position_time' => 'datetime',
        'resolved_at' => 'datetime',
        'meta' => 'json',
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
        'volume_diff' => 'decimal:2',
        'observed_percent' => 'decimal:2',
        'threshold_percent' => 'decimal:2',
    ];

    public function tank(): BelongsTo
    {
        return $this->belongsTo(Tank::class, 'tank_id', 'tank_id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(HardwareDevice::class, 'device_id', 'device_id');
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(RfidTag::class, 'tag_id', 'tag_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function geofence(): BelongsTo
    {
        return $this->belongsTo(Geofence::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(AnomalyRead::class, 'anomaly_id');
    }

    public function isReadBy(User $user): bool
    {
        return $this->reads()->where('user_id', $user->id)->exists();
    }

    /**
     * Scope: only unresolved anomalies.
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status_investigasi', ['open', 'investigating']);
    }
}
