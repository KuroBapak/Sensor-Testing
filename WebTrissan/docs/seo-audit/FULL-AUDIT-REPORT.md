# SEO Audit: PT. Trissan (local dev build)

**Target:** `http://127.0.0.1:8000` (Laravel dev server) · **Date:** 2026-10-29 · **Business type:** B2B industrial services (electrical, mechanical, automation), multi-branch (Bekasi, Malang, Timika)

## Scope and method (read this first)

- The claude-seo plugin's fetcher **refuses private hosts** (`url_safety: Blocked hostname: 127.0.0.1`), so its multi-agent audit could not run against localhost. I did not bypass that guard.
- Instead, the 5 public pages (`/`, `/about`, `/works`, `/portofolio`, `/license`) plus `robots.txt`, `sitemap.xml`, `favicon.ico`, `llms.txt` and a 404 probe were fetched directly and checked against the same categories and weights the skill uses.
- **Not assessed:** real Core Web Vitals (a dev server is not representative), HTTPS/HSTS, gzip/brotli, caching, CDN, indexation (Search Console), backlinks, SERP/competitor data. Re-run `/seo audit <live-domain>` after deployment for those.
- The score below is therefore an **estimate for the local build**, not a ranking prediction.

## SEO Health Score: ~47 / 100 (estimate)

| Category | Weight | Score | Note |
|---|---|---|---|
| Technical SEO | 22% | 55 | No sitemap, no canonicals, wrong `lang` on 2 pages, no security headers |
| Content Quality | 23% | 50 | Real company content now, but thin, and sample/fake sections remain |
| On-Page SEO | 20% | 62 | Titles/H1s solid; meta lengths, heading skips, no social tags |
| Schema / Structured Data | 10% | 0 | No JSON-LD anywhere |
| Performance (CWV) | 10% | 45 (est.) | Tailwind Play CDN runtime + unpinned unpkg script on every page; not measured |
| AI Search Readiness | 10% | 35 | Crawlers allowed, but no schema, no `llms.txt`, thin citable content |
| Images | 5% | 55 | Alt attributes present; no dimensions, mostly eager, placeholders |

## What works

- Every page returns 200, has a unique `<title>`, a meta description, exactly one `<h1>`, and a viewport tag.
- Titles are well sized (26 to 62 characters) and branded consistently.
- Unknown URLs return a proper **404**.
- `robots.txt` exists and allows crawling; `/admin` redirects to login (302).
- No `noindex` leaks; internal linking is healthy (23 to 44 links per page) with no broken `#` anchors in the page body.
- Every `<img>` has an `alt` attribute (none missing).

## Findings

### Critical / High

| # | Finding | Evidence | Fix |
|---|---|---|---|
| H1 | **No `sitemap.xml`** and `robots.txt` has no `Sitemap:` line | `/sitemap.xml` returns 404; `public/robots.txt` is just `User-agent: * / Disallow:` | Generate a sitemap of the 5 public URLs (and per-project URLs if added); reference it in `robots.txt` |
| H2 | **No canonical tag on any page** | `<link rel="canonical">` absent on all 5 pages | Add `<link rel="canonical" href="{{ url()->current() }}">` in the shared head |
| H3 | **No structured data (JSON-LD)** | 0 blocks on all pages | Add `Organization` (with branches) / `LocalBusiness`, `WebSite`, `BreadcrumbList`; `Service` and project markup on `/works` |
| H4 | **Tailwind runs from the Play CDN on every page** (`cdn.tailwindcss.com`) and the home/license pages load `unpkg.com/lucide@latest` unpinned | External scripts listed on all 5 pages; Tailwind 4 + Vite already in `package.json`, `public/build` exists | Compile CSS with `@vite` (production build), pin Lucide to a version or self-host; removes a render-blocking runtime and a supply-chain risk |
| H5 | **Wrong `lang` on `/works` and `/portofolio`** (`lang="en"`, content is Indonesian) | Hardcoded in `resources/views/works.blade.php:1` and `portofolio.blade.php:1`; other pages use `id` | Set `lang="id"` (ideally from one shared layout value) |

