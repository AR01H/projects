<?php

namespace Ah\Cms\Feature\FAQs;

defined( 'ABSPATH' ) || exit;

class FaqsModule {
	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
	}
	public static function registerMenu(): void {
		add_submenu_page( 'ah-cms', 'FAQs', 'FAQs', 'manage_options', 'ah-faqs', [ Controller\FaqsAdminController::class, 'render' ] );
	}
}
