# Filament Admin Panel Installation — Implementation Plan

## Task Objective
Install and configure Filament v3 admin panel for managing all CMS content defined in PRD v2.1.

## Source PRD
`prd.md` — PRD v2.1

## Prerequisites
- ✅ Database schema aligned with PRD v2.1
- ✅ All 16 models created with correct casts and relationships
- ✅ All 8 seeders populated with PRD-compliant data
- ✅ Test suite validates schema integrity (119 assertions passing)

---

## Phase 1 — Install Filament

### Checklist
- [ ] Run `composer require filament/filament:"^3.0"` to install Filament v3
- [ ] Run `php artisan filament:install --panels` to scaffold admin panel
- [ ] Run `php artisan make:filament-user` to create admin user
- [ ] Verify Filament panel accessible at `/admin`
- [ ] Configure Filament panel in `app/Providers/Filament/AdminPanelProvider.php`

### Exit Criteria
- Filament v3 installed successfully
- Admin panel accessible at `/admin` route
- Admin user can log in to Filament dashboard

---

## Phase 2 — Collection Resources

Create Filament resources for all collection models with proper forms, tables, and relationships.

### 2a. Service Management
- [ ] `php artisan make:filament-resource ServiceCluster --generate`
- [ ] `php artisan make:filament-resource Service --generate`
- [ ] Configure ServiceCluster form: `cluster_name`, `cluster_tag`, `cluster_icon`, `sort_order`
- [ ] Configure Service form: `service_name`, `short_title`, `description`, `service_icon_svg`, `is_active`, `sort_order`, BelongsTo `service_cluster`
- [ ] Add RelationManager: ServiceCluster → Services

### 2b. Works Management
- [ ] `php artisan make:filament-resource Work --generate`
- [ ] Configure form fields: `title`, `work_slug`, `featured_image`, `gallery_images` (repeater), `top_badge`, `bottom_badge`, `serial_number`, `client_name`, `client_sector`, `project_year`, `short_description`, `full_description`, `highlight_tags` (repeater), `quality_standards` (repeater), `tolerance_value`, `scale_value`, `production_date`, `is_featured`, `sort_order`, BelongsTo `service`
- [ ] Configure table columns: `featured_image`, `title`, `client_name`, `is_featured`, `sort_order`
- [ ] Add filters: `is_featured`, `service_id`

### 2c. Certification Management
- [ ] `php artisan make:filament-resource CertificationCategory --generate`
- [ ] `php artisan make:filament-resource CorporateAccreditation --generate`
- [ ] `php artisan make:filament-resource ProductCertification --generate`
- [ ] Configure CertificationCategory form: `name`, `slug`, `sort_order`
- [ ] Configure CorporateAccreditation form: `title`, `modal_title`, `registration_id`, `certificate_image`, `issuing_body`, `validity_date`, `scope_detail`, `sort_order`, BelongsTo `certification_category`

### 2d. Portfolio Management
- [ ] `php artisan make:filament-resource PortfolioCategory --generate`
- [ ] `php artisan make:filament-resource PortfolioItem --generate`
- [ ] Configure PortfolioCategory form: `name`, `slug`, `sort_order`
- [ ] Configure PortfolioItem form: `title`, `industry_tag`, `image`, `description`, `project_year`, `sort_order`, BelongsTo `portfolio_category`

### 2e. Personnel Management
- [ ] `php artisan make:filament-resource SpecialtyCluster --generate`
- [ ] `php artisan make:filament-resource Skill --generate`
- [ ] `php artisan make:filament-resource Personnel --generate`
- [ ] Configure SpecialtyCluster form: `name`, `sort_order`
- [ ] Configure Skill form: `skill_name`
- [ ] Configure Personnel form: `name`, `registration_id`, `position`, `photo`, `technical_focus`, `qualification_rank`, `status`, `sort_order`, BelongsTo `specialty_cluster`, BelongsToMany `skills`

### 2f. Contact Submissions
- [ ] `php artisan make:filament-resource ContactSubmission --generate`
- [ ] Configure form: `name`, `email`, `phone`, `message`, `status` (select: new/in_progress/resolved/closed), BelongsTo `service` (nullable)
- [ ] Configure table: `name`, `email`, `phone`, `status`, `created_at`
- [ ] Add filters: `status`, `service_id`, `created_at` (date range)
- [ ] Make form read-only (contact submissions should not be edited, only status updated)

---

## Phase 3 — Singleton Pages

Create custom Filament pages for singleton models that only have one record.

### 3a. Global Settings Page
- [ ] `php artisan make:filament-page ManageGlobalSettings`
- [ ] Load `GlobalSetting::current()` in mount method
- [ ] Create form with sections: Company Info, Contact Info, Social Media, Footer, Statistics, Map

### 3b. About Company Page
- [ ] `php artisan make:filament-page ManageAboutCompany`
- [ ] Load `AboutCompany::current()` in mount method
- [ ] Create form with sections: Hero, Overview, Vision/Mission/Values, Facilities, Milestones, Organization

### 3c. Page Settings Manager
- [ ] `php artisan make:filament-page ManagePageSettings`
- [ ] Add tabs for each page: Landing, Works, Portfolio, Licenses, About
- [ ] Load `PageSetting::forPage($page)` per tab
- [ ] Create forms per page type

---

## Phase 4 — Navigation & Polish

- [ ] Organize navigation groups: Content, Certifications, People, Submissions, Settings
- [ ] Add icons for resources and pages
- [ ] Test all CRUD operations
- [ ] Test file uploads, repeaters, and relationships

---

## Validation Summary

- **Installation**: Filament v3 installed, admin panel accessible
- **Resources**: 11 collection resources created with forms, tables, filters
- **Pages**: 3 singleton pages created for global/about/page settings
- **Navigation**: Organized into logical groups with icons
- **Testing**: All CRUD operations verified, test suite passing

- [ ] Configure ProductCertification form: `title`, `slug`, `category_tag`, `category_icon`, `card_icon`, `model_spec_identifier`, `certificate_number`, `issuing_body`, `spec_rows` (repeater: label/value), `scope_description`, `validity_date`, `certificate_image`, `certificate_pdf`, `is_featured`, `sort_order`, BelongsTo `work`
