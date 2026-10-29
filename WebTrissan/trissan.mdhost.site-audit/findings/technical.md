# Technical SEO - 46/100

Audited 2026-10-29 against https://trissan.mdhost.site/ (live curl checks plus read-only source review). Pages: /, /about, /portofolio, /license, /works.

## Category status

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

## What works
- All 5 pages return 200, `lang="id"`, one `<h1>` each, unique title and meta description, viewport meta present, no `noindex` anywhere.
- Self-referencing canonicals on every page. Query strings are stripped (`/?utm=1` canonicalises to `https://trissan.mdhost.site`).
- HTTP to HTTPS 301 works (`http://.../about` -> `https://.../about`). Unknown URLs return a real 404.
- `x-content-type-options: nosniff` and `x-frame-options: SAMEORIGIN` are set. Cookies are `secure`, `httponly` (session) and `samesite=lax`.
- No SPA shell. Raw HTML is 60-136 KB per page and contains the full text.

## [Critical] /sitemap.xml returns HTTP 500
- Evidence: `sitemap_discovery.py` found no valid sitemap. `/sitemap.xml` gives 500, and `sitemap_index.xml`, `sitemap-index.xml` and `wp-sitemap.xml` give 404. The exception message is `ParseError: syntax error, unexpected identifier "version"`.
- Cause: `resources/views/sitemap.blade.php` line 1 is `{!! '<?xml version="1.0" ...?>' !!}`. Blade compiles the file to PHP, and the literal `<?xml` inside the compiled output is parsed as a PHP open tag.
- Fix: stop emitting the prolog from Blade. Either build the XML in `SeoFilesController::sitemap()` (string or `response($xml)`) and prefix `'<?xml version="1.0" encoding="UTF-8"?>'."\n"`, or put `<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>` on line 1 of the view. Then run `php artisan view:clear` and confirm `curl -I` returns 200 with `application/xml`.
- The controller also has a latent bug: `$updatedAt[$page]?->toAtomString()` throws "Undefined array key" if a page has no `PageSetting` row. Use `$updatedAt->get($page)?->toAtomString()`.
- After the fix, submit the sitemap in Google Search Console and Bing Webmaster Tools.

## [Critical] Debug mode on in production (information disclosure)
- Evidence: the 500 on `/sitemap.xml` renders the full Laravel debug page. It shows the exception, a stack trace with source paths and the PHP and Laravel versions (PHP 8.4.27, Laravel 13.37.0). Local `.env` has `APP_ENV=local` and `APP_DEBUG=true`. The `.env` values were not printed beyond these flags, and the debug-page contents were not reproduced here.
- Impact: an attacker can read file paths, framework versions and possibly config or SQL on any other error. Error responses also show Google a 500 page with a huge body.
- Fix: on the server set `APP_ENV=production` and `APP_DEBUG=false`, set `APP_URL=https://trissan.mdhost.site` (currently `http://localhost:8000`), then run `php artisan config:clear && php artisan config:cache`. Rotate `APP_KEY` and any secrets if the debug page ever showed environment values.

## [High] robots.txt has no Sitemap line and the app's robots route is bypassed
- Evidence: the live `/robots.txt` is only Cloudflare's managed block (content signals plus about 40 AI-training bots disallowed, `User-agent: * / Allow: /`). It contains no `Sitemap:` and no `Disallow: /admin`. `SeoFilesController::robots()` is written to emit both, but its output does not appear. `sitemap_discovery.py` reports `declared: []`.
- Impact: crawlers get no sitemap hint, and `/admin` (Filament; redirects to `/admin/login`) is not disallowed. The Cloudflare block also disallows ClaudeBot, GPTBot, Google-Extended, Applebot-Extended, CCBot, anthropic-ai, Claude-Web and others. That is deliberate for training, but it may limit AI search citation (see geo.md). `Googlebot` and `Bingbot` are not blocked.
- Fix: in Cloudflare (Security > Bots > "Manage robots.txt") switch to "Disable robots.txt configuration" or to the mode that prepends the managed block and keeps the origin file. Confirm the origin route output appears below the managed block. Make sure it includes `Sitemap: https://trissan.mdhost.site/sitemap.xml`. Also add `noindex` via `X-Robots-Tag` on `/admin/*`, since a robots Disallow alone does not stop indexing.

## [High] Missing security headers (no HSTS, CSP, Referrer-Policy, Permissions-Policy)
- Evidence: the response headers on `/` contain only `x-content-type-options` and `x-frame-options`.
- Fix: enable HSTS in Cloudflare (SSL/TLS > Edge Certificates, `max-age=31536000`, includeSubDomains once subdomains are confirmed HTTPS). Add a small middleware or Cloudflare Transform Rule for `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` and a report-only CSP first. Note the CSP must allow `cdn.tailwindcss.com`, `fonts.googleapis.com` and `fonts.gstatic.com` until Tailwind is moved to a build.

## [High] Tailwind Play CDN loaded in production on all 5 pages
- Evidence: every page includes `<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries">`. `public/build` already exists, so a compiled build is available.
- Impact: Tailwind states the Play CDN is not for production. It is a render-blocking third-party JIT script that generates CSS in the browser after load. That hurts LCP and CLS (unstyled flash, layout shift when styles are injected) and INP (main-thread work). It is also a single point of failure and a third-party dependency on every page.
- Fix: compile Tailwind with Vite (`npm run build`) and link the built CSS instead. Remove the CDN script and put the theme config (brand colors, fonts) in `tailwind.config.js`.

