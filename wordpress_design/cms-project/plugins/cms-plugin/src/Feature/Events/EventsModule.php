<?php

namespace Ah\Cms\Feature\Events;

defined( 'ABSPATH' ) || exit;

class EventsModule {
	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
	}
	public static function registerMenu(): void {
		add_submenu_page( 'ah-cms', 'Events', 'Events', 'manage_options', 'ah-events', [ Controller\EventsAdminController::class, 'render' ] );
	}
}
