# Graph Report - DFM  (2026-09-30)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 1392 nodes · 3560 edges · 97 communities (54 shown, 43 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS · INFERRED: 5 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `3199e2b7`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- react
- User
- alarms.tsx
- Controller
- Illuminate\Database\Eloquent\Model
- dependencies
- Illuminate\Http\Request
- BackupSetting
- package.json
- app-sidebar.tsx
- Site
- dropdown-menu.tsx
- use-appearance.tsx
- sidebar.tsx
- AnomalyDetected
- @inertiajs/react
- PasswordValidationRules
- cn
- index.ts
- Tank
- Illuminate\Http\JsonResponse
- UserController.php
- TankLevelReading
- bootstrap/app.php
- app-header.tsx
- components.json
- compilerOptions
- AnomalyLog
- HardwareDevice
- Geofence
- AlarmLog
- SecurityTest.php
- ProfileController.php
- FortifyServiceProvider
- scripts
- Illuminate\Database\Schema\Blueprint
- RfidTag
- FuelMonitoringController.php
- composer.json
- require-dev
- AlarmApiTest.php
- Illuminate\Database\Seeder
- RfidTest.php
- ReportExportTest.php
- require
- PasswordResetTest.php
- toggle-group.tsx
- ProfileValidationRules
- Illuminate\Database\Migrations\Migration
- Illuminate\Support\Facades\Schema
- UserFactory.php
- devDependencies
- optionalDependencies
- vite.config.ts
- config
- scripts
- alert.tsx
- use-permission.tsx
- EdgeIngestionAuthTest.php
- global.d.ts
- psr-4
- laravel
- 0001_01_01_000001_create_cache_table.php
- 2026_09_22_062545_create_roles_and_permissions_tables.php
- 2026_09_22_062552_add_role_id_to_users_table.php
- 2026_09_29_202636_add_test_backup_timestamp_to_backup_settings_table.php
- collapsible.tsx
- use-clipboard.ts
- 2026_09_17_031611_create_main_tank_logs_table.php
- 2026_09_17_031612_create_mobile_tank_logs_table.php
- 2026_09_22_062551_create_sites_table.php
- 2026_09_22_072008_create_tanks_table.php
- 2026_09_22_072010_create_rfid_tags_table.php
- 2026_09_22_072012_create_tank_geofence_states_table.php
- 2026_09_22_072013_create_backup_settings_table.php
- 2026_09_22_072014_create_backup_runs_table.php
- 2026_09_22_072138_create_tank_level_readings_table.php
- 2026_09_22_072138_create_transactions_table.php
- 2026_09_22_072139_create_anomaly_logs_table.php
- 2026_09_24_040059_create_anomaly_reads_table.php
- 2026_09_24_040202_create_personal_access_tokens_table.php
- icon.tsx

## God Nodes (most connected - your core abstractions)
1. `cn()` - 123 edges
2. `User` - 102 edges
3. `Site` - 73 edges
4. `react` - 59 edges
5. `Button()` - 52 edges
6. `Tank` - 51 edges
7. `Role` - 45 edges
8. `Controller` - 45 edges
9. `@inertiajs/react` - 44 edges
10. `AnomalyLog` - 40 edges

## Surprising Connections (you probably didn't know these)
- `{closure#1}()` --calls--> `User`  [EXTRACTED]
  tests/Feature/Admin/BackupTest.php → app/Models/User.php
- `{closure#4}()` --calls--> `User`  [EXTRACTED]
  tests/Feature/Admin/GeofenceTest.php → app/Models/User.php
- `{closure#2}()` --calls--> `User`  [EXTRACTED]
  tests/Feature/Admin/RfidTest.php → app/Models/User.php
- `{closure#1}()` --calls--> `User`  [EXTRACTED]
  tests/Feature/Admin/SensitivityTest.php → app/Models/User.php
- `{closure#1}()` --calls--> `User`  [EXTRACTED]
  tests/Feature/Admin/SiteSettingsTest.php → app/Models/User.php

## Import Cycles
- None detected.

## Communities (97 total, 43 thin omitted)

### Community 0 - "react"
Cohesion: 0.06
Nodes (97): leaflet, leaflet-draw, lucide-react, @radix-ui/react-slot, react, react-leaflet, DeleteUser(), createTankDivIcon() (+89 more)

