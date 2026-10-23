<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\QuotaAdjustmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A manual top-up of a card. The top-up log.
 *
 * @property int $id
 * @property int $qr_card_id
 * @property float $liters
 * @property string|null $note
 * @property int|null $user_id
 * @property CarbonImmutable $created_at
 */
#[Fillable(['qr_card_id', 'liters', 'note', 'user_id', 'created_at'])]
class QuotaAdjustment extends Model
{
    /** @use HasFactory<QuotaAdjustmentFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'liters' => 'float',
            'created_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<QrCard, $this> */
    public function qrCard(): BelongsTo
    {
        return $this->belongsTo(QrCard::class)->withTrashed();
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
