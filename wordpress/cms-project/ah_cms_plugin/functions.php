<?php
defined( 'ABSPATH' ) || exit;

// ─────────────────────────────────────────────────────────────────────────────
// PLUGIN MODE — AH CMS plugin is active.
// The plugin already handled: autoloader, admin portal, AJAX, DB, shortcodes.
// This file only needs to add theme-specific frontend concerns.
// ─────────────────────────────────────────────────────────────────────────────
if ( defined( 'AH_PLUGIN_DIR' ) ) {

	add_action( 'after_setup_theme', static function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
		add_theme_support( 'custom-logo' );
		load_theme_textdomain( 'ah-theme', get_template_directory() . '/languages' );
		register_nav_menus( array(
			'primary' => __( 'Primary Menu', 'ah-theme' ),
			'footer'  => __( 'Footer Menu', 'ah-theme' ),
		) );
	} );

	// Frontend assets — AH_Asset_Loader is autoloaded by the plugin.
	// It uses get_template_directory_uri() internally so assets always resolve
	// to THIS theme folder, not the plugin folder.
	AH_Asset_Loader::init();

	return; // Plugin handles everything else.
}

// ─────────────────────────────────────────────────────────────────────────────
// STANDALONE THEME MODE — plugin is NOT active.
// Full bootstrap so the theme works on its own (backward-compatible).
// ─────────────────────────────────────────────────────────────────────────────
define( 'AH_THEME_VERSION', '1.0.0' );
define( 'AH_THEME_DIR',     get_template_directory() );
define( 'AH_THEME_URL',     get_template_directory_uri() );
define( 'AH_PLUGIN_DIR',    AH_THEME_DIR );
define( 'AH_PLUGIN_URL',    AH_THEME_URL );
define( 'AH_PLUGIN_VERSION', AH_THEME_VERSION );
define( 'AH_DB_VERSION_KEY', 'ah_cms_db_version' );

require_once AH_THEME_DIR . '/inc/class-autoloader.php';
AH_Autoloader::register();

AH_Theme_Setup::init();
AH_Asset_Loader::init();

if ( is_admin() ) {
	AH_Admin_Bootstrap::init();
}

AH_Ajax_Handlers::init_public();

add_action( 'after_switch_theme', array( 'AH_DB_Installer', 'install' ) );
add_action( 'wp_loaded',          array( 'AH_DB_Installer', 'maybe_upgrade' ) );
