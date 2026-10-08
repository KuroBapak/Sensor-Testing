# Graph Report - DFM  (2026-10-07)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 1394 nodes · 3578 edges · 94 communities (53 shown, 41 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS · INFERRED: 5 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `91c00baf`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- react
- alarms.tsx
- User
- ProfileController.php
- Site
- cn
- dependencies
- app-header.tsx
- Controller
- AnomalyLog
- BackupSetting
- package.json
- AnomalyDetected
- Inertia\Response
- use-appearance.tsx
- UserController.php
- Transaction
- Tank
- PasswordResetTest.php
- index.ts
- dropdown-menu.tsx
- app-sidebar.tsx
- RfidTag
- ReportExportTest.php
- components.json
- VendorFillTest.php
- utils.ts
- compilerOptions
- HardwareDevice
- TankLevelReading
- ReportExportController
- toggle-group.tsx
- ReportExportController.php
- AlarmLog
- FortifyServiceProvider
- scripts
- Illuminate\Database\Schema\Blueprint
- Closure
- composer.json
- require-dev
- AlarmApiTest.php
- require
- Illuminate\Database\Seeder
- breadcrumbs.tsx
- bootstrap/app.php
- Illuminate\Database\Migrations\Migration
- Illuminate\Support\Facades\Schema
- dashboard.tsx
- global.d.ts
- Illuminate\Http\Request
- UserFactory.php
- devDependencies
- optionalDependencies
- vite.config.ts
- config
- scripts
- psr-4
- laravel
- 0001_01_01_000001_create_cache_table.php
- 2026_09_22_062545_create_roles_and_permissions_tables.php
- 2026_09_22_062552_add_role_id_to_users_table.php
- 2026_09_29_202636_add_test_backup_timestamp_to_backup_settings_table.php
- collapsible.tsx
- use-clipboard.ts
- useIsMobile
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
2. `User` - 104 edges
3. `Site` - 73 edges
4. `react` - 59 edges
5. `Tank` - 54 edges
6. `Button()` - 52 edges
7. `Role` - 47 edges
8. `Controller` - 45 edges
9. `@inertiajs/react` - 44 edges
10. `AnomalyLog` - 40 edges

## Surprising Connections (you probably didn't know these)
- `{closure#3}()` --calls--> `Transaction`  [EXTRACTED]
  tests/Feature/Admin/VendorFillTest.php → app/Models/Transaction.php
- `{closure#12}()` --calls--> `Transaction`  [EXTRACTED]
  tests/Feature/ReportExportTest.php → app/Models/Transaction.php
- `{closure#2}()` --calls--> `Transaction`  [EXTRACTED]
  tests/Feature/ReportExportTest.php → app/Models/Transaction.php
- `{closure#1}()` --calls--> `User`  [EXTRACTED]
  tests/Feature/Admin/BackupTest.php → app/Models/User.php
- `{closure#4}()` --calls--> `User`  [EXTRACTED]
  tests/Feature/Admin/GeofenceTest.php → app/Models/User.php

## Import Cycles
- None detected.

## Communities (94 total, 41 thin omitted)

### Community 0 - "react"
Cohesion: 0.07
Nodes (96): @inertiajs/react, leaflet, leaflet-draw, lucide-react, @radix-ui/react-slot, react, react-leaflet, DeleteUser() (+88 more)

### Community 1 - "alarms.tsx"
Cohesion: 0.07
Nodes (41): laravel-echo, pusher-js, recharts, AlarmRow, AlarmTable(), AlarmTableProps, ANOMALY_TYPE_BADGES, getAnomalyBadge() (+33 more)

