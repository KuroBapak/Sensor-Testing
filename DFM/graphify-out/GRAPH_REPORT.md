# Graph Report - DFM  (2026-09-18)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 781 nodes · 1503 edges · 121 communities (33 shown, 88 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS · INFERRED: 5 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `954ab9b6`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- cn
- fuel-monitoring.tsx
- ProfileController.php
- use-appearance.tsx
- dependencies
- sidebar.tsx
- skeleton.tsx
- app-header.tsx
- private
- auth-card-layout.tsx
- Illuminate\Database\Migrations\Migration
- navigation-menu.tsx
- sheet.tsx
- use-clipboard.ts
- Illuminate\Http\Request
- components.json
- compilerOptions
- scripts
- User
- composer.json
- require-dev
- clsx
- select.tsx
- concurrently
- FortifyServiceProvider
- @inertiajs/react
- @inertiajs/vite
- package.json
- require
- App Info
- optionalDependencies
- laravel-vite-plugin
- Fuel Monitoring Dashboard Spec
- config
- lucide-react
- scripts
- @radix-ui/react-avatar
- devDependencies
- @radix-ui/react-checkbox
- psr-4
- laravel
- @radix-ui/react-collapsible
- logging.php
- collapsible.tsx
- layout.tsx
- @radix-ui/react-dialog
- GitHub Actions Tests Workflow
- artisan
- console.php
- @radix-ui/react-dropdown-menu
- GitHub Actions
- ADR Template
- MCP Tool Naming Gotcha
- Spec Template
- Favicon SVG
- Robots.txt
- @radix-ui/react-label
- @radix-ui/react-navigation-menu
- @radix-ui/react-select
- @radix-ui/react-separator
- @radix-ui/react-slot
- @radix-ui/react-toggle
- @radix-ui/react-toggle-group
- @radix-ui/react-tooltip
- react
- react-dom
- recharts
- sonner
- tailwind-merge
- tailwindcss
- @tailwindcss/vite
- tw-animate-css
- @types/react
- @types/react-dom
- typescript
- vite
- @vitejs/plugin-react
- $schema
- type
- babel-plugin-react-compiler
- clsx
- concurrently
- @laravel/multiplex
- lightningcss-linux-x64-gnu
- lightningcss-win32-x64-msvc
- react-dom
- @rollup/rollup-linux-x64-gnu
- @rollup/rollup-win32-x64-msvc
- tailwind-merge
- tailwindcss
- @tailwindcss/oxide-linux-x64-gnu
- @tailwindcss/oxide-win32-x64-msvc
- tw-animate-css
- @types/node
- @types/react
- @types/react-dom
- typescript
- vite
- mainNavItems
- Props
- rightNavItems
- SidebarContext
- SidebarGroupAction
- SidebarInput
- SidebarMenuAction
- SidebarMenuBadge
- SidebarMenuSkeleton
- SidebarMenuSub
- SidebarMenuSubButton
- SidebarMenuSubItem
- SidebarSeparator

## God Nodes (most connected - your core abstractions)
1. `lucide-react` - 21 edges
2. `User` - 17 edges
3. `compilerOptions` - 14 edges
4. `scripts` - 13 edges
5. `Button()` - 12 edges
6. `useAppearance()` - 12 edges
7. `require-dev` - 12 edges
8. `PasswordValidationRules` - 9 edges
9. `toUrl()` - 9 edges
10. `FortifyServiceProvider` - 8 edges

## Surprising Connections (you probably didn't know these)
- `CardFooter()` --calls--> `cn()`  [EXTRACTED]
  resources/js/components/ui/card.tsx → resources/js/lib/utils.ts
- `ToggleGroup()` --calls--> `cn()`  [EXTRACTED]
  resources/js/components/ui/toggle-group.tsx → resources/js/lib/utils.ts
- `SheetFooter()` --calls--> `cn()`  [EXTRACTED]
  resources/js/components/ui/sheet.tsx → resources/js/lib/utils.ts
- `SheetOverlay()` --calls--> `cn()`  [EXTRACTED]
  resources/js/components/ui/sheet.tsx → resources/js/lib/utils.ts
- `SelectContent()` --calls--> `cn()`  [EXTRACTED]
  resources/js/components/ui/select.tsx → resources/js/lib/utils.ts

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Fuel Monitoring Dashboard Pages** — fuel_monitoring_main_tank, fuel_monitoring_mobile_tanks, fuel_monitoring_alarms [EXTRACTED 0.75]

## Communities (121 total, 88 thin omitted)

