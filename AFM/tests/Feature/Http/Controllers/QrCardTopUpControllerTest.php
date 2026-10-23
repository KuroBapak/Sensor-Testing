<?php

use App\Models\FuelTransaction;
use App\Models\QrCard;
use App\Models\QuotaAdjustment;
use App\Models\VehicleType;
use App\Services\QuotaService;

beforeEach(function () {
    // Thursday 22 Oct 2026, 14:30 WIB.
    $this->travelTo('2026-10-22 14:30:00');
});

test('guests are redirected to the login page', function () {
    $card = QrCard::factory()->create();

    $this->post(route('qr-cards.top-ups.store', $card), ['liters' => 5])->assertRedirect(route('login'));

    expect(QuotaAdjustment::count())->toBe(0);
});

test('a top-up with reset on helps the current period only', function () {
    $card = QrCard::factory()->for(VehicleType::factory()->quota(5))->create();

    $this->actingAs(admin())
        ->post(route('qr-cards.top-ups.store', $card), ['liters' => 6, 'note' => 'Pekerjaan tambahan'])
        ->assertSessionHasNoErrors()
        ->assertInertiaFlash('toast.message', 'Top-up 6,0 L untuk '.$card->plate_number.' berhasil.');

    expect(app(QuotaService::class)->remaining($card))->toBe(11.0);

    $this->travelTo('2026-10-23 08:00:00');
    expect(app(QuotaService::class)->remaining($card))->toBe(5.0);
});

test('a top-up with reset off stays in the pool', function () {
    $card = QrCard::factory()->resetOff()->for(VehicleType::factory()->quota(5))->create();

    $this->actingAs(admin())->post(route('qr-cards.top-ups.store', $card), ['liters' => 6]);

    $this->travelTo('2026-11-15 08:00:00');
    expect(app(QuotaService::class)->remaining($card))->toBe(11.0);
});

test('a top-up puts a depleted lifetime card with reset off back in use', function () {
    $card = QrCard::factory()->resetOff()->for(VehicleType::factory()->quota(20))->create();
    FuelTransaction::factory()->for($card)->create(['liters' => 20, 'dispensed_at' => '2026-10-01 09:00:00']);
    expect(app(QuotaService::class)->remaining($card))->toBe(0.0);

    $this->actingAs(admin())->post(route('qr-cards.top-ups.store', $card), ['liters' => 30]);

    expect(app(QuotaService::class)->remaining($card))->toBe(30.0);
});

test('logs who topped up, when, how many liters and the note', function () {
    $user = admin();
    $card = QrCard::factory()->create();

    $this->actingAs($user)->post(route('qr-cards.top-ups.store', $card), ['liters' => 6.5, 'note' => 'Pekerjaan tambahan']);

    $adjustment = QuotaAdjustment::sole();
    expect($adjustment->qr_card_id)->toBe($card->id)
        ->and($adjustment->user_id)->toBe($user->id)
        ->and($adjustment->liters)->toBe(6.5)
        ->and($adjustment->note)->toBe('Pekerjaan tambahan')
        ->and($adjustment->created_at->toDateTimeString())->toBe('2026-10-22 14:30:00');
    $this->assertDatabaseHas('activity_logs', ['action' => 'card.topped_up', 'subject_id' => $card->id, 'user_id' => $user->id]);
});

test('rejects liters that are not a positive number', function (array $payload) {
    $card = QrCard::factory()->create();

    $this->actingAs(admin())
        ->post(route('qr-cards.top-ups.store', $card), $payload)
        ->assertSessionHasErrors('liters');

    expect(QuotaAdjustment::count())->toBe(0);
})->with([
    'zero' => [['liters' => 0]],
    'negative' => [['liters' => -3]],
    'missing' => [[]],
    'text' => [['liters' => 'sepuluh']],
]);

test('answers 404 for a card that was deleted', function () {
    $card = QrCard::factory()->create();
    $card->delete();

    $this->actingAs(admin())->post(route('qr-cards.top-ups.store', $card), ['liters' => 5])->assertNotFound();
});
