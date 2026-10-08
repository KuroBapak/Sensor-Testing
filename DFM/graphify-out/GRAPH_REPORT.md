# Graph Report - DFM  (2026-10-08)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 1459 nodes · 3667 edges · 99 communities (53 shown, 46 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS · INFERRED: 10 edges (avg confidence: 0.78)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `b2a23093`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- react
- alarms.tsx
- Tank
- AnomalyLog
- sidebar.tsx
- ProfileController.php
- Illuminate\Http\Request
- User
- Transaction
- Site
- dependencies
- BackupSetting
- cn
- package.json
- index.ts
- Geofence
- API Endpoints
- use-appearance.tsx
- AnomalyDetected
- RfidTag
- UserController.php
- user-menu-content.tsx
- bootstrap/app.php
- CSV Export & Reports Specification
- HardwareDevice
- app-header.tsx
- components.json
- User.php
- PasswordResetTest.php
- compilerOptions
- Illuminate\Database\Seeder
- FortifyServiceProvider
- scripts
- Illuminate\Database\Schema\Blueprint
- nav-user.tsx
- ReportExportTest.php
- composer.json
- require-dev
- AlarmApiTest.php
- require
- toggle-group.tsx
- Illuminate\Database\Migrations\Migration
- Illuminate\Support\Facades\Schema
- GeofenceTest.php
- SecurityTest.php
- devDependencies
- optionalDependencies
- vite.config.ts
- global.d.ts
- config
- Fuel Management System PRD v2.5
- scripts
- alert.tsx
- EdgeIngestionAuthTest.php
- psr-4
- laravel
- 0001_01_01_000001_create_cache_table.php
- 2026_09_22_062545_create_roles_and_permissions_tables.php
- 2026_09_22_062552_add_role_id_to_users_table.php
- 2026_09_29_202636_add_test_backup_timestamp_to_backup_settings_table.php
- collapsible.tsx
- dashboard.tsx
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
- ADR Template
- NPM Config Gotcha
- Auth Middleware Gotcha
- Inertia Reload Options Gotcha
- MCP Tool Naming Gotcha

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
- `{closure#2}()` --calls--> `Geofence`  [EXTRACTED]
  tests/Feature/Admin/GeofenceTest.php → app/Models/Geofence.php
- `{closure#7}()` --calls--> `Geofence`  [EXTRACTED]
  tests/Feature/Admin/GeofenceTest.php → app/Models/Geofence.php
- `{closure#8}()` --calls--> `Geofence`  [EXTRACTED]
  tests/Feature/Admin/GeofenceTest.php → app/Models/Geofence.php
- `{closure#2}()` --calls--> `AnomalyLog`  [EXTRACTED]
  tests/Feature/Api/ScanRejectionTest.php → app/Models/AnomalyLog.php
- `{closure#3}()` --calls--> `AnomalyLog`  [EXTRACTED]
  tests/Feature/Api/ScanRejectionTest.php → app/Models/AnomalyLog.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Frontend Monitoring and Administration** — concept_react_frontend, concept_monitoring_dashboard, concept_admin_dashboard, concept_websocket_realtime, concept_leaflet_map [EXTRACTED 0.90]
- **Fuel Data Ingestion Flow** — concept_teltonika_fmc225, concept_teltonika_rut956, concept_laravel_backend, concept_device_authentication, concept_idempotency_strategy [EXTRACTED 0.90]
- **Fuel Management System PRD Cluster** — docs_vault_specs_prd_fuel_management_unified_v2_5_md, docs_vault_specs_prd_digest_md, docs_vault_prd_alignment_implementation_summary_md [EXTRACTED 0.90]
- **LiteLLM Configuration Documentation Cluster** — docs_vault_adr_litellm_smart_router_config_md, docs_vault_gotchas_litellm_config_migration_md, docs_vault_gotchas_litellm_config_optimized_yaml [EXTRACTED 0.90]
- **Theft Detection and Alarm Lifecycle** — concept_theft_detection_c1_c2, concept_geofencing_logic, concept_gps_validity_check, concept_alarm_system, concept_sensitivity_tunables [EXTRACTED 0.90]
- **Export Shortcuts from Monitoring Pages** — frontend_main_tank_page, frontend_mobile_tanks_page, frontend_alarms_page, frontend_reports_page [EXTRACTED 1.00]
- **Permission Gating for Reports** — permission_reports_export, permission_main_tank_view, permission_alarms_view [EXTRACTED 1.00]
- **7 Report Types for CSV Export** — report_main_tank_transactions, report_main_tank_daily_reconciliation, report_browser_tank_refuels, report_browser_tank_daily_consumption, report_fuel_tanker_unloads, report_alarm_log, report_tank_level_readings [EXTRACTED 1.00]

