<?php

use App\Models\GlobalSetting;
use App\Models\PageSetting;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoContactDetailsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

/**
 * Fetch the Organization node of the home page JSON-LD.
 *
 * @return array<string, mixed>
 */
function organizationJsonLd(): array
{
    $content = test()->get(route('home'))->assertOk()->getContent();

    preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $content, $matches);

    return collect(json_decode($matches[1], true)['@graph'])->firstWhere('@type', 'Organization');
}

test('it is not part of the normal database seeder, so production data is only touched on purpose', function () {
    expect(GlobalSetting::current()->whatsapp_number)->toBeNull()
        ->and(organizationJsonLd())->not->toHaveKey('sameAs');
});

test('it fills the empty WhatsApp number with a number that cannot reach a real chat', function () {
    $this->seed(DemoContactDetailsSeeder::class);

    expect(GlobalSetting::current()->whatsappNumber())->toBe(DemoContactDetailsSeeder::WHATSAPP_NUMBER)
        ->and(DemoContactDetailsSeeder::WHATSAPP_NUMBER)->toStartWith('620');
});

test('it replaces bare social site roots with demo profile urls that then appear as sameAs', function () {
    $this->seed(DemoContactDetailsSeeder::class);

    expect(organizationJsonLd()['sameAs'])->toBe([
        'https://www.facebook.com/pt.trissan.demo',
        'https://www.instagram.com/pt.trissan.demo',
    ]);
});

test('real values entered in the admin panel are never overwritten', function () {
    GlobalSetting::current()->update([
        'whatsapp_number' => '6281234567890',
        'social_media' => [
            ['platform_name' => 'Facebook', 'platform_icon' => 'facebook', 'handle' => '@trissan.asli', 'profile_url' => 'https://www.facebook.com/trissan.asli', 'sort_order' => 1],
            ['platform_name' => 'Instagram', 'platform_icon' => 'instagram', 'handle' => null, 'profile_url' => 'https://www.instagram.com/', 'sort_order' => 2],
        ],
    ]);

    $this->seed(DemoContactDetailsSeeder::class);

    $settings = GlobalSetting::current();

    expect($settings->whatsapp_number)->toBe('6281234567890')
        ->and($settings->social_media[0]['profile_url'])->toBe('https://www.facebook.com/trissan.asli')
        ->and($settings->social_media[0]['handle'])->toBe('@trissan.asli')
        ->and($settings->social_media[1]['profile_url'])->toBe('https://www.instagram.com/pt.trissan.demo');
});

test('it replaces only the known English template headline on the landing page', function () {
    PageSetting::forPage('landing')->update(['hero_title' => DemoContactDetailsSeeder::PLACEHOLDER_HERO_TITLE]);
    $this->seed(DemoContactDetailsSeeder::class);

    expect(PageSetting::forPage('landing')->hero_title)->toBe(DemoContactDetailsSeeder::HERO_TITLE);

    PageSetting::forPage('landing')->update(['hero_title' => 'Judul pilihan klien']);
    $this->seed(DemoContactDetailsSeeder::class);

    expect(PageSetting::forPage('landing')->hero_title)->toBe('Judul pilihan klien');
});

test('running it twice changes nothing the second time', function () {
    $this->seed(DemoContactDetailsSeeder::class);
    $first = GlobalSetting::current()->only(['whatsapp_number', 'social_media']);

    $this->seed(DemoContactDetailsSeeder::class);

    expect(GlobalSetting::current()->only(['whatsapp_number', 'social_media']))->toBe($first);
});
