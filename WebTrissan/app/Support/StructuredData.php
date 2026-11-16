<?php

namespace App\Support;

use App\Models\GlobalSetting;
use App\Models\Service;
use Illuminate\Support\Collection;

/**
 * Builds the schema.org JSON-LD graph (Organization with its service catalogue, WebSite, the page itself,
 * BreadcrumbList and Service) from CMS data.
 */
class StructuredData
{
    /**
     * Breadcrumb label per route name; the home page has no breadcrumb.
     *
     * @var array<string, string>
     */
    private const BREADCRUMB_LABELS = [
        'about' => 'Tentang Kami',
        'works' => 'Proyek',
        'portfolio' => 'Portofolio',
        'licenses' => 'Lisensi',
    ];

    /**
     * The schema.org type of the page itself per route name; other pages are a plain WebPage.
     *
     * @var array<string, string>
     */
    private const PAGE_TYPES = [
        'about' => 'AboutPage',
        'works' => 'CollectionPage',
        'portfolio' => 'CollectionPage',
    ];

    /**
     * The JSON-LD payload safe to print inside a script tag, or null when there is no company name to describe.
     */
    public static function json(?GlobalSetting $global, ?string $routeName, ?Service $service = null, ?string $title = null, ?string $description = null, ?Collection $services = null): ?string
    {
        $graph = self::graph($global, $routeName, $service, $title, $description, $services);

        if ($graph === []) {
            return null;
        }

        return json_encode(
            ['@context' => 'https://schema.org', '@graph' => $graph],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
        ) ?: null;
    }

    /**
     * @param  Collection<int, Service>|null  $services  The services already loaded for the page, to avoid asking again
     * @return array<int, array<string, mixed>>
     */
    public static function graph(?GlobalSetting $global, ?string $routeName, ?Service $service = null, ?string $title = null, ?string $description = null, ?Collection $services = null): array
    {
        if ($global === null || blank($global->company_name)) {
            return [];
        }

        $graph = [self::organization($global, $services), self::website($global)];

        $breadcrumb = self::breadcrumb($routeName, $service);

        $graph[] = self::webPage($routeName, $title, $description, $breadcrumb !== null);

        if ($breadcrumb !== null) {
            $graph[] = $breadcrumb;
        }

        if ($routeName === 'services.show' && $service?->hasLandingContent()) {
            $graph[] = self::service($service);
        }

        return $graph;
    }

    /**
     * @return array<string, mixed>
     */
    private static function organization(GlobalSetting $global, ?Collection $services): array
    {
        $phones = collect($global->phones ?? [])
            ->map(fn (array $phone): ?string => self::internationalPhone((string) ($phone['tel_url'] ?? $phone['display_text'] ?? '')))
            ->filter()
            ->values();

        $organization = array_filter([
            '@type' => 'Organization',
            '@id' => self::organizationId(),
            'name' => $global->company_name,
            'slogan' => $global->company_tagline,
            'url' => url('/'),
            'description' => $global->footer_description,
            'logo' => self::logo($global->navbar_logo_url),
            'image' => BrandImages::social($global)['url'] ?? null,
            'email' => $global->primary_email,
            'telephone' => $phones->first(),
            'address' => filled($global->factory_address) ? self::postalAddress($global->factory_address) : null,
            'contactPoint' => $phones->map(fn (string $phone): array => [
                '@type' => 'ContactPoint',
                'telephone' => $phone,
                'contactType' => 'customer service',
                'areaServed' => 'ID',
                'availableLanguage' => 'id',
            ])->all(),
            'areaServed' => ['@type' => 'Country', 'name' => 'Indonesia'],
            'hasOfferCatalog' => self::offerCatalog($global, $services),
            'sameAs' => self::profileUrls($global),
        ], fn (mixed $value): bool => filled($value));

        return $organization;
    }

    /**
     * The logo with its size when it is a stored picture, so search engines know its proportions, else just its address.
     *
     * @return array<string, mixed>|string|null
     */
    private static function logo(?string $url): array|string|null
    {
        $size = ImageVariants::dimensions($url);

        return $size === null ? $url : ['@type' => 'ImageObject', 'url' => $url, ...$size];
    }

