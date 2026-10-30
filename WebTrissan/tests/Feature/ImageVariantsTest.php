<?php

use App\Jobs\GenerateImageVariants;
use App\Support\ImageVariants;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

/**
 * Store a real JPEG of the given size on the fake public disk.
 */
function storeJpeg(string $path, int $width, int $height): void
{
    $image = imagecreatetruecolor($width, $height);
    imagefill($image, 0, 0, imagecolorallocate($image, 20, 90, 160));
    ob_start();
    imagejpeg($image, null, 90);
    Storage::disk('public')->put($path, (string) ob_get_clean());
}

test('copies are made at every standard width that is not larger than the original, in WebP', function () {
    storeJpeg('hero/photo.jpg', 2000, 1000);

    expect(ImageVariants::generate('hero/photo.jpg'))->toBe(3);

    foreach ([640, 1280, 1920] as $width) {
        $path = ImageVariants::variantPath('hero/photo.jpg', $width);
        $contents = Storage::disk('public')->get($path);

        expect($path)->toBe("_variants/{$width}/hero/photo.webp")
            ->and(substr($contents, 0, 4).substr($contents, 8, 4))->toBe('RIFFWEBP')
            ->and(getimagesizefromstring($contents)[0])->toBe($width);
    }

    Storage::disk('public')->assertMissing(ImageVariants::variantPath('hero/photo.jpg', 2560));
});

test('a small image is never enlarged', function () {
    storeJpeg('seed/small.jpg', 800, 400);

    expect(ImageVariants::generate('seed/small.jpg'))->toBe(1);

    Storage::disk('public')->assertExists(ImageVariants::variantPath('seed/small.jpg', 640));
    Storage::disk('public')->assertMissing(ImageVariants::variantPath('seed/small.jpg', 1280));
});

test('an image narrower than the smallest width still gets one WebP copy at its own size', function () {
    storeJpeg('seed/logo.jpg', 500, 250);

    expect(ImageVariants::generate('seed/logo.jpg'))->toBe(1);

    $contents = Storage::disk('public')->get(ImageVariants::variantPath('seed/logo.jpg', 640));

    expect(getimagesizefromstring($contents)[0])->toBe(500)
        ->and(ImageVariants::srcset(asset('storage/seed/logo.jpg')))->toContain('_variants/640/seed/logo.webp');
});

test('existing copies are kept unless forced, and the original file is never changed', function () {
    storeJpeg('hero/photo.jpg', 1300, 650);
    $original = Storage::disk('public')->get('hero/photo.jpg');

    expect(ImageVariants::generate('hero/photo.jpg'))->toBe(2)
        ->and(ImageVariants::generate('hero/photo.jpg'))->toBe(0)
        ->and(ImageVariants::generate('hero/photo.jpg', force: true))->toBe(2)
        ->and(Storage::disk('public')->get('hero/photo.jpg'))->toBe($original);
});

test('files that are not supported images or are already copies are ignored', function () {
    Storage::disk('public')->put('docs/file.pdf', '%PDF');
    storeJpeg('_variants/640/hero/photo.jpg', 640, 320);

    expect(ImageVariants::supports('docs/file.pdf'))->toBeFalse()
        ->and(ImageVariants::generate('docs/file.pdf'))->toBe(0)
        ->and(ImageVariants::supports('_variants/640/hero/photo.jpg'))->toBeFalse()
        ->and(ImageVariants::generate('missing/none.jpg'))->toBe(0)
        ->and(ImageVariants::supports('a/b.PNG'))->toBeTrue();
});

test('a broken image is skipped without an error', function () {
    Storage::disk('public')->put('hero/broken.jpg', 'not an image');

    expect(ImageVariants::generate('hero/broken.jpg'))->toBe(0);
});