### Community 1 - "User"
Cohesion: 0.06
Nodes (27): User, {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#8}() (+19 more)

### Community 2 - "alarms.tsx"
Cohesion: 0.09
Nodes (37): recharts, AlarmRow, AlarmTable(), AlarmTableProps, ANOMALY_TYPE_BADGES, getAnomalyBadge(), STATUS_STYLES, StatusValue (+29 more)

### Community 3 - "Controller"
Cohesion: 0.08
Nodes (11): RfidController, SensitivityController, SiteSettingController, TankController, VendorFillController, RfidAuthController, Controller, GeofenceController (+3 more)

### Community 4 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.08
Nodes (7): AnomalyRead, ExportLog, RolePermission, TankGeofenceState, Transaction, {closure#2}(), {closure#3}()

### Community 5 - "dependencies"
Cohesion: 0.05
Nodes (39): dependencies, class-variance-authority, clsx, concurrently, @inertiajs/react, @inertiajs/vite, laravel-echo, laravel-vite-plugin (+31 more)

### Community 6 - "Illuminate\Http\Request"
Cohesion: 0.11
Nodes (12): {closure#1}(), {closure#10}(), {closure#3}(), {closure#4}(), {closure#6}(), {closure#7}(), {closure#9}(), ReportExportController (+4 more)

### Community 7 - "BackupSetting"
Cohesion: 0.10
Nodes (5): BackupController, RunBackupJob, BackupRun, BackupSetting, BackupStorageService

### Community 8 - "package.json"
Cohesion: 0.06
Nodes (33): private, $schema, type, babel-plugin-react-compiler, clsx, concurrently, laravel-echo, @laravel/multiplex (+25 more)

### Community 9 - "app-sidebar.tsx"
Cohesion: 0.13
Nodes (23): AppSidebar(), NavFooter(), NavMain(), Separator(), SidebarContent(), SidebarFooter(), SidebarGroup(), SidebarGroupContent() (+15 more)

### Community 10 - "Site"
Cohesion: 0.13
Nodes (18): Role, Site, {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#1}() (+10 more)

### Community 11 - "dropdown-menu.tsx"
Cohesion: 0.12
Nodes (21): @radix-ui/react-dropdown-menu, NavUser(), DropdownMenu(), DropdownMenuCheckboxItem(), DropdownMenuContent(), DropdownMenuGroup(), DropdownMenuItem(), DropdownMenuLabel() (+13 more)

### Community 12 - "use-appearance.tsx"
Cohesion: 0.13
Nodes (21): sonner, AppearanceToggleTab(), Toaster(), Appearance, applyTheme(), getStoredAppearance(), handleSystemThemeChange(), initializeTheme() (+13 more)

### Community 13 - "sidebar.tsx"
Cohesion: 0.12
Nodes (24): Sheet(), SheetContent(), SheetDescription(), SheetFooter(), SheetHeader(), SheetOverlay(), SheetPortal(), SheetTitle() (+16 more)

### Community 14 - "AnomalyDetected"
Cohesion: 0.16
Nodes (4): AlarmCreated, AnomalyDetected, FleetPositionUpdated, TankReadingReceived

### Community 15 - "@inertiajs/react"
Cohesion: 0.13
Nodes (12): @inertiajs/react, withApp(), AppLogo(), AppLogoIcon(), PlaceholderPattern(), PlaceholderPatternProps, TooltipProvider(), AuthSimpleLayout() (+4 more)

### Community 16 - "PasswordValidationRules"
Cohesion: 0.13
Nodes (6): ResetUserPassword, PasswordValidationRules, SecurityController, PasswordUpdateRequest, TwoFactorAuthenticationRequest, {closure#1}()

### Community 17 - "cn"
Cohesion: 0.17
Nodes (22): @radix-ui/react-navigation-menu, Breadcrumbs(), Breadcrumb(), BreadcrumbEllipsis(), BreadcrumbItem(), BreadcrumbLink(), BreadcrumbList(), BreadcrumbPage() (+14 more)

### Community 18 - "index.ts"
Cohesion: 0.18
Nodes (13): AppContent(), Props, AppShell(), Props, AppSidebarHeader(), SidebarInset(), SidebarProvider(), AppHeaderLayout() (+5 more)

### Community 19 - "Tank"
Cohesion: 0.12
Nodes (4): Tank, {closure#1}(), {closure#2}(), {closure#3}()

### Community 20 - "Illuminate\Http\JsonResponse"
Cohesion: 0.13
Nodes (5): AlarmApiController, GeofenceApiController, RfidSyncController, ScanRejectionController, TransactionIngestionController

### Community 21 - "UserController.php"
Cohesion: 0.13
Nodes (5): {closure#1}(), RoleController, {closure#1}(), UserController, Permissions

### Community 22 - "TankLevelReading"
Cohesion: 0.19
Nodes (5): AvlIngestionController, SiteReadingController, CheckGeofenceJob, DetectAnomalyJob, TankLevelReading

### Community 23 - "bootstrap/app.php"
Cohesion: 0.15
Nodes (6): CheckPermission, HandleAppearance, VerifyDeviceToken, {closure#1}(), {closure#2}(), {closure#3}()

### Community 24 - "app-header.tsx"
Cohesion: 0.25
Nodes (14): AppHeader(), mainNavItems, Props, rightNavItems, Avatar(), AvatarFallback(), AvatarImage(), Tooltip() (+6 more)

### Community 25 - "components.json"
Cohesion: 0.11
Nodes (17): aliases, components, hooks, lib, ui, utils, iconLibrary, rsc (+9 more)

### Community 26 - "compilerOptions"
Cohesion: 0.12
Nodes (15): compilerOptions, allowJs, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, module, moduleResolution (+7 more)

### Community 27 - "AnomalyLog"
Cohesion: 0.16
Nodes (4): AnomalyLog, {closure#1}(), {closure#2}(), {closure#3}()

### Community 28 - "HardwareDevice"
Cohesion: 0.17
Nodes (3): HardwareController, HardwareDevice, {closure#1}()

### Community 29 - "Geofence"
Cohesion: 0.17
Nodes (7): Geofence, PlaceholderDataSeeder, {closure#1}(), {closure#2}(), {closure#4}(), {closure#7}(), {closure#8}()

### Community 30 - "AlarmLog"
Cohesion: 0.20
Nodes (5): HandleInertiaRequests, AlarmLog, MainTankLog, MobileTankLog, FuelMonitoringSeeder

### Community 31 - "SecurityTest.php"
Cohesion: 0.18
Nodes (7): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), TestCase

### Community 34 - "scripts"
Cohesion: 0.15
Nodes (13): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+5 more)

### Community 35 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.23
Nodes (6): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#3}()

### Community 36 - "RfidTag"
Cohesion: 0.21
Nodes (5): {closure#1}(), RfidTag, {closure#2}(), {closure#3}(), {closure#11}()

### Community 38 - "composer.json"
Cohesion: 0.17
Nodes (11): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+3 more)

### Community 39 - "require-dev"
Cohesion: 0.17
Nodes (12): require-dev, fakerphp/faker, larastan/larastan, laravel/boost, laravel/pail, laravel/pao, laravel/pint, laravel/sail (+4 more)

### Community 41 - "Illuminate\Database\Seeder"
Cohesion: 0.23
Nodes (3): DatabaseSeeder, RolesAndPermissionsSeeder, SiteSeeder

### Community 42 - "RfidTest.php"
Cohesion: 0.17
Nodes (5): {closure#1}(), {closure#2}(), {closure#3}(), {closure#6}(), {closure#7}()

### Community 43 - "ReportExportTest.php"
Cohesion: 0.17
Nodes (4): {closure#1}(), {closure#12}(), {closure#13}(), {closure#14}()

### Community 44 - "require"
Cohesion: 0.18
Nodes (11): require, inertiajs/inertia-laravel, laravel/chisel, laravel/fortify, laravel/framework, laravel/reverb, laravel/sanctum, laravel/tinker (+3 more)

### Community 45 - "PasswordResetTest.php"
Cohesion: 0.18
Nodes (4): {closure#3}(), {closure#4}(), {closure#6}(), {closure#8}()

### Community 46 - "toggle-group.tsx"
Cohesion: 0.29
Nodes (8): class-variance-authority, @radix-ui/react-toggle, @radix-ui/react-toggle-group, ToggleGroup(), ToggleGroupContext, ToggleGroupItem(), Toggle(), toggleVariants

### Community 51 - "devDependencies"
Cohesion: 0.25
Nodes (8): devDependencies, babel-plugin-react-compiler, @laravel/vite-plugin-wayfinder, @rolldown/plugin-babel, @types/leaflet, @types/leaflet-draw, @types/node, vite-plus

### Community 52 - "optionalDependencies"
Cohesion: 0.25
Nodes (8): optionalDependencies, @laravel/multiplex, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc, @rollup/rollup-linux-x64-gnu, @rollup/rollup-win32-x64-msvc, @tailwindcss/oxide-linux-x64-gnu, @tailwindcss/oxide-win32-x64-msvc

### Community 53 - "vite.config.ts"
Cohesion: 0.25
Nodes (7): @inertiajs/vite, laravel-vite-plugin, @laravel/vite-plugin-wayfinder, @rolldown/plugin-babel, @tailwindcss/vite, vite-plus, @vitejs/plugin-react

### Community 54 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 55 - "scripts"
Cohesion: 0.29
Nodes (7): scripts, build, build:ssr, check, check:fix, dev, types:check

### Community 56 - "alert.tsx"
Cohesion: 0.62
Nodes (5): AlertError(), Alert(), AlertDescription(), AlertTitle(), alertVariants

### Community 58 - "EdgeIngestionAuthTest.php"
Cohesion: 0.48
Nodes (6): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), transactionPayload()

### Community 59 - "global.d.ts"
Cohesion: 0.40
Nodes (5): Auth, InertiaConfig, @inertiajs/core, InputHTMLAttributes, react

### Community 60 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 61 - "laravel"
Cohesion: 0.40
Nodes (5): extra, laravel, post-create-project, dont-discover, installer

### Community 68 - "use-clipboard.ts"
Cohesion: 0.40
Nodes (3): CopiedValue, CopyFn, UseClipboardReturn

## Knowledge Gaps
- **228 isolated node(s):** `FleetMapLeafletProps`, `Props`, `Geofence`, `Props`, `Props` (+223 more)
  These have ≤1 connection - possible missing edges. (Counts symbols only; 446 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **43 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `Illuminate\Database\Eloquent\Model`, `AlarmApiTest.php`, `Illuminate\Database\Seeder`, `Site`, `RfidTest.php`, `ReportExportTest.php`, `PasswordResetTest.php`, `ProfileValidationRules`, `PasswordValidationRules`, `UserFactory.php`, `Tank`, `UserController.php`, `AnomalyLog`, `Geofence`, `SecurityTest.php`?**
  _High betweenness centrality (0.043) - this node is a cross-community bridge._
- **Why does `react` connect `react` to `alarms.tsx`, `use-clipboard.ts`, `package.json`, `app-sidebar.tsx`, `dropdown-menu.tsx`, `use-appearance.tsx`, `sidebar.tsx`, `toggle-group.tsx`, `@inertiajs/react`, `cn`, `index.ts`, `alert.tsx`, `app-header.tsx`?**
  _High betweenness centrality (0.041) - this node is a cross-community bridge._
- **Why does `Site` connect `Site` to `Controller`, `RfidTag`, `FuelMonitoringController.php`, `Illuminate\Database\Eloquent\Model`, `BackupSetting`, `AlarmApiTest.php`, `Illuminate\Database\Seeder`, `RfidTest.php`, `ReportExportTest.php`, `Tank`, `Illuminate\Http\JsonResponse`, `TankLevelReading`, `EdgeIngestionAuthTest.php`, `AnomalyLog`, `HardwareDevice`, `Geofence`, `AlarmLog`?**
  _High betweenness centrality (0.029) - this node is a cross-community bridge._
- **What connects `FleetMapLeafletProps`, `Props`, `Geofence` to the rest of the system?**
  _228 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `react` be split into smaller, more focused modules?**
  _Cohesion score 0.06452901808011188 - nodes in this community are weakly interconnected._
- **Should `User` be split into smaller, more focused modules?**
  _Cohesion score 0.06448979591836734 - nodes in this community are weakly interconnected._
- **Should `alarms.tsx` be split into smaller, more focused modules?**
  _Cohesion score 0.08734693877551021 - nodes in this community are weakly interconnected._