<?php

use App\Models\GlobalSetting;
use App\Models\PageSetting;
use App\Models\Service;
use App\Support\BrandImages;
use App\Support\ImageVariants;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoServiceContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->seed(DatabaseSeeder::class);
});

/**
 * Store a real PNG of the given size on the fake public disk.
 */
function storePng(string $path, int $width, int $height, array $rgb = [10, 80, 170]): void
{
    $image = imagecreatetruecolor($width, $height);
    imagefill($image, 0, 0, imagecolorallocate($image, ...$rgb));
    ob_start();
    imagepng($image);
    Storage::disk('public')->put($path, (string) ob_get_clean());
}

/**
 * Give the company a wide logo stored on the fake public disk.
 */
function useWideLogo(int $width = 512, int $height = 448): GlobalSetting
{
    storePng('branding/logo.png', $width, $height);
    GlobalSetting::current()->update(['navbar_logo' => 'branding/logo.png', 'favicon' => null]);

    return GlobalSetting::current();
}

/**
 * The JSON-LD graph of a page, keyed by schema type (a type that appears once).
 *
 * @return array<string, array<string, mixed>>
 */
function pageSchemaGraph(string $uri): array
{
    $content = test()->get($uri)->assertOk()->getContent();
    preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $content, $matches);

    return collect(json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR)['@graph'])->keyBy('@type')->all();
}

test('the share picture is 1200 x 630 with the wide logo centered, not cropped', function () {
    $settings = useWideLogo();

    $social = BrandImages::social($settings);
    $image = imagecreatefromstring(Storage::disk('public')->get(ImageVariants::pathFromUrl($social['url'])));

    expect($social['width'])->toBe(1200)->and($social['height'])->toBe(630)
        ->and(imagesx($image))->toBe(1200)->and(imagesy($image))->toBe(630)
        ->and(imagecolorat($image, 5, 5) & 0xFFFFFF)->toBe(0xF8FAFC)
        ->and(imagecolorat($image, 600, 315) & 0xFFFFFF)->not->toBe(0xF8FAFC);
});

test('browser icons are square: transparent for the tab icon and solid white for the phone home screen', function () {
    $settings = useWideLogo();

    $tab = imagecreatefromstring(Storage::disk('public')->get(ImageVariants::pathFromUrl(BrandImages::icon($settings, 48))));
    $touch = imagecreatefromstring(Storage::disk('public')->get(ImageVariants::pathFromUrl(BrandImages::icon($settings, 180, '#FFFFFF'))));

    expect(imagesx($tab))->toBe(48)->and(imagesy($tab))->toBe(48)
        ->and((imagecolorat($tab, 0, 0) >> 24) & 0x7F)->toBe(127)
        ->and(imagesx($touch))->toBe(180)->and(imagesy($touch))->toBe(180)
        ->and(imagecolorat($touch, 0, 0) & 0xFFFFFF)->toBe(0xFFFFFF);
});

test('a new logo in the CMS gets new files and the same logo reuses the old ones', function () {
    $settings = useWideLogo();

    $first = BrandImages::social($settings)['url'];

    expect(BrandImages::social($settings)['url'])->toBe($first);

    $this->travel(5)->minutes();
    storePng('branding/logo.png', 600, 300, [200, 30, 30]);

    expect(BrandImages::social(GlobalSetting::current())['url'])->not->toBe($first);
});

test('nothing is made when there is no usable logo', function () {
    $settings = GlobalSetting::current();
    $settings->update(['navbar_logo' => null]);

    expect(BrandImages::social($settings->refresh()))->toBeNull()
        ->and(BrandImages::icon($settings, 48))->toBeNull();

    $settings->update(['navbar_logo' => 'https://example.com/logo.svg']);

    expect(BrandImages::social($settings->refresh()))->toBeNull();

    $settings->update(['navbar_logo' => 'branding/missing.png']);

    expect(BrandImages::icon($settings->refresh(), 48))->toBeNull();
});

