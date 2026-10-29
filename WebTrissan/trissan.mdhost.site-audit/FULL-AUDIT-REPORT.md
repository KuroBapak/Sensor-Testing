# Full SEO Audit — trissan.mdhost.site

**Date:** 29 Oct 2026 · **Tool:** Claude SEO v2 suite (`seo-audit` + 11 specialist agents) · **Pages:** `/`, `/about`, `/portofolio`, `/license`, `/works` (+ 4 modal pop-ups)

**Business type:** B2B industrial mechanical / electrical / automation contractor, HQ Bekasi with branches in Malang and Timika (hybrid local service).

## SEO Health Score: 48/100

| Category | Weight | Score | Source |
|---|---|---|---|
| Technical SEO | 22% | 46 | findings/technical.md |
| Content Quality | 23% | 36 | findings/content.md |
| On-Page SEO | 20% | 65 | findings/onpage.md |
| Schema / Structured Data | 10% | 62 | findings/schema.md |
| Performance (CWV) | 10% | 34 | findings/performance.md |
| AI Search Readiness | 10% | 50 | findings/geo.md + agentic.md |
| Images | 5% | 40 | findings/images.md |

AI Search Readiness blends GEO (45) and agent-readiness (72). Scores that are not weighted into the total:

| Specialist check | Score | Source |
|---|---|---|
| Sitemap | 10 | findings/sitemap.md |
| Local SEO | 28 | findings/local.md |
| Search Experience (SXO gap) | 42 | findings/sxo.md |
| Visual & Mobile | 66 | findings/visual.md |
| Agent readiness (heuristic) | 72 | findings/agentic.md |
| GEO / AI citations | 45 | findings/geo.md |
| Backlinks | insufficient data (readiness 12) | findings/backlinks.md |

## Top critical issues

1. **`/sitemap.xml` returns HTTP 500.** Line 1 of `resources/views/sitemap.blade.php` (the `{!! '<?xml ... ?>' !!}` prolog) compiles to invalid PHP when the server has `short_open_tag=On`. This was reproduced locally with the existing Pest test (passes with `short_open_tag=0`, fails with `=1`).
2. **Laravel debug mode is on in production.** Every error shows the full debug page with the stack trace, file paths, PHP 8.4.27 and Laravel 13.37.0.
3. **Sample data is published as fact.** `/license` lists certificates labelled "data contoh" / "Ganti melalui CMS" (AS9100D Aerospace, Nadcap, ASME, LRQA ISO 9001). Two of them (SMK3, ISO 45001) show expiry dates that have already passed. `/portofolio`'s "5 Personil Terdaftar" lists aerospace/subsea profiles that don't fit the business. Verified by direct fetch.
4. **Mobile pages are very slow.** Throttled-mobile LCP is 4.7–22.7 s against a 2.5 s target, with 4–5 MB of unoptimized JPG/PNG per page and no hero priority. On `/works` and `/portofolio` the LCP image itself is lazy-loaded.
5. **No page per service.** Google ranks single-service city pages for these queries, and Trissan has one homepage hub with 12 one-line cards. `/works?category=` filters canonicalise to `/works`.

## Also urgent: domain and identity

- **The old site trissan.co.id looks compromised.** It is live with the same address and phone, and its homepage carries about 4,000 outbound links to unrelated sites plus 77 gambling-term matches (slot/gacor/togel/judi). Verified by direct fetch. The company email is also on that domain, and brand searches surface it instead of mdhost.site.
- **Pick one production domain.** Ideally that's a cleaned-up trissan.co.id serving this new site, with 301 redirects from the mdhost.site subdomain. Do this before building Google Business Profile, citations or links.
- **The homepage H1 changed during the audit,** from "Solusi Kelistrikan & Mekanikal untuk Industri Anda" to "We Provide For User experience". If this was a CMS test, revert it.

## Quick wins

1. In the server `.env`, set `APP_DEBUG=false` and `APP_ENV=production`, then run `php artisan config:cache`.
2. Remove the XML prolog line from `sitemap.blade.php` (or build the XML in the controller), clear compiled views and submit the sitemap in Google Search Console and Bing Webmaster Tools.
3. Turn off Cloudflare "Managed robots.txt", or add `Sitemap:` and `Disallow: /admin` lines to it.
4. Hide `/license` sample certificates and the `/portofolio` sample personnel until real data (NIB, SBU, SMK3, K3, SKK) is entered.
5. Fix the invisible header "Kontak" button on `/works`, `/portofolio` and `/license` (white on transparent at mobile width).
6. Point the Facebook and Instagram links at the real company profiles and add a WhatsApp (`wa.me`) contact link.
7. Add `fetchpriority="high"` to the hero image, remove `loading="lazy"` from above-the-fold images and convert photos to WebP.

## What works

- All 5 pages return 200 over HTTPS with self-referencing canonicals, `lang="id"`, one H1 and good titles and meta descriptions.
- Content is server-rendered. Modal pop-up content is in the HTML, and the pop-ups are not intrusive: none open on load and all close with Esc.
- Valid Organization, WebSite and BreadcrumbList JSON-LD.
- AI search and user bots can access the site (OAI-SearchBot, ChatGPT-User, Claude-User, PerplexityBot all get 200); only training bots are blocked.
- Desktop speed is good (LCP 0.7–2.1 s) and CLS passes everywhere (max 0.053).
- Named, verifiable client projects (Astra Honda Motor, Mayora, Nihon Plast, Indofood CBP).

## Not covered / limitations

- Google PageSpeed Insights was rate-limited (429) on every attempt. Speed numbers come from a local throttled lab run (headless Edge; 1.6 Mbps, 150 ms latency, 4x CPU), not Lighthouse or field data. No CrUX data exists for the site.
- No Google Search Console, GA4, Matomo, Moz, Bing or DataForSEO credentials, so there is no indexation, traffic, query or backlink data.
- Search results came from WebSearch, not live Google results (no People Also Ask, local pack or rank positions). Google Business Profile existence could not be confirmed.
- No drift baseline existed, so this is the first snapshot.

---

# Detailed findings by specialist

## Technical SEO - 46/100

Audited 2026-10-29 against https://trissan.mdhost.site/ (live curl checks plus read-only source review). Pages: /, /about, /portofolio, /license, /works.

### Category status

| Category | Status |
|---|---|
| Crawlability (robots, sitemap) | FAIL |
| Indexability (canonical, duplicates) | PARTIAL |
| Security (HTTPS, headers, debug) | FAIL |
| URL structure / redirects | PARTIAL |
| Mobile (viewport) | PASS |
| Core Web Vitals risk (source) | FAIL (Tailwind CDN, slow TTFB) |
| Structured data | PASS (1 JSON-LD block on home; see schema.md) |
| JS rendering | PASS (server-rendered Blade, modal content in HTML) |
| IndexNow | Not implemented (Info) |

### What works
- All 5 pages return 200, `lang="id"`, one `<h1>` each, unique title and meta description, viewport meta present, no `noindex` anywhere.
- Self-referencing canonicals on every page. Query strings are stripped (`/?utm=1` canonicalises to `https://trissan.mdhost.site`).
- HTTP to HTTPS 301 works (`http://.../about` -> `https://.../about`). Unknown URLs return a real 404.
- `x-content-type-options: nosniff` and `x-frame-options: SAMEORIGIN` are set. Cookies are `secure`, `httponly` (session) and `samesite=lax`.
- No SPA shell. Raw HTML is 60-136 KB per page and contains the full text.

### [Critical] /sitemap.xml returns HTTP 500
- Evidence: `sitemap_discovery.py` found no valid sitemap. `/sitemap.xml` gives 500, and `sitemap_index.xml`, `sitemap-index.xml` and `wp-sitemap.xml` give 404. The exception message is `ParseError: syntax error, unexpected identifier "version"`.
- Cause: `resources/views/sitemap.blade.php` line 1 is `{!! '<?xml version="1.0" ...?>' !!}`. Blade compiles the file to PHP, and the literal `<?xml` inside the compiled output is parsed as a PHP open tag.
- Fix: stop emitting the prolog from Blade. Either build the XML in `SeoFilesController::sitemap()` (string or `response($xml)`) and prefix `'<?xml version="1.0" encoding="UTF-8"?>'."\n"`, or put `<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>` on line 1 of the view. Then run `php artisan view:clear` and confirm `curl -I` returns 200 with `application/xml`.
- The controller also has a latent bug: `$updatedAt[$page]?->toAtomString()` throws "Undefined array key" if a page has no `PageSetting` row. Use `$updatedAt->get($page)?->toAtomString()`.
- After the fix, submit the sitemap in Google Search Console and Bing Webmaster Tools.

### [Critical] Debug mode on in production (information disclosure)
- Evidence: the 500 on `/sitemap.xml` renders the full Laravel debug page. It shows the exception, a stack trace with source paths and the PHP and Laravel versions (PHP 8.4.27, Laravel 13.37.0). Local `.env` has `APP_ENV=local` and `APP_DEBUG=true`. The `.env` values were not printed beyond these flags, and the debug-page contents were not reproduced here.
- Impact: an attacker can read file paths, framework versions and possibly config or SQL on any other error. Error responses also show Google a 500 page with a huge body.
- Fix: on the server set `APP_ENV=production` and `APP_DEBUG=false`, set `APP_URL=https://trissan.mdhost.site` (currently `http://localhost:8000`), then run `php artisan config:clear && php artisan config:cache`. Rotate `APP_KEY` and any secrets if the debug page ever showed environment values.

### [High] robots.txt has no Sitemap line and the app's robots route is bypassed
- Evidence: the live `/robots.txt` is only Cloudflare's managed block (content signals plus about 40 AI-training bots disallowed, `User-agent: * / Allow: /`). It contains no `Sitemap:` and no `Disallow: /admin`. `SeoFilesController::robots()` is written to emit both, but its output does not appear. `sitemap_discovery.py` reports `declared: []`.
- Impact: crawlers get no sitemap hint, and `/admin` (Filament; redirects to `/admin/login`) is not disallowed. The Cloudflare block also disallows ClaudeBot, GPTBot, Google-Extended, Applebot-Extended, CCBot, anthropic-ai, Claude-Web and others. That is deliberate for training, but it may limit AI search citation (see geo.md). `Googlebot` and `Bingbot` are not blocked.
- Fix: in Cloudflare (Security > Bots > "Manage robots.txt") switch to "Disable robots.txt configuration" or to the mode that prepends the managed block and keeps the origin file. Confirm the origin route output appears below the managed block. Make sure it includes `Sitemap: https://trissan.mdhost.site/sitemap.xml`. Also add `noindex` via `X-Robots-Tag` on `/admin/*`, since a robots Disallow alone does not stop indexing.

### [High] Missing security headers (no HSTS, CSP, Referrer-Policy, Permissions-Policy)
- Evidence: the response headers on `/` contain only `x-content-type-options` and `x-frame-options`.
- Fix: enable HSTS in Cloudflare (SSL/TLS > Edge Certificates, `max-age=31536000`, includeSubDomains once subdomains are confirmed HTTPS). Add a small middleware or Cloudflare Transform Rule for `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` and a report-only CSP first. Note the CSP must allow `cdn.tailwindcss.com`, `fonts.googleapis.com` and `fonts.gstatic.com` until Tailwind is moved to a build.

### [High] Tailwind Play CDN loaded in production on all 5 pages
- Evidence: every page includes `<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries">`. `public/build` already exists, so a compiled build is available.
- Impact: Tailwind states the Play CDN is not for production. It is a render-blocking third-party JIT script that generates CSS in the browser after load. That hurts LCP and CLS (unstyled flash, layout shift when styles are injected) and INP (main-thread work). It is also a single point of failure and a third-party dependency on every page.
- Fix: compile Tailwind with Vite (`npm run build`) and link the built CSS instead. Remove the CDN script and put the theme config (brand colors, fonts) in `tailwind.config.js`.

### [Medium] Duplicate URLs: /index.php and /about/ both return 200
- Evidence: `/index.php` returns 200 with `canonical = https://trissan.mdhost.site/index.php` (a different canonical from `/`, so it is a second indexable URL, not a consolidation). `/about/` returns 200 with canonical `/about`. The `.htaccess` trailing-slash redirect does not run, and the edge or origin serves the page directly.
- Fix: add 301s at Cloudflare (Redirect Rules) or the web server: `/index.php` -> `/`, and any `/path/` -> `/path`. In `seo-head.blade.php` build the canonical from `route(Route::currentRouteName())` instead of `url()->current()`, so it is always the named-route URL even when the request path varies.
- Also: the home canonical has no trailing slash (`https://trissan.mdhost.site`). This is equivalent, but use `route('home')` for consistency with the sitemap.

### [Medium] Slow and uneven server response (TTFB)
- Evidence: single-run TTFB through Cloudflare was /=1.5s, /works=2.2s, /portofolio=0.8s, /license=0.5s, /about=0.2s. This was one sample per page, so treat it as indicative.
- Cause: every response is `Cache-Control: no-cache, private`, `cf-cache-status: DYNAMIC`, and sets `laravel-session` plus `XSRF-TOKEN` cookies even for anonymous visitors. That prevents any edge caching of these static-content pages.
- Fix: cache the rendered pages (Laravel response cache or `Cache::remember` of the CMS data, invalidated from Filament). Move the session and CSRF cookie to the contact form only (the `web` middleware group is on every route) so Cloudflare can cache HTML. Send `Cache-Control: public, max-age=300, s-maxage=3600` for the public GET pages. Target TTFB below 600 ms.

### [Medium] Custom 404 is the default Laravel page in English
- Evidence: `/nonexistent-xyz` returns 404 with `<html lang="en">`, title "Not Found" and the framework's inline CSS, with no site navigation.
- Fix: add `resources/views/errors/404.blade.php` in Indonesian with navbar and links back to the main pages. This recovers users and spreads link equity to real pages.

