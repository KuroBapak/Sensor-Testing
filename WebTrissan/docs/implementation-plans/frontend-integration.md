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

## Phase 2 — Home Page (`/`)

### Controller: `HomeController@index`
Query: `GlobalSetting::current()`, `PageSetting::forPage('landing')`, active `Service`s with clusters, featured `Work`s (take 6), `CorporateAccreditation`s.

### View Updates: `resources/views/home.blade.php`
- [ ] Hero slides from `$pageSettings->hero_slides`
- [ ] Trust card from `$pageSettings->trust_card`
- [ ] Service cards from `$services`
- [ ] Works grid from `$featuredWorks`
- [ ] Company stats from `$globalSettings->company_stats`
- [ ] Footer from `$globalSettings`

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
