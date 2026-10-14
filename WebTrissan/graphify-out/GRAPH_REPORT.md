# Graph Report - WebTrissan  (2026-10-14)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 247 nodes · 248 edges · 43 communities (15 shown, 28 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 5 edges (avg confidence: 0.76)
- Token cost: 1,230 input · 441 output

## Graph Freshness
- Built from commit: `b98079eb`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Composer Configuration
- Frontend Dependencies
- Database Migrations
- Certification Data
- User Model
- Database Configuration
- Contact Information
- Landing Page Styling
- Works Page Logic
- Development Dependencies
- Application Bootstrap
- Composer Scripts
- Plugin Configuration
- Testing Setup
- Service Provider
- Certification Filtering
- Category Selection
- AI Agent Prompts
- Modal Management
- MCP Configuration
- Controller Structure
- Filter Logic
- Modal Closure
- License Page Content
- Inquiry Submission
- Download Simulation
- Works Page Content
- Project Grid
- Project Search
- Project Sorting

## God Nodes (most connected - your core abstractions)
1. `License Page` - 15 edges
2. `Landing Page HTML` - 12 edges
3. `require-dev` - 10 edges
4. `User` - 9 edges
5. `scripts` - 9 edges
6. `Landing Page HTML` - 9 edges
7. `License Page HTML` - 7 edges
8. `Works Page (HTML/JS)` - 7 edges
9. `AppServiceProvider` - 5 edges
10. `UserFactory` - 5 edges

## Surprising Connections (you probably didn't know these)
- `Landing Page HTML` --references--> `tailwindcss`  [EXTRACTED]
  Frontend-HTML/landing.html → package.json
- `License Page HTML` --references--> `tailwindcss`  [EXTRACTED]
  Frontend-HTML/license.html → package.json
- `Works Page (HTML/JS)` --conceptually_related_to--> `Robots.txt Configuration`  [INFERRED]
  Frontend-HTML/works.html → public/robots.txt
- `Agentic Workflow Rules` --conceptually_related_to--> `Cline Setup Prompt`  [INFERRED]
  AGENTS.md → CLINE-SETUP-PROMPT.md
- `Agentic Workflow Rules` --conceptually_related_to--> `Pack README`  [INFERRED]
  AGENTS.md → README.md

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Pagination System Flow** — goToPage, changePage, projectCatalogGrid, projectDetailModal [EXTRACTED 0.90]
- **Project Card Interaction Flow** — project_card_1, project_card_2, project_card_3, project_card_4, openProjectModal, projectDetailModal [EXTRACTED 0.95]
- **Contact Information Nodes** — frontend_html_landing_html_tel_contact, frontend_html_landing_html_email_contact, frontend_html_landing_html_email_corporate, frontend_html_landing_html_google_maps_link [EXTRACTED 1.00]
- **Corporate Certifications** — frontend-html_license_iso9001, frontend-html_license_as9100, frontend-html_license_asme_u_r, frontend-html_license_iso14001_45001 [EXTRACTED 1.00]
- **External Library Dependencies** — lucide_js, ref_tailwindcss, google_fonts [EXTRACTED 1.00]
- **Frontend Dependencies** — ref_tailwindcss, lucide_icons, google_fonts [EXTRACTED 1.00]
- **License Modal Interaction Flow** — frontend_html_license_html_openverificationmodal, frontend_html_license_html_closeverificationmodal, frontend_html_license_html_verificationmodal [EXTRACTED 1.00]
- **Product Certifications** — frontend-html_license_rpm_buzzer, frontend-html_license_turbine_gearbox, frontend-html_license_pipe_hubs, frontend-html_license_hydraulic_manifold [EXTRACTED 1.00]
- **Social Media Links** — frontend_html_landing_html_linkedin, frontend_html_landing_html_instagram, frontend_html_landing_html_youtube [EXTRACTED 1.00]
- **Works Filtering System** — frontend_html_works_html_selectcategory, frontend_html_works_html_filtercategory, frontend_html_works_html_work_card, frontend_html_works_html_category_btn [EXTRACTED 1.00]
- **Agentic Workflow Documentation** — agentic_rules, cline_setup_prompt, readme_pack [INFERRED 0.75]
- **Navigation State Management Pattern** — setActiveNav_func_landing, setActiveNav_func_license, updateScrollspy_func_license [INFERRED 0.75]
- **Work Filtering Mechanism** — frontend_html_landing_html_filtercategory, frontend_html_landing_html_selectcategory, frontend_html_landing_html_license_certifications [INFERRED 0.80]

