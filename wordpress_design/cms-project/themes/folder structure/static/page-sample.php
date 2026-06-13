<?php
/**
 * static/page-sample.php - Static Constants & Defines
 *
 * RULE: All constants defined here - one file, one place.
 *       Everything lowercase with the npt_ prefix.
 *       Load this FIRST (before any module) from function.php.
 *
 * Usage anywhere in the theme:
 *   $ver  = NPT_VERSION;
 *   $slug = NPT_API_NS;
 */

defined( 'ABSPATH' ) || exit;

// ── Theme identity ────────────────────────────────────────────────
defined( 'NPT_VERSION'    ) || define( 'NPT_VERSION',    '1.0.0' );
defined( 'NPT_TEXTDOMAIN' ) || define( 'NPT_TEXTDOMAIN', 'npt' );
defined( 'NPT_THEME_DIR'  ) || define( 'NPT_THEME_DIR',  get_template_directory() );
defined( 'NPT_THEME_URI'  ) || define( 'NPT_THEME_URI',  get_template_directory_uri() );

// ── API / REST ────────────────────────────────────────────────────
defined( 'NPT_API_NS'         ) || define( 'NPT_API_NS',         'npt/v1' );
defined( 'NPT_API_RATE_LIMIT' ) || define( 'NPT_API_RATE_LIMIT', 60  );   // requests per window
defined( 'NPT_API_RATE_WINDOW') || define( 'NPT_API_RATE_WINDOW', 60  );   // seconds

// ── Pagination ────────────────────────────────────────────────────
defined( 'NPT_POSTS_PER_PAGE' ) || define( 'NPT_POSTS_PER_PAGE', 12 );

// ── Meta key names (always use these - never hardcode strings) ────
defined( 'NPT_META_REDIRECT'   ) || define( 'NPT_META_REDIRECT',   'npt_redirect_url' );
defined( 'NPT_META_ALT_URL'    ) || define( 'NPT_META_ALT_URL',    'npt_alternate_url' );
defined( 'NPT_META_ALT_TITLE'  ) || define( 'NPT_META_ALT_TITLE',  'npt_alternate_title' );
defined( 'NPT_META_ALT_SLUG'   ) || define( 'NPT_META_ALT_SLUG',   'npt_alternate_slug' );
defined( 'NPT_META_CANONICAL'  ) || define( 'NPT_META_CANONICAL',  'npt_canonical_url' );
defined( 'NPT_META_CLIENT'     ) || define( 'NPT_META_CLIENT',     'npt_client_name' );
defined( 'NPT_META_YEAR'       ) || define( 'NPT_META_YEAR',       'npt_project_year' );

// ── Option keys (wp_options table) ────────────────────────────────
defined( 'NPT_OPT_EXAMPLE'     ) || define( 'NPT_OPT_EXAMPLE',    'npt_example_option' );

// ── Taxonomy slugs ────────────────────────────────────────────────
defined( 'NPT_TAX_PORTFOLIO'   ) || define( 'NPT_TAX_PORTFOLIO',  'portfolio-category' );

// ── Image sizes (must match add_image_size() calls) ──────────────
defined( 'NPT_IMG_CARD'        ) || define( 'NPT_IMG_CARD',       'npt-card' );   // 600×400
defined( 'NPT_IMG_HERO'        ) || define( 'NPT_IMG_HERO',       'npt-hero' );   // 1920×800

// ── AJAX action names ─────────────────────────────────────────────
defined( 'NPT_AJAX_LOAD_MORE'  ) || define( 'NPT_AJAX_LOAD_MORE', 'npt_load_more' );
defined( 'NPT_AJAX_CONTACT'    ) || define( 'NPT_AJAX_CONTACT',   'npt_submit_form' );

// ── Transient / cache key prefixes ───────────────────────────────
defined( 'NPT_CACHE_PREFIX'    ) || define( 'NPT_CACHE_PREFIX',   'npt_cache_' );
defined( 'NPT_CACHE_TTL'       ) || define( 'NPT_CACHE_TTL',      3600 );         // 1 hour in seconds
