<?php

use App\Filament\Pages\ManagePageSettings;
use App\Models\PageSetting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('unauthenticated users are redirected from filament admin dashboard to login', function () {
    $response = $this->get('/admin');

    $response->assertRedirect('/admin/login');
});

test('authenticated admin user can access filament admin dashboard', function () {
    $admin = User::factory()->create([
        'email' => 'admin_test@example.com',
    ]);

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertOk();
});

test('authenticated admin user can access collection resources index pages', function () {
    $admin = User::factory()->create([
        'email' => 'admin_test2@example.com',
    ]);

    $routes = [
        '/admin/service-clusters',
        '/admin/services',
        '/admin/works',
        '/admin/certification-categories',
        '/admin/corporate-accreditations',
        '/admin/product-certifications',
        '/admin/portfolio-categories',
        '/admin/portfolio-items',
        '/admin/specialty-clusters',
        '/admin/skills',
        '/admin/personnels',
        '/admin/contact-submissions',
    ];

    foreach ($routes as $route) {
        $response = $this->actingAs($admin)->get($route);
        $response->assertOk();
    }
});

test('authenticated admin user can access singleton settings pages', function () {
    $admin = User::factory()->create([
        'email' => 'admin_test3@example.com',
    ]);

    $routes = [
        '/admin/manage-global-settings',
        '/admin/manage-about-company',
        '/admin/manage-page-settings',
    ];

    foreach ($routes as $route) {
        $response = $this->actingAs($admin)->get($route);
        $response->assertOk();
    }
});

test('admin can update page settings via ManagePageSettings form', function () {
    $admin = User::factory()->create([
        'email' => 'admin_test4@example.com',
    ]);

    Livewire\Livewire::actingAs($admin)
        ->test(ManagePageSettings::class)
        ->assertSuccessful()
        ->set('data.landing.hero_title', 'Precision Engineering Leaders')
        ->set('data.landing.about_teaser.description', 'Single consolidated description text for about teaser.')
        ->set('data.works.hero_title', 'Industrial Capabilities')
        ->set('data.portfolio.hero_title', 'Master Project Directory')
        ->set('data.licenses.hero_title', 'Accreditations & Compliance')
        ->call('save')
        ->assertHasNoErrors();

    expect(PageSetting::forPage('landing')->hero_title)->toBe('Precision Engineering Leaders');
    expect(PageSetting::forPage('landing')->about_teaser['description'])->toBe('Single consolidated description text for about teaser.');
    expect(PageSetting::forPage('works')->hero_title)->toBe('Industrial Capabilities');
    expect(PageSetting::forPage('portfolio')->hero_title)->toBe('Master Project Directory');
    expect(PageSetting::forPage('licenses')->hero_title)->toBe('Accreditations & Compliance');
});

test('ManagePageSettings mounts legacy paragraph_1 and paragraph_2 into single description field', function () {
    $admin = User::factory()->create([
        'email' => 'admin_test5@example.com',
    ]);

    $landing = PageSetting::forPage('landing');
    $landing->update([
        'about_teaser' => [
            'paragraph_1' => 'Apex Industrial Engineering berdiri dengan komitmen memberikan solusi teknik presisi.',
            'paragraph_2' => 'Didukung oleh lebih dari 150 insinyur berkualifikasi tinggi.',
        ],
    ]);

    Livewire\Livewire::actingAs($admin)
        ->test(ManagePageSettings::class)
        ->assertSuccessful()
        ->assertSet(
            'data.landing.about_teaser.description',
            "Apex Industrial Engineering berdiri dengan komitmen memberikan solusi teknik presisi.\n\nDidukung oleh lebih dari 150 insinyur berkualifikasi tinggi."
        );
});
