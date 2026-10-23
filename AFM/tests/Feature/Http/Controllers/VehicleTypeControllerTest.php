<?php

use App\Models\QrCard;
use App\Models\VehicleType;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $this->get(route('vehicles.index'))->assertRedirect(route('login'));
    $this->post(route('vehicles.store'), [])->assertRedirect(route('login'));
});

test('the vehicles page lists types with their card counts and honours the search', function () {
    $granMax = VehicleType::factory()->create(['name' => 'Daihatsu Gran Max']);
    VehicleType::factory()->create(['name' => 'Toyota Hilux']);
    QrCard::factory()->count(2)->for($granMax)->create();
    $deletedCard = QrCard::factory()->for($granMax)->create();
    $deletedCard->delete();

    $this->actingAs(admin())
        ->get(route('vehicles.index', ['search' => 'gran']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('vehicles/index')
            ->where('search', 'gran')
            ->has('vehicleTypes', 1)
            ->where('vehicleTypes.0.name', 'Daihatsu Gran Max')
            ->where('vehicleTypes.0.cards_count', 2));
});

test('the vehicles page lists types alphabetically', function () {
    VehicleType::factory()->create(['name' => 'Toyota Hilux']);
    VehicleType::factory()->create(['name' => 'Daihatsu Gran Max']);

    $names = $this->actingAs(admin())->get(route('vehicles.index'))->inertiaProps('vehicleTypes');

    expect(array_column($names, 'name'))->toBe(['Daihatsu Gran Max', 'Toyota Hilux']);
});

test('adds a vehicle type and logs it', function () {
    $this->actingAs(admin())
        ->post(route('vehicles.store'), ['name' => 'Isuzu Elf', 'max_capacity_liters' => 100, 'quota_liters' => 50])
        ->assertSessionHasNoErrors()
        ->assertInertiaFlash('toast.type', 'success');

    $this->assertDatabaseHas('vehicle_types', ['name' => 'Isuzu Elf', 'max_capacity_liters' => 100, 'quota_liters' => 50]);
    $this->assertDatabaseHas('activity_logs', ['action' => 'vehicle_type.created', 'subject_label' => 'Isuzu Elf']);
});

test('rejects a name that exists already, ignoring case', function () {
    VehicleType::factory()->create(['name' => 'Daihatsu Gran Max']);

    $this->actingAs(admin())
        ->post(route('vehicles.store'), ['name' => 'daihatsu GRAN max', 'max_capacity_liters' => 45, 'quota_liters' => 20])
        ->assertSessionHasErrors(['name' => "Kendaraan 'daihatsu GRAN max' sudah terdaftar."]);

    expect(VehicleType::count())->toBe(1);
});

test('rejects invalid input with Indonesian messages', function (array $payload, string $field, string $message) {
    $this->actingAs(admin())
        ->post(route('vehicles.store'), $payload + ['name' => 'Isuzu Elf', 'max_capacity_liters' => 100, 'quota_liters' => 50])
        ->assertSessionHasErrors([$field => $message]);

    expect(VehicleType::count())->toBe(0);
})->with([
    'missing name' => [['name' => ''], 'name', 'Nama wajib diisi.'],
    'zero capacity' => [['max_capacity_liters' => 0], 'max_capacity_liters', 'Kapasitas maksimal harus lebih besar dari 0.'],
    'negative quota' => [['quota_liters' => -5], 'quota_liters', 'Kuota BBM harus lebih besar dari 0.'],
    'text as capacity' => [['max_capacity_liters' => 'banyak'], 'max_capacity_liters', 'Kapasitas maksimal harus berupa angka.'],
]);

test('edits a type keeping its own name valid', function () {
    $type = VehicleType::factory()->create(['name' => 'Toyota Hilux', 'quota_liters' => 40]);

    $this->actingAs(admin())
        ->put(route('vehicles.update', $type), ['name' => 'Toyota Hilux', 'max_capacity_liters' => 80, 'quota_liters' => 45])
        ->assertSessionHasNoErrors();

    expect($type->fresh()->quota_liters)->toBe(45.0);
});

test('refuses to delete a type that still has cards and names how many', function () {
    $type = VehicleType::factory()->create(['name' => 'Toyota Hilux']);
    QrCard::factory()->count(3)->for($type)->create();

    $this->actingAs(admin())
        ->delete(route('vehicles.destroy', $type))
        ->assertRedirect()
        ->assertInertiaFlash('toast.type', 'error')
        ->assertInertiaFlash('toast.message', "Kendaraan 'Toyota Hilux' tidak dapat dihapus karena masih dipakai oleh 3 kartu QR.");

    $this->assertModelExists($type);
});

test('deletes a type without cards and keeps the row for history', function () {
    $type = VehicleType::factory()->create();

    $this->actingAs(admin())->delete(route('vehicles.destroy', $type))->assertRedirect();

    $this->assertSoftDeleted($type);
});

test('allows a type whose only cards were deleted to be removed', function () {
    $type = VehicleType::factory()->create();
    QrCard::factory()->for($type)->create()->delete();

    $this->actingAs(admin())->delete(route('vehicles.destroy', $type))->assertRedirect();

    $this->assertSoftDeleted($type);
});