### Community 2 - "User"
Cohesion: 0.06
Nodes (27): User, {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#8}() (+19 more)

### Community 3 - "ProfileController.php"
Cohesion: 0.07
Nodes (10): ResetUserPassword, PasswordValidationRules, ProfileValidationRules, ProfileController, SecurityController, PasswordUpdateRequest, ProfileDeleteRequest, ProfileUpdateRequest (+2 more)

### Community 4 - "Site"
Cohesion: 0.09
Nodes (23): Role, Site, {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#1}() (+15 more)

### Community 5 - "cn"
Cohesion: 0.10
Nodes (37): @radix-ui/react-navigation-menu, CardFooter(), NavigationMenu(), NavigationMenuContent(), NavigationMenuIndicator(), NavigationMenuItem(), NavigationMenuLink(), NavigationMenuList() (+29 more)

### Community 6 - "dependencies"
Cohesion: 0.05
Nodes (39): dependencies, class-variance-authority, clsx, concurrently, @inertiajs/react, @inertiajs/vite, laravel-echo, laravel-vite-plugin (+31 more)

### Community 7 - "app-header.tsx"
Cohesion: 0.12
Nodes (24): @radix-ui/react-avatar, withApp(), AppHeader(), mainNavItems, Props, rightNavItems, AppLogo(), AppLogoIcon() (+16 more)

### Community 8 - "Controller"
Cohesion: 0.09
Nodes (9): AlarmApiController, GeofenceApiController, RfidAuthController, {closure#1}(), RfidSyncController, ScanRejectionController, Controller, GeofenceController (+1 more)

### Community 9 - "AnomalyLog"
Cohesion: 0.09
Nodes (4): AnomalyLog, AnomalyRead, RolePermission, TankGeofenceState

### Community 10 - "BackupSetting"
Cohesion: 0.10
Nodes (5): BackupController, RunBackupJob, BackupRun, BackupSetting, BackupStorageService

### Community 11 - "package.json"
Cohesion: 0.06
Nodes (32): private, $schema, type, babel-plugin-react-compiler, clsx, concurrently, @laravel/multiplex, lightningcss-linux-x64-gnu (+24 more)

### Community 12 - "AnomalyDetected"
Cohesion: 0.15
Nodes (4): AlarmCreated, AnomalyDetected, FleetPositionUpdated, TankReadingReceived

### Community 13 - "Inertia\Response"
Cohesion: 0.11
Nodes (6): SensitivityController, SiteSettingController, VendorFillController, {closure#1}(), MapController, FuelMonitoringController

### Community 14 - "use-appearance.tsx"
Cohesion: 0.14
Nodes (20): sonner, AppearanceToggleTab(), Toaster(), Appearance, applyTheme(), getStoredAppearance(), handleSystemThemeChange(), initializeTheme() (+12 more)

### Community 15 - "UserController.php"
Cohesion: 0.11
Nodes (6): {closure#1}(), RoleController, {closure#1}(), UserController, Permissions, RolesAndPermissionsSeeder

### Community 16 - "Transaction"
Cohesion: 0.11
Nodes (5): TransactionIngestionController, {closure#4}(), {closure#7}(), DetectAnomalyJob, Transaction

### Community 17 - "Tank"
Cohesion: 0.12
Nodes (5): TankController, Tank, {closure#1}(), {closure#2}(), {closure#3}()

### Community 18 - "PasswordResetTest.php"
Cohesion: 0.09
Nodes (11): {closure#3}(), {closure#4}(), {closure#6}(), {closure#8}(), {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}() (+3 more)

### Community 19 - "index.ts"
Cohesion: 0.17
Nodes (14): AppContent(), Props, AppShell(), Props, AppSidebarHeader(), SidebarInset(), SidebarProvider(), AppHeaderLayout() (+6 more)

### Community 20 - "dropdown-menu.tsx"
Cohesion: 0.13
Nodes (14): @radix-ui/react-dropdown-menu, DropdownMenuCheckboxItem(), DropdownMenuGroup(), DropdownMenuItem(), DropdownMenuLabel(), DropdownMenuRadioItem(), DropdownMenuSeparator(), DropdownMenuShortcut() (+6 more)

### Community 21 - "app-sidebar.tsx"
Cohesion: 0.24
Nodes (18): AppSidebar(), NavFooter(), NavMain(), NavUser(), DropdownMenu(), DropdownMenuContent(), DropdownMenuTrigger(), SidebarContent() (+10 more)

### Community 22 - "RfidTag"
Cohesion: 0.14
Nodes (10): RfidController, RfidTag, {closure#1}(), {closure#2}(), {closure#3}(), {closure#6}(), {closure#7}(), {closure#1}() (+2 more)

### Community 23 - "ReportExportTest.php"
Cohesion: 0.12
Nodes (8): ExportLog, {closure#1}(), {closure#11}(), {closure#12}(), {closure#13}(), {closure#14}(), {closure#2}(), {closure#3}()

### Community 24 - "components.json"
Cohesion: 0.11
Nodes (17): aliases, components, hooks, lib, ui, utils, iconLibrary, rsc (+9 more)

### Community 25 - "VendorFillTest.php"
Cohesion: 0.14
Nodes (9): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}() (+1 more)

### Community 26 - "utils.ts"
Cohesion: 0.19
Nodes (10): Props, Separator(), IsCurrentOrParentUrlFn, IsCurrentUrlFn, useCurrentUrl(), UseCurrentUrlReturn, WhenCurrentUrlFn, SettingsLayout() (+2 more)

### Community 27 - "compilerOptions"
Cohesion: 0.12
Nodes (15): compilerOptions, allowJs, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, module, moduleResolution (+7 more)

### Community 29 - "TankLevelReading"
Cohesion: 0.26
Nodes (4): AvlIngestionController, SiteReadingController, CheckGeofenceJob, TankLevelReading

### Community 31 - "toggle-group.tsx"
Cohesion: 0.24
Nodes (11): class-variance-authority, AlertError(), Alert(), AlertDescription(), AlertTitle(), alertVariants, ToggleGroup(), ToggleGroupContext (+3 more)

### Community 32 - "ReportExportController.php"
Cohesion: 0.19
Nodes (6): {closure#10}(), {closure#3}(), {closure#4}(), {closure#6}(), {closure#7}(), {closure#9}()

### Community 33 - "AlarmLog"
Cohesion: 0.20
Nodes (5): HandleInertiaRequests, AlarmLog, MainTankLog, MobileTankLog, FuelMonitoringSeeder

### Community 35 - "scripts"
Cohesion: 0.15
Nodes (13): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+5 more)

### Community 36 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.23
Nodes (6): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#3}()

### Community 37 - "Closure"
Cohesion: 0.29
Nodes (3): CheckPermission, HandleAppearance, VerifyDeviceToken

### Community 38 - "composer.json"
Cohesion: 0.17
Nodes (11): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+3 more)

### Community 39 - "require-dev"
Cohesion: 0.17
Nodes (12): require-dev, fakerphp/faker, larastan/larastan, laravel/boost, laravel/pail, laravel/pao, laravel/pint, laravel/sail (+4 more)

### Community 41 - "require"
Cohesion: 0.18
Nodes (11): require, inertiajs/inertia-laravel, laravel/chisel, laravel/fortify, laravel/framework, laravel/reverb, laravel/sanctum, laravel/tinker (+3 more)

### Community 42 - "Illuminate\Database\Seeder"
Cohesion: 0.25
Nodes (3): DatabaseSeeder, PlaceholderDataSeeder, SiteSeeder

### Community 43 - "breadcrumbs.tsx"
Cohesion: 0.47
Nodes (8): Breadcrumbs(), Breadcrumb(), BreadcrumbEllipsis(), BreadcrumbItem(), BreadcrumbLink(), BreadcrumbList(), BreadcrumbPage(), BreadcrumbSeparator()

### Community 44 - "bootstrap/app.php"
Cohesion: 0.28
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 47 - "dashboard.tsx"
Cohesion: 0.28
Nodes (3): PlaceholderPattern(), PlaceholderPatternProps, Dashboard()

### Community 48 - "global.d.ts"
Cohesion: 0.28
Nodes (7): Auth, Role, User, InertiaConfig, @inertiajs/core, InputHTMLAttributes, react

### Community 49 - "Illuminate\Http\Request"
Cohesion: 0.39
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

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

### Community 56 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 57 - "laravel"
Cohesion: 0.40
Nodes (5): extra, laravel, post-create-project, dont-discover, installer

### Community 64 - "use-clipboard.ts"
Cohesion: 0.40
Nodes (3): CopiedValue, CopyFn, UseClipboardReturn

### Community 65 - "useIsMobile"
Cohesion: 0.70
Nodes (4): getServerSnapshot(), isSmallerThanBreakpoint(), mediaQueryListener(), useIsMobile()

## Knowledge Gaps
- **228 isolated node(s):** `FleetMapLeafletProps`, `Geofence`, `Props`, `Props`, `RfidTagItem` (+223 more)
  These have ≤1 connection - possible missing edges. (Counts symbols only; 446 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **41 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `ProfileController.php`, `Site`, `AlarmApiTest.php`, `AnomalyLog`, `Illuminate\Database\Seeder`, `UserController.php`, `UserFactory.php`, `PasswordResetTest.php`, `RfidTag`, `ReportExportTest.php`, `VendorFillTest.php`?**
  _High betweenness centrality (0.046) - this node is a cross-community bridge._
- **What connects `FleetMapLeafletProps`, `Geofence`, `Props` to the rest of the system?**
  _228 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `react` be split into smaller, more focused modules?**
  _Cohesion score 0.0682323072040622 - nodes in this community are weakly interconnected._
- **Why does `react` connect `react` to `use-clipboard.ts`, `alarms.tsx`, `useIsMobile`, `cn`, `app-header.tsx`, `breadcrumbs.tsx`, `package.json`, `use-appearance.tsx`, `dashboard.tsx`, `index.ts`, `dropdown-menu.tsx`, `app-sidebar.tsx`, `utils.ts`, `toggle-group.tsx`?**
  _High betweenness centrality (0.038) - this node is a cross-community bridge._
- **Should `alarms.tsx` be split into smaller, more focused modules?**
  _Cohesion score 0.0701344243132671 - nodes in this community are weakly interconnected._
- **Why does `Tank` connect `Tank` to `ReportExportController.php`, `User`, `Site`, `AlarmApiTest.php`, `AnomalyLog`, `AnomalyDetected`, `Inertia\Response`, `Transaction`, `RfidTag`, `ReportExportTest.php`, `VendorFillTest.php`, `ReportExportController`?**
  _High betweenness centrality (0.029) - this node is a cross-community bridge._
- **Should `User` be split into smaller, more focused modules?**
  _Cohesion score 0.06448979591836734 - nodes in this community are weakly interconnected._