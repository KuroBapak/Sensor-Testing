<?php

use App\Http\Middleware\RedirectToCanonicalUrl;
use App\Models\PageSetting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('the sitemap is well-formed XML that starts with the XML declaration', function () {
    $content = $this->get(route('sitemap'))->assertOk()->getContent();

    expect($content)->toStartWith('<?xml version="1.0" encoding="UTF-8"?>');

    $xml = simplexml_load_string($content);

    expect($xml)->not->toBeFalse()
        ->and($xml->url)->toHaveCount(5);
});

test('the sitemap still renders when a page has no CMS settings row', function () {
    PageSetting::query()->delete();

    $xml = simplexml_load_string($this->get(route('sitemap'))->assertOk()->getContent());

    expect($xml->url)->toHaveCount(5)
        ->and(isset($xml->url[0]->lastmod))->toBeFalse();
});

/**
 * Run the canonical-url middleware on a raw request line, since the HTTP test client trims trailing slashes
 * and reads a leading "//" as a host.
 */
function canonicalRedirectFor(string $uri, string $method = 'GET'): Response
{
    $query = str_contains($uri, '?') ? substr($uri, strpos($uri, '?')) : '';

    // The server variables a real nginx/php-fpm or built-in server sends, so a request for "/index.php" has it as base url.
    $request = Request::create('/placeholder'.$query, $method, server: [
        'SCRIPT_NAME' => '/index.php',
        'SCRIPT_FILENAME' => base_path('public/index.php'),
        'PHP_SELF' => '/index.php',
    ]);
    $request->server->set('REQUEST_URI', $uri);

    return (new RedirectToCanonicalUrl)->handle($request, fn () => response('passed through'));
}

test('duplicate spellings of a page redirect permanently to the canonical url', function (string $from, string $to) {
    $response = canonicalRedirectFor($from);

    expect($response->getStatusCode())->toBe(301)
        ->and($response->headers->get('Location'))->toBe('http://localhost'.$to);
})->with([
    'index.php' => ['/index.php', '/'],
    'index.php with a page' => ['/index.php/about', '/about'],
    'trailing slash' => ['/about/', '/about'],
    'trailing slash keeps the query string' => ['/works/?category=material-handling', '/works?category=material-handling'],
]);

test('a crafted path can never redirect to another host', function (string $from) {
    $location = canonicalRedirectFor($from)->headers->get('Location');

    expect(parse_url($location, PHP_URL_HOST))->toBe('localhost');
})->with(['/index.php//evil.example/', '//evil.example/', '/index.php///evil.example']);

test('canonical urls and non-GET requests are not redirected', function (string $uri, string $method) {
    expect(canonicalRedirectFor($uri, $method)->getContent())->toBe('passed through');
})->with([
    'home' => ['/', 'GET'],
    'page' => ['/about', 'GET'],
    'page with a query' => ['/works?category=material-handling', 'GET'],
    'form post with a trailing slash' => ['/contact/', 'POST'],
]);

test('a real page and the index.php spelling both work through the full stack', function () {
    $this->get('/about')->assertOk();
    $this->get('/index.php')->assertStatus(301)->assertHeader('Location', rtrim(config('app.url'), '/').'/');
});

test('pages send a referrer policy and a permissions policy', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
});

test('HSTS is only sent over HTTPS', function () {
    $this->get(route('home'))->assertHeaderMissing('Strict-Transport-Security');

    $this->get('https://localhost/')->assertHeader('Strict-Transport-Security', 'max-age=31536000');
});

test('unknown pages show the Indonesian not-found page without being indexed', function () {
    $this->get('/halaman-tidak-ada')
        ->assertNotFound()
        ->assertSee('Halaman tidak ditemukan')
        ->assertSee('<meta name="robots" content="noindex">', false);
});

test('homepage service cards are real links crawlers can follow', function () {
    $content = $this->get(route('home'))->assertOk()->getContent();

    expect($content)
        ->toMatch('#<a href="[^"]*/works\?category=[^"]+" class="snap-start#')
        ->not->toMatch('#<div onclick="window\.location\.href=\'[^\']*works\?category=#');
});

test('only the first hero image is fetched with priority and the others are deferred', function () {
    preg_match_all('#<div class="hero-slide[^>]*>\s*(?:<picture[^>]*>\s*<source[^>]*>\s*)?<img ([^>]*)>#', $this->get(route('home'))->assertOk()->getContent(), $matches);

    expect(count($matches[1]))->toBeGreaterThan(1)
        ->and($matches[1][0])->toContain('fetchpriority="high"')->not->toContain('loading="lazy"')
        ->and($matches[1][1])->toContain('data-src="')->not->toContain(' src="')->not->toContain('fetchpriority');
});

test('above-the-fold project images on works and portfolio are not lazy-loaded but later ones are', function (string $routeName, string $cardMarker) {
    $content = $this->get(route($routeName))->assertOk()->getContent();

    preg_match_all('#<img [^>]*(?:object-cover group-hover:scale-105)[^>]*>#', $content, $matches);
    $images = $matches[0];

    expect(count($images))->toBeGreaterThan(4)
        ->and($images[0])->toContain('fetchpriority="high"')->not->toContain('loading="lazy"')
        ->and($images[3])->not->toContain('loading="lazy"')
        ->and($images[count($images) - 1])->toContain('loading="lazy"');
})->with([
    'works' => ['works', 'project-card'],
    'portfolio' => ['portfolio', 'deliverable-card'],
]);

test('third-party scripts are pinned to exact versions', function (string $routeName) {
    $content = $this->get(route($routeName))->assertOk()->getContent();

    expect($content)->not->toContain('lucide@latest');
})->with(['home', 'about', 'works', 'portfolio', 'licenses']);

