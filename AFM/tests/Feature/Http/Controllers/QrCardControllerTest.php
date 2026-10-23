<?php

use App\Models\FuelTransaction;
use App\Models\QrCard;
use App\Models\VehicleType;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    // Thursday 22 Oct 2026, 14:30 WIB.
    $this->travelTo('2026-10-22 14:30:00');
});

function cardPayload(array $overrides = []): array
{
    return [
        'category' => 'internal',
        'vehicle_type_id' => VehicleType::factory()->create()->id,
        'plate_number' => 'KT 1234 AB',
        'validity' => 'lifetime',
        'valid_days' => null,
        'reset_enabled' => true,
        ...$overrides,
    ];
}

/** @return list<string> plate numbers on the page, in table order */
function listedPlates(mixed $response): array
{
    return array_column($response->inertiaProps('cards.data'), 'plate_number');
}

test('guests are redirected to the login page', function () {
    $this->get(route('qr-cards.index'))->assertRedirect(route('login'));
    $this->post(route('qr-cards.store'), [])->assertRedirect(route('login'));
});

test('creates a card with category, type, plate, validity and reset', function () {
    $user = admin();

    $this->actingAs($user)
        ->post(route('qr-cards.store'), cardPayload(['validity' => 'temporary', 'valid_days' => 30, 'plate_number' => 'kt 1234  ab']))
        ->assertSessionHasNoErrors()
        ->assertInertiaFlash('toast.message', 'Kartu QR KT 1234 AB dibuat.');

    $card = QrCard::firstOrFail();
    expect($card->plate_number)->toBe('KT 1234 AB')
        ->and($card->valid_days)->toBe(30)
        ->and($card->expires_at->toDateString())->toBe('2026-11-20')
        ->and($card->created_by)->toBe($user->id);
});

test('requires valid days for a temporary card only', function () {
    $this->actingAs(admin())
        ->post(route('qr-cards.store'), cardPayload(['validity' => 'temporary', 'valid_days' => null]))
        ->assertSessionHasErrors('valid_days');

    $this->post(route('qr-cards.store'), cardPayload(['validity' => 'lifetime']))
        ->assertSessionHasNoErrors();
});

test('rejects a plate that a card still valid is using', function () {
    QrCard::factory()->create(['plate_number' => 'KT 1234 AB']);

    $this->actingAs(admin())
        ->post(route('qr-cards.store'), cardPayload())
        ->assertSessionHasErrors(['plate_number' => "Nomor plat 'KT 1234 AB' sudah dipakai oleh kartu yang masih berlaku."]);

    expect(QrCard::count())->toBe(1);
});

test('reissues a plate after the old card expired or was deleted', function () {
    QrCard::factory()->expired()->create(['plate_number' => 'KT 1234 AB']);
    QrCard::factory()->create(['plate_number' => 'KT 9999 ZZ'])->delete();

    $this->actingAs(admin());

    $this->post(route('qr-cards.store'), cardPayload())->assertSessionHasNoErrors();
    $this->post(route('qr-cards.store'), cardPayload(['plate_number' => 'KT 9999 ZZ']))->assertSessionHasNoErrors();

    expect(QrCard::where('plate_number', 'KT 1234 AB')->count())->toBe(2)
        ->and(QrCard::where('plate_number', 'KT 9999 ZZ')->count())->toBe(1);
});

test('rejects a choice that does not exist', function (string $field, mixed $value) {
    $this->actingAs(admin())
        ->post(route('qr-cards.store'), cardPayload([$field => $value]))
        ->assertSessionHasErrors($field);

    expect(QrCard::count())->toBe(0);
})->with([
    'category' => ['category', 'rental'],
    'vehicle type' => ['vehicle_type_id', 9999],
    'validity' => ['validity', 'forever'],
    'valid days below one' => ['valid_days', 0],
]);

test('rejects an empty submission with every required field', function () {
    $this->actingAs(admin())
        ->post(route('qr-cards.store'), [])
        ->assertSessionHasErrors(['category', 'vehicle_type_id', 'plate_number', 'validity', 'reset_enabled']);
});

