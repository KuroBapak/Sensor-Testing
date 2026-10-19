# Graph Report - WebTrissan  (2026-10-19)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 4068 nodes · 11836 edges · 194 communities (93 shown, 101 thin omitted)
- Extraction: 89% EXTRACTED · 11% INFERRED · 0% AMBIGUOUS · INFERRED: 1317 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `1df0ba03`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- components/chart.js
- rich-editor.js
- stat/chart.js
- markdown-editor.js
- select.js
- constructor
- constructor
- draw
- x
- n
- file-upload.js
- Cn
- support.js
- _update
- s
- configure
- draw
- te
- e
- buildTicks
- getSelectedRange
- updateElements
- _update
- GlobalSetting
- $t
- reset
- I
- deleteInDirection
- getMinMax
- Filament\Forms\Form
- setAttribute
- r
- updateElements
- render
- P
- Filament\Actions
- Illuminate\Database\Eloquent\Factories\HasFactory
- Filament\Resources\Pages\ListRecords
- isHorizontal
- add
- o
- ManageAboutCompany.php
- t
- User
- I
- constructor
- echo.js
- notifications.js
- Ni
- notifyEditorElement
- createNodes
- Filament\Resources\Pages\CreateRecord
- AdminPanelProvider.php
- getDatasetMeta
- preload
- qt
- package.json
- filament/app.js
- fn
- Unified CMS Architecture PRD
- appendBlockForElement
- C
- serializeSelectionToDataTransfer
- St
- toString
- Illuminate\Database\Seeder
- FrontendRoutesTest.php
- color-picker.js
- t
- Illuminate\Http\Request
- Illuminate\Database\Schema\Blueprint
- PortfolioItem
- PageSetting
- r
- _notify
- date-time-picker.js
- ManagePageSettings
- Testing Best Practices Skill
- require-dev
- Fe
- composer.json
- scripts
- CertificationCategory
- dispatch
- Event Discovery
- config
- view
- Agent Workflow Rules
- Be
- pe
- te
- CertificationCategoryResource
- ContactSubmissionResource
- CorporateAccreditationResource
- PersonnelResource
- PortfolioCategoryResource
- PortfolioItemResource
- ServiceClusterResource
- ServiceResource
- SkillResource
- Resource Routes
- psr-4
- require
- 0001_01_01_000001_create_cache_table.php
- Illuminate\Database\Migrations\Migration
- Illuminate\Support\Facades\Schema
- status
- Frontend Integration Implementation Plan
- Laravel Best Practices Skill
- Cache::lock
- Model
- Exception
- 2026_10_16_064853_create_page_settings_table.php
- 2026_10_16_064854_create_service_clusters_table.php
- 2026_10_16_064855_create_certification_categories_table.php
- 2026_10_16_064856_create_about_company_table.php
- 2026_10_16_064857_create_corporate_accreditations_table.php
- 2026_10_16_064858_create_services_table.php
- 2026_10_16_064859_create_works_table.php
- 2026_10_16_064903_create_product_certifications_table.php
- 2026_10_16_064905_create_specialty_clusters_table.php
- 2026_10_16_064906_create_skills_table.php
- 2026_10_16_064908_create_personnel_table.php
- 2026_10_16_064909_create_contact_submissions_table.php
- 2026_10_16_064910_create_portfolio_items_table.php
- 2026_10_16_064911_create_personnel_skills_table.php
- PRD v2.1 Schema Alignment Implementation Plan
- Cache::memo
- lazy
- env
- Attribute Casts
- Http Client
- ShouldBeUnique
- MCP Tool Policy Rule
- autoload-dev
- extra
- laravel-boost
- abtus.blade.php
- license.blade.php
- portofolio.blade.php
- works.blade.php
- Admin Panel Access
- Deploying to Cloud Skill
- Infer Conventions Skill
- Collection
- Global Scopes
- Relationships
- Foreign-Key Constraints
- Idiomatic Syntax
- Form Request
- Import Syntax
- About Company Model/Table
- Certification Categories Model/Table
- Schedule
- Contact Submissions Model/Table
- Corporate Accreditations Model/Table
- Filament Login Instructions
- Frontend Rule
- Global Settings Model/Table
- Laravel Implementation Rule
- Page Settings Model/Table
- Personnel Model/Table
- Portfolio Categories Model/Table
- Portfolio Items Model/Table
- Product Certifications Model/Table
- Service Clusters Model/Table
- Services Model/Table
- Skills Model/Table
- Specialty Clusters Model/Table
- Works Model/Table

