<?php

use App\Enums\Role;
use App\Models\QrCard;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

function newAccount(array $overrides = []): array
{
    return [
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'password' => 'rahasia-banget-1',
        ...$overrides,
    ];
}

test('guests are redirected to the login page', function () {
    $this->get(route('admin-accounts.index'))->assertRedirect(route('login'));
});

test('an Admin gets 403 on every account action', function (string $method, string $route) {
    $target = User::factory()->admin()->create();
    $parameters = in_array($route, ['admin-accounts.index', 'admin-accounts.store'], true) ? [] : [$target];

    $this->actingAs(admin())->call($method, route($route, $parameters))->assertForbidden();
})->with([
    'list' => ['GET', 'admin-accounts.index'],
    'create' => ['POST', 'admin-accounts.store'],
    'update' => ['PUT', 'admin-accounts.update'],
    'delete' => ['DELETE', 'admin-accounts.destroy'],
    'reset password' => ['PUT', 'admin-accounts.password'],
]);

test('an Admin gets 403 even when the submitted account data is invalid', function () {
    $this->actingAs(admin())
        ->post(route('admin-accounts.store'), ['name' => '', 'email' => 'bukan-email'])
        ->assertForbidden();

    expect(User::count())->toBe(1);
});

test('a Super Admin sees only Admin accounts', function () {
    $superAdmin = superAdmin();
    User::factory()->admin()->create(['name' => 'Siti']);
    User::factory()->admin()->create(['name' => 'Agus']);
    User::factory()->admin()->create(['name' => 'Dihapus'])->delete();

    $this->actingAs($superAdmin)
        ->get(route('admin-accounts.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin-accounts/index')
            ->has('accounts', 2)
            ->where('accounts.0.name', 'Agus')
            ->where('accounts.1.name', 'Siti')
            ->missing('accounts.0.password'));
});

test('a Super Admin creates an Admin with a hashed password and a verified email', function () {
    $this->actingAs(superAdmin())
        ->post(route('admin-accounts.store'), newAccount())
        ->assertSessionHasNoErrors()
        ->assertInertiaFlash('toast.message', 'Akun Budi Santoso dibuat.');

    $account = User::where('email', 'budi@example.com')->sole();
    expect($account->role)->toBe(Role::Admin)
        ->and($account->email_verified_at)->not->toBeNull()
        ->and(Hash::check('rahasia-banget-1', $account->password))->toBeTrue()
        ->and($account->password)->not->toBe('rahasia-banget-1');
    $this->assertDatabaseHas('activity_logs', ['action' => 'account.created', 'subject_label' => 'budi@example.com']);
});

test('never creates a Super Admin, whatever the request says', function () {
    $this->actingAs(superAdmin())
        ->post(route('admin-accounts.store'), newAccount(['role' => 'super_admin']))
        ->assertSessionHasNoErrors();

    expect(User::where('email', 'budi@example.com')->sole()->role)->toBe(Role::Admin);
});

test('rejects invalid account data', function (array $overrides, string $field) {
    User::factory()->create(['email' => 'sudah@example.com']);

    $this->actingAs(superAdmin())
        ->post(route('admin-accounts.store'), newAccount($overrides))
        ->assertSessionHasErrors($field);

    expect(User::where('email', 'budi@example.com')->exists())->toBeFalse();
})->with([
    'missing name' => [['name' => ''], 'name'],
    'bad email' => [['email' => 'bukan-email'], 'email'],
    'email in use' => [['email' => 'sudah@example.com'], 'email'],
    'missing password' => [['password' => ''], 'password'],
]);

test('a Super Admin edits the name and email of an Admin', function () {
    $account = User::factory()->admin()->create();

    $this->actingAs(superAdmin())
        ->put(route('admin-accounts.update', $account), ['name' => 'Nama Baru', 'email' => 'baru@example.com'])
        ->assertSessionHasNoErrors();

    expect($account->fresh()->only(['name', 'email']))->toBe(['name' => 'Nama Baru', 'email' => 'baru@example.com']);
});

test('editing keeps the own email valid but rejects another account email', function () {
    $account = User::factory()->admin()->create(['email' => 'saya@example.com']);
    User::factory()->create(['email' => 'lain@example.com']);
    $this->actingAs(superAdmin());

    $this->put(route('admin-accounts.update', $account), ['name' => 'Saya', 'email' => 'saya@example.com'])
        ->assertSessionHasNoErrors();
    $this->put(route('admin-accounts.update', $account), ['name' => 'Saya', 'email' => 'lain@example.com'])
        ->assertSessionHasErrors('email');
});

test('a Super Admin resets the password of an Admin', function () {
    $account = User::factory()->admin()->create();

    $this->actingAs(superAdmin())
        ->put(route('admin-accounts.password', $account), ['password' => 'kata-sandi-baru-9'])
        ->assertSessionHasNoErrors();

    expect(Hash::check('kata-sandi-baru-9', $account->fresh()->password))->toBeTrue();
});

test('a new password is required to reset one', function () {
    $account = User::factory()->admin()->create();

    $this->actingAs(superAdmin())
        ->put(route('admin-accounts.password', $account), ['password' => ''])
        ->assertSessionHasErrors('password');
});

test('deleting an Admin removes the account but keeps its name on history', function () {
    $account = User::factory()->admin()->create(['name' => 'Siti']);
    $card = QrCard::factory()->create(['created_by' => $account->id]);

    $this->actingAs(superAdmin())->delete(route('admin-accounts.destroy', $account))->assertRedirect();

    $this->assertSoftDeleted($account);
    expect($card->fresh()->creator->name)->toBe('Siti');
});

test('a deleted Admin can no longer log in', function () {
    $account = User::factory()->admin()->create(['email' => 'siti@example.com']);
    $this->actingAs(superAdmin())->delete(route('admin-accounts.destroy', $account));
    auth()->logout();

    $this->post(route('login.store'), ['email' => 'siti@example.com', 'password' => 'password']);

    $this->assertGuest();
});

test('a Super Admin account cannot be changed or deleted through the accounts screen', function () {
    $other = superAdmin();
    $this->actingAs(superAdmin());

    $this->put(route('admin-accounts.update', $other), ['name' => 'X', 'email' => 'x@example.com'])->assertForbidden();
    $this->put(route('admin-accounts.password', $other), ['password' => 'kata-sandi-baru-9'])->assertForbidden();
    $this->delete(route('admin-accounts.destroy', $other))->assertForbidden();

    $this->assertNotSoftDeleted($other);
});

test('shares what the menu may show', function (string $role, bool $manageAdmins) {
    $this->actingAs(User::factory()->{$role}()->create())
        ->get(route('tank.index'))
        ->assertInertia(fn (Assert $page) => $page->where('can.manageAdmins', $manageAdmins)->etc());
})->with([
    'admin' => ['admin', false],
    'super admin' => ['superAdmin', true],
]);
