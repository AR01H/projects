<?php
namespace Adn\Theme\Bootstrap;

defined( 'ABSPATH' ) || exit;

/**
 * Theme Bootstrap
 *
 * Central entry point for the theme lifecycle.
 * Registers all hooks, initializes features, and manages the application lifecycle.
 *
 * @package Adn\Theme\Bootstrap
 */

class ThemeBootstrap {

	/**
	 * Initialize the theme.
	 * Called once from functions.php.
	 */
	public static function init(): void {
		// Register all hooks
		HookRegistrar::register();
	}

	/**
	 * Get the theme version.
	 */
	public static function getVersion(): string {
		return ADN_THEME_VERSION;
	}

	/**
	 * Get the theme directory path.
	 */
	public static function getDir(): string {
		return ADN_THEME_DIR;
	}

	/**
	 * Get the theme directory URL.
	 */
	public static function getUrl(): string {
		return ADN_THEME_URI;
	}
}