## God Nodes (most connected - your core abstractions)
1. `x()` - 89 edges
2. `_update()` - 86 edges
3. `_update()` - 83 edges
4. `te()` - 72 edges
5. `V()` - 68 edges
6. `constructor()` - 61 edges
7. `Cn()` - 53 edges
8. `draw()` - 53 edges
9. `Ae()` - 51 edges
10. `ge()` - 50 edges

## Surprising Connections (you probably didn't know these)
- `Dark Mode` --references--> `tailwindcss`  [EXTRACTED]
  .claude/skills/tailwindcss-development/SKILL.md → package.json
- `{closure#3}()` --calls--> `ContactSubmission`  [EXTRACTED]
  tests/Feature/SchemaAlignmentTest.php → app/Models/ContactSubmission.php
- `{closure#2}()` --calls--> `Skill`  [EXTRACTED]
  tests/Feature/SchemaAlignmentTest.php → app/Models/Skill.php
- `{closure#2}()` --calls--> `SpecialtyCluster`  [EXTRACTED]
  tests/Feature/SchemaAlignmentTest.php → app/Models/SpecialtyCluster.php
- `{closure#3}()` --calls--> `User`  [EXTRACTED]
  tests/Feature/FilamentAdminTest.php → app/Models/User.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Caching Strategies** — claude_skills_laravel_best_practices_rules_caching_cache_remember, claude_skills_laravel_best_practices_rules_caching_cache_flexible, claude_skills_laravel_best_practices_rules_caching_cache_memo, claude_skills_laravel_best_practices_rules_caching_once [EXTRACTED 0.90]
- **Laravel Query Optimization Patterns** — claude_skills_laravel_best_practices_rules_db_performance_with, claude_skills_laravel_best_practices_rules_db_performance_chunk, claude_skills_laravel_best_practices_rules_db_performance_chunk_by_id, claude_skills_laravel_best_practices_rules_db_performance_with_count [EXTRACTED 0.90]
- **Laravel Queue Management Patterns** — claude_skills_laravel_best_practices_rules_queue_jobs_retry_after, claude_skills_laravel_best_practices_rules_queue_jobs_should_be_unique, claude_skills_laravel_best_practices_rules_queue_jobs_bus_batch [EXTRACTED 0.90]
- **CMS Data Models** — global_settings_model, page_settings_model, service_clusters_model, services_model, works_model, certification_categories_model, corporate_accreditations_model, product_certifications_model, portfolio_categories_model, portfolio_items_model, specialty_clusters_model, skills_model, personnel_model, contact_submissions_model, about_company_model [EXTRACTED 1.00]
- **Development Workflow Tools** — laravel_boost_guidelines, graphify_navigation, pest_testing_framework [EXTRACTED 1.00]
- **Frontend Pages** — landing_page, about_us_page, portfolio_page, works_page, licenses_page [EXTRACTED 1.00]
- **Laravel Best Practices Ruleset** — claude_skills_laravel_best_practices_rules_advanced_queries_rule, claude_skills_laravel_best_practices_rules_architecture_rule, claude_skills_laravel_best_practices_rules_blade_views_rule [EXTRACTED 1.00]
- **Schema Alignment Verification Group** — global_settings, page_settings, about_company, service_clusters, services, works, certification_categories, corporate_accreditations, product_certifications, portfolio_categories, portfolio_items, specialty_clusters, skills, personnel, contact_submissions [EXTRACTED 1.00]
- **Testing Best Practices Rule Hierarchy** — claude_skills_testing_best_practices_rules_assertions_rule, claude_skills_testing_best_practices_rules_endpoint_tests_rule, claude_skills_testing_best_practices_rules_finding_features_rule, claude_skills_testing_best_practices_rules_isolation_rule, claude_skills_testing_best_practices_rules_naming_rule, claude_skills_testing_best_practices_rules_performance_rule, claude_skills_testing_best_practices_rules_review_rule, claude_skills_testing_best_practices_rules_security_rule, claude_skills_testing_best_practices_rules_test_data_rule [EXTRACTED 1.00]
- **Frontend Implementation Phases** — category_redirect_plan, featured_works_modal_plan, filament_admin_plan, docs_implementation_plans_frontend_integration_plan [INFERRED 0.80]
- **Cline Rules System** — command_efficiency_rule, laravel_rule, frontend_rule, prd_phase_workflow_rule, mcp_tool_policy_rule [INFERRED 0.90]

