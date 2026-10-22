<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get the service cluster that owns this service.
     *
     * @return BelongsTo<ServiceCluster, $this>
     */
    public function serviceCluster(): BelongsTo
    {
        return $this->belongsTo(ServiceCluster::class);
    }

    /**
     * Get the works associated with this service.
     *
     * @return HasMany<Work, $this>
     */
    public function works(): HasMany
    {
        return $this->hasMany(Work::class)->orderBy('sort_order');
    }
}