### [Low] Missing /favicon.ico (404) and apple-touch-icon fallback
- Evidence: `/favicon.ico` returns 404 even though `<link rel="icon">` points at `/storage/seed/logo.png`. The `SeoFilesController::favicon()` route is meant to redirect there but does not respond, probably for the same reason as the robots route (a static or edge rule takes precedence).
- Fix: confirm the route is reachable at the origin, or place a real `favicon.ico` in `public/`. The favicon file is named `seed/logo.png`, a seeder placeholder path. Upload a proper square icon (48x48 multiples) through the CMS.

### [Low] Google Fonts loaded twice, no font preload on the home page
- Evidence: the head includes `Material Symbols Outlined` stylesheets twice (two different axis ranges) plus the Inter/Montserrat stylesheet. `/works` has 3 stylesheet links versus 2 elsewhere.
- Fix: keep only one Material Symbols request (subset to the icons used, or switch to inline SVG, which the pages already use in places). Self-host the Inter and Montserrat WOFF2 files and preload the weights used above the fold.

### [Low] Cloudflare email-decode script on pages
- Evidence: `/cdn-cgi/scripts/.../email-decode.min.js` is injected on the home page (Cloudflare Email Address Obfuscation).
- Impact: small extra request. It also obfuscates mailto addresses from simple crawlers. Consider disabling it if the email address should be machine-readable for LocalBusiness data.

### [Info] Other checks
- Mobile: viewport meta `width=device-width, initial-scale=1.0` is present on the home page and the inner pages use the same layout. Touch-target sizing was not verified from source.
- Hreflang: none, and none is needed (single language, `lang="id"`). Delegate to `seo-hreflang` only if an English version is added.
- IndexNow: not implemented. Optional for Bing, Yandex and Naver. Add a key file in `public/` and ping on Filament save events.
- Modal pop-up content is in the initial HTML, so it is crawlable. If hidden through `display:none` it may be weighted lower. Prefer visible headings for the key service text.
- `trustProxies(at: '*')` in `bootstrap/app.php` is fine behind Cloudflare, but restrict it to Cloudflare IP ranges if the origin is reachable directly.
- Redirect chains: none found. The HTTP to HTTPS hop is a single 301. `www.trissan.mdhost.site` does not resolve (expected for a subdomain host).

### Prioritised fix order
1. Fix `sitemap.blade.php` and the `PageSetting` null lookup, then submit the sitemap.
2. Set `APP_DEBUG=false`, `APP_ENV=production`, correct `APP_URL`, and recache config.
3. Restore the origin robots.txt (Sitemap line, `/admin` disallow) behind Cloudflare's managed block.
4. Replace the Tailwind CDN with the Vite build.
5. Add HSTS and the other headers, 301s for `/index.php` and trailing slashes, and a route-based canonical.
6. Cache public pages and drop the session cookie from anonymous traffic.
7. Custom Indonesian 404 page, favicon and font clean-up.

**Technical score: 46/100** (crawlability 8/25, indexability 15/20, security 6/20, URL/redirects 6/10, mobile 8/10, CWV risk 3/15 -> normalised).

---

## Sitemap findings - https://trissan.mdhost.site/

Audited 2026-10-29. Sources read (read-only): `app/Http/Controllers/SeoFilesController.php`, `resources/views/sitemap.blade.php`, `routes/web.php`. Live responses were fetched with curl and treated as untrusted data.

### Score: 10 / 100

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

### Findings

#### 1. CRITICAL: /sitemap.xml returns 500 (ParseError)
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

#### 2. HIGH: robots.txt does not advertise the sitemap
- Live robots.txt contains only Cloudflare "Managed content" (lines up to `# END Cloudflare Managed Content`, line 129). It has no `Sitemap:` line and none of the origin's `Disallow: /admin`.
- `SeoFilesController::robots()` already emits `Sitemap: <route('sitemap')>` and `Disallow: /admin`, and a test covers it. The live output shows neither, so Cloudflare's managed robots.txt is replacing or overriding the origin output.
- Fix: in Cloudflare, go to Security > Bots > "Manage your robots.txt". Either turn the managed robots.txt off so Laravel's route is served, or keep it and add the origin's content. The content must include `Sitemap: https://trissan.mdhost.site/sitemap.xml` and `Disallow: /admin`.
- Until then, submit the sitemap in Google Search Console and Bing Webmaster Tools.

#### 3. LOW: latent null-safety bug in `sitemap()`
- `$updatedAt[$page]?->toAtomString()` on an Eloquent `pluck()` Collection throws "Undefined array key" (a 500) if a `page_settings` row is missing. `?->` does not guard a missing key.
- Fix: `$updatedAt->get($page)?->toAtomString()`. The view already handles a null `lastmod`.

#### 4. LOW: `lastmod` accuracy (verify after launch)
- `lastmod` comes from `PageSetting.updated_at`, which means CMS edits to the page's settings. It will not reflect changes to portfolio, works or license items shown on those pages.
- Keep `lastmod` only if it moves when the visible content changes. For /works, /portofolio and /license, consider `max(PageSetting.updated_at, latest item updated_at)`. Otherwise omit `lastmod`, since Google ignores it when it proves unreliable.
- Do not use the current time as `lastmod`.

#### 5. INFO: `loc` host depends on APP_URL
- `route()` builds absolute URLs from `APP_URL`/request host. Confirm production emits `https://trissan.mdhost.site/...` (https, no trailing-slash variation, no internal host).
- Home must be `https://trissan.mdhost.site/` and match the canonical URL.

### What the sitemap should contain

Include (5 canonical, indexable, 200 URLs, all already in `SITEMAP_PAGES`): `/`, `/about`, `/portofolio`, `/license`, `/works`.

Exclude `/works?category=...`:
- Category filtering runs in the browser on a single page (see the `WorksController` docblock). The parameter URLs return the same HTML as `/works`, so they are not separate pages.
- Listing them would create duplicate or parameter URLs. Google advises listing only canonical URLs.
- Verify that each `/works?category=...` URL has a canonical pointing at `/works`. `layouts/seo-head.blade.php` uses `{{ $seoUrl }}`. Check that `$seoUrl` strips the query string.
- Revisit only if categories become real server-rendered pages with unique content and their own canonicals (for example `/works/category/{slug}`).
- Also leave out `/admin`, Livewire and asset endpoints, and `/favicon.ico` (it redirects).

### Reference sitemap.xml (current pages)

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

### Expected result after fixes
Sitemap 200 with `application/xml`, 5 valid URLs, a `Sitemap:` line in robots.txt, and category URLs excluded. That would put the score at about 95/100. The remaining gap is the `lastmod` accuracy in finding 4.

### Verification gaps
- URL statuses for the five pages and their canonical or noindex tags were not individually checked (only `/works?category=x` returned 200).
- The production `short_open_tag` value is inferred from the reproduced ParseError. It was not read from the server's config.

---

## Content Quality: 36/100

Scope: 5 public pages (`/`, `/about`, `/portofolio`, `/license`, `/works`), fetched 2026-10-29 (server-rendered, not an SPA). Scoring uses trafilatura `extracted_text`. Language: Indonesian (lang=id). The site was judged against Indonesian B2B search habits, where queries are mostly in Indonesian with industry English mixed in (e.g. "jasa pembuatan conveyor Bekasi", "kontraktor fire hydrant Cikarang", "jasa panel listrik PLC").

| Score | Value |
|---|---|
| Content quality | **36 / 100** |
| E-E-A-T (weighted, internal model) | **34 / 100**: Experience 55, Expertise 35, Authoritativeness 25, Trust 25 |
| AI citation readiness | **38 / 100** |
| Metadata templating (`metadata_template.py`, 5 pairs) | site_risk **low**, templated_ratio **0.0**, shared_cta_phrases **none** |

**What works**
- Real client projects with named sites, e.g. "PT Astra Honda Motor Plant 3 MM2100 Cibitung", Mayora Indah, Indofood CBP Purwakarta, Nihon Plast, Mikuni and Yaskawa-AHM. Each has a dated scope in the modal `data-desc` (24-27 Nov 2023). These are good first-hand experience signals.
- The org chart on `/about` is in crawlable text and lists 24 named people with roles (Commissioner, Director, COO, engineers, R&D, Timika rep). Workshop machines (milling, cutting, CNC) are described with photos.
- The Bekasi head office NAP is complete (Ruko Metland Tambun Blok A2 No.24, 17510), with 2 landlines and Organization JSON-LD.
- The prose is clear Indonesian with about 12-14 words per sentence on the prose pages, which is easy to read. Titles and descriptions are unique per page with no bulk-template pattern.

### [Critical] License page publishes placeholder/fabricated certifications
Evidence: `/license` shows "Data contoh" and "Ganti melalui CMS" visibly, plus the sentence "Data pada halaman ini adalah contoh dan perlu diganti dengan dokumen resmi." It also lists invented credentials that don't fit the business: AS9100D Aerospace "SAE International & Nadcap Approved CERT-AS-9100D-019", ASME "Stamp U ASME-STAMP-U-4412", ISO 9001 by LRQA "CERT-ISO-9001-2024", "Turbine Shaft Hardening Nadcap Audit Pass ... Inconel 625", "IEC 61439-2 ... 65 kA", "Zeiss CMM ... 0.1 µm". Two of them show as already **expired**: ISO 45001 valid until 10 Feb 2026 and SMK3 until 14 Jan 2026. The downloadable PDFs are under `/storage/seed/certificates/`.
Why it matters: under the QRG, unverifiable or false credentials are a direct Trust failure and can pull a whole site toward a Lowest rating. A procurement officer who checks one certificate number loses trust in every other claim on the site.
Fix: take all sample rows and product certificates down now (unpublish in the CMS or hide the section). Republish only real documents. For an Indonesian M/E contractor the usual ones are NIB (OSS-RBA) with KBLI codes, SBU from LPJK, SKK/SKA for engineers, K3 Listrik/K3 Umum (Kemnaker), IUJPTL if applicable, and ISO only if actually held. Show the issuer, number, validity and a scan for each.

### [Critical] Fabricated "Matriks Direktori 5 Personil Terdaftar" on /portofolio
Evidence: anonymous entries such as "REG: ENG-CNC-001 Lead 5-Axis CNC Machinist, Fokus Teknis: Inconel & Titanium Aerospace Blisks", "High-Tolerance Subsea Valve Shafts", "Chief Metrologist ... AS9100 First Article Inspection" and "ASME Sec IX Clad Overlay & Pressure Vessels". These are labelled "Terverifikasi" (verified) but belong to a different industry (aerospace/subsea) and don't match the real 24-person org chart on `/about`.
Fix: delete the registry. Replace it with real team members from the org chart: name, role, years of experience, and real certificates (e.g. Ahli K3 Listrik, SKK Teknik Mekanikal, PLC vendor training) linked to the projects they led.

### [High] Trust claims not backed by evidence on the site
Evidence: the home "DIPERCAYA PERUSAHAAN TERKEMUKA" strip shows YAMAHA, YASKAWA, DAIKIN and FREEPORT, but only Yaskawa appears in any project. `/portofolio` claims "20+ Klien Industri ... Pertambangan" while `/works` says "6 Klien Proyek" and shows no mining project. "145 Karyawan" isn't explained anywhere. There's no founding year anywhere, and the "Milestone" timeline has a single entry (2023).
Fix: show only logos of clients you have work for (add a Freeport/Timika project if one exists). Use one consistent client count. Add the founding year, the legal entity details (NIB/NPWP) and a real multi-year milestone timeline.

### [High] Service offering is thin: 12 services as one-line blurbs, no service URLs
Evidence: each service gets one sentence, e.g. "Sistem lubrikasi otomatis untuk alat berat." Services link to `/works?category=...` filters, which show 0-2 projects. Home `extracted_text` is 451 words, below the 500-word homepage floor, and none of the 12 services clears the 800-word service-page floor because none has its own page.
Fix: build Indonesian service pages for the 4-6 core money terms: "Jasa Pembuatan Konveyor", "Instalasi Fire Hydrant", "Panel Listrik, PLC & HMI", "Maintenance Mesin & Konveyor", "Auto Grease System", "Jigs & Fixtures". Each should have 800+ words covering scope, process, materials/brands, related projects, areas served (Bekasi, Cikarang, MM2100, Jababeka, Karawang, Purwakarta, Malang, Timika), an FAQ and a WhatsApp CTA.

### [Medium] Project entries are 1-2 sentences, and the modal spec fields are empty
Evidence: e.g. Nihon Plast: "Proyek drying conveyor untuk PT. Nihon Plast." `data-tolerance` and `data-batch` are empty on all 7 cards. The modal headings "Spesifikasi Utama" and "Sertifikasi & Standar Mutu" render empty, and the sort options "Toleransi Tertinggi (Micron)" and "Skala Batch Terbesar" sort on no data.
Fix: write 150-300-word case studies (problem, solution, specs such as belt width/length/motor/PLC brand, duration, result, photos). Remove the sort options and modal fields that have no data.

### [Medium] Freshness: no new work since November 2023
Evidence: all 7 projects are dated 24-27 Nov 2023, the milestone list stops at 2023, and the footer says © 2026. Nothing on the site signals an update in about 3 years.
Fix: add recent projects (2024-2026) with dates, extend the milestone timeline, and show "Diperbarui" dates on project and service pages.

