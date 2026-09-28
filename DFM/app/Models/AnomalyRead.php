<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnomalyRead extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function anomaly(): BelongsTo
    {
        return $this->belongsTo(AnomalyLog::class, 'anomaly_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