## Communities (194 total, 101 thin omitted)

### Community 0 - "components/chart.js"
Cohesion: 0.01
Nodes (122): abutsStart(), acquireContext(), addControllers(), addPlugins(), addScales(), Ag(), an(), bg() (+114 more)

### Community 1 - "rich-editor.js"
Cohesion: 0.02
Nodes (135): activateAttributeIfSupported(), addHTMLAttribute(), appendStringToTextAtIndex(), applyBlockAttribute(), attachmentDidChangeAttributes(), attachmentDidChangeUploadProgress(), attachmentIsManaged(), canAcceptDataTransfer() (+127 more)

### Community 2 - "stat/chart.js"
Cohesion: 0.02
Nodes (104): So(), aa(), active(), addControllers(), addPlugins(), addScales(), an(), _animateOptions() (+96 more)

### Community 3 - "markdown-editor.js"
Cohesion: 0.04
Nodes (131): Ac(), ad(), af(), ai(), al(), An(), Ba(), Be() (+123 more)

### Community 4 - "select.js"
Cohesion: 0.05
Nodes (65): [b](), $c(), me(), D(), E(), g(), H(), J() (+57 more)

### Community 5 - "constructor"
Cohesion: 0.03
Nodes (114): after(), Al(), as(), bc(), before(), bo(), br(), _cachedScopes() (+106 more)

### Community 6 - "constructor"
Cohesion: 0.04
Nodes (107): addAttribute(), addAttributeAtRange(), addAttributesAtRange(), appendText(), applyBlockAttributeAtRange(), breakFormattedBlock(), canBeGroupedWith(), charAt() (+99 more)

### Community 7 - "draw"
Cohesion: 0.05
Nodes (94): aa(), adjustHitBoxes(), ae(), af(), afterDraw(), ah(), ao(), calculateLabelRotation() (+86 more)

### Community 8 - "x"
Cohesion: 0.11
Nodes (92): Ae(), ao(), as(), at(), B(), he(), br(), Bt() (+84 more)

### Community 9 - "n"
Cohesion: 0.07
Nodes (83): _a(), ar(), Bc(), ue(), u(), ci(), r(), df() (+75 more)

### Community 10 - "file-upload.js"
Cohesion: 0.05
Nodes (56): ba(), be(), bi(), c(), ca(), clickPercent(), constructor(), de() (+48 more)

### Community 11 - "Cn"
Cohesion: 0.10
Nodes (69): getType(), at(), Cn(), b(), Bt(), Ct(), dn(), Dt() (+61 more)

### Community 12 - "support.js"
Cohesion: 0.06
Nodes (55): ai(), apply(), B(), co(), Cr(), $e(), es(), Et() (+47 more)

### Community 13 - "_update"
Cohesion: 0.05
Nodes (64): active(), afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterFit(), afterSetDimensions(), afterTickToLabelConversion(), afterUpdate() (+56 more)

### Community 14 - "s"
Cohesion: 0.06
Nodes (64): addEventListener(), ba(), bindEvents(), bindResponsiveEvents(), bindUserEvents(), buildOrUpdateScales(), _checkEventBindings(), cl() (+56 more)

### Community 15 - "configure"
Cohesion: 0.05
Nodes (63): addBox(), addElements(), afterDatasetsUpdate(), beforeUpdate(), buildOrUpdateControllers(), buildOrUpdateElements(), configure(), _d() (+55 more)

### Community 16 - "draw"
Cohesion: 0.06
Nodes (62): acquireContext(), adjustHitBoxes(), bc(), Bl(), clear(), _computeLabelArea(), _computeTitleHeight(), _createItems() (+54 more)

