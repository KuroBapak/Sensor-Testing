# Implementation Plan: Featured Works Modal Redirect

## Objective
When user clicks a featured works card on landing page (home.blade.php), redirect to /works page and automatically open the modal for that specific project.

## Source PRD / User Request
User requested:
- Featured works cards on landing page should redirect to `/works` page
- After redirect, automatically open the modal popup for the clicked project
- Example: clicking "Turbine Gearbox Shaft" → redirect to `/works` → modal for "Turbine Gearbox Shaft" opens automatically

## Acceptance Criteria
1. Clicking any featured works card in `home.blade.php` redirects to `/works#project-slug`
2. Opening `/works#project-slug` automatically finds the matching project card and opens its modal
3. If no hash or invalid hash is provided, `/works` behaves normally (no modal opened)
4. Modal can be closed and all normal works page functionality still works

## Featured Works Projects (from home.blade.php)
- TURBINE GEARBOX SHAFTS → `/works#turbine-gearbox-shafts`
- HIGH-PRESSURE PIPE HUBS → `/works#high-pressure-pipe-hubs`
- INDUSTRIAL ROBOTIC GANTRIES → `/works#industrial-robotic-gantries`
- HYDRAULIC MANIFOLD BLOCKS → `/works#hydraulic-manifold-blocks`
- HEAVY LATTICE MAST BOOMS → `/works#heavy-lattice-mast-booms`
- RPM BUZZER MODULAR SENSOR → `/works#rpm-buzzer-modular-sensor`

## Phases
- [x] **Phase 1: Add data-id attributes to project cards in works.blade.php**
  - Confirmed `data-title` exists on all cards in `works.blade.php` (e.g. "Turbine Gearbox Shafts")
  - Slugs match 1-to-1 with home.blade.php hashes

- [x] **Phase 2: Add hash-based modal opening logic in works.blade.php**
  - On page load, read `window.location.hash`
  - Find card with matching `data-title` (slugified)
  - Call `populateAndOpenModalFromCard(targetCard)` to open the modal
  - Handle singular/plural tolerance

- [x] **Phase 3: Verify featured works links in home.blade.php**
  - All 6 featured works cards have `onclick="window.location.href='/works#{slug}'"`
  - All 6 cards have `<a href="/works#{slug}">` buttons

- [x] **Phase 4: Verification**
  - Pest tests passed: 2 passed (2 assertions)
  - Code formatted with Pint (`vendor/bin/pint --dirty --format agent`)
  - Compiled views cleared (`php artisan view:clear`)
  - Clean `git diff --check`

