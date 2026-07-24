<?php

namespace Ah\Cms\Feature\Pages;

defined( 'ABSPATH' ) || exit;

/**
 * Pages module entry point.
 * Registers hooks for page management, builder, and static pages.
 */
class PagesModule {

	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenus' ] );
		add_action( 'admin_enqueue_scripts', [ self::class, 'enqueueAssets' ] );
		add_action( 'template_redirect', [ self::class, 'handleFrontend' ] );
	}

	public static function registerMenus(): void {
		add_submenu_page( 'ah-cms', 'Pages Manager', 'Pages Manager', 'manage_options', 'ah-pages', [ Controller\PagesAdminController::class, 'render' ] );
		add_submenu_page( 'ah-cms', 'Page Builder', 'Page Builder', 'manage_options', 'ah-page-builder', [ Controller\PageBuilderController::class, 'render' ] );
		add_submenu_page( 'ah-cms', 'Static Pages', 'Static Pages', 'manage_options', 'ah-static-pages', [ Controller\StaticPageController::class, 'render' ] );
	}

	public static function enqueueAssets( string $hook ): void {
		$valid = [ 'ah-pages', 'ah-page-builder', 'ah-static-pages' ];
		$found = false;
		foreach ( $valid as $slug ) {
			if ( strpos( $hook, $slug ) !== false ) {
				$found = true;
				break;
			}
		}
		if ( ! $found ) {
			return;
		}
		wp_enqueue_style( 'ah-pages', AH_PLUGIN_URL . '/src/Feature/Pages/Assets/css/pages.css', [], '1.0' );
	}

	public static function handleFrontend(): void {
		// Builder page rendering via template_redirect
		// Static page rendering via template_redirect
	}
}
