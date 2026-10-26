<?php

use App\Models\Personnel;
use App\Models\PortfolioCategory;
use App\Models\PortfolioItem;
use App\Models\Skill;
use App\Models\SpecialtyCluster;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('portfolio page renders a tab per category with the total item count on the all tab', function () {
    $response = $this->get(route('portfolio'))
        ->assertOk()
        ->assertSee('Semua Hasil Kerja ('.PortfolioItem::count().')');

    foreach (PortfolioCategory::all() as $category) {
        $response->assertSee('data-category="'.$category->id.'"', false)
            ->assertSee(e($category->name), false);
    }
});

test('portfolio page renders one deliverable card per item with the data the modal needs', function () {
    $item = PortfolioItem::first();
    $item->update(['image' => 'portfolio/uji.jpg', 'description' => 'Deskripsi modal uji']);

    $content = $this->get(route('portfolio'))->assertOk()->getContent();

    expect(substr_count($content, 'class="deliverable-card'))->toBe(PortfolioItem::count())
        ->and($content)
        ->toContain('data-category="'.$item->portfolio_category_id.'"')
        ->toContain('data-title="'.e($item->title).'"')
        ->toContain('data-desc="Deskripsi modal uji"')
        ->toContain('data-image="'.asset('storage/portfolio/uji.jpg').'"')
        ->toContain('src="'.asset('storage/portfolio/uji.jpg').'"')
        ->not->toContain('lh3.googleusercontent.com/aida/');
});

test('portfolio deliverable falls back to a placeholder when the image file is missing', function () {
    PortfolioItem::query()->update(['image' => '/images/tidak-ada.jpg']);

    $content = $this->get(route('portfolio'))->assertOk()->getContent();

    expect($content)->toContain('>precision_manufacturing<')->not->toContain('tidak-ada.jpg');
});

test('portfolio page lists every active personnel with registry id role cluster and at most three skills', function () {
    $person = Personnel::first();
    $cluster = SpecialtyCluster::find($person->specialty_cluster_id);
    $skillIds = collect(['Skill Uji A', 'Skill Uji B', 'Skill Uji C', 'Skill Uji D'])
        ->map(fn (string $name) => Skill::create(['skill_name' => $name])->id);
    $person->skills()->sync($skillIds);

    $content = $this->get(route('portfolio'))->assertOk()->getContent();

    expect(substr_count($content, 'class="personnel-card'))->toBe(Personnel::where('status', true)->count())
        ->and($content)
        ->toContain('REG: '.e($person->registration_id))
        ->toContain(e($person->job_role))
        ->toContain(e($cluster->name))
        ->toContain('Matriks Direktori '.Personnel::where('status', true)->count().' Personil Terdaftar')
        ->toContain('>Skill Uji A<')
        ->toContain('>Skill Uji C<')
        ->not->toContain('>Skill Uji D<')
        ->and($content)->not->toContain('REG: MP-01');
});

test('portfolio personnel directory hides inactive personnel and marks status labels', function () {
    $inactive = Personnel::first();
    $inactive->update(['status' => false]);

    $this->get(route('portfolio'))
        ->assertOk()
        ->assertDontSee('REG: '.$inactive->registration_id, false)
        ->assertSee('>Aktif<', false);
});

test('portfolio personnel cards expose sorting and search data', function () {
    $person = Personnel::first();
    $person->update(['qualification_rank' => 7, 'technical_focus' => 'Fokus Uji Khusus']);

    $this->get(route('portfolio'))
        ->assertOk()
        ->assertSee('data-rank="7"', false)
        ->assertSee('data-cluster="'.e($person->specialtyCluster->name).'"', false)
        ->assertSee('Fokus Uji Khusus')
        ->assertSee('id="personnelSort"', false);
});

test('portfolio page escapes cms text', function () {
    PortfolioItem::first()->update(['title' => '<script>alert(1)</script>']);

    $this->get(route('portfolio'))->assertDontSee('<script>alert(1)</script>', false);
});

test('portfolio page renders empty states without items or personnel', function () {
    PortfolioItem::query()->delete();
    Personnel::query()->delete();

    $content = $this->get(route('portfolio'))->assertOk()->getContent();

    expect($content)->not->toContain('class="deliverable-card')->not->toContain('class="personnel-card')
        ->and($content)->toContain('Semua Hasil Kerja (0)')->toContain('id="deliverablesEmpty"')->toContain('id="personnelEmpty"');
});
