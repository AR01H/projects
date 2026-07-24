<?php

namespace Ah\Cms\Feature\SiteNotices;

defined( 'ABSPATH' ) || exit;

class SiteNoticesModule {
	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
	}
	public static function registerMenu(): void {
		add_submenu_page( 'ah-cms', 'Site Notices', 'Site Notices', 'manage_options', 'ah-notices', [ Controller\SiteNoticesAdminController::class, 'render' ] );
	}
}
