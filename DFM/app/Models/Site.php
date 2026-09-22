<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Site extends Model
{
    protected $fillable = [
        'name',
        'timezone',
        'rfid_list_version',
        'settings',
        'settings_updated_by',
        'settings_updated_at',
    ];

    protected $casts = [
        'rfid_list_version' => 'integer',
        'settings' => 'array',
        'settings_updated_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function settingsUpdater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'settings_updated_by');
    }
}
