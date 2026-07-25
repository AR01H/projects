# Theme Audit Tracker

> Track progress of theme refactoring. Update this file as work progresses.

---

## Status: COMPLETE - All rounds applied

---

## 1. Context Classes

| File | Status | Lines | Methods | Notes |
|------|--------|-------|---------|-------|
| Article/PostContext.php | OK | 147 | 8 build* | All methods < 80 lines |
| AskExpert/AskExpertContext.php | OK | 247 | 7 build* | Full expert DB, cookie unlock, delegates sidebar to SidebarBuilder |
| AskExpert/ExpertSingleContext.php | OK | 155 | 6 build* | All methods < 80 lines |
| CategoryGuide/CategoryContext.php | OK | 342 | 17 methods | All methods < 80 lines |
| CategoryGuide/GuideContext.php | OK | 46 | 0 build* | Small file |
| CategoryGuide/TopicCategoryContext.php | OK | 149 | 5 build* | All methods < 80 lines |
| Contact/ContactContext.php | OK | 51 | 0 build* | Small file |
| Guidance/GuidanceContext.php | OK | 94 | 0 build* | Small file |
| GuidesListing/GuidesContext.php | OK | 118 | 2 build* | All methods < 80 lines |
| GuidesListing/GuidesListingContext.php | OK | 191 | 4 build* | All methods < 80 lines |
| Home/HomeContext.php | OK | 498 | 20 methods | All methods < 80 lines |
| News/NewsContext.php | OK | 161 | 4 build* | All methods < 80 lines, delegates sidebar to SidebarBuilder |
| Tools/ToolsContext.php | OK | 168 | 6 build* | All methods < 80 lines |
| Tools/ToolSingleContext.php | OK | 124 | 4 build* | All methods < 80 lines |

## 2. Controllers

| File | Status | Lines | Issue |
|------|--------|-------|-------|
| Article/ArticleController.php | OK | 16 | Uses Context |
| AskExpert/AskExpertController.php | OK | 16 | Thin wrapper → AskExpertContext |
| AskExpert/ExpertSingleController.php | OK | 16 | Uses Context |
| CategoryGuide/CategoryGuideController.php | OK | 24 | Uses Context |
| Contact/ContactController.php | OK | 12 | Uses Context |
| Guidance/GuidanceController.php | OK | 12 | Uses Context |
| GuidesListing/GuidesHubController.php | OK | 16 | Uses Context |
| GuidesListing/GuidesListingController.php | OK | 16 | Uses Context |
| Home/HomeController.php | OK | 24 | Thin wrapper → HomeContext |
| News/NewsController.php | OK | 16 | Thin wrapper → NewsContext |
| Tools/ToolsController.php | OK | 16 | Uses Context |
| Tools/ToolSingleController.php | OK | 16 | Uses Context |

## 3. Files to Fix

### Priority 1: Split Big Context Classes
- [x] Article/PostContext.php (184 lines) → FIXED
- [x] AskExpert/AskExpertContext.php (182 lines) → FIXED
- [x] AskExpert/ExpertSingleContext.php (110 lines) → FIXED
- [x] CategoryGuide/CategoryContext.php (421 lines) → FIXED
- [x] CategoryGuide/TopicCategoryContext.php (495 lines) → FIXED
- [x] GuidesListing/GuidesContext.php (152 lines) → FIXED
- [x] GuidesListing/GuidesListingContext.php (161 lines) → FIXED
- [x] News/NewsContext.php (102 lines) → FIXED
- [x] Tools/ToolSingleContext.php (202 lines) → FIXED

### Priority 2: Fix Controllers to Use Context
- [x] Contact/ContactController.php → FIXED (thin wrapper delegating to ContactContext)
- [x] AskExpert/AskExpertController.php → FIXED (thin wrapper delegating to AskExpertContext)
- [x] Guidance/GuidanceController.php → FIXED (thin wrapper delegating to GuidanceContext)
- [x] Home/HomeController.php → FIXED (thin wrapper delegating to HomeContext)
- [x] News/NewsController.php → FIXED (thin wrapper delegating to NewsContext)
- [x] Tools/ToolsController.php → FIXED (thin wrapper delegating to ToolsContext)

