# Implementation Plan: Category Redirect from Landing to Works Page

## Objective
Enable service cards and cluster cards on landing page (`home.blade.php`) to redirect to `/works` page with automatic category filtering.

## Source PRD / User Request
User requested:
- The service cards below the hero on the landing page and the service cluster cards should redirect to the works page (`/works`) when clicked.
- The works page should automatically switch to the category that was clicked.

## Acceptance Criteria
1. Clicking any service card in the carousel redirects to `/works?category={slug}`.
2. Clicking any service link in the cluster cards redirects to `/works?category={slug}`.
3. Opening `/works?category={slug}` automatically activates that category filter tab and scrolls smoothly to the filter section.
4. If no category or an invalid category is provided, `/works` defaults to "all".
5. No broken links like `works.html` on the landing page.

## Category Mapping
- `mechanical-electrical`
- `digital-automation`
- `jigs-fixtures`
- `unload-robot-chucking`
- `material-handling`
- `distribution-panel`
- `electrical-installation`
- `grease-system`
- `electrical-equipment-supplier`
- `general-supplier`
- `special-construction`
- `labor-provision`

## Phases
- [x] **Phase 1: Update Works Page to handle category query parameter**
  - Read `?category=` from `window.location.search`.
  - On page load, find the matching `.filter-tab-btn[data-filter="{slug}"]`.
  - Programmatically activate it and filter the cards.
  - Smoothly scroll to the filter section on auto-select.
- [x] **Phase 2: Update Landing Page Carousel Cards**
  - Replaced all 12 `onclick="window.location.href='works.html'"` with `/works?category={slug}`.
- [x] **Phase 3: Update Landing Page Cluster Cards**
  - Replaced all 12 `<a href="#works" onclick="selectCategory('...')">` with `<a href="/works?category={slug}">`.
  - Fixed explore button `<a href="#works">` to `<a href="/works">`.
- [x] **Phase 4: Verification**
  - Verified all 12 services in carousel match category slugs.
  - Verified all 12 cluster cards match category slugs.
  - Cleared compiled view cache (`php artisan view:clear`).
  - Ran Pint formatter (`vendor/bin/pint --dirty --format agent`).
  - Ran Pest tests (`php artisan test --compact`) -> All passed.

