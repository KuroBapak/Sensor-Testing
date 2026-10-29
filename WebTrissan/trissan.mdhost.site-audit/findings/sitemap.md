# Sitemap findings - https://trissan.mdhost.site/

Audited 2026-10-29. Sources read (read-only): `app/Http/Controllers/SeoFilesController.php`, `resources/views/sitemap.blade.php`, `routes/web.php`. Live responses were fetched with curl and treated as untrusted data.

## Score: 10 / 100

The site has no usable sitemap. The route, the controller and the page list are sound. One template construct crashes on the production PHP config, so Google receives HTTP 500.

| Check | Result | Severity |
|---|---|---|
| /sitemap.xml returns 200 with valid XML | FAIL: HTTP 500, `text/html`, ParseError | Critical |
| Sitemap declared in robots.txt | FAIL: no `Sitemap:` line on the live robots.txt | High |
| XML well-formed | FAIL: none served | Critical |
| URLs under 50,000 and 50 MB | PASS: 5 URLs | - |
| Deprecated tags (priority/changefreq) | PASS: not used | - |
| `lastmod` format | PASS in source: `toAtomString()` is valid W3C Datetime | - |
| Duplicate or parameter URLs | PASS in source: only 5 named routes | - |
| Location-page gates | N/A: no location pages | - |

## Findings

### 1. CRITICAL: /sitemap.xml returns 500 (ParseError)
- Live response: HTTP 500, `text/html`. Error text: `ParseError: syntax error, unexpected identifier "version"` at `resources/views/sitemap.blade.php:1`. The stack trace goes through `SeoFilesController.php:65`, so the deployed controller matches the current source.
- Root cause: line 1 is `{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}`. When PHP runs with `short_open_tag=On`, Blade's tokenizer reads `<?xml` as a PHP open tag. That splits the `{!! ... !!}` echo, so the compiled view is invalid PHP.
- Reproduced locally with the existing test `sitemap lists every public page with its last modified date` in `tests/Feature/SeoMetaTest.php`:
  - `php -d short_open_tag=0 vendor/bin/pest --filter="sitemap lists"` passes (8 assertions).
  - `php -d short_open_tag=1 ...` fails with the same ParseError.
- The test passes on the dev machine because `short_open_tag` is off there. Production has it on. That explains why the bug shipped.
- Fix options (in order of preference):
  1. Remove line 1 from the view. The XML declaration is optional and UTF-8 is the default. The `Content-Type` header already says UTF-8.
  2. Build the document in `SeoFilesController::sitemap()` and return it with `response($xml, 200, [...])`, so no `<?` appears in a Blade file.
  3. Split the literal: `{!! '<'.'?xml version="1.0" encoding="UTF-8" ?>' !!}`. This is less robust.
- Also fix the cause: set `short_open_tag=Off` in the production php.ini or `.user.ini`.
- After the fix, run `php artisan view:clear`, then add a regression test that renders the view with `short_open_tag=1` or asserts the output starts with `<urlset`.

### 2. HIGH: robots.txt does not advertise the sitemap
- Live robots.txt contains only Cloudflare "Managed content" (lines up to `# END Cloudflare Managed Content`, line 129). It has no `Sitemap:` line and none of the origin's `Disallow: /admin`.
- `SeoFilesController::robots()` already emits `Sitemap: <route('sitemap')>` and `Disallow: /admin`, and a test covers it. The live output shows neither, so Cloudflare's managed robots.txt is replacing or overriding the origin output.
- Fix: in Cloudflare, go to Security > Bots > "Manage your robots.txt". Either turn the managed robots.txt off so Laravel's route is served, or keep it and add the origin's content. The content must include `Sitemap: https://trissan.mdhost.site/sitemap.xml` and `Disallow: /admin`.
- Until then, submit the sitemap in Google Search Console and Bing Webmaster Tools.

### 3. LOW: latent null-safety bug in `sitemap()`
- `$updatedAt[$page]?->toAtomString()` on an Eloquent `pluck()` Collection throws "Undefined array key" (a 500) if a `page_settings` row is missing. `?->` does not guard a missing key.
- Fix: `$updatedAt->get($page)?->toAtomString()`. The view already handles a null `lastmod`.

### 4. LOW: `lastmod` accuracy (verify after launch)
- `lastmod` comes from `PageSetting.updated_at`, which means CMS edits to the page's settings. It will not reflect changes to portfolio, works or license items shown on those pages.
- Keep `lastmod` only if it moves when the visible content changes. For /works, /portofolio and /license, consider `max(PageSetting.updated_at, latest item updated_at)`. Otherwise omit `lastmod`, since Google ignores it when it proves unreliable.
- Do not use the current time as `lastmod`.

### 5. INFO: `loc` host depends on APP_URL
- `route()` builds absolute URLs from `APP_URL`/request host. Confirm production emits `https://trissan.mdhost.site/...` (https, no trailing-slash variation, no internal host).
- Home must be `https://trissan.mdhost.site/` and match the canonical URL.

## What the sitemap should contain

Include (5 canonical, indexable, 200 URLs, all already in `SITEMAP_PAGES`): `/`, `/about`, `/portofolio`, `/license`, `/works`.

Exclude `/works?category=...`:
- Category filtering runs in the browser on a single page (see the `WorksController` docblock). The parameter URLs return the same HTML as `/works`, so they are not separate pages.
- Listing them would create duplicate or parameter URLs. Google advises listing only canonical URLs.
- Verify that each `/works?category=...` URL has a canonical pointing at `/works`. `layouts/seo-head.blade.php` uses `{{ $seoUrl }}`. Check that `$seoUrl` strips the query string.
- Revisit only if categories become real server-rendered pages with unique content and their own canonicals (for example `/works/category/{slug}`).
- Also leave out `/admin`, Livewire and asset endpoints, and `/favicon.ico` (it redirects).

## Reference sitemap.xml (current pages)

Replace the `lastmod` values with real CMS dates (the dates below are placeholders).

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url><loc>https://trissan.mdhost.site/</loc><lastmod>2026-10-29T00:00:00+07:00</lastmod></url>
  <url><loc>https://trissan.mdhost.site/about</loc><lastmod>2026-10-29T00:00:00+07:00</lastmod></url>
  <url><loc>https://trissan.mdhost.site/works</loc><lastmod>2026-10-29T00:00:00+07:00</lastmod></url>
  <url><loc>https://trissan.mdhost.site/portofolio</loc><lastmod>2026-10-29T00:00:00+07:00</lastmod></url>
  <url><loc>https://trissan.mdhost.site/license</loc><lastmod>2026-10-29T00:00:00+07:00</lastmod></url>
</urlset>
```

Note: the real file must start with the declaration shown above (the fenced block is documentation only). Serve it from the controller as shown in fix option 2, not from a Blade file.

## Expected result after fixes
Sitemap 200 with `application/xml`, 5 valid URLs, a `Sitemap:` line in robots.txt, and category URLs excluded. That would put the score at about 95/100. The remaining gap is the `lastmod` accuracy in finding 4.

## Verification gaps
- URL statuses for the five pages and their canonical or noindex tags were not individually checked (only `/works?category=x` returned 200).
- The production `short_open_tag` value is inferred from the reproduced ParseError. It was not read from the server's config.