test('srcset lists the existing copies with their widths and encodes odd file names', function () {
    storeJpeg('hero/Foto Utama.jpg', 1400, 700);
    ImageVariants::generate('hero/Foto Utama.jpg');

    $srcset = ImageVariants::srcset(asset('storage/hero/Foto%20Utama.jpg'));

    expect($srcset)->toBe(
        asset('storage/_variants/640/hero/Foto%20Utama.webp').' 640w, '.asset('storage/_variants/1280/hero/Foto%20Utama.webp').' 1280w'
    );
});

test('srcset is null for images that are not on the public disk or have no copies yet', function () {
    storeJpeg('hero/new.jpg', 2000, 1000);

    expect(ImageVariants::srcset('https://example.com/photo.jpg'))->toBeNull()
        ->and(ImageVariants::srcset(null))->toBeNull()
        ->and(ImageVariants::srcset(asset('storage/hero/absent.jpg')))->toBeNull()
        ->and(ImageVariants::srcset(asset('storage/hero/new.jpg')))->toBeNull();
});

test('missing copies are created after the response, and only once per image for a while', function () {
    Cache::flush();
    storeJpeg('hero/new.jpg', 2000, 1000);

    ImageVariants::srcset(asset('storage/hero/new.jpg'));
    ImageVariants::srcset(asset('storage/hero/new.jpg'));

    Bus::assertDispatchedAfterResponse(GenerateImageVariants::class, 1);
});

test('the job creates the copies', function () {
    storeJpeg('hero/job.jpg', 1300, 650);

    (new GenerateImageVariants('hero/job.jpg'))->handle();

    Storage::disk('public')->assertExists(ImageVariants::variantPath('hero/job.jpg', 640));
});

test('the responsive image component serves WebP copies with the original as fallback', function () {
    storeJpeg('hero/photo.jpg', 2000, 1000);
    ImageVariants::generate('hero/photo.jpg');

    $html = (string) $this->blade('<x-responsive-image :src="$src" alt="Foto" sizes="50vw" class="w-full" loading="lazy" />', [
        'src' => asset('storage/hero/photo.jpg'),
    ]);

    expect($html)
        ->toContain('<picture style="display: contents">')
        ->toContain('<source type="image/webp" sizes="50vw" srcset="'.asset('storage/_variants/640/hero/photo.webp').' 640w')
        ->toContain('src="'.asset('storage/hero/photo.jpg').'"')
        ->toContain('alt="Foto"')
        ->toContain('class="w-full"')
        ->toContain('loading="lazy"');
});

test('the component falls back to a plain image when there are no copies', function () {
    $html = (string) $this->blade('<x-responsive-image src="https://example.com/a.jpg" alt="Luar" fetchpriority="high" />');

    expect($html)->not->toContain('<picture')
        ->and($html)->toContain('<img src="https://example.com/a.jpg" alt="Luar"')
        ->and($html)->toContain('fetchpriority="high"');
});

test('a deferred image carries its address only in data attributes until a script sets them', function () {
    storeJpeg('hero/photo.jpg', 2000, 1000);
    ImageVariants::generate('hero/photo.jpg');

    $html = (string) $this->blade('<x-responsive-image :src="$src" alt="Foto" :defer="true" />', [
        'src' => asset('storage/hero/photo.jpg'),
    ]);

    expect($html)->toContain('data-srcset="')->toContain('data-src="')
        ->not->toContain(' srcset="')
        ->not->toContain(' src="');
});

test('the pre-warm command writes the missing copies for every image on the disk', function () {
    storeJpeg('seed/a.jpg', 1300, 650);
    storeJpeg('works/b.jpg', 700, 350);
    Storage::disk('public')->put('seed/readme.txt', 'x');

    $this->artisan('media:variants')->expectsOutputToContain('Checked 2 image(s), wrote 3 copy(ies).')->assertSuccessful();

    Storage::disk('public')->assertExists(ImageVariants::variantPath('seed/a.jpg', 1280));
    Storage::disk('public')->assertExists(ImageVariants::variantPath('works/b.jpg', 640));

    $this->artisan('media:variants')->expectsOutputToContain('wrote 0 copy(ies)')->assertSuccessful();
});