### [Medium] English-first headings on an Indonesian site
Evidence: H3/H4 service names ("Workforce & Labor Solutions", "Other Special Construction"), "Contact Us", "EXPLORE WORKS", "Deliverables" and the `/works` category labels ("Selection, Placement And Provision Activities domestic Part-time Labor") are English or KBLI-style.
Fix: use Indonesian primary wording and keep established English terms in parentheses, e.g. "Penyediaan Tenaga Kerja (Outsourcing)", "Sistem Konveyor (Material Handling)", "Hubungi Kami".

### [Medium] Icon ligature text pollutes headings and extracted content
Evidence: Material Symbols names end up in the text: "precision_manufacturing", "smart_toy", "analytics", "zoom_in", and the H4 "tuneSpesifikasi Utama" / "verifiedSertifikasi & Standar Mutu". The HTML also has empty H2/H3 elements (in the `/works` and `/portofolio` modals).
Fix: render icons as SVG, or as ligature spans with `aria-hidden="true"` outside heading elements. Give modal headings static text or make them non-heading elements.

### [Medium] Branch offices have no addresses
Evidence: Malang and Timika only appear in "Bekasi • Malang • Timika" and as "kantor perwakilan Timika". There's no address, phone or map for either.
Fix: add full NAP for each office (one short location section or page per office) and match it to Google Business Profiles.

### [Medium] Weak AI citation readiness
Evidence: no FAQ, no definition-style answer paragraphs, and no founding year, legal name format, service area list or capacity figures in plain text. The email is hidden behind Cloudflare email obfuscation in the HTML; it's only plain in the JSON-LD.
Fix: add a short "Sekilas PT. Trissan" fact block (founded, legal entity, NIB, offices, industries, headcount, key clients) and a 6-8 question Indonesian FAQ on the home and service pages (biaya, durasi, area layanan, garansi/purna jual). Show the email as plain text.

### [Low] Boilerplate repeated on every page
Evidence: the paragraph "PT. Trissan didirikan dengan tujuan memberi solusi..." appears in the home hero/about section, on `/about`, and in the footer of all 5 pages. It is generic mission copy with no specifics.
Fix: keep one specific version on `/about` and replace the footer copy with a one-line factual summary.

### [Low] Non-functional Privacy Policy / Terms links
Evidence: the footer shows `<span>Privacy Policy</span> <span>Terms of Service</span>` as plain text with no links or pages. The contact form collects name, email and phone.
Fix: publish a "Kebijakan Privasi" page (UU PDP No. 27/2022 applies to form data) and link it from the form and footer.

### [Low] WhatsApp contact missing
Evidence: the footer label says "TELEPON HOTLINE & WHATSAPP" but lists only (021) landlines, and there's no `wa.me` link. Indonesian B2B buyers expect a WhatsApp contact.
Fix: add a `wa.me/62...` mobile link next to the phone numbers and on every service CTA.

### [Low] Metadata (secondary)
Evidence: 3 descriptions (`/about`, `/portofolio`, `/license`) repeat the brand "PT. Trissan" that's already in the title (`brand_suffix_in_description`). There are no templated, duplicated or shared-CTA patterns.
Fix: optional. Use the description space for location and service terms instead, e.g. "...kontraktor konveyor & fire hydrant di Bekasi/Cikarang".

### [Info] AI-generated content assessment
The prose itself reads human and specific where it describes real projects. The quality problem is placeholder template data left from a theme/seed (aerospace, subsea and Nadcap content) that contradicts the real business, not AI phrasing. Removing it is the single biggest content-quality gain available.

### Word counts (extracted / visible incl. chrome)
`/` 451 / 1113 · `/about` 223 / 691 · `/portofolio` 264 / 477 · `/license` 238 / 673 · `/works` 137 / 628

**Final Content Quality score: 36 / 100.** It is pulled down mainly by the publicly visible sample certifications and personnel registry (Trust). It would recover to about 60-65 once those are removed and real NIB/SBU/K3 credentials are added, and to 75+ with Indonesian service pages and expanded case studies.

---

## On-Page SEO — 65/100

**What works**
- Unique titles 26-62 chars and meta descriptions 124-155 chars on all pages
- Exactly one H1 per page
- Open Graph and Twitter tags present on all pages
- Good internal linking between home, works and service categories

### [Medium] Weak keyword targeting in titles

Titles lack location and high-intent service terms ('Tentang Kami | PT. Trissan').

**Fix:** e.g. 'Kontraktor Mekanikal & Elektrikal Industri di Bekasi | PT. Trissan'.

### [Low] Heading hierarchy issues

Homepage has 12 H3s before the first H2; /works has an empty H2; H1s on /portofolio, /license, /works are ALL CAPS in source.

**Fix:** Wrap the service grid under an H2, remove the empty H2, write H1s in sentence case and uppercase via CSS.

### [Low] og:image is the logo on every page

Shares on WhatsApp/LinkedIn/Facebook show a logo, not a project photo.

**Fix:** Use a 1200x630 image per page.

#### [High] Homepage H1 changed mid-audit to English placeholder text

At 09:49 UTC the homepage H1 was "Solusi Kelistrikan & Mekanikal untuk Industri Anda". A later fetch the same day returned "We Provide For User experience" (title tag unchanged). The new H1 is English on a `lang="id"` page, contains no service or location keyword and does not match the title.

**Fix:** If this was a CMS test edit, revert it. Otherwise use an Indonesian H1 with service + location, e.g. "Kontraktor Mekanikal, Elektrikal & Otomasi Industri di Bekasi".

#### [High] No URL per service

12 services exist only as homepage cards and `/works?category=` filters that canonicalise to `/works` (see sxo.md, content.md).

**Fix:** Self-canonical `/layanan/{slug}` pages for the core services.

---

## Schema / Structured Data Audit - https://trissan.mdhost.site/

Pages checked: `/`, `/about`, `/portofolio`, `/license`, `/works` (raw HTML fetch, 2026-10-29).
Score: **62/100**

### 1. Detection

| Page | Format | Blocks | Types found |
|---|---|---|---|
| `/` | JSON-LD (single `@graph`) | 1 | Organization, PostalAddress, ContactPoint x2, WebSite |
| `/about`, `/portofolio`, `/license`, `/works` | JSON-LD (single `@graph`) | 1 each | Same as `/` plus BreadcrumbList (Beranda > page) |

- No Microdata or RDFa. JSON-LD is in the raw server HTML (not client-injected).
- No FAQPage, HowTo, SpecialAnnouncement or other deprecated types.

### 2. Validation

| Check | Result |
|---|---|
| JSON parses, `@context` = `https://schema.org` | PASS (all 5) |
| `@id` cross-reference (`WebSite.publisher` -> `#organization`) | PASS |
| Absolute URLs (`url`, `logo`, breadcrumb `item`) | PASS |
| Logo URL resolves | PASS (HTTP 200, `/storage/seed/logo.png`) |
| Placeholder text in JSON-LD | PASS (none) |
| Organization required fields | PASS (Organization has no hard requirements) |
| BreadcrumbList position/name/item | PASS |
| Breadcrumb/`url` match canonical (no trailing slash) | PASS |

No blocking errors. Issues below are completeness and quality gaps.

### 3. Findings

#### [High] No `sameAs`; social links are generic homepages
Footer links go to `https://www.facebook.com/` and `https://www.instagram.com/`, not company profiles. Organization has no `sameAs`, so there is no entity corroboration.
**Fix:** Find or create the real profiles (Facebook, Instagram, LinkedIn, Google Business Profile), fix the footer links, add them to `sameAs` (block A).

#### [Medium] Address is one unstructured string
`streetAddress` holds the whole address. `addressLocality`, `addressRegion`, `postalCode` are missing. **Fix:** split the fields (block A).

#### [Medium] Branches (Malang, Timika) absent from schema
The site says "tiga kantor di Bekasi, Malang, dan Timika", but only Bekasi is in the schema. Branch addresses are not visible on the pages checked, so the client must supply them.

#### [Medium] No physical-business signals
No `geo`, `hasMap`, `areaServed` or `openingHoursSpecification`. The page already links Google Maps for the HQ address (reusable as `hasMap`). Opening hours are not on the site; confirm with the client. Do not invent coordinates.

#### [Medium] Staging domain in every `@id`, `url` and breadcrumb
All identifiers use `trissan.mdhost.site`, while the email is `@trissan.co.id`. At go-live the production domain must replace it everywhere.
**Fix:** build schema URLs from `config('app.url')` and confirm staging is `noindex` (see technical findings).

#### [Medium] Services not in structured data
The homepage lists 12 services. **Fix:** `OfferCatalog` / `Service` nodes with `provider` = `#organization` (block B). No Google rich result exists for Service; the value is entity clarity.

#### [Low] No page-level `WebPage` nodes
BreadcrumbList has no `@id` and is not tied to a page node. **Fix:** block C.

#### [Low] Organization lacks descriptive properties
Missing `description`, `legalName`, `foundingDate`, `numberOfEmployees`. `/portofolio` shows 145 employees, but that page also holds sample data, so confirm with the client before marking it up. No confirmed founding year was found.

#### [Low] Project pages have no list markup
`/works` and `/portofolio` show 7 documented projects (Astra Honda Motor, Mayora Indah, Nihon Plast, Indofood CBP, Mikuni, Yaskawa-AHM). An `ItemList` helps entity understanding (block D). Do NOT use Product, Review or AggregateRating: the site has no ratings or reviews.

#### [Low] Two ContactPoints with the same `contactType`
Both are "customer service". Label the roles (e.g. sales vs. general) or keep one.

#### [Info] `/license` must stay unmarked until real data is in
The page states its data is sample ("data contoh ... perlu diganti dengan dokumen resmi"). It lists ISO 9001, AS9100D, ASME U-Stamp and ISO 45001/14001 with fake-looking IDs. Do not emit `hasCredential` from it: wrong credentials in schema is a trust and compliance risk. Expiry dates for ISO 45001 (10 Feb 2026) and the ASME stamp (1 Sep 2026) are already past as of today. Aerospace/ASME claims also do not match the stated business, so these look like template leftovers. Block E is the template for later.

#### [Info] `/portofolio` personnel directory is sample content
Entries such as "Lead 5-Axis CNC Machinist ... Inconel & Titanium Aerospace Blisks" do not fit the business. Do not add `Person`/`employee` schema for them.

#### [Info] FAQPage / HowTo
None present, none recommended (FAQ rich results retired; HowTo deprecated).

#### [Info] Strengths
Single clean `@graph`, stable `@id`s across pages, `inLanguage: "id"` consistent with `og:locale id_ID`, E.164 phone numbers, valid logo, no obsolete `SearchAction`.

### 4. Ready-to-paste JSON-LD

Replace every `REPLACE_*` value with confirmed facts, or delete the property. Use the production domain at launch.

#### A. Organization (replaces the existing Organization node; site-wide)
```json
{
  "@type": ["Organization", "ProfessionalService"],
  "@id": "https://trissan.mdhost.site/#organization",
  "name": "PT. Trissan",
  "legalName": "REPLACE_LEGAL_NAME",
  "slogan": "Always Present Better",
  "description": "PT. Trissan menyediakan produk dan layanan kelistrikan serta mekanikal industri: material handling, otomasi, panel distribusi, hingga perawatan mesin.",
  "url": "https://trissan.mdhost.site",
  "logo": "https://trissan.mdhost.site/storage/seed/logo.png",
  "email": "marketing@trissan.co.id",
  "telephone": "+622188373107",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "Ruko Metland Tambun Blok A2 No.24",
    "addressLocality": "Tambun Selatan, Bekasi",
    "addressRegion": "Jawa Barat",
    "postalCode": "17510",
    "addressCountry": "ID"
  },
  "hasMap": "https://maps.google.com/?q=Ruko+Metland+Tambun+Blok+A2+No.24+Tambun+Selatan+Bekasi",
  "areaServed": { "@type": "Country", "name": "Indonesia" },
  "hasOfferCatalog": { "@id": "https://trissan.mdhost.site/#services" },
  "sameAs": [
    "REPLACE_FACEBOOK_PAGE_URL",
    "REPLACE_INSTAGRAM_PROFILE_URL",
    "REPLACE_LINKEDIN_COMPANY_URL",
    "REPLACE_GOOGLE_BUSINESS_PROFILE_URL"
  ],
  "contactPoint": [
    { "@type": "ContactPoint", "telephone": "+622188373107", "email": "marketing@trissan.co.id", "contactType": "customer service", "areaServed": "ID", "availableLanguage": "id" },
    { "@type": "ContactPoint", "telephone": "+622188321576", "contactType": "REPLACE_ROLE", "areaServed": "ID", "availableLanguage": "id" }
  ],
  "department": [
    {
      "@type": "Organization",
      "@id": "https://trissan.mdhost.site/#branch-malang",
      "name": "PT. Trissan - Kantor Malang",
      "address": { "@type": "PostalAddress", "streetAddress": "REPLACE_MALANG_STREET", "addressLocality": "Malang", "addressRegion": "Jawa Timur", "postalCode": "REPLACE", "addressCountry": "ID" }
    },
    {
      "@type": "Organization",
      "@id": "https://trissan.mdhost.site/#branch-timika",
      "name": "PT. Trissan - Kantor Timika",
      "address": { "@type": "PostalAddress", "streetAddress": "REPLACE_TIMIKA_STREET", "addressLocality": "Timika", "addressRegion": "REPLACE_PROVINCE", "postalCode": "REPLACE", "addressCountry": "ID" }
    }
  ]
}
```
Optional once confirmed: `"foundingDate": "REPLACE_YYYY"`, `"numberOfEmployees": {"@type":"QuantitativeValue","value":145}`, `"geo"` (GeoCoordinates), `"openingHoursSpecification"`.

