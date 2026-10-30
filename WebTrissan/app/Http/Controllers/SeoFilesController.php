<?php

namespace App\Http\Controllers;

use App\Models\GlobalSetting;
use App\Models\PageSetting;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class SeoFilesController extends Controller
{
    /**
     * Public pages in the sitemap: route name => page settings slug used for the last modified date.
     *
     * @var array<string, string>
     */
    private const SITEMAP_PAGES = [
        'home' => 'landing',
        'about' => 'about',
        'works' => 'works',
        'portfolio' => 'portfolio',
        'licenses' => 'licenses',
    ];

    /**
     * Send browsers and crawlers that ask for /favicon.ico to the CMS favicon (or the navbar logo), 404 when neither is set.
     */
    public function favicon(): RedirectResponse
    {
        $url = GlobalSetting::query()->first()?->favicon_url;

        abort_if($url === null, 404);

        return redirect($url);
    }

    /**
     * Serve robots.txt with an absolute sitemap URL, which a static file cannot know.
     */
    public function robots(): Response
    {
        $body = implode("\n", [
            'User-agent: *',
            'Disallow: /admin',
            '',
            'Sitemap: '.route('sitemap'),
            '',
        ]);

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /**
     * Serve the XML sitemap of the public pages and the service landing pages that have enough content to be indexed,
     * using each page's CMS update time as last modified date.
     *
     * The XML declaration is prepended here rather than written in the Blade view: with PHP's short_open_tag
     * enabled (the default when no php.ini sets it), a literal "<?xml" in a compiled view is a parse error.
     */
    public function sitemap(): Response
    {
        $updatedAt = PageSetting::query()->pluck('updated_at', 'page');

        $urls = collect(self::SITEMAP_PAGES)->map(fn (string $page, string $routeName): array => [
            'loc' => route($routeName),
            'lastmod' => $updatedAt->get($page)?->toAtomString(),
        ]);

        $serviceUrls = Service::query()->orderBy('sort_order')->get()
            ->filter(fn (Service $service): bool => $service->hasLandingContent())
            ->map(fn (Service $service): array => [
                'loc' => route('services.show', $service),
                'lastmod' => $service->updated_at?->toAtomString(),
            ]);

        $urls = $urls->concat($serviceUrls)->values();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
