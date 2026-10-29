<?php

use App\Models\AboutCompany;
use App\Models\GlobalSetting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('about page renders the hero from the about company record', function () {
    AboutCompany::current()->update([
        'hero_badge' => 'Badge Uji About',
        'hero_title' => 'Judul Hero Uji',
        'hero_description' => '<p>Paragraf pertama uji.</p><p>Paragraf kedua uji.</p>',
        'hero_image' => 'about/hero.jpg',
        'partners_image' => 'about/partners.png',
        'company_profile_pdf' => 'about/profil.pdf',
        'image_overlay' => [
            'plant_badge' => 'PLANT UJI 99',
            'caption_title' => 'Caption Uji',
            'caption_subtitle' => 'Subcaption Uji',
            'tolerance_chip' => 'Chip Uji ±1mm',
        ],
    ]);

    $this->get(route('about'))
        ->assertOk()
        ->assertSee('Badge Uji About')
        ->assertSee('Judul Hero Uji')
        ->assertSee('Paragraf pertama uji.')
        ->assertSee('Paragraf kedua uji.')
        ->assertDontSee('<p>Paragraf pertama uji.</p>', false)
        ->assertSee('src="'.asset('storage/about/hero.jpg').'"', false)
        ->assertSee('src="'.asset('storage/about/partners.png').'"', false)
        ->assertSee('download href="'.asset('storage/about/profil.pdf').'"', false)
        ->assertSee('PLANT UJI 99')
        ->assertSee('Caption Uji')
        ->assertSee('Subcaption Uji')
        ->assertSee('Chip Uji ±1mm');
});

test('about page hides the profile download and partners banner when files are missing', function () {
    AboutCompany::current()->update([
        'company_profile_pdf' => '/files/tidak-ada.pdf',
        'partners_image' => '/images/tidak-ada.png',
        'hero_image' => '/images/tidak-ada.jpg',
    ]);

    $this->get(route('about'))
        ->assertOk()
        ->assertDontSee('Unduh Company Profile')
        ->assertDontSee('Our Partners')
        ->assertDontSee('tidak-ada', false)
        ->assertSee('>precision_manufacturing<', false);
});

test('about hero stats come from the global company stats using the about labels', function () {
    GlobalSetting::current()->update(['company_stats' => [
        ['value' => '77+', 'label_landing' => 'Label Landing Uji', 'label_about' => 'Label About Uji'],
    ]]);

    $this->get(route('about'))
        ->assertOk()
        ->assertSee('77+')
        ->assertSee('Label About Uji')
        ->assertDontSee('Label Landing Uji');
});

test('about page renders vision mission and values cards by type', function () {
    $content = $this->get(route('about'))->assertOk()->getContent();

    foreach (AboutCompany::current()->vision_mission_values as $card) {
        expect($content)->toContain(e($card['title']))->toContain(e($card['badge']))->toContain(e($card['footer_value']));
    }

    expect($content)
        ->toContain('Kepuasan Pelanggan:')
        ->toContain('Tolok ukur keberhasilan proyek')
        ->toContain('Memberi solusi dalam pemenuhan kebutuhan pelanggan');
});

test('about page renders facilities ordered by sort order and with a placeholder for missing images', function () {
    $about = AboutCompany::current();
    $facilities = $about->facilities;
    $facilities[0]['sort_order'] = 9;
    $facilities[0]['facility_image'] = null;
    $facilities[0]['facility_title'] = 'Fasilitas Terakhir Uji';
    $facilities[1]['sort_order'] = 1;
    $facilities[1]['facility_title'] = 'Fasilitas Pertama Uji';
    $about->update(['facilities' => $facilities]);

    $content = $this->get(route('about'))->assertOk()->getContent();

    expect(strpos($content, 'Fasilitas Pertama Uji'))->toBeLessThan(strpos($content, 'Fasilitas Terakhir Uji'))
        ->and($content)->toContain('>factory<')
        ->toContain(e($facilities[0]['spec_tag_1']));
});

test('about page renders milestones in order and marks the current one', function () {
    $about = AboutCompany::current();
    $milestones = $about->milestones;
    $milestones[array_key_last($milestones)]['is_current'] = true;
    $milestones[array_key_last($milestones)]['year'] = '2024 - SEKARANG';
    $about->update(['milestones' => $milestones]);

    $content = $this->get(route('about'))->assertOk()->getContent();

    $positions = collect($milestones)->map(fn (array $milestone) => strpos($content, e($milestone['title'])));

    expect($positions->every(fn ($position) => $position !== false))->toBeTrue()
        ->and($positions->values()->all())->toBe($positions->sort()->values()->all())
        ->and($content)->toContain('2024 - SEKARANG')
        ->toContain('bg-emerald-500 border-4 border-white')
        ->not->toContain('Pendirian PT Apex Industrial');
});

test('about page renders every person of the organization chart as nested levels', function () {
    $about = AboutCompany::current();
    $chart = $about->org_chart;
    $chart['chart_revision_tag'] = 'REV-UJI-1';
    $about->update(['org_chart' => $chart]);

    $response = $this->get(route('about'))->assertOk()->assertSee('REV-UJI-1');

    foreach ($chart['nodes'] as $node) {
        $response->assertSee(e($node['name']), false)->assertSee(e($node['position']), false);
    }

    expect($response->getContent())->toContain('id="org-tree"')->toContain('class="org-side"');
});

test('about page renders without optional sections when the record is empty', function () {
    AboutCompany::current()->update([
        'vision_mission_values' => null,
        'facilities' => null,
        'milestones' => null,
        'org_chart' => null,
        'image_overlay' => null,
    ]);

    $this->get(route('about'))->assertOk()->assertDontSee('Visi Perusahaan');
});

test('about page escapes cms text', function () {
    AboutCompany::current()->update(['hero_title' => '<script>alert(1)</script>']);

    $this->get(route('about'))->assertDontSee('<script>alert(1)</script>', false);
});
