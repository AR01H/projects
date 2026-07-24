<?php

namespace Ah\Cms\Feature\Reviews;

defined( 'ABSPATH' ) || exit;

class ReviewsModule {
	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
	}
	public static function registerMenu(): void {
		add_submenu_page( 'ah-cms', 'Reviews', 'Reviews', 'manage_options', 'ah-reviews', [ Controller\ReviewsAdminController::class, 'render' ] );
	}
}
