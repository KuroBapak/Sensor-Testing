# PRD v2.1 Schema Alignment — Implementation Plan

## Task Objective
Align the Laravel CMS database schema (models + migrations + seeders) with PRD v2.1 for the Apex Industrial Engineering website.

## Source PRD
`prd.md` — PRD v2.1

---

## Phase 1 — Schema Alignment (Migrations + Models)

### Status: ✅ Complete — Schema Already Aligned

A thorough comparison of the **live database schema** (via `DESCRIBE` queries) against every PRD §4 section confirms all required columns, types, indexes, and foreign keys are **already present** in the database.

#### Verified Tables & PRD Alignment

All 16 tables verified as aligned with PRD v2.1:
- ✅ `global_settings` — All 20 PRD fields present
- ✅ `page_settings` — Singleton per page key with all PRD fields
- ✅ `about_company` — Singleton with complete PRD structure
- ✅ `service_clusters`, `services` — Complete with PRD fields
- ✅ `works` — All PRD fields including tolerance/scale metrics
- ✅ `certification_categories`, `corporate_accreditations` — Aligned
- ✅ `product_certifications` — All PRD fields present
- ✅ `portfolio_categories`, `portfolio_items` — Complete
- ✅ `specialty_clusters`, `skills`, `personnel` — Complete with M2M pivot
- ✅ `contact_submissions` — Has service FK and status field

All 16 models verified with correct `$guarded`, `casts()`, and relationships.

---

## Phase 2 — Seeders

### Status: ✅ Complete — Seeders Already Aligned

All 8 seeders match PRD §5.3 seed data. `DatabaseSeeder` calls all in correct order.

---

## Phase 3 — Verification Tests

### Status: ✅ Complete

Created comprehensive test suite in `tests/Feature/SchemaAlignmentTest.php`:

- [x] Test: All models can be instantiated and queried (15 models)
- [x] Test: Seeders populate database correctly (15 assertions on counts)
- [x] Test: Model relationships work correctly (17 relationship assertions)
- [x] Test: Model casts work correctly (26 cast type assertions)
- [x] Test: PRD seed data structure matches expectations (46 structure assertions)

**Test Results**: ✅ 5 tests, 119 assertions, all passing

Configuration updates:
- Updated `phpunit.xml` to use MySQL instead of SQLite (pdo_sqlite not installed)
- Created `laravel_test` database for isolated test runs
- Added `RefreshDatabase` trait to test suite

---

## Phase 4 — Remaining Work (Future)

### 4a. Filament Admin Panel
- [ ] Install Filament PHP (`filament/filament`)
- [ ] Create Filament admin panel provider
- [ ] Create Filament resources for all collection models
- [ ] Create Filament pages for singleton models (GlobalSetting, AboutCompany)
- [ ] Create page-specific settings Filament pages

### 4b. Frontend Integration
- [ ] Create controllers to pass CMS data to views
- [ ] Wire up all 5 page views with dynamic data from DB
- [ ] Replace static content with dynamic CMS data

---

## Validation Summary

✅ **Migration status**: All 19 migrations ran successfully. Live DB matches PRD requirements.  
✅ **Seeder status**: All 8 seeders produce correct PRD-compliant data.  
✅ **Model status**: All 16 models have correct casts, relationships, and conventions.  
✅ **Test coverage**: Comprehensive test suite validates schema, relationships, casts, and seed data structure.  
✅ **Code quality**: All code formatted with Laravel Pint.

## Conclusion

**The schema alignment task (Phase 1–3) is complete.** The database schema, models, seeders, and verification tests are all aligned with PRD v2.1. The initial gap analysis was based on comparing *original base migration files* before subsequent alignment migrations were applied. The actual database has been fully aligned with the PRD in prior work.

The remaining work is **Filament admin panel installation** and **frontend integration**, which are separate future phases.

## Files Modified/Created

### Created
- `docs/implementation-plans/prd-v2-schema-alignment.md` (this file)
- `tests/Feature/SchemaAlignmentTest.php` (119 assertions)

### Modified
- `phpunit.xml` (MySQL test database configuration)

### Verified Existing (No Changes Needed)
- 16 migration files (all PRD-aligned)
- 16 model files (all PRD-aligned)
- 8 seeder files (all PRD-aligned)

