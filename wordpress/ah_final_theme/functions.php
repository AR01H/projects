<?php
defined( 'ABSPATH' ) || exit;

define( 'AH_THEME_VERSION', '1.0.0' );
define( 'AH_THEME_DIR', get_template_directory() );
define( 'AH_THEME_URL', get_template_directory_uri() );
define( 'AH_DB_VERSION_KEY', 'ah_theme_db_version' );

// Autoloader
require_once AH_THEME_DIR . '/inc/class-autoloader.php';
AH_Autoloader::register();

// Core init
AH_Theme_Setup::init();
AH_Asset_Loader::init();

// Admin portal
if ( is_admin() ) {
	AH_Admin_Bootstrap::init();
}

// Public AJAX (contact form — available to all visitors)
AH_Ajax_Handlers::init_public();

// Install DB tables on theme activation
add_action( 'after_switch_theme', array( 'AH_DB_Installer', 'install' ) );
add_action( 'wp_loaded', array( 'AH_DB_Installer', 'maybe_upgrade' ) );
