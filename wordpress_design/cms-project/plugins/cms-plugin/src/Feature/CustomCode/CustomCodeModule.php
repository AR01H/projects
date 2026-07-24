<?php

namespace Ah\Cms\Feature\CustomCode;

defined( 'ABSPATH' ) || exit;

class CustomCodeModule {
	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
		add_action( 'wp_head', [ Service\CustomCodeService::class, 'injectGlobalCss' ], 999 );
		add_action( 'wp_head', [ Service\CustomCodeService::class, 'injectSlugCss' ], 999 );
		add_action( 'wp_footer', [ Service\CustomCodeService::class, 'injectGlobalJs' ], 999 );
		add_action( 'wp_footer', [ Service\CustomCodeService::class, 'injectSlugJs' ], 999 );
	}
	public static function registerMenu(): void {
		add_submenu_page( 'ah-cms', 'Custom Code', 'Custom Code', 'manage_options', 'ah-custom-code', [ Controller\CustomCodeAdminController::class, 'render' ] );
	}
}
