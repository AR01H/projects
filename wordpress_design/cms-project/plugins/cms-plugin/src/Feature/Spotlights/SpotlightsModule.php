<?php

namespace Ah\Cms\Feature\Spotlights;

defined( 'ABSPATH' ) || exit;

class SpotlightsModule {
	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
	}
	public static function registerMenu(): void {
		add_submenu_page( 'ah-cms', 'Spotlights', 'Spotlights', 'manage_options', 'ah-spotlights', [ Controller\SpotlightsAdminController::class, 'render' ] );
	}
}
