<?php

use App\Filament\Pages\ManagePageSettings;
use App\Models\PageSetting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('panel allows any user in local and testing when no allowlist is configured', function () {
    config(['cms.admin_emails' => []]);

    $this->actingAs(User::factory()->create())->get('/admin')->assertOk();
});

test('panel forbids every user in production when no allowlist is configured', function () {
    config(['cms.admin_emails' => []]);
    app()->detectEnvironment(fn () => 'production');

    $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
});

test('panel only admits allowlisted emails when an allowlist is configured', function () {
    config(['cms.admin_emails' => ['owner@apex.test']]);

    $this->actingAs(User::factory()->create(['email' => 'intruder@apex.test']))->get('/admin')->assertForbidden();
    $this->actingAs(User::factory()->create(['email' => 'owner@apex.test']))->get('/admin')->assertOk();
});

test('seeder creates the admin with the configured password and no demo user in production', function () {
    app()->detectEnvironment(fn () => 'production');
    config(['cms.admin_emails' => ['owner@apex.test'], 'cms.admin_password' => 'rahasia-produksi-123']);

    app(DatabaseSeeder::class)->run();

    $admin = User::where('email', 'owner@apex.test')->first();

    expect($admin)->not->toBeNull()
        ->and(Hash::check('rahasia-produksi-123', $admin->password))->toBeTrue()
        ->and(User::where('email', 'test@example.com')->exists())->toBeFalse()
        ->and(User::where('email', 'admin@trissan.com')->exists())->toBeFalse();
});

test('seeder skips admin creation in production when no password is configured', function () {
    app()->detectEnvironment(fn () => 'production');
    config(['cms.admin_emails' => ['owner@apex.test'], 'cms.admin_password' => '']);

    app(DatabaseSeeder::class)->run();

    expect(User::count())->toBe(0);
});

test('seeder keeps the default admin and demo user in local and testing', function () {
    config(['cms.admin_emails' => [], 'cms.admin_password' => null]);

    $this->seed(DatabaseSeeder::class);

    $admin = User::where('email', 'admin@trissan.com')->first();

    expect($admin)->not->toBeNull()
        ->and(Hash::check('password', $admin->password))->toBeTrue()
        ->and(User::where('email', 'test@example.com')->exists())->toBeTrue();
});

test('admin can edit the about page seo from page settings', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = User::factory()->create();

    Livewire\Livewire::actingAs($admin)
        ->test(ManagePageSettings::class)
        ->assertFormSet(['about.seo_title' => 'Tentang Kami | PT. Trissan'])
        ->set('data.about.seo_title', 'Profil Perusahaan Baru')
        ->call('save')
        ->assertHasNoErrors();

    expect(PageSetting::forPage('about')->seo_title)->toBe('Profil Perusahaan Baru');
});
