<?php

use App\Models\GlobalSetting;
use App\Models\ServiceCluster;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    GlobalSetting::current()->update([
        'company_name' => 'PT Uji Layout Bersama',
        'company_tagline' => 'Tagline Uji Layout',
        'footer_description' => 'Deskripsi footer dari CMS untuk pengujian layout.',
        'factory_address' => 'Jl. Pengujian CMS No. 99, Bekasi',
        'primary_email' => 'primer@uji-layout.test',
        'corporate_email' => 'korporat@uji-layout.test',
        'phones' => [
            ['display_text' => '+62 21 1111-2222', 'tel_url' => 'tel:+622111112222', 'is_whatsapp' => false],
            ['display_text' => '+62 811-3333-4444', 'tel_url' => 'tel:+628113333444', 'is_whatsapp' => true],
        ],
        'navbar_logo' => 'global/navbar-uji.png',
        'footer_logo' => 'global/footer-uji.png',
        'footer_partner_image' => 'global/partner-uji.png',
    ]);
});

test('shared layout renders global settings on every page', function (string $routeName) {
    $this->get(route($routeName))
        ->assertOk()
        ->assertSee('PT Uji Layout Bersama')
        ->assertSee('Tagline Uji Layout')
        ->assertSee('Deskripsi footer dari CMS untuk pengujian layout.')
        ->assertSee('Jl. Pengujian CMS No. 99, Bekasi')
        ->assertSee('primer@uji-layout.test')
        ->assertSee('korporat@uji-layout.test')
        ->assertSee('+62 21 1111-2222')
        ->assertSee('href="tel:+622111112222"', false)
        ->assertSee('+62 811-3333-4444')
        ->assertSee(asset('storage/global/navbar-uji.png'), false)
        ->assertSee(asset('storage/global/footer-uji.png'), false);
})->with(['home', 'works', 'portfolio', 'licenses', 'about']);

test('footer partner banner uses the cms image', function (string $routeName) {
    $this->get(route($routeName))
        ->assertOk()
        ->assertSee(asset('storage/global/partner-uji.png'), false);
})->with(['home', 'works']);

test('layouts fall back to the default logo when the cms logo file is missing from public', function () {
    GlobalSetting::current()->update(['navbar_logo' => '/images/logo.png', 'footer_logo' => '/images/logo-footer.png']);

    $this->get(route('works'))
        ->assertOk()
        ->assertDontSee('/images/logo.png', false)
        ->assertDontSee('/images/logo-footer.png', false);
});

test('footer links portofolio to its own page and legal links come from the cms', function () {
    GlobalSetting::current()->update([
        'footer_legal_links' => [['label' => 'Kebijakan Uji CMS', 'url' => '/kebijakan-uji']],
    ]);

    $this->get(route('works'))
        ->assertOk()
        ->assertSee('href="'.url('/portofolio').'"', false)
        ->assertSee('Kebijakan Uji CMS')
        ->assertSee('href="/kebijakan-uji"', false);
});

test('navbar service summary reflects the number of services and clusters', function () {
    $this->get(route('about'))
        ->assertOk()
        ->assertSee('12 Layanan dalam 4 Klaster')
        ->assertDontSee('12 Klaster');

    ServiceCluster::query()->first()->services()->first()->delete();

    $this->get(route('about'))->assertSee('11 Layanan dalam 4 Klaster');
});

test('landing page uses the shared navbar and footer with in-page anchors', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('href="'.url('/#contact').'"', false)
        ->assertSee('href="'.url('/portofolio').'"', false)
        ->assertDontSee('Trissan');
});

test('footer renders legal links without a url as plain text instead of dead anchors', function () {
    GlobalSetting::current()->update([
        'footer_legal_links' => [
            ['label' => 'Tanpa Url Uji', 'url' => null],
            ['label' => 'Hash Saja Uji', 'url' => '#'],
            ['label' => 'Dengan Url Uji', 'url' => '/dengan-url'],
        ],
    ]);

    $content = $this->get(route('works'))->assertOk()->getContent();

    expect($content)
        ->toContain('<span>Tanpa Url Uji</span>')
        ->toContain('<span>Hash Saja Uji</span>')
        ->toContain('href="/dengan-url"')
        ->not->toContain('href="#"');
});

test('mobile bottom navigation links every main page and marks the current one', function (string $routeName, string $activePath) {
    $content = $this->get(route($routeName))
        ->assertOk()
        ->assertSee('id="bottom-nav"', false)
        ->assertSee('aria-label="Navigasi utama"', false)
        ->assertSee('href="'.url('/#contact').'"', false)
        ->getContent();

    foreach (['/', '/works', '/portofolio', '/about', '/license'] as $path) {
        expect($content)->toContain('href="'.url($path).'"');
    }

    $bottomNav = substr($content, strpos($content, 'id="bottom-nav"'));
    $bottomNav = substr($bottomNav, 0, strpos($bottomNav, '</nav>'));

    expect(preg_match('/aria-current="page"[^>]*href="'.preg_quote(url($activePath), '/').'"/', $bottomNav))->toBe(1)
        ->and(substr_count($bottomNav, 'aria-current="page"'))->toBe(1);
})->with([
    ['home', '/'],
    ['works', '/works'],
    ['portfolio', '/portofolio'],
    ['about', '/about'],
    ['licenses', '/license'],
]);

test('every public page includes the shared desktop scale stylesheet', function (string $routeName) {
    $this->get(route($routeName))
        ->assertOk()
        ->assertSee('--page-zoom', false)
        ->assertSee('zoom: var(--page-zoom)', false);
})->with(['home', 'works', 'portfolio', 'licenses', 'about']);

test('scale stylesheet only compensates viewport widths below the sm breakpoint so desktop card widths are not overridden', function () {
    $content = $this->get(route('home'))->assertOk()->getContent();

    expect($content)->toContain('@media (max-width: 639px) { html .w-\[78vw\]');
});