## Communities (43 total, 28 thin omitted)

### Community 0 - "Composer Configuration"
Cohesion: 0.08
Nodes (23): autoload, autoload-dev, psr-4, psr-4, description, extra, laravel, keywords (+15 more)

### Community 1 - "Frontend Dependencies"
Cohesion: 0.10
Nodes (19): devDependencies, concurrently, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite, optionalDependencies, @laravel/multiplex (+11 more)

### Community 2 - "Database Migrations"
Cohesion: 0.16
Nodes (8): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}()

### Community 3 - "Certification Data"
Cohesion: 0.12
Nodes (16): License Page, AS9100 Rev D, ASME 'U' & 'R' Stamp, handleCertFilter, Hydraulic Manifold Blocks, IATF 16949:2016, ISO 14001:2015, ISO 14001 & 45001 (+8 more)

### Community 6 - "Contact Information"
Cohesion: 0.15
Nodes (13): Landing Page HTML, Contact Modal, Contact Email, Corporate Email, filterCategory Function, Google Maps Embed, Google Maps Link, Instagram Profile (+5 more)

### Community 7 - "Landing Page Styling"
Cohesion: 0.24
Nodes (12): Apex Industrial Engineering Brand, Filter Category Function, Landing Page HTML, License Page HTML, Google Fonts, Lucide Icons, Lucide Icons Library, tailwindcss (+4 more)

### Community 8 - "Works Page Logic"
Cohesion: 0.25
Nodes (11): changePage Function, Works Page (HTML/JS), goToPage Function, openProjectModal Function, Project Catalog Grid DOM Element, Project Detail Modal DOM Element, RPM Buzzer Modular Sensor Card, 5-Axis Planetary Carrier Gears Card (+3 more)

### Community 9 - "Development Dependencies"
Cohesion: 0.20
Nodes (10): require-dev, fakerphp/faker, laravel/boost, laravel/pail, laravel/pao, laravel/pint, mockery/mockery, nunomaduro/collision (+2 more)

### Community 10 - "Application Bootstrap"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 11 - "Composer Scripts"
Cohesion: 0.22
Nodes (9): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+1 more)

### Community 12 - "Plugin Configuration"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 16 - "Certification Filtering"
Cohesion: 0.67
Nodes (4): cert-category-filter, certSearchInput, handleCertFilter, setCertCategory

### Community 17 - "Category Selection"
Cohesion: 0.50
Nodes (4): category-btn, filterCategory, selectCategory, work-card

### Community 19 - "AI Agent Prompts"
Cohesion: 0.67
Nodes (3): Agentic Workflow Rules, Cline Setup Prompt, Pack README

### Community 21 - "Modal Management"
Cohesion: 0.67
Nodes (3): closeVerificationModal, openVerificationModal, verificationModal

## Knowledge Gaps
- **104 isolated node(s):** `Controller`, `description`, `keywords`, `dont-discover`, `license` (+99 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 149 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **28 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `require-dev` connect `Development Dependencies` to `Composer Configuration`?**
  _High betweenness centrality (0.013) - this node is a cross-community bridge._
- **What connects `Controller`, `description`, `keywords` to the rest of the system?**
  _104 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Composer Configuration` be split into smaller, more focused modules?**
  _Cohesion score 0.08333333333333333 - nodes in this community are weakly interconnected._
- **Why does `scripts` connect `Composer Scripts` to `Composer Configuration`?**
  _High betweenness centrality (0.012) - this node is a cross-community bridge._
- **Should `Frontend Dependencies` be split into smaller, more focused modules?**
  _Cohesion score 0.10476190476190476 - nodes in this community are weakly interconnected._
- **Why does `config` connect `Plugin Configuration` to `Composer Configuration`?**
  _High betweenness centrality (0.009) - this node is a cross-community bridge._
- **Should `Certification Data` be split into smaller, more focused modules?**
  _Cohesion score 0.125 - nodes in this community are weakly interconnected._