<?php

namespace App\Models;

use App\Enums\CardCategory;
use App\Enums\Validity;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * One identity for one physical vehicle. There is no quota column: the quota is
 * always the vehicle type's current quota (see QuotaService).
 *
 * @property int $id
 * @property string $token
 * @property CardCategory $category
 * @property int $vehicle_type_id
 * @property string $plate_number
 * @property Validity $validity
 * @property int|null $valid_days
 * @property Carbon $issued_at
 * @property Carbon|null $expires_at
 * @property bool $reset_enabled
 * @property int|null $created_by
 * @property-read float|null $quota Present on queries built by QuotaService
 * @property-read float|null $remaining Present on queries built by QuotaService
 */
#[Fillable([
    'token', 'category', 'vehicle_type_id', 'plate_number', 'validity',
    'valid_days', 'issued_at', 'expires_at', 'reset_enabled', 'created_by',
])]
class QrCard extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'category' => CardCategory::class,
            'validity' => Validity::class,
            'valid_days' => 'integer',
            'issued_at' => 'datetime',
            'expires_at' => 'datetime',
            'reset_enabled' => 'boolean',
            // Computed columns added by QuotaService::query().
            'quota' => 'float',
            'max_capacity' => 'float',
            'used_period' => 'float',
            'adj_period' => 'float',
            'used_total' => 'float',
            'adj_total' => 'float',
            'remaining' => 'float',
            'used_in_period' => 'float',
        ];
    }

    /** @return BelongsTo<VehicleType, $this> */
    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class)->withTrashed();
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    /** @return HasMany<FuelTransaction, $this> */
    public function fuelTransactions(): HasMany
    {
        return $this->hasMany(FuelTransaction::class);
    }

    /** @return HasMany<QuotaAdjustment, $this> */
    public function quotaAdjustments(): HasMany
    {
        return $this->hasMany(QuotaAdjustment::class);
    }

    public function isExpired(?CarbonInterface $now = null): bool
    {
        return $this->expires_at !== null
            && $this->expires_at->lt($now ?? now());
    }

    /**
     * Cards that are not expired (lifetime cards included).
     *
     * @param  Builder<QrCard>  $query
     */
    public function scopeNotExpired(Builder $query, ?CarbonInterface $now = null): void
    {
        $query->where(function (Builder $q) use ($now) {
            $q->whereNull($this->qualifyColumn('expires_at'))
                ->orWhere($this->qualifyColumn('expires_at'), '>=', ($now ?? now())->toDateTimeString());
        });
    }
}
