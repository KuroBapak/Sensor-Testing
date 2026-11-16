<?php

namespace App\Http\Controllers;

use App\Models\AboutCompany;
use App\Models\CorporateAccreditation;
use App\Models\GlobalSetting;
use App\Models\PageSetting;
use App\Models\Personnel;
use App\Models\PortfolioItem;
use App\Models\ProductCertification;
use App\Models\Service;
use App\Models\Work;
use App\Support\BrandImages;
use App\Support\ImageVariants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

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
     * The models whose rows are shown on each public page, besides its own page settings: the latest change to any
     * of them is the page's last modified date.
     *
     * @var array<string, array<int, class-string<Model>>>
     */
    private const PAGE_CONTENT = [
        'home' => [GlobalSetting::class, Service::class, Work::class],
        'about' => [AboutCompany::class],
        'works' => [Work::class, Service::class],
        'portfolio' => [PortfolioItem::class, Personnel::class],
        'licenses' => [CorporateAccreditation::class, ProductCertification::class],
    ];

    /**
     * Answer /favicon.ico: redirect to the icon uploaded in the CMS, otherwise serve the square icon made from the logo
     * directly (a redirect here is a wasted request on every first visit), and 404 when there is neither.
     */
    public function favicon(): RedirectResponse|Response
    {
        $settings = GlobalSetting::query()->first();

        if (filled($settings?->favicon)) {
            return redirect($settings->favicon_url);
        }

        $path = ImageVariants::pathFromUrl(BrandImages::icon($settings, 48));

        if ($path !== null) {
            return response(Storage::disk('public')->get($path), 200, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        abort_if($settings?->favicon_url === null, 404);

        return redirect($settings->favicon_url);
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
            'lastmod' => $this->lastModified($routeName, $updatedAt->get($page))?->toAtomString(),
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

    /**
     * The latest of the page settings' update time and the update times of the records shown on the page.
     */
    private function lastModified(string $routeName, ?Carbon $pageUpdatedAt): ?Carbon
    {
        return collect(self::PAGE_CONTENT[$routeName] ?? [])
            ->map(fn (string $model): mixed => $model::query()->max('updated_at'))
            ->filter()
            ->map(fn (mixed $time): Carbon => Carbon::parse($time))
            ->push($pageUpdatedAt)
            ->filter()
            ->max();
    }
}
