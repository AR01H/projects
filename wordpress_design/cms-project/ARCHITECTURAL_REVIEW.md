# Architectural Review & Refactoring Plan

## CMS Plugin (`plugins/cms-plugin`) + Theme (`themes/advaithhomes_new`)

**Date:** 2026-07-23
**Scope:** Complete codebase audit — ~100 PHP files (plugin) + ~190 PHP files (theme)
**Status:** Architecture update — feature-first, modular, enterprise-level redesign

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Design Principles](#2-design-principles)
3. [Existing Architecture Overview](#3-existing-architecture-overview)
4. [Issues by Severity](#4-issues-by-severity)
5. [Code Smells & Anti-Patterns](#5-code-smells--anti-patterns)
6. [Files to Rename, Move, Merge, Split, or Remove](#6-files-to-rename-move-merge-split-or-remove)
7. [Target Architecture — Plugin (Generic CMS Framework)](#7-target-architecture--plugin-generic-cms-framework)
8. [Target Architecture — Theme (Presentation Layer)](#8-target-architecture--theme-presentation-layer)
9. [Feature Module Map](#9-feature-module-map)
10. [Models & Data Layer](#10-models--data-layer)
11. [Common Entry Points & Application Lifecycle](#11-common-entry-points--application-lifecycle)
12. [Request Lifecycle](#12-request-lifecycle)
13. [Admin Execution Flow](#13-admin-execution-flow)
14. [Frontend Execution Flow](#14-frontend-execution-flow)
15. [Service Layer Design](#15-service-layer-design)
16. [Repository Layer Design](#16-repository-layer-design)
17. [Cache System (Plugin-Level + Theme-Level)](#17-cache-system-plugin-level--theme-level)
18. [Intermediate Data Layer (Bridge Module)](#18-intermediate-data-layer-bridge-module)
19. [Dependency Rules & Coupling Boundaries](#19-dependency-rules--coupling-boundaries)
20. [Reusability Contracts](#20-reusability-contracts)
21. [AI-Friendly Organization](#21-ai-friendly-organization)
22. [JSON-Driven Configuration & Content](#22-json-driven-configuration--content)
23. [Theme Override System](#23-theme-override-system)
24. [Array-Driven Content Patterns](#24-array-driven-content-patterns)
25. [Configuration Strategy](#25-configuration-strategy)
26. [Logging & Exception Handling](#26-logging--exception-handling)
27. [Security Improvements](#27-security-improvements)
28. [Performance Improvements](#28-performance-improvements)
29. [Coding Standards](#29-coding-standards)
30. [Refactoring Roadmap](#30-refactoring-roadmap)
31. [Centralized Hook Registration Pattern](#31-centralized-hook-registration-pattern)
32. [Permission System](#32-permission-system)
33. [Refactoring Completion Status](#33-refactoring-completion-status)

---

## 1. Executive Summary

The CMS Plugin and Theme represent a substantial custom CMS built on WordPress: **~16,000+ lines of PHP** across ~290 files. The codebase is functional with solid security practices (nonces, capability checks, escaping, sanitization). However, it has accumulated significant architectural debt: god objects, no namespaces, procedural functions scattered across templates, and tight coupling between plugin and theme.

### What Changed in This Review

This revision replaces the previous type-based (layered MVC) architecture with a **feature-first, modular, enterprise-level** structure. The key shifts:

- **Plugin** becomes a **generic CMS framework** — reusable across any WordPress site, containing only generic CMS functionality, no project-specific logic.
- **Theme** becomes a **presentation-only layer** — organized by feature/page, with business logic extracted to plugin services.
- **Feature modules** are self-contained: each feature owns its controllers, services, models, views, assets, config, and templates.
- **Domain models** replace flat model files: one entity per class, clear relationships, repositories for data access.
- **Centralized entry points** make the application lifecycle easy to follow.

### Strengths (Preserved)
- Consistent security practices (nonces, capability checks, escaping, sanitization)
- Well-designed database schema with proper foreign keys, idempotent migrations, and seed data
- Dual-mode support (plugin + standalone theme) for deployment flexibility
- Rich component system in the theme (15 cards, 51 sections, 47+ parts)
- Intermediate logical layer pattern (data preparation between pages and components)
- Theme settings engine with tabs + subtabs system

### Critical Issues (Addressed)
- **No PSR-4 namespaces** → Adopt feature-scoped namespaces
- **5 missing autoloader entries** → Composer PSR-4 autoloading
- **God objects** → Split into focused feature modules
- **99 direct `$wpdb` calls in templates** → Repository layer
- **30+ procedural global functions** → Class methods in feature modules
- **8 dead/empty files** → Remove
- **Mixed concerns in bootstrap** → Thin boot + centralized entry points
- **Project-specific logic in plugin** → Move to theme

### Bottom Line
The security foundation is solid, the database design is clean, and the model base class provides a good starting point. A phased refactoring to a feature-first architecture will make the codebase maintainable, reusable, and AI-friendly.

---

## 2. Design Principles

### Feature-First Organization

Every feature is a **self-contained module** with its own directory containing:

```
feature-name/
├── controllers/     # Request handling (admin, AJAX, REST)
├── services/        # Business logic
├── models/          # Domain entities
├── repositories/    # Data access
├── views/           # Presentation templates (admin + frontend)
├── assets/          # Feature-specific CSS, JS, images
├── config/          # Feature configuration, schemas, defaults
├── validation/      # Input validation rules
└── tests/           # Feature-specific tests
```

**Rule:** If you can't determine which feature a file belongs to, the architecture has failed.

### Separation of Concerns

| Concern | Owner | Allowed Dependencies |
|---------|-------|---------------------|
| Request handling | Controllers | Services, Repositories, Helpers |
| Business logic | Services | Repositories, Helpers, Config |
| Data access | Repositories | Database (Connection), Models |
| Domain entities | Models | Nothing (pure data) |
| Presentation | Views/Templates | Helpers (for output formatting only) |
| Cross-cutting | Helpers/Utils | Nothing (pure functions) |
| Configuration | Config files | Nothing (static data) |

### Dependency Direction

```
Controllers → Services → Repositories → Database
     ↓           ↓            ↓
   Views      Helpers      Models
```

**Never:** Views → Database, Models → Services, Repositories → Controllers.

---

## 3. Existing Architecture Overview

### Current Plugin Structure (Type-Based)
```
cms-plugin/
├── ah-cms.php                    # 621 lines — bootstrap + shortcodes + business logic
├── functions.php                 # Dual-mode bootstrap
├── admin/
│   ├── class-admin-bootstrap.php # 898 lines — all admin init
│   ├── menus/class-admin-menus.php
│   ├── ajax/class-ajax-handlers.php (695 lines), class-analytics-ajax.php
│   ├── import/class-csv-importer.php
│   └── pages/                    # 34 flat admin page files
├── api/class-rest-routes.php     # Single file, all REST routes
├── assets/css/                   # 4 CSS files
├── database/                     # Schema, migrations, seed, FK, installer
├── helper/                       # 5 helpers + 4 empty stubs
├── inc/                          # Autoloader, cache, form builder, workflow, etc.
├── models/                       # 25 model classes (flat, no relationships)
├── templates/                    # 1 frontend template
├── components/                   # 1 component (toaster)
└── docs/                         # Documentation
```

**Problem:** Files grouped by type. A "workflow feature" spans `inc/class-workflow-manager.php` (2,118 lines), `admin/pages/workflow-manager.php`, `models/class-*-model.php`, and database tables. No single place to understand the feature.

### Current Theme Structure (Hybrid)
```
advaithhomes_new/
├── functions.php                 # 14 require_once calls
├── explode_function.php          # Misnamed file loader
├── admin/
│   ├── class-theme-admin.php     # 1,717 lines — all admin UI
│   ├── class-theme-settings.php  # Reusable settings engine
│   └── tabs/                     # Tab + subtab system
├── apis/
│   ├── services.php              # 665 lines procedural
│   ├── services_cms.php          # 810 lines procedural (raw SQL to plugin DB)
│   └── models/
├── common/common_functions.php   # 1,084 lines procedural catch-all
├── components/
│   ├── cards/                    # 15 card components
│   ├── parts/                    # 47+ reusable parts
│   └── sections/                 # 51 section components
├── intermediate/                 # 14 logical/data preparation files
├── pages/                        # 16 page templates
├── includes/                     # Core: routing, SEO, cache, data readers
├── calculators/                  # Calculator system
├── data/                         # Static JSON/CSV/HTML
├── languages/                    # Translation files
└── assets/                       # 24 CSS, 16 JS, fonts, images
```

**Problem:** Feature logic scattered across `common_functions.php`, `services.php`, `services_cms.php`, `includes/*.php`, and `intermediate/*.php`. No clear feature boundaries.

---

## 4. Issues by Severity

### P0 — Critical (Potential Runtime Errors)

| # | Issue | File(s) | Impact |
|---|-------|---------|--------|
| 1 | **5 classes missing from autoloader** — `Term_Manager`, `AH_Events_Model`, `AH_Home_Banners_Model`, `AH_Features_In_Model`, `AH_Newsletters_Model` | `inc/class-autoloader.php` | Runtime crash |
| 2 | **Version mismatch** — header says `1.0.2`, constant says `1.3.1` | `ah-cms.php:5,13` | Metadata confusion |
| 3 | **Two classes per file** — breaks PSR-4 autoloading | `models/class-spotlights-model.php`, `models/class-analytics-model.php` | Naming collision risk |

### P1 — High (Architecture / Maintainability)

| # | Issue | Detail |
|---|-------|--------|
| 4 | **No namespaces** — all 50+ classes in global namespace | Fragile autoloading, naming conflicts, poor IDE support |
| 5 | **Manual classmap autoloader** — every new class requires editing one array | Does not scale; PSR-4 with Composer is standard |
| 6 | **God objects** — `AH_Workflow_Manager` (2,118 lines), `ADN_Theme_Admin` (1,717 lines), `builder-block-renderer.php` (1,020 lines), `common_functions.php` (1,084 lines) | Single classes doing too many things |
| 7 | **99 direct `$wpdb` calls in admin page templates** | Presentation layer performing database queries |
| 8 | **30+ procedural global functions** in page template files | Functions in `admin/pages/*.php` instead of service classes |
| 9 | **Mixed concerns in main bootstrap** — `ah-cms.php` has initialization + shortcodes + business logic + `$wpdb` queries | Should be thin: only boot + hook registration |
| 10 | **Theme reads plugin DB directly** — `services_cms.php` has 810 lines of raw SQL to plugin tables | Tight coupling, no service contract |
| 11 | **Project-specific logic in plugin** — business-specific features (calculators, experts, guidance) in the CMS plugin | Plugin should be generic; business logic belongs in theme |

### P2 — Medium (Code Quality / Standards)

| # | Issue | Detail |
|---|-------|--------|
| 12 | **Dead/empty files** — 8 files with no content | Clutters codebase |
| 13 | **Misnamed file** — `explode_function.php` misleading | Should be `theme-helpers.php` |
| 14 | **Naming inconsistency** — `Term_Manager` lacks `AH_` prefix | Breaks convention |
| 15 | **Text domain mismatch** — plugin uses `ah-theme` text domain | Should be `cms-plugin` |
| 16 | **No dependency injection** — classes use `new` or static calls | Makes testing difficult |
| 17 | **Procedural service functions** — `apis/services.php` (665 lines), `apis/services_cms.php` (810 lines) | Should be service classes |
| 18 | **No error handling strategy** — no custom exceptions, no structured logging | Silent failures or `wp_die()` |
| 19 | **No tests** — zero unit or integration tests | No safety net |

### P3 — Low (Polish / Cleanup)

| # | Issue | Detail |
|---|-------|--------|
| 20 | **Duplicate screenshot** — `Screenshot.png` appears twice | Delete one |
| 21 | **CSS loaded on every page** — 14 CSS files always loaded | Should be conditional |
| 22 | **JS loaded on every page** — 9 JS files always loaded | Should be conditional |
| 23 | **Empty helper stubs** — `helper/common.php`, `helper/functions/*.php` | Remove |
| 24 | **`admin/import/samples/index.php`** — placeholder with no samples | Populate or remove |

---

## 5. Code Smells & Anti-Patterns

### God Objects

| Class | Lines | Should Be Split Into |
|-------|-------|---------------------|
| `AH_Workflow_Manager` | 2,118 | `RuleEngine`, `ConditionEvaluator`, `ActionExecutor`, `WorkflowCron`, `WorkflowRestApi`, `WorkflowAdminPage` |
| `ADN_Theme_Admin` | 1,717 | One controller per tab domain: `HomeController`, `CalculatorController`, `ExpertController`, etc. |
| `AH_Admin_Bootstrap` | 898 | `AdminHookRegistrar`, per-domain handlers: `NoticeHandler`, `BannerHandler`, `SpotlightHandler`, `NavigationHandler` |
| `ah_render_builder_block()` | 1,020 | One renderer per block type + `BlockRendererRegistry` |
| `common_functions.php` | 1,084 | `UrlHelper`, `IconHelper`, `PageHelper`, `LanguageHelper`, `ComponentRenderer` |

### Feature Envy
- Admin page templates query DB directly instead of calling models
- Theme `services_cms.php` reaches into plugin DB tables instead of using plugin's service layer

### Shotgun Surgery
- Adding a new admin page requires: menu registration + new template + potentially AJAX handlers — spread across 3+ files

### Data Clumps
- `$_POST['title'], $_POST['description'], $_POST['image']` repeated across many handlers — should be validated DTOs

---

## 6. Files to Rename, Move, Merge, Split, or Remove

### Remove (Dead Code)

| File | Reason |
|------|--------|
| `cms-plugin/constants.php` | Empty |
| `cms-plugin/helper/common.php` | Empty |
| `cms-plugin/helper/functions/includes.php` | Empty |
| `cms-plugin/helper/functions/routes.php` | Empty |
| `cms-plugin/helper/functions/api_resources.php` | Empty |
| `advaithhomes_new/Screenshot.png` (duplicate) | Duplicate |
| `advaithhomes_new/pages/page-newspage.php` | Empty |
| `advaithhomes_new/pages/page-posts_topic_category_guide.php` | Empty |
| `advaithhomes_new/components/parts/page_header.php` | Empty |

### Rename

| Current | Recommended | Reason |
|---------|-------------|--------|
| `explode_function.php` | `includes/theme-helpers.php` | Misleading name |
| `Term_Manager` class | `AH_Term_Manager` | Follows `AH_` prefix convention |

### Move to Feature Modules

| Current Location | Recommended Location | Reason |
|-----------------|---------------------|--------|
| Global functions in `admin/pages/featured-in.php` | `src/Feature/FeaturedIn/` | Functions belong in feature module |
| Global functions in `admin/pages/posts.php` | `src/Feature/Posts/` | Same |
| Global functions in `admin/pages/file-links.php` | `src/Feature/FileLinks/` | Same |
| `AH_Form_Builder::install_tables()` | `src/Database/Schema.php` | Table creation in schema layer |
| `AH_Newsletter::install()` | `src/Database/Schema.php` | Same |
| `apis/services_cms.php` (raw SQL) | Plugin service layer via interface | Theme should not bypass plugin services |

### Split

| Current File | Lines | Split Into |
|-------------|-------|------------|
| `AH_Workflow_Manager` | 2,118 | `RuleEngine` + `ConditionEvaluator` + `ActionExecutor` + `WorkflowCron` + `WorkflowRestApi` + `WorkflowAdminPage` |
| `ADN_Theme_Admin` | 1,717 | One controller per tab: `HomeController`, `CalculatorController`, `ExpertController`, `ContactController`, `GuidanceController`, `ImportExportController`, `CategoryController`, `AdminActionsController` |
| `AH_Admin_Bootstrap` | 898 | `AdminHookRegistrar` + per-domain handlers |
| `builder-block-renderer.php` | 1,020 | One renderer per block type + `BlockRendererRegistry` |
| `common_functions.php` | 1,084 | `UrlHelper`, `IconHelper`, `PageHelper`, `LanguageHelper`, `ComponentRenderer` |
| `services.php` | 665 | `JsonDataService`, `SiteChromeService`, `NavigationService`, etc. |
| `services_cms.php` | 810 | Plugin's `CmsDataService` via service contract |

---

## 7. Target Architecture — Plugin (Generic CMS Framework)

The plugin becomes a **reusable, generic CMS framework**. It contains zero project-specific logic. Business-specific features (calculators, experts, guidance, specific page types) belong in the theme or dedicated feature modules.

### Guiding Principle

> If you copy this plugin to another WordPress site and it works without modification, it is a proper CMS framework. If it contains references to "advaith", "homes", specific page slugs, or business-specific logic, it is not.

### Feature-First Plugin Structure

```
cms-plugin/
├── ah-cms.php                          # THIN BOOTSTRAP: constants + autoloader + lifecycle only
├── composer.json                        # PSR-4 autoloading, dependencies
│
├── src/                                 # All PHP classes under namespace Ah\Cms\
│   │
│   ├── Bootstrap/                       # ── LIFECYCLE ──
│   │   ├── PluginBootstrap.php          # Constants, hook registration, activation/deactivation
│   │   ├── AdminBootstrap.php           # Admin-specific hooks, menus, assets
│   │   ├── FrontendBootstrap.php        # Frontend-specific hooks, asset loading
│   │   ├── RestBootstrap.php            # REST API route registration
│   │   ├── AjaxBootstrap.php            # AJAX action registration
│   │   ├── CronBootstrap.php            # Scheduled task registration
│   │   ├── CliBootstrap.php             # WP-CLI command registration
│   │   └── HookRegistrar.php           # Centralized hooks/filters registration
│   │
│   ├── Database/                        # ── PERSISTENCE ──
│   │   ├── Connection.php               # wpdb wrapper (renamed from DB_Helper)
│   │   ├── Schema.php                   # All table definitions
│   │   ├── Installer.php                # Activation/upgrade orchestrator
│   │   ├── Migrations.php               # Column/data migrations
│   │   ├── Seed.php                     # Default data seeding
│   │   └── ForeignKeys.php              # FK constraints
│   │
│   ├── Feature/                         # ── SELF-CONTAINED FEATURE MODULES ──
│   │   │
│   │   ├── Workflow/                    # Workflow automation engine
│   │   │   ├── WorkflowModule.php       # Module entry: registers hooks, routes, assets
│   │   │   ├── Controller/
│   │   │   │   ├── WorkflowAdminController.php    # Admin page (GET/POST)
│   │   │   │   └── WorkflowRestController.php     # REST API endpoints
│   │   │   ├── Service/
│   │   │   │   ├── RuleEngine.php       # Rule CRUD + evaluation
│   │   │   │   ├── ConditionEvaluator.php # Condition matching logic
│   │   │   │   └── ActionExecutor.php   # Action dispatch (email/WhatsApp/HTTP/etc.)
│   │   │   ├── Model/
│   │   │   │   ├── Rule.php             # Domain entity
│   │   │   │   └── EvaluateLog.php      # Domain entity
│   │   │   ├── Repository/
│   │   │   │   ├── RulesRepository.php  # Data access for rules
│   │   │   │   └── EvaluateLogRepository.php
│   │   │   ├── Cron/
│   │   │   │   └── WorkflowCron.php     # Cron scheduling
│   │   │   ├── View/
│   │   │   │   └── workflow-admin.php   # Admin page template (HTML only)
│   │   │   ├── Config/
│   │   │   │   └── defaults.php         # Feature defaults
│   │   │   └── Assets/
│   │   │       ├── css/workflow.css
│   │   │       └── js/workflow.js
│   │   │
│   │   ├── FormBuilder/                 # Dynamic form builder
│   │   │   ├── FormBuilderModule.php    # Module entry
│   │   │   ├── Controller/
│   │   │   │   ├── FormBuilderAdminController.php
│   │   │   │   └── FormSubmitController.php
│   │   │   ├── Service/
│   │   │   │   ├── FormBuilder.php      # Form CRUD + rendering
│   │   │   │   └── FormValidator.php    # Field validation
│   │   │   ├── Model/
│   │   │   │   ├── Form.php
│   │   │   │   ├── FormField.php
│   │   │   │   └── FormSubmission.php
│   │   │   ├── Repository/
│   │   │   │   ├── FormsRepository.php
│   │   │   │   ├── FormFieldsRepository.php
│   │   │   │   └── FormSubmissionsRepository.php
│   │   │   ├── Shortcode/
│   │   │   │   └── FormShortcode.php    # [ah_form] shortcode
│   │   │   ├── View/
│   │   │   │   ├── form-admin.php
│   │   │   │   └── form-renderer.php    # Frontend form template
│   │   │   └── Assets/
│   │   │       └── css/form-builder.css
│   │   │
│   │   ├── Newsletter/                  # Newsletter subscription
│   │   │   ├── NewsletterModule.php
│   │   │   ├── Controller/
│   │   │   │   ├── NewsletterAdminController.php
│   │   │   │   └── NewsletterAjaxController.php
│   │   │   ├── Service/
│   │   │   │   ├── NewsletterService.php
│   │   │   │   └── EmailDispatcher.php
│   │   │   ├── Model/
│   │   │   │   ├── Newsletter.php
│   │   │   │   └── NewsletterSubscriber.php
│   │   │   ├── Repository/
│   │   │   │   └── NewsletterRepository.php
│   │   │   └── View/
│   │   │       └── newsletter-admin.php
│   │   │
│   │   ├── CustomCode/                  # Per-slug CSS/JS injection
│   │   │   ├── CustomCodeModule.php
│   │   │   ├── Controller/
│   │   │   │   └── CustomCodeAdminController.php
│   │   │   ├── Service/
│   │   │   │   └── CustomCodeService.php
│   │   │   ├── Model/
│   │   │   │   └── CustomCode.php
│   │   │   ├── Repository/
│   │   │   │   └── CustomCodeRepository.php
│   │   │   └── View/
│   │   │       └── custom-code-admin.php
│   │   │
│   │   ├── Redirect/                    # URL redirect rules
│   │   │   ├── RedirectModule.php
│   │   │   ├── Controller/
│   │   │   │   └── RedirectAdminController.php
│   │   │   ├── Service/
│   │   │   │   └── RedirectService.php
│   │   │   ├── Model/
│   │   │   │   └── RedirectRule.php
│   │   │   ├── Repository/
│   │   │   │   └── RedirectRulesRepository.php
│   │   │   └── View/
│   │   │       └── redirect-admin.php
│   │   │
│   │   ├── Cache/                       # Cache management
│   │   │   ├── CacheModule.php
│   │   │   ├── Service/
│   │   │   │   ├── CacheManager.php
│   │   │   │   └── CacheStorage.php
│   │   │   └── Controller/
│   │   │       └── CacheAdminController.php
│   │   │
│   │   ├── Settings/                    # Site settings
│   │   │   ├── SettingsModule.php
│   │   │   ├── Controller/
│   │   │   │   └── SettingsAdminController.php
│   │   │   ├── Service/
│   │   │   │   └── SettingsService.php
│   │   │   ├── Model/
│   │   │   │   └── Setting.php
│   │   │   ├── Repository/
│   │   │   │   └── SettingsRepository.php
│   │   │   └── View/
│   │   │       └── settings-admin.php
│   │   │
│   │   ├── Pages/                       # CMS pages management
│   │   │   ├── PagesModule.php
│   │   │   ├── Controller/
│   │   │   │   ├── PagesAdminController.php
│   │   │   │   ├── PageBuilderController.php
│   │   │   │   └── StaticPageController.php
│   │   │   ├── Service/
│   │   │   │   └── PageService.php
│   │   │   ├── Model/
│   │   │   │   ├── Page.php
│   │   │   │   ├── PageSection.php
│   │   │   │   └── StaticPage.php
│   │   │   ├── Repository/
│   │   │   │   ├── PagesRepository.php
│   │   │   │   ├── PageSectionsRepository.php
│   │   │   │   └── StaticPagesRepository.php
│   │   │   ├── Renderer/
│   │   │   │   ├── BlockRendererRegistry.php
│   │   │   │   ├── BuilderPageRenderer.php
│   │   │   │   ├── StaticPageRenderer.php
│   │   │   │   └── Block/
│   │   │   │       ├── HeroBlock.php
│   │   │   │       ├── HighlightsBlock.php
│   │   │   │       ├── WhyUsBlock.php
│   │   │   │       ├── GuideThroughBlock.php
│   │   │   │       └── ... (one per block type)
│   │   │   ├── Shortcode/
│   │   │   │   ├── StaticPageShortcode.php
│   │   │   │   └── ResourceShortcode.php
│   │   │   ├── View/
│   │   │   │   ├── pages-admin.php
│   │   │   │   └── builder-admin.php
│   │   │   └── Assets/
│   │   │       └── css/builder-page.css
│   │   │
│   │   ├── Posts/                       # Blog posts management
│   │   │   ├── PostsModule.php
│   │   │   ├── Controller/
│   │   │   │   └── PostsAdminController.php
│   │   │   ├── Service/
│   │   │   │   └── PostService.php
│   │   │   ├── Model/
│   │   │   │   └── Post.php
│   │   │   ├── Repository/
│   │   │   │   └── PostsRepository.php
│   │   │   └── View/
│   │   │       └── posts-admin.php
│   │   │
│   │   ├── Taxonomy/                    # Content taxonomy
│   │   │   ├── TaxonomyModule.php
│   │   │   ├── Controller/
│   │   │   │   └── TaxonomyAdminController.php
│   │   │   ├── Service/
│   │   │   │   └── TaxonomyService.php
│   │   │   ├── Model/
│   │   │   │   ├── Taxonomy.php
│   │   │   │   ├── TaxonomyType.php
│   │   │   │   ├── TaxonomyParentTerm.php
│   │   │   │   └── ContentTaxonomy.php
│   │   │   ├── Repository/
│   │   │   │   ├── TaxonomyRepository.php
│   │   │   │   ├── TaxonomyTypesRepository.php
│   │   │   │   ├── TaxonomyParentTermsRepository.php
│   │   │   │   └── ContentTaxonomyRepository.php
│   │   │   └── View/
│   │   │       └── taxonomy-admin.php
│   │   │
│   │   ├── Navigation/                  # Site navigation
│   │   │   ├── NavigationModule.php
│   │   │   ├── Controller/
│   │   │   │   └── NavigationAdminController.php
│   │   │   ├── Service/
│   │   │   │   └── NavigationService.php
│   │   │   ├── Model/
│   │   │   │   └── Navigation.php
│   │   │   ├── Repository/
│   │   │   │   └── NavigationRepository.php
│   │   │   └── View/
│   │   │       └── navigation-admin.php
│   │   │
│   │   ├── Reviews/                     # Customer reviews
│   │   │   ├── ReviewsModule.php
│   │   │   ├── Controller/
│   │   │   │   └── ReviewsAdminController.php
│   │   │   ├── Model/
│   │   │   │   └── Review.php
│   │   │   ├── Repository/
│   │   │   │   └── ReviewsRepository.php
│   │   │   └── View/
│   │   │       └── reviews-admin.php
│   │   │
│   │   ├── FAQs/                        # FAQ management
│   │   │   ├── FaqsModule.php
│   │   │   ├── Controller/
│   │   │   │   └── FaqsAdminController.php
│   │   │   ├── Model/
│   │   │   │   └── Faq.php
│   │   │   ├── Repository/
│   │   │   │   └── FaqsRepository.php
│   │   │   └── View/
│   │   │       └── faqs-admin.php
│   │   │
│   │   ├── Resources/                   # Resource links
│   │   │   ├── ResourcesModule.php
│   │   │   ├── Controller/
│   │   │   │   └── ResourcesAdminController.php
│   │   │   ├── Model/
│   │   │   │   └── Resource.php
│   │   │   ├── Repository/
│   │   │   │   └── ResourcesRepository.php
│   │   │   ├── Shortcode/
│   │   │   │   └── RelatedLinksShortcode.php
│   │   │   └── View/
│   │   │       └── resources-admin.php
│   │   │
│   │   ├── Spotlights/                  # Spotlight features
│   │   │   ├── SpotlightsModule.php
│   │   │   ├── Controller/
│   │   │   │   └── SpotlightsAdminController.php
│   │   │   ├── Model/
│   │   │   │   ├── Spotlight.php
│   │   │   │   └── SpotlightTerm.php
│   │   │   ├── Repository/
│   │   │   │   ├── SpotlightsRepository.php
│   │   │   │   └── SpotlightTermsRepository.php
│   │   │   └── View/
│   │   │       └── spotlights-admin.php
│   │   │
│   │   ├── Banners/                     # Home page banners
│   │   │   ├── BannersModule.php
│   │   │   ├── Controller/
│   │   │   │   └── BannersAdminController.php
│   │   │   ├── Model/
│   │   │   │   └── HomeBanner.php
│   │   │   ├── Repository/
│   │   │   │   └── HomeBannersRepository.php
│   │   │   └── View/
│   │   │       └── banners-admin.php
│   │   │
│   │   ├── Events/                      # Events management
│   │   │   ├── EventsModule.php
│   │   │   ├── Controller/
│   │   │   │   └── EventsAdminController.php
│   │   │   ├── Model/
│   │   │   │   └── Event.php
│   │   │   ├── Repository/
│   │   │   │   └── EventsRepository.php
│   │   │   └── View/
│   │   │       └── events-admin.php
│   │   │
│   │   ├── SiteNotices/                 # Site-wide notices
│   │   │   ├── SiteNoticesModule.php
│   │   │   ├── Controller/
│   │   │   │   └── SiteNoticesAdminController.php
│   │   │   ├── Model/
│   │   │   │   └── SiteNotice.php
│   │   │   ├── Repository/
│   │   │   │   └── SiteNoticesRepository.php
│   │   │   └── View/
│   │   │       └── notices-admin.php
│   │   │
│   │   ├── NewsBar/                     # News bar items
│   │   │   ├── NewsBarModule.php
│   │   │   ├── Controller/
│   │   │   │   └── NewsBarAdminController.php
│   │   │   ├── Model/
│   │   │   │   └── NewsBarItem.php
│   │   │   ├── Repository/
│   │   │   │   └── NewsBarRepository.php
│   │   │   └── View/
│   │   │       └── news-bar-admin.php
│   │   │
│   │   ├── FeaturedIn/                  # Featured-in logos
│   │   │   ├── FeaturedInModule.php
│   │   │   ├── Controller/
│   │   │   │   └── FeaturedInAdminController.php
│   │   │   ├── Model/
│   │   │   │   └── FeaturesIn.php
│   │   │   ├── Repository/
│   │   │   │   └── FeaturesInRepository.php
│   │   │   └── View/
│   │   │       └── featured-in-admin.php
│   │   │
│   │   ├── Media/                       # Media library
│   │   │   ├── MediaModule.php
│   │   │   ├── Controller/
│   │   │   │   └── MediaAdminController.php
│   │   │   ├── Model/
│   │   │   │   └── Media.php
│   │   │   ├── Repository/
│   │   │   │   └── MediaRepository.php
│   │   │   └── View/
│   │   │       └── media-admin.php
│   │   │
│   │   ├── FileLinks/                   # File download links
│   │   │   ├── FileLinksModule.php
│   │   │   ├── Controller/
│   │   │   │   └── FileLinksAdminController.php
│   │   │   ├── Model/
│   │   │   │   └── FileLink.php
│   │   │   ├── Repository/
│   │   │   │   └── FileLinksRepository.php
│   │   │   └── View/
│   │   │       └── file-links-admin.php
│   │   │
│   │   ├── Visitors/                    # Visitor tracking
│   │   │   ├── VisitorsModule.php
│   │   │   ├── Controller/
│   │   │   │   ├── VisitorsAdminController.php
│   │   │   │   └── VisitorPingRestController.php
│   │   │   ├── Model/
│   │   │   │   └── Visitor.php
│   │   │   ├── Repository/
│   │   │   │   └── VisitorRepository.php
│   │   │   └── View/
│   │   │       └── visitors-admin.php
│   │   │
│   │   ├── Analytics/                   # Analytics reports
│   │   │   ├── AnalyticsModule.php
│   │   │   ├── Controller/
│   │   │   │   ├── AnalyticsAdminController.php
│   │   │   │   └── AnalyticsAjaxController.php
│   │   │   ├── Model/
│   │   │   │   ├── AnalyticsReport.php
│   │   │   │   └── AnalyticsResult.php
│   │   │   ├── Repository/
│   │   │   │   ├── AnalyticsReportsRepository.php
│   │   │   │   └── AnalyticsResultsRepository.php
│   │   │   └── View/
│   │   │       └── analytics-admin.php
│   │   │
│   │   ├── Audit/                       # Audit logging
│   │   │   ├── AuditModule.php
│   │   │   ├── Service/
│   │   │   │   └── AuditService.php
│   │   │   ├── Model/
│   │   │   │   └── AuditLog.php
│   │   │   ├── Repository/
│   │   │   │   └── AuditRepository.php
│   │   │   └── View/
│   │   │       └── audit-log-admin.php
│   │   │
│   │   ├── Import/                      # CSV import
│   │   │   ├── ImportModule.php
│   │   │   ├── Controller/
│   │   │   │   └── ImportAdminController.php
│   │   │   ├── Service/
│   │   │   │   └── CsvImporter.php
│   │   │   └── View/
│   │   │       └── import-admin.php
│   │   │
│   │   └── AdminTools/                  # Developer/admin tools
│   │       ├── AdminToolsModule.php
│   │       ├── Controller/
│   │       │   └── AdminToolsController.php
│   │       └── View/
│   │           └── admin-tools.php
│   │
│   ├── Http/                            # ── REQUEST HANDLING ──
│   │   ├── Ajax/
│   │   │   ├── AjaxDispatcher.php       # Routes AJAX actions to feature controllers
│   │   │   └── Validator.php            # Common AJAX input validation
│   │   └── Rest/
│   │       ├── Routes.php               # REST route registration (aggregates from features)
│   │       └── RestController.php       # Base REST controller
│   │
│   ├── Model/                           # ── DOMAIN MODELS ──
│   │   └── AbstractModel.php            # Base model (replaces AH_Model_Base)
│   │
│   ├── Repository/                      # ── REPOSITORIES ──
│   │   └── AbstractRepository.php       # Base CRUD (find, findAll, insert, update, delete)
│   │
│   ├── Helper/                          # ── UTILITIES ──
│   │   ├── SlugHelper.php
│   │   ├── PaginationHelper.php
│   │   ├── ValidatorHelper.php
│   │   ├── UploaderHelper.php
│   │   ├── NoticeHelper.php
│   │   └── StringHelper.php
│   │
│   ├── Config/                          # ── CONFIGURATION ──
│   │   ├── Defaults.php                 # Default settings
│   │   └── DatabaseConfig.php           # DB table names, column names
│   │
│   ├── Exception/                       # ── ERROR HANDLING ──
│   │   ├── PluginException.php          # Base exception
│   │   ├── ValidationException.php
│   │   ├── UnauthorizedException.php
│   │   ├── NotFoundException.php
│   │   └── DatabaseException.php
│   │
│   └── Support/                         # ── CROSS-CUTTING ──
│       ├── Logger.php                   # Structured logging
│       └── ErrorHandler.php             # Global error/exception handler
│
├── templates/                           # PHP view templates (no classes)
│   ├── admin/                           # Admin page views (HTML only — controllers handle logic)
│   │   ├── layout/
│   │   │   ├── header.php
│   │   │   └── footer.php
│   │   └── pages/
│   │       ├── dashboard.php
│   │       └── ...
│   └── frontend/
│       ├── builder-page.php
│       └── static-page.php
│
├── assets/                              # Shared static assets
│   ├── css/
│   │   ├── variables.css
│   │   ├── animations.css
│   │   └── main.css
│   ├── js/
│   │   └── main.js
│   └── images/
│       └── logo.png
│
├── languages/                           # Translation files
│   ├── cms-plugin.pot
│   ├── cms-plugin-en_US.po
│   └── cms-plugin-en_US.mo
│
└── database/                            # SQL migration files only
    └── migrations/
```

### Plugin Namespace Structure

```
Ah\Cms\
├── Bootstrap\           PluginBootstrap, AdminBootstrap, FrontendBootstrap, RestBootstrap, AjaxBootstrap, CronBootstrap, CliBootstrap, HookRegistrar
├── Database\            Connection, Schema, Installer, Migrations, Seed, ForeignKeys
├── Feature\             (feature modules — each is a sub-namespace)
│   ├── Workflow\        Ah\Cms\Feature\Workflow\{Module, Controller, Service, Model, Repository, ...}
│   ├── FormBuilder\     Ah\Cms\Feature\FormBuilder\{...}
│   ├── Newsletter\      Ah\Cms\Feature\Newsletter\{...}
│   ├── CustomCode\      Ah\Cms\Feature\CustomCode\{...}
│   ├── Redirect\        Ah\Cms\Feature\Redirect\{...}
│   ├── Cache\           Ah\Cms\Feature\Cache\{...}
│   ├── Settings\        Ah\Cms\Feature\Settings\{...}
│   ├── Pages\           Ah\Cms\Feature\Pages\{...}
│   ├── Posts\           Ah\Cms\Feature\Posts\{...}
│   ├── Taxonomy\        Ah\Cms\Feature\Taxonomy\{...}
│   ├── Navigation\      Ah\Cms\Feature\Navigation\{...}
│   ├── Reviews\         Ah\Cms\Feature\Reviews\{...}
│   ├── FAQs\            Ah\Cms\Feature\FAQs\{...}
│   ├── Resources\       Ah\Cms\Feature\Resources\{...}
│   ├── Spotlights\      Ah\Cms\Feature\Spotlights\{...}
│   ├── Banners\         Ah\Cms\Feature\Banners\{...}
│   ├── Events\          Ah\Cms\Feature\Events\{...}
│   ├── SiteNotices\     Ah\Cms\Feature\SiteNotices\{...}
│   ├── NewsBar\         Ah\Cms\Feature\NewsBar\{...}
│   ├── FeaturedIn\      Ah\Cms\Feature\FeaturedIn\{...}
│   ├── Media\           Ah\Cms\Feature\Media\{...}
│   ├── FileLinks\       Ah\Cms\Feature\FileLinks\{...}
│   ├── Visitors\        Ah\Cms\Feature\Visitors\{...}
│   ├── Analytics\       Ah\Cms\Feature\Analytics\{...}
│   ├── Audit\           Ah\Cms\Feature\Audit\{...}
│   ├── Import\          Ah\Cms\Feature\Import\{...}
│   └── AdminTools\      Ah\Cms\Feature\AdminTools\{...}
├── Http\                Ajax\AjaxDispatcher, Ajax\Validator, Rest\Routes, Rest\RestController
├── Model\               AbstractModel
├── Repository\          AbstractRepository
├── Helper\              SlugHelper, PaginationHelper, ValidatorHelper, ...
├── Config\              Defaults, DatabaseConfig
├── Exception\           PluginException, ValidationException, ...
└── Support\             Logger, ErrorHandler
```

### Plugin Module Entry Point Pattern

Every feature module has a `*Module.php` entry point that registers all hooks, routes, and assets for that feature:

```php
namespace Ah\Cms\Feature\Workflow;

class WorkflowModule
{
    public static function register(): void
    {
        // Hooks
        add_action('init', [self::class, 'registerHooks']);
        add_action('admin_menu', [WorkflowAdminController::class, 'registerMenu']);

        // REST API
        add_action('rest_api_init', [WorkflowRestController::class, 'registerRoutes']);

        // AJAX
        add_action('wp_ajax_ah_workflow_save_rule', [WorkflowAdminController::class, 'saveRule']);

        // Cron
        add_action('ah_workflow_cron', [WorkflowCron::class, 'process']);

        // Assets
        add_action('admin_enqueue_scripts', [self::class, 'enqueueAssets']);
    }

    public static function enqueueAssets(string $hook): void
    {
        if (strpos($hook, 'ah_workflow') === false) return;
        wp_enqueue_style('ah-workflow', AH_PLUGIN_URL . 'src/Feature/Workflow/Assets/css/workflow.css');
        wp_enqueue_script('ah-workflow', AH_PLUGIN_URL . 'src/Feature/Workflow/Assets/js/workflow.js', ['jquery'], '1.0', true);
    }
}
```

---

## 8. Target Architecture — Theme (Presentation Layer)

The theme contains **only presentation and website-specific functionality**. Business logic lives in the plugin's service layer. The theme is organized by feature/page, not by file type.

### Guiding Principle

> The theme renders what the plugin provides. If the theme contains SQL queries, business rules, or data processing that isn't purely presentation-related, it belongs in the plugin.

### Feature-First Theme Structure

```
advaithhomes_new/
├── functions.php                        # THIN BOOTSTRAP: constants + autoloader + lifecycle only
├── composer.json                        # PSR-4 autoloading
│
├── src/                                 # All PHP classes under namespace Adn\Theme\
│   │
│   ├── Bootstrap/
│   │   └── ThemeBootstrap.php           # Theme setup, hooks, lifecycle
│   │
│   ├── Feature/                         # ── PAGE FEATURES (self-contained) ──
│   │   │
│   │   ├── Home/                        # Home page feature
│   │   │   ├── HomeFeature.php          # Module entry: registers routes, hooks
│   │   │   ├── Controller/
│   │   │   │   └── HomeController.php   # Data preparation + template rendering
│   │   │   ├── Service/
│   │   │   │   └── HomeDataService.php  # Aggregates data from plugin services + JSON
│   │   │   ├── View/
│   │   │   │   ├── page-home.php        # WordPress page template
│   │   │   │   └── intermediate/
│   │   │   │       └── home-logical.php # Data preparation (replaces intermediate/)
│   │   │   └── Assets/
│   │   │       ├── css/                 # Home-specific CSS (if any beyond shared)
│   │   │       └── js/                  # Home-specific JS (if any beyond shared)
│   │   │
│   │   ├── CategoryGuide/               # Category/guide pages
│   │   │   ├── CategoryGuideFeature.php
│   │   │   ├── Controller/
│   │   │   │   └── CategoryGuideController.php
│   │   │   ├── Service/
│   │   │   │   └── CategoryGuideDataService.php
│   │   │   ├── View/
│   │   │   │   ├── page-category_guide.php
│   │   │   │   ├── page-topic_category_guide.php
│   │   │   │   └── intermediate/
│   │   │   │       ├── category-logical.php
│   │   │   │       └── topic-category-logical.php
│   │   │   └── Assets/
│   │   │       ├── css/guidance.css
│   │   │       └── js/guidance.js
│   │   │
│   │   ├── Article/                     # Blog post / article pages
│   │   │   ├── ArticleFeature.php
│   │   │   ├── Controller/
│   │   │   │   └── ArticleController.php
│   │   │   ├── Service/
│   │   │   │   └── ArticleDataService.php
│   │   │   ├── View/
│   │   │   │   ├── single.php
│   │   │   │   └── intermediate/
│   │   │   │       └── post-logical.php
│   │   │   └── Assets/
│   │   │       ├── css/article.css
│   │   │       └── js/single.js
│   │   │
│   │   ├── Tools/                       # Tools/calculators listing + detail
│   │   │   ├── ToolsFeature.php
│   │   │   ├── Controller/
│   │   │   │   ├── ToolsController.php
│   │   │   │   └── ToolSingleController.php
│   │   │   ├── Service/
│   │   │   │   └── ToolsDataService.php
│   │   │   ├── View/
│   │   │   │   ├── page-tools.php
│   │   │   │   ├── page-tool-single.php
│   │   │   │   └── intermediate/
│   │   │   │       ├── tools-logical.php
│   │   │   │       └── tool-single-logical.php
│   │   │   └── Assets/
│   │   │       ├── css/tools.css
│   │   │       └── js/tools.js
│   │   │
│   │   ├── News/                        # News pages
│   │   │   ├── NewsFeature.php
│   │   │   ├── Controller/
│   │   │   │   └── NewsController.php
│   │   │   ├── Service/
│   │   │   │   └── NewsDataService.php
│   │   │   ├── View/
│   │   │   │   ├── page-newsall.php
│   │   │   │   └── intermediate/
│   │   │   │       └── news-logical.php
│   │   │   └── Assets/
│   │   │       ├── css/news.css
│   │   │       └── js/news.js
│   │   │
│   │   ├── Contact/                     # Contact page
│   │   │   ├── ContactFeature.php
│   │   │   ├── Controller/
│   │   │   │   └── ContactController.php
│   │   │   ├── Service/
│   │   │   │   └── ContactDataService.php
│   │   │   ├── View/
│   │   │   │   ├── page-contact.php
│   │   │   │   └── intermediate/
│   │   │   │       └── contact-logical.php
│   │   │   └── Assets/
│   │   │       ├── css/contact.css
│   │   │       └── js/contact.js
│   │   │
│   │   ├── AskExpert/                   # Expert ask + single expert
│   │   │   ├── AskExpertFeature.php
│   │   │   ├── Controller/
│   │   │   │   ├── AskExpertController.php
│   │   │   │   └── ExpertSingleController.php
│   │   │   ├── Service/
│   │   │   │   └── ExpertDataService.php
│   │   │   ├── View/
│   │   │   │   ├── page-ask-expert.php
│   │   │   │   ├── page-expert-single.php
│   │   │   │   └── intermediate/
│   │   │   │       ├── ask-expert-logical.php
│   │   │   │       └── expert-single-logical.php
│   │   │   └── Assets/
│   │   │       ├── css/ask_expert.css
│   │   │       └── js/ask_expert.js
│   │   │
│   │   ├── Guidance/                    # Guidance page
│   │   │   ├── GuidanceFeature.php
│   │   │   ├── Controller/
│   │   │   │   └── GuidanceController.php
│   │   │   ├── Service/
│   │   │   │   └── GuidanceDataService.php
│   │   │   ├── View/
│   │   │   │   ├── page-guidance.php
│   │   │   │   └── intermediate/
│   │   │   │       └── guidance-logical.php
│   │   │   └── Assets/
│   │   │       └── css/guidance.css
│   │   │
│   │   ├── GuidesListing/               # Guides listing
│   │   │   ├── GuidesListingFeature.php
│   │   │   ├── Controller/
│   │   │   │   └── GuidesListingController.php
│   │   │   ├── Service/
│   │   │   │   └── GuidesListingDataService.php
│   │   │   ├── View/
│   │   │   │   ├── page-guides_listing.php
│   │   │   │   └── intermediate/
│   │   │   │       └── guides-listing-logical.php
│   │   │   └── Assets/
│   │   │       └── css/guides_listing.css
│   │   │
│   │   ├── FAQs/                        # FAQs page
│   │   │   ├── FaqsFeature.php
│   │   │   ├── Controller/
│   │   │   │   └── FaqsController.php
│   │   │   ├── Service/
│   │   │   │   └── FaqsDataService.php
│   │   │   └── View/
│   │   │       └── page-faqs.php
│   │   │
│   │   └── ComingSoon/                  # Maintenance/coming soon page
│   │       ├── ComingSoonFeature.php
│   │       └── View/
│   │           └── page-coming.php
│   │
│   ├── Admin/                           # ── THEME ADMIN ──
│   │   ├── ThemeAdminController.php     # Slim: routes to tab controllers
│   │   ├── SettingsEngine.php           # Reusable settings render/save (existing, unchanged)
│   │   ├── Tab/                         # Tab controllers (one per admin tab)
│   │   │   ├── AbstractTab.php          # Base tab with capability check + rendering
│   │   │   ├── DashboardTab.php
│   │   │   ├── HomeTab.php
│   │   │   ├── CalculatorTab.php
│   │   │   ├── ExpertTab.php
│   │   │   ├── ContactInboxTab.php
│   │   │   ├── GuidanceInboxTab.php
│   │   │   ├── ImportExportTab.php
│   │   │   ├── CategoryTab.php
│   │   │   └── AdminActionsTab.php
│   │   ├── Schema/
│   │   │   └── SettingsSchemas.php      # Tab/subtab schema definitions
│   │   └── View/
│   │       └── tabs/                    # Tab view templates (HTML only)
│   │           ├── dashboard.php
│   │           ├── home/
│   │           │   ├── sections.php
│   │           │   ├── hero.php
│   │           │   └── ...
│   │           └── ...
│   │
│   ├── Api/                             # ── REST API ──
│   │   ├── ThemeRestRoutes.php          # Theme REST endpoint registration
│   │   └── HomeFragmentCache.php        # Home page fragment caching
│   │
│   ├── Service/                         # ── THEME SERVICES ──
│   │   ├── SiteChromeService.php        # Header/footer/nav data (replaces services.php)
│   │   ├── NavigationService.php        # Menu data
│   │   ├── CmsDataService.php           # Plugin DB data access (via plugin's service contract)
│   │   ├── CalculatorService.php        # Calculator registry + DB
│   │   ├── ExpertService.php            # Expert/team member data
│   │   ├── EnquiryService.php           # Contact/guidance enquiry CRUD
│   │   └── SeoService.php               # Meta tags, OG, JSON-LD
│   │
│   ├── DataReader/                      # ── DATA ACCESS ──
│   │   ├── DataReaderInterface.php      # Contract
│   │   ├── JsonReader.php
│   │   ├── CsvReader.php
│   │   ├── HtmlReader.php
│   │   └── DataLoader.php               # Facade (replaces ADN_Real_Loader)
│   │
│   ├── Routing/                         # ── ROUTING ──
│   │   └── TemplateRouter.php           # Virtual page routing (replaces core_routing.php)
│   │
│   ├── Cache/
│   │   └── FileSystemCache.php          # Theme-level caching
│   │
│   └── Helper/                          # ── UTILITIES ──
│       ├── UrlHelper.php
│       ├── IconHelper.php
│       ├── LanguageHelper.php
│       ├── PageHelper.php               # adn_page_open/close
│       ├── ComponentRenderer.php        # adn_component()
│       └── StringHelper.php
│
├── components/                          # Reusable PHP view partials (NO business logic)
│   ├── cards/                           # 15 card components (unchanged)
│   ├── parts/                           # 47+ reusable parts (unchanged)
│   ├── sections/                        # 51 section components (unchanged)
│   ├── form_builder/                    # Form builder component
│   └── marque_scroll/                   # Marquee scroll component
│
├── assets/                              # Shared static assets
│   ├── css/
│   │   ├── variables.css
│   │   ├── common.css
│   │   ├── common_utils.css
│   │   ├── main.css
│   │   ├── chrome.css
│   │   ├── shared.css
│   │   └── fonts.css
│   ├── js/
│   │   ├── main.js
│   │   ├── common.js
│   │   ├── common_utils.js
│   │   └── scroll-to-top.js
│   ├── fonts/
│   └── images/
│
├── data/                                # Static content
│   └── advaith/
│       ├── json/                        # 14 JSON files (unchanged)
│       ├── csv/
│       └── html/
│
├── calculators/                         # Calculator system
│   ├── views/                           # Calculator view templates
│   ├── assets/
│   │   ├── css/calculators.css
│   │   └── js/calculators.js
│   └── registry.php                     # Calculator registry
│
├── languages/                           # Translation files
│   ├── en.php
│   └── te.php
│
└── static/                              # Static HTML files
    └── ...
```

### Theme Namespace Structure

```
Adn\Theme\
├── Bootstrap\           ThemeBootstrap
├── Feature\             (page features — each is a sub-namespace)
│   ├── Home\            Adn\Theme\Feature\Home\{Controller, Service, View}
│   ├── CategoryGuide\   Adn\Theme\Feature\CategoryGuide\{...}
│   ├── Article\         Adn\Theme\Feature\Article\{...}
│   ├── Tools\           Adn\Theme\Feature\Tools\{...}
│   ├── News\            Adn\Theme\Feature\News\{...}
│   ├── Contact\         Adn\Theme\Feature\Contact\{...}
│   ├── AskExpert\       Adn\Theme\Feature\AskExpert\{...}
│   ├── Guidance\        Adn\Theme\Feature\Guidance\{...}
│   ├── GuidesListing\   Adn\Theme\Feature\GuidesListing\{...}
│   ├── FAQs\            Adn\Theme\Feature\FAQs\{...}
│   └── ComingSoon\      Adn\Theme\Feature\ComingSoon\{...}
├── Admin\
│   ├── Tab\             AbstractTab, DashboardTab, HomeTab, ...
│   └── Schema\          SettingsSchemas
├── Api\                 ThemeRestRoutes, HomeFragmentCache
├── Service\             SiteChromeService, CmsDataService, CalculatorService, ...
├── DataReader\          DataReaderInterface, JsonReader, CsvReader, ...
├── Routing\             TemplateRouter
├── Cache\               FileSystemCache
└── Helper\              UrlHelper, IconHelper, LanguageHelper, ...
```

---

## 9. Feature Module Map

### Plugin Features (Generic CMS)

| Feature Module | Purpose | Key Classes | DB Tables |
|---------------|---------|-------------|-----------|
| `Workflow` | Automation rules engine | RuleEngine, ConditionEvaluator, ActionExecutor | ah_rules, ah_evaluate_log |
| `FormBuilder` | Dynamic form builder | FormBuilder, FormValidator | ah_forms, ah_form_fields, ah_form_submissions |
| `Newsletter` | Email subscriptions | NewsletterService, EmailDispatcher | ah_newsletters, ah_newsletter_subscribers |
| `CustomCode` | Per-slug CSS/JS | CustomCodeService | ah_custom_code |
| `Redirect` | URL redirects | RedirectService | ah_redirect_rules |
| `Cache` | Cache management | CacheManager, CacheStorage | (transients/options) |
| `Settings` | Site settings | SettingsService | ah_site_settings |
| `Pages` | CMS pages + builder | PageService, BuilderPageRenderer | ah_pages, ah_page_sections, ah_builder_pages, ah_static_pages |
| `Posts` | Blog posts | PostService | ah_posts, ah_post_taxonomies |
| `Taxonomy` | Content taxonomy | TaxonomyService | ah_taxonomy_types, ah_taxonomies, ah_taxonomy_parent_terms, ah_content_taxonomies |
| `Navigation` | Site menus | NavigationService | ah_navigation (via options) |
| `Reviews` | Customer reviews | — | ah_reviews, ah_review_images |
| `FAQs` | FAQ management | — | ah_faqs |
| `Resources` | Resource links | — | ah_resources, ah_related_links |
| `Spotlights` | Spotlight features | — | ah_spotlights, ah_spotlight_terms |
| `Banners` | Home banners | — | ah_home_banners |
| `Events` | Events | — | ah_events |
| `SiteNotices` | Site notices | — | ah_site_notices |
| `NewsBar` | News bar | — | ah_news_bar_items |
| `FeaturedIn` | Featured logos | — | ah_features_in |
| `Media` | Media library | — | ah_media, ah_client_story_images, ah_client_gallery, ah_client_video_links |
| `FileLinks` | File downloads | — | ah_related_links |
| `Visitors` | Visitor tracking | — | ah_visitor_logs |
| `Analytics` | Analytics reports | — | ah_analytics_reports, ah_analytics_results |
| `Audit` | Audit logging | AuditService | ah_audit_logs, ah_trigger_logs |
| `Import` | CSV import | CsvImporter | (imports into other features) |
| `AdminTools` | Dev tools | — | (operational) |

### Theme Features (Page-Specific)

| Feature Module | Page(s) | Sections Used | CSS | JS |
|---------------|---------|---------------|-----|-----|
| `Home` | page-home.php | hero_home, home_banners_carousel, home_deferred_section, journey | (shared) | (shared) |
| `CategoryGuide` | page-category_guide, page-topic_category_guide | category_hero, category_journey, category_resources, category_control_center | guidance.css, guides_listing.css | guidance.js, guides_listing.js |
| `Article` | single.php | article_header, article_body, article_author, article_feedback, article_key_info | article.css, single.css | single.js |
| `Tools` | page-tools, page-tool-single | tools_hero, tools_all, tools_categories, tools_popular | tools.css | tools.js |
| `News` | page-newsall | news_hero, news_featured, news_section, news_three_col, news_cats_strip | news.css | news.js |
| `Contact` | page-contact | contact_hero, contact_form, contact_process, contact_resources | contact.css | contact.js |
| `AskExpert` | page-ask-expert, page-expert-single | expert_hero, expert_cant_find, expert_cats_strip | ask_expert.css | ask_expert.js |
| `Guidance` | page-guidance | guidance_hero, guidance_form, guidance_services, guidance_why_choose | guidance.css | guidance.js |
| `GuidesListing` | page-guides_listing | guides, guides_grid, guides_hero, guides_parent_group | guides_listing.css | guides_listing.js |
| `FAQs` | page-faqs | faqs_footer | faqs.css | faqs.js |

---

## 10. Models & Data Layer

### Domain Model Design

One model per entity. Models are plain PHP objects with typed properties. No database logic.

```php
namespace Ah\Cms\Feature\Pages\Model;

class Page
{
    public int $id;
    public string $title;
    public string $slug;
    public string $type;         // 'builder', 'static', etc.
    public string $status;       // 'published', 'draft', 'archived'
    public ?string $blocks;      // JSON content
    public string $createdAt;
    public string $updatedAt;

    // Domain methods (business logic that belongs on the entity)
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function hasBlocks(): bool
    {
        return !empty($this->blocks);
    }
}
```

### Entity Relationship Map

```
Page ─────────┬── PageSection (1:N)
              ├── RelatedLink (1:N)
              └── CustomCode (1:N, via slug)

Post ─────────┬── PostTaxonomy (N:M)
              ├── ContentTaxonomy (N:M)
              └── Resource (1:N)

Taxonomy ─────┬── TaxonomyType (N:1)
              ├── TaxonomyParentTerm (1:N)
              └── ContentTaxonomy (N:M, via Post)

Form ─────────┬── FormField (1:N)
              └── FormSubmission (1:N)

Rule ─────────┬── EvaluateLog (1:N)
              └── TriggerLog (1:N)

Newsletter ─── NewsletterSubscriber (1:N)

Workflow ───── ActionExecutor ── EmailDispatcher
                               ── WhatsAppDispatcher
                               ── HttpRequestDispatcher
                               ── CodeExecutor
                               ── OptionUpdater
```

### Repository Pattern

Repositories encapsulate all data access. No raw `$wpdb` calls outside repositories.

```php
namespace Ah\Cms\Feature\Pages\Repository;

class PagesRepository extends AbstractRepository
{
    protected function table(): string { return 'ah_pages'; }
    protected function primaryKey(): string { return 'id'; }

    public function findByType(string $type): array
    {
        return $this->findBy(['type' => $type]);
    }

    public function findActive(): array
    {
        return $this->findBy(['status' => 'published']);
    }

    public function findBySlug(string $slug): ?Page
    {
        $row = $this->findOneBy(['slug' => $slug]);
        return $row ? $this->mapToEntity($row) : null;
    }

    private function mapToEntity(array $row): Page
    {
        $page = new Page();
        $page->id = (int) $row['id'];
        $page->title = $row['title'];
        $page->slug = $row['slug'];
        // ... map all fields
        return $page;
    }
}
```

### AbstractRepository Base

```php
namespace Ah\Cms\Repository;

abstract class AbstractRepository
{
    public function __construct(protected Connection $db) {}

    abstract protected function table(): string;
    abstract protected function primaryKey(): string;

    public function find(int $id): ?array;
    public function findAll(array $args = []): array;
    public function findOneBy(array $conditions): ?array;
    public function findBy(array $conditions, array $orderBy = [], int $limit = 0, int $offset = 0): array;
    public function count(array $conditions = []): int;
    public function insert(array $data): int;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function paginate(array $args = []): array; // ['items' => [], 'total' => int, 'pages' => int]
    public function search(string $query, array $columns): array;
}
```

---

## 11. Common Entry Points & Application Lifecycle

Centralized entry points make the lifecycle easy to follow and debug.

### Plugin Lifecycle

```
ah-cms.php (Plugin Entry)
│
├── 1. Constants defined (AH_PLUGIN_DIR, AH_VERSION, etc.)
├── 2. Composer autoloader loaded
├── 3. PluginBootstrap::init()
│   ├── Config\Defaults loaded
│   ├── Connection instantiated
│   ├── Database\Installer::check() — run migrations if needed
│   └── Feature modules registered:
│       ├── WorkflowModule::register()
│       ├── FormBuilderModule::register()
│       ├── NewsletterModule::register()
│       ├── CustomCodeModule::register()
│       ├── RedirectModule::register()
│       ├── CacheModule::register()
│       ├── SettingsModule::register()
│       ├── PagesModule::register()
│       ├── PostsModule::register()
│       ├── TaxonomyModule::register()
│       ├── NavigationModule::register()
│       ├── ReviewsModule::register()
│       ├── FaqsModule::register()
│       ├── ResourcesModule::register()
│       ├── SpotlightsModule::register()
│       ├── BannersModule::register()
│       ├── EventsModule::register()
│       ├── SiteNoticesModule::register()
│       ├── NewsBarModule::register()
│       ├── FeaturedInModule::register()
│       ├── MediaModule::register()
│       ├── FileLinksModule::register()
│       ├── VisitorsModule::register()
│       ├── AnalyticsModule::register()
│       ├── AuditModule::register()
│       ├── ImportModule::register()
│       └── AdminToolsModule::register()
│
├── if (is_admin()):
│   └── AdminBootstrap::init()
│       ├── AdminMenu registered (aggregates from all modules)
│       ├── AdminAssetLoader registered
│       └── AdminAjaxHandlers registered
│
├── if (!is_admin()):
│   └── FrontendBootstrap::init()
│       ├── FrontendAssetLoader registered
│       └── Shortcodes registered
│
├── RestBootstrap::init()         — REST routes registered
├── AjaxBootstrap::init()         — AJAX actions registered
├── CronBootstrap::init()         — Cron schedules registered
├── CliBootstrap::init()          — WP-CLI commands registered
└── HookRegistrar::init()         — All hooks/filters centralized
```

### Theme Lifecycle

```
functions.php (Theme Entry)
│
├── 1. Constants defined
├── 2. Composer autoloader loaded
├── 3. ThemeBootstrap::init()
│   ├── Theme support registered (menus, thumbnails, title-tag)
│   ├── Feature modules registered:
│   │   ├── HomeFeature::register()
│   │   ├── CategoryGuideFeature::register()
│   │   ├── ArticleFeature::register()
│   │   ├── ToolsFeature::register()
│   │   ├── NewsFeature::register()
│   │   ├── ContactFeature::register()
│   │   ├── AskExpertFeature::register()
│   │   ├── GuidanceFeature::register()
│   │   ├── GuidesListingFeature::register()
│   │   ├── FaqsFeature::register()
│   │   └── ComingSoonFeature::register()
│   ├── DataReader registered
│   ├── TemplateRouter registered
│   └── Services initialized
│
├── if (is_admin()):
│   └── ThemeAdminController::init()
│       ├── SettingsEngine initialized
│       └── Tab controllers registered
│
└── Helper functions registered (UrlHelper, IconHelper, etc.)
```

---

## 12. Request Lifecycle

### Frontend Page Request (Plugin)

```
HTTP Request
    │
    ▼
WordPress Core Bootstrap
    │
    ▼
ah-cms.php → Composer autoloader → PluginBootstrap::init()
    │
    ├── Feature modules register their hooks
    │
    ▼
Hook: after_setup_theme
    ├── Theme support, menus
    ├── FrontendAssetLoader::register()
    │
    ▼
Hook: init
    ├── Shortcodes registered
    ├── Cron schedules registered
    │
    ▼
Hook: template_redirect
    ├── RedirectModule → RedirectService::matchCurrentUrl()
    │   └── If match: wp_redirect(301/302)
    ├── PagesModule → BuilderPageRenderer::tryRender()
    │   └── If builder page: render via BlockRendererRegistry
    ├── PagesModule → StaticPageRenderer::tryRender()
    │   └── If static page: render via template
    │
    ▼
Hook: wp_enqueue_scripts
    ├── FrontendAssetLoader::register()
    │   ├── CSS: variables, animations, main
    │   └── JS: main (localized with ajaxUrl, nonce)
    │
    ▼
Hook: the_content (shortcodes)
    ├── [ah_form] → FormBuilderModule → FormShortcode::render()
    ├── [ah_related_links] → ResourcesModule → RelatedLinksShortcode::render()
    ├── [ah_static_page] → PagesModule → StaticPageShortcode::render()
    │
    ▼
Hook: wp_head
    ├── CustomCodeModule → CustomCodeService::injectGlobalCss()
    ├── CustomCodeModule → CustomCodeService::injectSlugCss()
    │
    ▼
Hook: wp_footer
    ├── CustomCodeModule → CustomCodeService::injectGlobalJs()
    ├── CustomCodeModule → CustomCodeService::injectSlugJs()
    │
    ▼
HTML Response
```

### Frontend Page Request (Theme)

```
HTTP Request
    │
    ▼
WordPress Core Bootstrap
    │
    ▼
functions.php → Composer autoloader → ThemeBootstrap::init()
    │
    ├── Feature modules register hooks
    ├── DataReader, TemplateRouter, Services initialized
    │
    ▼
Hook: after_setup_theme
    ├── Theme support, menus
    │
    ▼
Hook: template_redirect
    ├── TemplateRouter::route()
    │   ├── Page definitions (virtual routes)
    │   ├── Parent term templates (CMS taxonomy URLs)
    │   ├── News single slug routes
    │   ├── Expert profile routes
    │   └── Calculator page routes
    │
    ▼
Hook: template_include
    ├── Returns resolved template path
    │
    ▼
Page Template Execution (Feature Controller)
    │
    ├── FeatureController::prepare() → data preparation
    │   ├── Service layer provides data
    │   └── DataReader loads JSON/CSV/HTML
    │
    ├── View: page-{feature}.php (template)
    │   ├── adn_page_open($ctx) — breadcrumbs, header
    │   ├── adn_component('sections/hero', $ctx) — section rendering
    │   ├── adn_component('cards/card', $ctx) — card rendering
    │   └── adn_page_close($ctx) — footer, newsletter CTA
    │
    ▼
Hook: wp_enqueue_scripts
    ├── Shared CSS/JS (variables, common, main)
    ├── Feature-specific CSS/JS (conditional per page)
    │
    ▼
Hook: wp_head
    ├── SeoService::outputMeta() — meta tags, OG, JSON-LD
    │
    ▼
Hook: wp_footer
    ├── SiteNoticesModule → popup rendering
    ├── Floating contact widget
    │
    ▼
HTML Response
```

---

## 13. Admin Execution Flow

### Plugin Admin

```
GET /wp-admin/admin.php?page=ah-{slug}
    │
    ▼
WordPress Admin Bootstrap
    │
    ▼
ah-cms.php → AdminBootstrap::init()
    │
    ▼
Hook: admin_menu
    ├── AdminMenu::register() — aggregates menus from all feature modules
    │
    ▼
Hook: admin_enqueue_scripts
    ├── AdminAssetLoader::enqueue($hook)
    │   ├── Shared: admin-style.css, admin-script.js
    │   └── Per-feature: each module enqueues its own assets
    │
    ▼
Admin Page Router (Feature Module)
    │
    ├── FeatureModule → Controller::handle($request)
    │   ├── Capability check (current_user_can)
    │   ├── POST handling (nonce → sanitize → validate → save)
    │   └── GET handling (prepare data → render view)
    │
    ▼
AJAX Requests
    ├── AjaxDispatcher routes to feature controllers
    │   ├── Validator::verify() — capability + nonce
    │   ├── Controller::handle() — validate → execute → JSON response
    │   └── wp_send_json_success/error()
    │
    ▼
admin_post Handlers
    ├── FeatureModule → Controller::handleAdminPost()
    │   ├── check_admin_referer()
    │   ├── Capability check
    │   └── Process → wp_safe_redirect()
```

### Theme Admin

```
GET /wp-admin/admin.php?page=adn-{tab}
    │
    ▼
ThemeAdminController::init()
    │
    ▼
Hook: admin_menu
    ├── Tab controllers register submenu pages
    │
    ▼
Tab Router
    ├── AbstractTab::load($tab, $subtab)
    │   ├── Capability check
    │   ├── POST handling (save settings)
    │   └── Render tab view template
    │
    ▼
SettingsEngine
    ├── render($schema, $values) — auto-render settings form
    ├── save($schema, $data) — auto-validate and persist
```

---

## 14. Frontend Execution Flow

```
Visitor lands on page
    │
    ▼
WordPress Template Hierarchy
    │
    ▼
template_redirect hooks
    │
    ├── Plugin: Redirect rules (RedirectService)
    ├── Plugin: Builder page routing (PagesModule)
    │
    ├── Theme: TemplateRouter::route()
    │   ├── Page definitions
    │   ├── Parent term templates
    │   ├── News slug routes
    │   ├── Expert profile routes
    │   └── Calculator page routes
    │
    ▼
Feature Controller::prepare()
    │
    ├── Data from plugin services (via service contract)
    ├── Data from theme services (JsonDataService, CmsDataService)
    ├── Data from DataReader (JSON, CSV, HTML files)
    │
    ▼
Template Rendering
    │
    ├── get_header() → header.php
    │   └── Navigation from NavigationService
    │
    ├── Page Template (pages/page-*.php)
    │   ├── adn_page_open($ctx)
    │   ├── adn_component('sections/section_name', $ctx)  × N
    │   ├── adn_component('cards/card_name', $ctx)        × N
    │   └── adn_page_close($ctx)
    │
    └── get_footer() → footer.php
        └── wp_footer()
    │
    ▼
Asset Loading
    ├── wp_head → SEO meta, shared CSS, feature CSS
    ├── wp_footer → shared JS, feature JS
```

---

## 15. Service Layer Design

### Plugin Services (Generic CMS)

| Service | Module | Responsibility | Replaces |
|---------|--------|---------------|----------|
| `SettingsService` | Settings | Site settings CRUD | Direct `$wpdb` in admin pages |
| `CustomCodeService` | CustomCode | Per-slug CSS/JS management | Inline code in `ah-cms.php` |
| `RedirectService` | Redirect | URL redirect rules + matching | Inline code in `ah-cms.php` |
| `CacheManager` | Cache | Cache registry, invalidation | `AH_Cache` (refactored) |
| `FormBuilder` | FormBuilder | Form CRUD, rendering, submission | `AH_Form_Builder` |
| `FormValidator` | FormBuilder | Field-level validation | Part of `AH_Form_Builder` |
| `NewsletterService` | Newsletter | Subscriber management | `AH_Newsletter` |
| `EmailDispatcher` | Newsletter | Email sending abstraction | Part of `AH_Newsletter` |
| `RuleEngine` | Workflow | Rule CRUD + evaluation | Part of `AH_Workflow_Manager` |
| `ConditionEvaluator` | Workflow | Condition matching | Part of `AH_Workflow_Manager` |
| `ActionExecutor` | Workflow | Action dispatch | Part of `AH_Workflow_Manager` |
| `PageService` | Pages | Page CRUD + builder logic | Scattered across `ah-cms.php` |
| `PostService` | Posts | Post management | Scattered admin pages |
| `TaxonomyService` | Taxonomy | Taxonomy CRUD | Scattered admin pages |
| `NavigationService` | Navigation | Menu management | Navigation admin page |
| `AuditService` | Audit | Structured audit logging | New |

### Theme Services

| Service | Responsibility | Replaces |
|---------|---------------|----------|
| `SiteChromeService` | Header/footer/nav data | `services.php` procedural functions |
| `NavigationService` | Menu data for templates | Part of `services.php` |
| `CmsDataService` | Plugin DB data (via service contract) | `services_cms.php` raw SQL |
| `CalculatorService` | Calculator registry + DB | `calculators.php` inline code |
| `ExpertService` | Expert/team member data | `AH_Expert_DB` |
| `EnquiryService` | Contact/guidance enquiries | `AH_Enquiry_Model` + `ADN_Form_Ajax` |
| `SeoService` | Meta tags, OG, Twitter Card, JSON-LD | `includes/seo.php` procedural |

### Plugin ↔ Theme Communication

The theme communicates with the plugin **only through service interfaces**:

```php
// Plugin exposes this interface
namespace Ah\Cms\Service;

interface CmsDataProviderInterface
{
    public function getPages(string $type = ''): array;
    public function getPosts(array $args = []): array;
    public function getTaxonomy(string $type = ''): array;
    public function getNavigation(): array;
    public function getSettings(string $group = ''): array;
    public function getReviews(array $args = []): array;
    public function getFaqs(string $slug = ''): array;
    public function getResources(array $args = []): array;
}

// Theme depends only on the interface, not plugin classes
class CmsDataService implements CmsDataProviderInterface
{
    public function __construct(
        private Ah\Cms\Feature\Pages\Repository\PagesRepository $pages,
        private Ah\Cms\Feature\Posts\Repository\PostsRepository $posts,
        // ... injected via service provider
    ) {}
}
```

---

## 16. Repository Layer Design

### AbstractRepository (Base)

```php
abstract class AbstractRepository
{
    protected Connection $db;

    abstract protected function table(): string;
    abstract protected function primaryKey(): string;

    public function find(int $id): ?array;
    public function findAll(array $args = []): array;
    public function findOneBy(array $conditions): ?array;
    public function findBy(array $conditions, array $orderBy = [], int $limit = 0, int $offset = 0): array;
    public function count(array $conditions = []): int;
    public function insert(array $data): int;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function truncate(): void;
    public function paginate(array $args = []): array; // ['items' => [], 'total' => int, 'pages' => int]
    public function search(string $query, array $columns): array;
}
```

### Concrete Repository Example

```php
class PagesRepository extends AbstractRepository
{
    protected function table(): string { return 'ah_pages'; }
    protected function primaryKey(): string { return 'id'; }

    public function findByType(string $type): array;
    public function findActive(): array;
    public function findBySlug(string $slug): ?Page;
}
```

### Database Table Summary (68+ tables)

```
Core Content:
  ah_pages, ah_page_sections, ah_posts, ah_post_taxonomies,
  ah_content_taxonomies, ah_related_links, ah_static_pages

Taxonomy:
  ah_taxonomy_types, ah_taxonomies, ah_taxonomy_parent_terms

Site Configuration:
  ah_site_settings, ah_navigation, ah_footer_config,
  ah_footer_contact_links, ah_footer_social_links

Page Sections:
  ah_section_hero, ah_section_highlights, ah_section_why_us,
  ah_section_why_us_cards, ah_section_guide_through, ah_section_guide_through_points,
  ah_section_stack_items, ah_section_difference, ah_section_difference_table,
  ah_section_featured_properties, ah_section_featured_properties_items,
  ah_section_experience, ah_section_experience_cards,
  ah_section_why_required, ah_section_why_required_cards,
  ah_section_reviews_header, ah_section_faq_header

Content Types:
  ah_reviews, ah_faqs, ah_resources, ah_spotlights, ah_spotlight_terms,
  ah_home_banners, ah_features_in, ah_events, ah_site_notices,
  ah_news_bar_items, ah_floating_widgets, ah_custom_code, ah_redirect_rules

Media:
  ah_media, ah_client_story_images, ah_client_gallery, ah_client_video_links,
  ah_review_images

Forms:
  ah_forms, ah_form_fields, ah_form_submissions

User/System:
  ah_admin_roles, ah_admin_users, ah_audit_logs, ah_trigger_logs,
  ah_visitor_logs, ah_newsletters, ah_newsletter_subscribers

Builder:
  ah_builder_pages, ah_builder_blocks (JSON in ah_builder_pages.blocks)

Analytics:
  ah_analytics_reports, ah_analytics_results

Workflow:
  ah_rules, ah_evaluate_log

Calculators:
  ah_calculators, ah_calculator_settings
```

---

## 17. Cache System (Plugin-Level + Theme-Level)

The cache system operates at **two independent levels** — plugin and theme — connected through a shared cache contract. Each level manages its own cache lifecycle, invalidation, and storage, but they coordinate through events so that plugin data changes automatically invalidate relevant theme caches.

### Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                     PLUGIN CACHE LAYER                          │
│                                                                  │
│  ┌──────────────┐     ┌──────────────┐     ┌──────────────┐    │
│  │  CacheManager │────▶│ CacheRegistry│────▶│ CacheStorage  │    │
│  │  (orchestrator)│    │ (key → store) │    │ (backends)    │    │
│  └──────┬───────┘     └──────────────┘     └──────┬───────┘    │
│         │                                          │            │
│         │         ┌──────────────┐                 │            │
│         │         │ CacheWarmer  │                 │            │
│         │         │ (preloading) │                 │            │
│         │         └──────────────┘                 │            │
│         │                                          │            │
│         ▼                                          ▼            │
│  ┌──────────────┐     ┌──────────────┐     ┌──────────────┐    │
│  │CacheInvalidator│   │ EventListener │    │  Backends:    │    │
│  │ (on data write)│──▶│ (notifies     │    │  - Object     │    │
│  └──────────────┘    │  theme cache) │    │  - Transient  │    │
│                      └──────────────┘     │  - File        │    │
│                                           │  - Database     │    │
│                                           └──────────────┘    │
└────────────────────────────┬────────────────────────────────────┘
                             │
                    Cache Invalidation Events
                    (wp_action: ah_cache_invalidated)
                             │
┌────────────────────────────▼────────────────────────────────────┐
│                     THEME CACHE LAYER                           │
│                                                                  │
│  ┌──────────────┐     ┌──────────────┐     ┌──────────────┐    │
│  │  ThemeCache   │────▶│FragmentCache │────▶│ FileSystemCache│   │
│  │  Manager      │     │ (per-section)│    │ (file-based)  │   │
│  └──────┬───────┘     └──────────────┘     └──────┬───────┘    │
│         │                                          │            │
│         │         ┌──────────────┐                 │            │
│         │         │ DataCache    │                 │            │
│         │         │ (JSON/CSV)   │                 │            │
│         │         └──────────────┘                 │            │
│         │                                          │            │
│         ▼                                          ▼            │
│  ┌──────────────┐     ┌──────────────┐     ┌──────────────┐    │
│  │ PageCache    │     │ QueryCache   │     │  Storage:     │    │
│  │ (full page)  │     │ (WP_Query)   │     │  - Transient  │    │
│  └──────────────┘     └──────────────┘     │  - File        │    │
│                                            │  - Object      │    │
│                                            └──────────────┘    │
└─────────────────────────────────────────────────────────────────┘
```

### Plugin-Level Cache

#### CacheManager (Orchestrator)

The central cache orchestrator. All cache operations flow through it.

```php
namespace Ah\Cms\Feature\Cache\Service;

class CacheManager
{
    public function __construct(
        private CacheRegistry $registry,
        private CacheInvalidator $invalidator,
        private CacheWarmer $warmer
    ) {}

    /**
     * Get cached value or compute and cache it.
     * 
     * @param string $key     Cache key (e.g., 'pages:published', 'settings:general')
     * @param callable $compute  Function that returns the value if not cached
     * @param int $ttl       Time-to-live in seconds (default: 1 hour)
     * @param string $group  Cache group for bulk invalidation (e.g., 'pages', 'settings')
     * @return mixed
     */
    public function remember(string $key, callable $compute, int $ttl = 3600, string $group = 'default'): mixed
    {
        $store = $this->registry->getStore($key);
        $cached = $store->get($key);

        if ($cached !== false) {
            return $cached;
        }

        $value = $compute();
        $store->set($key, $value, $ttl);
        $this->registry->trackKey($key, $group);

        return $value;
    }

    /**
     * Invalidate a specific cache key.
     */
    public function invalidate(string $key): void
    {
        $store = $this->registry->getStore($key);
        $store->delete($key);
        $this->registry->untrackKey($key);

        // Notify theme layer
        $this->invalidator->notify('key', $key);
    }

    /**
     * Invalidate all keys in a group.
     * e.g., invalidateGroup('pages') clears all page-related caches.
     */
    public function invalidateGroup(string $group): void
    {
        $keys = $this->registry->getKeysByGroup($group);

        foreach ($keys as $key) {
            $store = $this->registry->getStore($key);
            $store->delete($key);
        }

        $this->registry->clearGroup($group);

        // Notify theme layer
        $this->invalidator->notify('group', $group);
    }

    /**
     * Invalidate all caches (nuclear option).
     */
    public function flush(): void
    {
        $this->registry->flushAll();
        $this->invalidator->notify('flush', '*');
    }

    /**
     * Warm caches for frequently accessed data.
     */
    public function warm(): void
    {
        $this->warmer->warmAll();
    }
}
```

#### CacheRegistry (Key Tracking)

Tracks which keys exist and their group membership for bulk invalidation.

```php
namespace Ah\Cms\Feature\Cache\Service;

class CacheRegistry
{
    /**
     * Map of key → group for tracking.
     * Stored in WP option 'ah_cache_registry'.
     */
    private array $registry = [];

    private array $stores = [];

    public function __construct()
    {
        $this->registry = get_option('ah_cache_registry', []);
    }

    public function registerStore(string $name, CacheStorageInterface $store): void
    {
        $this->stores[$name] = $store;
    }

    public function getStore(string $key): CacheStorageInterface
    {
        // Route to appropriate store based on key prefix
        // e.g., 'db:*' → DatabaseStore, 'file:*' → FileStore, default → TransientStore
        $prefix = explode(':', $key)[0] ?? 'default';

        return $this->stores[$this->resolveStore($prefix)] ?? $this->stores['transient'];
    }

    public function trackKey(string $key, string $group): void
    {
        $this->registry[$key] = $group;
        $this->persist();
    }

    public function getKeysByGroup(string $group): array
    {
        return array_keys(array_filter($this->registry, fn($g) => $g === $group));
    }

    public function clearGroup(string $group): void
    {
        $this->registry = array_filter($this->registry, fn($g) => $g !== $group);
        $this->persist();
    }

    public function flushAll(): void
    {
        foreach ($this->stores as $store) {
            $store->flush();
        }
        $this->registry = [];
        $this->persist();
    }

    private function persist(): void
    {
        update_option('ah_cache_registry', $this->registry, false);
    }
}
```

#### CacheStorageInterface (Pluggable Backends)

```php
namespace Ah\Cms\Feature\Cache\Service;

interface CacheStorageInterface
{
    public function get(string $key): mixed;
    public function set(string $key, mixed $value, int $ttl = 3600): void;
    public function delete(string $key): void;
    public function flush(): void;
    public function has(string $key): bool;
}
```

**Implementations:**

| Backend | Use Case | TTL Support | Persistence |
|---------|----------|-------------|-------------|
| `TransientStore` | Default — small values, auto-cleanup | Yes | Database |
| `ObjectCacheStore` | High-frequency reads (settings, nav) | Yes | Runtime (requires persistent object cache) |
| `FileStore` | Large values (HTML fragments, JSON) | Yes | Filesystem |
| `DatabaseStore` | Audit logs, analytics (queryable cache) | Yes | Database |

#### CacheInvalidator (Cross-Layer Notification)

When plugin data changes, this notifies the theme cache layer to invalidate related caches.

```php
namespace Ah\Cms\Feature\Cache\Service;

class CacheInvalidator
{
    /**
     * Fire a cache invalidation event.
     * Theme listens to 'ah_cache_invalidated' action.
     */
    public function notify(string $type, string $target): void
    {
        /**
         * Fires when plugin cache is invalidated.
         *
         * @param string $type   'key', 'group', or 'flush'
         * @param string $target The key, group name, or '*' for flush
         */
        do_action('ah_cache_invalidated', $type, $target);
    }

    /**
     * Listen for theme cache invalidation requests.
     */
    public static function onInvalidate(string $type, string $target): void
    {
        $themeCache = CacheManager::instance('theme');

        match ($type) {
            'key' => $themeCache->invalidate($target),
            'group' => $themeCache->invalidateGroup($target),
            'flush' => $themeCache->flush(),
        };
    }
}
```

#### CacheWarmer (Preloading)

Preloads frequently accessed data into cache on activation or cron.

```php
namespace Ah\Cms\Feature\Cache\Service;

class CacheWarmer
{
    public function __construct(private CacheManager $cache) {}

    public function warmAll(): void
    {
        // Settings (almost never change)
        $this->cache->remember('settings:all', fn() => $this->loadAllSettings(), 86400, 'settings');

        // Navigation (changes rarely)
        $this->cache->remember('navigation:main', fn() => $this->loadNavigation(), 3600, 'navigation');

        // Published pages (change on edit)
        $this->cache->remember('pages:published', fn() => $this->loadPublishedPages(), 1800, 'pages');

        // Taxonomy tree (changes on term edit)
        $this->cache->remember('taxonomy:tree', fn() => $this->loadTaxonomyTree(), 3600, 'taxonomy');
    }
}
```

#### Cache Configuration

```php
// Plugin Config\Defaults.php
return [
    'cache' => [
        'default_ttl' => 3600,           // 1 hour
        'settings_ttl' => 86400,         // 24 hours
        'navigation_ttl' => 3600,        // 1 hour
        'pages_ttl' => 1800,             // 30 minutes
        'posts_ttl' => 1800,             // 30 minutes
        'taxonomy_ttl' => 3600,          // 1 hour
        'reviews_ttl' => 3600,           // 1 hour
        'faqs_ttl' => 7200,              // 2 hours
        'resources_ttl' => 3600,         // 1 hour
        'default_store' => 'transient',  // transient|object_cache|file|database
        'warm_on_activate' => true,
        'warm_on_cron' => true,          // Warm via WP-Cron every 6 hours
    ],
];
```

#### Cache Group Map

| Group | Keys | TTL | Invalidation Trigger |
|-------|------|-----|---------------------|
| `settings` | `settings:all`, `settings:{group}` | 24h | Settings save |
| `navigation` | `navigation:main`, `navigation:{menu}` | 1h | Menu edit |
| `pages` | `pages:published`, `pages:{slug}`, `pages:{id}` | 30m | Page save/delete |
| `posts` | `posts:all`, `posts:{slug}`, `posts:{id}` | 30m | Post save/delete |
| `taxonomy` | `taxonomy:tree`, `taxonomy:{type}` | 1h | Term add/edit/delete |
| `reviews` | `reviews:featured`, `reviews:all` | 1h | Review save/delete |
| `faqs` | `faqs:all`, `faqs:{slug}` | 2h | FAQ save/delete |
| `resources` | `resources:all`, `resources:{type}` | 1h | Resource save/delete |
| `spotlights` | `spotlights:active` | 1h | Spotlight save/delete |
| `banners` | `banners:active` | 30m | Banner save/delete |
| `forms` | `forms:{id}`, `forms:submit:{id}` | 1h | Form save |
| `newsletter` | `newsletter:count` | 1h | Subscribe/unsubscribe |
| `workflow` | `rules:active`, `rules:{trigger}` | 5m | Rule save/delete |
| `custom_code` | `custom_code:{slug}`, `custom_code:global` | 1h | Code save/delete |
| `analytics` | `analytics:{report}` | 5m | Report generate |

---

### Theme-Level Cache

The theme has its own independent cache layer that handles page fragments, query results, and data files.

#### ThemeCacheManager

```php
namespace Adn\Theme\Cache;

class ThemeCacheManager
{
    public function __construct(
        private FragmentCache $fragments,
        private DataCache $data,
        private PageCache $pages,
        private QueryCache $queries
    ) {
        // Listen for plugin cache invalidation events
        add_action('ah_cache_invalidated', [CacheInvalidator::class, 'onInvalidate']);
    }

    /**
     * Cache a rendered component/section fragment.
     */
    public function rememberFragment(string $key, callable $render, int $ttl = 3600): string
    {
        return $this->fragments->remember($key, $render, $ttl);
    }

    /**
     * Cache data loaded from JSON/CSV files.
     */
    public function rememberData(string $key, callable $loader, int $ttl = 86400): mixed
    {
        return $this->data->remember($key, $loader, $ttl);
    }

    /**
     * Cache a full page output.
     */
    public function rememberPage(string $slug, callable $render, int $ttl = 1800): string
    {
        return $this->pages->remember($slug, $render, $ttl);
    }

    /**
     * Cache WP_Query results.
     */
    public function rememberQuery(string $key, array $args, int $ttl = 1800): \WP_Query
    {
        return $this->queries->remember($key, $args, $ttl);
    }
}
```

#### FragmentCache (Section-Level Caching)

Caches individual rendered sections/components. This is the most granular cache — a page refresh only re-renders sections whose data changed.

```php
namespace Adn\Theme\Cache;

class FragmentCache
{
    private const PREFIX = 'ah_frag_';

    /**
     * Cache a rendered fragment (section, card, part).
     *
     * @param string $key     Unique key (e.g., 'section:hero_home', 'card:tool_card:123')
     * @param callable $render  Function that returns HTML
     * @param int $ttl       Time-to-live in seconds
     */
    public function remember(string $key, callable $render, int $ttl = 3600): string
    {
        $cacheKey = self::PREFIX . $key;
        $cached = get_transient($cacheKey);

        if ($cached !== false) {
            return $cached;
        }

        $html = $render();
        set_transient($cacheKey, $html, $ttl);

        return $html;
    }

    /**
     * Invalidate a specific fragment.
     */
    public function invalidate(string $key): void
    {
        delete_transient(self::PREFIX . $key);
    }

    /**
     * Invalidate all fragments matching a pattern.
     * e.g., invalidatePattern('section:hero_*') clears all hero sections.
     */
    public function invalidatePattern(string $pattern): void
    {
        global $wpdb;

        $like = self::PREFIX . str_replace('*', '%', $pattern);
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            $wpdb->esc_like($like) . '%'
        ));
    }

    /**
     * Invalidate all fragments for a specific page.
     */
    public function invalidatePage(string $pageSlug): void
    {
        $this->invalidatePattern("page:{$pageSlug}:*");
    }

    /**
     * Invalidate all fragments.
     */
    public function flush(): void
    {
        global $wpdb;
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            $wpdb->esc_like(self::PREFIX) . '%'
        ));
    }
}
```

#### DataCache (JSON/CSV File Caching)

Caches parsed data from static files. Invalidates when files change (filemtime check).

```php
namespace Adn\Theme\Cache;

class DataCache
{
    private const PREFIX = 'ah_data_';

    /**
     * Cache data from a file, auto-invalidating when file changes.
     *
     * @param string $key     Cache key (e.g., 'json:home_page', 'csv:faqs')
     * @param callable $loader  Function that loads and parses the file
     * @param int $ttl       Max TTL (actual TTL may be shorter if file changes)
     */
    public function remember(string $key, callable $loader, int $ttl = 86400): mixed
    {
        $cacheKey = self::PREFIX . $key;
        $cached = get_transient($cacheKey);

        if ($cached !== false) {
            // Check if underlying file has changed
            $fileMtime = $cached['_file_mtime'] ?? 0;
            $filePath = $cached['_file_path'] ?? '';

            if ($filePath && file_exists($filePath) && filemtime($filePath) > $fileMtime) {
                // File changed — invalidate and recompute
                delete_transient($cacheKey);
                $cached = false;
            } else {
                return $cached['data'];
            }
        }

        $data = $loader();
        set_transient($cacheKey, [
            'data' => $data,
            '_file_mtime' => time(),
            '_file_path' => $data['_file_path'] ?? '',
        ], $ttl);

        return $data;
    }
}
```

#### PageCache (Full Page Caching)

Caches complete rendered pages for anonymous users.

```php
namespace Adn\Theme\Cache;

class PageCache
{
    private const PREFIX = 'ah_page_';

    /**
     * Cache a full page output.
     * Only caches for non-logged-in users.
     */
    public function remember(string $slug, callable $render, int $ttl = 1800): string
    {
        if (is_user_logged_in()) {
            return $render(); // Never cache for logged-in users
        }

        $cacheKey = self::PREFIX . $slug . '_' . $this->getCacheVariant();
        $cached = get_transient($cacheKey);

        if ($cached !== false) {
            return $cached;
        }

        $html = $render();
        set_transient($cacheKey, $html, $ttl);

        return $html;
    }

    /**
     * Cache variant based on user role/language for multi-language support.
     */
    private function getCacheVariant(): string
    {
        $lang = $_COOKIE['ah_lang'] ?? 'en';
        $isMobile = wp_is_mobile() ? 'm' : 'd';
        return "{$lang}_{$isMobile}";
    }

    /**
     * Invalidate page cache for a specific page.
     */
    public function invalidate(string $slug): void
    {
        global $wpdb;
        $like = self::PREFIX . $wpdb->esc_like($slug) . '_%';
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            $like
        ));
    }
}
```

#### QueryCache (WP_Query Caching)

Caches expensive WP_Query results.

```php
namespace Adn\Theme\Cache;

class QueryCache
{
    private const PREFIX = 'ah_query_';

    /**
     * Cache WP_Query results.
     */
    public function remember(string $key, array $args, int $ttl = 1800): \WP_Query
    {
        $cacheKey = self::PREFIX . md5($key . serialize($args));
        $cached = get_transient($cacheKey);

        if ($cached !== false) {
            // Reconstitute WP_Query from cached data
            $query = new \WP_Query();
            $query->posts = $cached['posts'];
            $query->found_posts = $cached['found_posts'];
            $query->max_num_pages = $cached['max_num_pages'];
            return $query;
        }

        $query = new \WP_Query($args);
        set_transient($cacheKey, [
            'posts' => $query->posts,
            'found_posts' => $query->found_posts,
            'max_num_pages' => $query->max_num_pages,
        ], $ttl);

        return $query;
    }
}
```

#### Theme Cache Invalidation on Plugin Changes

```php
namespace Adn\Theme\Cache;

class CacheInvalidator
{
    /**
     * Called when plugin fires 'ah_cache_invalidated' action.
     * Maps plugin cache groups to theme cache invalidation.
     */
    public static function onInvalidate(string $type, string $target): void
    {
        $themeCache = ThemeCacheManager::instance();

        match ($type) {
            'flush' => $themeCache->flush(),
            'group' => self::invalidateGroup($themeCache, $target),
            'key' => self::invalidateKey($themeCache, $target),
        };
    }

    private static function invalidateGroup(ThemeCacheManager $cache, string $group): void
    {
        match ($group) {
            'pages' => $cache->fragments()->invalidatePattern('page:*'),
            'navigation' => $cache->fragments()->invalidatePattern('section:nav*'),
            'settings' => $cache->fragments()->invalidatePattern('section:header*'),
            'reviews' => $cache->fragments()->invalidatePattern('section:review*'),
            'faqs' => $cache->fragments()->invalidatePattern('section:faq*'),
            'banners' => $cache->fragments()->invalidatePattern('section:banner*'),
            'spotlights' => $cache->fragments()->invalidatePattern('section:spotlight*'),
            default => $cache->fragments()->flush(),
        };
    }

    private static function invalidateKey(ThemeCacheManager $cache, string $key): void
    {
        // Parse key to determine what to invalidate
        // e.g., 'pages:123' → invalidate page cache for post 123
        $parts = explode(':', $key);

        match ($parts[0]) {
            'pages' => $cache->pages()->invalidate($parts[1] ?? ''),
            'navigation' => $cache->fragments()->invalidatePattern('section:nav*'),
            'settings' => $cache->fragments()->invalidatePattern('section:header*'),
            default => $cache->fragments()->invalidate($key),
        };
    }

    /**
     * Flush all theme caches.
     */
    public function flush(): void
    {
        $this->fragments()->flush();
        $this->data()->flush();
        $this->pages()->flush();
        $this->queries()->flush();
    }
}
```

---

### Cache Event Flow

```
Admin saves a Page in plugin admin
    │
    ▼
PagesModule → PagesRepository::update()
    │
    ▼
CacheInvalidator::notify('key', 'pages:' . $id)
    │
    ├──▶ Plugin CacheManager::invalidate('pages:' . $id)
    │       └── Clears transient/object cache for that page key
    │
    └──▶ do_action('ah_cache_invalidated', 'key', 'pages:' . $id)
            │
            ▼
        Theme CacheInvalidator::onInvalidate('key', 'pages:' . $id)
            │
            ├──▶ ThemeCacheManager::pages()->invalidate($slug)
            │       └── Clears full page cache for that slug
            │
            └──▶ ThemeCacheManager::fragments()->invalidatePattern('page:' . $slug . ':*')
                    └── Clears all section fragments for that page
```

---

### Cache Cron Jobs

| Job | Schedule | Action |
|-----|----------|--------|
| `ah_cache_warm` | Every 6 hours | `CacheWarmer::warmAll()` — preloads frequently accessed data |
| `ah_cache_cleanup` | Daily at 3 AM | `CacheCleanup::removeExpired()` — garbage collection for file-based caches |
| `ah_cache_stats` | Weekly | `CacheStats::collect()` — log cache hit/miss ratios for monitoring |

---

## 18. Intermediate Data Layer (Bridge Module)

The Intermediate Data Layer is the **single bridge** between the plugin's data and the theme's presentation. It lives in a dedicated `src/Bridge/` directory (in the theme) and is responsible for:

1. **Fetching** data from plugin services (via service contracts)
2. **Fetching** data from theme data files (JSON, CSV, HTML)
3. **Aggregating** data from multiple sources into a single context array
4. **Transforming** raw data into presentation-ready format
5. **Reflecting** changes — when plugin data changes, the bridge re-fetches and updates
6. **Updating** — can push changes back to the plugin via service contracts

### Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                        PLUGIN LAYER                              │
│                                                                  │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐       │
│  │  Pages    │  │  Posts   │  │ Reviews  │  │  FAQs    │  ...  │
│  │  Module   │  │  Module  │  │  Module  │  │  Module  │       │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘       │
│       │              │              │              │              │
│       ▼              ▼              ▼              ▼              │
│  ┌──────────────────────────────────────────────────────┐      │
│  │           CmsDataProviderInterface                     │      │
│  │   (Service contract — theme depends on this, not     │      │
│  │    on plugin classes directly)                        │      │
│  └──────────────────────┬───────────────────────────────┘      │
│                          │                                       │
└──────────────────────────┼───────────────────────────────────────┘
                           │
                    Service Calls (read/write)
                           │
┌──────────────────────────┼───────────────────────────────────────┐
│                          │          THEME LAYER                   │
│                          ▼                                       │
│  ┌──────────────────────────────────────────────────────┐      │
│  │              BRIDGE MODULE (src/Bridge/)               │      │
│  │                                                        │      │
│  │  ┌──────────────┐     ┌──────────────┐               │      │
│  │  │DataAggregator │────▶│DataTransformer│               │      │
│  │  │ (collects)    │     │ (formats)     │               │      │
│  │  └──────┬───────┘     └──────┬───────┘               │      │
│  │         │                     │                        │      │
│  │         ▼                     ▼                        │      │
│  │  ┌──────────────┐     ┌──────────────┐               │      │
│  │  │DataSynchronizer│   │ DataCache    │               │      │
│  │  │ (syncs data)  │    │ (caches)     │               │      │
│  │  └──────┬───────┘     └──────────────┘               │      │
│  │         │                                             │      │
│  │         ▼                                             │      │
│  │  ┌──────────────┐     ┌──────────────┐               │      │
│  │  │DataValidator │     │EventDispatcher│               │      │
│  │  │ (validates)  │     │ (notifies)    │               │      │
│  │  └──────────────┘     └──────────────┘               │      │
│  │                                                        │      │
│  └──────────────────────┬───────────────────────────────┘      │
│                          │                                       │
│                    Context Array                                 │
│                    ($pageData)                                   │
│                          │                                       │
│                          ▼                                       │
│  ┌──────────────────────────────────────────────────────┐      │
│  │  Feature Controllers → Views → Components             │      │
│  └──────────────────────────────────────────────────────┘      │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Bridge Module Directory

```
src/Bridge/
├── BridgeModule.php              # Module entry: registers hooks, initializes bridge
├── DataAggregator.php            # Collects data from all sources
├── DataTransformer.php           # Formats data for presentation
├── DataSynchronizer.php          # Syncs data between plugin and theme
├── DataValidator.php             # Validates data before rendering
├── EventDispatcher.php           # Dispatches data change events
├── DataSource/                   # Data source adapters
│   ├── DataSourceInterface.php   # Contract for all data sources
│   ├── PluginDataSource.php      # Reads from plugin via service interfaces
│   ├── JsonDataSource.php        # Reads from JSON files
│   ├── CsvDataSource.php         # Reads from CSV files
│   ├── HtmlDataSource.php        # Reads from HTML files
│   ├── DatabaseDataSource.php    # Direct DB reads (for theme-specific tables)
│   └── WpPostDataSource.php      # Reads from WP posts (blog posts, pages)
├── Context/                      # Page context builders
│   ├── ContextInterface.php      # Contract
│   ├── HomeContext.php           # Home page context
│   ├── CategoryContext.php       # Category/guide page context
│   ├── ArticleContext.php        # Article/post page context
│   ├── ToolsContext.php          # Tools/calculators context
│   ├── NewsContext.php           # News page context
│   ├── ContactContext.php        # Contact page context
│   ├── ExpertContext.php         # Expert page context
│   ├── GuidanceContext.php       # Guidance page context
│   └── FaqsContext.php           # FAQs page context
├── Hydrator/                     # Entity hydration
│   ├── HydratorInterface.php     # Contract
│   ├── PageHydrator.php          # Hydrates Page entities
│   ├── PostHydrator.php          # Hydrates Post entities
│   ├── ReviewHydrator.php        # Hydrates Review entities
│   └── FaqHydrator.php           # Hydrates FAQ entities
└── Config/
    └── sources.php               # Data source configuration
```

### Bridge Module Namespace

```
Adn\Theme\Bridge\
├── BridgeModule
├── DataAggregator
├── DataTransformer
├── DataSynchronizer
├── DataValidator
├── EventDispatcher
├── DataSource\
│   ├── DataSourceInterface
│   ├── PluginDataSource
│   ├── JsonDataSource
│   ├── CsvDataSource
│   ├── HtmlDataSource
│   ├── DatabaseDataSource
│   └── WpPostDataSource
├── Context\
│   ├── ContextInterface
│   ├── HomeContext
│   ├── CategoryContext
│   ├── ArticleContext
│   ├── ToolsContext
│   ├── NewsContext
│   ├── ContactContext
│   ├── ExpertContext
│   ├── GuidanceContext
│   └── FaqsContext
├── Hydrator\
│   ├── HydratorInterface
│   ├── PageHydrator
│   ├── PostHydrator
│   ├── ReviewHydrator
│   └── FaqHydrator
└── Config\
    └── sources
```

### DataAggregator (Core Bridge)

Collects data from all sources and assembles a unified context array for each page.

```php
namespace Adn\Theme\Bridge;

class DataAggregator
{
    public function __construct(
        private PluginDataSource $plugin,
        private JsonDataSource $json,
        private CsvDataSource $csv,
        private HtmlDataSource $html,
        private WpPostDataSource $wpPosts,
        private DataCache $cache
    ) {}

    /**
     * Aggregate all data needed for a page.
     *
     * @param string $pageSlug  The page slug (e.g., 'home', 'tools', 'contact')
     * @param array $routeParams  Additional route parameters
     * @return array  Unified context array ready for rendering
     */
    public function aggregate(string $pageSlug, array $routeParams = []): array
    {
        $cacheKey = "bridge:{$pageSlug}:" . md5(serialize($routeParams));

        return $this->cache->remember($cacheKey, function () use ($pageSlug, $routeParams) {
            $context = [];

            // 1. Plugin data (CMS content)
            $context['pages'] = $this->plugin->getPages();
            $context['navigation'] = $this->plugin->getNavigation();
            $context['settings'] = $this->plugin->getSettings();
            $context['siteNotices'] = $this->plugin->getSiteNotices();
            $context['banners'] = $this->plugin->getBanners();
            $context['spotlights'] = $this->plugin->getSpotlights();
            $context['newsBar'] = $this->plugin->getNewsBar();
            $context['featuredIn'] = $this->plugin->getFeaturedIn();
            $context['reviews'] = $this->plugin->getReviews();
            $context['faqs'] = $this->plugin->getFaqs();
            $context['resources'] = $this->plugin->getResources();

            // 2. Theme-specific data (from JSON/CSV/HTML)
            $context['siteChrome'] = $this->json->load('site_chrome');
            $context['homePage'] = $this->json->load('home_page');
            $context['sidebarCards'] = $this->json->load('sidebar_cards');
            $context['postSidebar'] = $this->json->load('post_sidebar');
            $context['terms'] = $this->json->load('terms');

            // 3. WordPress post data
            $context['wpPosts'] = $this->wpPosts->getPosts(['post_type' => 'post', 'posts_per_page' => 10]);

            // 4. Page-specific data (overridden by Context classes)
            $context['page'] = $routeParams;

            return $context;
        }, 1800); // 30 min cache
    }

    /**
     * Get page-specific context.
     * Each page type has its own Context class that adds/modifies the base context.
     */
    public function getContext(string $pageSlug, array $routeParams = []): ContextInterface
    {
        $baseContext = $this->aggregate($pageSlug, $routeParams);

        return match ($pageSlug) {
            'home' => new HomeContext($baseContext, $this->plugin, $this->json),
            'category', 'topic' => new CategoryContext($baseContext, $this->plugin, $this->json, $routeParams),
            'article' => new ArticleContext($baseContext, $this->plugin, $routeParams),
            'tools', 'tool-single' => new ToolsContext($baseContext, $this->plugin, $routeParams),
            'news' => new NewsContext($baseContext, $this->plugin),
            'contact' => new ContactContext($baseContext, $this->plugin),
            'ask-expert', 'expert-single' => new ExpertContext($baseContext, $this->plugin, $routeParams),
            'guidance' => new GuidanceContext($baseContext, $this->plugin),
            'faqs' => new FaqsContext($baseContext, $this->plugin),
            default => new DefaultContext($baseContext),
        };
    }
}
```

### PluginDataSource (Plugin Bridge)

Reads data from the plugin through service interfaces. This is the ONLY way the theme accesses plugin data.

```php
namespace Adn\Theme\Bridge\DataSource;

class PluginDataSource implements DataSourceInterface
{
    public function __construct(
        private CmsDataProviderInterface $cmsProvider
    ) {}

    public function getPages(string $type = ''): array
    {
        return $this->cmsProvider->getPages($type);
    }

    public function getPosts(array $args = []): array
    {
        return $this->cmsProvider->getPosts($args);
    }

    public function getNavigation(): array
    {
        return $this->cmsProvider->getNavigation();
    }

    public function getSettings(string $group = ''): array
    {
        return $this->cmsProvider->getSettings($group);
    }

    public function getReviews(array $args = []): array
    {
        return $this->cmsProvider->getReviews($args);
    }

    public function getFaqs(string $slug = ''): array
    {
        return $this->cmsProvider->getFaqs($slug);
    }

    public function getResources(array $args = []): array
    {
        return $this->cmsProvider->getResources($args);
    }

    public function getSiteNotices(): array
    {
        return $this->cmsProvider->getSiteNotices();
    }

    public function getBanners(): array
    {
        return $this->cmsProvider->getBanners();
    }

    public function getSpotlights(): array
    {
        return $this->cmsProvider->getSpotlights();
    }

    public function getNewsBar(): array
    {
        return $this->cmsProvider->getNewsBar();
    }

    public function getFeaturedIn(): array
    {
        return $this->cmsProvider->getFeaturedIn();
    }

    public function getTaxonomy(string $type = ''): array
    {
        return $this->cmsProvider->getTaxonomy($type);
    }
}
```

### Context Classes (Page-Specific Data Assembly)

Each page type has a Context class that extends the base data with page-specific queries.

```php
namespace Adn\Theme\Bridge\Context;

class ToolsContext implements ContextInterface
{
    public function __construct(
        private array $baseContext,
        private PluginDataSource $plugin,
        private array $routeParams
    ) {}

    public function toArray(): array
    {
        $ctx = $this->baseContext;

        // Add tools-specific data
        $ctx['tools'] = $this->plugin->getPages('tool');
        $ctx['calculators'] = $this->plugin->getCalculators();
        $ctx['popularTools'] = $this->plugin->getPopularTools();

        // If single tool page, add tool detail
        if (!empty($this->routeParams['slug'])) {
            $ctx['tool'] = $this->plugin->getPageBySlug($this->routeParams['slug']);
            $ctx['relatedTools'] = $this->plugin->getRelatedTools($ctx['tool']['id'] ?? 0);
        }

        // Add sidebar data
        $ctx['sidebar'] = [
            'categories' => $this->plugin->getTaxonomy('tool_category'),
            'popular' => $this->plugin->getPopularTools(5),
        ];

        return $ctx;
    }
}
```

### DataSynchronizer (Bidirectional Sync)

Handles data synchronization between plugin and theme. When the theme needs to update plugin data (e.g., enquiry submissions, form submissions), it goes through the synchronizer.

```php
namespace Adn\Theme\Bridge;

class DataSynchronizer
{
    public function __construct(
        private CmsDataProviderInterface $cmsProvider,
        private EventDispatcher $events
    ) {}

    /**
     * Push data from theme to plugin.
     * Used for: enquiry submissions, form submissions, visitor tracking.
     */
    public function push(string $entity, array $data): bool
    {
        $result = match ($entity) {
            'enquiry' => $this->cmsProvider->submitEnquiry($data),
            'form_submission' => $this->cmsProvider->submitForm($data),
            'newsletter_subscribe' => $this->cmsProvider->subscribe($data['email'] ?? ''),
            'visitor_ping' => $this->cmsProvider->trackVisitor($data),
            default => false,
        };

        if ($result) {
            $this->events->dispatch('data.pushed', ['entity' => $entity, 'data' => $data]);
        }

        return $result;
    }

    /**
     * Pull fresh data from plugin (force refresh, bypass cache).
     */
    public function pull(string $entity, array $args = []): array
    {
        return match ($entity) {
            'pages' => $this->cmsProvider->getPages($args['type'] ?? ''),
            'posts' => $this->cmsProvider->getPosts($args),
            'navigation' => $this->cmsProvider->getNavigation(),
            'settings' => $this->cmsProvider->getSettings($args['group'] ?? ''),
            'reviews' => $this->cmsProvider->getReviews($args),
            'faqs' => $this->cmsProvider->getFaqs($args['slug'] ?? ''),
            'resources' => $this->cmsProvider->getResources($args),
            default => [],
        };
    }

    /**
     * Sync all theme caches with current plugin data.
     * Called after bulk plugin operations (import, migration).
     */
    public function syncAll(): void
    {
        $this->events->dispatch('sync.all_started');

        // Force-refresh all cached data
        $entities = ['pages', 'posts', 'navigation', 'settings', 'reviews', 'faqs', 'resources'];
        foreach ($entities as $entity) {
            $this->pull($entity);
        }

        $this->events->dispatch('sync.all_completed');
    }
}
```

### DataTransformer (Presentation Formatting)

Transforms raw data into presentation-ready format. Handles date formatting, URL generation, image processing, excerpt creation, etc.

```php
namespace Adn\Theme\Bridge;

class DataTransformer
{
    /**
     * Transform a page entity for presentation.
     */
    public function transformPage(array $page): array
    {
        return [
            'id' => (int) $page['id'],
            'title' => esc_html($page['title']),
            'slug' => sanitize_title($page['slug']),
            'url' => home_url('/' . $page['slug']),
            'excerpt' => wp_trim_words(wp_strip_all_tags($page['description'] ?? ''), 25),
            'image' => $this->transformImage($page['image'] ?? ''),
            'date' => date('M j, Y', strtotime($page['created_at'])),
            'status' => $page['status'],
            'type' => $page['type'],
            // ... more transformations
        ];
    }

    /**
     * Transform a collection of entities.
     */
    public function transformCollection(array $items, string $type): array
    {
        return array_map(fn($item) => match ($type) {
            'page' => $this->transformPage($item),
            'post' => $this->transformPost($item),
            'review' => $this->transformReview($item),
            'faq' => $this->transformFaq($item),
            'resource' => $this->transformResource($item),
            default => $item,
        }, $items);
    }

    private function transformImage(string $imagePath): array
    {
        if (empty($imagePath)) {
            return ['url' => '', 'alt' => '', 'srcset' => ''];
        }

        $url = wp_upload_dir()['baseurl'] . '/' . $imagePath;
        return [
            'url' => esc_url($url),
            'alt' => esc_attr(pathinfo($imagePath, PATHINFO_FILENAME)),
            'srcset' => $this->generateSrcset($url),
        ];
    }
}
```

### DataValidator (Input Validation)

Validates data before it enters the rendering pipeline.

```php
namespace Adn\Theme\Bridge;

class DataValidator
{
    /**
     * Validate page context before rendering.
     * Ensures required fields exist and are safe.
     */
    public function validateContext(array $context, string $pageType): array|false
    {
        $required = $this->getRequiredFields($pageType);

        foreach ($required as $field => $rules) {
            if (!isset($context[$field])) {
                if ($rules['required'] ?? false) {
                    return false; // Missing required field
                }
                $context[$field] = $rules['default'] ?? null;
            }

            // Sanitize based on type
            $context[$field] = match ($rules['type'] ?? 'text') {
                'html' => wp_kses_post($context[$field]),
                'text' => sanitize_text_field($context[$field]),
                'url' => esc_url_raw($context[$field]),
                'int' => absint($context[$field]),
                'array' => array_map('sanitize_text_field', (array) $context[$field]),
                default => $context[$field],
            };
        }

        return $context;
    }

    private function getRequiredFields(string $pageType): array
    {
        return match ($pageType) {
            'home' => [
                'siteChrome' => ['type' => 'array', 'required' => true],
                'navigation' => ['type' => 'array', 'required' => true],
                'banners' => ['type' => 'array', 'required' => false, 'default' => []],
            ],
            'article' => [
                'post' => ['type' => 'array', 'required' => true],
                'navigation' => ['type' => 'array', 'required' => true],
            ],
            default => [
                'navigation' => ['type' => 'array', 'required' => true],
            ],
        };
    }
}
```

### EventDispatcher (Change Notification)

Dispatches events when data changes, allowing components to react.

```php
namespace Adn\Theme\Bridge;

class EventDispatcher
{
    private array $listeners = [];

    public function listen(string $event, callable $handler): void
    {
        $this->listeners[$event][] = $handler;
    }

    public function dispatch(string $event, array $data = []): void
    {
        // WordPress action (for cross-plugin communication)
        do_action("ah_bridge_{$event}", $data);

        // Internal listeners
        foreach ($this->listeners[$event] ?? [] as $handler) {
            $handler($data);
        }
    }
}
```

### How Feature Controllers Use the Bridge

```php
namespace Adn\Theme\Feature\Tools\Controller;

class ToolsController
{
    public function __construct(
        private DataAggregator $aggregator,
        private DataTransformer $transformer,
        private DataValidator $validator
    ) {}

    /**
     * Prepare data for the tools listing page.
     */
    public function prepare(string $pageSlug, array $routeParams = []): array
    {
        // 1. Get context from bridge
        $context = $this->aggregator->getContext($pageSlug, $routeParams);

        // 2. Validate
        $validated = $this->validator->validateContext($context->toArray(), 'tools');
        if ($validated === false) {
            // Fallback to defaults
            $validated = $this->getDefaultContext();
        }

        // 3. Transform for presentation
        $validated['tools'] = $this->transformer->transformCollection($validated['tools'], 'page');
        $validated['calculators'] = $this->transformer->transformCollection($validated['calculators'], 'calculator');

        return $validated;
    }

    /**
     * Handle form submission (enquiry from tools page).
     */
    public function handleEnquiry(array $postData): bool
    {
        $synchronizer = new DataSynchronizer(/* ... */);
        return $synchronizer->push('enquiry', $postData);
    }
}
```

### Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                     COMPLETE DATA FLOW                           │
│                                                                  │
│  1. Page Request                                                 │
│     │                                                            │
│     ▼                                                            │
│  2. TemplateRouter → resolves to Feature (e.g., Tools)           │
│     │                                                            │
│     ▼                                                            │
│  3. FeatureController::prepare()                                 │
│     │                                                            │
│     ├──▶ DataAggregator::aggregate('tools', $routeParams)        │
│     │       │                                                    │
│     │       ├──▶ PluginDataSource::getPages('tool')              │
│     │       │       └──▶ CmsDataProviderInterface (service)      │
│     │       │               └──▶ PagesRepository (DB)            │
│     │       │                                                    │
│     │       ├──▶ JsonDataSource::load('site_chrome')             │
│     │       │       └──▶ data/advaith/json/site_chrome.json      │
│     │       │                                                    │
│     │       ├──▶ WpPostDataSource::getPosts()                    │
│     │       │       └──▶ WP_Query                                │
│     │       │                                                    │
│     │       └──▶ DataCache::remember()                           │
│     │               └──▶ Transient/File/ObjectCache              │
│     │                                                            │
│     ├──▶ Context::toArray() (page-specific data assembly)        │
│     │                                                            │
│     ├──▶ DataValidator::validateContext()                        │
│     │                                                            │
│     └──▶ DataTransformer::transformCollection()                  │
│             │                                                    │
│             ▼                                                    │
│  4. Context Array ($ctx) passed to view                           │
│     │                                                            │
│     ▼                                                            │
│  5. View Template (page-tools.php)                                │
│     │                                                            │
│     ├──▶ adn_page_open($ctx)                                     │
│     │       └──▶ FragmentCache::remember('page:tools:header')    │
│     │                                                            │
│     ├──▶ adn_component('sections/tools_hero', $ctx)              │
│     │       └──▶ FragmentCache::remember('section:tools_hero')   │
│     │                                                            │
│     ├──▶ adn_component('cards/tool_card', $ctx) × N              │
│     │       └──▶ FragmentCache::remember('card:tool:' . $id)     │
│     │                                                            │
│     └──▶ adn_page_close($ctx)                                    │
│             └──▶ FragmentCache::remember('page:tools:footer')    │
│                                                                  │
│  6. HTML Response                                                 │
│                                                                  │
│  ─── On Data Change (admin saves a tool) ───                     │
│                                                                  │
│  Admin saves tool                                                │
│     │                                                            │
│     ▼                                                            │
│  PagesModule → PagesRepository::update()                         │
│     │                                                            │
│     ▼                                                            │
│  CacheInvalidator::notify('key', 'pages:123')                    │
│     │                                                            │
│     ├──▶ Plugin CacheManager::invalidate('pages:123')            │
│     │                                                            │
│     └──▶ do_action('ah_cache_invalidated', 'key', 'pages:123')   │
│             │                                                    │
│             ▼                                                    │
│          Theme CacheInvalidator::onInvalidate()                  │
│             │                                                    │
│             ├──▶ PageCache::invalidate('tools')                  │
│             └──▶ FragmentCache::invalidatePattern('page:tools:*')│
│                                                                  │
│  Next page request → fresh data, no stale cache                  │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Bridge Module Registration

```php
// BridgeModule.php
namespace Adn\Theme\Bridge;

class BridgeModule
{
    public static function register(): void
    {
        // Initialize bridge on theme init
        add_action('after_setup_theme', [self::class, 'init']);

        // Listen for plugin cache invalidation
        add_action('ah_cache_invalidated', [CacheInvalidator::class, 'onInvalidate']);

        // Register REST API endpoints for bridge data
        add_action('rest_api_init', [self::class, 'registerRestRoutes']);
    }

    public static function init(): void
    {
        // Register data sources
        $aggregator = new DataAggregator(
            new PluginDataSource(/* injected */),
            new JsonDataSource(AHN_DATA_DIR . '/advaith/json'),
            new CsvDataSource(AHN_DATA_DIR . '/advaith/csv'),
            new HtmlDataSource(AHN_DATA_DIR . '/advaith/html'),
            new WpPostDataSource(),
            new DataCache()
        );

        // Make available globally (or use DI container)
        $GLOBALS['ah_bridge'] = $aggregator;
    }

    /**
     * REST endpoints for bridge data (for AJAX/JS consumption).
     */
    public static function registerRestRoutes(): void
    {
        register_rest_route('ah-bridge/v1', '/data/(?P<entity>[a-z_]+)', [
            'methods' => 'GET',
            'callback' => [self::class, 'handleRestRequest'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function handleRestRequest(\WP_REST_Request $request): \WP_REST_Response
    {
        $entity = $request->get_param('entity');
        $bridge = $GLOBALS['ah_bridge'];
        $synchronizer = new DataSynchronizer(/* injected */);

        $data = $synchronizer->pull($entity, $request->get_params());

        return new \WP_REST_Response($data, 200);
    }
}
```

---

### Summary: Cache + Bridge Interaction

| Layer | Responsibility | Cache Type | Invalidation Trigger |
|-------|---------------|------------|---------------------|
| **Plugin Cache** | Caches DB queries, settings, entity data | Transient, Object Cache, File | Admin save, cron, import |
| **Theme Cache** | Caches rendered fragments, page output, queries | Transient, File, Object | Plugin cache invalidation event |
| **Bridge Module** | Fetches from plugin + theme data, assembles context | Bridge-level transient | Plugin data change event |
| **Feature Controller** | Calls bridge, validates, transforms | (uses bridge cache) | — |
| **View/Component** | Renders HTML from context | Fragment cache | Data change event |

```
Plugin Data Change
    │
    ├──▶ Plugin CacheManager::invalidate()
    │       └── Clears plugin-level caches
    │
    └──▶ do_action('ah_cache_invalidated')
            │
            ├──▶ Theme CacheInvalidator
            │       └── Clears theme-level caches (fragments, pages, queries)
            │
            └──▶ Bridge DataCache
                    └── Clears bridge-level data aggregation cache
```

The cache system and bridge module work together to ensure:
1. **Plugin data changes** automatically invalidate both plugin and theme caches
2. **Theme never queries plugin DB directly** — always through the bridge + service interfaces
3. **Bridge caches aggregated data** so repeated page loads are fast
4. **Fragment caching** means only changed sections are re-rendered
5. **Bidirectional sync** allows the theme to push data back to the plugin (enquiries, forms, tracking)

### Module Dependency Rules

```
┌─────────────────────────────────────────────────────────────────┐
│                    PLUGIN MODULE BOUNDARIES                      │
│                                                                  │
│  Controllers ──▶ Services ──▶ Repositories ──▶ Database         │
│      │              │              │                             │
│      ▼              ▼              ▼                             │
│    Views          Helpers        Models                         │
│                                                                  │
│  RULES:                                                          │
│  1. Controllers → Services, Repositories, Helpers (never DB)    │
│  2. Services → Repositories, Helpers, Config (never Http)       │
│  3. Repositories → Database, Models (never Services)            │
│  4. Models → Nothing (pure domain entities)                     │
│  5. Views → Helpers (for output formatting only)                │
│  6. Features → Never cross-reference each other directly         │
│     (communicate through services or events)                     │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
                            │
                   Plugin exposes service
                   interfaces (contracts)
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                     THEME MODULE BOUNDARIES                      │
│                                                                  │
│  Feature Controllers ──▶ Theme Services ──▶ Plugin Services     │
│         │                    │                    │              │
│         ▼                    ▼                    ▼              │
│       Views              DataReader          Repositories       │
│    (components/)      (JSON/CSV/HTML)       (via interface)     │
│                                                                  │
│  RULES:                                                          │
│  1. Theme NEVER directly queries plugin DB tables                │
│  2. Theme depends on plugin service INTERFACES, not classes      │
│  3. Theme controllers prepare data, then delegate to views       │
│  4. Views (components/) contain zero business logic              │
│  5. Feature modules are independent of each other                │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Coupling Boundaries

| Boundary | Current (Bad) | Target (Good) |
|----------|--------------|---------------|
| Plugin ↔ Theme | Theme reads plugin DB via raw SQL | Theme uses plugin's service interfaces |
| Admin pages ↔ Database | 99 `$wpdb` calls in templates | Repositories encapsulate all queries |
| Bootstrap ↔ Everything | `ah-cms.php` does everything | Thin bootstrap → Feature modules |
| Features ↔ Features | WorkflowManager knows about all features | Each feature is independent, communicates via events/services |
| Business logic ↔ Templates | Logic embedded in templates | Controllers prepare data, templates only render |

---

## 19. Reusability Contracts

### Feature Reusability

Each feature module is designed to be copied between projects:

```
Feature/Workflow/
├── WorkflowModule.php          # Self-registering entry point
├── Controller/                 # Handles HTTP requests
├── Service/                    # Business logic (no WP dependencies)
├── Model/                      # Domain entities (no WP dependencies)
├── Repository/                 # Data access (depends only on Connection)
├── View/                       # Admin page templates
├── Config/                     # Feature defaults
└── Assets/                     # Feature-specific CSS/JS
```

**To reuse in another project:**
1. Copy the `Feature/Workflow/` directory
2. Register `WorkflowModule::register()` in the new project's bootstrap
3. Done — no modifications needed if the base classes (AbstractRepository, Connection, etc.) are available

### Component Reusability (Theme)

Components (`cards/`, `sections/`, `parts/`) are already well-designed for reuse:

```php
// Render any component with data context
adn_component('sections/hero_home', $context);
adn_component('cards/tool_card', $context);
adn_component('parts/faq_list', $context);
```

**Rule:** Components receive data via `$context` array. They never fetch data themselves.

### Service Reusability

Services are independent of presentation:

```php
// Can be used from admin, REST API, AJAX, CLI, or frontend
$settings = $settingsService->get('site_name');
$pages = $pagesService->findActive();
$form = $formBuilder->render($formId, $context);
```

### Model Independence

Models have zero WordPress dependencies:

```php
$page = new Page();
$page->title = 'My Page';
$page->isPublished(); // Domain logic, no DB calls
```

---

## 20. AI-Friendly Organization

### Discoverability Rules

An AI agent (or new developer) should be able to answer these questions instantly:

| Question | Answer Location |
|----------|----------------|
| Where does the workflow feature live? | `src/Feature/Workflow/` |
| What handles the form builder AJAX? | `src/Feature/FormBuilder/Controller/FormSubmitController.php` |
| How are pages queried from the DB? | `src/Feature/Pages/Repository/PagesRepository.php` |
| What business rules does the newsletter have? | `src/Feature/Newsletter/Service/NewsletterService.php` |
| Where is the home page template? | Theme: `src/Feature/Home/View/page-home.php` |
| How does the theme get plugin data? | `src/Service/CmsDataService.php` (implements plugin's interface) |
| What CSS does the tools page use? | `src/Feature/Tools/Assets/css/tools.css` + shared `assets/css/` |
| How do I add a new admin page? | Create a new Feature module with Controller, View, and register in bootstrap |

### Naming Conventions

| Element | Convention | Example |
|---------|-----------|---------|
| Feature module | PascalCase, plural | `Workflow`, `FormBuilder`, `Pages` |
| Module entry | `{Feature}Module.php` | `WorkflowModule.php` |
| Controller | `{Action}{Feature}Controller.php` | `WorkflowAdminController.php` |
| Service | `{Feature}Service.php` | `NewsletterService.php` |
| Model | Singular entity name | `Page`, `Rule`, `Form` |
| Repository | Plural entity name | `PagesRepository`, `RulesRepository` |
| View template | kebab-case | `workflow-admin.php`, `page-home.php` |
| CSS asset | kebab-case, feature prefix | `workflow.css`, `tools.css` |
| JS asset | kebab-case, feature prefix | `workflow.js`, `tools.js` |
| Namespace | `{Root}\{Layer}\{Feature}\{Sublayer}` | `Ah\Cms\Feature\Workflow\Service\RuleEngine` |

### File Responsibility Matrix

Every file has ONE responsibility:

| File Type | Responsibility | Contains Business Logic? |
|-----------|---------------|------------------------|
| `*Module.php` | Hook/route registration | No |
| `*Controller.php` | Request handling, response | No (delegates to services) |
| `*Service.php` | Business logic, orchestration | Yes |
| `*Model.php` | Domain entity, domain methods | Yes (entity-level) |
| `*Repository.php` | Data access, queries | No (SQL only) |
| `*View.php` / `*.php` (in View/) | HTML rendering | No |
| `*Helper.php` | Pure utility functions | No |
| `*Config.php` / `defaults.php` | Static configuration | No |

### Request Flow Tracing

To trace a request through the system:

```
1. Entry point: ah-cms.php or functions.php
2. Bootstrap: which bootstrap handles this context?
3. Module: which feature module registered this hook/route?
4. Controller: which controller handles this action?
5. Service: which service contains the business logic?
6. Repository: which repository accesses the data?
7. Model: which entity represents this data?
8. View: which template renders this response?
```

---

## 21. JSON-Driven Configuration & Content

### Core Principle: Industry-Agnostic Architecture

The theme must work for **any industry** — UK Property today, Organic Farming tomorrow, Healthcare next week — **without touching a single line of PHP code**. Every piece of branding, terminology, navigation, page content, icons, URLs, forms, and UI text lives in JSON files. Changing the industry means swapping JSON files only.

### Current State (What's Already Good)

The `terms.json` file already follows this pattern well:

```json
{
    "brand": {
        "name": "ADVAITH HOMES",
        "industry": "UK Property",
        "domain_noun": "Property",
        "expert_noun": "Property Expert"
    },
    "taxonomy": {
        "parent": "Guide",
        "parent_plural": "Guides",
        "section": "Category",
        "content": "Article"
    },
    "urls": {
        "guides": "/guides/",
        "tools": "/calculators/",
        "expert": "/ask-expert/"
    }
}
```

This is exactly the right pattern. The problem is that **not everything follows it** — many titles, labels, and content are still hardcoded in PHP templates.

### Target JSON Configuration Structure

All JSON files live in one place. The theme reads from this directory. To change the industry, swap the entire directory.

```
data/
├── config/                        # ── GLOBAL CONFIG (industry-agnostic) ──
│   ├── site.json                  # Site name, tagline, description, copyright
│   ├── industry.json              # Industry type, domain terms, icons
│   ├── navigation.json            # Main nav, footer nav, mobile nav
│   ├── footer.json                # Footer columns, social links, bottom links
│   ├── seo.json                   # Default meta titles, descriptions, OG settings
│   ├── forms.json                 # Form labels, placeholders, validation messages
│   ├── emails.json                # Email templates, subject lines, headers
│   └── constants.json             # Feature flags, limits, API endpoints
│
├── pages/                         # ── PER-PAGE CONTENT ──
│   ├── home.json                  # Home page: hero, sections, CTAs
│   ├── contact.json               # Contact page: form, sidebar, process steps
│   ├── guides.json                # Guides listing: hero, filters, sort options
│   ├── news.json                  # News listing: layout, filters, pagination
│   ├── tools.json                 # Tools listing: categories, sort, filters
│   ├── faqs.json                  # FAQs page: categories, search config
│   ├── ask-expert.json            # Expert page: form, categories, trust items
│   ├── guidance.json              # Guidance page: form, services, process
│   └── about.json                 # About page: team, values, story
│
├── sections/                      # ── REUSABLE SECTION DATA ──
│   ├── hero-home.json             # Home hero content
│   ├── hero-category.json         # Category hero content
│   ├── hero-article.json          # Article hero content
│   ├── hero-tools.json            # Tools hero content
│   ├── hero-news.json             # News hero content
│   ├── newsletter-cta.json        # Newsletter CTA section
│   ├── trust-bar.json             # Trust indicators
│   ├── process-steps.json         # Generic process steps
│   └── cta-banner.json            # Generic CTA banner
│
├── components/                    # ── REUSABLE COMPONENT DATA ──
│   ├── cards/
│   │   ├── tool-card.json         # Tool card defaults
│   │   ├── news-card.json         # News card defaults
│   │   ├── guide-card.json        # Guide card defaults
│   │   └── expert-card.json       # Expert card defaults
│   ├── sidebar/
│   │   ├── newsletter.json        # Newsletter sidebar widget
│   │   ├── expert-help.json       # Expert help sidebar
│   │   ├── browse-categories.json # Browse by category
│   │   └── related-guides.json    # Related guides widget
│   └── parts/
│       ├── breadcrumb.json        # Breadcrumb config
│       ├── share-bar.json         # Social share options
│       └── floating-contact.json  # Floating contact widget
│
├── terms/                         # ── TERMINOLOGY (rebrandable) ──
│   ├── site-terms.json            # Brand, taxonomy, icons, URLs
│   ├── feature-labels.json        # Feature names and labels
│   ├── button-labels.json         # All button text
│   ├── placeholder-text.json      # Form placeholders
│   ├── section-headings.json      # All section headings
│   └── error-messages.json        # Validation and error messages
│
└── overrides/                     # ── THEME OVERRIDE LAYER ──
    └── (empty by default — theme-specific overrides go here)
```

### JSON File Structure: Global Config

#### `config/site.json` — Site Identity

```json
{
    "_comment": "Change this file to rebrand the entire site. No PHP changes needed.",

    "name": "ADVAITH HOMES",
    "tagline": "Property Made Simple",
    "description": "Independent property information, smart tools and expert insights for buyers, sellers and house movers in the UK.",
    "copyright_year": "2025",
    "copyright_text": "© 2025 ADVAITH HOMES. All rights reserved.",
    "made_with": "Made with ♥ in the UK",
    "disclaimer": "Information on this website is general guidance only and does not constitute legal, financial or professional advice.",
    "logo": {
        "icon": "🏠",
        "name": "ADVAITH",
        "subtitle": "HOMES",
        "url": "/"
    },
    "contact": {
        "email": "contact@advaithhomes.co.uk",
        "whatsapp": "+44 7747 223 762",
        "phone": "",
        "address": ""
    },
    "social": [
        { "platform": "facebook", "url": "#", "label": "Facebook" },
        { "platform": "instagram", "url": "#", "label": "Instagram" },
        { "platform": "youtube", "url": "#", "label": "YouTube" }
    ]
}
```

**Tomorrow, for Organic Farming:**
```json
{
    "name": "GREEN FIELD ORGANICS",
    "tagline": "Organic Farming Made Simple",
    "description": "Independent organic farming information, tools and expert insights for farmers, growers and homesteaders.",
    "logo": { "icon": "🌾", "name": "GREEN FIELD", "subtitle": "ORGANICS" },
    "contact": {
        "email": "hello@greenfieldorganics.com",
        "whatsapp": "+1 555 123 4567"
    }
}
```

#### `config/industry.json` — Industry & Domain Terms

```json
{
    "_comment": "Defines the industry context. All terminology derives from this.",

    "type": "property",
    "label": "UK Property",
    "domain_noun": "Property",
    "domain_plural": "Properties",
    "expert_noun": "Property Expert",
    "expert_plural": "Property Experts",
    "guide_noun": "Guide",
    "guide_plural": "Guides",
    "calculator_noun": "Calculator",
    "calculator_plural": "Calculators",
    "article_noun": "Article",
    "article_plural": "Articles",
    "category_noun": "Category",
    "category_plural": "Categories",
    "topic_noun": "Topic",
    "topic_plural": "Topics",
    "news_noun": "News",
    "news_plural": "News",
    "tool_noun": "Tool",
    "tool_plural": "Tools",
    "review_noun": "Review",
    "review_plural": "Reviews",
    "faq_noun": "FAQ",
    "faq_plural": "FAQs",
    "resource_noun": "Resource",
    "resource_plural": "Resources",
    "icon": "🏠",
    "hero_icon": "🏠",
    "currency": "£",
    "location_default": "UK"
}
```

**Tomorrow, for Organic Farming:**
```json
{
    "type": "organic-farming",
    "label": "Organic Farming",
    "domain_noun": "Farm",
    "domain_plural": "Farms",
    "expert_noun": "Farming Expert",
    "guide_noun": "Growing Guide",
    "calculator_noun": "Yield Calculator",
    "icon": "🌾",
    "currency": "$",
    "location_default": "USA"
}
```

#### `config/navigation.json` — All Navigation Menus

```json
{
    "_comment": "All navigation in one place. Change labels, URLs, order — no PHP.",

    "main": [
        { "label": "Guides", "url": "/guides/", "icon": "📚" },
        { "label": "Calculators", "url": "/calculators/", "icon": "🧮" },
        { "label": "News", "url": "/news/", "icon": "📰" },
        { "label": "Ask an Expert", "url": "/ask-expert/", "icon": "🤝" },
        { "label": "Contact", "url": "/contact/", "icon": "💬" }
    ],
    "mobile": [
        { "label": "Home", "url": "/", "icon": "🏠" },
        { "label": "Guides", "url": "/guides/", "icon": "📚" },
        { "label": "Calculators", "url": "/calculators/", "icon": "🧮" },
        { "label": "News", "url": "/news/", "icon": "📰" },
        { "label": "Expert", "url": "/ask-expert/", "icon": "🤝" },
        { "label": "Contact", "url": "/contact/", "icon": "💬" }
    ],
    "footer": [
        {
            "heading": "Explore",
            "links": [
                { "label": "Buying Guides", "url": "/buying/" },
                { "label": "Selling Guides", "url": "/selling/" },
                { "label": "Moving Guides", "url": "/moving/" },
                { "label": "Calculators", "url": "/calculators/" }
            ]
        },
        {
            "heading": "Company",
            "links": [
                { "label": "About Us", "url": "/about/" },
                { "label": "Contact", "url": "/contact/" },
                { "label": "News", "url": "/news/" },
                { "label": "FAQs", "url": "/faqs/" }
            ]
        },
        {
            "heading": "Legal",
            "links": [
                { "label": "Terms", "url": "/terms/" },
                { "label": "Privacy", "url": "/privacy/" },
                { "label": "Cookie Policy", "url": "/cookie-policy/" }
            ]
        }
    ],
    "cta": {
        "label": "Get Guidance",
        "url": "/ask-expert/"
    },
    "breadcrumbs": {
        "home_label": "Home",
        "separator": "/"
    }
}
```

#### `config/footer.json` — Footer Layout

```json
{
    "columns": [
        {
            "heading": "Explore",
            "links": [
                { "label": "Buying Guides", "url": "/buying/" },
                { "label": "Selling Guides", "url": "/selling/" },
                { "label": "Moving Guides", "url": "/moving/" },
                { "label": "Calculators", "url": "/calculators/" }
            ]
        },
        {
            "heading": "Company",
            "links": [
                { "label": "About Us", "url": "/about/" },
                { "label": "Contact", "url": "/contact/" }
            ]
        }
    ],
    "bottom_links": [
        { "label": "Terms", "url": "/terms/" },
        { "label": "Privacy", "url": "/privacy/" },
        { "label": "Cookie Policy", "url": "/cookie-policy/" }
    ],
    "newsletter": {
        "heading": "Stay Updated",
        "description": "Get the latest guides and expert tips delivered to your inbox.",
        "placeholder": "Your email address",
        "button_label": "Subscribe",
        "note": "No spam. Unsubscribe anytime."
    }
}
```

#### `config/seo.json` — Default SEO Settings

```json
{
    "_comment": "Default SEO metadata. Pages can override via their own JSON.",

    "defaults": {
        "title_suffix": " | ADVAITH HOMES",
        "og_type": "website",
        "og_locale": "en_GB",
        "twitter_card": "summary_large_image",
        "robots": "index, follow"
    },
    "pages": {
        "home": {
            "title": "UK Property Guides, Calculators & Expert Advice | ADVAITH HOMES",
            "description": "Independent property information, smart tools and expert insights for buyers, sellers and house movers in the UK.",
            "og_image": "/assets/images/og-home.jpg"
        },
        "guides": {
            "title": "Property Guides | ADVAITH HOMES",
            "description": "Browse our complete library of property guides covering buying, selling and moving home in the UK."
        },
        "tools": {
            "title": "Property Calculators | ADVAITH HOMES",
            "description": "Free property calculators for stamp duty, mortgage repayments, affordability and more."
        },
        "news": {
            "title": "Property News & Insights | ADVAITH HOMES",
            "description": "Latest property news, market updates and expert insights."
        },
        "contact": {
            "title": "Contact Us | ADVAITH HOMES",
            "description": "Get in touch with our property experts. We're here to help."
        }
    }
}
```

#### `config/forms.json` — All Form Configuration

```json
{
    "_comment": "All form labels, placeholders, validation messages in one place.",

    "common": {
        "name_label": "Your Name",
        "email_label": "Email Address",
        "phone_label": "Phone Number",
        "message_label": "Your Message",
        "submit_label": "Submit",
        "required_suffix": "*",
        "optional_suffix": "(Optional)",
        "loading_text": "Sending...",
        "success_text": "Thank you! We'll get back to you soon.",
        "error_text": "Something went wrong. Please try again."
    },
    "validation": {
        "name_required": "Name is required",
        "email_required": "Email is required",
        "email_invalid": "Please enter a valid email address",
        "phone_invalid": "Please enter a valid phone number",
        "message_required": "Message is required",
        "message_min": "Message must be at least 10 characters"
    },
    "contact_form": {
        "heading": "Send us your enquiry",
        "description": "Tell us about your situation and we'll get back to you with the right guidance.",
        "submit_label": "Submit Enquiry",
        "enquiry_types": [
            { "key": "general", "icon": "💬", "label": "General Question" },
            { "key": "support", "icon": "🛠", "label": "Support Request" },
            { "key": "feedback", "icon": "⭐", "label": "Feedback" }
        ]
    },
    "newsletter": {
        "heading": "Stay Informed, Stay Ahead",
        "description": "Subscribe to get the latest news, guides and expert insights.",
        "placeholder": "Enter your email address",
        "button_label": "Subscribe Now",
        "note": "No spam. Unsubscribe anytime.",
        "consent_text": "I agree to receive updates and understand I can unsubscribe at any time."
    },
    "expert_form": {
        "heading": "Tell us about your requirement",
        "help_label": "I am looking for help with",
        "type_label": "I am a",
        "time_label": "When do you need help?",
        "submit_label": "Submit Request",
        "consent_text": "I agree to the terms and consent to my details being shared with trusted partners."
    },
    "guidance_form": {
        "heading": "Get Expert Guidance",
        "description": "Connect with the right professional for your situation.",
        "submit_label": "Get Matched Now"
    }
}
```

#### `config/emails.json` — Email Templates

```json
{
    "_comment": "Email subject lines and template snippets. No PHP email templates needed.",

    "enquiry_received": {
        "subject": "We received your enquiry - {brand_name}",
        "body_intro": "Hi {name},",
        "body_main": "Thank you for reaching out to {brand_name}. We've received your enquiry and will get back to you within 1-2 working days.",
        "body_closing": "Best regards,\nThe {brand_name} Team"
    },
    "newsletter_welcome": {
        "subject": "Welcome to {brand_name} Newsletter",
        "body_intro": "Hi there,",
        "body_main": "You've been subscribed to the {brand_name} newsletter. You'll receive the latest guides, tools and expert insights.",
        "body_closing": "Best regards,\nThe {brand_name} Team"
    },
    "expert_matched": {
        "subject": "Your Expert Match - {brand_name}",
        "body_intro": "Hi {name},",
        "body_main": "Based on your requirements, we've matched you with a {expert_noun}.",
        "body_closing": "Best regards,\nThe {brand_name} Team"
    }
}
```

#### `config/constants.json` — Feature Flags & Limits

```json
{
    "_comment": "Feature flags and operational limits. Toggle features without code changes.",

    "features": {
        "enable_newsletter": true,
        "enable_whatsapp": true,
        "enable_comments": true,
        "enable_search": true,
        "enable_calculators": true,
        "enable_experts": true,
        "enable_guidance": true,
        "enable_news": true,
        "enable_coming_soon": false,
        "enable_cookie_consent": true,
        "enable_analytics_consent": true,
        "enable_floating_contact": true
    },
    "limits": {
        "news_per_page": 12,
        "guides_per_page": 12,
        "tools_per_page": 12,
        "faqs_per_page": 50,
        "reviews_per_page": 10,
        "search_min_length": 3,
        "newsletter_max_per_page": 3
    },
    "social": {
        "share_platforms": ["facebook", "twitter", "linkedin", "whatsapp", "email"]
    }
}
```

### JSON File Structure: Per-Page Content

#### `pages/home.json` — Home Page Content

```json
{
    "_comment": "Home page content. Change hero text, sections, CTAs — no PHP.",

    "hero": {
        "title_lines": [
            { "text": "Your {domain} Journey,", "accent": false },
            { "text": "Explained.", "accent": true },
            { "text": "Simply. Clearly. Confidently.", "accent": false }
        ],
        "description": "Independent information hub for buyers, sellers and house movers. Clear guides, smart tools and expert insights.",
        "actions": [
            { "label": "Start Your Journey →", "url": "/buying/", "style": "primary" },
            { "label": "Ask an Expert", "url": "/ask-expert/", "style": "outline" }
        ],
        "bg_icon": "🏠"
    },
    "sections": [
        {
            "type": "journey",
            "heading": "Choose Your {domain} Journey",
            "cards": [
                {
                    "icon": "👥",
                    "gradient": "linear-gradient(135deg,#ede8f8,#a890d8)",
                    "title": "I need Professional Help",
                    "description": "Connect with the right professionals at the right time.",
                    "link_label": "Find Expert Help →",
                    "url": "/ask-expert/"
                },
                {
                    "icon": "🧮",
                    "gradient": "linear-gradient(135deg,#e8f4ee,#7bbfa4)",
                    "title": "Use our {calculators}",
                    "description": "All in one place.",
                    "link_label": "Open {calculators} →",
                    "url": "/calculators/"
                },
                {
                    "icon": "📚",
                    "gradient": "linear-gradient(135deg,#fef6e4,#e6c97a)",
                    "title": "Read {guides}",
                    "description": "Step-by-step guides.",
                    "link_label": "Browse {guides} →",
                    "url": "/guides/"
                }
            ]
        },
        {
            "type": "news",
            "heading": "Latest {news}",
            "link_label": "View all →",
            "link_url": "/news/"
        },
        {
            "type": "tools",
            "heading": "Popular {calculators}",
            "link_label": "View all →",
            "link_url": "/calculators/"
        },
        {
            "type": "newsletter",
            "icon": "✉️",
            "heading": "Stay Informed, Stay Ahead",
            "description": "Subscribe to get the latest {news}, {guides} and expert insights.",
            "placeholder": "Enter your email address",
            "button_label": "Subscribe Now",
            "note": "No spam. Unsubscribe anytime."
        }
    ]
}
```

**Key pattern:** `{domain}`, `{calculators}`, `{guides}`, `{news}` are placeholders that get replaced from `industry.json` at render time. This means the same template works for any industry.

#### `pages/contact.json` — Contact Page Content

```json
{
    "meta": {
        "page_title": "Contact Us",
        "meta_description": "Get in touch with our experts."
    },
    "hero": {
        "title": "How can we help you?",
        "description": "Have a question? We're here to help.",
        "bg_icon": "💬",
        "trust_items": [
            { "icon": "🤝", "title": "Expert Guidance", "subtitle": "Advice from our experts" },
            { "icon": "⚖️", "title": "Impartial & Independent", "subtitle": "Unbiased information" },
            { "icon": "✅", "title": "Reliable Information", "subtitle": "Practical and easy to understand" },
            { "icon": "⏱", "title": "Response Within 24-48 Hours", "subtitle": "We aim to respond quickly" }
        ]
    },
    "form": "contact_form",
    "process_steps": [
        { "number": "1", "icon": "📝", "title": "Send your enquiry", "description": "Fill out a short form." },
        { "number": "2", "icon": "🔍", "title": "We review your situation", "description": "Our team reviews your enquiry." },
        { "number": "3", "icon": "💡", "title": "We provide guidance", "description": "We send you helpful guidance." }
    ],
    "sidebar": {
        "whatsapp": {
            "icon": "💬",
            "heading": "Prefer WhatsApp?",
            "note": "Quickest way to reach us.",
            "button_label": "Start WhatsApp Chat"
        },
        "email": {
            "icon": "📧",
            "heading": "Prefer Email?",
            "note": "We'll get back to you within 1-2 working days.",
            "button_label": "Send an Email"
        }
    },
    "resources": {
        "heading": "While you wait, explore popular resources",
        "items": [
            { "icon": "📚", "title": "{guide_plural}", "desc": "Step-by-step guides", "url": "/guides/" },
            { "icon": "🧮", "title": "{calculator_plural}", "desc": "Useful tools", "url": "/calculators/" },
            { "icon": "📰", "title": "{news}", "desc": "Latest updates", "url": "/news/" }
        ]
    }
}
```

### JSON File Structure: Reusable Sections

#### `sections/hero-home.json` — Home Hero

```json
{
    "title_lines": [
        { "text": "Your {domain} Journey,", "accent": false },
        { "text": "Explained.", "accent": true },
        { "text": "Simply. Clearly. Confidently.", "accent": false }
    ],
    "description": "Independent information hub for {domain_plural}.",
    "actions": [
        { "label": "Start Your Journey →", "url": "/buying/", "style": "primary" },
        { "label": "Ask an Expert", "url": "/ask-expert/", "style": "outline" }
    ],
    "diagram": {
        "center_icon": "{icon}",
        "center_lines": ["{domain}", "Made Simple"],
        "nodes": [
            { "icon": "💰", "label": "Budget & Mortgage" },
            { "icon": "🔍", "label": "Find & View" },
            { "icon": "🤝", "label": "Offer & Negotiate" },
            { "icon": "⚖️", "label": "Solicitor & Searches" },
            { "icon": "📋", "label": "Exchange & Completion" },
            { "icon": "📐", "label": "Survey & Checks" }
        ]
    }
}
```

#### `sections/newsletter-cta.json` — Newsletter CTA

```json
{
    "icon": "✉️",
    "heading": "Stay Informed, Stay Ahead",
    "description": "Subscribe to get the latest {news}, {guides} and expert insights.",
    "placeholder": "Enter your email address",
    "button_label": "Subscribe Now",
    "note": "No spam. Unsubscribe anytime.",
    "consent_text": "I agree to receive updates and understand I can unsubscribe at any time."
}
```

#### `sections/trust-bar.json` — Trust Indicators

```json
{
    "items": [
        { "icon": "✓", "label": "Independent & Unbiased" },
        { "icon": "✓", "label": "No hidden fees" },
        { "icon": "✓", "label": "Plain English advice" }
    ]
}
```

### JSON File Structure: Component Data

#### `components/cards/tool-card.json` — Tool Card Defaults

```json
{
    "defaults": {
        "icon": "{icon}",
        "gradient": "linear-gradient(135deg,#e8f4ee,#7bbfa4)",
        "link_label": "Calculate Now →",
        "show_popular_badge": true
    },
    "variants": {
        "featured": {
            "gradient": "linear-gradient(135deg,#ede8f8,#a890d8)",
            "show_popular_badge": true
        },
        "standard": {
            "gradient": "linear-gradient(135deg,#e8f4ee,#7bbfa4)",
            "show_popular_badge": false
        }
    }
}
```

#### `components/sidebar/newsletter.json` — Newsletter Sidebar

```json
{
    "heading": "Stay Updated",
    "description": "Get the latest {guides} and expert tips delivered to your inbox.",
    "placeholder": "Your email address",
    "button_label": "Subscribe",
    "note": "No spam. Unsubscribe anytime."
}
```

### JSON File Structure: Terminology

#### `terms/site-terms.json` — Core Terminology

```json
{
    "brand": {
        "name": "ADVAITH HOMES",
        "icon": "🏠",
        "industry": "UK Property",
        "domain_noun": "Property",
        "domain_plural": "Properties",
        "expert_noun": "Property Expert",
        "expert_plural": "Property Experts"
    },
    "taxonomy": {
        "parent": "Guide",
        "parent_plural": "Guides",
        "section": "Category",
        "section_plural": "Topics",
        "content": "Article",
        "content_plural": "Articles"
    },
    "urls": {
        "home": "/",
        "guides": "/guides/",
        "news": "/news/",
        "tools": "/calculators/",
        "expert": "/ask-expert/",
        "guidance": "/guidance/",
        "faqs": "/faqs/",
        "contact": "/contact/"
    }
}
```

#### `terms/button-labels.json` — All Button Text

```json
{
    "explore_all": "Explore all",
    "explore_arrow": "Explore →",
    "calculate_now": "Calculate Now",
    "load_more": "Load More",
    "search": "Search",
    "view_all": "View all →",
    "read_more": "Read more →",
    "get_started": "Get Started →",
    "contact_us": "Contact Us",
    "subscribe": "Subscribe",
    "submit": "Submit",
    "back": "← Back",
    "next": "Next →",
    "previous": "← Previous",
    "filter": "Filter",
    "sort": "Sort",
    "clear": "Clear"
}
```

#### `terms/section-headings.json` — All Section Headings

```json
{
    "home_hero": "Your {domain} Journey, Explained.",
    "home_journey": "Choose Your {domain} Journey",
    "home_news": "Latest {news}",
    "home_tools": "Popular {calculators}",
    "home_guides": "{guides} & Insights",
    "home_regulations": "{brand} Updates",
    "home_hot_topics": "Hot Topics",
    "home_newsletter": "Stay Informed, Stay Ahead",
    "tools_hero": "{calculator_plural}",
    "tools_all": "All {calculator_plural}",
    "tools_categories": "Browse by Category",
    "tools_popular": "Most Popular",
    "news_hero": "{news} & Insights",
    "news_featured": "Featured {news}",
    "news_latest": "Latest {news}",
    "guides_hero": "{guide_plural}",
    "guides_browse": "Browse by {category}",
    "guides_featured": "Featured {guide_plural}",
    "contact_hero": "How can we help you?",
    "contact_form": "Send us your enquiry",
    "expert_hero": "Ask an {expert}",
    "expert_form": "Tell us about your requirement",
    "guidance_hero": "Get Expert {expert}",
    "guidance_form": "Tell us about your requirement",
    "faqs_hero": "Frequently Asked {faq_plural}",
    "newsletter_heading": "Stay Updated",
    "need_help": "Need Help With",
    "read_more": "Read More",
    "view_all": "View All",
    "share": "Share this {article}",
    "related": "Related {guide_plural}",
    "comments": "Comments"
}
```

### How JSON Data Flows to Templates

```
1. Page Request → TemplateRouter resolves page type
    │
    ▼
2. FeatureController::prepare()
    │
    ├──▶ Load config/site.json         → $site
    ├──▶ Load config/industry.json     → $industry
    ├──▶ Load config/navigation.json   → $nav
    ├──▶ Load pages/{page}.json        → $page
    ├──▶ Load sections/{section}.json  → $sections[]
    ├──▶ Load components/{comp}.json   → $components[]
    └──▶ Load terms/site-terms.json    → $terms
    │
    ▼
3. DataAggregator::merge()
    │
    ├──▶ Merge all JSON into unified $ctx
    ├──▶ Replace {domain}, {calculators}, {guides} placeholders from $industry
    ├──▶ Merge plugin data (DB content) into $ctx
    └──▶ Return $ctx
    │
    ▼
4. View Template receives $ctx
    │
    ├──▶ echo $ctx['site']['name']                    // "ADVAITH HOMES"
    ├──▶ echo $ctx['industry']['domain_noun']          // "Property"
    ├──▶ echo $ctx['sections']['hero-home']['title']   // "Your Property Journey..."
    ├──▶ foreach ($ctx['nav']['main'] as $item)        // Loop nav items
    │       echo $item['label']                        // "Guides", "Calculators"...
    └──▶ foreach ($ctx['footer']['columns'] as $col)   // Loop footer columns
            echo $col['heading']                       // "Explore", "Company"...
```

### Placeholder Replacement Engine

JSON values can contain `{placeholders}` that get replaced from `industry.json`:

```php
namespace Adn\Theme\Bridge;

class PlaceholderResolver
{
    private array $industryTerms = [];

    public function __construct(array $industry)
    {
        $this->industryTerms = $industry;
    }

    /**
     * Replace all {placeholders} in a value.
     * e.g., "Your {domain} Journey" → "Your Property Journey"
     */
    public function resolve(mixed $value): mixed
    {
        if (is_string($value)) {
            return str_replace(
                array_keys($this->industryTerms),
                array_values($this->industryTerms),
                $value
            );
        }

        if (is_array($value)) {
            return array_map([$this, 'resolve'], $value);
        }

        return $value;
    }

    /**
     * Recursively resolve all placeholders in an entire data structure.
     */
    public function resolveAll(array $data): array
    {
        return $this->resolve($data);
    }
}
```

**Usage in templates:**
```php
// industry.json has: { "domain": "Property", "calculators": "Calculators" }
// page JSON has: "Your {domain} Journey" 
// Resolved: "Your Property Journey"

echo $this->placeholderResolver->resolve($pageJson['hero']['title']);
// Output: "Your Property Journey"
```

---

## 22. Theme Override System

### How It Works

The theme has a **layered override system**. The base JSON files provide defaults. The `overrides/` directory allows per-project customization without modifying base files.

### Override Directory Structure

```
data/
├── config/                         # Base config (DO NOT EDIT for projects)
│   ├── site.json
│   ├── industry.json
│   └── ...
├── pages/                          # Base page content
├── sections/                       # Base section data
├── components/                     # Base component data
├── terms/                          # Base terminology
│
└── overrides/                      # ── PROJECT-SPECIFIC OVERRIDES ──
    │
    ├── (empty = use base defaults)
    │
    ├── OR per-project:
    │   ├── config/
    │   │   └── site.json           # Override just site identity
    │   ├── pages/
    │   │   └── home.json           # Override just home page content
    │   ├── sections/
    │   │   └── newsletter-cta.json # Override just newsletter CTA
    │   └── terms/
    │       └── site-terms.json     # Override just terminology
```

### Override Resolution Logic

```php
namespace Adn\Theme\Bridge;

class ConfigResolver
{
    private string $baseDir;
    private string $overrideDir;

    public function __construct(string $baseDir, string $overrideDir)
    {
        $this->baseDir = $baseDir;
        $this->overrideDir = $overrideDir;
    }

    /**
     * Load a JSON config file, with override support.
     * 
     * Resolution order:
     * 1. Check overrides/{path}
     * 2. If exists → use override
     * 3. If not → use base/{path}
     * 
     * This means: override files REPLACE base files entirely (not merge).
     * For partial overrides, use the merge method.
     */
    public function load(string $path): array
    {
        $overridePath = $this->overrideDir . '/' . $path;
        $basePath = $this->baseDir . '/' . $path;

        if (file_exists($overridePath)) {
            return $this->readJson($overridePath);
        }

        return $this->readJson($basePath);
    }

    /**
     * Load with deep merge — override file values override base values,
     * but missing keys fall back to base.
     * 
     * Use this when you want to override just ONE field in a large JSON.
     */
    public function loadMerged(string $path): array
    {
        $base = $this->readJson($this->baseDir . '/' . $path);
        $overridePath = $this->overrideDir . '/' . $path;

        if (!file_exists($overridePath)) {
            return $base;
        }

        $override = $this->readJson($overridePath);
        return array_replace_recursive($base, $override);
    }

    /**
     * Load with industry context — replace placeholders after loading.
     */
    public function loadResolved(string $path, array $industry): array
    {
        $data = $this->loadMerged($path);
        $resolver = new PlaceholderResolver($industry);
        return $resolver->resolveAll($data);
    }

    private function readJson(string $path): array
    {
        $content = file_get_contents($path);
        return json_decode($content, true) ?? [];
    }
}
```

### Override Examples

#### Example 1: Change Brand Only

Create `overrides/config/site.json`:
```json
{
    "name": "GREEN FIELD ORGANICS",
    "tagline": "Organic Farming Made Simple",
    "logo": { "icon": "🌾", "name": "GREEN FIELD", "subtitle": "ORGANICS" },
    "contact": { "email": "hello@greenfieldorganics.com" }
}
```

Everything else (navigation, footer, terms, pages) falls back to base defaults.

#### Example 2: Change Home Page Only

Create `overrides/pages/home.json`:
```json
{
    "hero": {
        "title_lines": [
            { "text": "Grow Better, Farm Smarter,", "accent": false },
            { "text": "Naturally.", "accent": true }
        ],
        "description": "Expert organic farming guidance, yield calculators and growing guides."
    }
}
```

#### Example 3: Change Industry + All Terminology

Create `overrides/config/industry.json`:
```json
{
    "type": "organic-farming",
    "label": "Organic Farming",
    "domain_noun": "Farm",
    "domain_plural": "Farms",
    "expert_noun": "Farming Expert",
    "guide_noun": "Growing Guide",
    "calculator_noun": "Yield Calculator",
    "icon": "🌾"
}
```

Then ALL templates automatically use "Farm" instead of "Property", "Growing Guide" instead of "Guide", etc.

### Admin Override Interface

The theme admin provides a UI to edit overrides without touching files:

```
Theme Admin → Admin Actions → Overrides
    │
    ├── Site Identity (config/site.json)
    ├── Navigation (config/navigation.json)
    ├── Page Content (pages/*.json)
    ├── Section Text (sections/*.json)
    └── Terminology (terms/*.json)
```

Each override editor:
1. Shows the base value (read-only)
2. Shows an input for the override value
3. Saves to `overrides/` directory
4. Clears relevant caches

---

## 23. Array-Driven Content Patterns

### Principle: No Duplication, Loop Everything

Every repeated UI pattern (nav items, footer columns, cards, process steps, trust items, social links, etc.) is defined as a JSON array and rendered with a loop. **Zero duplication in templates.**

### Pattern 1: Navigation Loop

**Before (hardcoded):**
```php
<!-- BAD: Each nav item hardcoded -->
<li><a href="/guides/">Guides</a></li>
<li><a href="/calculators/">Calculators</a></li>
<li><a href="/news/">News</a></li>
<li><a href="/ask-expert/">Ask an Expert</a></li>
<li><a href="/contact/">Contact</a></li>
```

**After (array-driven):**
```php
<!-- GOOD: Single loop from JSON -->
<?php foreach ($ctx['nav']['main'] as $item): ?>
    <li>
        <a href="<?= esc_url($item['url']) ?>">
            <?= esc_html($item['icon'] . ' ' . $item['label']) ?>
        </a>
    </li>
<?php endforeach; ?>
```

### Pattern 2: Footer Columns Loop

```php
<?php foreach ($ctx['footer']['columns'] as $column): ?>
    <div class="footer-column">
        <h4><?= esc_html($column['heading']) ?></h4>
        <ul>
            <?php foreach ($column['links'] as $link): ?>
                <li>
                    <a href="<?= esc_url($link['url']) ?>">
                        <?= esc_html($link['label']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endforeach; ?>
```

### Pattern 3: Trust Items Loop

```php
<?php foreach ($ctx['trust_items'] as $item): ?>
    <div class="trust-item">
        <span class="trust-icon"><?= esc_html($item['icon']) ?></span>
        <span class="trust-label"><?= esc_html($item['label']) ?></span>
    </div>
<?php endforeach; ?>
```

### Pattern 4: Process Steps Loop

```php
<?php foreach ($ctx['process_steps'] as $step): ?>
    <div class="process-step">
        <div class="step-number"><?= esc_html($step['number']) ?></div>
        <div class="step-icon"><?= esc_html($step['icon']) ?></div>
        <h3><?= esc_html($step['title']) ?></h3>
        <p><?= esc_html($step['description']) ?></p>
    </div>
<?php endforeach; ?>
```

### Pattern 5: Social Links Loop

```php
<?php foreach ($ctx['site']['social'] as $social): ?>
    <a href="<?= esc_url($social['url']) ?>"
       target="_blank"
       rel="noopener noreferrer"
       aria-label="<?= esc_attr($social['label']) ?>">
        <?= esc_html($social['platform']) ?>
    </a>
<?php endforeach; ?>
```

### Pattern 6: Page Sections Loop (Dynamic Section Rendering)

```php
<?php foreach ($ctx['sections'] as $section): ?>
    <?php
    // Load section data from JSON
    $sectionData = $configResolver->loadMerged("sections/{$section['type']}.json");
    $sectionData = array_merge($sectionData, $section['overrides'] ?? []);
    ?>
    <?php adn_component("sections/{$section['type']}", $sectionData); ?>
<?php endforeach; ?>
```

### Pattern 7: Card Grid Loop

```php
<?php
// Load card defaults from JSON
$cardDefaults = $configResolver->loadMerged("components/cards/{$cardType}.json");
?>

<?php foreach ($items as $item): ?>
    <?php
    // Merge item data with defaults
    $cardData = array_merge($cardDefaults['defaults'], $item);
    ?>
    <?php adn_component("cards/{$cardType}", $cardData); ?>
<?php endforeach; ?>
```

### Pattern 8: Enquiry Types / Form Options Loop

```php
<?php
// Load from config/forms.json
$formConfig = $configResolver->loadMerged('config/forms.json');
$enquiryTypes = $formConfig['contact_form']['enquiry_types'];
?>

<?php foreach ($enquiryTypes as $type): ?>
    <label class="enquiry-type-option">
        <input type="radio" name="enquiry_type" value="<?= esc_attr($type['key']) ?>">
        <span class="icon"><?= esc_html($type['icon']) ?></span>
        <span class="label"><?= esc_html($type['label']) ?></span>
    </label>
<?php endforeach; ?>
```

### Pattern 9: Breadcrumbs Loop

```php
<?php
$breadcrumbs = $ctx['breadcrumbs'] ?? [];
$homeLabel = $ctx['nav']['breadcrumbs']['home_label'] ?? 'Home';
$separator = $ctx['nav']['breadcrumbs']['separator'] ?? '/';
?>

<nav aria-label="Breadcrumb">
    <ol class="breadcrumb">
        <li>
            <a href="<?= esc_url($ctx['site']['logo']['url']) ?>">
                <?= esc_html($homeLabel) ?>
            </a>
        </li>
        <?php foreach ($breadcrumbs as $index => $crumb): ?>
            <li>
                <?php if ($crumb['url']): ?>
                    <a href="<?= esc_url($crumb['url']) ?>">
                        <?= esc_html($crumb['label']) ?>
                    </a>
                <?php else: ?>
                    <span aria-current="page"><?= esc_html($crumb['label']) ?></span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
```

### Pattern 10: FAQ Accordion Loop

```php
<?php foreach ($ctx['faqs'] as $category => $faqs): ?>
    <div class="faq-category">
        <h3><?= esc_html($category) ?></h3>
        <?php foreach ($faqs as $faq): ?>
            <details class="faq-item">
                <summary><?= esc_html($faq['question']) ?></summary>
                <div class="faq-answer">
                    <?= wp_kses_post($faq['answer']) ?>
                </div>
            </details>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>
```

### Pattern 11: Meta Tags Loop (SEO)

```php
<?php
$seoConfig = $configResolver->loadMerged('config/seo.json');
$pageSeo = $seoConfig['pages'][$pageSlug] ?? [];
$defaults = $seoConfig['defaults'];
?>

<title><?= esc_html($pageSeo['title'] ?? $siteName . $defaults['title_suffix']) ?></title>
<meta name="description" content="<?= esc_attr($pageSeo['description'] ?? $siteDescription) ?>">
<meta property="og:title" content="<?= esc_attr($pageSeo['title'] ?? $siteName) ?>">
<meta property="og:type" content="<?= esc_attr($defaults['og_type']) ?>">
<meta property="og:image" content="<?= esc_url($pageSeo['og_image'] ?? $defaultOgImage) ?>">
<meta name="twitter:card" content="<?= esc_attr($defaults['twitter_card']) ?>">
```

### Pattern 12: Feature Flags Loop

```php
<?php
$constants = $configResolver->loadMerged('config/constants.json');
$features = $constants['features'];
?>

<?php if ($features['enable_newsletter']): ?>
    <?php adn_component('parts/newsletter_signup', $ctx); ?>
<?php endif; ?>

<?php if ($features['enable_whatsapp']): ?>
    <?php adn_component('parts/whatsapp_button', $ctx); ?>
<?php endif; ?>

<?php if ($features['enable_floating_contact']): ?>
    <?php adn_component('parts/floating_contact', $ctx); ?>
<?php endif; ?>
```

### Complete Override Example: Property → Organic Farming

**Step 1:** Create `overrides/config/site.json`:
```json
{
    "name": "GREEN FIELD ORGANICS",
    "tagline": "Organic Farming Made Simple",
    "description": "Expert organic farming guidance, yield calculators and growing guides.",
    "logo": { "icon": "🌾", "name": "GREEN FIELD", "subtitle": "ORGANICS" },
    "contact": { "email": "hello@greenfieldorganics.com" },
    "social": [
        { "platform": "youtube", "url": "#", "label": "YouTube" }
    ]
}
```

**Step 2:** Create `overrides/config/industry.json`:
```json
{
    "type": "organic-farming",
    "label": "Organic Farming",
    "domain_noun": "Farm",
    "domain_plural": "Farms",
    "expert_noun": "Farming Expert",
    "guide_noun": "Growing Guide",
    "calculator_noun": "Yield Calculator",
    "icon": "🌾",
    "currency": "$",
    "location_default": "USA"
}
```

**Step 3:** Create `overrides/pages/home.json`:
```json
{
    "hero": {
        "title_lines": [
            { "text": "Your Organic Farming Journey,", "accent": false },
            { "text": "Starts Here.", "accent": true }
        ],
        "description": "Expert guidance, yield calculators and growing tips for organic farmers.",
        "bg_icon": "🌾"
    }
}
```

**Result:** The entire site now reads:
- Header: "GREEN FIELD ORGANICS"
- Hero: "Your Organic Farming Journey, Starts Here."
- Nav: "Growing Guides" instead of "Property Guides"
- Tools: "Yield Calculators" instead of "Property Calculators"
- Expert: "Farming Expert" instead of "Property Expert"
- Footer: "© 2025 GREEN FIELD ORGANICS"

**Zero PHP changes. Zero template changes. Only JSON files.**

---

## 24. Configuration Strategy

**See Sections 22-24 for the full JSON-driven configuration system.**

### Configuration Hierarchy

```
┌─────────────────────────────────────────────────────────────┐
│  LAYER 1: Plugin Defaults (src/Config/Defaults.php)          │
│  Hardcoded PHP defaults — fallback for everything            │
├─────────────────────────────────────────────────────────────┤
│  LAYER 2: Plugin DB Settings (ah_site_settings)              │
│  Admin-editable settings stored in database                  │
├─────────────────────────────────────────────────────────────┤
│  LAYER 3: JSON Base Files (data/config/*.json)               │
│  Industry-agnostic defaults — site identity, nav, content    │
├─────────────────────────────────────────────────────────────┤
│  LAYER 4: JSON Page Files (data/pages/*.json)                │
│  Per-page content, hero text, CTAs                           │
├─────────────────────────────────────────────────────────────┤
│  LAYER 5: JSON Override Files (data/overrides/*.json)        │
│  Project-specific overrides — no base file modification      │
├─────────────────────────────────────────────────────────────┤
│  LAYER 6: Theme Admin Overrides (WP Options)                 │
│  Runtime overrides via theme admin UI                        │
└─────────────────────────────────────────────────────────────┘

Resolution: Layer 6 > Layer 5 > Layer 4 > Layer 3 > Layer 2 > Layer 1
```

### Plugin Configuration

| Type | Current | Recommended |
|------|---------|-------------|
| Constants | `ah-cms.php` lines 12-29 | Keep in bootstrap, add `Config\Defaults.php` |
| Settings | `ah_site_settings` DB table | Keep, add `SettingsService` for CRUD |
| Workflow config | `ah_workflow_config` WP option | Move to dedicated `ah_workflow_settings` table |
| Autoloader | Manual classmap in `class-autoloader.php` | Replace with Composer PSR-4 |
| Feature config | Scattered across files | Each feature has its own `Config/defaults.php` |

### Theme Configuration

| Type | Current | Recommended |
|------|---------|-------------|
| Static JSON | `data/advaith/json/*.json` | Restructure to `data/config/`, `data/pages/`, `data/sections/`, `data/components/`, `data/terms/` |
| Terms | `includes/core_terms.php` + `terms.json` | Move all to `data/terms/site-terms.json` |
| Settings | WP options (`adn_*`) | Keep, ensure consistent naming |
| Settings schemas | `admin/settings-schemas.php` | Move to `src/Admin/Schema/` |
| CORS | `cors-origin.php` hardcoded | Move to admin-configurable setting |
| Hardcoded titles | PHP templates | Move to JSON per-section and per-page files |
| Industry-specific | Scattered in PHP | All in `data/config/industry.json` with placeholder replacement |

---

## 25. Logging & Exception Handling

### Exception Hierarchy

```
Ah\Cms\Exception\PluginException (base)
├── ValidationException        # Input validation failures
├── UnauthorizedException      # Auth/capability failures
├── NotFoundException          # Entity not found
├── DatabaseException          # DB operation failures
└── ExternalServiceException   # Third-party API failures
```

### Logging Strategy

| Log Type | Destination | Retention |
|----------|-------------|-----------|
| PHP errors | `wp-content/debug.log` | Manual |
| Application errors | `wp-content/logs/cms-plugin-{date}.log` | 30 days |
| Audit trail | `ah_audit_logs` DB table | Configurable |
| Workflow execution | `ah_trigger_logs` DB table | Configurable |
| Admin actions | `ah_audit_logs` DB table | Permanent |

### Error Handler

```php
class ErrorHandler
{
    public static function register(): void
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
    }

    public static function handleError(int $code, string $message, string $file, int $line): bool
    {
        Logger::error($message, ['code' => $code, 'file' => $file, 'line' => $line]);
        return false;
    }

    public static function handleException(\Throwable $e): void
    {
        Logger::error($e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        if (is_admin()) {
            // Show error notice in admin
        }
        // Silent in frontend — log only
    }
}
```

---

## 26. Security Improvements

### Already Implemented (Preserved)
- Nonce verification on all form submissions and AJAX handlers
- Capability checks (`manage_options`) on all admin operations
- Input sanitization (`sanitize_text_field`, `sanitize_textarea_field`, etc.)
- Output escaping (`esc_html`, `esc_attr`, `esc_url`, etc.)
- Direct access prevention (`defined('ABSPATH') || exit`)
- Honeypot anti-spam on public forms
- Path traversal prevention via `realpath()` containment
- SQL injection prevention via `$wpdb->prepare()`
- Timing-safe token comparison (`hash_equals`)
- `.htaccess` directory protection

### Improvements Needed

| # | Issue | Recommendation |
|---|-------|----------------|
| 1 | **No CSRF on some admin_post handlers** | Ensure every POST handler has `check_admin_referer()` |
| 2 | **Hardcoded API keys in REST endpoints** | Add rate limiting on public endpoints |
| 3 | **SQL validation is keyword-based** | Use prepared statements with whitelisted columns |
| 4 | **No CSP headers** | Add Content Security Policy for admin pages |
| 5 | **Predictable honeypot field name** (`adn_hp`) | Randomize per form |
| 6 | **Custom code stores raw CSS/JS** | Validate syntax before storage |
| 7 | **Newsletter unsubscribe tokens don't expire** | Add expiration |
| 8 | **No rate limiting on AJAX** | Add throttling on form submit and subscribe |
| 9 | **`WP_DEBUG` in production** | Add admin notice if enabled |
| 10 | **CSV export writes to filesystem** | Consider streaming download |

---

## 27. Performance Improvements

| # | Issue | Impact | Recommendation |
|---|-------|--------|----------------|
| 1 | **14 CSS loaded on every page (theme)** | 14 HTTP requests | Combine into 2-3 shared + feature-specific |
| 2 | **9 JS loaded on every page (theme)** | 9 parse/execute cycles | Combine into 1-2 shared + feature-specific |
| 3 | **Font Awesome CDN on every page** | External dependency | Bundle locally or subset |
| 4 | **No object caching** | DB hit on every settings read | Implement wp_object_cache integration |
| 5 | **99 wpdb calls in admin templates** | N+1 queries | Move to repositories with caching |
| 6 | **Home fragment cache is optional** | Cache miss = full rebuild | Make mandatory for home page |
| 7 | **No lazy loading enforcement** | LCP delay | Ensure all images use `loading="lazy"` |
| 8 | **Workflow cron every minute** | DB overhead | Skip if no active rules |
| 9 | **Full table scan for search** | Slow on large datasets | Add FULLTEXT indexes |
| 10 | **No transients for expensive queries** | Repeated queries | Cache taxonomy trees, menus, settings |

---

## 28. Coding Standards

| # | Standard | Detail |
|---|----------|--------|
| 1 | **PSR-4 namespaces** | All classes under `Ah\Cms\` (plugin) and `Adn\Theme\` (theme) |
| 2 | **PHP 7.4+ minimum** | Typed properties, parameter/return types on all methods |
| 3 | **One class per file** | Filename matches class name |
| 4 | **Naming conventions** | PascalCase classes, camelCase methods, UPPER_SNAKE_CASE constants |
| 5 | **No procedural functions** | All functions in classes as static or instance methods |
| 6 | **No global state** | Use dependency injection, not `global $wpdb` or static singletons |
| 7 | **Text domains** | Plugin: `cms-plugin`, Theme: `advaith` |
| 8 | **PHPCS** | WordPress Coding Standards + PSR-12 |
| 9 | **PHPDoc** | All public methods documented |
| 10 | **No `style.css` for plugin header** | Plugin header only in `ah-cms.php` |

---

## 29. Refactoring Roadmap

### Phase 1: Foundation (Week 1-2)
**Goal:** Set up infrastructure without changing functionality.

| Step | Task | Risk | Files Touched |
|------|------|------|---------------|
| 1.1 | Remove 8 dead/empty files | None | `constants.php`, `helper/common.php`, etc. |
| 1.2 | Fix version mismatch (`1.0.2` → `1.3.1`) | None | `ah-cms.php` |
| 1.3 | Fix 5 missing autoloader entries | Low | `inc/class-autoloader.php` |
| 1.4 | Split two-classes-per-file | Low | `models/class-spotlights-model.php`, `models/class-analytics-model.php` |
| 1.5 | Rename `explode_function.php` → `theme-helpers.php` | Low | `explode_function.php`, `functions.php` |
| 1.6 | Rename `Term_Manager` → `AH_Term_Manager` | Low | `inc/term-manager.php`, references |
| 1.7 | Add Composer with PSR-4 autoloading (alongside classmap) | Low | New `composer.json`, `ah-cms.php` |
| 1.8 | Add PHPCS configuration | None | New `.phpcs.xml`, `composer.json` |

### Phase 2: Feature Module Structure (Week 3-4)
**Goal:** Create the feature-first directory structure and module entry points.

| Step | Task | Risk | Files Touched |
|------|------|------|---------------|
| 2.1 | Create `src/Feature/` directory structure | None | New directories |
| 2.2 | Create `AbstractRepository` base class | None | New `src/Repository/AbstractRepository.php` |
| 2.3 | Create `Connection` wrapper (rename `AH_DB_Helper`) | Low | `database/class-db-helper.php` → `src/Database/Connection.php` |
| 2.4 | Create module entry points for each feature | None | New `*Module.php` files |
| 2.5 | Move existing model classes into feature modules | Low | `models/*.php` → `src/Feature/*/Model/` |
| 2.6 | Create repositories for each entity (22+) | Low | New `src/Feature/*/Repository/` files |

### Phase 3: Service Extraction (Week 5-6)
**Goal:** Extract business logic from god objects and procedural files.

| Step | Task | Risk | Files Touched |
|------|------|------|---------------|
| 3.1 | Extract `SettingsService` | Low | New file, `ah-cms.php` |
| 3.2 | Extract `CustomCodeService` | Medium | `ah-cms.php` lines 399-540 |
| 3.3 | Extract `RedirectService` | Medium | `ah-cms.php` lines 313-365 |
| 3.4 | Extract shortcodes into feature modules | Low | `ah-cms.php` lines 82-268 |
| 3.5 | Extract theme procedural services into classes | Medium | `apis/services.php`, `apis/services_cms.php` |
| 3.6 | Extract `SeoService` | Low | `includes/seo.php` |

### Phase 4: Workflow Manager Decomposition (Week 7-8)
**Goal:** Break the 2,118-line god object into focused classes.

| Step | Task | Risk | Files Touched |
|------|------|------|---------------|
| 4.1 | Extract `RuleEngine` (CRUD) | Medium | `inc/class-workflow-manager.php` |
| 4.2 | Extract `ConditionEvaluator` | Medium | `inc/class-workflow-manager.php` |
| 4.3 | Extract `ActionExecutor` with strategy classes | Medium | `inc/class-workflow-manager.php` |
| 4.4 | Extract `WorkflowRestApi` | Low | `inc/class-workflow-manager.php` |
| 4.5 | Extract `WorkflowCron` | Low | `inc/class-workflow-manager.php` |
| 4.6 | Extract `WorkflowAdminPage` | Medium | `inc/class-workflow-manager.php` |

### Phase 5: Admin Bootstrap Decomposition (Week 9-10)
**Goal:** Split admin bootstrap into feature controllers.

| Step | Task | Risk | Files Touched |
|------|------|------|---------------|
| 5.1 | Create `AbstractAdminPage` base | Low | New file |
| 5.2 | Extract admin page controllers into feature modules | Medium | 34 admin page files |
| 5.3 | Extract `AdminAssetLoader` | Low | `admin/class-admin-bootstrap.php` |
| 5.4 | Extract admin_post handlers into controllers | Medium | `admin/class-admin-bootstrap.php` |

### Phase 6: Block Renderer Decomposition (Week 11)
**Goal:** Split the 1,020-line block renderer.

| Step | Task | Risk | Files Touched |
|------|------|------|---------------|
| 6.1 | Create `BlockRendererInterface` + `BlockRendererRegistry` | Low | New files |
| 6.2 | Extract each block type into its own class | Low | `inc/builder-block-renderer.php` |
| 6.3 | Update `BuilderPageRenderer` to use registry | Low | `templates/template-builder-page.php` |

### Phase 7: Theme Feature Modules (Week 12-14)
**Goal:** Organize theme by feature modules.

| Step | Task | Risk | Files Touched |
|------|------|------|---------------|
| 7.1 | Create `src/Feature/` directory structure for theme | None | New directories |
| 7.2 | Create feature entry points (*Feature.php) | Low | New files |
| 7.3 | Move page templates into feature modules | Medium | `pages/*.php` → `src/Feature/*/View/` |
| 7.4 | Move intermediate files into feature modules | Medium | `intermediate/*.php` → `src/Feature/*/View/intermediate/` |
| 7.5 | Move feature-specific CSS/JS into feature modules | Medium | `assets/css/*.css`, `assets/js/*.js` |
| 7.6 | Create `CmsDataService` (replaces `services_cms.php`) | Medium | `apis/services_cms.php` |
| 7.7 | Decompose `common_functions.php` into helpers | Low | `common/common_functions.php` |

### Phase 8: Theme Admin Decomposition (Week 15)
**Goal:** Break the 1,717-line theme admin into tab controllers.

| Step | Task | Risk | Files Touched |
|------|------|------|---------------|
| 8.1 | Create `AbstractTab` base class | Low | New file |
| 8.2 | Extract each tab into its own controller | Medium | `admin/class-theme-admin.php` |
| 8.3 | Move admin_post handlers into tab controllers | Medium | `admin/class-theme-admin.php` |

### Phase 9: Asset Optimization (Week 16)
**Goal:** Reduce unnecessary asset loading.

| Step | Task | Risk | Files Touched |
|------|------|------|---------------|
| 9.1 | Move shared CSS/JS to `assets/` (shared layer) | Medium | Theme `assets/css/` |
| 9.2 | Move feature-specific CSS/JS to feature modules | Medium | Feature `Assets/` dirs |
| 9.3 | Make CSS/JS loading conditional per feature | Medium | Feature `*Feature.php` |
| 9.4 | Bundle Font Awesome (subset) | Low | Font files |
| 9.5 | Add defer/async to non-critical JS | Low | Asset enqueue calls |

### Phase 10: Error Handling & Logging (Week 17)
**Goal:** Add structured error handling.

| Step | Task | Risk | Files Touched |
|------|------|------|---------------|
| 10.1 | Create exception hierarchy | None | New `src/Exception/` files |
| 10.2 | Create `Logger` class | None | New `src/Support/Logger.php` |
| 10.3 | Create `ErrorHandler` | None | New `src/Support/ErrorHandler.php` |
| 10.4 | Replace `wp_die()` with exceptions | Low | All error paths |

### Phase 11: Testing & Documentation (Week 18)
**Goal:** Add safety net.

| Step | Task | Risk | Files Touched |
|------|------|------|---------------|
| 11.1 | Add PHPUnit configuration | None | `phpunit.xml`, `composer.json` |
| 11.2 | Write tests for Repository layer | None | New test files |
| 11.3 | Write tests for Service layer | None | New test files |
| 11.4 | Add PHPDoc to all public methods | None | All PHP files |
| 11.5 | Update ARCHITECTURE.md | None | `docs/ARCHITECTURE.md` |

### Phase 12: JSON Migration & Industry-Agnostic Content (Week 19-20)
**Goal:** Move all hardcoded text, titles, labels, and content to JSON files. Make the site fully rebrandable without code changes.

| Step | Task | Risk | Files Touched |
|------|------|------|---------------|
| 12.1 | Create `data/config/` directory structure | None | New directories |
| 12.2 | Create `config/site.json` from current hardcoded values | Low | New file, audit all templates |
| 12.3 | Create `config/industry.json` with current property terms | None | New file |
| 12.4 | Create `config/navigation.json` from current nav registration | Low | `admin/menus/`, `includes/core_routing.php` |
| 12.5 | Create `config/footer.json` from current footer HTML | Low | `components/parts/main_footer.php` |
| 12.6 | Create `config/seo.json` from current SEO function | Low | `includes/seo.php` |
| 12.7 | Create `config/forms.json` from current form labels | Low | All form templates |
| 12.8 | Create `config/emails.json` from current email text | Low | `class-newsletter.php`, enquiry handlers |
| 12.9 | Create `config/constants.json` with feature flags | None | New file |
| 12.10 | Create `pages/*.json` for all 10 page types | Medium | New files, audit page templates |
| 12.11 | Create `sections/*.json` for all reusable sections | Medium | New files, audit section components |
| 12.12 | Create `components/*.json` for card/sidebar defaults | Low | New files |
| 12.13 | Create `terms/*.json` (migrate from `terms.json`) | Low | `terms.json` → split into multiple files |
| 12.14 | Create `PlaceholderResolver` class | None | New `src/Bridge/PlaceholderResolver.php` |
| 12.15 | Create `ConfigResolver` class with override support | None | New `src/Bridge/ConfigResolver.php` |
| 12.16 | Update DataAggregator to use ConfigResolver | Medium | `src/Bridge/DataAggregator.php` |
| 12.17 | Update all templates to read from JSON context | High | All 113 components, 16 page templates |
| 12.18 | Create empty `overrides/` directory | None | New directory |
| 12.19 | Test: change `industry.json` to verify placeholder replacement | None | Verification only |
| 12.20 | Test: create override files to verify override system | None | Verification only |

---

## Summary Metrics

| Metric | Current | Target |
|--------|---------|--------|
| Largest file | 2,118 lines (`AH_Workflow_Manager`) | < 300 lines |
| Files > 500 lines | 8 | 0 |
| Direct `$wpdb` in templates | 99 calls | 0 |
| Global functions in page templates | 30+ | 0 |
| Dead/empty files | 8 | 0 |
| Autoloader missing entries | 5 | 0 |
| Namespaces | 0 | 2 root namespaces + 30+ feature sub-namespaces |
| Unit tests | 0 | Core business logic |
| CSS files per page (theme) | 14 (all pages) | 2-3 shared + 1-2 feature-specific |
| JS files per page (theme) | 9 (all pages) | 1-2 shared + 1 feature-specific |
| Feature modules (plugin) | 0 | 25+ |
| Feature modules (theme) | 0 | 11 |
| Coupling: theme → plugin DB | Direct SQL | Service interfaces only |
| Hardcoded titles in templates | 50+ occurrences | 0 (all in JSON) |
| Industry-specific strings in PHP | 100+ occurrences | 0 (all in `industry.json`) |
| JSON config files | 14 (scattered) | 30+ (organized by purpose) |
| Override capability | None | Full (config, pages, sections, terms) |
| Rebrand effort | Days (code changes) | Minutes (JSON swap only) |

---

## 30. Centralized Hook Registration Pattern

### Principle: All Hooks in One Place

Every `add_action`, `add_filter`, and `add_shortcode` lives in a single `HookRegistrar` file per project. When migrating, you only look at 2 files (plugin HookRegistrar + theme HookRegistrar) to see the full hook picture.

### Plugin HookRegistrar

**File:** `src/Bootstrap/HookRegistrar.php`

All plugin hooks are registered in one place:
- Database hooks (activation, upgrade)
- Admin bootstrap
- REST API routes
- Frontend hooks (template_redirect, wp_head, wp_footer)
- Shortcodes
- Cron schedules
- AJAX handlers
- Filters
- Feature module admin menus + assets
- Permission system

**Usage in `ah-cms.php`:**
```php
// Single call — all hooks registered
AH_Cms\Bootstrap\HookRegistrar::register();
```

### Theme HookRegistrar

**File:** `src/Bootstrap/HookRegistrar.php`

All theme hooks are registered in one place:
- Theme setup (after_setup_theme)
- Cache management
- Frontend output (footer, head, template_redirect)
- Database installation
- AJAX handlers
- Filters (cache busting, lazy loading)
- Shortcodes
- Feature module hooks

**Usage in `functions.php`:**
```php
// Single call — all hooks registered
Adn\Theme\Bootstrap\HookRegistrar::register();
```

### Common Functions Pattern

All built-in functions live in organized files under `common/`:

```
common/
├── ajax/              # AJAX handlers (expert, post, comment)
├── frontend/          # Frontend output (site-notice, floating-contact, scroll-reveal)
├── enqueue/           # Asset loading
├── database/          # DB installation
├── filters/           # Filter callbacks (cache-busting, lazy-loading)
├── shortcodes/        # Shortcode callbacks
├── cache/             # Cache management
└── helpers/           # Utility functions
```

**Migration benefit:** Look at `HookRegistrar` for what hooks exist. Look at `common/` for what functions exist. Everything is organized and easy to find.

---

## 31. Permission System

### Feature Capabilities

Every CMS feature has granular permissions: `view`, `edit`, `delete`.

**Capability naming:** `ah_{feature}_{action}`
- `ah_pages_view` — View Pages
- `ah_pages_edit` — Edit Pages
- `ah_pages_delete` — Delete Pages
- `ah_reviews_view` — View Reviews
- `ah_reviews_edit` — Edit Reviews
- etc.

**File:** `src/Config/Capabilities.php` — defines all 80+ capabilities

### Permission Checking

**File:** `src/Support/PermissionService.php`

```php
use Ah\Cms\Support\PermissionService;

// Check permission
if ( PermissionService::can( 'pages', 'edit' ) ) {
    // User can edit pages
}

// Enforce permission (dies with 403 if denied)
PermissionService::enforce( 'reviews', 'delete' );

// Admin always has full access via 'manage_options'
// Other roles need specific capabilities assigned
```

### Admin UI

**File:** `src/Feature/AdminTools/Controller/PermissionManagerController.php`

Admin page at **CMS → Permissions** lets you:
- View all capabilities per feature
- Toggle capabilities per role
- Administrator always has full access

### How It Works

1. **Admin** (`manage_options`) — always has full access to everything
2. **Custom roles** — get specific capabilities assigned via the Permissions admin page
3. **Capability check** — `PermissionService::can('feature', 'action')` checks WordPress capabilities
4. **Menu visibility** — `add_submenu_page` uses `getMenuCapability()` so users only see menus they can access

### Adding Permissions to New Features

1. Add capabilities to `Capabilities::getAll()`:
   ```php
   'ah_myfeature_view' => 'View My Feature',
   'ah_myfeature_edit' => 'Edit My Feature',
   'ah_myfeature_delete' => 'Delete My Feature',
   ```

2. Check permissions in controllers:
   ```php
   PermissionService::enforce( 'myfeature', 'edit' );
   ```

3. Register capabilities on activation:
   ```php
   Capabilities::register(); // Adds caps to all existing roles
   ```

---

## 32. Refactoring Completion Status

### Completed Phases (2026-07-23)

| Phase | Name | Status | Key Deliverables |
|-------|------|--------|------------------|
| 1 | Foundation | **COMPLETED** | Dead files removed, version fixed, autoloader updated, PSR-4 added |
| 2 | Feature Module Structure | **COMPLETED** | 28 plugin modules, 11 theme modules, Bootstrap files created |
| 3 | Service Extraction | **COMPLETED** | Shortcodes, Redirect, CustomCode, BuilderPage services extracted |
| 4 | Workflow Manager Decomposition | **COMPLETED** | RuleEngine, ConditionEvaluator, ActionExecutor, WorkflowCron created |
| 6 | Block Renderer Decomposition | **COMPLETED** | BlockRendererInterface + BlockRendererRegistry created |
| 7 | Theme Feature Modules | **COMPLETED** | 12 controllers, 13 page templates, Bridge module created |
| 8 | Theme Admin Decomposition | **COMPLETED** | 10 tab controllers in `src/Admin/Tab/` |
| 10 | Error Handling & Logging | **COMPLETED** | Exception hierarchy (5 classes) + Logger + ErrorHandler |
| 31 | HookRegistrar Pattern | **COMPLETED** | Centralized hook registration for plugin + theme |
| 32 | Permission System | **COMPLETED** | 80+ capabilities, PermissionService, Admin UI |
| 33 | OOP Classes | **COMPLETED** | RequestHelper, MediaHelper, SiteChromeService, CmsDataService |

### New Architecture Components Created

| Component | Location | Purpose |
|-----------|----------|---------|
| `HookRegistrar` (Plugin) | `src/Bootstrap/HookRegistrar.php` | All plugin hooks in one file |
| `HookRegistrar` (Theme) | `src/Bootstrap/HookRegistrar.php` | All theme hooks in one file |
| `PluginBootstrap` | `src/Bootstrap/PluginBootstrap.php` | Plugin lifecycle entry point |
| `ThemeBootstrap` | `src/Bootstrap/ThemeBootstrap.php` | Theme lifecycle entry point |
| `Capabilities` | `src/Config/Capabilities.php` | 80+ feature permissions |
| `PermissionService` | `src/Support/PermissionService.php` | Permission checking |
| `PermissionManagerController` | `src/Feature/AdminTools/Controller/` | Admin UI for permissions |
| `DataAggregator` | `src/Bridge/DataAggregator.php` | Data aggregation bridge |
| `PluginDataSource` | `src/Bridge/PluginDataSource.php` | Plugin data reader |
| `JsonDataSource` | `src/Bridge/JsonDataSource.php` | JSON file reader |
| `RequestHelper` | `src/Helper/RequestHelper.php` | Request/input utilities |
| `MediaHelper` | `src/Helper/MediaHelper.php` | Media URL resolution |
| `StringHelper` | `src/Helper/StringHelper.php` | String utilities |
| `SiteChromeService` | `src/Service/SiteChromeService.php` | Site chrome data |
| `CmsDataService` | `src/Service/CmsDataService.php` | CMS database access |

### Files Modified

| File | Before | After | Change |
|------|--------|-------|--------|
| `ah-cms.php` | 109 lines | 37 lines | Thin bootstrap, PSR-4 autoloading |
| `functions.php` | 816 lines | 72 lines | Thin bootstrap, common wired |
| `common_functions.php` | 926 lines | ~200 lines | Thin wrappers delegating to OOP classes |
| `class-autoloader.php` | 99 lines | 110 lines | Added PSR-4 for `Ah\Cms\` namespace |
| All `src/` PHP files | namespace ordering fixed | 147 files | Namespace before `defined()` |

### OOP Classes Created (Replacing Procedural Functions)

| Class | Replaces | Methods |
|-------|----------|---------|
| `RequestHelper` | `getRequestParameter()`, `getJsonParameter()`, `getJsonData()` | `get()`, `getJson()`, `getJsonBody()` |
| `MediaHelper` | `adn_settings_media_url_type()` | `resolveUrlType()` |
| `SiteChromeService` | `adn_service_site_chrome()`, `adn_get_contact_setting()`, `adn_get_social_setting()` | `getData()`, `getContactSetting()`, `getSocialSetting()` |
| `CmsDataService` | `adn_cms_table()`, `adn_cms_available()`, `adn_cms_guide_parents()`, etc. | 15+ methods |

### Backward Compatibility

All original function names are preserved as thin wrappers in `common_functions.php`. Existing code continues to work. New code should use the OOP classes directly.

---

*This architecture document defines the target state. The refactoring roadmap provides a safe, incremental path across 12 phases. Each phase is independently deployable — no big-bang rewrites. The JSON-driven configuration system (Phases 12) makes the entire site rebrandable by swapping JSON files only — zero code changes needed. The HookRegistrar pattern (Section 31) centralizes all hooks for easy migration. The Permission System (Section 32) provides granular access control per feature. The OOP classes (Section 33) replace procedural functions for reusability and testability.
