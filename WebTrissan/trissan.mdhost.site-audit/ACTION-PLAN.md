# Action Plan — trissan.mdhost.site

SEO Health Score: **48/100** (29 Oct 2026). Details for each item are in FULL-AUDIT-REPORT.md and findings/.

## Critical (this week)

- [ ] **Turn off debug mode in production.** In the server `.env`, set `APP_DEBUG=false` and `APP_ENV=production`, then run `php artisan config:cache`. (technical)
- [ ] **Fix `/sitemap.xml` (500).** Remove the `{!! '<?xml ... ?>' !!}` line from `sitemap.blade.php` or build the XML in `SeoFilesController::sitemap()`, use `->get($page)?->` for lastmod, clear compiled views, and add a regression test. (sitemap, technical)
- [ ] **Unpublish sample data.** Hide the `/license` certificates marked "data contoh" and the `/portofolio` "5 Personil Terdaftar" until real NIB/SBU/SMK3/K3/SKK data exists. (content, geo, sxo)
- [ ] **Secure trissan.co.id.** It serves about 4,000 spam/gambling outbound links. Clean it, then decide which domain is production. (local, geo)
- [ ] **Speed up mobile LCP (4.7–22.7 s).** Resize and convert images to WebP, add `fetchpriority="high"` to the hero and remove `loading="lazy"` from above-the-fold images. (performance, images)
- [ ] **Revert or fix the homepage H1** ("We Provide For User experience") to an Indonesian service + location H1. (onpage, sxo)

## High (next 1–2 weeks)

- [ ] Serve the app's robots.txt: turn off Cloudflare Managed robots.txt, or add `Sitemap:` and `Disallow: /admin` to it. (technical, sitemap)
- [ ] Submit the sitemap to Google Search Console and Bing Webmaster Tools and verify both properties. (sitemap, backlinks)
- [ ] Replace `cdn.tailwindcss.com` with the Vite build on all pages; bundle or pin lucide; self-host and subset fonts. (performance, technical)
- [ ] Fix the invisible header "Kontak" button on `/works`, `/portofolio` and `/license` at mobile width. (visual)
- [ ] Use real Facebook and Instagram profile URLs, add LinkedIn, and add `sameAs` to the Organization schema. (schema, local, geo)
- [ ] Add a WhatsApp (`wa.me`) contact link and a visible hero CTA on mobile. (sxo, visual)
- [ ] Add security headers: HSTS, Referrer-Policy, Permissions-Policy, CSP. (technical)
- [ ] Create or claim a Google Business Profile for the Bekasi HQ once the domain is decided. (local)

## Medium (within a month)

- [ ] Create self-canonical service pages (`/layanan/{slug}`) for the core services, in Indonesian, with location, process, project photos and FAQ. (sxo, content)
- [ ] Expand the 7 projects into short case studies (scope, specs, outcome, dates); fill or hide the empty spec fields in the pop-ups. (content, geo)
- [ ] Add LocalBusiness/ProfessionalService schema with a structured address, geo, hours, areaServed, branch offices and a Service catalog (ready-to-paste JSON-LD in findings/schema.md). (schema, local)
- [ ] Publish addresses and phone numbers for the Malang and Timika branches. (local)
- [ ] Make the 18 homepage service cards real `<a href>` links instead of `div onclick`. (agentic)
- [ ] 301 `/index.php` to `/` and trailing-slash URLs to their slashless versions; localize the 404 page. (technical)
- [ ] Allow Cloudflare to cache anonymous HTML (no session cookie on public pages) to stabilize TTFB. (performance)
- [ ] Enlarge tap targets under 44 px (filter chips, carousel arrows, modal close buttons). (visual)

## Low (backlog)

- [ ] Add width and height to all `<img>` tags (CLS already passes). (images)
- [ ] Add missing alt text on `/portofolio`, `/license` and `/works`. (images)
- [ ] Serve a real `/favicon.ico`; deduplicate the Google Fonts requests. (technical)
- [ ] Add `role="dialog"`, `aria-modal` and scroll lock to the `/portofolio` and `/license` modals. (visual, agentic)
- [ ] Decide deliberately on AI training-bot blocking (GPTBot, ClaudeBot, Google-Extended); optionally add llms.txt. (geo, agentic)
- [ ] Pursue links from client case studies, Indonesian industry associations and supplier directories after the domain move. (backlinks)

## Re-measure after fixes

- Run pagespeed.web.dev (mobile) for all 5 pages.
- Re-run `/seo audit https://trissan.mdhost.site/` and capture a drift baseline (`/seo drift`) to track regressions.