### Community 0 - "cn"
Cohesion: 0.10
Nodes (38): @inertiajs/react, @radix-ui/react-checkbox, @radix-ui/react-label, react, resources_js_actions_app_http_controllers_settings_profilecontroller, resources_js_actions_app_http_controllers_settings_securitycontroller, DeleteUser(), InputError() (+30 more)

### Community 1 - "fuel-monitoring.tsx"
Cohesion: 0.09
Nodes (22): recharts, AlarmTable(), AlarmTableProps, MainTankChart(), MainTankChartProps, MainTankTable(), MainTankTableProps, MobileTankChart() (+14 more)

### Community 2 - "ProfileController.php"
Cohesion: 0.06
Nodes (27): PasswordValidationRules, ProfileValidationRules, Controller, FuelMonitoringController, ProfileController, SecurityController, PasswordUpdateRequest, ProfileDeleteRequest (+19 more)

### Community 3 - "use-appearance.tsx"
Cohesion: 0.11
Nodes (25): @radix-ui/react-tooltip, sonner, AppearanceToggleTab(), Toaster(), Tooltip(), TooltipContent(), TooltipProvider(), TooltipTrigger() (+17 more)

### Community 5 - "sidebar.tsx"
Cohesion: 0.06
Nodes (52): lucide-react, AppContent(), Props, AppShell(), Props, AppSidebar(), footerNavItems, AppSidebarHeader() (+44 more)

### Community 7 - "app-header.tsx"
Cohesion: 0.07
Nodes (35): @radix-ui/react-avatar, @radix-ui/react-dropdown-menu, Avatar(), AvatarFallback(), AvatarImage(), DropdownMenu(), DropdownMenuCheckboxItem(), DropdownMenuContent() (+27 more)

### Community 9 - "auth-card-layout.tsx"
Cohesion: 0.10
Nodes (17): AppLogo(), AppLogoIcon(), Card(), CardContent(), CardDescription(), CardFooter(), CardHeader(), CardTitle() (+9 more)

### Community 10 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.14
Nodes (3): Illuminate\Database\Migrations\Migration, Illuminate\Database\Schema\Blueprint, Illuminate\Support\Facades\Schema

### Community 11 - "navigation-menu.tsx"
Cohesion: 0.08
Nodes (25): class-variance-authority, @radix-ui/react-navigation-menu, @radix-ui/react-slot, @radix-ui/react-toggle, @radix-ui/react-toggle-group, Alert(), AlertDescription(), AlertTitle() (+17 more)

### Community 12 - "sheet.tsx"
Cohesion: 0.17
Nodes (9): @radix-ui/react-dialog, Sheet(), SheetContent(), SheetDescription(), SheetFooter(), SheetHeader(), SheetOverlay(), SheetTitle() (+1 more)

### Community 13 - "use-clipboard.ts"
Cohesion: 0.40
Nodes (3): CopiedValue, CopyFn, UseClipboardReturn

### Community 14 - "Illuminate\Http\Request"
Cohesion: 0.17
Nodes (11): HandleAppearance, HandleInertiaRequests, Closure, Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware, Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets, Illuminate\Http\Request (+3 more)

### Community 15 - "components.json"
Cohesion: 0.11
Nodes (17): aliases, components, hooks, lib, ui, utils, iconLibrary, rsc (+9 more)

### Community 16 - "compilerOptions"
Cohesion: 0.12
Nodes (15): compilerOptions, allowJs, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, module, moduleResolution (+7 more)

### Community 17 - "scripts"
Cohesion: 0.15
Nodes (13): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+5 more)

### Community 18 - "User"
Cohesion: 0.05
Nodes (29): ResetUserPassword, User, UserFactory, DatabaseSeeder, Illuminate\Auth\Notifications\ResetPassword, Illuminate\Cache\RateLimiting\Limit, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Eloquent\Attributes\Fillable (+21 more)

### Community 19 - "composer.json"
Cohesion: 0.17
Nodes (11): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+3 more)

### Community 20 - "require-dev"
Cohesion: 0.17
Nodes (12): require-dev, fakerphp/faker, larastan/larastan, laravel/boost, laravel/pail, laravel/pao, laravel/pint, laravel/sail (+4 more)

### Community 22 - "select.tsx"
Cohesion: 0.17
Nodes (8): @radix-ui/react-select, SelectContent(), SelectItem(), SelectLabel(), SelectScrollDownButton(), SelectScrollUpButton(), SelectSeparator(), SelectTrigger()

### Community 24 - "FortifyServiceProvider"
Cohesion: 0.17
Nodes (6): AppServiceProvider, FortifyServiceProvider, Carbon\CarbonImmutable, Illuminate\Support\Facades\Date, Illuminate\Support\Facades\DB, Illuminate\Support\ServiceProvider

