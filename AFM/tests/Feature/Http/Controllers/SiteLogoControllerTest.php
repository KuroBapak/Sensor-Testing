<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

test('answers 404 while no logo has been uploaded', function () {
    $this->get(route('site-logo'))->assertNotFound();
});

test('streams the uploaded logo to anyone, with cache and security headers', function () {
    $path = UploadedFile::fake()->image('logo.png', 64, 64)->store('logos', 'local');
    setSettings(['logo_path' => $path]);

    $this->get(route('site-logo'))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Content-Security-Policy', "default-src 'none'; style-src 'unsafe-inline'; sandbox")
        ->assertHeader('Cache-Control', 'immutable, max-age=31536000, public');
});

test('answers 404 when the setting points to a file that is gone', function () {
    setSettings(['logo_path' => 'logos/missing.png']);

    $this->get(route('site-logo'))->assertNotFound();
});
