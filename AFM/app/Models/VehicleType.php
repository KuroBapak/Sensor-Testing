<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property float $max_capacity_liters
 * @property float $quota_liters
 */
#[Fillable(['name', 'max_capacity_liters', 'quota_liters'])]
class VehicleType extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'max_capacity_liters' => 'float',
            'quota_liters' => 'float',
        ];
    }

    /** @return HasMany<QrCard, $this> */
    public function qrCards(): HasMany
    {
        return $this->hasMany(QrCard::class);
    }
}
