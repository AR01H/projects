<?php
namespace Ah\Cms\Bootstrap;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin Bootstrap
 *
 * Central entry point for the plugin lifecycle.
 * Registers all hooks, initializes features, and manages the application lifecycle.
 *
 * @package Ah\Cms\Bootstrap
 */

class PluginBootstrap {

	/**
	 * Initialize the plugin.
	 * Called once from ah-cms.php.
	 */
	public static function init(): void {
		// Register all hooks
		HookRegistrar::register();
	}

	/**
	 * Get the plugin version.
	 */
	public static function getVersion(): string {
		return AH_PLUGIN_VERSION;
	}

	/**
	 * Get the plugin directory path.
	 */
	public static function getDir(): string {
		return AH_PLUGIN_DIR;
	}

	/**
	 * Get the plugin directory URL.
	 */
	public static function getUrl(): string {
		return AH_PLUGIN_URL;
	}
}
