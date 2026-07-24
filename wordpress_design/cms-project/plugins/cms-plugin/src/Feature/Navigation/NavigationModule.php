<?php

namespace Ah\Cms\Feature\Navigation;

defined( 'ABSPATH' ) || exit;

class NavigationModule {
	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
	}
	public static function registerMenu(): void {
		add_submenu_page( 'ah-cms', 'Navigation Editor', 'Navigation Editor', 'manage_options', 'ah-navigation', [ Controller\NavigationAdminController::class, 'render' ] );
	}
}
