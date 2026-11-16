<?php

use App\Models\Service;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoServiceContentSeeder;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ServiceSeeder::class);
});

test('every service gets landing copy that passes the thin-content check, so all twelve pages go live', function () {
    $this->seed(DemoServiceContentSeeder::class);

    $services = Service::query()->get();

    expect($services)->toHaveCount(12)
        ->and($services->reject(fn (Service $service): bool => $service->hasLandingContent())->pluck('slug')->all())->toBe([])
        ->and($services->every(fn (Service $service): bool => str_contains($service->publicUrl(), '/layanan/')))->toBeTrue();
});

test('the demo copy covers every seeded service and no unknown slug', function () {
    expect(array_keys(DemoServiceContentSeeder::content()))->toEqualCanonicalizing(Service::query()->pluck('slug')->all());
});

test('the demo copy has the buyer keywords the audit found missing', function () {
    $text = collect(DemoServiceContentSeeder::content())->pluck('body')->flatten()->implode(' ');

    expect($text)->toContain('kontraktor mekanikal elektrikal')
        ->toContain('jasa otomasi industri')
        ->toContain('panel listrik');
});

test('a service page shows its seeded copy and is listed in the sitemap', function () {
    $this->seed(DemoServiceContentSeeder::class);

    $this->get('/layanan/material-handling')
        ->assertOk()
        ->assertSee('Material handling adalah urat nadi logistik')
        ->assertSee('Tahapan Pengerjaan');

    $this->get('/sitemap.xml')->assertOk()->assertSee('/layanan/material-handling', false);
});

test('text written in the admin panel is never overwritten', function () {
    Service::query()->where('slug', 'jigs-fixtures')->update([
        'landing_body' => 'Teks asli dari klien.',
        'faqs' => [['question' => 'Tanya?', 'answer' => 'Jawab.']],
    ]);

    $this->seed(DemoServiceContentSeeder::class);

    $service = Service::query()->where('slug', 'jigs-fixtures')->firstOrFail();

    expect($service->landing_body)->toBe('Teks asli dari klien.')
        ->and($service->faqs)->toEqual([['question' => 'Tanya?', 'answer' => 'Jawab.']])
        ->and($service->process_steps)->toHaveCount(4);
});

test('running it twice changes nothing the second time', function () {
    $this->seed(DemoServiceContentSeeder::class);
    $first = Service::query()->orderBy('id')->get(['landing_body', 'process_steps', 'faqs'])->toArray();

    $this->seed(DemoServiceContentSeeder::class);

    expect(Service::query()->orderBy('id')->get(['landing_body', 'process_steps', 'faqs'])->toArray())->toBe($first);
});

test('the normal seeder adds the demo copy everywhere except the test environment', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Service::query()->whereNull('landing_body')->count())->toBe(12);

    app()->detectEnvironment(fn (): string => 'local');
    $this->seed(DatabaseSeeder::class);

    expect(Service::query()->whereNull('landing_body')->count())->toBe(0);
});
