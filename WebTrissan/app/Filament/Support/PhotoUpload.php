<?php

namespace App\Filament\Support;

use App\Support\ImageVariants;
use Filament\Forms\Components\FileUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Str;

/**
 * An admin photo upload that resizes and compresses the picture on the server, so editors can upload
 * straight from a camera or phone without preparing the file first.
 *
 * The stored file is a WebP at most 2560 px wide (never enlarged); smaller copies for phones and laptops are
 * made from it by ImageVariants. Use it for photos only: logos and documents keep their own fields.
 */
class PhotoUpload
{
    public const MAX_WIDTH = 2560;

    public const QUALITY = 85;

    /**
     * Largest accepted file in kilobytes; Livewire's default limit for temporary uploads is 12 MB.
     */
    public const MAX_SIZE_KB = 12288;

    /**
     * A file upload field for photos stored in the given directory of the public disk.
     */
    public static function make(string $name, string $directory): FileUpload
    {
        return FileUpload::make($name)
            ->disk('public')
            ->directory($directory)
            ->image()
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->maxSize(self::MAX_SIZE_KB)
            ->helperText('JPG, PNG atau WebP, maksimal 12 MB. Otomatis diperkecil (maks. 2560 px lebar) dan dikompres, jadi tidak perlu dikompres dulu.')
            ->saveUploadedFileUsing(fn (UploadedFile $file): string => self::store($file, $directory));
    }

    /**
     * Resize and compress an uploaded photo, store it on the public disk and return its path there.
     *
     * A picture that is too large to process safely is stored as uploaded instead of failing the form.
     */
    public static function store(UploadedFile $file, string $directory): string
    {
        $directory = trim($directory, '/');
        // The header is read without decoding the picture, so a huge file never exhausts memory here.
        [$width, $height] = @getimagesize((string) $file->getRealPath()) ?: [0, 0];

        if ($width === 0 || $width * $height > ImageVariants::MAX_PIXELS) {
            return (string) $file->storePublicly($directory, 'public');
        }

        $name = Str::uuid()->toString().'.webp';

        Image::fromPath((string) $file->getRealPath())->orient()
            ->scale(width: self::MAX_WIDTH)
            ->toWebp()
            ->quality(self::QUALITY)
            ->storePubliclyAs($directory, $name, 'public');

        return $directory.'/'.$name;
    }
}
