# Graph Report - WebTrissan  (2026-10-16)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 189 nodes · 191 edges · 34 communities (9 shown, 25 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `7a8a7c20`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Composer Configuration
- Database Migrations
- Frontend Dependencies
- User Model And Seeding
- Database Factories And Config
- Development Dependencies
- Application Bootstrap
- Composer Scripts
- Project Guidelines
- Composer Settings
- Testing Framework Setup
- Service Providers
- Vite Configuration
- MCP Integration Config
- Base Controller
- Cline Setup Instructions

## God Nodes (most connected - your core abstractions)
1. `require-dev` - 10 edges
2. `User` - 9 edges
3. `scripts` - 9 edges
4. `Repository Guidance` - 6 edges
5. `AppServiceProvider` - 5 edges
6. `UserFactory` - 5 edges
7. `config` - 5 edges
8. `DatabaseSeeder` - 4 edges
9. `psr-4` - 4 edges
10. `require` - 4 edges

## Surprising Connections (you probably didn't know these)
- `Repository Guidance` --conceptually_related_to--> `Command and Context Efficiency Rules`  [EXTRACTED]
  AGENTS.md → .clinerules/10-command-efficiency.md
- `Repository Guidance` --conceptually_related_to--> `Laravel Implementation Rules`  [EXTRACTED]
  AGENTS.md → .clinerules/20-laravel.md
- `Repository Guidance` --conceptually_related_to--> `Frontend Rules`  [EXTRACTED]
  AGENTS.md → .clinerules/30-frontend.md
- `Repository Guidance` --conceptually_related_to--> `Robots.txt`  [EXTRACTED]
  AGENTS.md → public/robots.txt
- `Laravel Implementation Rules` --references--> `Laravel Boost Guidelines`  [EXTRACTED]
  .clinerules/20-laravel.md → AGENTS.md

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Cline Configuration Files** — agents_laravel_boost_guidelines, agents_repository_guidance, cline_rules_10_command_efficiency, cline_rules_20_laravel, cline_rules_30_frontend, readme_readme, public_robots_txt [INFERRED 0.80]

## Communities (34 total, 25 thin omitted)

### Community 0 - "Composer Configuration"
Cohesion: 0.08
Nodes (23): autoload, autoload-dev, psr-4, psr-4, description, extra, laravel, keywords (+15 more)

### Community 1 - "Database Migrations"
Cohesion: 0.16
Nodes (8): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}()

### Community 2 - "Frontend Dependencies"
Cohesion: 0.14
Nodes (14): optionalDependencies, @laravel/multiplex, private, $schema, scripts, build, dev, type (+6 more)

### Community 5 - "Development Dependencies"
Cohesion: 0.20
Nodes (10): require-dev, fakerphp/faker, laravel/boost, laravel/pail, laravel/pao, laravel/pint, mockery/mockery, nunomaduro/collision (+2 more)

### Community 6 - "Application Bootstrap"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 7 - "Composer Scripts"
Cohesion: 0.22
Nodes (9): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+1 more)

### Community 8 - "Project Guidelines"
Cohesion: 0.38
Nodes (7): Laravel Boost Guidelines, Repository Guidance, Command and Context Efficiency Rules, Laravel Implementation Rules, Frontend Rules, Robots.txt, README

### Community 9 - "Composer Settings"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 13 - "Vite Configuration"
Cohesion: 0.33
Nodes (6): devDependencies, concurrently, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite

## Knowledge Gaps
- **58 isolated node(s):** `Controller`, `description`, `keywords`, `dont-discover`, `license` (+53 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 112 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **25 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `require-dev` connect `Development Dependencies` to `Composer Configuration`?**
  _High betweenness centrality (0.023) - this node is a cross-community bridge._
- **What connects `Controller`, `description`, `keywords` to the rest of the system?**
  _58 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Composer Configuration` be split into smaller, more focused modules?**
  _Cohesion score 0.08333333333333333 - nodes in this community are weakly interconnected._
- **Why does `scripts` connect `Composer Scripts` to `Composer Configuration`?**
  _High betweenness centrality (0.020) - this node is a cross-community bridge._
- **Should `Frontend Dependencies` be split into smaller, more focused modules?**
  _Cohesion score 0.14166666666666666 - nodes in this community are weakly interconnected._
- **Why does `config` connect `Composer Settings` to `Composer Configuration`?**
  _High betweenness centrality (0.015) - this node is a cross-community bridge._