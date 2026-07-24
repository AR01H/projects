<?php

namespace Ah\Cms\Feature\Analytics;

defined( 'ABSPATH' ) || exit;

class AnalyticsModule {
	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
		add_action( 'wp_ajax_ah_analytics_action', [ Controller\AnalyticsAjaxController::class, 'handle' ] );
	}
	public static function registerMenu(): void {
		add_submenu_page( 'ah-cms', 'Analytics Reports', 'Analytics Reports', 'manage_options', 'ah-analytics', [ Controller\AnalyticsAdminController::class, 'render' ] );
	}
}
