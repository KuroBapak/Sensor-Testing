<?php

namespace App\Support;

class MediaUrl
{
    /**
     * Resolve a CMS image value into a public URL.
     *
     * Absolute URLs pass through, leading-slash values are treated as files inside `public/`
     * (and ignored when the file is missing), and anything else is a path on the `public` disk
     * as stored by Filament uploads. Returns null when there is nothing usable so views can fall back.
     */
    public static function resolve(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            $relative = ltrim($path, '/');

            return file_exists(public_path($relative)) ? asset($relative) : null;
        }

        return asset('storage/'.$path);
    }
}