### Community 17 - "te"
Cohesion: 0.05
Nodes (14): Bi(), bn(), Id(), ji(), kd(), ki(), on(), qd() (+6 more)

### Community 18 - "e"
Cohesion: 0.06
Nodes (56): addEventListener(), bf(), bindEvents(), bindResponsiveEvents(), bindUserEvents(), bu(), co(), contains() (+48 more)

### Community 19 - "buildTicks"
Cohesion: 0.05
Nodes (55): bi(), buildTicks(), clone(), create(), df(), dtFormatter(), ef(), eras() (+47 more)

### Community 20 - "getSelectedRange"
Cohesion: 0.07
Nodes (54): attachmentManagerDidRequestRemovalOfAttachment(), breaksOnReturn(), Ca(), canSetCurrentAttribute(), canSetCurrentBlockAttribute(), compositionControllerDidRequestRemovalOfAttachment(), decreaseBlockAttributeLevel(), decreaseListLevel() (+46 more)

### Community 21 - "updateElements"
Cohesion: 0.07
Nodes (54): ad(), applyStack(), aspectRatio(), Bh(), C(), _calculateBarIndexPixels(), _calculateBarValuePixels(), calculateCircumference() (+46 more)

### Community 22 - "_update"
Cohesion: 0.06
Nodes (53): afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterDraw(), afterFit(), afterSetDimensions(), afterTickToLabelConversion(), afterUpdate() (+45 more)

### Community 23 - "GlobalSetting"
Cohesion: 0.09
Nodes (18): AboutController, Controller, HomeController, LicensesController, WorksController, AboutCompany, CorporateAccreditation, GlobalSetting (+10 more)

### Community 24 - "$t"
Cohesion: 0.05
Nodes (50): alpha(), average(), bd(), Bt(), dh(), Dn(), Ee(), eo() (+42 more)

### Community 25 - "reset"
Cohesion: 0.06
Nodes (47): ArrowLeft(), ArrowRight(), beforeinput(), canApplyToDocument(), compositionend(), compositionstart(), compositionupdate(), dragend() (+39 more)

### Community 26 - "I"
Cohesion: 0.14
Nodes (43): Ae(), ar(), q(), Bi(), I(), c(), H(), d() (+35 more)

### Community 27 - "deleteInDirection"
Cohesion: 0.06
Nodes (46): backspace(), createLinkHTML(), d(), delete(), deleteByComposition(), deleteByCut(), deleteByDrag(), deleteCompositionText() (+38 more)

### Community 28 - "getMinMax"
Cohesion: 0.06
Nodes (46): ac(), afterAutoSkip(), Ai(), Ao(), Bi(), buildLookupTable(), ca(), calculateLabelRotation() (+38 more)

### Community 29 - "Filament\Forms\Form"
Cohesion: 0.10
Nodes (3): ProductCertificationResource, SpecialtyClusterResource, WorkResource

### Community 30 - "setAttribute"
Cohesion: 0.08
Nodes (45): add(), applyKeyboardCommand(), attachmentEditorDidRequestRemovalOfAttachment(), canBeGrouped(), copyUsingObjectMap(), copyUsingObjectsFromDocument(), createCaptionElement(), dialogIsVisible() (+37 more)

### Community 31 - "r"
Cohesion: 0.06
Nodes (45): _a(), au(), Ce(), ch(), r(), data(), determineDataLimits(), l() (+37 more)

### Community 32 - "updateElements"
Cohesion: 0.08
Nodes (41): applyStack(), ar(), as(), aspectRatio(), _calculateBarIndexPixels(), _calculateBarValuePixels(), _calculatePadding(), _computeGridLineItems() (+33 more)

### Community 33 - "render"
Cohesion: 0.08
Nodes (40): attachFiles(), canDecreaseBlockAttributeLevel(), canDecreaseNestingLevel(), canIncreaseNestingLevel(), compositionControllerDidFocus(), compositionDidLoadSnapshot(), compositionDidRequestChangingSelectionToLocationRange(), decreaseNestingLevel() (+32 more)

### Community 34 - "P"
Cohesion: 0.07
Nodes (39): ac(), At(), Bs(), Ca(), cd(), _computeLabelSizes(), De(), fu() (+31 more)

