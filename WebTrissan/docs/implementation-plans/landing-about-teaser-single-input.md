# Implementation Plan: Landing About Teaser Single Input Field

## Task Objective
Consolidate the two separate paragraph input fields (`about_teaser.paragraph_1` and `about_teaser.paragraph_2`) in Filament Page Settings (`ManagePageSettings`) into a single input field (`about_teaser.description`), update the seeder and existing database record, render paragraphs cleanly on the landing page (`home.blade.php`) supporting multiple paragraphs via newlines while preserving backward compatibility, and update feature tests.

## Scope & Non-Goals
- Scope:
  - `app/Filament/Pages/ManagePageSettings.php`: Replace `about_teaser.paragraph_1` and `about_teaser.paragraph_2` with single `about_teaser.description` Textarea. Auto-combine legacy fields on mount.
  - `database/seeders/PageSettingSeeder.php`: Update `about_teaser` seeder data to use single `description` key.
  - Database: Migrate/update existing `page_settings` record for `landing` so `about_teaser` has `description`.
  - `resources/views/home.blade.php`: Consume `$aboutTeaser['description']` (with backward compatibility for `paragraph_1`/`paragraph_2`) and cleanly render paragraphs separated by newlines.
  - `tests/Feature/PageSettingsRenderingTest.php`: Update/add tests ensuring single field and legacy fallbacks work as expected.
- Non-Goals:
  - Do not alter unrelated sections of `ManagePageSettings` or `home.blade.php`.
  - Do not modify database table schemas.

## Acceptance Criteria
- [x] Filament CMS `ManagePageSettings` displays exactly one textarea field for the About Teaser description instead of two separate paragraph fields.
- [x] When loaded, `ManagePageSettings` fills `about_teaser.description` seamlessly, even if previously stored as `paragraph_1` and `paragraph_2`.
- [x] Landing page (`home.blade.php`) renders the About Teaser description cleanly with proper spacing, supporting single or multi-line paragraphs.
- [x] Backward compatibility is preserved if existing data or tests provide `paragraph_1` / `paragraph_2`.
- [x] Database seeder and existing DB row for `page_settings` are aligned.
- [x] Pint code formatting passes.
- [x] Relevant Pest feature tests pass.

## Phases

### Phase 1: Filament Page Settings Form Consolidation
- [x] Replace `about_teaser.paragraph_1` and `about_teaser.paragraph_2` with single `Textarea::make('about_teaser.description')` in `ManagePageSettings.php`.
- [x] In `mount()`, if `description` is not set but `paragraph_1` or `paragraph_2` are present, automatically concatenate them into `description`.

### Phase 2: Seeder & Database Record Update
- [x] Update `PageSettingSeeder.php` to define `description` in `about_teaser`.
- [x] Re-run seeder or update `page_settings` row for `landing`.

### Phase 3: Blade View Rendering
- [x] In `resources/views/home.blade.php`, resolve `$aboutDescription` from `about_teaser['description']`, falling back to `paragraph_1` + `paragraph_2`, and falling back to default text.
- [x] Render paragraphs cleanly using newline splitting and `<p>` tags with proper margin/spacing.

### Phase 4: Verification & Test Updates
- [x] Update `tests/Feature/PageSettingsRenderingTest.php` to test both `description` and backwards compatibility.
- [x] Run `vendor/bin/pest` on affected tests.
- [x] Run `vendor/bin/pint --dirty --format agent`.
- [x] Verify `git diff --check`.

## Status
- Complete

