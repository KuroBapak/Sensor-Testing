<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Storage;

/**
 * Generates placeholder media on the public disk so seeded CMS records render with visible images
 * and downloadable files, exactly like files uploaded through Filament (paths relative to the disk).
 *
 * Real files in database/seeders/images are used instead of placeholders: either a name listed in PHOTOS, or a file
 * named after the seed name with "/" written as "-" (e.g. "hero-1.jpg", "portfolio-auto-1.webp", "certs-lrqa.png";
 * PDFs likewise, e.g. "Trissan_Company_Profile.pdf").
 */
class SeedMedia
{
    /**
     * Image extensions recognized by the naming convention, in order of preference.
     *
     * @var array<int, string>
     */
    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * Seed image name => file in database/seeders/images.
     *
     * @var array<string, string>
     */
    private const PHOTOS = [
        'work-1' => 'water-meter-bypass-chiller.jpg',
        'work-2' => 'fire-hydrant-system.jpg',
        'work-3' => 'chiller-duct-bypass.jpg',
        'work-4' => 'drying-conveyor.jpg',
        'work-5' => 'conveyor-maintenance.jpg',
        'work-6' => 'conveyor-feeding-packaging.jpg',
        'work-7' => 'conveyor-tending-auto-cutting.jpg',
        'portfolio/auto-1' => 'water-meter-bypass-chiller.jpg',
        'portfolio/auto-2' => 'chiller-duct-bypass.jpg',
        'portfolio/auto-3' => 'conveyor-tending-auto-cutting.jpg',
        'portfolio/food-1' => 'fire-hydrant-system.jpg',
        'portfolio/food-2' => 'conveyor-feeding-packaging.jpg',
        'portfolio/plastic-1' => 'drying-conveyor.jpg',
        'portfolio/plastic-2' => 'conveyor-maintenance.jpg',
    ];

    /**
     * Copy the real photo for this name to the public disk and return its path; with no real photo, create (once)
     * a generated placeholder PNG instead.
     *
     * PNG is used instead of SVG because Filament's upload preview (FilePond) cannot read the size of SVG files
     * and stays stuck on "Loading" for records that point at them.
     */
    public static function image(string $name): string
    {
        $photo = self::photoPath($name);

        if ($photo !== null) {
            $path = 'seed/'.$name.'.'.strtolower(pathinfo($photo, PATHINFO_EXTENSION));
            Storage::disk('public')->put($path, (string) file_get_contents($photo));

            return $path;
        }

        $path = "seed/{$name}.png";

        if (! Storage::disk('public')->exists($path)) {
            [$width, $height] = self::dimensionsFor($name);
            Storage::disk('public')->put($path, self::png($name, $width, $height));
        }

        return $path;
    }

    /**
     * Whether a real file exists for this seed name, for slots that should stay empty instead of using a placeholder.
     */
    public static function hasPhoto(string $name): bool
    {
        return self::photoPath($name) !== null;
    }