### Community 35 - "Filament\Actions"
Cohesion: 0.09
Nodes (12): EditCertificationCategory, EditContactSubmission, EditCorporateAccreditation, EditPersonnel, EditPortfolioCategory, EditPortfolioItem, EditProductCertification, EditServiceCluster (+4 more)

### Community 36 - "Illuminate\Database\Eloquent\Factories\HasFactory"
Cohesion: 0.11
Nodes (4): ContactSubmission, Skill, SpecialtyCluster, PersonnelSeeder

### Community 37 - "Filament\Resources\Pages\ListRecords"
Cohesion: 0.07
Nodes (12): ListCertificationCategories, ListContactSubmissions, ListCorporateAccreditations, ListPersonnels, ListPortfolioCategories, ListPortfolioItems, ListProductCertifications, ListServiceClusters (+4 more)

### Community 38 - "isHorizontal"
Cohesion: 0.08
Nodes (37): buildTicks(), calculateCircumference(), _circumference(), _computeAngle(), _computeLabelItems(), computeTickLimit(), diff(), _drawArgs() (+29 more)

### Community 39 - "add"
Cohesion: 0.07
Nodes (36): add(), afterAutoSkip(), Ai(), buildLookupTable(), $f(), fn(), Fr(), Gc() (+28 more)

### Community 40 - "o"
Cohesion: 0.08
Nodes (36): average(), c(), l(), fn(), fr(), G(), getActiveElements(), getCenterPoint() (+28 more)

### Community 42 - "t"
Cohesion: 0.17
Nodes (34): _a(), aa(), ba(), Be(), br(), T(), Ca(), ce() (+26 more)

### Community 43 - "User"
Cohesion: 0.09
Nodes (7): User, UserFactory, DatabaseSeeder, {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}()

### Community 44 - "I"
Cohesion: 0.10
Nodes (33): oi(), A(), attributeChangedCallback(), checkValidity(), connectedCallback(), disabled(), disconnectedCallback(), form() (+25 more)

### Community 45 - "constructor"
Cohesion: 0.09
Nodes (31): _a(), add(), addElements(), buildOrUpdateElements(), _cachedScopes(), chartOptionScopes(), configure(), constructor() (+23 more)

### Community 46 - "echo.js"
Cohesion: 0.09
Nodes (13): a(), ar(), cr(), H(), ji(), L(), Me(), pt() (+5 more)

### Community 47 - "notifications.js"
Cohesion: 0.08
Nodes (3): duration(), persistent(), seconds()

### Community 48 - "Ni"
Cohesion: 0.09
Nodes (27): alpha(), be(), ea(), en(), fe(), Fs(), ge(), greyscale() (+19 more)

### Community 49 - "notifyEditorElement"
Cohesion: 0.09
Nodes (26): actionIsExternal(), canInvokeAction(), compositionControllerDidBlur(), compositionControllerDidSyncDocumentView(), compositionDidAddAttachment(), compositionDidChangeAttachmentPreviewURL(), compositionDidChangeCurrentAttributes(), compositionDidChangeDocument() (+18 more)

### Community 50 - "createNodes"
Cohesion: 0.10
Nodes (26): cacheViewForObject(), createAttachmentNodes(), createChildView(), createContainerElement(), createDocumentFragmentForSync(), createElement(), createNodes(), createStringNodes() (+18 more)

### Community 51 - "Filament\Resources\Pages\CreateRecord"
Cohesion: 0.12
Nodes (12): CreateCertificationCategory, CreateContactSubmission, CreateCorporateAccreditation, CreatePersonnel, CreatePortfolioCategory, CreatePortfolioItem, CreateProductCertification, CreateServiceCluster (+4 more)

### Community 53 - "getDatasetMeta"
Cohesion: 0.12
Nodes (25): afterDatasetsUpdate(), buildOrUpdateControllers(), _destroy(), _destroyDatasetMeta(), Ei(), generateLabels(), getController(), getDatasetMeta() (+17 more)

### Community 54 - "preload"
Cohesion: 0.10
Nodes (24): attachmentForFile(), attributesForFile(), compositionShouldAcceptFile(), didChangeAttributes(), getContentType(), getCurrentTextAttributes(), getHeight(), getHref() (+16 more)

