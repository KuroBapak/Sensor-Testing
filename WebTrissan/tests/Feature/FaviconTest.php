<?php

use App\Filament\Pages\ManageGlobalSettings;
use App\Models\GlobalSetting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SeedMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('without a favicon, every public page offers square icons made from the logo instead of the wide logo itself', function (string $routeName) {
    $content = $this->get(route($routeName))->assertOk()->getContent();

    expect($content)
        ->toMatch('#<link rel="icon" href="[^"]*/storage/_brand/icon-48-[0-9a-f]{10}\.png" type="image/png" sizes="48x48">#')
        ->toMatch('#<link rel="apple-touch-icon" href="[^"]*/storage/_brand/icon-180-solid-[0-9a-f]{10}\.png" sizes="180x180">#');
})->with(['home', 'about', 'works', 'portfolio', 'licenses']);

test('the logo is the browser icon as before when it cannot be turned into square icons', function () {
    GlobalSetting::current()->update(['navbar_logo' => 'https://example.com/logo.svg']);

    $logo = GlobalSetting::current()->navbar_logo_url;

    $this->get(route('home'))->assertOk()
        ->assertSee('<link rel="icon" href="'.$logo.'" type="image/svg+xml">', false)
        ->assertSee('<link rel="apple-touch-icon" href="'.$logo.'">', false);
});

test('a favicon uploaded in the cms takes priority over the logo', function () {
    GlobalSetting::current()->update(['favicon' => 'branding/favicon-uji.png']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('<link rel="icon" href="'.asset('storage/branding/favicon-uji.png').'" type="image/png">', false);
});

test('no icon tags are printed when there is neither a favicon nor a logo', function () {
    GlobalSetting::current()->update(['favicon' => null, 'navbar_logo' => null]);

    $this->get(route('home'))->assertOk()->assertDontSee('rel="icon"', false)->assertDontSee('apple-touch-icon', false);
});

test('/favicon.ico redirects to an icon uploaded in the cms, serves the logo icon itself otherwise, and is a 404 with neither', function () {
    GlobalSetting::current()->update(['favicon' => 'branding/favicon-uji.png']);

    $this->get('/favicon.ico')->assertRedirect(asset('storage/branding/favicon-uji.png'));

    GlobalSetting::current()->update(['favicon' => null]);

    $response = $this->get('/favicon.ico')->assertOk()->assertHeader('Content-Type', 'image/png');
    expect(getimagesizefromstring((string) $response->getContent())[0])->toBe(48);

    GlobalSetting::current()->update(['navbar_logo' => null]);

    $this->get('/favicon.ico')->assertNotFound();
});

test('the admin panel uses the company name and icon instead of the framework defaults', function () {
    $content = $this->get('/admin/login')->assertOk()->getContent();

    expect($content)
        ->toContain(e(GlobalSetting::current()->company_name))
        ->toContain('href="'.GlobalSetting::current()->favicon_url.'"')
        ->not->toContain('>Laravel<');
});

test('the seeder only sets a favicon when a real favicon file was added', function () {
    expect(GlobalSetting::current()->favicon)->toBeNull()
        ->and(SeedMedia::hasPhoto('favicon'))->toBeFalse()
        ->and(SeedMedia::hasPhoto('logo'))->toBeTrue();
});

test('the global settings form accepts a favicon', function () {
    $admin = User::factory()->create(['email' => 'admin_favicon@example.com']);

    Livewire\Livewire::actingAs($admin)
        ->test(ManageGlobalSettings::class)
        ->assertFormFieldExists('favicon');
});
