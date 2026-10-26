<?php

use App\Filament\Resources\WorkResource\Pages\EditWork;
use App\Models\Service;
use App\Models\User;
use App\Models\Work;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

function makeWork(array $attributes = []): Work
{
    static $counter = 0;
    $counter++;

    return Work::create([
        'service_id' => Service::first()->id,
        'work_title' => "Work Uji {$counter}",
        'work_slug' => "work-uji-{$counter}",
        'sort_order' => 500 + $counter,
        ...$attributes,
    ]);
}

test('works page renders a filter tab per service and a card per work', function () {
    $response = $this->get(route('works'))->assertOk();

    foreach (Service::all() as $service) {
        $response->assertSee('data-filter="'.$service->slug.'"', false)
            ->assertSee(e($service->service_title), false);
    }

    foreach (Work::all() as $work) {
        $response->assertSee('data-slug="'.$work->work_slug.'"', false)
            ->assertSee(e($work->work_title), false);
    }

    expect(substr_count($response->getContent(), 'class="project-card'))->toBe(Work::count());
});

test('works page shows the total work count on the all tab', function () {
    makeWork();

    $this->get(route('works'))
        ->assertOk()
        ->assertSee(Work::count().' Proyek Aktif Terdaftar');
});

test('work card exposes the attributes the browser uses for filter sort and modal', function () {
    $work = makeWork([
        'work_title' => 'Poros Uji Presisi',
        'work_slug' => 'poros-uji-presisi',
        'serial_number' => 'SER-UJI-01',
        'client_sector' => 'SEKTOR UJI',
        'material' => 'Titanium Uji',
        'tolerance_specs' => '0.003mm Max',
        'tolerance_value' => 0.003,
        'batch_scale' => '750 Unit',
        'scale_value' => 750,
        'production_date' => '2025-03-15',
        'quality_standards' => ['ISO Uji 1', 'ISO Uji 2'],
        'highlight_tags' => ['Tag Satu', 'Tag Dua', 'Tag Tiga', 'Tag Empat'],
    ]);

    $this->get(route('works'))
        ->assertOk()
        ->assertSee('data-slug="poros-uji-presisi"', false)
        ->assertSee('data-category="'.$work->service->slug.'"', false)
        ->assertSee('data-category-label="'.e($work->service->service_title).'"', false)
        ->assertSee('data-serial="SER-UJI-01"', false)
        ->assertSee('data-tolerance="0.003mm Max"', false)
        ->assertSee('data-tolerance-value="0.003"', false)
        ->assertSee('data-scale="750"', false)
        ->assertSee('data-date="2025-03-15"', false)
        ->assertSee('data-date-label="Maret 2025"', false)
        ->assertSee('data-standards="'.e(json_encode(['ISO Uji 1', 'ISO Uji 2'])).'"', false)
        ->assertSee('Tag Tiga')
        ->assertDontSee('Tag Empat');
});

test('work card uses the cms image and gallery, and a placeholder when none exist', function () {
    makeWork([
        'work_title' => 'Work Bergambar',
        'work_image' => 'works/utama.jpg',
        'gallery_images' => ['works/gallery/a.jpg', 'works/gallery/b.jpg', 'works/gallery/c.jpg', 'works/gallery/d.jpg'],
    ]);
    makeWork(['work_title' => 'Work Tanpa Gambar', 'work_image' => '/images/tidak-ada.jpg', 'gallery_images' => null]);

    $content = $this->get(route('works'))->assertOk()->getContent();

    expect($content)
        ->toContain('src="'.asset('storage/works/utama.jpg').'"')
        ->toContain(e(json_encode([
            asset('storage/works/utama.jpg'),
            asset('storage/works/gallery/a.jpg'),
            asset('storage/works/gallery/b.jpg'),
        ])))
        ->not->toContain('works/gallery/c.jpg')
        ->not->toContain('tidak-ada.jpg')
        ->toContain('>precision_manufacturing<');
});

test('work card escapes cms text', function () {
    makeWork(['work_title' => '<script>alert(1)</script>', 'short_description' => '<b>tebal</b>']);

    $this->get(route('works'))
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('<b>tebal</b>', false);
});

test('works page renders without cards when there are no works', function () {
    Work::query()->delete();

    $response = $this->get(route('works'))->assertOk()->assertSee('0 Proyek Aktif Terdaftar');

    expect($response->getContent())->not->toContain('class="project-card');
});

test('work description is plain text built from the rich text field', function () {
    $work = makeWork(['full_description' => '<p>Paragraf satu &amp; dua.</p><p>Paragraf <strong>tiga</strong>.</p>']);
    $fallback = makeWork(['full_description' => null, 'short_description' => 'Ringkasan saja']);

    expect($work->description_plain)->toBe("Paragraf satu & dua.\nParagraf tiga.")
        ->and($fallback->description_plain)->toBe('Ringkasan saja');
});

test('production date label is formatted in indonesian', function () {
    expect(makeWork(['production_date' => '2024-10-01'])->production_date_label)->toBe('Oktober 2024')
        ->and(makeWork(['production_date' => null])->production_date_label)->toBeNull();
});

test('admin cannot feature a seventh work', function () {
    $admin = User::factory()->create();
    $candidate = makeWork(['is_featured' => false]);

    expect(Work::where('is_featured', true)->count())->toBe(6);

    Livewire\Livewire::actingAs($admin)
        ->test(EditWork::class, ['record' => $candidate->getRouteKey()])
        ->fillForm(['is_featured' => true])
        ->call('save')
        ->assertHasFormErrors(['is_featured']);

    expect($candidate->fresh()->is_featured)->toBeFalse();
});

test('admin can feature a work while fewer than six are featured', function () {
    $admin = User::factory()->create();
    Work::where('is_featured', true)->first()->update(['is_featured' => false]);
    $candidate = makeWork(['is_featured' => false]);

    Livewire\Livewire::actingAs($admin)
        ->test(EditWork::class, ['record' => $candidate->getRouteKey()])
        ->fillForm(['is_featured' => true])
        ->call('save')
        ->assertHasNoFormErrors(['is_featured']);
});

test('admin can keep an already featured work featured when six are featured', function () {
    $admin = User::factory()->create();
    $featured = Work::where('is_featured', true)->first();

    Livewire\Livewire::actingAs($admin)
        ->test(EditWork::class, ['record' => $featured->getRouteKey()])
        ->fillForm(['is_featured' => true])
        ->call('save')
        ->assertHasNoFormErrors(['is_featured']);
});
