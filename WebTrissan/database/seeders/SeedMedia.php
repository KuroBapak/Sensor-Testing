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
     * Create (once) a placeholder SVG image and return its path on the public disk.
     */
    public static function image(string $name): string
    {
        $path = "seed/{$name}.svg";

        if (! Storage::disk('public')->exists($path)) {
            [$width, $height] = self::dimensionsFor($name);
            Storage::disk('public')->put($path, self::svg($name, $width, $height));
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

    private static function svg(string $name, int $width, int $height): string
    {
        $hue = 205 + (crc32($name) % 40);
        $label = htmlspecialchars(self::label($name), ENT_XML1);
        $fontSize = max(14, (int) (min($width, $height) / 14));
        $captionY = (int) ($height / 2 + $fontSize * 1.6);
        $caption = max(10, (int) ($fontSize / 2.6));
        $isBackdrop = str_starts_with($name, 'hero') || str_starts_with($name, 'about-hero');
        $text = $isBackdrop ? '' : <<<TEXT
  <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle" fill="#ffffff" fill-opacity="0.92" font-family="Arial, Helvetica, sans-serif" font-weight="700" font-size="{$fontSize}">{$label}</text>
  <text x="50%" y="{$captionY}" text-anchor="middle" fill="#93c5fd" font-family="Arial, Helvetica, sans-serif" font-size="{$caption}">CONTOH GAMBAR - GANTI MELALUI CMS</text>
TEXT;

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="hsl({$hue},55%,22%)"/>
      <stop offset="1" stop-color="hsl({$hue},60%,10%)"/>
    </linearGradient>
    <pattern id="p" width="40" height="40" patternUnits="userSpaceOnUse">
      <path d="M40 0H0V40" fill="none" stroke="#ffffff" stroke-opacity="0.07"/>
    </pattern>
  </defs>
  <rect width="100%" height="100%" fill="url(#g)"/>
  <rect width="100%" height="100%" fill="url(#p)"/>
{$text}
</svg>
SVG;
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
