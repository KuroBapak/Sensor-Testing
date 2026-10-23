<?php

namespace App\Http\Controllers;

use App\Services\SettingsService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Streams the uploaded logo. The URL carries the file name, so the browser may cache it for good. */
class SiteLogoController extends Controller
{
    public function __invoke(SettingsService $settings): StreamedResponse
    {
        $path = (string) $settings->get('logo_path');
        $disk = Storage::disk('local');

        abort_if($path === '' || ! $disk->exists($path), 404);

        return $disk->response($path, null, [
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
            // An SVG logo must never run scripts, even if someone opens the URL directly.
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
        ]);
    }
}