#### B. Service catalog (add to the `@graph` on `/`)
```json
{
  "@type": "OfferCatalog",
  "@id": "https://trissan.mdhost.site/#services",
  "name": "Layanan PT. Trissan",
  "itemListElement": [
    { "@type": "Service", "name": "Mechanical & Electrical", "description": "Rekayasa mekanikal presisi dan instalasi kelistrikan industri terpadu.", "provider": { "@id": "https://trissan.mdhost.site/#organization" }, "areaServed": "ID" },
    { "@type": "Service", "name": "Digital Automation & Controlling", "description": "Sistem kendali terotomasi modern dengan PLC/SCADA.", "provider": { "@id": "https://trissan.mdhost.site/#organization" }, "areaServed": "ID" },
    { "@type": "Service", "name": "Jigs & Fixtures", "description": "Fabrikasi jig presisi dan clamping fixture.", "provider": { "@id": "https://trissan.mdhost.site/#organization" }, "areaServed": "ID" },
    { "@type": "Service", "name": "Unload Robot Chucking", "description": "Sistem chucking otomatis untuk robot industri.", "provider": { "@id": "https://trissan.mdhost.site/#organization" }, "areaServed": "ID" },
    { "@type": "Service", "name": "Material Handling", "description": "Konveyor dan sistem logistik internal pabrik.", "provider": { "@id": "https://trissan.mdhost.site/#organization" }, "areaServed": "ID" },
    { "@type": "Service", "name": "Distribution Panel, PLC, HMI", "description": "Perakitan panel distribusi dan pemrograman HMI.", "provider": { "@id": "https://trissan.mdhost.site/#organization" }, "areaServed": "ID" },
    { "@type": "Service", "name": "Electrical & Machine Maintenance", "description": "Perawatan preventif peralatan listrik dan mesin.", "provider": { "@id": "https://trissan.mdhost.site/#organization" }, "areaServed": "ID" },
    { "@type": "Service", "name": "Electrical Equipment Supplier", "description": "Pengadaan komponen kelistrikan industri.", "provider": { "@id": "https://trissan.mdhost.site/#organization" }, "areaServed": "ID" },
    { "@type": "Service", "name": "Auto Grease System (Loader)", "description": "Sistem lubrikasi otomatis untuk alat berat.", "provider": { "@id": "https://trissan.mdhost.site/#organization" }, "areaServed": "ID" },
    { "@type": "Service", "name": "Industrial Machinery & Parts", "description": "Pemasok peralatan industri dan suku cadang.", "provider": { "@id": "https://trissan.mdhost.site/#organization" }, "areaServed": "ID" },
    { "@type": "Service", "name": "Other Special Construction", "description": "Pekerjaan konstruksi spesialis industri.", "provider": { "@id": "https://trissan.mdhost.site/#organization" }, "areaServed": "ID" },
    { "@type": "Service", "name": "Workforce & Labor Solutions", "description": "Penyediaan tenaga kerja terampil manufaktur.", "provider": { "@id": "https://trissan.mdhost.site/#organization" }, "areaServed": "ID" }
  ]
}
```
No prices appear on the site, so no `Offer` is included.

#### C. Page node (example `/about`; repeat per page)
Use `AboutPage` for `/about`, `CollectionPage` for `/works` and `/portofolio`, `WebPage` for `/` and `/license`.
```json
{
  "@type": "AboutPage",
  "@id": "https://trissan.mdhost.site/about#webpage",
  "url": "https://trissan.mdhost.site/about",
  "name": "REPLACE_WITH_PAGE_TITLE",
  "inLanguage": "id",
  "isPartOf": { "@id": "https://trissan.mdhost.site/#website" },
  "about": { "@id": "https://trissan.mdhost.site/#organization" },
  "breadcrumb": { "@id": "https://trissan.mdhost.site/about#breadcrumb" }
}
```
Add `"@id": "https://trissan.mdhost.site/about#breadcrumb"` to the existing BreadcrumbList on that page.

#### D. Project list (add to `/works`; mirror on `/portofolio`)
```json
{
  "@type": "ItemList",
  "@id": "https://trissan.mdhost.site/works#projects",
  "name": "Portofolio Proyek PT. Trissan",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Fasum OP Mainline Factory Water Meter & Bypass Chiller Installation" },
    { "@type": "ListItem", "position": 2, "name": "Fire Hydrant System" },
    { "@type": "ListItem", "position": 3, "name": "Chiller Duct Bypass" },
    { "@type": "ListItem", "position": 4, "name": "Drying Conveyor" },
    { "@type": "ListItem", "position": 5, "name": "Conveyor Maintenance" },
    { "@type": "ListItem", "position": 6, "name": "Material Handling Conveyor for Automation" },
    { "@type": "ListItem", "position": 7, "name": "Tending Conveyor Auto Cutting Automotive Parts" }
  ]
}
```
Generate from the database in the Blade view; add per-item `url` if project detail pages are created.

#### E. Certifications (DO NOT publish until `/license` holds real data)
```json
{
  "@type": "EducationalOccupationalCredential",
  "name": "REPLACE_CERT_NAME",
  "credentialCategory": "certification",
  "recognizedBy": { "@type": "Organization", "name": "REPLACE_ISSUER" }
}
```
Attach via `"hasCredential": [ ... ]` on the Organization node.

### 5. Implementation notes (Laravel)
- Build the `@graph` once in a shared Blade partial from `config('app.url')` and DB-backed settings (address, phones, branches, social URLs), so the staging-to-production switch needs no schema edits.
- Emit with `json_encode(..., JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)` in `<script type="application/ld+json">`.
- Re-validate with Google Rich Results Test and validator.schema.org after changes.

### 6. Score: 62/100
Strong base (valid, server-rendered, consistent IDs, correct breadcrumbs) offset by missing `sameAs`, unstructured address, no branches/geo/hours, no Service or page-level nodes, and staging-domain IDs. Completing blocks A to D with real data should reach roughly 85 to 90.

---

## Performance (CWV) - 34/100 (lab-estimated, mobile-throttled)

Evidence: PSI API retried once on 2026-10-29, still 429 (no Lighthouse build/score available). CrUX: none (new site).
Primary data = local throttled lab run (perf-lab.json; headless Edge, 1.6 Mbps / 150 ms / 4x CPU) plus live HTML/header
inspection. Lab numbers are single runs and only indicative; they are not the 75th-percentile field assessment.
Score is an analyst estimate, not a Lighthouse score.

### Core Web Vitals status (lab, no field data)

| Page | Mobile LCP | Mobile FCP | CLS (m/d) | Desktop LCP | Transfer | Status |
|---|---|---|---|---|---|---|
| / | 18.4 s (hero img) | 4.5 s | 0.007 / 0.009 | 1.1 s | 4.9 MB | LCP FAIL (poor) |
| /about | 9.6 s (about-hero.png) | 3.2 s | 0.003 | 0.7 s | 1.9 MB | LCP FAIL (poor) |
| /portofolio | 22.7 s (auto-1.jpg) | 4.2 s | 0.053 / 0.003 | 2.1 s | 4.3 MB | LCP FAIL, CLS good |
| /license | 4.7 s (text) | 4.7 s | 0 | 2.0 s | 0.6 MB | LCP FAIL (poor, just over 4.0 s) |
| /works | 22.3 s (work-3.jpg) | 3.6 s | 0.001 | 1.0 s | 4.3 MB | LCP FAIL (poor) |

- LCP: FAIL on mobile for all 5 pages (threshold 2.5 s; all are above the 4.0 s "poor" line). Desktop is good
  except /portofolio (2.1 s) and /license (2.0 s), which are TTFB-driven but still under 2.5 s.
- INP: not measurable in lab. Risk is low-moderate: about 926 DOM nodes on home (under 1,500), but the Tailwind runtime
  uses a MutationObserver that regenerates CSS on DOM changes, and modals (portfolio/license/works) toggle classes.
  Expect Good, but confirm with field data once traffic exists.
- CLS: PASS everywhere (max 0.053). Latent risk: no `<img>` on any page has width/height, and font swap
  (display=swap) on Montserrat/Inter/Material Symbols can shift text. Currently absorbed by fixed-aspect containers.

### Findings

#### [Critical] Mobile LCP 9-23 s: oversized, unprioritized, eager/lazy-misapplied images
- LCP images are 200-785 KB JPG/PNG served at original size (no srcset/sizes, no AVIF/WebP).
  Home transfers 4.9 MB, /portofolio and /works 4.3 MB each (images about 4.26 MB).
- Home: all 13 `<img>` are eager (0 `loading="lazy"`), so 3 stacked hero slides + 6 work cards + teaser compete with
  the LCP image on a 1.6 Mbps pipe. No `fetchpriority="high"` and no `<link rel="preload" as="image">` on any page.
- /portofolio and /works: the LCP element is an image with `loading="lazy"` (7 of 7 gallery images lazy, including the
  first above-the-fold one). Lazy-loading the LCP candidate delays discovery until layout; this explains 22 s LCP vs
  3.6-4.2 s FCP.
- /about: `about-hero.png` is a PNG with photographic content and no priority hint; LCP 9.6 s.
- The live home hero is now a CMS-uploaded PNG (`/storage/hero/DHU8...F.png`) measuring 5.07 MB (verified by download),
  whereas the lab run measured seeded `hero-1.jpg` (4.9 MB page total). Real home LCP on mobile is therefore likely
  worse than the 18.4 s lab figure. Other sizes: hero-2.jpg 320 KB, hero-3.jpg 240 KB, about-hero.png 397 KB,
  work-1.jpg 458 KB. Convert and cap dimensions on upload.
- Fix (expected: -60-80% image bytes, mobile LCP to roughly 2.5-4 s):
  1. Resize to display width (hero <=1600 px, cards <=800 px), encode AVIF/WebP at q~75; target hero <=120 KB,
     cards <=60 KB. Do conversion on upload (e.g. Intervention Image or Spatie media-library conversions).
  2. Hero / first-row image: `fetchpriority="high"`, `loading="eager"`, plus `<link rel="preload" as="image" imagesrcset>`.
  3. Remove `loading="lazy"` from above-the-fold images; keep it (plus `decoding="async"`) for the rest; lazy-load
     hero slides 2-3.
  4. Add `width`/`height` (or aspect-ratio) and `srcset`/`sizes` to every `<img>`.

#### [High] Tailwind Play CDN (cdn.tailwindcss.com) is a render-blocking runtime compiler on all 5 pages
- Synchronous script in `<head>`; it compiles CSS in the browser on each visit and is the main mobile FCP gate
  (FCP 3.2-4.7 s even on the 589 KB /license page). Tailwind documents it as not for production, and it is a
  third-party single point of failure (no SRI, no fallback).
- /about, /portofolio, /works use `?plugins=forms,container-queries`, so the payload is larger still.
- No page references `build/assets`: no Vite-built CSS on the public pages.
- Fix (expected: mobile FCP -1.5 to -2.5 s, LCP follows): compile Tailwind at build time through the Vite pipeline
  (`@vite(['resources/css/app.css'])`), purge unused classes (typically 10-30 KB CSS), and move the inline
  `tailwind.config` into the build config.

#### [High] Render-blocking Google Fonts CSS, 2-4 stylesheets per page, over-requested axes
- 2-3 separate `fonts.googleapis.com` stylesheets per page: Montserrat 400-900, Inter 300-700, JetBrains Mono (about),
  and Material Symbols Outlined with full variable axes (`wght,FILL@100..700,0..1`). /works adds a second
  Material Symbols link with opsz/GRAD axes. Each is a render-blocking request to a second origin plus gstatic font fetches.
- Material Symbols (full variable icon font) is used for about 30 icons on home.
- Fix: self-host latin-subset woff2, preload the 1-2 above-the-fold weights, trim to the weights actually used,
  merge duplicate Material Symbols links, and replace the icon font with inline SVG (or `icon_names=` subsetting).
  Keep `display=swap` and add a fallback with `size-adjust`.

#### [Medium] unpkg.com/lucide@latest (home, /license): unpinned, redirecting, blocking
- Synchronous script; `@latest` answers 302 to `/lucide@1.64.0/dist/umd/lucide.min.js` with `max-age=60`, so new
  visits pay an extra third-party round trip, and the version can change without notice (breakage / supply-chain
  risk). The UMD bundle ships every icon.
- Fix: install `lucide` via npm and import only the icons used (tree-shaken in Vite). At minimum pin an exact
  version, add `defer` and SRI. Also unify icon systems (Material Symbols and Lucide are both loaded on home/license).

#### [Medium] Slow, inconsistent server response (TTFB); no HTML caching
- Lab TTFB: 164-688 ms typical, but 1.26-1.40 s on /portofolio (both profiles) and 1.25 s on /license desktop.
  Today's curl samples (including DNS/TLS) ranged from 0.77 s to 14 s on a cold home request and 6 s on /portofolio,
  which points at slow cold origin / PHP boot rather than the network. TTFB above 0.8 s eats the LCP budget.
- Headers on every page: `Cache-Control: no-cache, private`, `cf-cache-status: DYNAMIC`, plus Set-Cookie
  (laravel-session, XSRF-TOKEN) on anonymous GETs, which prevents edge caching of HTML.
- Fix: `php artisan optimize` (config/route/view/event caches), OPcache, cache settings/portfolio queries; avoid
  session cookies on public GET routes and add a Cloudflare cache rule (or short `s-maxage` +
  `stale-while-revalidate`) for anonymous HTML.

#### [Low] Static asset caching
- Images: `Cache-Control: max-age=14400` (4 h), Cloudflare HIT. Raise to 30 days with fingerprinted/versioned
  filenames after conversion. Brotli on HTML and HTTP/3 are already enabled (good).

#### [Low] CLS / INP hardening
- Add width/height to footer logos, partner strips and gallery images; reserve space for modal images.
- Keep modal open/close handlers light. Removing the Tailwind runtime removes its MutationObserver cost.