    /**
     * The services the company offers, each with a link to its page when that page is ready to be indexed.
     *
     * @param  Collection<int, Service>|null  $services
     * @return array<string, mixed>|null
     */
    private static function offerCatalog(GlobalSetting $global, ?Collection $services): ?array
    {
        $services = ($services?->whereInstanceOf(Service::class) ?? Service::query()->orderBy('sort_order')->get())->values();

        if ($services->isEmpty()) {
            return null;
        }

        return [
            '@type' => 'OfferCatalog',
            'name' => 'Layanan '.$global->company_name,
            'itemListElement' => $services->map(fn (Service $service): array => [
                '@type' => 'Offer',
                'itemOffered' => array_filter([
                    '@type' => 'Service',
                    'name' => $service->service_title,
                    'description' => $service->service_description,
                    'url' => $service->hasLandingContent() ? route('services.show', $service) : null,
                ], fn (mixed $value): bool => filled($value)),
            ])->all(),
        ];
    }

    /**
     * The page being viewed, part of the website and about the organization.
     *
     * @return array<string, mixed>
     */
    private static function webPage(?string $routeName, ?string $title, ?string $description, bool $hasBreadcrumb): array
    {
        return array_filter([
            '@type' => self::PAGE_TYPES[$routeName] ?? 'WebPage',
            '@id' => url()->current().'#webpage',
            'url' => url()->current(),
            'name' => $title,
            'description' => $description,
            'inLanguage' => 'id',
            'isPartOf' => ['@id' => rtrim(url('/'), '/').'/#website'],
            'about' => ['@id' => self::organizationId()],
            'breadcrumb' => $hasBreadcrumb ? ['@id' => url()->current().'#breadcrumb'] : null,
        ], fn (mixed $value): bool => filled($value));
    }

    /**
     * @return array<string, mixed>
     */
    private static function website(GlobalSetting $global): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => rtrim(url('/'), '/').'/#website',
            'url' => url('/'),
            'name' => $global->company_name,
            'inLanguage' => 'id',
            'publisher' => ['@id' => self::organizationId()],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function breadcrumb(?string $routeName, ?Service $service): ?array
    {
        $label = $routeName === 'services.show'
            ? ($service?->short_title ?? $service?->service_title)
            : (self::BREADCRUMB_LABELS[$routeName] ?? null);

        if ($label === null) {
            return null;
        }

        return [
            '@type' => 'BreadcrumbList',
            '@id' => url()->current().'#breadcrumb',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $label, 'item' => url()->current()],
            ],
        ];
    }

    /**
     * The service described on its landing page, offered by the organization.
     *
     * @return array<string, mixed>
     */
    private static function service(Service $service): array
    {
        return [
            '@type' => 'Service',
            '@id' => url()->current().'#service',
            'name' => $service->service_title,
            'description' => $service->metaDescription(),
            'url' => url()->current(),
            'serviceType' => $service->short_title,
            'areaServed' => 'ID',
            'inLanguage' => 'id',
            'provider' => ['@id' => self::organizationId()],
        ];
    }

    /**
     * Social profile links over http(s), leaving out bare site roots such as https://www.facebook.com/ that identify no profile.
     *
     * @return array<int, string>
     */
    private static function profileUrls(GlobalSetting $global): array
    {
        return collect($global->social_media ?? [])
            ->pluck('profile_url')
            ->filter(fn (mixed $url): bool => filled($url)
                && in_array(parse_url((string) $url, PHP_URL_SCHEME), ['http', 'https'], true)
                && trim((string) parse_url((string) $url, PHP_URL_PATH), '/') !== '')
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private static function postalAddress(string $text): array
    {
        return ['@type' => 'PostalAddress', 'streetAddress' => $text, 'addressCountry' => 'ID'];
    }

    private static function organizationId(): string
    {
        return rtrim(url('/'), '/').'/#organization';
    }

    /**
     * Convert a tel: link or display number into +62... form, or null when it holds no digits.
     */
    private static function internationalPhone(string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', $value);

        if (blank($digits)) {
            return null;
        }

        return '+'.(str_starts_with($digits, '0') ? '62'.substr($digits, 1) : $digits);
    }
}
