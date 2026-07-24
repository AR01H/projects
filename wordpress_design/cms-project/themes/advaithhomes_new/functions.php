<?php
/**
 * Theme Functions — Thin Bootstrap
 *
 * This file is intentionally minimal. All logic is organized into:
 * - src/Bootstrap/HookRegistrar.php — all WordPress hooks
 * - common/ — all function definitions (ajax, frontend, cache, filters, etc.)
 * - includes/ — core classes and helpers
 * - src/Feature/ — feature modules
 *
 * @package Adn\Theme
 */
defined( 'ABSPATH' ) || exit;

// ===========================
// THEME CONSTANTS
// ===========================
require_once get_template_directory() . '/includes/core_info.php';
require_once get_template_directory() . '/includes/core_terms.php';
require_once get_template_directory() . '/includes/core_settings.php';
require_once get_template_directory() . '/includes/rules_conditions.php';
require_once get_template_directory() . '/includes/core_routing.php';
require_once get_template_directory() . '/includes/CategorySettings.php';
require_once get_template_directory() . '/includes/CalculatorDb.php';
require_once get_template_directory() . '/includes/ExpertDb.php';
require_once get_template_directory() . '/includes/AdnEnquiry.php';
require_once get_template_directory() . '/includes/AdnFormAjax.php';
require_once get_template_directory() . '/includes/AdnSidebarHelpers.php';
require_once get_template_directory() . '/includes/CommentCallbacks.php';
require_once get_template_directory() . '/includes/seo.php';
require_once get_template_directory() . '/includes/AdnCache.php';

// ===========================
// LOAD HELPER FUNCTIONS
// ===========================
require_once ADN_THEME_DIR . '/ThemeHelpers.php';

// ===========================
// LOAD OOP CLASSES (for common_functions.php wrappers)
// ===========================
require_once ADN_THEME_DIR . '/src/Helper/RequestHelper.php';
require_once ADN_THEME_DIR . '/src/Helper/MediaHelper.php';
require_once ADN_THEME_DIR . '/src/Helper/ComponentRenderer.php';
require_once ADN_THEME_DIR . '/src/Helper/IconHelper.php';
require_once ADN_THEME_DIR . '/src/Helper/PageHelper.php';
require_once ADN_THEME_DIR . '/src/Helper/LanguageHelper.php';
require_once ADN_THEME_DIR . '/src/Helper/UrlHelper.php';
require_once ADN_THEME_DIR . '/src/Helper/StringHelper.php';
require_once ADN_THEME_DIR . '/src/Service/AssetLoader.php';
require_once ADN_THEME_DIR . '/src/Repository/CategoryRepository.php';
require_once ADN_THEME_DIR . '/src/Repository/HomeRepository.php';
require_once ADN_THEME_DIR . '/src/Repository/TopicCategoryRepository.php';

// ===========================
// LOAD SERVICE CONTEXT CLASSES (used by intermediate wrappers)
// ===========================
require_once ADN_THEME_DIR . '/src/Service/HomeContext.php';
require_once ADN_THEME_DIR . '/src/Service/CategoryContext.php';
require_once ADN_THEME_DIR . '/src/Service/TopicCategoryContext.php';
require_once ADN_THEME_DIR . '/src/Service/AskExpertContext.php';
require_once ADN_THEME_DIR . '/src/Service/ContactContext.php';
require_once ADN_THEME_DIR . '/src/Service/GuidesContext.php';
require_once ADN_THEME_DIR . '/src/Service/GuidesListingContext.php';
require_once ADN_THEME_DIR . '/src/Service/NewsContext.php';
require_once ADN_THEME_DIR . '/src/Service/ToolsContext.php';
require_once ADN_THEME_DIR . '/src/Service/ToolSingleContext.php';
require_once ADN_THEME_DIR . '/src/Service/ExpertSingleContext.php';
require_once ADN_THEME_DIR . '/src/Service/GuidanceContext.php';
require_once ADN_THEME_DIR . '/src/Service/GuideContext.php';
require_once ADN_THEME_DIR . '/src/Service/PostContext.php';

