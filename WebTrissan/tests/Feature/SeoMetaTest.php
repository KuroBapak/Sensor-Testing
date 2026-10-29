<?php

use App\Models\GlobalSetting;
use App\Models\PageSetting;
use App\Support\StructuredData;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('every public page declares its language, canonical url and social tags', function (string $routeName) {
    $url = route($routeName);

    $content = $this->get($url)->assertOk()->getContent();

    expect($content)
        ->toMatch('/<html[^>]*lang="id"/')
        ->toContain('<link rel="canonical" href="'.$url.'">')
        ->toContain('<meta property="og:title" content="')
        ->toContain('<meta property="og:url" content="'.$url.'">')
        ->toContain('<meta name="twitter:card" content="')
        ->toContain('<meta property="og:site_name" content="'.e(GlobalSetting::current()->company_name).'">');
})->with(['home', 'about', 'works', 'portfolio', 'licenses']);

/**
 * Fetch a page and return its decoded JSON-LD graph keyed by schema type.
 *
 * @return array<string, array<string, mixed>>
 */
function jsonLdGraphOf(string $url): array
{
    $content = test()->get($url)->assertOk()->getContent();

    expect(preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $content, $matches))->toBe(1);

    $decoded = json_decode($matches[1][0], true, flags: JSON_THROW_ON_ERROR);

    expect($decoded['@context'])->toBe('https://schema.org');

    return collect($decoded['@graph'])->keyBy('@type')->all();
}

test('every public page describes the organization and website as JSON-LD from the CMS, without workshop machines as branches', function (string $routeName) {
    $graph = jsonLdGraphOf(route($routeName));
    $global = GlobalSetting::current();

    expect($graph['Organization'])
        ->toMatchArray([
            'name' => $global->company_name,
            'url' => url('/'),
            'email' => $global->primary_email,
            'telephone' => '+622188373107',
        ])
        ->and($graph['Organization']['address']['streetAddress'])->toBe($global->factory_address)
        ->and($graph['Organization']['contactPoint'])->toHaveCount(2)
        ->and($graph['Organization'])->not->toHaveKey('location')
        ->and($graph['WebSite'])->toMatchArray(['name' => $global->company_name, 'inLanguage' => 'id']);
})->with(['home', 'about', 'works', 'portfolio', 'licenses']);

test('breadcrumbs are described for inner pages but not the home page', function () {
    expect(jsonLdGraphOf(route('home')))->not->toHaveKey('BreadcrumbList');

    $items = jsonLdGraphOf(route('about'))['BreadcrumbList']['itemListElement'];

    expect($items)->toHaveCount(2)
        ->and($items[0])->toMatchArray(['position' => 1, 'name' => 'Beranda', 'item' => url('/')])
        ->and($items[1])->toMatchArray(['position' => 2, 'name' => 'Tentang Kami', 'item' => route('about')]);
});

test('only real social profiles are listed as sameAs, not bare site roots', function () {
    expect(jsonLdGraphOf(route('home'))['Organization'])->not->toHaveKey('sameAs');

    GlobalSetting::current()->update(['social_media' => [
        ['platform_name' => 'Instagram', 'profile_url' => 'https://www.instagram.com/trissan.id'],
        ['platform_name' => 'Facebook', 'profile_url' => 'https://www.facebook.com/'],
        ['platform_name' => 'Skrip', 'profile_url' => 'javascript:alert(1)'],
    ]]);

    expect(jsonLdGraphOf(route('home'))['Organization']['sameAs'])->toBe(['https://www.instagram.com/trissan.id']);
});

test('CMS text cannot break out of the JSON-LD script tag', function () {
    $hostile = 'Evil</script><script>alert(1)</script>';
    GlobalSetting::current()->update(['company_name' => $hostile]);

    $this->get(route('home'))->assertOk()->assertDontSee('<script>alert(1)</script>', false);

    expect(jsonLdGraphOf(route('home'))['Organization']['name'])->toBe($hostile);
});

test('no JSON-LD is produced without a company name', function () {
    expect(StructuredData::json(null, 'home'))->toBeNull()
        ->and(StructuredData::json(new GlobalSetting(['company_name' => '']), 'home'))->toBeNull();
});

