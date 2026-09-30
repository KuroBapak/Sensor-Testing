<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class BackupSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'use_path_style' => 'boolean',
        'enabled' => 'boolean',
        'last_connection_test_ok' => 'boolean',
        'last_connection_test_at' => 'datetime',
        'last_test_backup_at' => 'datetime',
    ];

    protected function accessKeyId(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? decrypt($value) : null,
            set: fn ($value) => $value ? encrypt($value) : null,
        );
    }

    protected function secretAccessKey(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? decrypt($value) : null,
            set: fn ($value) => $value ? encrypt($value) : null,
        );
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Check if both connection test and test backup have been completed successfully
     */
    public function isFullyTested(): bool
    {
        return $this->last_connection_test_ok === true
            && $this->last_connection_test_at !== null
            && $this->last_test_backup_at !== null
            && $this->updated_at <= $this->last_connection_test_at
            && $this->updated_at <= $this->last_test_backup_at;
    }

    /**
     * Reset test status when settings change
     */
    public function resetTestStatus(): void
    {
        $this->last_connection_test_at = null;
        $this->last_connection_test_ok = null;
        $this->last_test_backup_at = null;
    }
}
