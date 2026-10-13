# Graph Report - WebTrissan  (2026-10-13)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 265 nodes · 279 edges · 36 communities (12 shown, 24 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS · INFERRED: 1 edges (avg confidence: 0.8)
- Token cost: 1,145 input · 372 output

## Graph Freshness
- Built from commit: `87289dc4`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Frontend Dependencies
- Composer Configuration
- Database Migrations
- Inertia React Setup
- TypeScript Configuration
- Inertia Middleware
- User Model and Seeder
- NPM Scripts
- Database Factories
- Application Service Provider
- Development Dependencies
- Authentication Types
- Composer Plugins
- Testing TestCase
- Build Scripts
- Dev Dependencies
- Inertia Views
- Controllers

## God Nodes (most connected - your core abstractions)
1. `compilerOptions` - 14 edges
2. `scripts` - 13 edges
3. `require-dev` - 12 edges
4. `User` - 9 edges
5. `scripts` - 7 edges
6. `AppServiceProvider` - 6 edges
7. `require` - 6 edges
8. `HandleInertiaRequests` - 5 edges
9. `UserFactory` - 5 edges
10. `config` - 5 edges

## Surprising Connections (you probably didn't know these)
- `InertiaConfig` --references--> `Auth`  [EXTRACTED]
  resources/js/types/global.d.ts → resources/js/types/auth.ts

## Import Cycles
- None detected.

## Communities (36 total, 24 thin omitted)

### Community 0 - "Frontend Dependencies"
Cohesion: 0.07
Nodes (31): optionalDependencies, @laravel/multiplex, lightningcss-linux-x64-gnu, @rollup/rollup-linux-x64-gnu, @tailwindcss/oxide-linux-x64-gnu, private, $schema, type (+23 more)

### Community 1 - "Composer Configuration"
Cohesion: 0.08
Nodes (24): autoload, autoload-dev, psr-4, psr-4, description, extra, laravel, keywords (+16 more)

### Community 2 - "Database Migrations"
Cohesion: 0.16
Nodes (8): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}()

### Community 3 - "Inertia React Setup"
Cohesion: 0.12
Nodes (16): dependencies, clsx, concurrently, @inertiajs/react, @inertiajs/vite, laravel-vite-plugin, react, react-dom (+8 more)

### Community 4 - "TypeScript Configuration"
Cohesion: 0.12
Nodes (15): compilerOptions, allowJs, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, module, moduleResolution (+7 more)

### Community 5 - "Inertia Middleware"
Cohesion: 0.21
Nodes (4): HandleInertiaRequests, {closure#1}(), {closure#2}(), {closure#3}()

### Community 7 - "NPM Scripts"
Cohesion: 0.15
Nodes (13): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+5 more)

### Community 10 - "Development Dependencies"
Cohesion: 0.17
Nodes (12): require-dev, fakerphp/faker, larastan/larastan, laravel/boost, laravel/pail, laravel/pao, laravel/pint, laravel/sail (+4 more)

### Community 11 - "Authentication Types"
Cohesion: 0.28
Nodes (6): Auth, User, InertiaConfig, @inertiajs/core, InputHTMLAttributes, react

### Community 12 - "Composer Plugins"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 14 - "Build Scripts"
Cohesion: 0.29
Nodes (7): scripts, build, build:ssr, check, check:fix, dev, types:check

### Community 15 - "Dev Dependencies"
Cohesion: 0.33
Nodes (6): devDependencies, babel-plugin-react-compiler, @laravel/vite-plugin-wayfinder, @rolldown/plugin-babel, @types/node, vite-plus

## Knowledge Gaps
- **111 isolated node(s):** `User`, `InputHTMLAttributes`, `Controller`, `@laravel/multiplex`, `lightningcss-linux-x64-gnu` (+106 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 169 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **24 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `dependencies` connect `Inertia React Setup` to `Frontend Dependencies`?**
  _High betweenness centrality (0.025) - this node is a cross-community bridge._
- **What connects `User`, `InputHTMLAttributes`, `Controller` to the rest of the system?**
  _111 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Frontend Dependencies` be split into smaller, more focused modules?**
  _Cohesion score 0.06890756302521009 - nodes in this community are weakly interconnected._
- **Why does `scripts` connect `NPM Scripts` to `Composer Configuration`?**
  _High betweenness centrality (0.017) - this node is a cross-community bridge._
- **Should `Composer Configuration` be split into smaller, more focused modules?**
  _Cohesion score 0.08 - nodes in this community are weakly interconnected._
- **Why does `require-dev` connect `Development Dependencies` to `Composer Configuration`?**
  _High betweenness centrality (0.016) - this node is a cross-community bridge._
- **Should `Inertia React Setup` be split into smaller, more focused modules?**
  _Cohesion score 0.125 - nodes in this community are weakly interconnected._