<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Storage;

/**
 * Generates placeholder media on the public disk so seeded CMS records render with visible images
 * and downloadable files, exactly like files uploaded through Filament (paths relative to the disk).
 */
class SeedMedia
{
    /**
     * Create (once) a placeholder PNG image and return its path on the public disk.
     *
     * PNG is used instead of SVG because Filament's upload preview (FilePond) cannot read the size of SVG files
     * and stays stuck on "Loading" for records that point at them.
     */
    public static function image(string $name): string
    {
        $path = "seed/{$name}.png";

        if (! Storage::disk('public')->exists($path)) {
            [$width, $height] = self::dimensionsFor($name);
            Storage::disk('public')->put($path, self::png($name, $width, $height));
        }

        return $path;
    }

    /**
     * Create (once) a minimal one-page placeholder PDF and return its path on the public disk.
     */
    public static function pdf(string $name): string
    {
        $path = "seed/{$name}.pdf";

        if (! Storage::disk('public')->exists($path)) {
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