## [Medium] Duplicate URLs: /index.php and /about/ both return 200
- Evidence: `/index.php` returns 200 with `canonical = https://trissan.mdhost.site/index.php` (a different canonical from `/`, so it is a second indexable URL, not a consolidation). `/about/` returns 200 with canonical `/about`. The `.htaccess` trailing-slash redirect does not run, and the edge or origin serves the page directly.
- Fix: add 301s at Cloudflare (Redirect Rules) or the web server: `/index.php` -> `/`, and any `/path/` -> `/path`. In `seo-head.blade.php` build the canonical from `route(Route::currentRouteName())` instead of `url()->current()`, so it is always the named-route URL even when the request path varies.
- Also: the home canonical has no trailing slash (`https://trissan.mdhost.site`). This is equivalent, but use `route('home')` for consistency with the sitemap.

## [Medium] Slow and uneven server response (TTFB)
- Evidence: single-run TTFB through Cloudflare was /=1.5s, /works=2.2s, /portofolio=0.8s, /license=0.5s, /about=0.2s. This was one sample per page, so treat it as indicative.
- Cause: every response is `Cache-Control: no-cache, private`, `cf-cache-status: DYNAMIC`, and sets `laravel-session` plus `XSRF-TOKEN` cookies even for anonymous visitors. That prevents any edge caching of these static-content pages.
- Fix: cache the rendered pages (Laravel response cache or `Cache::remember` of the CMS data, invalidated from Filament). Move the session and CSRF cookie to the contact form only (the `web` middleware group is on every route) so Cloudflare can cache HTML. Send `Cache-Control: public, max-age=300, s-maxage=3600` for the public GET pages. Target TTFB below 600 ms.

## [Medium] Custom 404 is the default Laravel page in English
- Evidence: `/nonexistent-xyz` returns 404 with `<html lang="en">`, title "Not Found" and the framework's inline CSS, with no site navigation.
- Fix: add `resources/views/errors/404.blade.php` in Indonesian with navbar and links back to the main pages. This recovers users and spreads link equity to real pages.

## [Low] Missing /favicon.ico (404) and apple-touch-icon fallback
- Evidence: `/favicon.ico` returns 404 even though `<link rel="icon">` points at `/storage/seed/logo.png`. The `SeoFilesController::favicon()` route is meant to redirect there but does not respond, probably for the same reason as the robots route (a static or edge rule takes precedence).
- Fix: confirm the route is reachable at the origin, or place a real `favicon.ico` in `public/`. The favicon file is named `seed/logo.png`, a seeder placeholder path. Upload a proper square icon (48x48 multiples) through the CMS.

## [Low] Google Fonts loaded twice, no font preload on the home page
- Evidence: the head includes `Material Symbols Outlined` stylesheets twice (two different axis ranges) plus the Inter/Montserrat stylesheet. `/works` has 3 stylesheet links versus 2 elsewhere.
- Fix: keep only one Material Symbols request (subset to the icons used, or switch to inline SVG, which the pages already use in places). Self-host the Inter and Montserrat WOFF2 files and preload the weights used above the fold.

## [Low] Cloudflare email-decode script on pages
- Evidence: `/cdn-cgi/scripts/.../email-decode.min.js` is injected on the home page (Cloudflare Email Address Obfuscation).
- Impact: small extra request. It also obfuscates mailto addresses from simple crawlers. Consider disabling it if the email address should be machine-readable for LocalBusiness data.

## [Info] Other checks
- Mobile: viewport meta `width=device-width, initial-scale=1.0` is present on the home page and the inner pages use the same layout. Touch-target sizing was not verified from source.
- Hreflang: none, and none is needed (single language, `lang="id"`). Delegate to `seo-hreflang` only if an English version is added.
- IndexNow: not implemented. Optional for Bing, Yandex and Naver. Add a key file in `public/` and ping on Filament save events.
- Modal pop-up content is in the initial HTML, so it is crawlable. If hidden through `display:none` it may be weighted lower. Prefer visible headings for the key service text.
- `trustProxies(at: '*')` in `bootstrap/app.php` is fine behind Cloudflare, but restrict it to Cloudflare IP ranges if the origin is reachable directly.
- Redirect chains: none found. The HTTP to HTTPS hop is a single 301. `www.trissan.mdhost.site` does not resolve (expected for a subdomain host).

## Prioritised fix order
1. Fix `sitemap.blade.php` and the `PageSetting` null lookup, then submit the sitemap.
2. Set `APP_DEBUG=false`, `APP_ENV=production`, correct `APP_URL`, and recache config.
3. Restore the origin robots.txt (Sitemap line, `/admin` disallow) behind Cloudflare's managed block.
4. Replace the Tailwind CDN with the Vite build.
5. Add HSTS and the other headers, 301s for `/index.php` and trailing slashes, and a route-based canonical.
6. Cache public pages and drop the session cookie from anonymous traffic.
7. Custom Indonesian 404 page, favicon and font clean-up.

**Technical score: 46/100** (crawlability 8/25, indexability 15/20, security 6/20, URL/redirects 6/10, mobile 8/10, CWV risk 3/15 -> normalised).
