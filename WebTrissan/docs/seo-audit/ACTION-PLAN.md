# SEO Action Plan: PT. Trissan

Source: [FULL-AUDIT-REPORT.md](FULL-AUDIT-REPORT.md) (local dev build, estimated score ~47/100).

## Phase 1: Critical fixes (before launch)

- [ ] **H5** Set `lang="id"` on `/works` and `/portofolio` (`resources/views/works.blade.php:1`, `portofolio.blade.php:1`). Effort: 5 min.
- [ ] **H2** Add a canonical tag to every page. Effort: 15 min.
- [ ] **H1** Generate `sitemap.xml` and add `Sitemap:` to `robots.txt`. Effort: 30 min.
- [ ] **H4** Replace the Tailwind CDN runtime with the Vite build; pin or self-host Lucide. Effort: 1 to 3 hours (templates use CDN-only config such as a `tailwind.config` script, which must move into the CSS build).
- [ ] **M8** Remove or replace sample certifications and personnel with real data. Effort: depends on getting the real documents.

## Phase 2: High-impact improvements (week 1 to 2)

- [ ] **H3** JSON-LD: `Organization` with the 3 branches, `WebSite`, `BreadcrumbList`, `Service` on `/works`.
- [ ] **M1** Open Graph + Twitter tags with a real share image.
- [ ] **M2** Rewrite meta descriptions to ~120 to 155 characters (home is 198, license is 44).
- [ ] **M9** Publish Privacy Policy and Terms pages and link them.
- [ ] **M4 / M5 / M6** Accessibility and image hygiene: `aria-hidden` on icon spans, real `alt` on the partners banner, `width`/`height` and lazy loading on images.

## Phase 3: Content and authority (month 2)

- [ ] **M7** Dedicated project pages (`/works/{slug}`) with scope, client sector, location and photos.
- [ ] **M3** Fix heading hierarchy (no `h1` to `h3` jumps).
- [ ] Create and complete Google Business Profile entries for Bekasi, Malang and Timika; keep NAP identical to the site.
- [ ] Real photos for hero, facility and team images (placeholders remain on several pages).

## Phase 4: Monitoring and hardening (ongoing)

- [ ] Deploy, then re-run `/seo audit <live-domain>` for the full multi-agent audit (CWV, indexation, HTTPS, headers, backlinks).
- [ ] Security headers, `expose_php=Off`, gzip/brotli and cache headers on the web server (L5, L6).
- [ ] `Disallow: /admin`, explicit favicon links, real social profile URLs (L1, L2, L4).
- [ ] Optional: `llms.txt` (L3).
- [ ] Submit the sitemap in Google Search Console and Bing Webmaster Tools; track with `/seo drift` baselines after launch.