### Third-party impact summary
| Origin | Where | Blocking | Impact |
|---|---|---|---|
| cdn.tailwindcss.com | all pages | yes (script) | High: runtime CSS compile, FCP/LCP gate |
| fonts.googleapis.com / gstatic | all pages | yes (CSS) | Medium-High: 2-4 requests, large icon font |
| unpkg.com/lucide@latest | /, /license | yes (script) | Medium: redirect + unpinned |
| Cloudflare email-decode | all pages | no | Negligible |

### Prioritized plan
1. Convert/resize images; add fetchpriority/preload to the LCP image; remove lazy from above-the-fold images (biggest LCP win).
2. Replace the Tailwind CDN with Vite-compiled CSS (biggest FCP win).
3. Self-host and subset fonts; swap the Material Symbols font for SVG icons; bundle or drop lucide.
4. Fix TTFB: Laravel optimize caches, no cookies on anonymous pages, Cloudflare HTML caching.
5. Re-run PSI (mobile) and monitor CrUX once traffic exists; targets at p75: LCP <=2.5 s, INP <=200 ms, CLS <=0.1.

### Not verified
PSI/Lighthouse scores and build, CrUX field data, real INP, and a lab re-run against the current 5.07 MB home hero.

---

## Images — 40/100

**What works**
- Most images have descriptive Indonesian alt text

### [High] Very large image files

Sampled 18 images = 7.1 MB. work-7.jpg 785 KB, work-5.jpg 623 KB, logo.png 336 KB, about-hero/teaser PNG 388 KB each.

**Fix:** Resize to display size (max ~1600px), convert to WebP/AVIF, logo to SVG or a <20 KB PNG.

### [Medium] No width/height attributes

0 of 47 img tags have width/height, causing layout shift (CLS).

**Fix:** Add width and height (or aspect-ratio CSS) to every img.

### [Medium] No lazy loading on homepage

All 13 homepage images load eagerly, including footer images.

**Fix:** loading="lazy" on below-the-fold images; fetchpriority="high" on the first hero image.

### [Low] Missing alt text

One image without alt on each of /portofolio, /license and /works.

**Fix:** Add alt text (or alt="" if decorative).

---

## Visual and Mobile Audit: trissan.mdhost.site - 66/100

Method: Edge (Playwright) at 375x812 (DPR 2, touch) on /, /about, /portofolio, /license, /works, plus existing desktop/laptop/tablet homepage shots. DOM metrics gathered by script. Page content treated as untrusted data.
Screenshots (output_dir/screenshots/): mobile_{home,about,portofolio,license,works}.png, mobile_works_header.png, mobile_works_modal.png, mobile_portofolio_modal.png, mobile_license_modal.png, mobile_home_contact_modal.png, mobile_home_kontak_tap.png.

### Score breakdown
| Area | Score |
|---|---|
| Above-the-fold / CTA | 12/20 |
| Mobile layout / responsiveness | 17/20 |
| Tap targets / readability | 11/20 |
| Modals / interstitial safety | 17/20 |
| Cross-page visual consistency | 9/20 |

### [High] Header "Kontak" button is invisible on /works, /portofolio, /license (mobile)
- Measured: on these 3 pages the link computes color rgb(255,255,255) on background rgba(0,0,0,0) over a 75% white header, i.e. white-on-white (about 1:1 contrast). See mobile_works_header.png: only a faint ghost at top right.
- On / and /about the same element has a navy bg (rgb(10,25,47) / rgb(13,33,55)) and renders correctly. Classes are identical (`bg-brand-navy text-white ...`), so the `brand-navy` colour utility is evidently not defined in those 3 pages' Tailwind/CSS setup.
- Impact: the only top-bar contact CTA vanishes on 3 of 5 pages (bottom nav has no Contact item), so users cannot reach contact in one tap. Hurts conversion and WCAG 1.4.3.
- Fix: define `brand-navy` (same palette/config) for those templates, or move to one shared layout; re-check contrast.

### [Medium] Inconsistent design system across pages
- /about is dark-themed; others light. Body font-size is 15px on /portofolio vs 16px elsewhere; heading fonts and card styles differ; /license has a dotted background.
- Fix: unify via a single Blade layout and Tailwind config.

### [Medium] Mobile homepage above the fold has no primary CTA except the small header "Kontak"
- At 375px the hero "LIHAT PORTOFOLIO" button exists but is hidden (parent display:none, 0x0). The first viewport shows H1, intro, a "trusted by" card (Yamaha/Yaskawa/Daikin/Freeport), carousel arrows and the first service card, whose "EXPLORE WORKS" is a small text link. Desktop and tablet (768) show the button.
- Fix: show a full-width "Lihat Portofolio" / "Hubungi Kami" button under the intro on mobile.
- Positives: H1 visible immediately, hero image loads, no layout shift seen, logo and name visible, header sticky.

### [Medium] Tap targets below 44-48px
Interactive elements under 44px at 375px: / 43 of 69, /about 17 of 31, /portofolio 26 of 41, /license 37 of 54, /works 44 of 59.
Worst (CSS px):
- Header: "Kontak" 73x32; logo link 90x29 (all pages).
- Home: carousel prev/next 26x26; "DETAIL" buttons 152x25; "PELAJARI PROFIL LENGKAP" 154x12; "EKSPLORASI SEMUA SERVICES" 197x26; contact block phones 86x14 and email 148x17; footer nav links 14px tall; footer phone/email 10px tall.
- /about: breadcrumb 49x12; facility arrows 23x23; "UNDUH COMPANY PROFILE" 199x26.
- /portofolio: filter chips 25px tall; pagination 25-26px; search input 28px; sort select 28px.
- /works: category chips 26px; carousel arrow 26x26; breadcrumb 12px.
- /license: filter chips 20px; "Show Image" buttons 20px; tabs 26px.
Fix: min-h-11/min-w-11 on chips, arrows, pagination, text links; more spacing in footer nav.
Bottom nav (47px, 5 equal items, clear active state) is good.

### [Low] Text size and content polish
- Base font 16px (15px on /portofolio). Many micro-labels are 10-12px uppercase/monospace (about stat labels, "DATA CONTOH" tags, footer links); grey second paragraph on /about over navy is low contrast (verify).
- /license shows placeholder copy to visitors: "DATA CONTOH", "Ganti melalui CMS", "Data pada halaman ini adalah contoh dan perlu diganti dengan dokumen resmi." Looks unfinished; undermines trust and E-E-A-T.

### No horizontal scroll (pass)
scrollWidth = 375 on all 5 pages; viewport meta correct (width=device-width, initial-scale=1.0). Carousels and chip rows scroll inside their own containers by design. Fixed UI: sticky header 41px + bottom nav 47px (about 11% of viewport), acceptable.

### Pop-ups and modals: no intrusive interstitials (pass)
All modals open only on user tap. Zero visible dialogs on load on every page; nothing fired on a timer or scroll during a 2.5s observation. Google's intrusive-interstitial rule is not triggered.
| Page | Modal | Trigger | Close | Notes |
|---|---|---|---|---|
| / | #contact-modal (role=dialog, aria-modal) | "CONTACT FORM" button | X 26x26, Esc (verified), scroll locked | Header "Kontak" does NOT open it; it is an anchor to /#contact (scrolls). Submit 315x32. |
| /works | #projectDetailModal (role=dialog) | "Detail" | X 26x26, "Tutup Detail" 321x26, Esc (verified) | Fits viewport, scroll locked. "SPESIFIKASI UTAMA" and "SERTIFIKASI & STANDAR MUTU" headings render with empty content for the tested item. Closed state is opacity-0 + pointer-events-none (does not block taps). |
| /portofolio | #deliverableModal | project card | X 26x26, Esc (verified) | Works; no role/aria-modal. |
| /license | #verificationModal | "Show Image" | X 17x21, "Tutup" 317x29, Esc (verified) | Body scroll NOT locked; no role/aria-modal. |
Issues (Low-Medium): close buttons 17-26px; missing dialog semantics on /portofolio and /license; focus trapping not verified.

### Desktop / tablet (pass)
- 1920 and 1366: nav, H1, "LIHAT PORTOFOLIO" CTA all above the fold; hero card and service cards aligned in max-width container; carousel arrows sit at the container edge and slightly overlap the first/last card.
- 768: header "Kontak" visible, hero CTA visible, bottom nav shown; service carousel clips the third card by design.

### Prioritised fixes
1. (High) Make header Kontak visible on /works, /portofolio, /license.
2. (Medium) Visible hero CTA on mobile home.
3. (Medium) Tap targets to 44px+ for chips, pagination, arrows, footer links, modal close buttons.
4. (Medium) Replace "DATA CONTOH / Ganti melalui CMS" placeholders on /license.
5. (Low) Lock scroll and add ARIA dialog semantics on /license and /portofolio modals; fill or hide empty spec sections in /works modal.
6. (Low) Unify layout, fonts, base size across templates.

audit-data.json Visual: score 66; popups_intrusive=false; findings = [High kontak-invisible on 3 pages; Medium no mobile hero CTA; Medium tap targets; Medium inconsistent templates; Low placeholder copy; Low modal a11y].

---

## AI Search Readiness (GEO): 45/100

Pages audited: /, /about, /portofolio, /license, /works (raw SSR HTML, is_spa=false). Checked 2026-10-29.

| Dimension | Weight | Score | Notes |
|---|---|---|---|
| Citability | 25% | 40 | Facts exist (named clients, 3 offices, 145 staff) but every passage is 1-2 sentences; no answer blocks, no question headings |
| Structural Readability | 20% | 55 | Clean H1-H3 hierarchy, but all-caps H1s, empty H2/H3 nodes on /works and /portofolio, EN/ID mixed headings |
| Multi-Modal | 15% | 50 | 13 images on / with alt text, workshop photos, org chart, company-profile PDF; no video, no captioned project galleries |
| Authority & Brand | 20% | 20 | Placeholder certificates and template staff (see Critical), no named people, no sameAs, entity split with trissan.co.id |
| Technical Accessibility | 20% | 60 | SSR, search/user bots return 200; sitemap 500, no llms.txt, debug mode on |

**What works**
- Server-rendered HTML; all content (including modals) is in the initial response, so AI fetchers need no JS.
- Search and user-triggered AI bots are allowed (robots `User-agent: *` Allow) and got HTTP 200 in UA tests: OAI-SearchBot, ChatGPT-User, Claude-SearchBot, Claude-User, PerplexityBot, Perplexity-User, Googlebot, bingbot.
- Verifiable client names (Astra Honda Motor Plant 3 Cibitung, Mayora Indah, Indofood CBP Purwakarta, Nihon Plast, Mikuni, Yaskawa) are strong, citable entity co-occurrences.
- Organization + WebSite JSON-LD on every page, BreadcrumbList on inner pages.

### AI crawler access (live Cloudflare managed robots.txt)

| Crawler | Governs | robots.txt | UA test |
|---|---|---|---|
| OAI-SearchBot | ChatGPT Search citations | Allowed (*) | 200 |
| ChatGPT-User | User-triggered ChatGPT fetches | Allowed (*) | 200 |
| Claude-SearchBot | Claude search citations | Allowed (*) | 200 |
| Claude-User | User-triggered Claude fetches | Allowed (*) | 200 |
| PerplexityBot / Perplexity-User | Perplexity index / live fetch | Allowed (*) | 200 / 200 |
| Googlebot | Google Search + AI Overviews/AI Mode | Allowed (*) | 200 |
| bingbot | Bing + Copilot grounding (and ChatGPT via Bing) | Allowed (*) | 200 |
| Applebot | Siri/Spotlight/Safari | Allowed (*) | n/t |
| GPTBot | OpenAI model training only | Disallowed | 403 at edge |
| ClaudeBot | Anthropic model training only | Disallowed | 403 at edge |
| Google-Extended | Gemini/Vertex training + Gemini grounding (not Search/AIO) | Disallowed | n/t |
| Applebot-Extended | Apple Intelligence training only | Disallowed | n/t |
| CCBot, cohere-ai, meta-externalagent, MistralAI-Training, Bytespider, Amazonbot | Training / datasets | Disallowed | n/t |
| GoogleOther, Google-CloudVertexBot | Google R&D crawl; Vertex AI agent crawls | Disallowed | n/t |

Content-Signal: `search=yes, ai-train=no, use=reference`. `ai-input` (RAG/grounding) is not declared; `use=reference` is not a Cloudflare-defined key and will likely be ignored. UA tests used spoofed UAs; verified-bot handling may differ.

### [Critical] /license publishes placeholder certifications as fact

/license states "Data pada halaman ini adalah contoh dan perlu diganti" yet lists ISO 9001 (LRQA), AS9100D/Nadcap, ASME Stamp U, ISO 14001/45001 (SGS) and SMK3 Emas with fake IDs (CERT-ISO-9001-2024, ASME-STAMP-U-4412). Two show expired dates (ISO 45001 10 Feb 2026, SMK3 14 Jan 2026). Product certs (turbine shaft Nadcap, IEC 61439-2 arc test) do not match the business. AI systems extract these as claims about PT. Trissan; if a buyer verifies them, trust collapses, and the page signals fabricated E-E-A-T.

**Fix:** Remove all sample rows today (or noindex /license until real). Publish only certificates the company actually holds (e.g. SIUJK/SBU, K3 Listrik, ISO if held) with real issuer, number, validity and scan.

### [Critical] /portofolio team directory is template filler

"Matriks Direktori 5 Personil Terdaftar" lists unnamed roles with aerospace/subsea specialties (Inconel blisks, subsea valve shafts, AS9100 FAI, KUKA/ABB robotic cells) and IDs like ENG-CNC-001. This conflicts with the real business (conveyors, hydrants, chillers, panels) and with "145 karyawan".

