<?php

namespace App\Models;

use Database\Factories\VehicleTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
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
    /** @use HasFactory<VehicleTypeFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'max_capacity_liters' => 'float',
            'quota_liters' => 'float',
        ];
    }

    /**
     * @param  Builder<VehicleType>  $query
     */
    #[Scope]
    protected function search(Builder $query, string $term): void
    {
        if ($term !== '') {
            $query->where('name', 'like', '%'.addcslashes($term, '%_\\').'%');
        }
    }

    /** @return HasMany<QrCard, $this> */
    public function qrCards(): HasMany
    {
        return $this->hasMany(QrCard::class);
    }
}
