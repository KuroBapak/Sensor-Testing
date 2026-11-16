<?php

use App\Models\GlobalSetting;
use App\Models\PageSetting;
use App\Models\Service;
use App\Models\Work;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoServiceContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

/**
 * The first body paragraph of a service with its whitespace normalised, as the meta description is cut from it.
 */
function normalisedFirstParagraph(Service $service): string
{
    return trim((string) preg_replace('/\s+/', ' ', $service->landing_paragraphs[0] ?? ''));
}

test('no service meta description is cut in the middle of a word', function () {
    $this->seed(DemoServiceContentSeeder::class);

    foreach (Service::query()->get() as $service) {
        $description = $service->metaDescription();
        $text = normalisedFirstParagraph($service);
        $kept = rtrim($description, '…');

        expect(mb_strlen($description))->toBeLessThanOrEqual(Service::META_DESCRIPTION_LIMIT + 1)
            ->and(str_starts_with($text, $kept))->toBeTrue()
            ->and(mb_strlen($kept) === mb_strlen($text) || mb_substr($text, mb_strlen($kept), 1) === ' ')->toBeTrue();
    }
});

test('the description ends after whole sentences when they fit', function () {
    $service = Service::factory()->make(['landing_body' => 'Kalimat pertama yang pendek. Kalimat kedua juga pendek. Kalimat ketiga '.str_repeat('panjang sekali ', 12).'selesai.']);

    expect($service->metaDescription())->toBe('Kalimat pertama yang pendek. Kalimat kedua juga pendek.');
});

test('"PT." is an abbreviation and does not end a sentence', function () {
    $service = Service::factory()->make(['landing_body' => 'PT. Trissan adalah kontraktor mekanikal elektrikal untuk pabrik dan fasilitas industri yang membutuhkan instalasi andal, tepat waktu, dan terdokumentasi dengan baik oleh satu tim. Kalimat kedua.']);

    expect($service->metaDescription())->not->toBe('PT.')
        ->and($service->metaDescription())->toStartWith('PT. Trissan adalah kontraktor');
});

test('one very long first sentence is shortened at a word boundary with an ellipsis', function () {
    $service = Service::factory()->make(['landing_body' => str_repeat('kata ', 80).'selesai.']);

    $description = $service->metaDescription();

    expect($description)->toEndWith('kata…')
        ->and(mb_strlen($description))->toBeLessThanOrEqual(Service::META_DESCRIPTION_LIMIT + 1);
});

test('a short text is used as it is, and a service without a body falls back to its one-liner', function () {
    expect(Service::factory()->make(['landing_body' => 'Teks pendek.'])->metaDescription())->toBe('Teks pendek.')
        ->and(Service::factory()->make(['landing_body' => null, 'service_description' => 'Satu kalimat dari CMS.'])->metaDescription())->toBe('Satu kalimat dari CMS.');
});

test('the page title uses the short service name and stays within what search results show', function () {
    $company = GlobalSetting::current()->company_name;
    $general = Service::query()->where('slug', 'general-supplier')->firstOrFail();

    expect($general->seoTitle($company))->toBe('Industrial Machinery & Parts | '.$company)
        ->and(Service::factory()->make(['short_title' => null, 'service_title' => 'Selection, Placement And Provision Activities domestic Part-time Labor'])->seoTitle($company))
        ->toContain(' | '.$company)->not->toContain('domestic Part-time Labor');

    foreach (Service::query()->get() as $service) {
        expect(mb_strlen($service->seoTitle($company)))->toBeLessThanOrEqual(Service::SEO_TITLE_LIMIT + 1);
    }
});

test('a service page is titled with its short name', function () {
    $this->seed(DemoServiceContentSeeder::class);
    $company = e(GlobalSetting::current()->company_name);

    $this->get('/layanan/general-supplier')->assertOk()->assertSee('<title>Industrial Machinery &amp; Parts | '.$company.'</title>', false);
});

test('every public page links to the service pages from the footer', function (string $uri) {
    $this->seed(DemoServiceContentSeeder::class);
    $content = $this->get($uri)->assertOk()->getContent();
    $footer = substr($content, (int) strpos($content, 'LAYANAN KAMI'));

    expect($content)->toContain('LAYANAN KAMI');

    foreach (Service::query()->get() as $service) {
        expect($footer)->toContain('href="'.route('services.show', $service).'"');
    }
})->with(['/', '/about', '/works', '/portofolio', '/license']);

test('the footer service list costs no extra queries however many services exist', function () {
    $queriesFor = function (string $uri): int {
        $count = 0;
        DB::listen(function () use (&$count): void {
            $count++;
        });
        $this->get($uri)->assertOk();

        return $count;
    };

    $queriesFor('/about');
    $before = $queriesFor('/about');
    Service::factory()->count(5)->create();

    expect($queriesFor('/about'))->toBe($before);
});

test('the landing page cards say what they open', function () {
    $this->seed(DemoServiceContentSeeder::class);
    Service::query()->where('slug', 'jigs-fixtures')->update(['landing_body' => null]);

    $content = $this->get('/')->assertOk()->getContent();

    expect(substr_count($content, '<span>Lihat layanan</span>'))->toBe(11)
        ->and(substr_count($content, '<span>Lihat proyek</span>'))->toBe(1);
});

test('the Lihat Proyek button on a service page only exists together with its project section', function () {
    $this->seed(DemoServiceContentSeeder::class);

    foreach (Service::query()->get() as $service) {
        $content = $this->get(route('services.show', $service))->assertOk()->getContent();

        expect(str_contains($content, 'href="#proyek"'))->toBe(str_contains($content, 'id="proyek"'));
    }
});

test('the license download button has no placeholder link before a certificate is chosen', function () {
    expect($this->get('/license')->assertOk()->getContent())->not->toContain('href="#"');
});

test('a page is as new as the newest record shown on it, not as new as the seeding', function () {
    $newest = now()->addDay()->startOfSecond();
    Work::query()->first()->forceFill(['updated_at' => $newest])->saveQuietly();
    PageSetting::query()->update(['updated_at' => now()->subYear()]);

    $xml = simplexml_load_string($this->get(route('sitemap'))->assertOk()->getContent());
    $dates = [];

    foreach ($xml->url as $url) {
        $dates[(string) $url->loc] = (string) $url->lastmod;
    }

    $lastmodOf = fn (string $path): ?string => $dates[url($path)] ?? null;

    expect($lastmodOf('/works'))->toBe($newest->toAtomString())
        ->and($lastmodOf('/portofolio'))->not->toBe($newest->toAtomString());
});
