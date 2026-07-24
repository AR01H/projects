<?php

namespace Ah\Cms\Feature\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Settings module entry point.
 * Registers hooks, routes, and assets for site settings management.
 */
class SettingsModule {

	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
		add_action( 'admin_enqueue_scripts', [ self::class, 'enqueueAssets' ] );
	}

	public static function registerMenu(): void {
		add_submenu_page(
			'ah-cms',
			'Site Settings',
			'Site Settings',
			'manage_options',
			'ah-settings',
			[ Controller\SettingsAdminController::class, 'render' ]
		);
	}

	public static function enqueueAssets( string $hook ): void {
		if ( strpos( $hook, 'ah-settings' ) === false ) {
			return;
		}
		wp_enqueue_style( 'ah-settings', AH_PLUGIN_URL . '/src/Feature/Settings/Assets/css/settings.css', [], '1.0' );
	}
}
