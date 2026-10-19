# Implementation Plan: CMS (Filament PageSettings) Audit & Fix

## Task Objective
Audit and fix CMS (Filament PageSettings) integration across all 5 pages:
1. Fix image upload disk settings and create storage symlink so uploaded images are stored on `public` disk and accessible via web.
2. Wire missing "About Teaser" section on the landing page (`home.blade.php`) using `$pageSettings->about_teaser` with fallback.
3. Wire hero elements, trust cards, stats, and SEO titles/descriptions across pages to consume PageSetting CMS data.
4. Ensure all Filament FileUpload components explicitly configure `disk('public')` and appropriate directories.

## Scope
- Image storage disk configuration across Filament Page & Resource classes.
- Public storage symlink creation (`php artisan storage:link`).
- `home.blade.php` template integration for About Teaser and hero CMS fields.
- Page header / Hero / SEO metadata wiring for `abtus.blade.php`, `works.blade.php`, `portofolio.blade.php`, and `license.blade.php`.
- Adding unit/feature tests for page settings rendering and image disk consistency.

## Explicit Non-Goals
- Full rewrite of static HTML catalog cards in views where dynamic DB queries (e.g. Services, Portfolio items) are complex and unrequested unless necessary for CMS PageSettings integration.
- Destructive DB schema changes.

## Acceptance Criteria
- [x] `php artisan storage:link` is executed and `public/storage` points to `storage/app/public`.
- [x] All `FileUpload` instances in `ManagePageSettings`, `ManageGlobalSettings`, `ManageAboutCompany`, and relevant resources specify `->disk('public')`.
- [x] The landing page renders the About Teaser section using data from `$pageSettings->about_teaser` (image, title, eyebrow, paragraphs, cta_text, cta_url, image_badge) with clean fallback when null.
- [x] Landing page hero section respects `$pageSettings->hero_title`, `hero_subtitle`, `hero_cta_text`, `hero_cta_url`, `hero_slides`, and `trust_card`.
- [x] Views consume `seo_title` and `seo_description` where defined in PageSetting.
- [x] Works, Portfolio, and Licenses views consume their respective `$pageSettings` hero titles, subtitles, and stats cards.
- [x] Code formatted with Laravel Pint (`vendor/bin/pint --dirty --format agent`).
- [x] All Pest tests pass without regressions, and new tests verify PageSettings rendering.

---

## Phases

### Phase 1: Storage Symlink & FileUpload Disk Configuration
- [x] Run `php artisan storage:link` to create the public storage symlink.
- [x] Update `ManagePageSettings.php` to set `->disk('public')` on all FileUpload components.
- [x] Update `ManageGlobalSettings.php` to set `->disk('public')` on all FileUpload components.
- [x] Update `ManageAboutCompany.php` to set `->disk('public')` on all FileUpload components.
- [x] Update Resource FileUploads (`WorkResource.php`, `CorporateAccreditationResource.php`, `PortfolioItemResource.php`, `ProductCertificationResource.php`) with `->disk('public')`.
- [x] Verify file upload directory permissions and test storage URL resolution helper.
*Status: Complete*

### Phase 2: Landing Page (`home.blade.php`) CMS & About Teaser Integration
- [x] Wire SEO title and meta description.
- [x] Wire hero CTA text & URL, hero carousel slides (dynamic images with fallback to default static images), and trust card.
- [x] Update `resources/views/home.blade.php` to wire the About Teaser section (`<section id="about">`) with dynamic `$pageSettings->about_teaser` data:
  - Image (served via `Storage::disk('public')->url(...)` or `asset('storage/...')` if path exists, fallback to existing static URL).
  - Image badge ("EST. 1998 • 25+ THN").
  - Eyebrow ("TENTANG PT. TRISSAN").
  - Title ("Dedikasi Rekayasa Presisi...").
  - Paragraph 1 & Paragraph 2.
  - CTA Button text ("Pelajari Profil Lengkap") and CTA URL ("/about").
  - Stats card fallback if `stats` is null.
- [x] Wire services slider (`@forelse($services as $service)`), works grid (`@forelse($featuredWorks as $work)`), and services overview (`@forelse($serviceClusters as $cluster)`).
- [x] Wire navbar & footer dynamic branding (logo, company name, tagline, description, partner banner, phones, emails, factory address, legal links, copyright).
- [x] Verify landing page renders correctly with both populated CMS data and null/empty state.
*Status: Complete*

### Phase 3: Wire PageSettings in Remaining Pages
- [x] `resources/views/works.blade.php`: Wire SEO title/description, hero title, subtitle, stats cards from `$pageSettings`.
- [x] `resources/views/portofolio.blade.php`: Wire SEO title/description, hero title, subtitle, stats cards from `$pageSettings`.
- [x] `resources/views/license.blade.php`: Wire SEO title/description, hero title, subtitle, stats, hero badge card, corporate highlight cards from `$pageSettings`.
- [x] `resources/views/abtus.blade.php`: Wire SEO title/description from `$pageSettings`.
*Status: Complete*

### Phase 4: Verification & Testing
- [x] Create Pest feature test verifying PageSettings rendering across views (including About Teaser on home page).
- [x] Run Pint formatter (`vendor/bin/pint --dirty --format agent`).
- [x] Run full test suite (`php artisan test --compact`).
- [x] Check `git diff --check`.
*Status: Complete*
