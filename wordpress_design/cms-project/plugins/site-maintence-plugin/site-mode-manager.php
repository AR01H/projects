<?php
/**
 * Plugin Name:       Site Maintenance Manager
 * Description:       Controls the entire website state: Coming Soon, Maintenance, or Normal mode. Includes an admin toggle UI.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Your Name
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       site-maintenance-manager
 * Domain Path:       /languages
 *
 * @package SiteModeManager
 */

declare( strict_types=1 );

namespace SiteModeManager;

// Block direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// ─── Plugin Constants ────────────────────────────────────────────────────────

define( 'SMM_VERSION',     '1.0.0' );
define( 'SMM_PLUGIN_FILE', __FILE__ );
define( 'SMM_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'SMM_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'SMM_PLUGIN_BASE', plugin_basename( __FILE__ ) );

// ─── Autoloader ──────────────────────────────────────────────────────────────

spl_autoload_register(
	function ( string $class ): void {
		// Only handle our namespace.
		if ( strpos( $class, 'SiteModeManager\\' ) !== 0 ) {
			return;
		}

		$relative = str_replace( 'SiteModeManager\\', '', $class );
		$file      = SMM_PLUGIN_DIR . 'includes/' . strtolower(
			str_replace( '\\', '/', $relative )
		) . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
);

// ─── Bootstrap ───────────────────────────────────────────────────────────────

/**
 * Returns the single Plugin instance (lazy singleton).
 *
 * @return Plugin
 */
function smm(): Plugin {
	return Plugin::instance();
}
require_once SMM_PLUGIN_DIR . 'includes/helpers.php';

// Activation / deactivation hooks must be registered before the plugin loads.
register_activation_hook(   SMM_PLUGIN_FILE, [ 'SiteModeManager\Activator',   'activate'   ] );
register_deactivation_hook( SMM_PLUGIN_FILE, [ 'SiteModeManager\Activator',   'deactivate' ] );

add_action( 'plugins_loaded', 'SiteModeManager\smm' );
