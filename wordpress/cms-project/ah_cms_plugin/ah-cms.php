<?php
/**
 * Plugin Name:  AH CMS
 * Description:  CMS engine — admin portal, database, models, helpers, and form builder.
 *               Install as a plugin and pair with any frontend theme that reads wp_ah_* tables.
 * Version:      1.0.0
 * Author:       Akhilesh Ravuri
 * Text Domain:  ah-theme
 */
defined( 'ABSPATH' ) || exit;

// ── Constants ────────────────────────────────────────────────────────────────
define( 'AH_PLUGIN_VERSION', '1.0.0' );
define( 'AH_DB_VERSION_KEY', 'ah_cms_db_version' );

// plugin_dir_path() has a trailing slash; strip it so paths match the existing
// AH_THEME_DIR convention (no trailing slash) — autoloader adds its own slash.
define( 'AH_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'AH_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

// Backward-compat aliases: every model, admin page, helper, and importer that
// already uses AH_THEME_DIR / AH_THEME_URL continues to work unchanged.
define( 'AH_THEME_DIR',     AH_PLUGIN_DIR );
define( 'AH_THEME_URL',     AH_PLUGIN_URL );
define( 'AH_THEME_VERSION', AH_PLUGIN_VERSION );

// ── Autoloader ───────────────────────────────────────────────────────────────
require_once AH_PLUGIN_DIR . '/inc/class-autoloader.php';
AH_Autoloader::register();

// ── Admin portal ─────────────────────────────────────────────────────────────
if ( is_admin() ) {
	AH_Admin_Bootstrap::init();
}

// ── Public AJAX (form builder frontend — works for logged-in and guests) ─────
AH_Ajax_Handlers::init_public();

// ── Shortcode [ah_form id="N"] ───────────────────────────────────────────────
add_action( 'init', static function () {
	add_shortcode( 'ah_form', array( 'AH_Form_Builder', 'render' ) );
} );

// ── ahTheme JS object (needed by form-builder shortcode frontend JS) ─────────
// The active theme's functions.php should enqueue its own scripts; this only
// localizes the object so fetch() in the shortcode can reach admin-ajax.php.
add_action( 'wp_enqueue_scripts', static function () {
	wp_localize_script( 'jquery', 'ahTheme', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'ah_frontend_nonce' ),
	) );
} );

// ── Database ─────────────────────────────────────────────────────────────────
register_activation_hook( __FILE__, array( 'AH_DB_Installer', 'install' ) );
add_action( 'wp_loaded', array( 'AH_DB_Installer', 'maybe_upgrade' ) );
