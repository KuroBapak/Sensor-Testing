<?php

namespace App\Models;

use App\Enums\CardCategory;
use App\Enums\Validity;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\QrCardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
 * @property CarbonImmutable $issued_at
 * @property CarbonImmutable|null $expires_at
 * @property bool $reset_enabled
 * @property int|null $created_by
 * @property-read string $vehicle_type_name Computed columns below exist on queries built by QuotaService::query()
 * @property-read float $quota
 * @property-read float $max_capacity
 * @property-read float $used_period
 * @property-read float $adj_period
 * @property-read float $used_total
 * @property-read float $adj_total
 * @property-read float $remaining
 * @property-read float $used_in_period Added by UsageService::cardRows()
 */
#[Fillable([
    'token', 'category', 'vehicle_type_id', 'plate_number', 'validity',
    'valid_days', 'issued_at', 'expires_at', 'reset_enabled', 'created_by',
])]
class QrCard extends Model
{
    /** @use HasFactory<QrCardFactory> */
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
    #[Scope]
    protected function notExpired(Builder $query, ?CarbonInterface $now = null): void
    {
        $query->where(function (Builder $q) use ($now) {
            $q->whereNull($this->qualifyColumn('expires_at'))
                ->orWhere($this->qualifyColumn('expires_at'), '>=', ($now ?? now())->toDateTimeString());
        });
    }
}
