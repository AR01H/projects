<?php

namespace Ah\Cms\Feature\Import;

defined( 'ABSPATH' ) || exit;

class ImportModule {
	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
	}
	public static function registerMenu(): void {
		add_submenu_page( 'ah-cms', 'Data Import', 'Data Import', 'manage_options', 'ah-import', [ Controller\ImportAdminController::class, 'render' ] );
	}
}
