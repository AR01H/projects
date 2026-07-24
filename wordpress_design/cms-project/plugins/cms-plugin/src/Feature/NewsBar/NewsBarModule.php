<?php

namespace Ah\Cms\Feature\NewsBar;

defined( 'ABSPATH' ) || exit;

class NewsBarModule {
	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
	}
	public static function registerMenu(): void {
		add_submenu_page( 'ah-cms', 'News Bar', 'News Bar', 'manage_options', 'ah-news-bar', [ Controller\NewsBarAdminController::class, 'render' ] );
	}
}
