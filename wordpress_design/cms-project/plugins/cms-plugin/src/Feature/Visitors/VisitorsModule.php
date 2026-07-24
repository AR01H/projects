<?php

namespace Ah\Cms\Feature\Visitors;

defined( 'ABSPATH' ) || exit;

class VisitorsModule {
	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
		add_action( 'rest_api_init', [ Controller\VisitorPingRestController::class, 'registerRoutes' ] );
	}
	public static function registerMenu(): void {
		add_submenu_page( 'ah-cms', 'Visitor Stats', 'Visitor Stats', 'manage_options', 'ah-visitors', [ Controller\VisitorsAdminController::class, 'render' ] );
	}
}
