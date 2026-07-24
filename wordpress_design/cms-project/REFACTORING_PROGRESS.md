# Refactoring Progress Tracker

## Architecture Document
`ARCHITECTURAL_REVIEW.md` — 32 sections, 12 phases, ~5400 lines

## Current Status (Updated 2026-07-23T10:05)

| Phase | Name | Status | Notes |
|-------|------|--------|-------|
| 1 | Foundation | **COMPLETED** | All steps done, Term_Manager refs fixed |
| 2 | Feature Module Structure | **COMPLETED** | Bootstrap + StringHelper + PSR-4 autoloading |
| 3 | Service Extraction | **COMPLETED** | Shortcodes, Redirect, CustomCode, BuilderPage services extracted |
| 4 | Workflow Manager Decomposition | **COMPLETED** | 2118→308 lines. RuleEngine (552L), ActionExecutor (837L), ConditionEvaluator (71L), WorkflowCron (94L) |
| 5 | Admin Bootstrap Decomposition | **COMPLETED** | 898→302 lines. NavigationAdmin, SpotlightsAdmin, SiteNoticesAdmin, BannersAdmin, PostsAdmin controllers |
| 6 | Block Renderer Decomposition | **COMPLETED** | BlockRendererInterface + BlockRendererRegistry created |
| 7 | Theme Feature Modules | **COMPLETED** | 11 feature modules, controllers exist, Bridge module created |
| 8 | Theme Admin Decomposition | **COMPLETED** | 10 tab controllers in `src/Admin/Tab/` |
| 9 | Asset Optimization | **COMPLETED** | CSS split into core (11) + conditional per page. JS split into core (5) + conditional. Homepage: 14 CSS (was 17) |
| 10 | Error Handling & Logging | **COMPLETED** | Exception hierarchy (5 classes) + Logger + ErrorHandler |
| 11 | Testing & Documentation | **SKIPPED** | phpunit.xml exists but no tests — dedicated session needed |
| 12 | JSON Migration | **COMPLETED** | 6 config JSON + 14 page JSON. Header/footer read from JSON via SiteChromeService. Bridge module created. |
| 13 | Intermediate File Cleanup | **COMPLETED** | 3 Repository classes extracted. Zero $wpdb in intermediates. |
| 14 | Intermediate → OOP Conversion | **COMPLETED** | 14 intermediate files → 14 OOP classes in src/Service/*Context.php. Wrappers kept (11-38 lines each). Controllers call OOP directly. |
| 31 | HookRegistrar Pattern | **COMPLETED** | Centralized hook registration for plugin + theme |
| 32 | Permission System | **COMPLETED** | 80+ capabilities, PermissionService, Admin UI |
| 33 | OOP Classes | **COMPLETED** | RequestHelper, MediaHelper, SiteChromeService, CmsDataService |

### New Work Verified ✅

| Item | Status | Details |
|------|--------|---------|
| Plugin HookRegistrar | ✅ | `src/Bootstrap/HookRegistrar.php` — all plugin hooks centralized, `\` prefixes on WP functions |
| Theme HookRegistrar | ✅ | `src/Bootstrap/HookRegistrar.php` — all theme hooks centralized, `\` prefixes on WP functions |
| Plugin bootstrap | ✅ | `ah-cms.php` = 37 lines (thin) — PSR-4 autoloading handles class loading |
| Theme functions.php | ✅ | `functions.php` = 72 lines (thin) — common wired via require_once |
| Theme common functions | ✅ | 12 organized files under `common/` (ajax, frontend, cache, filters, shortcodes, etc.) |
| Plugin PluginBootstrap | ✅ | `src/Bootstrap/PluginBootstrap.php` — lifecycle entry point |
| Theme ThemeBootstrap | ✅ | `src/Bootstrap/ThemeBootstrap.php` — lifecycle entry point |
| Theme StringHelper | ✅ | `src/Helper/StringHelper.php` — string utilities (slug, truncate, case conversion) |
| Permission System | ✅ | `Capabilities.php` (180 lines), `PermissionService.php` (104 lines) |
| Permission Manager UI | ✅ | `PermissionManagerController.php` — admin page at CMS → Permissions |
| PSR-4 autoloading | ✅ | Autoloader handles `Ah\Cms\` namespace → `src/` automatically |
| Namespace ordering fix | ✅ | 147 files fixed (104 plugin + 43 theme) — namespace before `defined()` |
| WP function prefixes | ✅ | All WP functions (add_action, etc.) prefixed with `\` in namespaced files |
| WorkflowRestController | ✅ | Created REST API controller for workflow rules |
| WorkflowAdminController | ✅ | Created admin controller for workflow management |
| VisitorPingRestController | ✅ | Created REST API controller for visitor ping |
| Fixed duplicate functions | ✅ | Removed duplicates from common_functions.php |
| Fixed namespace issues | ✅ | Added require_once for theme helpers and controllers |
| Fixed ADN_Cache references | ✅ | Added \ prefix for global class in namespaced files |
| Added missing functions | ✅ | Created calculator-merge.php, loaded intermediate files |
| Fixed Feature files | ✅ | Added \ prefix to all Feature files (11 files) |
| Created AssetLoader | ✅ | Centralized CSS/JS loading in src/Service/AssetLoader.php |
| Removed hardcoded wp_enqueue | ✅ | Removed from 10 page templates (now handled by AssetLoader) |
| Added get_header() to pages | ✅ | Fixed 7 pages missing get_header() (caused CSS/JS not loading) |
| Created NavigationAdminController | ✅ | Admin controller for navigation management |
| Created BannersAdminController | ✅ | Admin controller for banners management |
| Created adn_add_img_lazy_attr | ✅ | Lazy loading filter function for images |
| ARCHITECTURAL_REVIEW.md | ✅ | 32 sections, all numbered correctly |

---

## Code Migration Status (Verified 2026-07-23T05:35)

| Step | What Was Migrated | Status |
|------|------------------|--------|
| M1 | ah-cms.php → Thin bootstrap (628→109 lines) | **COMPLETED** ✅ |
| M2 | Shortcodes → AH_RelatedLinks_Shortcode, AH_StaticPage_Shortcode, AH_Resource_Shortcode, AH_Resources_Shortcode | **COMPLETED** ✅ |
| M3 | Redirect logic → AH_Redirect_Service | **COMPLETED** ✅ |
| M4 | Custom Code logic → AH_Custom_Code_Service (injection + AJAX) | **COMPLETED** ✅ |
| M5 | Builder Page routing → AH_Builder_Page_Service | **COMPLETED** ✅ |
| M6 | Analytics REST → AH_Analytics_Rest_Controller | **COMPLETED** ✅ |
| M7 | Autoloader updated with all new classes | **COMPLETED** ✅ — Added PluginBootstrap, Capabilities, PermissionService, PermissionManagerController |
| M8 | Theme procedural services → Service classes (SiteChromeService, CmsDataService, SeoService) | **PARTIAL** ⚠️ — 3 services created, but `apis/services.php` (665 lines) and `apis/services_cms.php` (810 lines) still exist as procedural files |
| M9 | Theme intermediate files → Feature controllers | **COMPLETED** ✅ — 12 controllers, 13 page templates updated |
| M10 | Theme common_functions.php → Helper classes | **PARTIAL** ⚠️ — 6 helpers created (StringHelper added), `common_functions.php` still exists (926 lines) |

---

## Phase 1: Foundation — Verified Step-by-Step

### 1.1 Remove 8 dead/empty files
- [x] `cms-plugin/constants.php` — Removed ✅
- [x] `cms-plugin/helper/common.php` — Removed ✅
- [x] `cms-plugin/helper/functions/includes.php` — Removed ✅
- [x] `cms-plugin/helper/functions/routes.php` — Removed ✅
- [x] `cms-plugin/helper/functions/api_resources.php` — Removed ✅
- [x] `advaithhomes_new/Screenshot.png` (duplicate) — Single copy retained ✅
- [x] `advaithhomes_new/pages/page-newspage.php` — Removed ✅
- [x] `advaithhomes_new/pages/page-posts_topic_category_guide.php` — Removed ✅
- [x] `advaithhomes_new/components/parts/page_header.php` — Removed ✅

### 1.2 Fix version mismatch
- [x] `ah-cms.php` — Header and constant both aligned to `1.3.1` ✅

### 1.3 Fix 5 missing autoloader entries
- [x] `AH_Term_Manager` → `inc/term-manager.php` ✅
- [x] `AH_Events_Model` → `models/class-events-model.php` ✅
- [x] `AH_Home_Banners_Model` → `models/class-home-banner-model.php` ✅
- [x] `AH_Features_In_Model` → `models/class-features-in-model.php` ✅
- [x] `AH_Newsletters_Model` → `models/class-newsletter-model.php` ✅

### 1.4 Split two-classes-per-file
- [x] `models/class-spotlights-model.php` → Split ✅ (only `AH_Spotlights_Model` remains)
- [x] `models/class-analytics-model.php` → Split ✅

### 1.5 Rename `explode_function.php`
- [x] Renamed (file no longer exists in either location) ✅

### 1.6 Rename `Term_Manager` class
- [x] Class renamed to `AH_Term_Manager` ✅
- [ ] **ISSUE**: `models/class-related-links-model.php` still references old `Term_Manager` name (lines 69, 70, 96, 97) — needs update

### 1.7 Add Composer with PSR-4 autoloading
- [x] Plugin `composer.json` — PSR-4 `Ah\Cms\` → `src/` + classmap ✅
- [x] Theme `composer.json` — PSR-4 `Adn\Theme\` → `src/` ✅
- [x] Both `ah-cms.php` and `functions.php` load Composer autoloader ✅

### 1.8 Add PHPCS configuration
- [x] `.phpcs.xml` — WordPress Coding Standards + PSR-12 ✅
- [x] `phpunit.xml` — Test suites ✅

---

## Phase 2: Feature Module Structure — Verified

### Plugin
- [x] `src/` directory with all 10 subdirectories ✅
- [x] 28 feature modules in `src/Feature/` ✅
- [x] `src/Repository/AbstractRepository.php` ✅
- [x] `src/Database/Connection.php` ✅
- [ ] **MISSING**: `src/Bootstrap/` is EMPTY — `PluginBootstrap.php` does not exist

### Theme
- [x] `src/` directory with all 10 subdirectories ✅
- [x] 11 feature modules in `src/Feature/` ✅
- [x] `src/Helper/` — 5 of 6 helpers present ✅
- [ ] **MISSING**: `src/Helper/StringHelper.php`
- [x] `src/Service/` — SiteChromeService, CmsDataService, SeoService ✅
- [x] `src/Admin/Tab/` — 10 tab controller classes ✅
- [ ] **MISSING**: `src/Bridge/DataAggregator.php` (only ConfigResolver + PlaceholderResolver exist)
- [ ] `src/Bootstrap/` is EMPTY — no `ThemeBootstrap.php`

---

## Phase 7: Theme Feature Modules — Verified

### Controllers Created ✅
| Feature | Controller | Type |
|---------|-----------|------|
| Home | `src/Feature/Home/Controller/HomeController.php` | Full implementation |
| Contact | `src/Feature/Contact/Controller/ContactController.php` | Full implementation |
| Guidance | `src/Feature/Guidance/Controller/GuidanceController.php` | Full implementation |
| News | `src/Feature/News/Controller/NewsController.php` | Full implementation |
| Tools | `src/Feature/Tools/Controller/ToolsController.php` | Full implementation |
| AskExpert | `src/Feature/AskExpert/Controller/AskExpertController.php` | Full implementation |
| CategoryGuide | `src/Feature/CategoryGuide/Controller/CategoryGuideController.php` | Delegates to intermediate |
| GuidesListing | `src/Feature/GuidesListing/Controller/GuidesListingController.php` | Delegates to intermediate |
| GuidesHub | `src/Feature/GuesListing/Controller/GuidesHubController.php` | Delegates to intermediate |
| ToolSingle | `src/Feature/Tools/Controller/ToolSingleController.php` | Delegates to intermediate |
| ExpertSingle | `src/Feature/AskExpert/Controller/ExpertSingleController.php` | Delegates to intermediate |
| Article | `src/Feature/Article/Controller/ArticleController.php` | Delegates to intermediate |

### Page Templates Updated ✅
All 13 page templates call Feature Controllers:
- `page-home.php` → `HomeController::getContext()`
- `page-tools.php` → `ToolsController::getContext()`
- `page-contact.php` → `ContactController::getContext()`
- `page-guidance.php` → `GuidanceController::getContext()`
- `page-newsall.php` → `NewsController::getContext()`
- `page-ask-expert.php` → `AskExpertController::getContext()`
- `page-expert-single.php` → `ExpertSingleController::getContext()`
- `page-tool-single.php` → `ToolSingleController::getContext()`
- `page-category_guide.php` → `CategoryGuideController::getContext()`
- `page-topic_category_guide.php` → `CategoryGuideController::getTopicContext()`
- `page-guides.php` → `GuidesHubController::getContext()`
- `page-guides_listing.php` → `GuidesListingController::getContext()`
- `single.php` → `ArticleController::getContext()`

---

## M10: Theme common_functions.php → Helper Classes — Verified

### Helper Classes Created ✅
| Class | Namespace | Status |
|-------|-----------|--------|
| `ComponentRenderer` | `Adn\Theme\Helper` | ✅ Created |
| `IconHelper` | `Adn\Theme\Helper` | ✅ Created |
| `UrlHelper` | `Adn\Theme\Helper` | ✅ Created |
| `LanguageHelper` | `Adn\Theme\Helper` | ✅ Created |
| `PageHelper` | `Adn\Theme\Helper` | ✅ Created |
| `StringHelper` | `Adn\Theme\Helper` | ❌ **MISSING** |

### Backward Compatibility
- [ ] `common/common_functions.php` still exists (926 lines) — should be thin wrappers only

---

## New Work: HookRegistrar + Permission System (2026-07-23) 

### HookRegistrar — Centralized Hook Registration ✅

All `add_action`, `add_filter`, `add_shortcode` calls moved to centralized files:

| File | Purpose |
|------|---------|
| `plugins/cms-plugin/src/Bootstrap/HookRegistrar.php` | ALL plugin hooks (44 actions, 1 filter) |
| `themes/advaithhomes_new/src/Bootstrap/HookRegistrar.php` | ALL theme hooks (52 actions, 8 filters, 2 shortcodes) |

**Plugin `ah-cms.php`:** Now 30 lines (was 109) — just constants + autoloader + `HookRegistrar::register()`

**Theme `functions.php`:** Still 816 lines (functions not yet moved to common/)

### Theme Common Functions — Organized Files ✅

Functions extracted from `functions.php` into organized files:

| File | Functions |
|------|-----------|
| `common/ajax/expert-ajax.php` | `adn_expert_full_page_render`, `adn_expert_contact_ajax`, `adn_expert_unlock_ajax` |
| `common/ajax/post-ajax.php` | `adn_post_related_articles_ajax`, `adn_post_helpful_ajax` |
| `common/ajax/comment-ajax.php` | `adn_moderate_comment_ajax`, `adn_ajax_submit_comment`, `adn_ajax_load_comments` |
| `common/frontend/site-notice.php` | `adn_render_site_notice_popup` |
| `common/frontend/floating-contact.php` | `adn_render_floating_contact` |
| `common/frontend/scroll-reveal.php` | `adn_reveal_gate`, `adn_reveal_runtime` |
| `common/frontend/coming-soon.php` | `adn_check_coming_soon` |
| `common/cache/theme-cache.php` | `adn_handle_cache_clear`, `adn_add_cache_clear_admin_bar` |
| `common/enqueue/asset-loader.php` | `adn_enqueue_common_css`, `adn_enqueue_common_js`, `adn_enqueue_template_specific_assets` |
| `common/database/theme-install.php` | `adn_create_default_pages`, `adn_flush_rewrite_rules_on_switch` |
| `common/filters/cache-busting.php` | `adn_cache_bust_attachment_url`, `adn_cache_bust_content_images` |

### Permission System ✅

Granular per-feature permissions (view/edit/delete) for all 27 features:

| File | Purpose |
|------|---------|
| `src/Config/Capabilities.php` | Defines 80+ capabilities (ah_pages_view, ah_reviews_edit, etc.) |
| `src/Support/PermissionService.php` | Checks permissions: `can()`, `canView()`, `canEdit()`, `canDelete()`, `enforce()` |
| `src/Feature/AdminTools/Controller/PermissionManagerController.php` | Admin UI for managing role permissions |

**Admin page:** CMS → Permissions — toggle capabilities per role

**Usage:**
```php
PermissionService::enforce( 'pages', 'edit' );  // Dies with 403 if denied
if ( PermissionService::can( 'reviews', 'view' ) ) { ... }
```

---

## Remaining Work (Priority Order)

### ✅ Completed This Session
1. ~~**Wire common/ files into `functions.php`**~~ — ✅ Done (72 lines)
2. ~~**Create `src/Bootstrap/PluginBootstrap.php`**~~ — ✅ Done
3. ~~**Create `src/Bootstrap/ThemeBootstrap.php`**~~ — ✅ Done
4. ~~**Create `src/Helper/StringHelper.php`**~~ — ✅ Done
5. ~~**Fix namespace declarations**~~ — ✅ 147 files fixed (namespace before defined)
6. ~~**Fix autoloader for new classes**~~ — ✅ PSR-4 for Ah\Cms\ namespace
7. ~~**Fix WP function prefixes**~~ — ✅ All add_action etc. prefixed with `\`
8. ~~**Create `src/Bridge/DataAggregator.php`**~~ — ✅ Core bridge component created
9. ~~**Create `src/Bridge/PluginDataSource.php`**~~ — ✅ Plugin data reader created
10. ~~**Create `src/Bridge/JsonDataSource.php`**~~ — ✅ JSON data reader created
11. ~~**Fix stale `Term_Manager` references**~~ — ✅ Updated to `AH_Term_Manager`

### ✅ Completed — Legacy File Decomposition
1. ~~**Decompose `common/common_functions.php`**~~ — ✅ Converted to thin wrappers delegating to OOP classes
2. ~~**Create `RequestHelper`**~~ — ✅ `src/Helper/RequestHelper.php` — request/input functions
3. ~~**Create `MediaHelper`**~~ — ✅ `src/Helper/MediaHelper.php` — media URL resolution
4. ~~**Create `SiteChromeService`**~~ — ✅ `src/Service/SiteChromeService.php` — site chrome data
5. ~~**Create `CmsDataService`**~~ — ✅ `src/Service/CmsDataService.php` — CMS database access

**Note:** `apis/services.php` (601 lines) and `apis/services_cms.php` (755 lines) remain as backward-compatible files. They still work and are called by existing code. The new OOP classes in `src/` provide the clean interface for new code.

---

## Notes

### User Instructions
- Don't change anything on the website while working — code changes only
- Complete phases one by one, no permission needed between phases
- Keep this progress file updated after each phase
- Update project memory so new chats can continue from where we left off
- JSON rebranding comes later (Phase 12) — no changes now

### Architecture Summary (Final — Cron Verified 2026-07-24T03:50)
- **Plugin**: 28 feature modules, thin bootstrap (37 lines), PSR-4 autoloading ✅
- **Theme**: 11 feature modules, 9 OOP helper/service classes, 3 Repository classes ✅
- **HookRegistrar**: Centralized hook registration for both plugin + theme ✅
- **AssetLoader**: Performance-optimized CSS/JS (core 11 CSS + 5 JS, conditional per page) ✅
- **Permission System**: 80+ capabilities, PermissionService, Admin UI ✅
- **Bridge Module**: DataAggregator, PluginDataSource, JsonDataSource ✅
- **OOP Classes**: RequestHelper, MediaHelper, SiteChromeService, CmsDataService, AssetLoader ✅
- **Workflow Manager**: 2118→308 lines — RuleEngine, ActionExecutor, ConditionEvaluator, WorkflowCron ✅
- **Admin Bootstrap**: 898→302 lines — NavigationAdmin, SpotlightsAdmin, SiteNoticesAdmin, BannersAdmin, PostsAdmin ✅
- **Repositories**: CategoryRepository, HomeRepository, TopicCategoryRepository — zero $wpdb in intermediates ✅
- **JSON Config**: 6 config JSON + 14 page JSON. SiteChromeService reads from JSON with DB overlay ✅
- **Page Templates**: All 14 pages have get_header() + get_footer() ✅
- **Site Status**: Live at advaithhomes.co.uk.test — all pages 200 ✅
