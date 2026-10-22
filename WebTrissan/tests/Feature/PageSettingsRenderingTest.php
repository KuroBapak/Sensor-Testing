<?php

use App\Models\PageSetting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('home page renders dynamic hero and about teaser from page settings', function () {
    $homeSetting = PageSetting::forPage('landing');
    $homeSetting->update([
        'hero_title' => 'Custom Engineering Hero Title',
        'hero_subtitle' => 'Custom Engineering Subtitle Description',
        'hero_cta_text' => 'Custom CTA Button',
        'hero_cta_url' => '/custom-cta-target',
        'about_teaser' => [
            'image' => null,
            'image_badge' => 'EST. 1990 • 35+ TAHUN',
            'eyebrow' => 'TENTANG KAMI APEX',
            'title' => 'Judul About Teaser Dinamis',
            'paragraph_1' => 'Paragraf pertama tentang kapabilitas manufaktur dan rekayasa.',
            'paragraph_2' => 'Paragraf kedua tentang kepatuhan mutu dan standar internasional.',
            'cta_text' => 'Buka Profil Perusahaan',
            'cta_url' => '/about-us-custom',
        ],
    ]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Custom Engineering Hero Title');
    $response->assertSee('Custom Engineering Subtitle Description');
    $response->assertSee('Custom CTA Button');
    $response->assertSee('/custom-cta-target');
    $response->assertSee('EST. 1990 • 35+ TAHUN');
    $response->assertSee('TENTANG KAMI APEX');
    $response->assertSee('Judul About Teaser Dinamis');
    $response->assertSee('Paragraf pertama tentang kapabilitas manufaktur dan rekayasa.');
    $response->assertSee('Paragraf kedua tentang kepatuhan mutu dan standar internasional.');
    $response->assertSee('Buka Profil Perusahaan');
    $response->assertSee('/about-us-custom');
});

test('home page renders about teaser with single description field', function () {
    $homeSetting = PageSetting::forPage('landing');
    $homeSetting->update([
        'about_teaser' => [
            'image' => null,
            'image_badge' => 'EST. 2026',
            'eyebrow' => 'TENTANG KAMI SATU INPUT',
            'title' => 'Judul Single Description',
            'description' => "Apex Industrial Engineering berdiri dengan komitmen memberikan solusi teknik presisi dengan toleransi ekstrem bagi industri kritis.\n\nDidukung oleh lebih dari 150 insinyur berkualifikasi tinggi dan fasilitas modern seluas 12.000 m² di Kawasan Industri Jababeka.",
            'cta_text' => 'Pelajari Profil Lengkap',
            'cta_url' => '/about',
        ],
    ]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('TENTANG KAMI SATU INPUT');
    $response->assertSee('Judul Single Description');
    $response->assertSee('Apex Industrial Engineering berdiri dengan komitmen memberikan solusi teknik presisi dengan toleransi ekstrem bagi industri kritis.');
    $response->assertSee('Didukung oleh lebih dari 150 insinyur berkualifikasi tinggi dan fasilitas modern seluas 12.000 m² di Kawasan Industri Jababeka.');
});

test('home page renders clean fallbacks when page setting is empty', function () {
    PageSetting::where('page', 'landing')->update([
        'hero_title' => null,
        'hero_subtitle' => null,
        'hero_cta_text' => null,
        'hero_cta_url' => null,
        'about_teaser' => null,
        'trust_card' => null,
    ]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('TENTANG PT. TRISSAN');
    $response->assertSee('Dedikasi Rekayasa Presisi &amp; Standar Manufaktur Kelas Dunia', false);
    $response->assertSee('Pelajari Profil Lengkap');
});

test('works page renders dynamic hero and stats from page settings', function () {
    $worksSetting = PageSetting::forPage('works');
    $worksSetting->update([
        'hero_title' => 'KATALOG WORKS DINAMIS',
        'hero_subtitle' => 'Deskripsi works dinamis dari CMS',
        'stats' => [
            [
                'icon' => 'factory',
                'label' => 'Total Unit Produksi',
                'value' => '999+',
                'caption' => 'Komponen Lolos Uji',
            ],
        ],
    ]);

    $response = $this->get(route('works'));

    $response->assertOk();
    $response->assertSee('KATALOG WORKS DINAMIS');
    $response->assertSee('Deskripsi works dinamis dari CMS');
    $response->assertSee('Total Unit Produksi');
    $response->assertSee('999+');
    $response->assertSee('Komponen Lolos Uji');
});

test('portfolio page renders dynamic hero, stats and registry tag from page settings', function () {
    $portfolioSetting = PageSetting::forPage('portfolio');
    $portfolioSetting->update([
        'hero_title' => 'PORTOFOLIO DINAMIS CMS',
        'hero_subtitle' => 'Subjudul portofolio dinamis CMS',
        'registry_revision_tag' => 'APEX REGISTRY: REV-2026.Q4-TEST',
        'stats' => [
            [
                'icon' => 'engineering',
                'label' => 'Lead Engineers Test',
                'value' => '25',
                'caption' => 'Certified ASME Level III',
            ],
        ],
    ]);

    $response = $this->get(route('portfolio'));

    $response->assertOk();
    $response->assertSee('PORTOFOLIO DINAMIS CMS');
    $response->assertSee('Subjudul portofolio dinamis CMS');
    $response->assertSee('APEX REGISTRY: REV-2026.Q4-TEST');
    $response->assertSee('Lead Engineers Test');
    $response->assertSee('25');
    $response->assertSee('Certified ASME Level III');
});

test('licenses page renders dynamic hero, badge, stats, and corporate highlight cards from page settings', function () {
    $licenseSetting = PageSetting::forPage('licenses');
    $licenseSetting->update([
        'hero_title' => 'LISENSI DINAMIS CMS',
        'hero_subtitle' => 'Subjudul lisensi dinamis CMS',
        'hero_badge_card' => [
            'eyebrow' => 'Uji Mutu Khusus',
            'title' => 'Lab Terakreditasi KAN',
            'caption' => 'ISO/IEC 17025:2017',
        ],
        'stats' => [
            [
                'icon' => 'military_tech',
                'label' => 'Akreditasi Dirgantara',
                'value' => '8+',
                'caption' => 'AS9100 Rev D & NADCAP',
            ],
        ],
        'corporate_highlight_cards' => [
            [
                'icon' => 'verified_user',
                'title' => 'NADCAP AC7110',
                'subtitle' => 'Welding Accreditation',
                'caption' => 'Performance Review Institute',
            ],
        ],
    ]);

    $response = $this->get(route('licenses'));

    $response->assertOk();
    $response->assertSee('LISENSI DINAMIS CMS');
    $response->assertSee('Subjudul lisensi dinamis CMS');
    $response->assertSee('Uji Mutu Khusus');
    $response->assertSee('Lab Terakreditasi KAN');
    $response->assertSee('ISO/IEC 17025:2017');
    $response->assertSee('Akreditasi Dirgantara');
    $response->assertSee('8+');
    $response->assertSee('NADCAP AC7110');
    $response->assertSee('Welding Accreditation');
});

test('about page renders dynamic seo metadata from page settings', function () {
    $aboutSetting = PageSetting::forPage('about');
    $aboutSetting->update([
        'seo_title' => 'Profil Lengkap PT Apex Industrial Engineering 2026',
        'seo_description' => 'Profil korporasi, rekam jejak industri presisi, dan visi ke depan.',
    ]);

    $response = $this->get(route('about'));

    $response->assertOk();
    $response->assertSee('Profil Lengkap PT Apex Industrial Engineering 2026');
    $response->assertSee('Profil korporasi, rekam jejak industri presisi, dan visi ke depan.');
});

test('public storage symlink exists and points to storage app public', function () {
    $publicStorage = public_path('storage');
    expect(file_exists($publicStorage))->toBeTrue()
        ->and(is_link($publicStorage))->toBeTrue();
});
