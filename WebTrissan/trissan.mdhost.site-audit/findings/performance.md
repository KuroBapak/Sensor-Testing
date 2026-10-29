# Performance (CWV) - 34/100 (lab-estimated, mobile-throttled)

Evidence: PSI API retried once on 2026-10-29, still 429 (no Lighthouse build/score available). CrUX: none (new site).
Primary data = local throttled lab run (perf-lab.json; headless Edge, 1.6 Mbps / 150 ms / 4x CPU) plus live HTML/header
inspection. Lab numbers are single runs and only indicative; they are not the 75th-percentile field assessment.
Score is an analyst estimate, not a Lighthouse score.

## Core Web Vitals status (lab, no field data)

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

## Findings

### [Critical] Mobile LCP 9-23 s: oversized, unprioritized, eager/lazy-misapplied images
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

### [High] Tailwind Play CDN (cdn.tailwindcss.com) is a render-blocking runtime compiler on all 5 pages
- Synchronous script in `<head>`; it compiles CSS in the browser on each visit and is the main mobile FCP gate
  (FCP 3.2-4.7 s even on the 589 KB /license page). Tailwind documents it as not for production, and it is a
  third-party single point of failure (no SRI, no fallback).
- /about, /portofolio, /works use `?plugins=forms,container-queries`, so the payload is larger still.
- No page references `build/assets`: no Vite-built CSS on the public pages.
- Fix (expected: mobile FCP -1.5 to -2.5 s, LCP follows): compile Tailwind at build time through the Vite pipeline
  (`@vite(['resources/css/app.css'])`), purge unused classes (typically 10-30 KB CSS), and move the inline
  `tailwind.config` into the build config.

### [High] Render-blocking Google Fonts CSS, 2-4 stylesheets per page, over-requested axes
- 2-3 separate `fonts.googleapis.com` stylesheets per page: Montserrat 400-900, Inter 300-700, JetBrains Mono (about),
  and Material Symbols Outlined with full variable axes (`wght,FILL@100..700,0..1`). /works adds a second
  Material Symbols link with opsz/GRAD axes. Each is a render-blocking request to a second origin plus gstatic font fetches.
- Material Symbols (full variable icon font) is used for about 30 icons on home.
- Fix: self-host latin-subset woff2, preload the 1-2 above-the-fold weights, trim to the weights actually used,
  merge duplicate Material Symbols links, and replace the icon font with inline SVG (or `icon_names=` subsetting).
  Keep `display=swap` and add a fallback with `size-adjust`.

### [Medium] unpkg.com/lucide@latest (home, /license): unpinned, redirecting, blocking
- Synchronous script; `@latest` answers 302 to `/lucide@1.64.0/dist/umd/lucide.min.js` with `max-age=60`, so new
  visits pay an extra third-party round trip, and the version can change without notice (breakage / supply-chain
  risk). The UMD bundle ships every icon.
- Fix: install `lucide` via npm and import only the icons used (tree-shaken in Vite). At minimum pin an exact
  version, add `defer` and SRI. Also unify icon systems (Material Symbols and Lucide are both loaded on home/license).

### [Medium] Slow, inconsistent server response (TTFB); no HTML caching
- Lab TTFB: 164-688 ms typical, but 1.26-1.40 s on /portofolio (both profiles) and 1.25 s on /license desktop.
  Today's curl samples (including DNS/TLS) ranged from 0.77 s to 14 s on a cold home request and 6 s on /portofolio,
  which points at slow cold origin / PHP boot rather than the network. TTFB above 0.8 s eats the LCP budget.
- Headers on every page: `Cache-Control: no-cache, private`, `cf-cache-status: DYNAMIC`, plus Set-Cookie
  (laravel-session, XSRF-TOKEN) on anonymous GETs, which prevents edge caching of HTML.
- Fix: `php artisan optimize` (config/route/view/event caches), OPcache, cache settings/portfolio queries; avoid
  session cookies on public GET routes and add a Cloudflare cache rule (or short `s-maxage` +
  `stale-while-revalidate`) for anonymous HTML.

### [Low] Static asset caching
- Images: `Cache-Control: max-age=14400` (4 h), Cloudflare HIT. Raise to 30 days with fingerprinted/versioned
  filenames after conversion. Brotli on HTML and HTTP/3 are already enabled (good).

### [Low] CLS / INP hardening
- Add width/height to footer logos, partner strips and gallery images; reserve space for modal images.
- Keep modal open/close handlers light. Removing the Tailwind runtime removes its MutationObserver cost.

## Third-party impact summary
| Origin | Where | Blocking | Impact |
|---|---|---|---|
| cdn.tailwindcss.com | all pages | yes (script) | High: runtime CSS compile, FCP/LCP gate |
| fonts.googleapis.com / gstatic | all pages | yes (CSS) | Medium-High: 2-4 requests, large icon font |
| unpkg.com/lucide@latest | /, /license | yes (script) | Medium: redirect + unpinned |
| Cloudflare email-decode | all pages | no | Negligible |

## Prioritized plan
1. Convert/resize images; add fetchpriority/preload to the LCP image; remove lazy from above-the-fold images (biggest LCP win).
2. Replace the Tailwind CDN with Vite-compiled CSS (biggest FCP win).
3. Self-host and subset fonts; swap the Material Symbols font for SVG icons; bundle or drop lucide.
4. Fix TTFB: Laravel optimize caches, no cookies on anonymous pages, Cloudflare HTML caching.
5. Re-run PSI (mobile) and monitor CrUX once traffic exists; targets at p75: LCP <=2.5 s, INP <=200 ms, CLS <=0.1.

## Not verified
PSI/Lighthouse scores and build, CrUX field data, real INP, and a lab re-run against the current 5.07 MB home hero.
