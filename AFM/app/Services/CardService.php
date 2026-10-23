<?php

namespace App\Services;

use App\Enums\CardCategory;
use App\Enums\Validity;
use App\Models\QrCard;
use App\Models\QuotaAdjustment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

class CardService
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly ActivityLogger $logger,
    ) {}

    /** "b 1234 xyz " becomes "B 1234 XYZ". */
    public static function normalizePlate(string $plate): string
    {
        return Str::upper(trim(preg_replace('/\s+/', ' ', $plate) ?? $plate));
    }

    /**
     * The expiry moment of a Temporary card: 23:59:59 on the last valid day, in
     * the site time zone. The creation day counts as day 1.
     */
    public function expiryFor(CarbonInterface $issuedAt, int $validDays): CarbonImmutable
    {
        return CarbonImmutable::instance($issuedAt)
            ->setTimezone($this->settings->timezone())
            ->addDays($validDays - 1)
            ->setTime(23, 59, 59)
            ->setTimezone(config('app.timezone'));
    }

    /** True when a non-expired, non-deleted card already uses this plate. */
    public function plateInUse(string $plate, ?int $exceptId = null): bool
    {
        return QrCard::query()
            ->where('plate_number', self::normalizePlate($plate))
            ->notExpired()
            ->when($exceptId, fn ($q) => $q->whereKeyNot($exceptId))
            ->exists();
    }

    /**
     * @param  array<string, mixed>  $data  category, vehicle_type_id, plate_number, validity, valid_days (temporary only), reset_enabled
     */
    public function create(array $data, ?User $user = null, bool $log = true): QrCard
    {
        $validity = $data['validity'] instanceof Validity ? $data['validity'] : Validity::from($data['validity']);
        $issuedAt = now();
        $days = $validity === Validity::Temporary ? (int) $data['valid_days'] : null;

        $card = QrCard::query()->create([
            'token' => Str::random(40),
            'category' => $data['category'] instanceof CardCategory ? $data['category'] : CardCategory::from($data['category']),
            'vehicle_type_id' => $data['vehicle_type_id'],
            'plate_number' => self::normalizePlate($data['plate_number']),
            'validity' => $validity,
            'valid_days' => $days,
            'issued_at' => $issuedAt,
            'expires_at' => $days !== null ? $this->expiryFor($issuedAt, $days) : null,
            'reset_enabled' => (bool) $data['reset_enabled'],
            'created_by' => $user?->id,
        ]);

        if ($log) {
            $this->logger->log('card.created', $card, $card->plate_number, [
                'category' => $card->category->value,
                'validity' => $card->validity->value,
            ]);
        }

        return $card;
    }

    public function topUp(QrCard $card, float $liters, ?string $note, ?User $user): QuotaAdjustment
    {
        $adjustment = $card->quotaAdjustments()->create([
            'liters' => $liters,
            'note' => $note,
            'user_id' => $user?->id,
            'created_at' => now(),
        ]);

        $this->logger->log('card.topped_up', $card, $card->plate_number, [
            'liters' => $liters,
            'note' => $note,
        ]);

        return $adjustment;
    }

    /** Soft delete: history stays in usage graphs and exports. */
    public function delete(QrCard $card): void
    {
        $card->delete();

        $this->logger->log('card.deleted', $card, $card->plate_number);
    }
}
