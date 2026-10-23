<?php

use App\Models\User;

test('the last Super Admin cannot be deleted', function () {
    $last = superAdmin();
    User::factory()->admin()->create();

    expect(fn () => $last->delete())->toThrow(LogicException::class, 'Super Admin terakhir tidak dapat dihapus.');

    $this->assertNotSoftDeleted($last);
});

test('a Super Admin can be deleted while another one remains', function () {
    $first = superAdmin();
    superAdmin();

    $first->delete();

    $this->assertSoftDeleted($first);
});

test('an Admin can always be deleted', function () {
    $admin = admin();

    $admin->delete();

    $this->assertSoftDeleted($admin);
});

test('knows whether it is a Super Admin', function () {
    expect(superAdmin()->isSuperAdmin())->toBeTrue()
        ->and(admin()->isSuperAdmin())->toBeFalse();
});
