# On-Page SEO — 65/100

**What works**
- Unique titles 26-62 chars and meta descriptions 124-155 chars on all pages
- Exactly one H1 per page
- Open Graph and Twitter tags present on all pages
- Good internal linking between home, works and service categories

## [Medium] Weak keyword targeting in titles

Titles lack location and high-intent service terms ('Tentang Kami | PT. Trissan').

**Fix:** e.g. 'Kontraktor Mekanikal & Elektrikal Industri di Bekasi | PT. Trissan'.

## [Low] Heading hierarchy issues

Homepage has 12 H3s before the first H2; /works has an empty H2; H1s on /portofolio, /license, /works are ALL CAPS in source.

**Fix:** Wrap the service grid under an H2, remove the empty H2, write H1s in sentence case and uppercase via CSS.

## [Low] og:image is the logo on every page

Shares on WhatsApp/LinkedIn/Facebook show a logo, not a project photo.

**Fix:** Use a 1200x630 image per page.

### [High] Homepage H1 changed mid-audit to English placeholder text

At 09:49 UTC the homepage H1 was "Solusi Kelistrikan & Mekanikal untuk Industri Anda". A later fetch the same day returned "We Provide For User experience" (title tag unchanged). The new H1 is English on a `lang="id"` page, contains no service or location keyword and does not match the title.

**Fix:** If this was a CMS test edit, revert it. Otherwise use an Indonesian H1 with service + location, e.g. "Kontraktor Mekanikal, Elektrikal & Otomasi Industri di Bekasi".

### [High] No URL per service

12 services exist only as homepage cards and `/works?category=` filters that canonicalise to `/works` (see sxo.md, content.md).

**Fix:** Self-canonical `/layanan/{slug}` pages for the core services.
