# Filament Admin Panel Implementation — PRD v2.1 Alignment Plan

## Task Objective
Build and configure the Filament v3 admin panel to achieve full functional alignment with PRD v2.1. This includes navigation grouping, resource form and table enhancements, and fully implementing the three singleton pages (`ManageGlobalSettings`, `ManageAboutCompany`, and `ManagePageSettings`).

## Source PRD
`prd.md` — Unified CMS Architecture: Apex Industrial Engineering (v2.1)

## Status
- Overall Status: Complete
- Phase 1 (Navigation Grouping & Icons): Complete
- Phase 2 (Resource Forms & Tables Fixes): Complete
- Phase 3 (ManageGlobalSettings Page): Complete
- Phase 4 (ManageAboutCompany Page): Complete
- Phase 5 (ManagePageSettings Page): Complete

---

## Phase 1 — Navigation Grouping & Icons
Configure navigation groups, sorts, icons, and labels across all 11 resources and 3 singleton pages.

- [x] Update `AdminPanelProvider.php` with official navigation groups
- [x] Configure `ManageGlobalSettings` (⚙️ Global Data Settings, sort: 1)
- [x] Configure `CertificationCategoryResource` (⚙️ Global Data Settings, sort: 2)
- [x] Configure `PortfolioCategoryResource` (⚙️ Global Data Settings, sort: 3)
- [x] Configure `SpecialtyClusterResource` (⚙️ Global Data Settings, sort: 4)
- [x] Configure `SkillResource` (⚙️ Global Data Settings, sort: 5)
- [x] Configure `ManagePageSettings` (🏠 Landing Page, sort: 1)
- [x] Configure `ManageAboutCompany` (ℹ️ About Page, sort: 1)
- [x] Configure `ServiceClusterResource` (🔧 Works Page, sort: 1)
- [x] Configure `ServiceResource` (🔧 Works Page, sort: 2)
- [x] Configure `WorkResource` (🔧 Works Page, sort: 3)
- [x] Configure `CorporateAccreditationResource` (📜 License & Certification, sort: 1)
- [x] Configure `ProductCertificationResource` (📜 License & Certification, sort: 2)
- [x] Configure `PortfolioItemResource` (📁 Portfolio Page, sort: 1)
- [x] Configure `PersonnelResource` (📁 Portfolio Page, sort: 2)
- [x] Configure `ContactSubmissionResource` (📬 Inquiries, sort: 1)

Exit Criteria: Admin sidebar displays 7 distinct groups with correct order, labels, and icons.

---

## Phase 2 — Fix All Resource Forms & Tables
Upgrade raw scaffolded forms and tables to production-grade UX.

- [x] `WorkResource`: relationship select `service_title`, `gallery_images` FileUpload, `highlight_tags` & `quality_standards` repeaters, `full_description` RichEditor, `production_date` required, sectioned form, table columns and filters.
- [x] `ServiceResource`: relationship select `cluster_name`, table column.
- [x] `ProductCertificationResource`: relationship select `work_title`, `spec_rows` repeater, `certificate_pdf` FileUpload, table column.
- [x] `ContactSubmissionResource`: relationship select `service_title`, `status` select, table column and filters.
- [x] `PersonnelResource`: `skills` multi-select relationship, table column fixes.
- [x] `CorporateAccreditationResource`: table column type fixes.
- [x] `PortfolioItemResource`: table column type fixes.

Exit Criteria: All collection forms and tables use human-readable options, validated repeaters, and appropriate inputs matching PRD constraints.

---

## Phase 3 — Build ManageGlobalSettings Page
Implement singleton configuration page for site-wide settings.

- [x] Form schema for PRD Section 4.1: Brand, Company Stats (4 items), Contact Info (phones, emails), Locations, Social Media, Notification Email.
- [x] Mount and save methods linked to `GlobalSetting::current()`.
- [x] Update blade view with form and action buttons.

Exit Criteria: Admin can modify and persist all global branding, contact, stats, and social media data.

---

## Phase 4 — Build ManageAboutCompany Page
Implement singleton configuration page for company profile and about page.

- [x] Form schema for PRD Section 4.3: Hero Profile, Vision/Mission/Values (3 cards), Facilities, Milestones, Org Chart.
- [x] Mount and save methods linked to `AboutCompany::current()`.
- [x] Update blade view with form and action buttons.

Exit Criteria: Admin can modify and persist all about page data including facilities, milestones, and org chart.

---

## Phase 5 — Build ManagePageSettings Page
Implement multi-page hero, SEO, and stat settings per page slug.

- [x] Implement tabbed layout for `landing`, `works`, `portfolio`, and `licenses`.
- [x] Form schemas for each tab per PRD Section 4.2 (min/max repeaters, trust card, about teaser, registry tags, corporate highlights, stats).
- [x] Load and save handlers for each `PageSetting` record mapped per statePath.
- [x] Blade view with `<x-filament-panels::form>` and action buttons.
- [x] Automated test coverage in `FilamentAdminTest` verifying access and multi-page state persistence.

Exit Criteria: Admin can manage individual page settings across all four core pages.
