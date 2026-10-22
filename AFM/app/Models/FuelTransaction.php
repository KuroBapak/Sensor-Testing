<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $qr_card_id
 * @property float $liters
 * @property Carbon $dispensed_at
 * @property string $source dummy|dispenser
 * @property int|null $dispenser_id
 */
#[Fillable(['qr_card_id', 'liters', 'dispensed_at', 'source', 'dispenser_id'])]
class FuelTransaction extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'liters' => 'float',
            'dispensed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<QrCard, $this> */
    public function qrCard(): BelongsTo
    {
        return $this->belongsTo(QrCard::class)->withTrashed();
    }
}