### Community 55 - "qt"
Cohesion: 0.09
Nodes (24): beforeDatasetsDraw(), beforeDraw(), dataset(), eh(), first(), getMaximumSize(), getSortedVisibleDatasetMetas(), getVisibleDatasetCount() (+16 more)

### Community 56 - "package.json"
Cohesion: 0.09
Nodes (21): devDependencies, concurrently, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite, optionalDependencies, @laravel/multiplex (+13 more)

### Community 57 - "filament/app.js"
Cohesion: 0.17
Nodes (22): B(), C(), D(), H(), I(), J(), O(), U() (+14 more)

### Community 58 - "fn"
Cohesion: 0.18
Nodes (23): da(), fa(), Fi(), fn(), S(), Ii(), je(), Jr() (+15 more)

### Community 59 - "Unified CMS Architecture PRD"
Cohesion: 0.25
Nodes (21): About Company Model, About Us Page, Certification Categories Model, Contact Submissions Model, Corporate Accreditations Model, Global Settings Model, Landing Page, Licenses Page (+13 more)

### Community 60 - "appendBlockForElement"
Cohesion: 0.17
Nodes (21): It(), appendAttachmentWithAttributes(), appendBlockForAttributesWithElement(), appendBlockForElement(), appendBlockForTextNode(), appendEmptyBlock(), appendPiece(), appendStringWithAttributes() (+13 more)

### Community 61 - "C"
Cohesion: 0.16
Nodes (21): C(), Co(), endOf(), Et(), formats(), getLabels(), getMaxOffset(), getMaxOverflow() (+13 more)

### Community 62 - "serializeSelectionToDataTransfer"
Cohesion: 0.11
Nodes (20): canSetCurrentTextAttribute(), compositionControllerDidRequestDeselectingAttachment(), compositionDidStartEditingAttachment(), cut(), didClickAttachment(), dragstart(), findAttachmentForElement(), getAttachmentAndPositionById() (+12 more)

### Community 63 - "St"
Cohesion: 0.12
Nodes (20): defaultZone(), At(), beforeDraw(), dataset(), Fa(), getMaximumSize(), _getSortedDatasetMetas(), getSortedVisibleDatasetMetas() (+12 more)

### Community 64 - "toString"
Cohesion: 0.12
Nodes (18): box(), canBeConsolidatedWith(), compositionControllerDidRender(), fromUCS2String(), getDirection(), getTargetDOMRange(), hasSameAttributesAsPiece(), hasSameConstructorAs() (+10 more)

### Community 65 - "Illuminate\Database\Seeder"
Cohesion: 0.20
Nodes (3): AboutCompanySeeder, GlobalSettingSeeder, WorkSeeder

### Community 67 - "color-picker.js"
Cohesion: 0.13
Nodes (4): style(), update(), [x](), tt()

### Community 68 - "t"
Cohesion: 0.16
Nodes (13): b(), di(), e(), g(), Ht(), i(), Ie(), P() (+5 more)

### Community 69 - "Illuminate\Http\Request"
Cohesion: 0.23
Nodes (4): ContactController, {closure#1}(), {closure#2}(), {closure#3}()

### Community 70 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.23
Nodes (6): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#3}()

### Community 71 - "PortfolioItem"
Cohesion: 0.29
Nodes (4): PortfolioController, PortfolioCategory, PortfolioItem, PortfolioSeeder

### Community 73 - "r"
Cohesion: 0.17
Nodes (12): Be(), ei(), ii(), le(), ni(), oi(), r(), ri() (+4 more)

### Community 74 - "_notify"
Cohesion: 0.23
Nodes (12): al(), cancel(), _createDescriptors(), _descriptors(), ll(), _notify(), _notifyStateChanges(), pluginScopeKeys() (+4 more)

### Community 75 - "date-time-picker.js"
Cohesion: 0.27
Nodes (7): e(), i(), l(), Ni(), o(), t(), u()

### Community 77 - "Testing Best Practices Skill"
Cohesion: 0.20
Nodes (10): Assertions Rule, Endpoint Tests Rule, Finding Features Rule, Isolation Rule, Naming Rule, Performance Rule, Review Rule, Security Rule (+2 more)

### Community 78 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, fakerphp/faker, laravel/boost, laravel/pail, laravel/pao, laravel/pint, mockery/mockery, nunomaduro/collision (+2 more)

### Community 79 - "Fe"
Cohesion: 0.20
Nodes (10): Ce(), De(), Dt(), Fe(), He(), ir(), Mt(), nr() (+2 more)

### Community 80 - "composer.json"
Cohesion: 0.22
Nodes (8): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type

### Community 81 - "scripts"
Cohesion: 0.22
Nodes (9): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+1 more)

