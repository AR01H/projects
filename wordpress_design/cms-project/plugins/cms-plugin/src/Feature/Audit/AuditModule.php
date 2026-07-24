<?php

namespace Ah\Cms\Feature\Audit;

defined( 'ABSPATH' ) || exit;

class AuditModule {
	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
	}
	public static function registerMenu(): void {
		add_submenu_page( 'ah-cms', 'Audit Log', 'Audit Log', 'manage_options', 'ah-audit', [ Controller\AuditAdminController::class, 'render' ] );
	}
}