### Community 27 - "package.json"
Cohesion: 0.33
Nodes (8): @inertiajs/vite, laravel-vite-plugin, ref_laravel_vite_plugin_fonts, @laravel/vite-plugin-wayfinder, @rolldown/plugin-babel, @tailwindcss/vite, vite-plus, @vitejs/plugin-react

### Community 28 - "require"
Cohesion: 0.25
Nodes (8): require, inertiajs/inertia-laravel, laravel/chisel, laravel/fortify, laravel/framework, laravel/tinker, laravel/wayfinder, php

### Community 29 - "App Info"
Cohesion: 0.25
Nodes (8): App Info, Auth Middleware Routes Gotcha, Inertia, Laravel Framework, Laravel Pint, Pest, pnpm workspace, Wayfinder

### Community 30 - "optionalDependencies"
Cohesion: 0.25
Nodes (8): optionalDependencies, @laravel/multiplex, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc, @rollup/rollup-linux-x64-gnu, @rollup/rollup-win32-x64-msvc, @tailwindcss/oxide-linux-x64-gnu, @tailwindcss/oxide-win32-x64-msvc

### Community 32 - "Fuel Monitoring Dashboard Spec"
Cohesion: 0.29
Nodes (7): Alarm Logs Table, Fuel Monitoring Dashboard Spec, Fuel Monitoring Alarms Route, Fuel Monitoring Main Tank Route, Fuel Monitoring Mobile Tanks Route, Main Tank Logs Table, Mobile Tank Logs Table

### Community 33 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 35 - "scripts"
Cohesion: 0.29
Nodes (7): scripts, build, build:ssr, check, check:fix, dev, types:check

### Community 37 - "devDependencies"
Cohesion: 0.33
Nodes (6): devDependencies, babel-plugin-react-compiler, @laravel/vite-plugin-wayfinder, @rolldown/plugin-babel, @types/node, vite-plus

### Community 39 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 40 - "laravel"
Cohesion: 0.40
Nodes (5): extra, laravel, post-create-project, dont-discover, installer

### Community 42 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 44 - "layout.tsx"
Cohesion: 0.14
Nodes (16): @radix-ui/react-separator, AppHeader(), Heading(), Separator(), IsCurrentOrParentUrlFn, IsCurrentUrlFn, useCurrentUrl(), UseCurrentUrlReturn (+8 more)

### Community 46 - "GitHub Actions Tests Workflow"
Cohesion: 0.50
Nodes (4): actions/checkout, actions/setup-node, actions/setup-php, GitHub Actions Tests Workflow

## Knowledge Gaps
- **214 isolated node(s):** `Props`, `Props`, `Props`, `PageProps`, `Props` (+209 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 323 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **88 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `react` connect `cn` to `fuel-monitoring.tsx`, `use-appearance.tsx`, `sidebar.tsx`, `app-header.tsx`, `auth-card-layout.tsx`, `navigation-menu.tsx`, `layout.tsx`, `sheet.tsx`, `use-clipboard.ts`, `select.tsx`, `package.json`?**
  _High betweenness centrality (0.093) - this node is a cross-community bridge._
- **Why does `@inertiajs/react` connect `cn` to `fuel-monitoring.tsx`, `use-appearance.tsx`, `sidebar.tsx`, `app-header.tsx`, `auth-card-layout.tsx`, `layout.tsx`, `package.json`?**
  _High betweenness centrality (0.070) - this node is a cross-community bridge._
- **Why does `cn()` connect `cn` to `use-appearance.tsx`, `sidebar.tsx`, `skeleton.tsx`, `app-header.tsx`, `auth-card-layout.tsx`, `navigation-menu.tsx`, `layout.tsx`, `sheet.tsx`, `SidebarGroupAction`, `SidebarInput`, `SidebarMenuAction`, `SidebarMenuBadge`, `SidebarMenuSkeleton`, `SidebarMenuSub`, `select.tsx`, `SidebarMenuSubButton`, `SidebarMenuSubItem`, `SidebarSeparator`?**
  _High betweenness centrality (0.064) - this node is a cross-community bridge._
- **What connects `Props`, `Props`, `Props` to the rest of the system?**
  _214 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `cn` be split into smaller, more focused modules?**
  _Cohesion score 0.096045197740113 - nodes in this community are weakly interconnected._
- **Should `fuel-monitoring.tsx` be split into smaller, more focused modules?**
  _Cohesion score 0.09365079365079365 - nodes in this community are weakly interconnected._
- **Should `ProfileController.php` be split into smaller, more focused modules?**
  _Cohesion score 0.060109289617486336 - nodes in this community are weakly interconnected._