<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Percentages are of the tank capacity. total_pct is always fuel_pct + water_pct.
 *
 * @property int $id
 * @property int $tank_id
 * @property Carbon $read_at
 * @property float $total_pct
 * @property float $fuel_pct
 * @property float $water_pct
 */
#[Fillable(['tank_id', 'read_at', 'total_pct', 'fuel_pct', 'water_pct'])]
class TankReading extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'total_pct' => 'float',
            'fuel_pct' => 'float',
            'water_pct' => 'float',
        ];
    }

    /** @return BelongsTo<Tank, $this> */
    public function tank(): BelongsTo
    {
        return $this->belongsTo(Tank::class);
    }
}
