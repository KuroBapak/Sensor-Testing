<?php

use App\Filament\Pages\ManagePageSettings;
use App\Models\GlobalSetting;
use App\Models\PageSetting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

/**
 * @param  array<string, string|null>  $values
 */
function setExtraData(string $page, array $values): void
{
    $setting = PageSetting::forPage($page);
    $setting->update(['extra_data' => [...($setting->extra_data ?? []), ...$values]]);
}

test('section headings on every page come from the cms', function (string $routeName, string $page, array $values) {
    setExtraData($page, $values);

    $response = $this->get(route($routeName))->assertOk();

    foreach ($values as $value) {
        $response->assertSee($value);
    }
})->with([
    'landing' => ['home', 'landing', [
        'featured_works_badge' => 'FW Badge Uji',
        'featured_works_title' => 'FW Judul Uji',
        'featured_works_description' => 'FW Deskripsi Uji',
        'services_directory_title' => 'SD Judul Uji',
        'contact_title' => 'Kontak Judul Uji',
        'contact_card_label' => 'Kartu Label Uji',
        'contact_card_title' => 'Kartu Judul Uji',
        'contact_card_text' => 'Kartu Paragraf Uji',
        'social_label' => 'Sosial Label Uji',
        'social_title' => 'Sosial Judul Uji',
        'map_badge' => 'Peta Badge Uji',
        'map_title' => 'Peta Judul Uji',
        'map_description' => 'Peta Deskripsi Uji',
    ]],
    'licenses' => ['licenses', 'licenses', [
        'corporate_badge' => 'Korporat Badge Uji',
        'corporate_title' => 'Korporat Judul Uji',
        'corporate_description' => 'Korporat Deskripsi Uji',
        'product_badge' => 'Produk Badge Uji',
        'product_title' => 'Produk Judul Uji',
        'product_description' => 'Produk Deskripsi Uji',
    ]],
    'portfolio' => ['portfolio', 'portfolio', [
        'catalog_badge' => 'Katalog Badge Uji',
        'catalog_title' => 'Katalog Judul Uji',
        'catalog_description' => 'Katalog Deskripsi Uji',
        'personnel_badge' => 'Personil Badge Uji',
    ]],
    'about' => ['about', 'about', [
        'values_badge' => 'Nilai Badge Uji',
        'values_subtitle' => 'Nilai Subjudul Uji',
        'facilities_title' => 'Fasilitas Judul Uji',
        'milestones_description' => 'Milestone Deskripsi Uji',
        'structure_badge' => 'Struktur Badge Uji',
    ]],
]);

test('blank cms section texts leave no text but keep the element and its spacing', function () {
    PageSetting::forPage('landing')->update(['extra_data' => null]);

    $response = $this->get(route('home'))->assertOk();

    $response
        ->assertDontSee('ENGINEERED OUTPUT')
        ->assertDontSee('Tinjau contoh rekam jejak')
        ->assertDontSee('GET IN TOUCH')
        ->assertDontSee('How Can We Help?')
        ->assertDontSee('Terhubung di Saluran Resmi')
        ->assertDontSee('Headquarters &amp; Plant Location', false);

    $html = $response->getContent();

    expect($html)->toMatch('/<h2[^>]*>\x{00A0}<\/h2>/u')
        ->and($html)->toMatch('/shadow-sm invisible">/');
});

test('removed hard-coded texts no longer appear on any page', function (string $routeName, array $gone) {
    $response = $this->get(route($routeName))->assertOk();

    foreach ($gone as $text) {
        $response->assertDontSee($text, false);
    }
})->with([
    'works' => ['works', [
        'Sertifikasi AS9100D &amp; Rekam NDT Lengkap',
        'Arsip Batch 2024-2025',
        'SAMPLE PRODUCT OUTPUT',
        'DOKUMENTASI BENCHMARK',
    ]],
    'license' => ['licenses', [
        'Total 4 Dokumen Produk',
        'Apex Verification Engine',
        'Produk Unggulan</span>',
    ]],
    'about' => ['about', ['100% Operational Capacity']],
    'landing' => ['home', ['Portofolio Manpower', 'Layanan dalam']],
]);

test('hero, stats and teaser render nothing when their cms data is empty', function () {
    PageSetting::query()->update([
        'hero_title' => null,
        'hero_subtitle' => null,
        'hero_cta_text' => null,
        'hero_slides' => null,
        'trust_card' => null,
        'about_teaser' => null,
        'stats' => null,
        'hero_badge_card' => null,
        'corporate_highlight_cards' => null,
        'registry_revision_tag' => null,
    ]);
    GlobalSetting::current()->update(['company_stats' => null]);

    foreach (['home', 'works', 'portfolio', 'licenses'] as $routeName) {
        $this->get(route($routeName))
            ->assertOk()
            ->assertDontSee('lh3.googleusercontent.com', false)
            ->assertDontSee('Total Deliveries')
            ->assertDontSee('Tenaga Ahli')
            ->assertDontSee('Sertifikasi Produk</div>', false)
            ->assertDontSee('APEX REGISTRY');
    }

    $this->get(route('home'))
        ->assertDontSee('hero-slide', false)
        ->assertDontSee('SIEMENS')
        ->assertDontSee('25+');
});

test('landing map shows nothing when the cms has no map urls', function () {
    GlobalSetting::current()->update(['maps_button_url' => null, 'maps_iframe_embed_url' => null]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('<iframe', false)
        ->assertDontSee('maps.google.com', false);
});

test('admin can edit section headings through the page settings form', function () {
    $admin = User::factory()->create(['email' => 'admin_headings@example.com']);

    Livewire\Livewire::actingAs($admin)
        ->test(ManagePageSettings::class)
        ->assertSuccessful()
        ->assertSet('data.landing.extra_data.featured_works_title', 'Proyek Pilihan Kami')
        ->set('data.landing.extra_data.featured_works_title', 'Karya Unggulan')
        ->set('data.landing.extra_data.contact_success_message', '')
        ->set('data.about.extra_data.facilities_badge', 'Galeri')
        ->call('save')
        ->assertHasNoErrors();

    expect(PageSetting::forPage('landing')->extra('featured_works_title'))->toBe('Karya Unggulan')
        ->and(PageSetting::forPage('landing')->extra('contact_success_message'))->toBe('')
        ->and(PageSetting::forPage('about')->extra('facilities_badge'))->toBe('Galeri');
});

test('extra returns an empty string for missing keys and missing extra data', function () {
    $setting = new PageSetting;

    expect($setting->extra('anything'))->toBe('');

    $setting->extra_data = ['a_title' => 'Judul'];

    expect($setting->extra('a_title'))->toBe('Judul')
        ->and($setting->extra('a_badge'))->toBe('');
});
