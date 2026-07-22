<?php
/**
 * Plugin Name:  CMS Suggestion Bot
 * Description:  Independent CMS knowledge engine - reads, caches, and indexes WordPress content into a queryable knowledge base with an extensible bot layer.
 * Version:      0.1.0
 * Requires PHP: 8.2
 * Author:       Akhilesh Ravuri
 * Text Domain:  cms-suggestion-bot
 *
 * This plugin is fully self-contained: it has no dependency on any theme or
 * other plugin, uses its own database tables (cms_sug_bot_*), and its own
 * PSR-4 autoloader under the CmsSuggestionBot\ namespace.
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

// ── Constants ────────────────────────────────────────────────────────────────
define( 'CSB_VERSION', '0.1.0' );
define( 'CSB_DB_VERSION', '1.0.0' );
define( 'CSB_DB_VERSION_OPTION', 'csb_db_version' );

// All custom tables are named: {$wpdb->prefix}cms_sug_bot_{table}, e.g. wp_cms_sug_bot_cache.
define( 'CSB_TABLE_PREFIX', 'cms_sug_bot_' );

define( 'CSB_PLUGIN_FILE', __FILE__ );
define( 'CSB_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'CSB_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'CSB_APP_DIR', CSB_PLUGIN_DIR . '/app' );
define( 'CSB_APP_NAMESPACE', 'CmsSuggestionBot' );

// ── Autoloading ──────────────────────────────────────────────────────────────
// Prefer Composer's autoloader when the plugin has been installed with
// `composer install` (adds PSR-4 class maps, dev tooling, etc.); otherwise
// fall back to the bundled zero-dependency PSR-4 autoloader so the plugin
// still works when simply copied into wp-content/plugins.
if ( file_exists( CSB_PLUGIN_DIR . '/vendor/autoload.php' ) ) {
	require_once CSB_PLUGIN_DIR . '/vendor/autoload.php';
} else {
	require_once CSB_APP_DIR . '/Core/Autoloader.php';
	CmsSuggestionBot\Core\Autoloader::register( CSB_APP_NAMESPACE, CSB_APP_DIR );
}

require_once CSB_PLUGIN_DIR . '/config/constants.php';

// ── Lifecycle ────────────────────────────────────────────────────────────────
register_activation_hook( __FILE__, array( CmsSuggestionBot\Core\Activator::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( CmsSuggestionBot\Core\Deactivator::class, 'deactivate' ) );

// ── Boot ─────────────────────────────────────────────────────────────────────
add_action( 'plugins_loaded', static function (): void {
	CmsSuggestionBot\Core\Plugin::instance()->boot();
} );
