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
    preg_match_all('#<div class="hero-slide[^>]*>\s*<img ([^>]*)>#', $this->get(route('home'))->assertOk()->getContent(), $matches);

    expect(count($matches[1]))->toBeGreaterThan(1)
        ->and($matches[1][0])->toContain('fetchpriority="high"')->not->toContain('loading="lazy"')
        ->and($matches[1][1])->toContain('fetchpriority="low"')->toContain('loading="lazy"');
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

    expect($content)
        ->not->toContain('lucide@latest')
        ->not->toMatch('#cdn\.tailwindcss\.com(?:\?|")#');
})->with(['home', 'about', 'works', 'portfolio', 'licenses']);

test('the mobile Kontak button does not depend on a brand color the page might not define', function (string $routeName) {
    $content = $this->get(route($routeName))->assertOk()->getContent();

    expect($content)->toMatch('#<a class="lg:hidden[^>]*bg-\[\#0A192F\][^>]*href="[^"]*/\#contact"#');
})->with(['home', 'works', 'portfolio', 'licenses']);