## Communities (99 total, 46 thin omitted)

### Community 0 - "react"
Cohesion: 0.06
Nodes (99): @inertiajs/react, leaflet, leaflet-draw, lucide-react, @radix-ui/react-slot, react, react-leaflet, DeleteUser() (+91 more)

### Community 1 - "alarms.tsx"
Cohesion: 0.07
Nodes (41): laravel-echo, pusher-js, recharts, AlarmRow, AlarmTable(), AlarmTableProps, ANOMALY_TYPE_BADGES, getAnomalyBadge() (+33 more)

### Community 2 - "Tank"
Cohesion: 0.07
Nodes (10): SensitivityController, SiteSettingController, TankController, VendorFillController, Controller, GeofenceController, {closure#1}(), MapController (+2 more)

### Community 3 - "AnomalyLog"
Cohesion: 0.07
Nodes (11): AlarmApiController, AlarmLog, AnomalyLog, AnomalyRead, ExportLog, MainTankLog, MobileTankLog, RolePermission (+3 more)

### Community 4 - "sidebar.tsx"
Cohesion: 0.10
Nodes (36): AppSidebar(), NavFooter(), NavMain(), Separator(), SidebarContent(), SidebarContext, SidebarFooter(), SidebarGroup() (+28 more)

### Community 5 - "ProfileController.php"
Cohesion: 0.07
Nodes (10): ResetUserPassword, PasswordValidationRules, ProfileValidationRules, ProfileController, SecurityController, PasswordUpdateRequest, ProfileDeleteRequest, ProfileUpdateRequest (+2 more)

### Community 6 - "Illuminate\Http\Request"
Cohesion: 0.10
Nodes (13): {closure#1}(), {closure#10}(), {closure#3}(), {closure#4}(), {closure#6}(), {closure#7}(), {closure#9}(), ReportExportController (+5 more)

### Community 7 - "User"
Cohesion: 0.08
Nodes (27): User, {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#8}() (+19 more)

### Community 8 - "Transaction"
Cohesion: 0.08
Nodes (8): AvlIngestionController, SiteReadingController, {closure#4}(), {closure#7}(), CheckGeofenceJob, DetectAnomalyJob, TankLevelReading, Transaction

### Community 9 - "Site"
Cohesion: 0.10
Nodes (21): Role, Site, {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#1}() (+13 more)

### Community 10 - "dependencies"
Cohesion: 0.05
Nodes (39): dependencies, class-variance-authority, clsx, concurrently, @inertiajs/react, @inertiajs/vite, laravel-echo, laravel-vite-plugin (+31 more)

### Community 11 - "BackupSetting"
Cohesion: 0.10
Nodes (5): BackupController, RunBackupJob, BackupRun, BackupSetting, BackupStorageService

### Community 12 - "cn"
Cohesion: 0.11
Nodes (29): AppHeader(), CardFooter(), DropdownMenuCheckboxItem(), DropdownMenuRadioItem(), DropdownMenuShortcut(), DropdownMenuSubContent(), DropdownMenuSubTrigger(), NavigationMenu() (+21 more)

### Community 13 - "package.json"
Cohesion: 0.06
Nodes (33): private, $schema, type, babel-plugin-react-compiler, clsx, concurrently, @laravel/multiplex, lightningcss-linux-x64-gnu (+25 more)

### Community 14 - "index.ts"
Cohesion: 0.14
Nodes (21): AppContent(), Props, AppShell(), Props, AppSidebarHeader(), Breadcrumbs(), Breadcrumb(), BreadcrumbEllipsis() (+13 more)

### Community 15 - "Geofence"
Cohesion: 0.10
Nodes (7): GeofenceApiController, RfidAuthController, {closure#1}(), RfidSyncController, ScanRejectionController, TransactionIngestionController, Geofence

### Community 16 - "API Endpoints"
Cohesion: 0.12
Nodes (30): Admin Dashboard, Alarm System, API Endpoints, Backup System, Database Schema, Decimal Precision, Device Authentication, Device Protocols (+22 more)

### Community 17 - "use-appearance.tsx"
Cohesion: 0.13
Nodes (24): sonner, withApp(), AppearanceToggleTab(), Toaster(), TooltipProvider(), Appearance, applyTheme(), getStoredAppearance() (+16 more)

### Community 18 - "AnomalyDetected"
Cohesion: 0.15
Nodes (4): AlarmCreated, AnomalyDetected, FleetPositionUpdated, TankReadingReceived

### Community 19 - "RfidTag"
Cohesion: 0.10
Nodes (11): RfidController, RfidTag, {closure#1}(), {closure#2}(), {closure#3}(), {closure#6}(), {closure#7}(), {closure#1}() (+3 more)

### Community 20 - "UserController.php"
Cohesion: 0.13
Nodes (5): {closure#1}(), RoleController, {closure#1}(), UserController, Permissions

### Community 21 - "user-menu-content.tsx"
Cohesion: 0.17
Nodes (16): Avatar(), AvatarFallback(), AvatarImage(), DropdownMenuGroup(), DropdownMenuItem(), DropdownMenuLabel(), DropdownMenuSeparator(), UserInfo() (+8 more)

### Community 22 - "bootstrap/app.php"
Cohesion: 0.15
Nodes (6): CheckPermission, HandleAppearance, VerifyDeviceToken, {closure#1}(), {closure#2}(), {closure#3}()

### Community 23 - "CSV Export & Reports Specification"
Cohesion: 0.16
Nodes (20): API Endpoint: GET /api/v1/reports/{reportType}/export.csv, CSV Injection Protection Logic, CSV Export & Reports Specification, Website Features Phased Roadmap, Export Audit Log Entity, Frontend Alarms Page (/fuel-monitoring/alarms), Frontend Main Tank Page (/fuel-monitoring/main-tank), Frontend Mobile Tanks Page (/fuel-monitoring/mobile-tanks) (+12 more)

### Community 24 - "HardwareDevice"
Cohesion: 0.15
Nodes (5): HardwareController, HardwareDevice, {closure#1}(), {closure#2}(), {closure#3}()

### Community 25 - "app-header.tsx"
Cohesion: 0.19
Nodes (7): mainNavItems, Props, rightNavItems, AppLogo(), AppLogoIcon(), AuthSimpleLayout(), AuthSplitLayout()

### Community 26 - "components.json"
Cohesion: 0.11
Nodes (17): aliases, components, hooks, lib, ui, utils, iconLibrary, rsc (+9 more)

### Community 28 - "PasswordResetTest.php"
Cohesion: 0.12
Nodes (5): {closure#3}(), {closure#4}(), {closure#6}(), {closure#8}(), TestCase

### Community 29 - "compilerOptions"
Cohesion: 0.12
Nodes (15): compilerOptions, allowJs, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, module, moduleResolution (+7 more)

### Community 30 - "Illuminate\Database\Seeder"
Cohesion: 0.20
Nodes (4): DatabaseSeeder, PlaceholderDataSeeder, RolesAndPermissionsSeeder, SiteSeeder

### Community 32 - "scripts"
Cohesion: 0.15
Nodes (13): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+5 more)

### Community 33 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.23
Nodes (6): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#3}()

### Community 34 - "nav-user.tsx"
Cohesion: 0.27
Nodes (11): NavUser(), DropdownMenu(), DropdownMenuContent(), DropdownMenuTrigger(), SidebarRail(), SidebarTrigger(), useSidebar(), getServerSnapshot() (+3 more)

### Community 35 - "ReportExportTest.php"
Cohesion: 0.15
Nodes (5): {closure#1}(), {closure#12}(), {closure#13}(), {closure#14}(), {closure#2}()

### Community 36 - "composer.json"
Cohesion: 0.17
Nodes (11): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+3 more)

### Community 37 - "require-dev"
Cohesion: 0.17
Nodes (12): require-dev, fakerphp/faker, larastan/larastan, laravel/boost, laravel/pail, laravel/pao, laravel/pint, laravel/sail (+4 more)

### Community 39 - "require"
Cohesion: 0.18
Nodes (11): require, inertiajs/inertia-laravel, laravel/chisel, laravel/fortify, laravel/framework, laravel/reverb, laravel/sanctum, laravel/tinker (+3 more)

### Community 40 - "toggle-group.tsx"
Cohesion: 0.29
Nodes (8): class-variance-authority, @radix-ui/react-toggle, @radix-ui/react-toggle-group, ToggleGroup(), ToggleGroupContext, ToggleGroupItem(), Toggle(), toggleVariants

### Community 43 - "GeofenceTest.php"
Cohesion: 0.22
Nodes (5): {closure#1}(), {closure#2}(), {closure#4}(), {closure#7}(), {closure#8}()

### Community 44 - "SecurityTest.php"
Cohesion: 0.32
Nodes (6): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}()

### Community 45 - "devDependencies"
Cohesion: 0.25
Nodes (8): devDependencies, babel-plugin-react-compiler, @laravel/vite-plugin-wayfinder, @rolldown/plugin-babel, @types/leaflet, @types/leaflet-draw, @types/node, vite-plus

### Community 46 - "optionalDependencies"
Cohesion: 0.25
Nodes (8): optionalDependencies, @laravel/multiplex, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc, @rollup/rollup-linux-x64-gnu, @rollup/rollup-win32-x64-msvc, @tailwindcss/oxide-linux-x64-gnu, @tailwindcss/oxide-win32-x64-msvc

### Community 47 - "vite.config.ts"
Cohesion: 0.25
Nodes (7): @inertiajs/vite, laravel-vite-plugin, @laravel/vite-plugin-wayfinder, @rolldown/plugin-babel, @tailwindcss/vite, vite-plus, @vitejs/plugin-react

### Community 48 - "global.d.ts"
Cohesion: 0.32
Nodes (6): Auth, Role, InertiaConfig, @inertiajs/core, InputHTMLAttributes, react

### Community 49 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 50 - "Fuel Management System PRD v2.5"
Cohesion: 0.29
Nodes (7): LiteLLM Smart Router Config ADR, App Info, LiteLLM Config Migration Guide, LiteLLM Optimized Config, PRD Alignment Implementation Summary, PRD Digest, Fuel Management System PRD v2.5

### Community 51 - "scripts"
Cohesion: 0.29
Nodes (7): scripts, build, build:ssr, check, check:fix, dev, types:check

### Community 52 - "alert.tsx"
Cohesion: 0.62
Nodes (5): AlertError(), Alert(), AlertDescription(), AlertTitle(), alertVariants

### Community 53 - "EdgeIngestionAuthTest.php"
Cohesion: 0.48
Nodes (6): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), transactionPayload()

### Community 54 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 55 - "laravel"
Cohesion: 0.40
Nodes (5): extra, laravel, post-create-project, dont-discover, installer

### Community 62 - "dashboard.tsx"
Cohesion: 0.60
Nodes (3): PlaceholderPattern(), PlaceholderPatternProps, Dashboard()

### Community 63 - "use-clipboard.ts"
Cohesion: 0.40
Nodes (3): CopiedValue, CopyFn, UseClipboardReturn

## Knowledge Gaps
- **246 isolated node(s):** `FleetMapLeafletProps`, `Props`, `Geofence`, `Props`, `Props` (+241 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 467 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **46 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `AnomalyLog`, `ReportExportTest.php`, `ProfileController.php`, `AlarmApiTest.php`, `Site`, `GeofenceTest.php`, `SecurityTest.php`, `RfidTag`, `UserController.php`, `User.php`, `PasswordResetTest.php`, `Illuminate\Database\Seeder`?**
  _High betweenness centrality (0.051) - this node is a cross-community bridge._
- **What connects `FleetMapLeafletProps`, `Props`, `Geofence` to the rest of the system?**
  _246 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `react` be split into smaller, more focused modules?**
  _Cohesion score 0.0605840740068928 - nodes in this community are weakly interconnected._
- **Why does `react` connect `react` to `alarms.tsx`, `nav-user.tsx`, `sidebar.tsx`, `toggle-group.tsx`, `cn`, `package.json`, `index.ts`, `use-appearance.tsx`, `alert.tsx`, `user-menu-content.tsx`, `app-header.tsx`, `dashboard.tsx`, `use-clipboard.ts`?**
  _High betweenness centrality (0.036) - this node is a cross-community bridge._
- **Should `alarms.tsx` be split into smaller, more focused modules?**
  _Cohesion score 0.07199032062915911 - nodes in this community are weakly interconnected._
- **Why does `@inertiajs/react` connect `react` to `alarms.tsx`, `nav-user.tsx`, `sidebar.tsx`, `package.json`, `index.ts`, `use-appearance.tsx`, `user-menu-content.tsx`, `app-header.tsx`, `dashboard.tsx`?**
  _High betweenness centrality (0.029) - this node is a cross-community bridge._
- **Should `Tank` be split into smaller, more focused modules?**
  _Cohesion score 0.07337526205450734 - nodes in this community are weakly interconnected._