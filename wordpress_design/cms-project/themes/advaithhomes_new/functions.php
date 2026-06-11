<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Block direct file access.
}

// ===========================
// THEME CONSTANTS
// ===========================
require_once get_template_directory() . '/includes/core_terms.php';
require_once get_template_directory() . '/includes/core_settings.php';
require_once get_template_directory() . '/includes/core_info.php';
require_once get_template_directory() . '/includes/rules_conditions.php';

// ===========================
// LOAD HELPER FUNCTIONS
// ===========================
require_once ADN_THEME_DIR . '/explode_function.php';

// ===========================
// DATA LOADERS (csv / json / html / pdf)
// ===========================
require_once ADN_THEME_DIR . '/includes/data_fetcher/class-real-loader.php';

// ===========================
// ADMIN (tabs + subtabs page)
// ===========================
if ( is_admin() ) {
    require_once ADN_THEME_DIR . '/admin/class-theme-admin.php';
    ADN_Theme_Admin::init();
}

// ===========================
// HOOKS
// ===========================
add_action( 'after_setup_theme', 'ahn_include_files' );
add_action( 'after_setup_theme', 'adn_theme_register' );

// Create default pages only once, when the theme is activated, instead of on every admin load.
add_action( 'after_switch_theme', 'adn_create_default_pages' );

// Persist the language choice early, before any template output starts.
add_action( 'init', 'adn_set_language_cookie' );

add_action( 'wp_enqueue_scripts', 'adn_enqueue_common_css' );
add_action( 'wp_enqueue_scripts', 'adn_enqueue_common_js' );
add_action( 'wp_enqueue_scripts', 'adn_enqueue_template_specific_assets' );
add_action( 'template_redirect', 'adn_check_coming_soon' );
