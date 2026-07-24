<?php

namespace Ah\Cms\Feature\FeaturedIn;

defined( 'ABSPATH' ) || exit;

class FeaturedInModule {
	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
	}
	public static function registerMenu(): void {
		add_submenu_page( 'ah-cms', 'Featured In', 'Featured In', 'manage_options', 'ah-featured-in', [ Controller\FeaturedInAdminController::class, 'render' ] );
	}
}