test('soft deletes a card, keeps its history and logs it', function () {
    $card = QrCard::factory()->create();
    FuelTransaction::factory()->for($card)->create();

    $this->actingAs(admin())->delete(route('qr-cards.destroy', $card))->assertRedirect();

    $this->assertSoftDeleted($card);
    expect(FuelTransaction::count())->toBe(1);
    $this->assertDatabaseHas('activity_logs', ['action' => 'card.deleted', 'subject_id' => $card->id]);
});

test('answers 404 when the card was deleted already', function () {
    $card = QrCard::factory()->create();
    $card->delete();

    $this->actingAs(admin())->delete(route('qr-cards.destroy', $card))->assertNotFound();
});

test('lists the lowest remaining liters first and flags depleted and low cards', function () {
    $type = VehicleType::factory()->quota(10)->create();
    QrCard::factory()->for($type)->create(['plate_number' => 'B 1 FINE']);
    $low = QrCard::factory()->for($type)->create(['plate_number' => 'B 2 LOW']);
    $depleted = QrCard::factory()->for($type)->create(['plate_number' => 'B 3 DEP']);
    FuelTransaction::factory()->for($low)->create(['liters' => 9, 'dispensed_at' => '2026-10-22 08:00:00']);
    FuelTransaction::factory()->for($depleted)->create(['liters' => 10, 'dispensed_at' => '2026-10-22 08:00:00']);

    $this->actingAs(admin())
        ->get(route('qr-cards.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('qr/index')
            ->where('cards.data.0.plate_number', 'B 3 DEP')
            ->where('cards.data.0.depleted', true)
            ->where('cards.data.0.remaining', 0)
            ->where('cards.data.0.next_reset', '2026-10-23T00:00:00+07:00')
            ->where('cards.data.1.plate_number', 'B 2 LOW')
            ->where('cards.data.1.low', true)
            ->where('cards.data.2.plate_number', 'B 1 FINE')
            ->where('cards.data.2.low', false)
            ->where('counters.depleted', 1)
            ->etc());
});

test('a depleted card with reset off has no next reset', function () {
    $card = QrCard::factory()->resetOff()->for(VehicleType::factory()->quota(10))->create();
    FuelTransaction::factory()->for($card)->create(['liters' => 10]);

    $this->actingAs(admin())
        ->get(route('qr-cards.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('cards.data.0.depleted', true)
            ->where('cards.data.0.next_reset', null)
            ->etc());
});

test('filters the table', function (array $query, array $expected) {
    $alpha = VehicleType::factory()->quota(10)->create(['name' => 'Alpha']);
    $beta = VehicleType::factory()->quota(10)->create(['name' => 'Beta']);
    QrCard::factory()->for($alpha)->create(['plate_number' => 'B 1 AAA']);
    QrCard::factory()->for($alpha)->external()->temporary(30)->create(['plate_number' => 'B 2 BBB']);
    QrCard::factory()->for($beta)->resetOff()->create(['plate_number' => 'B 3 CCC']);
    QrCard::factory()->for($beta)->expired()->create(['plate_number' => 'B 4 DDD']);

    $plates = listedPlates($this->actingAs(admin())->get(route('qr-cards.index', $query + ['sort' => 'plate'])));

    expect($plates)->toBe($expected);
})->with([
    'lifetime' => [['validity' => 'lifetime'], ['B 1 AAA', 'B 3 CCC']],
    'temporary' => [['validity' => 'temporary'], ['B 2 BBB', 'B 4 DDD']],
    'active' => [['state' => 'active'], ['B 1 AAA', 'B 2 BBB', 'B 3 CCC']],
    'expired' => [['state' => 'expired'], ['B 4 DDD']],
    'reset on' => [['reset' => 'on'], ['B 1 AAA', 'B 2 BBB', 'B 4 DDD']],
    'reset off' => [['reset' => 'off'], ['B 3 CCC']],
    'external' => [['category' => 'external'], ['B 2 BBB']],
    'search by plate' => [['search' => 'bbb'], ['B 2 BBB']],
]);

test('filters by vehicle type and by low quota', function () {
    $alpha = VehicleType::factory()->quota(10)->create();
    $beta = VehicleType::factory()->quota(10)->create();
    QrCard::factory()->for($alpha)->create(['plate_number' => 'B 1 AAA']);
    $low = QrCard::factory()->for($beta)->create(['plate_number' => 'B 2 BBB']);
    FuelTransaction::factory()->for($low)->create(['liters' => 9, 'dispensed_at' => '2026-10-22 08:00:00']);

    $this->actingAs(admin());

    expect(listedPlates($this->get(route('qr-cards.index', ['vehicle_type' => $beta->id]))))->toBe(['B 2 BBB'])
        ->and(listedPlates($this->get(route('qr-cards.index', ['state' => 'low']))))->toBe(['B 2 BBB']);
});

test('sorts every column and puts the soonest expiry first', function () {
    $type = VehicleType::factory()->quota(10)->create();
    QrCard::factory()->for($type)->temporary(30)->create(['plate_number' => 'B 1 LATE']);
    QrCard::factory()->for($type)->temporary(5)->create(['plate_number' => 'B 2 SOON']);
    QrCard::factory()->for($type)->create(['plate_number' => 'B 3 LIFE']);

    $this->actingAs(admin());

    expect(listedPlates($this->get(route('qr-cards.index', ['sort' => 'expiry']))))->toBe(['B 2 SOON', 'B 1 LATE', 'B 3 LIFE'])
        ->and(listedPlates($this->get(route('qr-cards.index', ['sort' => 'plate', 'dir' => 'desc']))))->toBe(['B 3 LIFE', 'B 2 SOON', 'B 1 LATE']);
});

test('accepts every sortable column', function (string $column) {
    QrCard::factory()->create();

    $this->actingAs(admin())
        ->get(route('qr-cards.index', ['sort' => $column, 'dir' => 'desc']))
        ->assertOk();
})->with(['plate', 'category', 'vehicle_type', 'expiry', 'reset', 'quota', 'remaining', 'status']);

test('ignores a sort column that is not allowed', function () {
    QrCard::factory()->create(['plate_number' => 'B 1 AAA']);

    $response = $this->actingAs(admin())->get(route('qr-cards.index', ['sort' => 'id; drop table qr_cards']));

    $response->assertOk();
    expect(listedPlates($response))->toBe(['B 1 AAA']);
});

test('paginates 25 cards per page', function () {
    QrCard::factory()->count(30)->create();

    $this->actingAs(admin())
        ->get(route('qr-cards.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 25)
            ->where('cards.total', 30)
            ->where('cards.last_page', 2)
            ->etc());
});

test('shares the reset period so the create form can label its reset checkbox', function () {
    setSettings(['reset_period' => 'week']);

    $this->actingAs(admin())
        ->get(route('qr-cards.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('site.resetPeriod', 'week')
            ->where('site.resetUnit', 'minggu')
            ->missing('defaults')
            ->etc());
});

test('requires at least one valid day for a temporary card', function (mixed $days) {
    $this->actingAs(admin())
        ->post(route('qr-cards.store'), cardPayload(['validity' => 'temporary', 'valid_days' => $days]))
        ->assertSessionHasErrors('valid_days');

    expect(QrCard::count())->toBe(0);
})->with([
    'zero' => [0],
    'empty' => [null],
    'negative' => [-3],
]);

test('sends what the printable QR card shows: plate, type, tank capacity, quota, token and creation time', function () {
    $type = VehicleType::factory()->quota(20)->create(['name' => 'Daihatsu Gran Max', 'max_capacity_liters' => 45]);
    $card = QrCard::factory()->for($type)->create(['plate_number' => 'KT 1234 AB']);

    $this->actingAs(admin())
        ->get(route('qr-cards.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('cards.data.0.plate_number', 'KT 1234 AB')
            ->where('cards.data.0.vehicle_type', 'Daihatsu Gran Max')
            ->where('cards.data.0.max_capacity', 45)
            ->where('cards.data.0.quota', 20)
            ->where('cards.data.0.token', $card->token)
            ->where('cards.data.0.issued_at', $card->issued_at->toIso8601String())
            ->etc());
});
