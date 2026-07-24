<?php

namespace Ah\Cms\Feature\Media;

defined( 'ABSPATH' ) || exit;

class MediaModule {
	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
	}
	public static function registerMenu(): void {
		add_submenu_page( 'ah-cms', 'Media Library', 'Media Library', 'manage_options', 'ah-media', [ Controller\MediaAdminController::class, 'render' ] );
	}
}
