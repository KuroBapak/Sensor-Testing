<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property float $capacity_liters
 */
#[Fillable(['name', 'capacity_liters'])]
class Tank extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['capacity_liters' => 'float'];
    }

    /** @return HasMany<TankReading, $this> */
    public function readings(): HasMany
    {
        return $this->hasMany(TankReading::class);
    }
}
