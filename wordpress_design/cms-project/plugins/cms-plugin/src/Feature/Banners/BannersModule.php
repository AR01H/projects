<?php

namespace Ah\Cms\Feature\Banners;

defined( 'ABSPATH' ) || exit;

class BannersModule {
	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
	}
	public static function registerMenu(): void {
		add_submenu_page( 'ah-cms', 'Home Banners', 'Home Banners', 'manage_options', 'ah-banners', [ Controller\BannersAdminController::class, 'render' ] );
	}
}