**Fix:** Replace with real named engineers/managers, real certifications (K3, BNSP), years of experience and project links; otherwise remove the section.

### [High] Entity split: trissan.co.id is live with the same content

https://trissan.co.id/ returns 200 (own robots.txt + sitemaps, Nov 2023 project posts) while the new site lives on trissan.mdhost.site and its Organization `url` points to mdhost.site; the email is marketing@trissan.co.id. Engines and LLMs see two sites for one company, with the older domain holding history.

**Fix:** Launch the new site on trissan.co.id (or www) and 301 every legacy URL; set Organization `url`/`@id` to the official domain. If mdhost.site is staging, add noindex until cutover.

### [High] No citable answer passages; projects are one sentence

Service blurbs are 5-10 words; each project is one sentence with no scope, dates, specs or outcome. AI answers to "kontraktor konveyor Bekasi" or "instalasi fire hydrant pabrik" have nothing extractable to quote.

**Fix:** Per service and per top project, add a 130-170 word self-contained block that answers directly in the first 1-2 sentences (what, where, for whom, specs, duration, result). Use question H2s in Indonesian ("Berapa lama pemasangan konveyor?", "Area layanan PT. Trissan di mana?").

### [High] Weak off-site brand footprint

Quick checks: no Wikipedia (id/en) or Wikidata entity; Bing results for "PT Trissan" Bekasi returned no brand pages; Reddit not reachable (blocked); footer Facebook/Instagram links point to generic homepages; no LinkedIn, YouTube or Google Business Profile linked. YouTube and Reddit mentions correlate most with AI citations.

**Fix:** Create/claim LinkedIn company page, Google Business Profile (Bekasi, Malang, Timika), YouTube channel with short project videos (conveyor commissioning, hydrant test); link them and add to `sameAs`. Get listed in Indonesian B2B directories (Indonetwork, Indotrading) and client/vendor pages.

### [Medium] Sitemap 500 + debug mode limit AI discovery

/sitemap.xml returns 500 with the Laravel debug page (see technical.md). Bing (feeds Copilot and ChatGPT Search) relies heavily on sitemaps/IndexNow for a new domain.

**Fix:** Fix sitemap, set APP_DEBUG=false, submit to GSC and Bing Webmaster Tools, enable IndexNow.

### [Medium] Training opt-out is fine, but declare grounding intent

Blocking GPTBot/ClaudeBot/CCBot only affects model training, not ChatGPT/Claude/Perplexity search citations (those bots are allowed). Blocking Google-Extended does remove content from Gemini app/Vertex grounding, though not from AI Overviews.

**Fix:** Keep training blocks if desired. Add `ai-input=yes` to Content-Signal. Consider unblocking Google-Extended if Gemini answers matter; optionally allow GPTBot/ClaudeBot for long-term model awareness of a low-profile brand.

### [Low] Structural noise

All-caps H1s ("DAFTAR PROYEK & HASIL PENGERJAAN (WORKS)"), empty H2/H3 elements, and filter empty-state text ("Tidak ada proyek yang sesuai") are in the extracted text of /works and /portofolio.

**Fix:** Sentence-case headings, remove empty headings, hide empty-state copy with `hidden` until needed.

### [Low] No llms.txt / RSL

/llms.txt, /llms-full.txt, /.well-known/llms.txt and RSL license.xml all 404. Not used by Google; low impact elsewhere.

**Fix:** Optional: add a short /llms.txt (company summary, services, offices, key URLs) after the official domain is settled.

### Platform scores

| Platform | Score | Driver |
|---|---|---|
| Google AI Overviews / AI Mode | 40 | Googlebot allowed, SSR; held back by sitemap 500, duplicate domain, thin passages |
| ChatGPT Search | 35 | OAI-SearchBot allowed; depends on Bing index where brand is barely visible |
| Perplexity | 40 | PerplexityBot allowed; favours specific passages and third-party mentions, both lacking |
| Bing Copilot | 35 | bingbot allowed; no sitemap, no Bing Webmaster/IndexNow, weak entity signals |
| Gemini app | 20 | Google-Extended blocked removes grounding use |

### Top 5 changes (impact / effort)

1. Remove placeholder certificates and template staff; publish real ones: Critical / 2-4 h
2. Consolidate onto trissan.co.id with 301s and updated Organization url: High / 1-2 days
3. Add 130-170 word answer blocks + question H2s per service and top 6 projects: High / 2-3 days
4. Fix sitemap, disable debug, submit to GSC + Bing, enable IndexNow: High / 1-2 h
5. Build entity footprint (GBP x3, LinkedIn, YouTube project clips, real sameAs, B2B directories): High / 1-2 weeks

---

## Agent Readiness: https://trissan.mdhost.site/ (checked 2026-10-29)

Status: complete except Lighthouse Agentic Browsing (unavailable). Fetched content treated as untrusted data.

### Scores
- Lighthouse Agentic Browsing: unavailable (X/N not obtainable). PSI mobile and desktop both returned HTTP 429 (daily quota; no Google API key configured). Fix: `/seo google setup`, then re-run `lighthouse_agentic.py --strategy both`. Not estimated.
- Agent-UX heuristic: 80/100, status complete (separate from Lighthouse). 1248 a11y nodes, 52 interactive (47 links, 5 buttons), 0 unnamed interactive, 11 landmarks. Only deduction: 18 div onclick widgets (-20).
- AI Search Readiness (category judgment, 0-100): 72. Strong crawlability and server rendering; weak on agent-operable navigation and a broken sitemap.

### Access policy (robots.txt is Cloudflare Managed Content, 32 groups)
- Training: BLOCKED. Disallow: / for GPTBot, ClaudeBot, anthropic-ai, Claude-Web, CCBot, Google-Extended, Applebot-Extended, Amazonbot, Bytespider, Diffbot, GoogleOther, meta-externalagent, cohere-ai and others. Consistent with `ai-train=no`.
- Search: ALLOWED. `User-agent: *` Allow: / with `Content-Signal: search=yes,ai-train=no,use=reference`. OAI-SearchBot, Claude-SearchBot, Googlebot and Bingbot fall under * (not blocked).
- User-triggered: ALLOWED by robots.txt (ChatGPT-User, Claude-User, Perplexity-User, Google-Agent are not named, so they fall under *). Vendor behaviour: Claude-User honours robots.txt; ChatGPT-User may not; Perplexity-User and Google-Agent generally ignore it.
- Content-Signal is a draft/preference, not enforcement (vendor-matrix facts checked 2026-09-23; Google does not act on it; IETF draft expired 2026-04-04).

### Findings

#### P0
None. Primary content is present without JS (1111 words, no JS shell marker). robots.txt reachable (200). Real 404 status (no soft-404). /, /about, /portofolio, /license, /works all return 200.

#### P1
1. Broken sitemap: `/sitemap.xml` returns HTTP 500 and robots.txt declares no `Sitemap:` line. Crawlers and agents lose page discovery. Fix: repair the sitemap route or generator and add `Sitemap: https://trissan.mdhost.site/sitemap.xml` to robots.txt. (Overlaps the technical-SEO audit.)
2. Navigation cards built as `<div onclick="window.location.href='/works?category=...'">` (18 on the homepage: mechanical-electrical, digital-automation, jigs-fixtures, material-handling, distribution-panel and others). No link role, no href, no keyboard focus. Agents reading the accessibility tree see `generic` nodes and cannot reliably follow them; crawlers cannot discover the `/works?category=` URLs from the homepage. Fix: wrap each card in `<a href="...">`. This is the only deduction in the Agent-UX score.
3. Content-Signal is not repeated inside the named groups (GPTBot, ClaudeBot, Google-Extended, Applebot-Extended, CCBot). A crawler that matches a named group reads only that group, so the signal may not apply. Low impact because those groups already Disallow: /. The file is Cloudflare-managed, so any change is a Cloudflare setting, not site source.

#### P2
4. Contact form (`POST /contact`, Laravel CSRF `_token`) is accessible: every field has `<label for>` (name, email, phone, service_id select, message textarea; required set). Concerns: (a) `GET /contact` returns 405, so no standalone contact URL for an agent to land on or link; (b) honeypot field `website` has a visible label and may be filled by agents that populate every field; (c) CSRF requires the page to be rendered first (acceptable). Fix: put the honeypot in an `aria-hidden="true"` wrapper; consider a GET /contact page or redirect to the form anchor.
5. Modals: markup uses `role="dialog"` and `aria-modal="true"` (good). Not verified at runtime in this run: focus moves into the dialog, Escape closes it, and the trigger is a real `<button>`. Hidden modal content is reachable only after the trigger click.
6. Cloudflare treatment of agent traffic (single GET to `/` with a spoofed UA via curl): `Mozilla/5.0` 200; `ClaudeBot/1.0` 403; `GPTBot/1.1` 403; `ChatGPT-User/1.0` 200; `Claude-User` 200. Cloudflare blocks AI training bots at the edge and lets user-triggered fetchers through, which matches the declared policy. Spoofed-UA curl does not prove how real signed agents (Web Bot Auth, draft) or agent browsers are treated; check Cloudflare Security > Bots that verified-agent traffic is not challenged on /contact. Server header: cloudflare; cookies set on every response (XSRF-TOKEN, laravel-session), `Cache-Control: no-cache, private`.

#### P3 / opportunities (absence is not a defect; no ranking, citation or traffic effect promised)
7. llms.txt: 404 (llms-full.txt 404). Lighthouse drops the audit from the fraction when absent; publishing a valid one (H1, `>` summary, Markdown links to /about, /portofolio, /works, /license) would add one counted pass. Community spec; Google Search ignores it.
8. Markdown delivery: none (Accept: text/markdown returns HTML; /index.md 404; no rel=alternate). `Vary: Accept-Encoding` only. Optional.
9. WebMCP: none. 0 registerTool call sites, no navigator.modelContext, 1 form (/contact) without `toolname`/`tooldescription`. W3C Community Group draft, Chrome origin trial M149-M156 (checked 2026-09-23). The form sends enquiries, so annotate only if safe for agents to submit.
10. ai-catalog.json (ARD 1.0 draft), api-catalog, OAuth metadata, agent-card.json, UCP: all 404 and not applicable (no agent services to list).
11. Web Bot Auth: not configured; IETF draft (httpsig-protocol-00, 2026-09-01); relevant only if you want to verify agents.

### What passed
- Server-rendered content, 53 real anchors, 11 landmarks, JSON-LD present, canonical and meta description present.
- Real 404 status; deliberate AI-bot groups in robots.txt; no search crawlers blocked at root.

### Method notes
- Ran lighthouse_agentic.py (429), agentic_check.py, agent_ux_check.py. Playwright/msedge not used directly.
- The five UA probes in finding 6 were single GETs outside the skill's `--ua-matrix` flag, which was not authorized for this site. Recorded for transparency; no further UA testing was done.

### Structured findings for audit-data.json (AI Search Readiness)
```json
[
 {"title":"Sitemap returns HTTP 500 and is not declared in robots.txt","severity":"High","description":"/sitemap.xml returns 500; robots.txt has no Sitemap line.","recommendation":"Fix sitemap generation and add a Sitemap: directive."},
 {"title":"Category cards are div onclick, not links","severity":"Medium","description":"18 div onclick navigation widgets on the homepage appear as generic nodes in the accessibility tree and are not crawlable or keyboard operable.","recommendation":"Replace with <a href> elements."},
 {"title":"Contact form: no GET page, labelled honeypot, modal behaviour unverified","severity":"Medium","description":"POST-only /contact (GET 405); honeypot field is labelled; dialog focus and Escape not verified.","recommendation":"Hide honeypot from the accessibility tree; verify modal focus and Escape; add a contact landing page."},
 {"title":"Content-Signal not repeated in named bot groups","severity":"Low","description":"Signal exists only in the User-agent: * group; named groups already Disallow.","recommendation":"Adjust the Cloudflare managed robots setting if desired."},
 {"title":"No llms.txt, Markdown delivery or WebMCP","severity":"Info","description":"Optional agent features absent; llms.txt would add one counted Lighthouse audit.","recommendation":"Optional: publish llms.txt; consider WebMCP only for safe forms."},
 {"title":"Lighthouse Agentic Browsing score unavailable","severity":"Info","description":"PSI HTTP 429 quota exceeded on mobile and desktop.","recommendation":"Configure a Google API key and re-run."}
]
```

---

## Search Experience (SXO) — SXO Gap Score 42/100

Separate from the SEO Health Score. Target: https://trissan.mdhost.site/ (rendered with Playwright, 916 words, H1 "We Provide For User experience").
Category pages (`/works?category=material-handling` etc.) canonicalise to `/works` and share its title, so **the homepage is the only indexable page for all 12 services**.

**What works**
- Named industrial clients with project photos: PT Astra Honda Motor Plant 3 Cibitung, PT Mayora Indah Tbk, PT Indofood CBP Purwakarta, PT Nihon Plast
- Contact paths are in place: 2 `tel:` links, `mailto:`, POST form, contact modal, embedded Google Map, full Bekasi address
- Title and meta description use Indonesian service terms (kelistrikan, mekanikal, otomasi, material handling)

### SERP evidence (5 query clusters, about 45 results reviewed)

