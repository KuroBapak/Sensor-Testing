<?php

namespace App\Support;

use App\Models\GlobalSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;
use Throwable;

/**
 * The images search engines, social networks and browsers expect, made from the company logo in the CMS:
 * a 1200 x 630 share picture and square browser icons. The logo itself is wider than tall, which social cards
 * crop and browsers squash, so it is centered on a canvas of the right proportions.
 *
 * Files are written once to `_brand/` on the public disk and named after the logo's file, size and modification time,
 * so a new logo in the CMS gets new files and the old ones stop being used.
 */
class BrandImages
{
    public const SOCIAL_WIDTH = 1200;

    public const SOCIAL_HEIGHT = 630;

    public const DIRECTORY = '_brand';

    private const SOCIAL_BACKGROUND = '#F8FAFC';

    /**
     * The share picture for og:image, or null when there is no usable logo.
     *
     * @return array{url: string, width: int, height: int}|null
     */
    public static function social(?GlobalSetting $settings): ?array
    {
        $url = self::render($settings, 'social', function (ImageManager $manager, string $logoPath): string {
            $logo = $manager->decodePath($logoPath)->scaleDown(900, 420);
            $canvas = $manager->createImage(self::SOCIAL_WIDTH, self::SOCIAL_HEIGHT)->fill(self::SOCIAL_BACKGROUND);

            return (string) $canvas->insert($logo, 0, 0, 'center')->encodeUsingFormat(Format::PNG);
        });

        return $url === null ? null : ['url' => $url, 'width' => self::SOCIAL_WIDTH, 'height' => self::SOCIAL_HEIGHT];
    }

    /**
     * A square PNG icon of the logo, or null when there is no usable logo.
     *
     * @param  string|null  $background  A fill color, or null to keep the corners transparent
     */
    public static function icon(?GlobalSetting $settings, int $size, ?string $background = null): ?string
    {
        return self::render($settings, 'icon-'.$size.($background === null ? '' : '-solid'), function (ImageManager $manager, string $logoPath) use ($size, $background): string {
            $logo = $manager->decodePath($logoPath)->scaleDown($size, $size);
            $canvas = $manager->createImage($size, $size);

            if ($background !== null) {
                $canvas->fill($background);
            }

            return (string) $canvas->insert($logo, 0, 0, 'center')->encodeUsingFormat(Format::PNG);
        });
    }

    /**
     * The public URL of a generated file, writing it first when it does not exist yet.
     *
     * @param  callable(ImageManager, string): string  $compose  Returns the PNG bytes for the logo at the given disk path
     */
    private static function render(?GlobalSetting $settings, string $kind, callable $compose): ?string
    {
        $source = ImageVariants::pathFromUrl($settings?->navbar_logo_url);
        $disk = Storage::disk('public');

        if ($source === null || ! ImageVariants::supports($source) || ! $disk->exists($source)) {
            return null;
        }

        $target = self::DIRECTORY.'/'.$kind.'-'.substr(md5($source.'|'.$disk->lastModified($source).'|'.$disk->size($source)), 0, 10).'.png';

        if (! $disk->exists($target)) {
            try {
                [$width, $height] = @getimagesize($disk->path($source)) ?: [0, 0];

                if ($width === 0 || $width * $height > ImageVariants::MAX_PIXELS) {
                    return null;
                }

                $disk->put($target, $compose(ImageManager::usingDriver(Driver::class), $disk->path($source)));
            } catch (Throwable $exception) {
                Log::warning("Brand image {$kind} failed for {$source}: ".$exception->getMessage());

                return null;
            }
        }

        return asset('storage/'.$target);
    }
}
