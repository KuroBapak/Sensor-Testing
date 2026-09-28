<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TankGeofenceState extends Model
{
    protected $primaryKey = ['tank_id', 'geofence_id'];

    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'is_inside' => 'boolean',
    ];

    public function tank(): BelongsTo
    {
        return $this->belongsTo(Tank::class, 'tank_id', 'tank_id');
    }

    public function geofence(): BelongsTo
    {
        return $this->belongsTo(Geofence::class);
    }

    /**
     * Override getKey for composite primary key.
     */
    protected function setKeysForSaveQuery($query)
    {
        return $query
            ->where('tank_id', $this->getAttribute('tank_id'))
            ->where('geofence_id', $this->getAttribute('geofence_id'));
    }
}