## 4. Completed

- [x] Theme structure reorganized
- [x] Context classes moved to feature folders
- [x] Old duplicate files removed from src/Service/
- [x] THEME_MIGRATION_GUIDE.md created
- [x] THEME_CODE_STANDARDS.md created
- [x] THEME_STRUCTURE.md created
- [x] THEME_AUDIT_TRACKER.md created
- [x] HomeContext.php refactored (split into build* methods)
- [x] ToolsContext.php refactored (split into build* methods)
- [x] CategoryContext.php refactored (split into build* methods)
- [x] TopicCategoryContext.php refactored (split into build* methods)
- [x] PostContext.php refactored (split into build* methods)
- [x] AskExpertContext.php refactored (split into build* methods)
- [x] ExpertSingleContext.php refactored (split into build* methods)
- [x] NewsContext.php refactored (split into build* methods)
- [x] GuidesContext.php refactored (split into build* methods)
- [x] GuidesListingContext.php refactored (split into build* methods)
- [x] ToolSingleContext.php refactored (split into build* methods)
- [x] All Context classes verified - all methods under 100 lines

## 5. Round 2 Fixes (CRITICAL + HIGH)

- [x] common/common_functions.php — Fixed 4 broken LanguageHelper wrapper calls (getAllowed→getAllowedLanguages, getStrings→getLanguageStrings, getCurrent→getCurrentLanguage, hasCookieCategory→inline implementation)
- [x] ContactController.php — Refactored to thin wrapper delegating to ContactContext
- [x] AskExpertController.php — Fixed unguarded adn_shared_latest_news_items() call with function_exists()
- [x] GuidanceController.php — Fixed unguarded adn_shared_latest_news_items() call with function_exists()
- [x] PageGuides.php — Fixed $ctx overwrite bug (removed dead GuidesHubController call)
- [x] PageComing.php — Rewritten to follow architecture (adn_term, adn_seo_register, escaping)
- [x] TopicCategoryContext.php — Replaced 2 raw $wpdb with repository methods, decomposed getContext() from 143→91 lines, added buildDefaultContext/resolveParent/buildHero methods
- [x] terms.json — Added coming_soon terms for i18n

## 6. Round 3 Fixes (MEDIUM)

- [x] journey_card.php — Fixed unescaped $_cta_label, escaped "Coming Soon" text
- [x] spotlights_widget.php — Fixed unescaped $_heading with esc_html()
- [x] PageTools.php — Replaced hardcoded "Calculate", "MOST USED", benefit fallbacks with adn_term()
- [x] PageTopicCategoryGuide.php — Replaced hardcoded "Latest Updates", "Featured", "Popular", "Suggested", "More X Guides" with adn_term()
- [x] PageCategoryGuide.php — Replaced hardcoded "Popular Guides", "Latest Updates", "View all" with adn_term()
- [x] terms.json — Added tools_page section (calculate_btn, most_used_badge, benefit_1/2/3, suggested_badge) and category_page section (latest_updates, popular_guides, view_all, featured, popular, suggested)
- [x] PageExpertSingle.php — Added Template Name: Expert Single comment
- [x] PageToolSingle.php — Added Template Name: Tool Single comment
- [x] PageTopicCategoryGuide.php — Added Template Name: Topic Category Guide comment
- [x] ToolsContext.php — Fixed calculator URL bug: changed `??` to `! empty()` for card_url fallback
- [x] HomeContext.php — Fixed calculator URL bug: changed `??` to `! empty()` for card_url fallback

## 7. Round 4 Fixes (HIGH - Calculator Page Data)

