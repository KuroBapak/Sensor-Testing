<?php

use App\Support\MediaUrl;

test('media url returns null for empty values', function (?string $value) {
    expect(MediaUrl::resolve($value))->toBeNull();
})->with([null, '', '   ']);

test('media url passes absolute urls through unchanged', function () {
    expect(MediaUrl::resolve('https://cdn.example.com/logo.png'))->toBe('https://cdn.example.com/logo.png');
});

test('media url maps filament upload paths to the public storage url', function () {
    expect(MediaUrl::resolve('page-settings/hero.jpg'))->toBe(asset('storage/page-settings/hero.jpg'));
});

test('media url ignores leading-slash files that are missing from the public directory', function () {
    expect(MediaUrl::resolve('/images/definitely-missing-file.png'))->toBeNull();
});

test('media url resolves leading-slash files that exist in the public directory', function () {
    expect(MediaUrl::resolve('/favicon.ico'))->toBe(asset('favicon.ico'));
});
