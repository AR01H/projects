<?php
/**
 * config/theme.php - Theme identity + feature flags.
 *
 * The ONLY config file that defines constants (everything else returns arrays).
 * Loaded first by core/bootstrap.php, so every other file can rely on these.
 *
 * To rebrand this template for a new site:
 *   1. Change the values below.
 *   2. Find/replace the function prefix:  nt_  -> yourprefix_
 *      and the constant prefix:           NT_  -> YOURPREFIX_
 *      (see ARCHITECTURE.md, "Renaming the prefix").
 */

defined( 'ABSPATH' ) || exit;

// ---------------------------------------------------------------------------
// Core paths / identity (do not edit - derived automatically).
// ---------------------------------------------------------------------------
define( 'NT_THEME_DIR',     get_template_directory() );
define( 'NT_THEME_URI',     get_template_directory_uri() );
define( 'NT_THEME_VERSION', wp_get_theme()->get( 'Version' ) ?: '1.0.0' );
define( 'NT_TEXT_DOMAIN',   'new-theme' );

// ---------------------------------------------------------------------------
// Brand / company info (the old core_info.php - edit per site).
// ---------------------------------------------------------------------------
define( 'NT_BRAND_NAME',  'New Theme' );
define( 'NT_BRAND_PHONE', '+91 00000 00000' );
define( 'NT_BRAND_EMAIL', 'hello@example.com' );

// ---------------------------------------------------------------------------
// Global content term levels (the old core_terms.php).
// The 3-level content hierarchy used across templates, admin labels and
// queries. nt_term_label() / nt_terms_tree() (admin/includes/terms.php) and
// the demo tree in admin/data/terms.json build on these.
// ---------------------------------------------------------------------------
define( 'NT_TERM_PARENT',  'Guide' );     // level 1, e.g. Guide / Topic / Service
define( 'NT_TERM_SECTION', 'Category' );  // level 2
define( 'NT_TERM_CONTENT', 'Article' );   // level 3

// ---------------------------------------------------------------------------
// Feature flags (the old core_settings.php).
// ---------------------------------------------------------------------------

// When true, all visitors (except admins) are redirected to the coming-soon
// page. The page itself is defined in config/pages.php under this slug.
define( 'NT_COMING_SOON',      false );
define( 'NT_COMING_SOON_SLUG', 'coming-soon' );