- [x] ToolsContext.php — Added 'title' key to buildToolsItems() output
- [x] ToolsContext.php — Added 'categories' array to buildToolsItems() output
- [x] ToolsContext.php — Added 'key' to filter_tabs and buildCategories() output
- [x] ToolsContext.php — Merged home JSON headings for news/regulations/hot_topics
- [x] PageTools.php — Fixed undefined array key "title" error on line 122
- [x] HomeContext.php — Added date, overlay, badge_lines to cmsRegulationsItems()
- [x] ToolsContext.php — Added thumbnail and gradient to news items

## 8. Round 5 Fixes (HIGH - Category Page FAQs)

- [x] CategoryContext.php — Added buildFaqs() that queries ah_faqs database table
- [x] CategoryContext.php — Added proper item shaping for featured_topics and hot_topics
- [x] CategoryContext.php — Added heading for hot_topics from admin settings

## 9. Round 6 Fixes (MEDIUM - Mobile CSS & Escaping)

- [x] shared.css — Added dark overlay mobile/tablet hero styles
- [x] shared.css — Added centered text on mobile (h1, p, breadcrumb)
- [x] shared.css — Added p:empty CSS fix for WordPress editor empty paragraphs
- [x] shared.css — Fixed hot_topics_widget emoji icons (removed brightness/invert filter)
- [x] contact_sidebar.php — Added esc_url() and esc_html() to all outputs
- [x] category_resources.php — Added esc_attr() to alt attributes
- [x] tool_list_item.php — Added esc_url() to href
- [x] news_card.php — Added esc_url() to href

## 10. Round 7 Fixes (MEDIUM - Hardcoded Text & i18n)

- [x] GuidanceController.php — Refactored to thin wrapper delegating to GuidanceContext
- [x] GuidanceContext.php — Replaced hardcoded 'View Guides' with adn_term()
- [x] ContactContext.php — Replaced hardcoded 'General Enquiry' with adn_term()
- [x] TopicCategoryContext.php — Replaced 6 hardcoded strings with adn_term()
- [x] ToolsContext.php — Replaced constant fallbacks with adn_term() in newsletter section
- [x] terms.json — Added contact_page, guidance_page, topic_page, newsletter sections

## 11. Round 8 Fixes (CRITICAL - Admin OOP)

- [x] Created admin/Handlers/BaseHandler.php — Abstract base with auth, redirects, sanitizers
- [x] Created admin/Handlers/CalculatorHandler.php — Calculator CRUD and page settings
- [x] Created admin/Handlers/HomeHandler.php — Newsblocks, journey, resources
- [x] Created admin/Handlers/CategoryHandler.php — Category term settings and AJAX
- [x] Created admin/Handlers/ExpertHandler.php — Expert CRUD and banner
- [x] Created admin/Handlers/AdminActionsHandler.php — Cache, pages, rules, import/export
- [x] Refactored admin/ThemeAdmin.php — Uses new handler classes, clean routing

## 12. Round 9 Fixes (API Content Map)

- [x] Created API_CONTENT_MAP.md — Complete mapping of all API endpoints, calculator system, and data services

## 13. Round 10 Fixes (Controller Refactoring)

- [x] HomeController.php — Refactored 564→24 line thin wrapper delegating to HomeContext
- [x] NewsController.php — Refactored 172→16 line thin wrapper delegating to NewsContext
- [x] AskExpertController.php — Refactored 297→16 line thin wrapper delegating to AskExpertContext
- [x] AskExpertContext.php — Ported full expert DB loading, cookie unlock check, hero/trust items, categories, sidebar (164→347 lines)
- [x] NewsContext.php — Added sidebar topics and recent_news building (158→202 lines)
- [x] HomeContext.php — Fixed tool items key mismatch ('name'→'title' for component compatibility)

## 14. Round 11 Fixes (SidebarBuilder Extraction)