// ===========================
// LOAD FEATURE CONTROLLERS
// ===========================
require_once ADN_THEME_DIR . '/src/Feature/Home/Controller/HomeController.php';
require_once ADN_THEME_DIR . '/src/Feature/Contact/Controller/ContactController.php';
require_once ADN_THEME_DIR . '/src/Feature/Guidance/Controller/GuidanceController.php';
require_once ADN_THEME_DIR . '/src/Feature/News/Controller/NewsController.php';
require_once ADN_THEME_DIR . '/src/Feature/Tools/Controller/ToolsController.php';
require_once ADN_THEME_DIR . '/src/Feature/Tools/Controller/ToolSingleController.php';
require_once ADN_THEME_DIR . '/src/Feature/AskExpert/Controller/AskExpertController.php';
require_once ADN_THEME_DIR . '/src/Feature/AskExpert/Controller/ExpertSingleController.php';
require_once ADN_THEME_DIR . '/src/Feature/CategoryGuide/Controller/CategoryGuideController.php';
require_once ADN_THEME_DIR . '/src/Feature/GuidesListing/Controller/GuidesListingController.php';
require_once ADN_THEME_DIR . '/src/Feature/GuidesListing/Controller/GuidesHubController.php';
require_once ADN_THEME_DIR . '/src/Feature/Article/Controller/ArticleController.php';

// ===========================
// DATA LOADERS (csv / json / html / pdf)
// ===========================
require_once ADN_THEME_DIR . '/includes/data_fetcher/RealLoader.php';

// ===========================
// ADMIN (tabs + subtabs page)
// ===========================
if ( is_admin() ) {
    require_once ADN_THEME_DIR . '/admin/ThemeAdmin.php';
    ADN_Theme_Admin::init();
}

// ===========================
// COMMON FUNCTIONS (organized by concern)
// ===========================
require_once ADN_THEME_DIR . '/common/cache/ThemeCache.php';
require_once ADN_THEME_DIR . '/common/frontend/SiteNotice.php';
require_once ADN_THEME_DIR . '/common/frontend/FloatingContact.php';
require_once ADN_THEME_DIR . '/common/frontend/ScrollReveal.php';
require_once ADN_THEME_DIR . '/common/frontend/ComingSoon.php';
require_once ADN_THEME_DIR . '/common/ajax/ExpertAjax.php';
require_once ADN_THEME_DIR . '/common/ajax/PostAjax.php';
require_once ADN_THEME_DIR . '/common/ajax/CommentAjax.php';
require_once ADN_THEME_DIR . '/common/filters/CacheBusting.php';
require_once ADN_THEME_DIR . '/common/enqueue/AssetLoader.php';
require_once ADN_THEME_DIR . '/common/database/ThemeInstall.php';
require_once ADN_THEME_DIR . '/common/shortcodes/ThemeShortcodes.php';
require_once ADN_THEME_DIR . '/common/helpers/CalculatorMerge.php';
require_once ADN_THEME_DIR . '/intermediate/PageHomeLogical.php';
require_once ADN_THEME_DIR . '/intermediate/PostLogical.php';
require_once ADN_THEME_DIR . '/intermediate/PageCategoryLogical.php';
require_once ADN_THEME_DIR . '/intermediate/PageContactLogical.php';
require_once ADN_THEME_DIR . '/intermediate/PageExpertSingleLogical.php';
require_once ADN_THEME_DIR . '/intermediate/PageGuidanceLogical.php';
require_once ADN_THEME_DIR . '/intermediate/PageGuidesLogical.php';
require_once ADN_THEME_DIR . '/intermediate/PageGuidesListingLogical.php';
require_once ADN_THEME_DIR . '/intermediate/PageGuideLogical.php';
require_once ADN_THEME_DIR . '/intermediate/PageNewsLogical.php';
require_once ADN_THEME_DIR . '/intermediate/PageToolsLogical.php';
require_once ADN_THEME_DIR . '/intermediate/PageToolSingleLogical.php';
require_once ADN_THEME_DIR . '/intermediate/PageTopicCategoryLogical.php';
require_once ADN_THEME_DIR . '/intermediate/PageAskExpertLogical.php';

// ===========================
// ALL HOOKS (centralized)
// ===========================
// Every add_action, add_filter, add_shortcode lives in HookRegistrar.
// See: src/Bootstrap/HookRegistrar.php
require_once ADN_THEME_DIR . '/src/Bootstrap/HookRegistrar.php';
Adn\Theme\Bootstrap\HookRegistrar::register();
