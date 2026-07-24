<?php

namespace Ah\Cms\Feature\Redirect;

defined( 'ABSPATH' ) || exit;

class RedirectModule {
	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
		add_action( 'template_redirect', [ Service\RedirectService::class, 'checkRedirects' ], 1 );
	}
	public static function registerMenu(): void {
		add_submenu_page( 'ah-cms', 'Redirect Rules', 'Redirect Rules', 'manage_options', 'ah-redirects', [ Controller\RedirectAdminController::class, 'render' ] );
	}
}