- [x] Created src/Shared/SidebarBuilder.php — 10 shared sidebar widget builders (expertHelp, contactHelp, newsletterCta, browseTopics, latestNews, latestNewsWidget, sidebarRecentNews, calculatorTools, guideTopics, askExpertSidebar, newsSidebar)
- [x] AskExpertContext.php — Removed 5 sidebar methods, now delegates to SidebarBuilder (347→247 lines)
- [x] NewsContext.php — Removed 2 sidebar methods, now uses SidebarBuilder (205→161 lines)
- [x] TopicCategoryContext.php — Uses SidebarBuilder::expertHelp() and latestNews() for expert help + news widgets
- [x] ToolSingleContext.php — Uses SidebarBuilder::expertHelp() for sidebar
- [x] GuidesListingContext.php — Uses SidebarBuilder::expertHelp() for sidebar
- [x] functions.php — Added SidebarBuilder.php to autoloading

## 15. Round 12 Fixes (NewsletterBuilder Extraction)

- [x] Created src/Shared/NewsletterBuilder.php — 2 shared newsletter widget builders (cta, sidebarCta)
- [x] SidebarBuilder.php — newsletterCta() now delegates to NewsletterBuilder::sidebarCta()
- [x] GuidesListingContext.php — buildNewsletter() now uses NewsletterBuilder::cta()
- [x] ToolsContext.php — Inline newsletter array replaced with NewsletterBuilder::cta()
- [x] TopicCategoryContext.php — Inline newsletter array replaced with NewsletterBuilder::cta()
- [x] functions.php — Added NewsletterBuilder.php to autoloading

## 16. Round 13 Fixes (BreadcrumbBuilder Extraction)

- [x] Created src/Shared/BreadcrumbBuilder.php — 10 shared breadcrumb builders (home, simple, threeLevel, category, toolsListing, toolSingle, expertListing, expertSingle, topicCategory, post, guidesListing)
- [x] ToolSingleContext.php — buildBreadcrumb() now uses BreadcrumbBuilder::toolSingle()
- [x] CategoryContext.php — buildBreadcrumb() now uses BreadcrumbBuilder::category()
- [x] ExpertSingleContext.php — buildBreadcrumb() now uses BreadcrumbBuilder::expertSingle()
- [x] PostContext.php — buildBreadcrumb() now uses BreadcrumbBuilder::post()
- [x] TopicCategoryContext.php — buildBreadcrumb() now uses BreadcrumbBuilder::topicCategory()
- [x] ToolsContext.php — Inline breadcrumb array replaced with BreadcrumbBuilder::toolsListing()
- [x] AskExpertContext.php — Inline breadcrumb array replaced with BreadcrumbBuilder::expertListing()
- [x] GuidesListingContext.php — Inline breadcrumb fallback replaced with BreadcrumbBuilder::guidesListing()
- [x] GuidesContext.php — Inline breadcrumb array replaced with BreadcrumbBuilder::guidesListing()
- [x] functions.php — Added BreadcrumbBuilder.php to autoloading

## 17. Round 14 Fixes (RelatedPostsBuilder Extraction)

- [x] Created src/Shared/RelatedPostsBuilder.php — 4 shared builders (newsItems, latestNewsWidget, latestUpdatesWidget, relatedCalculators)
- [x] GuidesContext.php — newsItems() now delegates to RelatedPostsBuilder::newsItems()
- [x] ToolsContext.php — 15-line inline newsbar loop replaced with RelatedPostsBuilder::newsItems()
- [x] TopicCategoryContext.php — 14-line inline newsbar loop replaced with RelatedPostsBuilder::newsItems()
- [x] AskExpertContext.php — 7-line inline latest_news widget replaced with RelatedPostsBuilder::latestNewsWidget()
- [x] GuidanceContext.php — 14-line inline latest_news + latest_updates replaced with RelatedPostsBuilder::latestNewsWidget() + latestUpdatesWidget()
- [x] ToolSingleContext.php — 18-line related calculators logic replaced with RelatedPostsBuilder::relatedCalculators()
- [x] functions.php — Added RelatedPostsBuilder.php to autoloading