### Medium

| # | Finding | Evidence | Fix |
|---|---|---|---|
| M1 | **No Open Graph / Twitter tags** | `og:*` and `twitter:*` absent on all pages | Add `og:title/description/image/url/type`, `twitter:card`; needs a real share image (currently placeholders) |
| M2 | **Meta description lengths off** | Home 198 chars (truncates in results); Portofolio 80; License 44 | Aim for ~120 to 155 characters each |
| M3 | **Heading hierarchy skips levels** | Home and About go `h1` then `h3` (service/commitment cards) before the first `h2` | Use `h2` for the card groups or add a section `h2` first |
| M4 | **Icon text leaks into headings** | `/works` modal headings read "tuneSpesifikasi Utama" and "verifiedSertifikasi & Standar Mutu"; an empty `<h2>` exists in the modal | Add `aria-hidden="true"` to Material Symbols spans; remove the empty heading |
| M5 | **Partners banner has empty `alt`** though it is informative | `<img src=".../partners-banner.jpg" alt="">` on home/About | `alt="Mitra dan klien PT. Trissan"` (keep `alt=""` only for decorative images) |
| M6 | **No `width`/`height` on any image** (all pages) and little lazy-loading (home: 0 of 13, license: 0 of 4) | Parsed `<img>` attributes | Add dimensions to prevent layout shift; `loading="lazy"` for below-the-fold images |
| M7 | **Projects have no URLs of their own** | Works are `/works#slug` anchors; one page holds everything | Dedicated project pages (`/works/{slug}`) would give each project (Astra Honda, Mayora, Indofood...) its own indexable URL, title and schema |
| M8 | **Sample/fake content still published** | License page shows "Turbine Shaft Hardening Nadcap" and similar sample certificates; Portofolio lists "Lead 5-Axis CNC Machinist" etc. as a "5 Personil Terdaftar" directory | Replace with real data or hide those sections before launch; fake credentials damage trust and E-E-A-T |
| M9 | **Legal links have no destination** | Footer "Privacy Policy" and "Terms of Service" have no URL (plain text) | Publish real pages and link them |

### Low

| # | Finding | Fix |
|---|---|---|
| L1 | No `<link rel="icon">` (a `favicon.ico` file exists, so browsers still find it) | Add explicit icon links (ico, png, apple-touch) |
| L2 | `robots.txt` does not mention `/admin` | Add `Disallow: /admin` (login page should not be crawled) |
| L3 | No `llms.txt` (optional, ignored by Google Search) | Only if AI-assistant discoverability matters to you |
| L4 | Social links point at facebook.com / instagram.com homepages | Replace with the real profile URLs |
| L5 | `X-Powered-By: PHP/8.4.24` header exposed | Set `expose_php=Off` in production |
| L6 | No security headers (HSTS, X-Frame-Options, X-Content-Type-Options, CSP, Referrer-Policy, Permissions-Policy) | Add at the web server or a Laravel middleware; check again on the live domain |

### Production-only checks (cannot be judged on localhost)

HTTPS and redirects (www/non-www, http to https), gzip/brotli (the dev server sends none; home HTML is 140 KB uncompressed), cache headers (`no-cache, private` locally), real LCP/INP/CLS, and indexation.

## Local SEO and AI search notes

- Name, address and phones are consistent between footer and contact block, but there is **no LocalBusiness markup** and the three branches (Bekasi, Malang, Timika) are not machine-readable. Google Business Profile listings for each branch would matter more than anything on-page.
- AI crawlers are not blocked (`Disallow:` is empty). Citability is limited by thin copy and no structured data; project write-ups with scope, client sector and location would give assistants something to quote.

## Method limits

All findings above come from fetched HTML and headers of the local site on 2026-10-29. Item M4's heading text is inside a JS-populated modal; I confirmed it in the rendered HTML, but it is not visible on first load.