test('SEO title and description entered in the CMS reach the title, description and social tags of every page', function (string $page, string $routeName) {
    PageSetting::forPage($page)->update([
        'seo_title' => "Judul CMS untuk {$page}",
        'seo_description' => "Deskripsi CMS untuk halaman {$page} yang diisi dari panel admin.",
    ]);

    $content = $this->get(route($routeName))->assertOk()->getContent();

    expect($content)
        ->toContain("<title>Judul CMS untuk {$page}</title>")
        ->toContain("<meta name=\"description\" content=\"Deskripsi CMS untuk halaman {$page} yang diisi dari panel admin.\">")
        ->toContain("<meta property=\"og:title\" content=\"Judul CMS untuk {$page}\">")
        ->toContain("<meta property=\"og:description\" content=\"Deskripsi CMS untuk halaman {$page} yang diisi dari panel admin.\">")
        ->toContain("<meta name=\"twitter:title\" content=\"Judul CMS untuk {$page}\">")
        ->toContain("<meta name=\"twitter:description\" content=\"Deskripsi CMS untuk halaman {$page} yang diisi dari panel admin.\">");
})->with([
    'landing' => ['landing', 'home'],
    'about' => ['about', 'about'],
    'works' => ['works', 'works'],
    'portfolio' => ['portfolio', 'portfolio'],
    'licenses' => ['licenses', 'licenses'],
]);

test('public view templates hard-code no company brand, so every brand mention comes from the CMS', function () {
    $templates = array_merge(
        glob(resource_path('views/*.blade.php')),
        glob(resource_path('views/layouts/*.blade.php')),
        glob(resource_path('views/components/*.blade.php')),
    );

    foreach ($templates as $template) {
        if (basename($template) === 'welcome.blade.php') {
            continue;
        }

        expect(file_get_contents($template))->not->toMatch('/apex|trissan/i', basename($template));
    }
});

test('hero texts left empty in the CMS render nothing instead of hard-coded fallback copy', function () {
    PageSetting::query()->whereIn('page', ['works', 'licenses'])->update([
        'hero_title' => null,
        'hero_subtitle' => null,
        'hero_badge_card' => null,
    ]);

    foreach (['works', 'licenses'] as $routeName) {
        $this->get(route($routeName))
            ->assertOk()
            ->assertDontSee('DAFTAR PRODUK')
            ->assertDontSee('Portofolio Lisensi, Akreditasi')
            ->assertDontSee('Fasilitas Terakreditasi Penuh')
            ->assertDontSee('Apex');
    }
});

test('seeded meta descriptions fit within search result snippets', function () {
    foreach (PageSetting::all() as $page) {
        expect(mb_strlen($page->seo_description))->toBeBetween(70, 160, "seo_description of {$page->page}");
    }
});

test('page title falls back to the company name instead of a hard-coded brand', function () {
    PageSetting::query()->delete();

    $this->get(route('works'))->assertOk()->assertSee('<title>'.e(GlobalSetting::current()->company_name).'</title>', false);
});

test('sitemap lists every public page with its last modified date', function () {
    $response = $this->get(route('sitemap'))->assertOk();

    expect($response->headers->get('Content-Type'))->toContain('application/xml');

    foreach (['home', 'about', 'works', 'portfolio', 'licenses'] as $routeName) {
        $response->assertSee('<loc>'.route($routeName).'</loc>', false);
    }

    $response->assertSee('<lastmod>', false);
});

test('robots.txt hides the admin panel and points to the sitemap', function () {
    $response = $this->get(route('robots'))->assertOk();

    expect($response->headers->get('Content-Type'))->toContain('text/plain')
        ->and($response->getContent())->toContain("Disallow: /admin\n")->toContain('Sitemap: '.route('sitemap'));
});

test('decorative icon glyphs are hidden from assistive technology and the partner banner has alt text', function () {
    $content = $this->get(route('home'))->assertOk()->getContent();

    expect($content)
        ->not->toContain('<span class="material-symbols-outlined"')
        ->toContain('aria-hidden="true" class="material-symbols-outlined"')
        ->toContain('alt="Logo mitra perusahaan"');
});
