<?php

namespace App\Support;

use App\Models\GlobalSetting;

/**
 * Builds the schema.org JSON-LD graph (Organization, WebSite and BreadcrumbList) from CMS data.
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
        'works' => 'Works',
        'portfolio' => 'Portofolio',
        'licenses' => 'Lisensi',
    ];

    /**
     * The JSON-LD payload safe to print inside a script tag, or null when there is no company name to describe.
     */
    public static function json(?GlobalSetting $global, ?string $routeName): ?string
    {
        $graph = self::graph($global, $routeName);

        if ($graph === []) {
            return null;
        }

        return json_encode(
            ['@context' => 'https://schema.org', '@graph' => $graph],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
        ) ?: null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function graph(?GlobalSetting $global, ?string $routeName): array
    {
        if ($global === null || blank($global->company_name)) {
            return [];
        }

        $graph = [self::organization($global), self::website($global)];

        $breadcrumb = self::breadcrumb($routeName);

        if ($breadcrumb !== null) {
            $graph[] = $breadcrumb;
        }

        return $graph;
    }

    /**
     * @return array<string, mixed>
     */
    private static function organization(GlobalSetting $global): array
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
            'logo' => $global->navbar_logo_url,
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
            'sameAs' => self::profileUrls($global),
        ], fn (mixed $value): bool => filled($value));

        return $organization;
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
    private static function breadcrumb(?string $routeName): ?array
    {
        $label = self::BREADCRUMB_LABELS[$routeName] ?? null;

        if ($label === null) {
            return null;
        }

        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $label, 'item' => url()->current()],
            ],
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