| Query | What ranks | Dominant type (taxonomy) | Consensus |
|---|---|---|---|
| kontraktor mekanikal elektrikal Bekasi | JobStreet / loker.id job listings (10/10) | none (job-seeker intent) | ambiguous |
| kontraktor MEP industri Bekasi Cikarang | "Jasa MEP Bekasi" (KGT), metalis-mep.com, manatek.co.id, buanaproject "Jasa MEP Bekasi 2026", kontraktorhub | Service Page (city + service) | 7/9, ~78% |
| jasa (fabrikasi/pembuatan) konveyor (Bekasi/Cikarang) | mitrautamateknik.com has 6 "[service] [city]" URLs, gedeprimapresisi "Fabrikasi di Cikarang", plus FB page | Service Page (city + service) | 8/9, ~89% (unlocalised query: academic PDFs) |
| instalasi fire hydrant pabrik / kontraktor fire hydrant Bekasi | Journals, ITS thesis, Bina Marga SNI doc; Bekasi variant: spam pages and Indotrading | Blog/informational (unlocalised); no clear vendor (localised) | ~80% informational |
| jasa pembuatan panel PLC HMI Bekasi/Cikarang | Job listings, theses, one distributor page | none; weak competition | gap |
| PT Trissan Bekasi (brand) | EMIS profile only (founded 2005, lists **trissan.co.id**); target site absent | n/a | n/a |

Signals seen: buyers say **"MEP"** and **"Jasa [service] [city]"**, and the city set is Bekasi + Cikarang + Karawang + industrial estates (Jababeka, MM2100, EJIP). Competitor pages are around 900-1,000 words **per service** and include conveyor types, a price table (Rp 8.5-18 jt), a 5-step order process, testimonials, a 4-question FAQ and a WhatsApp CTA. Fire-hydrant SERPs reward SNI 03-1745 / NFPA 14/20 compliance content.

### [Critical] Page-type mismatch: one multi-service homepage vs a SERP of single-service city pages

The SERP for every commercial query is made up of Service Pages built as "Jasa [layanan] [kota]". Trissan has a Landing/hub homepage with 12 one-line service cards (for example "Konveyor dan sistem logistik internal pabrik."). The cards link to filtered `/works` URLs that canonicalise away. As a result the site has no URL Google can match to "jasa pembuatan konveyor Bekasi", "panel PLC HMI Bekasi" or "instalasi fire hydrant pabrik". Severity by query: conveyor **CRITICAL**, panel PLC/HMI **CRITICAL** (low competition, so easy to win), fire hydrant **CRITICAL** (not even one of the 12 services, only 2 project mentions), kontraktor MEP Bekasi **HIGH** (homepage could rank but never says "kontraktor", "MEP", "Cikarang" or "Karawang": 0 occurrences each).
**Fix:** Create indexable `/layanan/{slug}` Service Pages, starting with conveyor/material handling, panel PLC/HMI, fire hydrant, and ME maintenance. Each page needs a self-canonical, the H1 "Jasa [Layanan] di Bekasi & Cikarang", about 800+ words, process, types/specs, related projects from /works, FAQ, and a CTA. Run `/seo page` per new page.

### [High] H1 and hero do not answer "who, what, where"

The H1 "We Provide For User experience" is English, generic, and grammatically broken. Within 10 seconds a plant buyer cannot tell that this is a Bekasi ME/automation contractor.
**Fix:** Use the H1 "Kontraktor Mekanikal, Elektrikal & Otomasi Industri di Bekasi". Add a subline: "Konveyor, panel PLC/HMI, fire hydrant & perawatan mesin untuk pabrik di Bekasi, Cikarang, Karawang". Put two CTAs above the fold: "Minta Penawaran" and "Chat WhatsApp Engineer".

### [High] Trust layer undermined by placeholder certifications

`/license` (linked from main nav) shows "Data pada halaman ini adalah contoh". It lists sample ISO 9001, **AS9100D aerospace** and ASME cards, each marked "(data contoh) / Ganti melalui CMS". Footer social links go to bare facebook.com / instagram.com. For procurement, fake-looking certificates rule a vendor out.
**Fix:** Replace the content with real documents (NIB, SBU/LPJK, ISO if held, K3/SMK3, Ahli K3 Listrik), or hide the page until they exist. Fix the social URLs. Run `/seo content` for E-E-A-T.

### [High] No WhatsApp or low-friction quote path

Every competitor in the SERP leads with WhatsApp. Trissan has 0 `wa.me` links, and no "survei lokasi", response time, or company-profile download.
**Fix:** Add a sticky WhatsApp button with a prefilled message for each service ("Halo Trissan, saya butuh penawaran [layanan] untuk pabrik di ..."). Add "Unduh Company Profile (PDF)" and "Jadwalkan Survei Lokasi".

### [Medium] Missing decision content buyers compare on

The page has 0 mentions of harga/estimasi, garansi, SNI/NFPA, PLC brands (Siemens/Omron/Mitsubishi), years in business (EMIS says 2005), or FAQ. Projects have no dates, scope, capacity, or duration.
**Fix:** On each service page add a "Faktor Biaya" section (no fixed prices needed), list the brands and standards handled, give project facts (client, lokasi, kapasitas, durasi, tahun), and add a 4-6 question FAQ.

### [Medium] Schema does not describe services or branches

The page has only Organization + WebSite markup, with the address as one string, no LocalBusiness, no Service, and no Malang/Timika locations.
**Fix:** Add `LocalBusiness` (or `GeneralContractor`) with structured PostalAddress and geo, `department` entries for the branches, and `Service` + `areaServed` on each new service page. Use `/seo schema` to generate it. Use `/seo local` for the GBP.

### [Low] Freshness signals absent

There are no project dates or "updated" stamps. htmldate guessed 2026-01-01.
**Fix:** Show the project year on each /works card, and add dateModified to the service pages.

### User stories (from SERP signals)

1. **Awareness, Plant Engineering Manager.** As a plant engineering manager in Cikarang, I want to find a vendor who designs and fabricates a custom conveyor because our line is expanding, but I'm blocked by **an information gap**: Trissan has one sentence on conveyors and no types or specs. *(Source: mitrautamateknik "Jenis Conveyor" + "Proses Pemesanan" sections; 6 "conveyor [city]" URLs ranking)*
2. **Consideration, Procurement Officer.** As a procurement officer, I want to check vendor legality and certification before an RFQ because audit requires it, but I'm blocked by **a trust gap**: the certificates are visibly placeholders. *(Source: SERP summaries telling buyers to "minta referensi proyek, sertifikasi, penawaran terperinci"; KGT "250+ proyek, 15+ tahun" claims)*
3. **Consideration, HSE/Facility Manager.** As an HSE manager, I want a hydrant installer who builds to SNI 03-1745 / NFPA 14 because a fire inspection is due, but I'm blocked by **technical confusion**: Trissan does not mention standards. *(Source: hydrant SERP is 80% journals and Bina Marga SNI documents)*
4. **Decision, Maintenance Supervisor.** As a maintenance supervisor with a conveyor down, I want to reach a technician today, but I'm blocked by **time pressure**: there is no WhatsApp and no stated response time. *(Source: competitor WA CTAs; "biaya maintenance fire hydrant system cikarang" query pattern)*
5. **Decision, Plant Engineering Manager (automation).** As an automation lead, I want a panel PLC/HMI builder near Bekasi, but I'm blocked by **an information gap**: the SERP has only job ads and theses, and Trissan offers one line. *(Source: panel PLC SERP with 0 vendor pages, a winnable gap)*

### Persona scores (homepage)

| Persona | Relevance | Clarity | Trust | Action | Total | Rating |
|---|---|---|---|---|---|---|
| HSE / Facility Manager (fire hydrant) | 7 | 8 | 8 | 12 | 35 | Critical Mismatch |
| Procurement Officer | 10 | 10 | 6 | 12 | 38 | Critical Mismatch |
| Maintenance Supervisor (urgent) | 12 | 12 | 13 | 11 | 48 | Needs Work |
| Plant Engineering Manager (conveyor/PLC) | 14 | 13 | 14 | 14 | 55 | Needs Work |

Weighted average (commercial personas weighted higher): **about 44/100**.
- **HSE (35):** Fire hydrant is not one of the 12 services and has no standards content. Fix: add the page `/layanan/instalasi-fire-hydrant` with an "Standar SNI/NFPA yang Kami Ikuti" section, the Mayora project facts, a pump/pillar/GWT scope list, and the CTA "Minta Survei Hydrant".
- **Procurement (38):** Placeholder certificates, no NIB/SBU, no company-profile PDF. Fix: add a "Legalitas & Sertifikasi" strip on the homepage (real docs only), the CTA "Unduh Company Profile", and "Berdiri sejak 2005".
- **Maintenance (48):** No WhatsApp, SLA, or emergency path. Fix: add a sticky WA button and the line "Respons tim teknisi < 24 jam area Bekasi-Cikarang-Karawang".
- **Engineering (55):** Proof is strong (AHM, Indofood) but there are no specs. Fix: give each service page a specs table (kapasitas, material, brand PLC) and link related projects.
- **Systemic issues:** Relevance and Clarity are lowest across all personas because no service-level URL exists and the H1 says nothing. Trust is hurt site-wide by the /license placeholders.

### SXO Gap Score breakdown

| Dimension | Score | Evidence |
|---|---|---|
| Page Type | 5/15 | Hub homepage vs single-service city pages in SERP; category URLs canonicalised away |
| Content Depth | 5/15 | 916 words for 12 services (about 1 line each) vs ~900-1,000 words per service at competitors |
| UX Signals | 9/15 | Tel, mail, form, modal and map present; broken English H1; no WhatsApp; no FAQ |
| Schema | 5/15 | Organization + WebSite only; no LocalBusiness/Service/branches |
| Media | 8/15 | 13 images, real project cards; hero images look like stock ("Robot industri..."); no video or diagrams |
| Authority | 6/15 | Strong client names, offset by placeholder certificates, dead social links, no years or SBU |
| Freshness | 4/10 | No project dates; no visible update stamp |
| **Total** | **42/100** | |

### Limitations

- WebSearch is not a live Google SERP. It gave no PAA, ads, local pack, AI Overview, or positions, so page-type consensus comes from result URLs and titles. Validate with real Google (id-ID, Bekasi location) or the DataForSEO extension.
- No search volumes, so persona weights are estimates. No GSC/GA4 data, so real queries, CTR, and on-site behaviour are not assessed.
- The site runs on a staging-like domain (mdhost.site), while EMIS lists trissan.co.id. Check which domain is meant to rank.
- Only 2 competitor pages were fetched in depth. The /works detail and /about pages were not persona-scored.

Generate a PDF report? Use `/seo google report`.

---

## Local SEO — 28/100

Audited: https://trissan.mdhost.site/ (hybrid / service-area B2B contractor, industrial mechanical, electrical and automation). DataForSEO unavailable; no Google Maps, GBP, or live local-pack data could be read. Fetched content treated as untrusted data.

| Dimension | Weight | Score | Notes |
|---|---|---|---|
| GBP Signals | 25% | 8/25 | Maps link only; no embed, no place ID, no GBP confirmation |
| Reviews & Reputation | 20% | 0/20 | No reviews, ratings, testimonials or aggregateRating |
| Local On-Page SEO | 20% | 8/20 | Address, phones, "Bekasi/Malang/Timika" present; no city/service pages, no hours |
| NAP & Citations | 15% | 7/15 | On-site NAP consistent; citations unverifiable; staging domain differs from legacy domain |
| Local Schema | 10% | 4/10 | Organization only, no LocalBusiness subtype, geo, hours |
| Local Links & Authority | 10% | 1/10 | Social links are generic homepages; no sameAs; no local link evidence |
| **Total** | | **28/100** | |

### Business type and vertical
- Type: hybrid. Visible street address (HQ Bekasi) plus national service area (3 offices). Operates B2B at client sites, so most "near me" demand is low. Branded and service-plus-region queries matter more than the map pack.
- Vertical: no standard match. Closest fit is Home Services/Contractor signals (installation, maintenance, licenses), but it is industrial B2B (fire hydrant, chiller, conveyor, PLC/HMI panels, jigs and fixtures, robots). Best GBP primary category candidates: "Industrial equipment supplier" or "Electrical installation service" or "Mechanical contractor" (verify the category list in GBP; the primary category is the top ranking factor, so choose by testing).
- Proximity (about 55% of local ranking variance) is outside our control. Bekasi Kabupaten (Tambun Selatan) is the only proximity anchor.

### NAP audit
| Source | Name | Address | Phone |
|---|---|---|---|
| Visible HTML (contact section) | PT. Trissan | Ruko Metland Tambun Blok A2 No.24, Tambun Selatan, Bekasi, Jawa Barat 17510, Indonesia | (021) 88373107 / (021) 88321576 |
| JSON-LD Organization | PT. Trissan | same (single streetAddress string) | +622188373107 (primary), both in ContactPoint |
| tel: links | n/a | n/a | +622188373107, +622188321576 |
| Maps link query | n/a | "Ruko Metland Tambun Blok A2 No.24 Tambun Selatan Bekasi" | n/a |
| Legacy site trissan.co.id (live) | PT. TRISSAN | "Blok A2 No.24. Tambun Selatan, Bekasi" (period after 24) | (+6221) 88373107 / (+6221) 88321576 |

Result: consistent on content, with formatting variance only. Gaps:
- [Medium] Schema `streetAddress` holds the whole address; `addressLocality`, `addressRegion`, `postalCode` are missing.
- [Medium] Name variants: "PT. Trissan" vs "PT. TRISSAN" vs the Maps embed title "PT. Trissan - Kantor Pusat". Pick one and match the GBP name exactly.
- [Medium] Phone shown as "(021) 88373107" on-page and "(+6221) …" on the legacy site. Use one format everywhere (+62 21 8837 3107).
- [High] Two domains: the legacy production site trissan.co.id (email marketing@trissan.co.id) and this staging-style domain trissan.mdhost.site. Business email and the canonical identity sit on a different domain than the audited site. If mdhost.site is launched as-is, the NAP's web entity splits. Move the final site to trissan.co.id (or confirm the intended canonical) before building citations.
- Branch addresses and phones for Malang and Timika appear nowhere (not on-page, not in schema), so branch NAP cannot be audited or cited.

