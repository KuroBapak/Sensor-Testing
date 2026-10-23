<?php

use App\Enums\Validity;
use App\Models\ActivityLog;
use App\Models\FuelTransaction;
use App\Models\QrCard;
use App\Models\QuotaAdjustment;
use App\Models\VehicleType;
use App\Services\CardService;

beforeEach(function () {
    // Thursday 22 Oct 2026, 14:30 WIB.
    $this->travelTo('2026-10-22 14:30:00');
});

function createCard(array $overrides = []): QrCard
{
    return app(CardService::class)->create([
        'category' => 'internal',
        'vehicle_type_id' => VehicleType::factory()->create()->id,
        'plate_number' => 'B 1234 XYZ',
        'validity' => 'temporary',
        'valid_days' => 3,
        'reset_enabled' => true,
        ...$overrides,
    ]);
}

test('a temporary card expires at 23:59 on its last day, counting the creation day as day one', function () {
    $card = createCard(['valid_days' => 3]);

    expect($card->expires_at->toDateTimeString())->toBe('2026-10-24 23:59:59');

    $this->travelTo('2026-10-24 23:58:00');
    expect($card->fresh()->isExpired())->toBeFalse();

    $this->travelTo('2026-10-25 00:00:01');
    expect($card->fresh()->isExpired())->toBeTrue();
});

test('a temporary card expires in the time zone from settings', function () {
    setSettings(['timezone' => 'Asia/Makassar']); // WITA = WIB + 1h

    $card = createCard(['valid_days' => 1]);

    // 14:30 WIB is 15:30 WITA on the 22nd, so the card ends 23:59 WITA = 22:59 WIB.
    expect($card->expires_at->toDateTimeString())->toBe('2026-10-22 22:59:59');
});

test('a lifetime card never expires and has no expiry date', function () {
    $card = createCard(['validity' => 'lifetime', 'valid_days' => null]);

    $this->travelTo('2046-01-01 00:00:00');

    expect($card->expires_at)->toBeNull()
        ->and($card->fresh()->isExpired())->toBeFalse()
        ->and($card->validity)->toBe(Validity::Lifetime);
});

test('every card gets a random token that carries no plate or rules', function () {
    $first = createCard(['plate_number' => 'B 1 A']);
    $second = createCard(['plate_number' => 'B 2 B']);

    expect($first->token)->toHaveLength(40)
        ->and($first->token)->not->toBe($second->token)
        ->and($first->token)->not->toContain('B 1 A');
});

test('plate numbers are normalised to upper case with single spaces', function () {
    expect(CardService::normalizePlate('  kt   1234  ab '))->toBe('KT 1234 AB');
});

test('a plate is in use while a card with it is neither expired nor deleted', function () {
    $card = createCard();

    expect(app(CardService::class)->plateInUse('b 1234 xyz'))->toBeTrue();

    $this->travelTo('2026-10-25 08:00:00');
    expect(app(CardService::class)->plateInUse('B 1234 XYZ'))->toBeFalse();

    $reissued = createCard(['validity' => 'lifetime', 'valid_days' => null]);
    expect(app(CardService::class)->plateInUse('B 1234 XYZ'))->toBeTrue();

    app(CardService::class)->delete($reissued);
    expect(app(CardService::class)->plateInUse('B 1234 XYZ'))->toBeFalse();
});

test('deleting a card hides it but keeps its fuel and top-up history', function () {
    $card = QrCard::factory()->for(VehicleType::factory())->create();
    FuelTransaction::factory()->for($card)->create();
    QuotaAdjustment::factory()->for($card)->create();

    app(CardService::class)->delete($card);

    expect(QrCard::count())->toBe(0)
        ->and(QrCard::withTrashed()->count())->toBe(1)
        ->and(FuelTransaction::count())->toBe(1)
        ->and(QuotaAdjustment::count())->toBe(1);
});

test('card creation, top-ups and deletion are written to the activity log with the user', function () {
    $user = admin();
    $this->actingAs($user);
    $card = createCard(['category' => 'external', 'validity' => 'lifetime', 'valid_days' => null, 'reset_enabled' => false]);

    app(CardService::class)->topUp($card, 10, 'Tambahan', $user);
    app(CardService::class)->delete($card);

    expect(ActivityLog::orderBy('id')->pluck('action')->all())->toBe(['card.created', 'card.topped_up', 'card.deleted'])
        ->and(ActivityLog::pluck('user_id')->unique()->all())->toBe([$user->id]);
});

test('creating a card without logging leaves the activity log untouched', function () {
    createCard(['plate_number' => 'B 9 Z']);
    ActivityLog::query()->delete();

    app(CardService::class)->create([
        'category' => 'internal', 'vehicle_type_id' => VehicleType::factory()->create()->id,
        'plate_number' => 'B 8 Y', 'validity' => 'lifetime', 'reset_enabled' => true,
    ], log: false);

    expect(ActivityLog::count())->toBe(0);
});
