<?php

use App\Models\ContactSubmission;
use App\Models\GlobalSetting;
use App\Models\Service;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

/**
 * The public pages that share the header, footer and floating contact button.
 *
 * @return array<string, array{0: string}>
 */
dataset('contact pages', [
    'home' => ['/'],
    'about' => ['/about'],
    'works' => ['/works'],
    'portfolio' => ['/portofolio'],
    'license' => ['/license'],
]);

test('every public page has the floating contact button and the same contact form', function (string $uri) {
    $content = $this->get($uri)->assertOk()->getContent();

    expect($content)->toContain('id="contact-fab"')
        ->toContain('onclick="openContactModal()"')
        ->toContain('aria-controls="contact-modal"')
        ->toContain('role="dialog"')
        ->toContain('action="'.route('contact.store').'"')
        ->toContain('name="website"')
        ->toContain('id="contact-service"')
        ->and(substr_count($content, 'id="contact-modal"'))->toBe(1);
})->with('contact pages');

test('the form lists every service, also on pages that did not load them', function () {
    $content = $this->get('/about')->assertOk()->getContent();

    foreach (Service::query()->get() as $service) {
        expect($content)->toContain('<option value="'.$service->id.'"');
    }
});

test('the form opens in a new tab only when a WhatsApp number is set, as on the landing page', function (string $uri) {
    // The script also names the attribute, so look at the form tag itself.
    expect($this->get($uri)->getContent())->not->toMatch('#<form[^>]*data-opens-whatsapp#');

    GlobalSetting::current()->update(['whatsapp_number' => '6281234567890']);

    expect($this->get($uri)->getContent())->toMatch('#<form[^>]*target="_blank"[^>]*data-opens-whatsapp#');
})->with('contact pages');

test('a failed submission from any page returns to that page with the form open and the errors shown', function () {
    $content = $this->from('/works')->followingRedirects()->post(route('contact.store'), ['name' => ''])
        ->assertOk()->getContent();

    expect($content)->toContain('id="contact-modal"')
        ->toMatch('#id="contact-modal"|class="fixed inset-0 z-\[60\][^"]*\bflex"#')
        ->toContain('text-red-600');
    expect(preg_match('#<div[^>]*class="fixed inset-0 z-\[60\][^"]*\bflex"[^>]*id="contact-modal"#', $content))->toBe(1);
});

test('a valid submission from another page is stored, and the visitor sees the CMS success message there', function () {
    $content = $this->from('/about')->followingRedirects()->post(route('contact.store'), [
        'name' => 'Budi',
        'email' => 'budi@example.com',
        'message' => 'Mohon penawaran panel listrik.',
    ])->assertOk()->getContent();

    expect(ContactSubmission::query()->where('email', 'budi@example.com')->exists())->toBeTrue()
        ->and($content)->toContain('role="status"')
        ->and(preg_match('#<div[^>]*class="fixed inset-0 z-\[60\][^"]*\bflex"[^>]*id="contact-modal"#', $content))->toBe(1);
});

test('the contact form stays closed on a normal page view', function (string $uri) {
    $content = $this->get($uri)->assertOk()->getContent();

    expect(preg_match('#<div[^>]*class="fixed inset-0 z-\[60\][^"]*\bhidden"[^>]*id="contact-modal"#', $content))->toBe(1);
})->with('contact pages');

test('the landing page contact section still opens the same form', function () {
    $content = $this->get('/')->assertOk()->getContent();

    expect(substr_count($content, 'openContactModal()'))->toBeGreaterThanOrEqual(2)
        ->and(substr_count($content, 'function openContactModal'))->toBe(1);
});

test('the floating button sits above the phone tab bar while it is open and never overlaps it', function () {
    $content = $this->get('/')->assertOk()->getContent();

    expect($content)->toContain('html[data-bottom-nav="open"] .contact-fab{bottom:calc(var(--bn-h,64px) + 16 * var(--u))}')
        ->toContain('z-index:45');
});

test('the tab bar has one sliding track that also runs when the next page loads, and keeps its state while browsing', function (string $uri) {
    $content = $this->get($uri)->assertOk()->getContent();

    expect($content)->toContain('class="bn-track"')
        ->toContain("sessionStorage.getItem('bottomNavOpen')")
        ->toContain("'bottomNavFrom'")
        ->toContain('data-bottom-nav')
        ->toContain('html[data-bottom-nav="closed"] #bottom-nav{transform:translateY(100%);visibility:hidden');
})->with('contact pages');

test('the hamburger controls the tab bar and tells assistive technology whether it is open', function (string $uri) {
    $content = $this->get($uri)->assertOk()->getContent();

    expect($content)->toContain('id="bottom-nav-toggle"')
        ->toContain('aria-expanded="false"')
        ->toContain("'Tutup menu navigasi'")
        ->toContain("'Buka menu navigasi'");
})->with('contact pages');

test('with a WhatsApp number set, the form on any page sends the visitor to the same prefilled WhatsApp chat', function (string $from) {
    GlobalSetting::current()->update(['whatsapp_number' => '6281234567890']);

    $response = $this->from($from)->post(route('contact.store'), [
        'name' => 'Siti',
        'email' => 'siti@example.com',
        'message' => 'Mohon penawaran.',
    ]);

    $response->assertRedirectContains('https://wa.me/6281234567890?text=');
    expect(urldecode((string) $response->headers->get('Location')))->toContain('Halo, saya Siti.')->toContain('Mohon penawaran.')
        ->and(ContactSubmission::query()->where('email', 'siti@example.com')->exists())->toBeTrue();
})->with(['/', '/about', '/works', '/portofolio', '/license']);

test('the phone menu button has no border', function () {
    $css = file_get_contents(resource_path('views/layouts/navbar.blade.php'));

    expect($css)->toContain('border:0;border-radius:8px;background:transparent')
        ->not->toContain('border:1px solid #E2E8F0;border-radius:8px;background:#fff');
});
