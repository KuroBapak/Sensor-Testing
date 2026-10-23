<?php

use App\Models\User;
use App\Policies\UserPolicy;

test('only a Super Admin may list and create accounts', function (string $actor, bool $allowed) {
    $user = User::factory()->{$actor}()->create();

    expect((new UserPolicy)->viewAny($user))->toBe($allowed)
        ->and((new UserPolicy)->create($user))->toBe($allowed);
})->with([
    'super admin' => ['superAdmin', true],
    'admin' => ['admin', false],
]);

test('only a Super Admin may manage an Admin account', function (string $ability, string $actor, bool $allowed) {
    $actingUser = User::factory()->{$actor}()->create();
    $target = User::factory()->admin()->create();

    expect((new UserPolicy)->{$ability}($actingUser, $target))->toBe($allowed);
})->with(function () {
    foreach (['update', 'delete', 'resetPassword'] as $ability) {
        yield "{$ability} by super admin" => [$ability, 'superAdmin', true];
        yield "{$ability} by admin" => [$ability, 'admin', false];
    }
});

test('nobody manages a Super Admin account in the app', function (string $ability, string $actor) {
    $actingUser = User::factory()->{$actor}()->create();
    $target = User::factory()->superAdmin()->create();

    expect((new UserPolicy)->{$ability}($actingUser, $target))->toBeFalse();
})->with(function () {
    foreach (['update', 'delete', 'resetPassword'] as $ability) {
        yield "{$ability} by super admin" => [$ability, 'superAdmin'];
        yield "{$ability} by admin" => [$ability, 'admin'];
    }
});
