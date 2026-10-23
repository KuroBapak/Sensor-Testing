<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\FuelTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $qr_card_id
 * @property float $liters
 * @property CarbonImmutable $dispensed_at
 * @property string $source dummy|dispenser
 * @property int|null $dispenser_id
 */
#[Fillable(['qr_card_id', 'liters', 'dispensed_at', 'source', 'dispenser_id'])]
class FuelTransaction extends Model
{
    /** @use HasFactory<FuelTransactionFactory> */
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