### Community 83 - "dispatch"
Cohesion: 0.25
Nodes (8): dispatch(), dispatchSelf(), dispatchTo(), emit(), emitSelf(), emitTo(), event(), eventData()

### Community 84 - "Event Discovery"
Cohesion: 0.29
Nodes (5): Event Discovery, HasLocalePreference, ShouldDispatchAfterCommit, ShouldQueue, Mailable

### Community 85 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 86 - "view"
Cohesion: 0.29
Nodes (7): actions(), button(), constructor(), grouped(), link(), name(), view()

### Community 87 - "Agent Workflow Rules"
Cohesion: 0.47
Nodes (6): Agent Workflow Rules, Cline Setup Prompt, Graphify Navigation, Laravel Boost Guidelines, Pest Testing Framework, Pack README

### Community 100 - "Resource Routes"
Cohesion: 0.40
Nodes (5): Resource Routes, Route Model Binding, Authorization, CSRF Protection, Rate Limiter

### Community 101 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 102 - "require"
Cohesion: 0.40
Nodes (5): require, filament/filament, laravel/framework, laravel/tinker, php

### Community 107 - "status"
Cohesion: 0.40
Nodes (5): danger(), info(), status(), success(), warning()

### Community 108 - "Frontend Integration Implementation Plan"
Cohesion: 0.50
Nodes (4): Category Redirect Implementation Plan, Frontend Integration Implementation Plan, Featured Works Modal Implementation Plan, Filament Admin Panel Implementation Plan

### Community 109 - "Laravel Best Practices Skill"
Cohesion: 0.50
Nodes (4): Advanced Query Rules, Architecture Rules, Blade and View Rules, Laravel Best Practices Skill

### Community 127 - "PRD v2.1 Schema Alignment Implementation Plan"
Cohesion: 0.50
Nodes (4): PRD v2.1 Schema Alignment Implementation Plan, Filament Admin Panel (Future), Frontend Integration (Future), PRD v2.1

### Community 133 - "Attribute Casts"
Cohesion: 0.67
Nodes (3): Attribute Casts, Encrypted Casts, Mass Assignment

### Community 136 - "MCP Tool Policy Rule"
Cohesion: 0.67
Nodes (3): Command Efficiency Rule, MCP Tool Policy Rule, PRD Phase Workflow Rule

### Community 137 - "autoload-dev"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 138 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

## Knowledge Gaps
- **130 isolated node(s):** `App\\`, `Database\\Factories\\`, `Database\\Seeders\\`, `filament/filament`, `laravel/framework` (+125 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 738 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **101 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Qs()` connect `e` to `components/chart.js`, `reset`, `qt`?**
  _High betweenness centrality (0.084) - this node is a cross-community bridge._
- **Are the 14 inferred relationships involving `x()` (e.g. with `file-upload.js` and `D()`) actually correct?**
  _`x()` has 14 INFERRED edges - model-reasoned connections that need verification._
- **What connects `App\\`, `Database\\Factories\\`, `Database\\Seeders\\` to the rest of the system?**
  _130 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `components/chart.js` be split into smaller, more focused modules?**
  _Cohesion score 0.011418533157663592 - nodes in this community are weakly interconnected._
- **Why does `start()` connect `reset` to `rich-editor.js`, `render`, `constructor`, `e`, `getSelectedRange`, `deleteInDirection`?**
  _High betweenness centrality (0.084) - this node is a cross-community bridge._
- **Are the 17 inferred relationships involving `te()` (e.g. with `je()` and `It()`) actually correct?**
  _`te()` has 17 INFERRED edges - model-reasoned connections that need verification._
- **Should `rich-editor.js` be split into smaller, more focused modules?**
  _Cohesion score 0.01572139303482587 - nodes in this community are weakly interconnected._