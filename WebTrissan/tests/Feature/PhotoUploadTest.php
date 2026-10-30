<?php

use App\Filament\Resources\PortfolioItemResource\Pages\CreatePortfolioItem;
use App\Filament\Support\PhotoUpload;
use App\Models\PortfolioCategory;
use App\Models\PortfolioItem;
use App\Models\User;
use App\Support\ImageVariants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

/**
 * A tiny, valid PNG whose header claims the given size, without the memory a real picture of that size needs.
 */
function pngClaimingSize(int $width, int $height): UploadedFile
{
    $header = pack('N', $width).pack('N', $height).chr(8).chr(2).chr(0).chr(0).chr(0);
    $png = "\x89PNG\r\n\x1a\n".pack('N', 13).'IHDR'.$header.pack('N', crc32('IHDR'.$header))
        .pack('N', 0).'IEND'.pack('N', crc32('IEND'));

    return UploadedFile::fake()->createWithContent('huge.png', $png);
}

test('a large photo is resized to 2560 px wide and stored as a compressed WebP', function () {
    $file = UploadedFile::fake()->image('camera.png', 3200, 2000);

    $path = PhotoUpload::store($file, 'hero');
    $contents = Storage::disk('public')->get($path);

    expect($path)->toMatch('#^hero/[0-9a-f-]{36}\.webp$#')
        ->and(substr($contents, 0, 4).substr($contents, 8, 4))->toBe('RIFFWEBP')
        ->and(getimagesizefromstring($contents)[0])->toBe(PhotoUpload::MAX_WIDTH)
        ->and(strlen($contents))->toBeLessThan($file->getSize());
});

test('a photo that is already small is not enlarged', function () {
    $path = PhotoUpload::store(UploadedFile::fake()->image('small.jpg', 900, 600), '/team/');

    expect($path)->toStartWith('team/')
        ->and(getimagesizefromstring(Storage::disk('public')->get($path))[0])->toBe(900);
});

test('the original upload is not kept next to the compressed one', function () {
    PhotoUpload::store(UploadedFile::fake()->image('camera.jpg', 3000, 2000), 'works');

    expect(Storage::disk('public')->allFiles('works'))->toHaveCount(1);
});

test('a picture too large to process safely is stored as uploaded instead of failing', function () {
    $path = PhotoUpload::store(pngClaimingSize(8000, 5000), 'hero');

    expect($path)->toStartWith('hero/')->toEndWith('.png');

    Storage::disk('public')->assertExists($path);
});

test('the field accepts photos up to the livewire upload limit and tells editors not to compress first', function () {
    $field = PhotoUpload::make('image', 'hero');

    expect($field->getMaxSize())->toBe(12288)
        ->and($field->getDirectory())->toBe('hero')
        ->and($field->getAcceptedFileTypes())->toBe(['image/jpeg', 'image/png', 'image/webp'])
        ->and((string) $field->getHelperText())->toContain('tidak perlu dikompres dulu');
});

test('variants are skipped for a picture that is too large, without decoding it', function () {
    $file = pngClaimingSize(8000, 5000);
    Storage::disk('public')->put('hero/huge.png', $file->getContent());

    expect(ImageVariants::generate('hero/huge.png'))->toBe(0);
});

test('uploading a large photo through the admin form saves a compressed, resized WebP', function () {
    // The form's own database seeding is not needed; only an admin, a category and the upload.
    $admin = User::factory()->create(['email' => 'admin_upload@example.com']);
    $category = PortfolioCategory::query()->create(['name' => 'Uji', 'slug' => 'uji', 'sort_order' => 1]);

    $photo = UploadedFile::fake()->image('kamera.jpg', 4000, 3000)->size(5000);

    Livewire\Livewire::actingAs($admin)
        ->test(CreatePortfolioItem::class)
        ->fillForm([
            'portfolio_category_id' => $category->getKey(),
            'title' => 'Proyek uji',
            'image' => $photo,
            'sort_order' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $stored = PortfolioItem::query()->where('title', 'Proyek uji')->firstOrFail()->image;

    expect($stored)->toStartWith('portfolio/')->toEndWith('.webp')
        ->and(getimagesizefromstring(Storage::disk('public')->get($stored))[0])->toBe(PhotoUpload::MAX_WIDTH);
});
