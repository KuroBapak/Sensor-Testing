<?php

namespace App\Http\Controllers;

use App\Models\PageSetting;
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
     * Serve the XML sitemap of the public pages, using each page's CMS update time as last modified date.
     */
    public function sitemap(): Response
    {
        $updatedAt = PageSetting::query()->pluck('updated_at', 'page');

        $urls = collect(self::SITEMAP_PAGES)->map(fn (string $page, string $routeName): array => [
            'loc' => route($routeName),
            'lastmod' => $updatedAt[$page]?->toAtomString(),
        ]);

        return response()->view('sitemap', ['urls' => $urls], 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
