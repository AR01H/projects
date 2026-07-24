<?php

namespace Ah\Cms\Feature\AdminTools;

defined( 'ABSPATH' ) || exit;

class AdminToolsModule {
	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
	}
	public static function registerMenu(): void {
		add_submenu_page( 'ah-cms', 'Admin Tools', 'Admin Tools', 'manage_options', 'ah-admin-tools', [ Controller\AdminToolsController::class, 'render' ] );
		add_submenu_page( 'ah-cms', 'Permissions', 'Permissions', 'manage_options', 'ah-permissions', [ Controller\PermissionManagerController::class, 'render' ] );
	}
}
