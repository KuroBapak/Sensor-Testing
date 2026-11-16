<?php

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

/**
 * The public pages that load fonts and icons.
 *
 * @return array<string, array{0: string}>
 */
dataset('public pages', [
    'home' => ['/'],
    'about' => ['/about'],
    'works' => ['/works'],
    'portfolio' => ['/portofolio'],
    'license' => ['/license'],
]);

test('no public page loads a third-party script or a Google text font', function (string $uri) {
    $content = $this->get($uri)->assertOk()->getContent();

    expect($content)->not->toContain('unpkg.com')
        ->not->toContain('family=Montserrat')
        ->not->toContain('family=Inter')
        ->not->toContain('family=JetBrains');
})->with('public pages');

test('the icon font holds only the curated icons, not the 1.1 MB variable font', function (string $uri) {
    $content = $this->get($uri)->assertOk()->getContent();

    expect(substr_count($content, 'fonts.googleapis.com/css2'))->toBe(1)
        ->and($content)->toContain('opsz,wght,FILL,GRAD@24,400,0,0&amp;icon_names=')
        ->toContain('&amp;display=block')
        ->not->toContain('wght,FILL@100..700');
})->with('public pages');

test('an icon name outside the curated list shows the generic icon instead of raw text', function () {
    $html = (string) $this->blade('<x-material-icon name="not_in_the_list" /><x-material-icon name="bolt" />');

    expect($html)->toContain('data-icon="category"')
        ->toContain('data-icon="bolt"')
        ->not->toContain('not_in_the_list');
});

test('icon names are drawn from a data attribute, so they never become page text', function (string $uri) {
    $content = $this->get($uri)->assertOk()->getContent();

    expect($content)->toContain('.material-symbols-outlined::before{content:attr(data-icon)}')
        ->and(preg_match('/class="[^"]*material-symbols-outlined[^"]*"[^>]*>\s*[a-z_0-9]+\s*</', $content))->toBe(0);
})->with('public pages');

test('the icon component keeps the name in a data attribute and hides the empty element from assistive tech', function () {
    $html = (string) $this->blade('<x-material-icon name="bolt" class="text-2xl" />');

    expect($html)->toContain('aria-hidden="true"')
        ->toContain('class="material-symbols-outlined text-2xl"')
        ->toContain('data-icon="bolt"></span>');
});

test('the works filter offers no sort that only made sense for machined parts', function () {
    $this->get('/works')->assertOk()
        ->assertSee('Terbaru')
        ->assertDontSee('Toleransi Tertinggi')
        ->assertDontSee('Skala Batch Terbesar')
        ->assertDontSee('Ti-6Al-4V')
        ->assertDontSee('ALL WORKS');
});

test('the shared stylesheet keeps a visible keyboard focus ring and honours reduced motion', function () {
    $css = file_get_contents(resource_path('css/site/compat.css'));

    expect($css)->toContain(':focus-visible')
        ->toContain('@media (prefers-reduced-motion: reduce)');
});

test('the landing page has no decorative pulsing dots and no leftover template comments', function () {
    $content = $this->get('/')->assertOk()->getContent();

    expect($content)->not->toContain('animate-pulse')
        ->not->toContain('<!--');
});
