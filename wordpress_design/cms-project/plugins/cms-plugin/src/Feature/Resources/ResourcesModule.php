<?php

namespace Ah\Cms\Feature\Resources;

defined( 'ABSPATH' ) || exit;

class ResourcesModule {
	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
	}
	public static function registerMenu(): void {
		add_submenu_page( 'ah-cms', 'Resources', 'Resources', 'manage_options', 'ah-resources', [ Controller\ResourcesAdminController::class, 'render' ] );
	}
}
