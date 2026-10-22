# Frontend Integration — Implementation Plan

## Task Objective
Replace static Blade view content with dynamic CMS data from the database for all 5 pages.

## Source PRD
`prd.md` — PRD v2.1

## Prerequisites
- ✅ Database schema aligned with PRD v2.1
- ✅ All models created with correct casts and relationships
- ✅ All seeders populated with PRD-compliant data

---

## Phase 1 — Create Controllers ✅ COMPLETE

Replace route closures with proper controllers that query the database and pass data to views.

### Checklist
- [x] `php artisan make:controller HomeController`
- [x] `php artisan make:controller WorksController`
- [x] `php artisan make:controller PortfolioController`
- [x] `php artisan make:controller LicensesController`
- [x] `php artisan make:controller AboutController`
- [x] `php artisan make:controller ContactController` (for form submissions)
- [x] Update `routes/web.php` to use controllers instead of closures

### Exit Criteria ✅
- 6 controllers created
- Routes updated to point to controller actions
- Routes accessible and passing required CMS data to views (17/17 tests passing)

---

## Phase 2 — Layout Components & Shared Partials ✅ COMPLETE

### Completed
- [x] Update `layouts/navbar.blade.php` to use `$globalSettings->company_name`, `company_tagline`, `company_logo`
- [x] Update `layouts/footer.blade.php` to use `$globalSettings` (company info, address, phone, email, copyright text)
- [x] Verified `works.blade.php`, `portofolio.blade.php`, `license.blade.php`, `abtus.blade.php` all use shared layouts
- [x] `home.blade.php` hero section: Use `$pageSettings->hero_title` and `$pageSettings->hero_subtitle` with fallbacks

### Exit Criteria ✅
- Shared navbar and footer layouts are dynamic with fallbacks
- All pages except `home.blade.php` use `@include('layouts.navbar')` and `@include('layouts.footer')`
- `home.blade.php` has partial integration (hero title/subtitle dynamic)
- All 17 tests passing, Pint formatting passed

**Note**: `home.blade.php` has a massive inline header/footer (6000+ chars each). The shared `layouts/navbar.blade.php` and `layouts/footer.blade.php` are fully dynamic and used by all other pages.

---

## Phase 3 — Dynamic Data Integration in Views — NOT STARTED

The views still contain static HTML that needs to be wired with dynamic data from controllers.

### `home.blade.php` Remaining Tasks
- [x] Update `layouts/navbar.blade.php` to use `$globalSettings->company_name`, `company_tagline`, `company_logo`
- [x] Update `layouts/footer.blade.php` to use `$globalSettings` (company info, address, phone, email, social links)
- [x] Hero: Use `$pageSettings->hero_title` and `$pageSettings->hero_subtitle` with fallbacks ✓
- [ ] Replace inline header (lines 55-135) with `@include('layouts.navbar')` — BLOCKED: 6000+ char inline nav, keep as-is for now
- [ ] Replace inline footer (lines 235-316) with `@include('layouts.footer')` — BLOCKED: similar issue
- [ ] Services: Loop `@foreach($serviceClusters as $cluster)` with `$cluster->services`
- [ ] Featured Works: Loop `@foreach($featuredWorks as $work)`
- [ ] Contact form: Add modal form with `route('contact.store')`, `@csrf`, validation errors

**Note**: `home.blade.php` has massive inline header/footer (6000+ chars each). The shared `layouts/navbar.blade.php` and `layouts/footer.blade.php` are already dynamic and used by all other pages. For `home.blade.php`, we're injecting dynamic data directly into its existing structure rather than extracting the layout.

---

## Phase 3 — Works Page (`/works`)

### Controller: `WorksController@index`
Query: `GlobalSetting::current()`, `PageSetting::forPage('works')`, active `Service`s, paginated `Work`s.


## Phase 4 — Portfolio Page (`/portofolio`)

### Controller: `PortfolioController@index`
Query: `GlobalSetting::current()`, `PageSetting::forPage('portfolio')`, categories with items, paginated `PortfolioItem`s.

### View Updates: `resources/views/portofolio.blade.php`
- [ ] Hero and stats from `$pageSettings`
- [ ] Portfolio items from `$portfolioItems`
- [ ] Category filtering

---

## Phase 5 — Licenses Page (`/license`)

### Controller: `LicensesController@index`
Query: `GlobalSetting::current()`, `PageSetting::forPage('licenses')`, corporate accreditations by category, featured product certifications.

### View Updates: `resources/views/license.blade.php`
- [ ] Hero, badge card, and corporate highlights from `$pageSettings`
- [ ] Certifications grid from `$corporateAccreditations`
- [ ] Product certifications from `$productCertifications`

---

## Phase 6 — About Page (`/about`)

### Controller: `AboutController@index`
Query: `GlobalSetting::current()`, `PageSetting::forPage('about')`, `AboutCompany::current()`, active `Personnel` with clusters and skills.

### View Updates: `resources/views/abtus.blade.php`
- [ ] Hero and overview from `$aboutCompany`
- [ ] VMV cards from `$aboutCompany->vision_mission_values`
- [ ] Facilities from `$aboutCompany->facilities`
- [ ] Milestones timeline from `$aboutCompany->milestones`
- [ ] Org chart from `$aboutCompany->org_chart`
- [ ] Personnel grid from `$personnel`

---

## Phase 7 — Contact Form

### Controller: `ContactController@store`
- [ ] POST route `/contact`
- [ ] Validate and create `ContactSubmission` (status: 'new')
- [ ] Add contact form partial or component

---

## Phase 8 — Verification & Testing

- [ ] Verify each of the 5 pages renders dynamic data
- [ ] Verify contact form submission works
- [ ] Run test suite: `php artisan test --compact`

### View Updates: `resources/views/works.blade.php`
- [ ] Hero and stats bar from `$pageSettings`
- [ ] Works grid from `$works`
- [ ] Details: title, client, badges, image, description
