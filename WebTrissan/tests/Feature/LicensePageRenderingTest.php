<?php

use App\Models\CertificationCategory;
use App\Models\CorporateAccreditation;
use App\Models\ProductCertification;
use App\Models\Work;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('license page renders a category tab per certification category', function () {
    $response = $this->get(route('licenses'))->assertOk();

    foreach (CertificationCategory::all() as $category) {
        $response->assertSee('id="btn-cat-'.$category->slug.'"', false)
            ->assertSee(e($category->name), false);
    }
});

test('license page renders one table row per corporate accreditation', function () {
    $content = $this->get(route('licenses'))->assertOk()->getContent();

    expect(substr_count($content, 'class="cert-row'))->toBe(CorporateAccreditation::count());

    foreach (CorporateAccreditation::all() as $accreditation) {
        expect($content)->toContain('data-reg="'.e($accreditation->registration_id).'"');
    }

    expect($content)->not->toContain('QMS-ID-980144');
});

test('accreditation row exposes the modal data and an indonesian validity label', function () {
    $category = CertificationCategory::first();
    CorporateAccreditation::create([
        'certification_category_id' => $category->id,
        'standard_name' => 'ISO Uji 9999',
        'standard_description' => 'Sistem Uji',
        'accreditation_body' => 'Badan Uji',
        'registration_id' => 'REG-UJI-1',
        'validity_date' => '2027-03-24',
        'scope_detail' => 'Lingkup pengujian modal',
        'modal_title' => 'Judul Modal Uji',
        'certificate_image' => 'certs/uji.png',
        'sort_order' => 99,
    ]);

    $this->get(route('licenses'))
        ->assertOk()
        ->assertSee('data-category="'.$category->slug.'"', false)
        ->assertSee('data-title="Judul Modal Uji"', false)
        ->assertSee('data-reg="REG-UJI-1"', false)
        ->assertSee('data-body="Badan Uji"', false)
        ->assertSee('data-validity="24 Mar 2027"', false)
        ->assertSee('data-scope="Lingkup pengujian modal"', false)
        ->assertSee('data-image="'.asset('storage/certs/uji.png').'"', false);
});

test('accreditation row falls back to the standard name and description for the modal title', function () {
    CorporateAccreditation::create([
        'certification_category_id' => CertificationCategory::first()->id,
        'standard_name' => 'ISO Tanpa Judul',
        'standard_description' => 'Deskripsi Singkat',
        'scope_detail' => 'Lingkup',
        'sort_order' => 98,
    ]);

    $this->get(route('licenses'))->assertSee('data-title="ISO Tanpa Judul Deskripsi Singkat"', false);
});

test('license page renders every product certification card with its spec rows', function () {
    $content = $this->get(route('licenses'))->assertOk()->getContent();

    expect(substr_count($content, 'class="cert-card'))->toBe(ProductCertification::count());

    foreach (ProductCertification::all() as $certification) {
        expect($content)
            ->toContain('id="cert-'.$certification->slug.'"')
            ->toContain(e($certification->product_name))
            ->toContain('Valid s/d '.$certification->validity_date->format('Y'));

        foreach ($certification->spec_rows as $row) {
            expect($content)->toContain(e($row['label']).':')->toContain(e($row['value']));
        }
    }

    expect($content)->not->toContain('cert-rpm-buzzer')->not->toContain('Check SHA');
});

test('product certification keeps slugs that already start with cert as anchors', function () {
    ProductCertification::create(['slug' => 'cert-uji-anchor', 'product_name' => 'Produk Anchor']);

    $this->get(route('licenses'))->assertSee('id="cert-uji-anchor"', false)->assertDontSee('id="cert-cert-uji-anchor"', false);
});

test('product certification shows only four spec rows and the featured label', function () {
    ProductCertification::create([
        'slug' => 'banyak-spek',
        'product_name' => 'Produk Banyak Spek',
        'is_featured' => true,
        'spec_rows' => array_map(fn (int $n) => ['label' => "Label {$n}", 'value' => "Nilai {$n}"], range(1, 6)),
    ]);

    $this->get(route('licenses'))
        ->assertSee('Label 4:', false)
        ->assertDontSee('Label 5:', false)
        ->assertSee('Unggulan');
});

test('product certification download link only appears when the pdf exists', function () {
    ProductCertification::query()->update(['certificate_pdf' => null]);
    ProductCertification::create(['slug' => 'dengan-pdf', 'product_name' => 'Produk Dengan PDF', 'certificate_pdf' => 'certificates/uji.pdf']);

    $content = $this->get(route('licenses'))->assertOk()->getContent();

    expect(substr_count($content, 'download href="http'))->toBe(1)
        ->and($content)->toContain('href="'.asset('storage/certificates/uji.pdf').'"');
});

test('product certification links to its related work', function () {
    $work = Work::first();
    ProductCertification::create(['slug' => 'terkait-work', 'product_name' => 'Produk Terkait', 'work_id' => $work->id]);

    $this->get(route('licenses'))->assertSee('href="'.url('/works#'.$work->work_slug).'"', false);
});

test('license page escapes cms text', function () {
    ProductCertification::create(['slug' => 'xss', 'product_name' => '<script>alert(1)</script>']);

    $this->get(route('licenses'))->assertDontSee('<script>alert(1)</script>', false);
});

test('license page renders without rows or cards when there is no certification data', function () {
    ProductCertification::query()->delete();
    CorporateAccreditation::query()->delete();

    $content = $this->get(route('licenses'))->assertOk()->getContent();

    expect($content)->not->toContain('class="cert-row')->not->toContain('class="cert-card')
        ->and($content)->toContain('id="certEmptyRow"');
});

test('license table hides secondary columns on phones and repeats their data under the standard name', function () {
    $accreditation = CorporateAccreditation::first();

    $this->get(route('licenses'))
        ->assertOk()
        ->assertSee('hidden sm:table-cell', false)
        ->assertSee('<div class="sm:hidden font-mono text-[11px] text-primary font-medium">'.e($accreditation->registration_id).'</div>', false);
});
