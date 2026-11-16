<?php

use App\Http\Middleware\HideFromSearchEngines;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('pages are open to search engines by default', function (string $uri) {
    $response = $this->get($uri)->assertOk();

    expect($response->headers->has('X-Robots-Tag'))->toBeFalse()
        ->and($response->getContent())->not->toContain('content="noindex');
})->with(['/', '/about', '/works', '/portofolio', '/license']);

test('with SEO_INDEXABLE off, every page and file carries noindex in a header and, for pages, a meta tag', function (string $uri) {
    config(['seo.indexable' => false]);

    $response = $this->get($uri)->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');

    if (! in_array($uri, ['/sitemap.xml', '/robots.txt'], true)) {
        expect($response->getContent())->toContain('<meta name="robots" content="noindex, nofollow">');
    }
})->with(['/', '/about', '/works', '/portofolio', '/license', '/sitemap.xml', '/robots.txt']);

test('a hidden site still lets crawlers fetch pages, because a crawler that cannot fetch a page never sees its noindex', function () {
    config(['seo.indexable' => false]);

    expect($this->get('/robots.txt')->getContent())->not->toContain('Disallow: /'.PHP_EOL)
        ->and($this->get('/robots.txt')->getContent())->toContain('Disallow: /admin');
});

test('the 404 page and the admin login are hidden too', function () {
    config(['seo.indexable' => false]);

    $this->get('/tidak-ada')->assertNotFound()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    $this->get('/admin/login')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
});

test('the middleware is registered for every response', function () {
    expect(app('router')->getMiddleware())->toBeArray();
    $kernelMiddleware = app(Kernel::class)->getGlobalMiddleware();

    expect($kernelMiddleware)->toContain(HideFromSearchEngines::class);
});
