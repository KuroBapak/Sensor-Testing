<?php

use App\Filament\Resources\ServiceResource\Pages\EditService;
use App\Models\GlobalSetting;
use App\Models\Service;
use App\Models\User;
use App\Models\Work;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

/**
 * Fetch a page and return its decoded JSON-LD graph keyed by schema type.
 *
 * @return array<string, array<string, mixed>>
 */
function serviceJsonLdGraphOf(string $url): array
{
    $content = test()->get($url)->assertOk()->getContent();

    expect(preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $content, $matches))->toBe(1);

    return collect(json_decode($matches[1][0], true, flags: JSON_THROW_ON_ERROR)['@graph'])->keyBy('@type')->all();
}

test('a service with full landing content is served at /layanan/{slug} and shows its CMS content', function () {
    $service = Service::factory()->withLandingContent()->create(['slug' => 'fabrikasi-baja']);

    expect(route('services.show', $service, false))->toBe('/layanan/fabrikasi-baja');

    $response = $this->get('/layanan/fabrikasi-baja')
        ->assertOk()
        ->assertViewIs('service')
        ->assertSee($service->service_title)
        ->assertSee($service->service_description)
        ->assertSee($service->landing_paragraphs[0])
        ->assertSee('Tahapan Pengerjaan')
        ->assertSee('Pertanyaan yang Sering Diajukan')
        ->assertSee('Minta Penawaran');

    expect($response->getContent())
        ->toMatch('/<html[^>]*lang="id"/')
        ->toContain('aria-label="Breadcrumb"');
    foreach ($service->process_steps as $step) {
        $response->assertSee($step['title']);
    }
    foreach ($service->faqs as $faq) {
        $response->assertSee($faq['question']);
    }
});

test('an unknown service slug is a 404', function () {
    $this->get('/layanan/tidak-ada')->assertNotFound();
});

test('the landing page lists the service works with their CMS photos and no works of other services', function () {
    $service = Service::factory()->withLandingContent()->create();
    $other = Service::factory()->create();
    Work::create(['service_id' => $service->id, 'work_title' => 'Proyek Jig Bekasi', 'work_slug' => 'proyek-jig-bekasi', 'work_image' => 'https://cdn.example.test/proyek-jig.jpg', 'sort_order' => 1]);
    Work::create(['service_id' => $other->id, 'work_title' => 'Proyek Layanan Lain', 'work_slug' => 'proyek-lain', 'sort_order' => 1]);

    $this->get(route('services.show', $service))
        ->assertOk()
        ->assertSee('Proyek Jig Bekasi')
        ->assertSee('src="https://cdn.example.test/proyek-jig.jpg" alt="Proyek Jig Bekasi"', false)
        ->assertSee('href="'.url('/works#proyek-jig-bekasi').'"', false)
        ->assertDontSee('Proyek Layanan Lain');
});

test('the landing page offers contact options from the global settings', function () {
    GlobalSetting::current()->update([
        'phones' => [['display_text' => '+62 812-7000-2222', 'tel_url' => 'tel:+6281270002222', 'is_whatsapp' => true]],
    ]);
    $service = Service::factory()->withLandingContent()->create();

    $this->get(route('services.show', $service))
        ->assertOk()
        ->assertSee('href="'.url('/#contact-modal').'"', false)
        ->assertSee('href="tel:+6281270002222"', false)
        ->assertSee('https://wa.me/6281270002222', false);
});

test('each landing page has a self canonical url and its own title, description and social tags', function () {
    $first = Service::factory()->withLandingContent()->create(['service_title' => 'Fabrikasi Baja']);
    $second = Service::factory()->withLandingContent()->create(['service_title' => 'Instalasi Panel']);
    $company = e(GlobalSetting::current()->company_name);

    $content = $this->get(route('services.show', $first).'?utm_source=iklan')->assertOk()->getContent();
    $secondContent = $this->get(route('services.show', $second))->assertOk()->getContent();
    $description = e($first->metaDescription());

    expect($content)
        ->toContain("<title>Fabrikasi Baja | {$company}</title>")
        ->toContain('<link rel="canonical" href="'.route('services.show', $first).'">')
        ->toContain('<meta name="description" content="'.$description.'">')
        ->toContain('<meta property="og:title" content="Fabrikasi Baja | '.$company.'">')
        ->toContain('<meta property="og:description" content="'.$description.'">')
        ->toContain('<meta property="og:url" content="'.route('services.show', $first).'">')
        ->not->toContain('name="robots"')
        ->and($secondContent)
        ->toContain("<title>Instalasi Panel | {$company}</title>")
        ->not->toContain($description);
    expect(mb_strlen($first->metaDescription()))->toBeLessThanOrEqual(155);
});