    /**
     * The real photo for a seed name: the mapped file, else a file following the naming convention.
     */
    private static function photoPath(string $name): ?string
    {
        $mapped = isset(self::PHOTOS[$name]) ? self::imagesPath(self::PHOTOS[$name]) : null;

        if ($mapped !== null && is_file($mapped)) {
            return $mapped;
        }

        foreach (self::EXTENSIONS as $extension) {
            $candidate = self::imagesPath(str_replace('/', '-', $name).'.'.$extension);

            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private static function imagesPath(string $file): string
    {
        return __DIR__.'/images/'.$file;
    }

    /**
     * Copy the real PDF for this name to the public disk and return its path; with no real PDF, create (once)
     * a minimal one-page placeholder PDF instead.
     */
    public static function pdf(string $name): string
    {
        $path = "seed/{$name}.pdf";
        $source = self::imagesPath(str_replace('/', '-', $name).'.pdf');

        if (is_file($source)) {
            Storage::disk('public')->put($path, (string) file_get_contents($source));
        } elseif (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, self::pdfDocument(self::label($name)));
        }

        return $path;
    }

    /**
     * @return array{0: int, 1: int}
     */
    private static function dimensionsFor(string $name): array
    {
        return match (true) {
            str_starts_with($name, 'hero'), str_starts_with($name, 'about-hero') => [1920, 1080],
            str_contains($name, 'partners') => [1200, 300],
            str_starts_with($name, 'logo') => [256, 256],
            str_starts_with($name, 'team') => [400, 400],
            str_starts_with($name, 'certs/') => [800, 1100],
            default => [1200, 800],
        };
    }

    private static function label(string $name): string
    {
        return strtoupper(str_replace(['-', '_', '/'], ' ', $name));
    }

    private static function png(string $name, int $width, int $height): string
    {
        $hue = 205 + (crc32($name) % 40);
        $image = imagecreatetruecolor($width, $height);

        [$topR, $topG, $topB] = self::hslToRgb($hue, 0.55, 0.22);
        [$bottomR, $bottomG, $bottomB] = self::hslToRgb($hue, 0.60, 0.10);

        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / max($height - 1, 1);
            $color = imagecolorallocate(
                $image,
                (int) ($topR + ($bottomR - $topR) * $ratio),
                (int) ($topG + ($bottomG - $topG) * $ratio),
                (int) ($topB + ($bottomB - $topB) * $ratio),
            );
            imageline($image, 0, $y, $width, $y, $color);
        }

        imagealphablending($image, true);
        $grid = imagecolorallocatealpha($image, 255, 255, 255, 118);
        for ($x = 0; $x < $width; $x += 40) {
            imageline($image, $x, 0, $x, $height, $grid);
        }
        for ($y = 0; $y < $height; $y += 40) {
            imageline($image, 0, $y, $width, $y, $grid);
        }

        $isBackdrop = str_starts_with($name, 'hero') || str_starts_with($name, 'about-hero');
        if (! $isBackdrop) {
            self::drawCenteredText($image, self::label($name), $width, $height, 255, 255, 255, (int) ($height * 0.5) - 20, 0.55);
            self::drawCenteredText($image, 'CONTOH GAMBAR - GANTI MELALUI CMS', $width, $height, 147, 197, 253, (int) ($height * 0.5) + 30, 0.4);
        }

        ob_start();
        imagepng($image);
        $contents = (string) ob_get_clean();
        imagedestroy($image);

        return $contents;
    }

    private static function drawCenteredText(\GdImage $image, string $text, int $width, int $height, int $r, int $g, int $b, int $top, float $widthRatio): void
    {
        $textWidth = max(strlen($text) * 9, 1);
        $textHeight = 15;

        $layer = imagecreatetruecolor($textWidth, $textHeight);
        imagealphablending($layer, false);
        imagefill($layer, 0, 0, imagecolorallocatealpha($layer, 0, 0, 0, 127));
        imagesavealpha($layer, true);
        imagestring($layer, 5, 0, 0, $text, imagecolorallocate($layer, $r, $g, $b));

        $targetWidth = min((int) ($width * $widthRatio), $textWidth * 6);
        $targetHeight = (int) ($targetWidth * $textHeight / $textWidth);

        imagecopyresampled($image, $layer, (int) (($width - $targetWidth) / 2), $top, 0, 0, $targetWidth, $targetHeight, $textWidth, $textHeight);
        imagedestroy($layer);
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private static function hslToRgb(float $hue, float $saturation, float $lightness): array
    {
        $chroma = (1 - abs(2 * $lightness - 1)) * $saturation;
        $segment = fmod($hue / 60, 6);
        $x = $chroma * (1 - abs(fmod($segment, 2) - 1));
        $match = $lightness - $chroma / 2;

        [$r, $g, $b] = match (true) {
            $segment < 1 => [$chroma, $x, 0],
            $segment < 2 => [$x, $chroma, 0],
            $segment < 3 => [0, $chroma, $x],
            $segment < 4 => [0, $x, $chroma],
            $segment < 5 => [$x, 0, $chroma],
            default => [$chroma, 0, $x],
        };

        return [(int) round(($r + $match) * 255), (int) round(($g + $match) * 255), (int) round(($b + $match) * 255)];
    }

    private static function pdfDocument(string $title): string
    {
        $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], "{$title} - dokumen contoh, ganti melalui CMS");
        $stream = "BT /F1 18 Tf 60 760 Td ({$text}) Tj ET";

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $index => $body) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n{$body}\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";
    }
}
