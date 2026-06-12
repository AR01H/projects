<?php
/**
 * Activation and deactivation handler.
 *
 * @package SiteModeManager
 */

declare( strict_types=1 );

namespace SiteModeManager;

// Block direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Activator
 */
final class Activator {

	/**
	 * Runs on plugin activation.
	 *
	 * - Checks minimum PHP / WP requirements.
	 * - Initialises the mode option in the database.
	 * - Flushes rewrite rules.
	 *
	 * @return void
	 */
	public static function activate(): void {
		self::check_requirements();

		// Manually load Settings because the autoloader may not be running yet
		// at the point register_activation_hook fires (plugins_loaded hasn't fired).
		if ( ! class_exists( Settings::class ) ) {
			require_once plugin_dir_path( SMM_PLUGIN_FILE ) . 'includes/settings.php';
		}

		( new Settings() )->maybe_init_option();

		flush_rewrite_rules();
	}

	/**
	 * Runs on plugin deactivation.
	 *
	 * - Flushes rewrite rules.
	 * (Options are intentionally kept so mode config survives reactivation.)
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}

	// ─── Private Helpers ─────────────────────────────────────────────────────

	/**
	 * Verifies that the server meets minimum requirements.
	 * Deactivates the plugin with a friendly error if not.
	 *
	 * @return void
	 */
	private static function check_requirements(): void {
		$errors = [];

		if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
			$errors[] = sprintf(
				/* translators: %s = current PHP version */
				__( 'Site Mode Manager requires PHP 8.0 or higher. Your server is running PHP %s.', 'site-mode-manager' ),
				PHP_VERSION
			);
		}

		global $wp_version;
		if ( version_compare( $wp_version, '6.0', '<' ) ) {
			$errors[] = sprintf(
				/* translators: %s = current WP version */
				__( 'Site Mode Manager requires WordPress 6.0 or higher. You are running WordPress %s.', 'site-mode-manager' ),
				$wp_version
			);
		}

		if ( ! empty( $errors ) ) {
			deactivate_plugins( SMM_PLUGIN_BASE );
			wp_die(
				'<p>' . implode( '</p><p>', array_map( 'esc_html', $errors ) ) . '</p>',
				esc_html__( 'Plugin Activation Error', 'site-mode-manager' ),
				[ 'back_link' => true ]
			);
		}
	}
}
