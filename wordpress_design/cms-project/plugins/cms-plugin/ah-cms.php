<?php
/**
 * Plugin Name:  CMS ADMIN
 * Description:  CMS engine - admin portal, database, models, helpers, and form builder.
 *               Install as a plugin and pair with any frontend theme that reads wp_ah_* tables.
 * Version:      1.3.1
 * Author:       Akhilesh Ravuri
 * Text Domain:  ah-theme
 */
defined( 'ABSPATH' ) || exit;

// ── Constants ────────────────────────────────────────────────────────────────
define( 'AH_PLUGIN_VERSION', '1.3.1' );
define( 'AH_DB_VERSION_KEY', 'ah_cms_db_version' );
define( 'TABLE_MID_FIX', '_cms_plug_' );
define( 'AH_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'AH_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'AH_THEME_DIR', AH_PLUGIN_DIR );
define( 'AH_THEME_URL', AH_PLUGIN_URL );
define( 'AH_THEME_VERSION', AH_PLUGIN_VERSION );

// ── Autoloaders ──────────────────────────────────────────────────────────────
$composer_autoload = AH_PLUGIN_DIR . '/vendor/autoload.php';
if ( file_exists( $composer_autoload ) ) {
	require_once $composer_autoload;
}
require_once AH_PLUGIN_DIR . '/inc/Autoloader.php';
require_once AH_PLUGIN_DIR . '/inc/AhCache.php';
AH_Autoloader::register();

// ── Components ───────────────────────────────────────────────────────────────
require_once AH_PLUGIN_DIR . '/components/toaster/index.php';

// ── All Hooks (centralized) ─────────────────────────────────────────────────
// Every add_action, add_filter, add_shortcode lives in HookRegistrar.
// See: src/Bootstrap/HookRegistrar.php
Ah\Cms\Bootstrap\HookRegistrar::register();
