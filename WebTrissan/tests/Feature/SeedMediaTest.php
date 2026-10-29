<?php

use Database\Seeders\SeedMedia;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

/**
 * Run a callback with real files temporarily placed in database/seeders/images, then remove them.
 *
 * @param  array<string, string>  $files  file name => contents
 */
function withSeedFiles(array $files, Closure $callback): void
{
    $directory = database_path('seeders/images');

    try {
        foreach ($files as $name => $contents) {
            File::put("{$directory}/{$name}", $contents);
        }

        $callback();
    } finally {
        foreach (array_keys($files) as $name) {
            File::delete("{$directory}/{$name}");
        }
    }
}

test('a real file named after the seed name replaces the placeholder, keeping its own extension', function () {
    withSeedFiles(['zz-slot-test.webp' => 'REAL-PHOTO-BYTES'], function () {
        $path = SeedMedia::image('zz/slot-test');

        expect($path)->toBe('seed/zz/slot-test.webp')
            ->and(Storage::disk('public')->get($path))->toBe('REAL-PHOTO-BYTES');
    });
});

test('a real file is copied again on every seed so a replaced photo takes effect', function () {
    withSeedFiles(['zz-refresh-test.png' => 'FIRST'], function () {
        SeedMedia::image('zz-refresh-test');

        File::put(database_path('seeders/images/zz-refresh-test.png'), 'SECOND');
        SeedMedia::image('zz-refresh-test');

        expect(Storage::disk('public')->get('seed/zz-refresh-test.png'))->toBe('SECOND');
    });
});

test('names without a real file still get a readable generated placeholder', function () {
    $path = SeedMedia::image('zz-no-file-test');

    expect($path)->toBe('seed/zz-no-file-test.png')
        ->and(getimagesizefromstring(Storage::disk('public')->get($path)))->not->toBeFalse();
});

test('the mapped project photos are used for the work slots', function () {
    $path = SeedMedia::image('work-1');

    expect($path)->toBe('seed/work-1.jpg')
        ->and(Storage::disk('public')->get($path))->toBe(file_get_contents(database_path('seeders/images/water-meter-bypass-chiller.jpg')));
});

test('a real PDF replaces the placeholder PDF', function () {
    withSeedFiles(['zz-doc-test.pdf' => '%PDF-REAL'], function () {
        expect(Storage::disk('public')->get(SeedMedia::pdf('zz-doc-test')))->toBe('%PDF-REAL');
    });

    expect(Storage::disk('public')->get(SeedMedia::pdf('zz-doc-other')))->toStartWith('%PDF-1.4');
});
