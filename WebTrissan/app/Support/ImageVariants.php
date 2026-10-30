<?php

namespace App\Support;

use App\Jobs\GenerateImageVariants;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Smaller WebP copies of the images on the public disk, so each device downloads only the size it needs.
 *
 * A copy of `hero/photo.jpg` at 1280 px wide is stored as `_variants/1280/hero/photo.webp`. The original is never
 * changed and copies are never made larger than the original.
 */
class ImageVariants
{
    /**
     * Widths in pixels, from phone to large monitor.
     *
     * @var array<int, int>
     */
    public const WIDTHS = [640, 1280, 1920, 2560];

    public const QUALITY = 80;

    /**
     * Folder on the public disk that holds all copies.
     */
    public const DIRECTORY = '_variants';

    /**
     * Source images larger than this many pixels are skipped, to keep PHP's memory use bounded.
     */
    public const MAX_PIXELS = 25_000_000;

    /**
     * Whether a file on the public disk is a kind of image that gets copies.
     */
    public static function supports(string $path): bool
    {
        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true)
            && ! str_starts_with($path, self::DIRECTORY.'/');
    }

    /**
     * Where the copy of an image at a given width is stored on the public disk.
     */
    public static function variantPath(string $path, int $width): string
    {
        $directory = pathinfo($path, PATHINFO_DIRNAME);
        $name = pathinfo($path, PATHINFO_FILENAME);

        return self::DIRECTORY.'/'.$width.'/'.($directory === '.' ? '' : $directory.'/').$name.'.webp';
    }

    /**
     * Create the missing copies of one image and return how many were written.
     *
     * With $force every copy is rewritten, otherwise copies newer than the original are kept.
     * Copies are never larger than the original.
     */
    public static function generate(string $path, bool $force = false): int
    {
        $disk = Storage::disk('public');

        if (! self::supports($path) || ! $disk->exists($path)) {
            return 0;
        }

        $created = 0;

        try {
            // The header is read without decoding the picture, so a huge file never exhausts memory here.
            [$width, $height] = @getimagesize($disk->path($path)) ?: [0, 0];

            if ($width === 0 || $width * $height > self::MAX_PIXELS) {
                Log::warning("Image variants skipped, {$path} is larger than ".self::MAX_PIXELS.' pixels.');

                return 0;
            }

            // An image narrower than the smallest width still gets one WebP copy, at its own size and
            // stored under the smallest width, so even small logos and banners are served as WebP.
            $targets = array_filter(self::WIDTHS, fn (int $target): bool => $target <= $width) ?: [self::WIDTHS[0]];

            foreach ($targets as $target) {
                $variant = self::variantPath($path, $target);

                if (! $force && $disk->exists($variant) && $disk->lastModified($variant) >= $disk->lastModified($path)) {
                    continue;
                }

                Image::fromStorage($path, 'public')
                    ->scale(width: min($target, $width))
                    ->toWebp()
                    ->quality(self::QUALITY)
                    ->storePubliclyAs(dirname($variant), basename($variant), 'public');

                $created++;
            }
        } catch (Throwable $exception) {
            Log::warning("Image variants failed for {$path}: ".$exception->getMessage());
        }

        return $created;
    }

    /**
     * The `srcset` value (WebP copies with their widths) for an image URL, or null when it has none yet.
     *
     * Only images stored on the public disk are handled. When copies are missing, they are created after the
     * response has been sent, so the first visitor gets the original and later ones get the smaller copies.
     */
    public static function srcset(?string $url): ?string
    {
        $path = self::pathFromUrl($url);

        if ($path === null || ! self::supports($path)) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return null;
        }

        $candidates = [];

        foreach (self::WIDTHS as $width) {
            $variant = self::variantPath($path, $width);

            if ($disk->exists($variant)) {
                $candidates[] = asset('storage/'.self::encode($variant)).' '.$width.'w';
            }
        }

        if (count($candidates) < count(self::WIDTHS) && Cache::add('image-variants:'.$path, true, now()->addMinutes(10))) {
            GenerateImageVariants::dispatchAfterResponse($path);
        }

        return $candidates === [] ? null : implode(', ', $candidates);
    }

    /**
     * The path on the public disk for a public storage URL, or null for any other URL.
     */
    private static function pathFromUrl(?string $url): ?string
    {
        $prefix = asset('storage').'/';

        if ($url === null || ! str_starts_with($url, $prefix)) {
            return null;
        }

        return rawurldecode(substr($url, strlen($prefix)));
    }

    /**
     * URL-encode each segment of a path, keeping the slashes.
     */
    private static function encode(string $path): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }
}