test('pages share the generated picture with its size and a text description for social networks', function (string $uri) {
    useWideLogo();
    $name = GlobalSetting::current()->company_name;

    $content = $this->get($uri)->assertOk()->getContent();

    expect($content)->toMatch('#<meta property="og:image" content="[^"]*/storage/_brand/social-[0-9a-f]{10}\.png">#')
        ->toContain('<meta property="og:image:width" content="1200">')
        ->toContain('<meta property="og:image:height" content="630">')
        ->toContain('<meta property="og:image:alt" content="'.e($name).'">')
        ->toContain('<meta name="twitter:image:alt" content="'.e($name).'">')
        ->toMatch('#<meta name="twitter:image" content="[^"]*/storage/_brand/social-#');
})->with(['/', '/about', '/works', '/portofolio', '/license']);

test('the landing page starts loading its first slide before the stylesheet, as a responsive WebP when copies exist', function () {
    storePng('hero/slide.png', 2000, 1000);
    ImageVariants::generate('hero/slide.png');
    PageSetting::forPage('landing')->update(['hero_slides' => [
        ['image' => 'hero/slide.png', 'alt_text' => 'Slide satu'],
        ['image' => 'hero/other.png', 'alt_text' => 'Slide dua'],
    ]]);

    $content = $this->get('/')->assertOk()->getContent();

    expect($content)->toContain('<link rel="preload" as="image" href="'.asset('storage/hero/slide.png').'" type="image/webp" imagesrcset="')
        ->toContain('imagesizes="100vw" fetchpriority="high">')
        ->and(strpos($content, 'rel="preload" as="image"'))->toBeLessThan(strpos($content, 'rel="stylesheet"'));
});

test('the landing page preloads nothing when no slide is set', function () {
    PageSetting::forPage('landing')->update(['hero_slides' => []]);

    expect($this->get('/')->assertOk()->getContent())->not->toContain('rel="preload" as="image"');
});

test('the about page content sits inside a main landmark between the menu and the footer', function () {
    $content = $this->get('/about')->assertOk()->getContent();

    expect(substr_count($content, '<main'))->toBe(1)
        ->and(strpos($content, '<main'))->toBeLessThan(strpos($content, 'id="about-hero"'))
        ->and(strpos($content, '</main>'))->toBeLessThan(strpos($content, '<footer'));
});

test('project modals have a heading before a project is chosen, so no page carries an empty heading', function (string $uri) {
    expect($this->get($uri)->assertOk()->getContent())
        ->not->toMatch('#<h[1-6][^>]*id="modalTitle"[^>]*>\s*</h[1-6]>#');
})->with(['/works', '/portofolio']);

test('the schema describes the page itself, linked to the website, the organization and the breadcrumb', function () {
    $about = pageSchemaGraph('/about');
    $works = pageSchemaGraph('/works');
    $home = pageSchemaGraph('/');

    expect($about['AboutPage']['isPartOf']['@id'])->toEndWith('/#website')
        ->and($about['AboutPage']['about']['@id'])->toEndWith('/#organization')
        ->and($about['AboutPage']['breadcrumb']['@id'])->toBe($about['BreadcrumbList']['@id'])
        ->and($about['AboutPage']['name'])->not->toBeEmpty()
        ->and($works)->toHaveKey('CollectionPage')
        ->and($home)->toHaveKey('WebPage')->and($home['WebPage'])->not->toHaveKey('breadcrumb');
});

test('the organization lists every service as an offer, linking only the pages ready to be indexed', function () {
    $this->seed(DemoServiceContentSeeder::class);
    Service::query()->where('slug', 'jigs-fixtures')->update(['landing_body' => null]);

    $offers = collect(pageSchemaGraph('/')['Organization']['hasOfferCatalog']['itemListElement'])->pluck('itemOffered');

    expect($offers)->toHaveCount(12)
        ->and($offers->firstWhere('name', 'Material Handling')['url'])->toEndWith('/layanan/material-handling')
        ->and($offers->firstWhere('name', 'Jigs and Fixtures'))->not->toHaveKey('url');
});

test('the organization has its logo with a size, an area served and the share picture', function () {
    useWideLogo();

    $organization = pageSchemaGraph('/')['Organization'];

    expect($organization['logo'])->toMatchArray(['@type' => 'ImageObject', 'width' => 512, 'height' => 448])
        ->and($organization['areaServed'])->toBe(['@type' => 'Country', 'name' => 'Indonesia'])
        ->and($organization['image'])->toContain('/storage/_brand/social-');
});