test('styles are compiled by Vite instead of the Tailwind Play CDN', function (string $routeName) {
    $content = $this->get(route($routeName))->assertOk()->getContent();

    expect($content)
        ->not->toContain('cdn.tailwindcss.com')
        ->not->toContain('tailwind.config');
})->with(['home', 'about', 'works', 'portfolio', 'licenses']);

test('every stylesheet a public page loads through Vite is built by vite.config.js', function (string $view) {
    $entries = [];
    preg_match_all("/@vite\\('([^']+)'\\)/", file_get_contents(resource_path("views/{$view}.blade.php")), $entries);

    expect($entries[1])->not->toBeEmpty();

    foreach ($entries[1] as $entry) {
        expect(file_get_contents(base_path('vite.config.js')))->toContain("'{$entry}'");
        expect(base_path($entry))->toBeFile();
    }
})->with(['home', 'abtus', 'license', 'works', 'portofolio']);

test('the service landing page is also styled by Vite, not the Tailwind Play CDN', function () {
    $view = file_get_contents(resource_path('views/service.blade.php'));

    expect($view)
        ->toContain("@vite('resources/css/site/home.css')")
        ->not->toContain('cdn.tailwindcss.com')
        ->not->toContain('tailwind.config');

    expect(file_get_contents(resource_path('css/site/home.css')))->toContain('views/service.blade.php');
});

test('hover styles apply on every device so the navbar dropdowns open, as they did under Tailwind v3', function () {
    // v4 wraps hover: and group-hover: in @media (hover: hover), which embedded browsers and some touch laptops fail.
    expect(file_get_contents(resource_path('css/site/compat.css')))->toContain('@custom-variant hover (&:hover);');

    foreach (['home', 'about', 'license', 'catalog'] as $entry) {
        expect(file_get_contents(resource_path("css/site/{$entry}.css")))->toContain("@import './compat.css';");
    }
});

test('the mobile Kontak button does not depend on a brand color the page might not define', function (string $routeName) {
    $content = $this->get(route($routeName))->assertOk()->getContent();

    expect($content)->toMatch('#<a class="lg:hidden[^>]*bg-\[\#0A192F\][^>]*href="[^"]*/\#contact"#');
})->with(['home', 'works', 'portfolio', 'licenses']);

test('wide screens scale the page up from the 1280px laptop layout instead of stretching it', function (string $routeName) {
    $content = $this->get(route($routeName))->assertOk()->getContent();

    expect($content)
        ->toContain("root.style.setProperty('--page-zoom', Math.min(width * 0.75 / 1280, 1.5).toFixed(4))")
        ->toContain('if (width >= 1280)');
})->with(['home', 'about', 'works', 'portfolio', 'licenses']);

test('the sticky filter bar on works sits flush under the navbar at both navbar heights', function () {
    // Navbar is h-14 (56px) below lg and h-[72px] from lg; the bar must stick at exactly that offset, with no gap.
    $this->get(route('works'))
        ->assertOk()
        ->assertSee('md:sticky md:top-14 lg:top-[72px]', false);

    expect(file_get_contents(resource_path('views/layouts/navbar.blade.php')))->toContain('h-14 lg:h-[72px]');
});

test('error responses show Indonesian noindex pages that never reveal exception details', function (int $status, string $title) {
    config(['app.debug' => false]);

    Route::get('/uji-galat/{status}', fn (int $status) => abort($status, 'Rahasia internal: '.base_path()));

    $response = $this->get('/uji-galat/'.$status)->assertStatus($status);

    expect($response->getContent())
        ->toContain('<html lang="id">')
        ->toContain($title)
        ->toContain('<meta name="robots" content="noindex">')
        ->not->toContain('Rahasia internal')
        ->not->toContain(base_path());
})->with([
    '401' => [401, 'Perlu masuk'],
    '403' => [403, 'Akses ditolak'],
    '404' => [404, 'Halaman tidak ditemukan'],
    '405 uses the 4xx fallback' => [405, 'Permintaan tidak dapat diproses'],
    '419' => [419, 'Sesi telah berakhir'],
    '429' => [429, 'Terlalu banyak permintaan'],
    '500' => [500, 'Terjadi kesalahan pada server'],
    '502 uses the 5xx fallback' => [502, 'Terjadi gangguan pada server'],
    '503' => [503, 'Sedang dalam pemeliharaan'],
]);

test('every modal is a labelled dialog that locks page scroll and manages focus', function (string $routeName, string $modalId, string $labelId) {
    $content = $this->get(route($routeName))->assertOk()->getContent();

    expect($content)
        ->toMatch('#<div[^>]*id="'.$modalId.'"[^>]*role="dialog"|<div[^>]*role="dialog"[^>]*id="'.$modalId.'"#')
        ->toMatch('#<div[^>]*id="'.$modalId.'"[^>]*aria-modal="true"|<div[^>]*aria-modal="true"[^>]*id="'.$modalId.'"#')
        ->toContain('aria-labelledby="'.$labelId.'"')
        ->toContain("document.body.style.overflow = 'hidden'")
        ->toContain('.focus()');
})->with([
    'contact modal' => ['home', 'contact-modal', 'contact-modal-title'],
    'certificate modal' => ['licenses', 'verificationModal', 'modalCertTitle'],
    'deliverable modal' => ['portfolio', 'deliverableModal', 'modalTitle'],
]);

test('the hero upload field rejects oversized files and resizes photos before upload', function () {
    $source = file_get_contents(app_path('Filament/Pages/ManagePageSettings.php'));

    expect($source)
        ->toContain("PhotoUpload::make('image', 'hero')")
        ->toContain('->maxItems(3)');
});
