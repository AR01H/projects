<?php

namespace Ah\Cms\Feature\FileLinks;

defined( 'ABSPATH' ) || exit;

class FileLinksModule {
	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
	}
	public static function registerMenu(): void {
		add_submenu_page( 'ah-cms', 'File Links', 'File Links', 'manage_options', 'ah-file-links', [ Controller\FileLinksAdminController::class, 'render' ] );
	}
}