### GBP signals
Detected: link to maps.google.com/?q=... (text search, not a place link); "Kantor Pusat" label; headquarters section with the address; two tel: links.
Missing or unverified:
- [Critical] No evidence a Google Business Profile exists. Bing searches for the company name and address returned no company profile, Maps card or directory listing in the top results (weak evidence, not proof; Google Maps could not be queried). Owner must check and claim or verify.
- [High] No Maps iframe embed, no `hasMap`/place URL (`https://maps.google.com/?cid=...`), no "write a review" link, no review widget.
- [High] No opening hours anywhere on the site (visible or schema).
- [Medium] No GBP posts, photos or Q&A evidence. Project photos exist in /works and /portofolio but are not tied to a GBP.
- [Medium] Malang and Timika offices need their own GBPs only if they are staffed, signed, and serve visitors (Google rules). Otherwise list them as service areas of the HQ profile or as a department on-site.
- Recommended GBP setup: name exactly "PT. Trissan", primary category chosen as above, secondary categories for the other services, service areas (Bekasi, Jakarta, Karawang, Malang, Papua/Timika), website pointing to the final domain, phone +62 21 8837 3107, attributes, 10+ project photos, and a UTM-tagged site URL.

### Reviews
- [High] No visible rating, review count, testimonials, client quotes or case-study quotes. No aggregateRating (correctly absent, since nothing to mark up). A client logo strip exists (Yamaha, Yaskawa, Daikin, Freeport), which adds trust, but names are not linked to case studies or permission statements.
- Velocity and response rate cannot be assessed (no GBP found). For B2B, collect 10 to 15 reviews from PIC contacts at client factories, plus testimonials on-page with named client, project and date.

### Citations (Tier 1)
- Not verifiable: Yelp returned 403 to scripted fetch, Google and DuckDuckGo blocked automated queries, and Bing showed no company listings. No BBB equivalent applies in Indonesia.
- Recommended Indonesia-relevant sources to check and create: Google Business Profile, Bing Places, Apple Business Connect, Facebook Page, Instagram, LinkedIn Company page, Yellow Pages Indonesia, Kompass, Indonetwork, Indotrading, Panduan/Pages (Pagesinfo), Bizapedia-style registries, KADIN Indonesia, GAPENSI or Gabungan Kontraktor Mekanikal Elektrikal, Dun and Bradstreet, and the Freeport/Timika vendor directories if listed.
- [High] Facebook and Instagram links point to https://www.facebook.com/ and https://www.instagram.com/ (generic). Outbound link to profiles is currently missing or broken; no sameAs. Treat the legacy site's identical placeholder links as the same bug, copied forward.

### Local schema
Present: Organization + WebSite (@graph, @id), name, url, logo, email, telephone, address (PostalAddress with only streetAddress and country), two ContactPoints (areaServed ID).
Issues:
- [High] No LocalBusiness subtype. Use `["Organization","ProfessionalService"]` or `GeneralContractor` (Schema.org has `GeneralContractor`, `ElectricalContractor`, `HVACBusiness`); choose the closest and keep Organization semantics. Do not use deprecated or invalid types.
- [High] Missing geo (5-decimal latitude/longitude), openingHoursSpecification, hasMap, areaServed (Bekasi, Malang, Timika, Indonesia), image, priceRange (optional; skip for B2B), and sameAs.
- [Medium] Missing addressLocality, addressRegion, postalCode as separate fields.
- [Medium] Branches: model Malang and Timika as `department` or separate `LocalBusiness` nodes with `@id` and `parentOrganization`/`branchOf` pointing to `https://<final-domain>/#organization`, each with its own PostalAddress, once real addresses exist.
- [Low] No Service schema for the 12 services (also noted in schema.md).

### Location pages
- [High] No dedicated pages for Bekasi, Malang or Timika. "3 Cabang Nasional / Bekasi · Malang · Timika" and "tiga kantor di Bekasi, Malang, dan Timika" are the only mentions, plus a counter "3 Kantor Cabang". No addresses, phones, hours, staff, map or photos for Malang and Timika.
- Doorway risk is currently nil (no location pages). When adding them, each page needs a real address, local phone, local project proof (for example Freeport/Timika projects), team, and unique copy (target at least 60 percent unique). Do not clone with a city swap.
- [High] Dedicated service pages are the top local organic factor and no service pages exist (services are an overview block with in-page anchors to /works#...). Build 12 service pages, then service-plus-region pages where real coverage exists (for example "instalasi chiller Bekasi", "panel kontrol Malang").
- Internal linking: footer and nav have no office or service-area links.

### Industry-specific factors
- Industrial B2B buyers verify credentials. /license exists, but without the legal identifiers (NIB, SBU Konstruksi, SBUJK, K3 and SKT numbers) visibly in structured text, local trust signals are limited. Add certification numbers, client lists, and project proof near the contact block.
- Indonesian signals: add `inLanguage` id plus English alternates if targeting multinationals (Freeport, Daikin). Add WhatsApp contact (no wa.me link detected; form opens WhatsApp via script only).
- [Low] Email is cloaked on page ([email protected]) and fine for spam, but schema exposes it. The email domain (trissan.co.id) differs from the site domain.

### Prioritized actions
1. [Critical] Search Google Maps for "PT Trissan" and claim, verify, or create the GBP (HQ Bekasi). Confirm category, hours, service areas, and website.
2. [Critical] Decide the production domain (trissan.co.id vs mdhost.site) before citations and GBP are tied to a URL. The legacy trissan.co.id currently serves around 4,000 spam outbound links (a compromise indicator), which would undermine any local link-trust work. Clean it before reusing it or redirecting from it.
3. [High] Replace generic facebook.com/instagram.com links with real profile URLs, add sameAs (Facebook, Instagram, LinkedIn, GBP).
4. [High] Add LocalBusiness-type schema with full PostalAddress, geo, hours, areaServed, hasMap.
5. [High] Embed Google Maps (iframe) and a place-based link on the contact section; add opening hours as visible text.
6. [High] Publish Malang and Timika branch details (address, phone, hours) and branch pages with unique content; add branch nodes to schema.
7. [High] Build dedicated service pages for the 12 services, then region variants where genuine.
8. [High] Start a review program: ask 10 to 15 clients for GBP reviews and add named testimonials on-page (then add Review/aggregateRating only if marked up honestly).
9. [Medium] Standardize NAP format and name ("PT. Trissan") across site, schema, GBP, and directories; add split address fields.
10. [Low] Build citations: Bing Places, Apple Business Connect, Indonetwork, Indotrading, Kompass, Yellow Pages Indonesia, KADIN, industry associations.

### Limitations
No Google Maps/GBP, Yelp, local-pack, or review data was accessible; citation checks relied on blocked or low-signal search results. No geocoding of the address, no branch addresses to compare, and no backlink/local link data. Proximity effects not assessable. Rating, review velocity and response rate are therefore "not assessable" rather than scored on evidence.

---

## Backlink Profile - trissan.mdhost.site

Date: 2026-10-29 | Tier 0 (Common Crawl + verification crawler only; no Moz/Bing/DataForSEO/Keywords Everywhere keys)

### Score
- Weighted Backlink Health Score: **INSUFFICIENT DATA** (0 of 7 scoring factors have a data source; Tier 0 needs at least 4). No numeric health score is produced on purpose.
- Backlink readiness (analyst judgment, NOT the weighted score, low confidence): **12/100**. Basis: no observable inbound links, no external citations, shared-host subdomain, brand-new site.

### Data collected
| Source | Result | Confidence |
|---|---|---|
| Common Crawl web graph, release cc-main-2026-jan-feb-mar, host `trissan.mdhost.site` | Not in crawl, not in rankings. PageRank and harmonic centrality: null | 0.50 (domain-level; quarterly graph) |
| Common Crawl, parent `mdhost.site` | Not in crawl, not in rankings | 0.50 |
| Moz (DA/PA/spam, referring domains, anchors) | Not available, no API key | n/a |
| Bing Webmaster inbound links | Not available, no API key and property not verified | n/a |
| Own-site HTML (render.json, parsed) | Homepage links only to its own pages, Google Fonts, and one maps.google.com address link. No social profile links, no `sameAs` found in rendered HTML | Parsed 0.95 |
| Verification crawler | Not run: no known backlink list was supplied | n/a |

Interpretation: "not in Common Crawl" means the host is absent from a Jan-Mar 2026 snapshot. That is consistent with a new or very low-authority site, but it does NOT prove zero backlinks. Links created after the snapshot, or on pages Common Crawl skipped, would not appear. Referring domains: **unknown** (Tier 0 cannot enumerate them). Anchor text, toxicity, link velocity, follow ratio and geo distribution are all unscored (no data).

### Factors scored vs skipped
Scored: none. Skipped: referring domain count, domain quality distribution, anchor naturalness, toxic ratio, velocity, follow/nofollow, geographic relevance.

### Findings

#### 1. [High] No measurable backlink profile (Common Crawl: absent)
Neither the host nor `mdhost.site` has any graph presence. For a B2B contractor this means no visible third-party endorsement signal at present. Treat the baseline as approximately zero until Google Search Console (Links report) or Bing Webmaster data shows otherwise.

#### 2. [High] Shared-hosting subdomain limits link equity and trust
Observed: the site lives at `trissan.mdhost.site`, a subdomain of a hosting provider's domain. Impact below is general SEO reasoning, not measured here:
- Authority accrues to the registrable domain `mdhost.site`, which PT. Trissan does not control. That domain is also absent from Common Crawl, so there is no inherited authority to borrow.
- Every link, citation and directory listing built toward this URL must be redirected or rebuilt if the company later moves to its own domain. Redirects carry most but not all value, and migration risk grows as links accumulate.
- Many directories, associations and vendor-registration portals expect a company domain and corporate email. A hosting subdomain reads as a trial or hobby site and may be rejected or discounted.
- Brand signals (branded search, email on own domain, knowledge panel eligibility) are weaker on a subdomain.
- Not verified: whether `mdhost.site` is on the Public Suffix List. If it is, the subdomain is treated as its own site; if not, it may share reputation with other tenants, including spammy ones. Worth checking.

Recommendation: register a company-owned domain (a .co.id requires Indonesian company documents; .com is the alternative), 301 the subdomain to it, and only then invest heavily in link building. If the move is planned within months, do citation work using the final domain only.

#### 3. [Medium] No social or entity profile links from the site
No `sameAs` or social links were found on the pages crawled. Linking the company LinkedIn page, Google Business Profile and any Facebook/Instagram pages (and getting reciprocal links back) establishes entity ties that make early link building easier to verify. See the schema and local findings for related items.

#### 4. [Medium] Technical blockers reduce the value of any earned links
Other findings in this audit (sitemap.xml returning 500 with a debug page, robots.txt without a Sitemap line, APP_DEBUG exposure) undermine crawling of earned links. Fix before outreach. `/seo technical <url>` covers crawlability; `/seo content <url>` covers E-E-A-T depth.

#### 5. [Low] Toxic-link risk: unknown, presumed low
No spam data available. A new site with no links has little toxic exposure. Avoid bulk directories and paid link packages. Re-check with Moz Spam Score or GSC once links exist.

### Link-building priorities (B2B industrial contractor, Indonesia)
Linkable assets already on the site: /works and /portofolio (project case studies such as chiller duct bypass, fire hydrant system, conveyor systems, distribution panels) and /license (credentials).

1. **Client and partner case-study links [High value, hardest]**: ask completed-project clients, principal contractors and the equipment brands Trissan installs for a testimonial, logo or project mention linking to the matching /works entry. Offer a ready-written project summary with photos.
2. **Indonesian industry associations [High value]**: check membership eligibility and member-directory listings, for example KADIN Indonesia and regional chapters (Bekasi, Malang, Timika/Papua), GAPENSI (construction), electrical contractor/installer associations such as AKLI, automation/instrumentation bodies, and tenant directories of Bekasi industrial estates. These names and eligibility rules come from general knowledge and were not verified; confirm each before applying.
3. **Supplier and vendor directories [Medium]**: moderated Indonesian B2B and industry directories (for example Indonetwork, Kompass, Yellow Pages Indonesia) and vendor-registration portals of large manufacturers in the Bekasi corridor. Keep NAP identical to the Google Business Profile for the Bekasi office. Prefer relevant, moderated directories over bulk submissions.
4. **Brand partner pages [Medium]**: authorized-installer or reseller pages from brands Trissan works with.
5. **Local and regional citations [Medium]**: Google Business Profile for Bekasi, Malang and Timika offices (entity value more than link equity), regional chamber or government business listings.
6. **Technical content and trade media [Medium-Low]**: articles on fire hydrant system design, conveyor maintenance and automation retrofits for Indonesian engineering and industry publications, plus LinkedIn posts as supporting entity signals.

Sequencing: (a) move to an own domain, (b) fix sitemap, robots and debug issues, (c) verify GSC and Bing Webmaster (free; gives real link data), (d) pursue items 1-3 first.

### Next data steps
- Verify the property (or the new domain) in Google Search Console and Bing Webmaster Tools and export the Links report.
- Add a free Moz API key (2,500 rows/month) to reach Tier 1 (confidence 0.85), then re-run `/seo backlinks`.
- Re-check Common Crawl after the next quarterly release.
- Run `verify_backlinks.py` once placed links exist.

### Severity summary
| Severity | Item |
|---|---|
| High | No measurable backlink profile |
| High | Shared-hosting subdomain limits equity and trust; migration risk |
| Medium | No social/entity profile links |
| Medium | Technical blockers reduce link value |
| Low | Toxic risk unknown (presumed low) |

---

