<?php

namespace Ah\Cms\Feature\Taxonomy;

defined( 'ABSPATH' ) || exit;

class TaxonomyModule {
	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
	}
	public static function registerMenu(): void {
		add_submenu_page( 'ah-cms', 'Taxonomy Manager', 'Taxonomy Manager', 'manage_options', 'ah-taxonomy', [ Controller\TaxonomyAdminController::class, 'render' ] );
	}
}