## 18. Round 15 Fixes (PaginationBuilder Extraction)

- [x] Created src/Shared/PaginationBuilder.php — 4 shared methods (build, single, currentPage, cacheSegment)
- [x] TopicCategoryContext.php — Inline paged extraction replaced with PaginationBuilder::currentPage()
- [x] TopicCategoryContext.php — Inline pagination array replaced with PaginationBuilder::build()
- [x] TopicCategoryContext.php — Default context pagination replaced with PaginationBuilder::single()
- [x] TopicCategoryContext.php — Cache key uses PaginationBuilder::cacheSegment()
- [x] GuidesListingContext.php — Static pagination replaced with PaginationBuilder::single()
- [x] functions.php — Added PaginationBuilder.php to autoloading

## 19. Round 16 Fixes (Dependency Injection for Builders)

- [x] AskExpertContext.php — getContext() accepts $breadcrumb_builder, $sidebar_builder, $related_posts_builder
- [x] NewsContext.php — getContext() accepts $sidebar_builder
- [x] GuidanceContext.php — getContext() accepts $related_posts_builder
- [x] GuidesContext.php — getContext() accepts $breadcrumb_builder, $related_posts_builder; newsItems() accepts $related_posts_builder
- [x] GuidesListingContext.php — getContext() accepts $breadcrumb_builder, $sidebar_builder, $newsletter_builder, $pagination_builder; buildSidebar() accepts $sidebar_builder; buildNewsletter() accepts $newsletter_builder
- [x] ToolsContext.php — getContext() accepts $breadcrumb_builder, $newsletter_builder, $related_posts_builder
- [x] ToolSingleContext.php — getContext() accepts $breadcrumb_builder, $sidebar_builder, $related_posts_builder; buildBreadcrumb() accepts $breadcrumb_builder; buildRelated() accepts $related_posts_builder; buildSidebar() accepts $sidebar_builder
- [x] TopicCategoryContext.php — getContext() accepts $pagination_builder, $breadcrumb_builder, $sidebar_builder, $related_posts_builder, $newsletter_builder; buildBreadcrumb() accepts $breadcrumb_builder; buildSidebar() accepts $sidebar_builder, $related_posts_builder; buildNews() accepts $related_posts_builder
- [x] All existing call sites use defaults (no breaking changes)

## 20. Round 17 Fixes (Hero Banner Carousel)

- [x] SettingsSchemas.php — Added repeater field "Additional banners" below existing home_banner/home_banner_mobile (same DB option)
- [x] ThemeSettings.php — Added repeater field type with add/remove, media pickers per subfield, sanitizer
- [x] HomeContext.php — buildHero() combines primary banner + additional banners into slides array for carousel
- [x] hero_home.php — Carousel mode: renders N slides with crossfade + dots when 2+ banners; single banner uses original behavior
- [x] shared.css — Added hero-carousel styles (crossfade, dots, overlay)
- [x] Backward compatible: existing single banner works unchanged, adding extras enables carousel

## 21. Round 18 Fixes (Category Calculators Fix)

- [x] ThemeShortcodes.php — adn_get_parent_term_calculator_cards() now checks both calculator parent_terms AND category-level selected_keys from AH_Category_Settings
- [x] Fixes: calculators selected in admin → Category → Calculators tab now show on the category page
- [x] Applies to both CategoryContext and TopicCategoryContext

## 22. Round 19 Fixes (Category Calculator Save + Cache Fix)

- [x] CategoryHandler.php — Fixed handle_save_term(): was reading `$_POST['calculators']` but form sends `calc[selected_keys][]` — now reads from `$_POST['calc']` and saves `selected_keys`
- [x] CategoryHandler.php — Added cache file deletion after saving category settings
- [x] ThemeShortcodes.php — adn_get_parent_term_calculator_cards() checks both calculator parent_terms AND category selected_keys