test('the landing page describes the service as JSON-LD linked to the organization, with breadcrumbs', function () {
    $service = Service::factory()->withLandingContent()->create(['service_title' => 'Fabrikasi Baja', 'short_title' => 'Baja']);
    $url = route('services.show', $service);

    $graph = serviceJsonLdGraphOf($url);

    expect($graph['Service'])->toMatchArray([
        'name' => 'Fabrikasi Baja',
        'url' => $url,
        'provider' => ['@id' => $graph['Organization']['@id']],
    ])->and($graph['BreadcrumbList']['itemListElement'])->toHaveCount(2)
        ->and($graph['BreadcrumbList']['itemListElement'][0])->toMatchArray(['position' => 1, 'name' => 'Beranda', 'item' => url('/')])
        ->and($graph['BreadcrumbList']['itemListElement'][1])->toMatchArray(['position' => 2, 'name' => 'Baja', 'item' => $url]);
});

test('a service without enough landing content stays reachable but is noindex without a Service node', function (array $thinContent) {
    $service = Service::factory()->withLandingContent()->create($thinContent);

    expect($service->hasLandingContent())->toBeFalse();

    $content = $this->get(route('services.show', $service))->assertOk()->getContent();

    expect($content)->toContain('<meta name="robots" content="noindex, follow">');
    expect(serviceJsonLdGraphOf(route('services.show', $service)))->not->toHaveKey('Service')->toHaveKey('BreadcrumbList');
})->with([
    'one-line CMS description only' => [['landing_body' => null, 'process_steps' => null, 'faqs' => null]],
    'body too short' => [['landing_body' => 'Hanya satu kalimat pendek.']],
    'too few process steps' => [['process_steps' => [['title' => 'Survei', 'description' => 'Cek lokasi.']]]],
    'too few FAQ entries' => [['faqs' => [['question' => 'Berapa lama?', 'answer' => 'Tergantung lingkup.']]]],
    'FAQ entries without answers' => [['faqs' => array_fill(0, 5, ['question' => 'Berapa lama?', 'answer' => ''])]],
]);

test('every seeded one-line service is noindex until real content is entered', function () {
    $service = Service::query()->orderBy('sort_order')->firstOrFail();

    expect($service->hasLandingContent())->toBeFalse();

    $this->get(route('services.show', $service))->assertOk()->assertSee('<meta name="robots" content="noindex, follow">', false);
});

test('the sitemap lists indexable service pages with a last modified date and omits thin ones', function () {
    $ready = Service::factory()->withLandingContent()->create();
    $thin = Service::factory()->create();

    $response = $this->get(route('sitemap'))->assertOk();

    $response->assertSee('<loc>'.route('services.show', $ready).'</loc>', false)
        ->assertDontSee(route('services.show', $thin), false);
    expect($response->getContent())->toMatch('#<loc>'.preg_quote(route('services.show', $ready), '#').'</loc>\s*<lastmod>[^<]+</lastmod>#');
});

test('home page cards link to the landing page only for services that have content', function () {
    $ready = Service::factory()->withLandingContent()->create(['slug' => 'siap-tayang']);
    $thin = Service::factory()->create(['slug' => 'masih-tipis']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('href="'.route('services.show', $ready).'"', false)
        ->assertDontSee(route('services.show', $thin), false)
        ->assertSee('href="'.url('/works?category=masih-tipis').'"', false);
});

test('related services in the same cluster link to their landing page when ready and to the works list otherwise', function () {
    $service = Service::factory()->withLandingContent()->create();
    $ready = Service::factory()->withLandingContent()->create(['service_cluster_id' => $service->service_cluster_id, 'slug' => 'kembaran-siap']);
    Service::factory()->create(['service_cluster_id' => $service->service_cluster_id, 'slug' => 'kembaran-tipis']);

    $this->get(route('services.show', $service))
        ->assertOk()
        ->assertSee('href="'.route('services.show', $ready).'"', false)
        ->assertSee('href="'.url('/works?category=kembaran-tipis').'"', false);
});

test('admin can enter the landing page content for a service in the CMS', function () {
    $admin = User::factory()->create(['email' => 'admin_service_landing@example.com']);
    $service = Service::factory()->create();
    $content = Service::factory()->withLandingContent()->make();

    Livewire\Livewire::actingAs($admin)
        ->test(EditService::class, ['record' => $service->getRouteKey()])
        ->assertSuccessful()
        ->fillForm([
            'landing_body' => $content->landing_body,
            'process_steps' => $content->process_steps,
            'faqs' => $content->faqs,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($service->refresh()->hasLandingContent())->toBeTrue();
});
