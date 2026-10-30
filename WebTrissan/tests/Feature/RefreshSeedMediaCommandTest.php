<?php

use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('it replaces a stored seed image with the current source file and keeps its path', function () {
    Storage::disk('public')->put('seed/hero-1.jpg', 'OLD-BIG-FILE'.str_repeat('x', 5000));

    $this->artisan('media:refresh-seed')
        ->expectsOutputToContain('replaced seed/hero-1.jpg')
        ->assertSuccessful();

    expect(Storage::disk('public')->get('seed/hero-1.jpg'))
        ->toBe(file_get_contents(database_path('seeders/images/hero-1.jpg')));
});

test('a dry run lists the change but writes nothing', function () {
    Storage::disk('public')->put('seed/hero-1.jpg', 'OLD');

    $this->artisan('media:refresh-seed --dry-run')
        ->expectsOutputToContain('would replace seed/hero-1.jpg')
        ->assertSuccessful();

    expect(Storage::disk('public')->get('seed/hero-1.jpg'))->toBe('OLD');
});

test('files that are not seed images, such as CMS uploads, are never touched', function () {
    Storage::disk('public')->put('hero/DHU8nsPQmEt.png', 'UPLOAD');
    Storage::disk('public')->put('seed/unknown-photo.jpg', 'KEEP');

    $this->artisan('media:refresh-seed')->assertSuccessful();

    expect(Storage::disk('public')->get('hero/DHU8nsPQmEt.png'))->toBe('UPLOAD')
        ->and(Storage::disk('public')->get('seed/unknown-photo.jpg'))->toBe('KEEP');
});

test('a stored png whose source became a jpg is skipped with a warning, not overwritten', function () {
    Storage::disk('public')->put('seed/about-hero.png', 'OLD-PNG');

    $this->artisan('media:refresh-seed')
        ->expectsOutputToContain('skipped seed/about-hero.png')
        ->assertSuccessful();

    expect(Storage::disk('public')->get('seed/about-hero.png'))->toBe('OLD-PNG');
});

test('a file that already matches the source is left alone', function () {
    Storage::disk('public')->put('seed/hero-1.jpg', file_get_contents(database_path('seeders/images/hero-1.jpg')));

    $this->artisan('media:refresh-seed')
        ->expectsOutputToContain('Replaced 0 file(s)')
        ->assertSuccessful();
});
