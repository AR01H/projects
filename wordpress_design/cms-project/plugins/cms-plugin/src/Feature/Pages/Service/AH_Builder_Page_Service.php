<?php
defined( 'ABSPATH' ) || exit;

/**
 * Builder Page Service — handles frontend routing for builder pages.
 * Replaces inline template_redirect handler in ah-cms.php.
 */
class AH_Builder_Page_Service {

	public static function handleFrontend(): void {
		if ( ! is_404() ) return;

		global $wpdb;
		$table       = $wpdb->prefix . 'ah_builder_pages';
		$home_path   = trim( (string) parse_url( home_url(), PHP_URL_PATH ), '/' );
		$request_path = trim( (string) parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );

		if ( $home_path !== '' && strpos( $request_path, $home_path ) === 0 ) {
			$request_path = ltrim( substr( $request_path, strlen( $home_path ) ), '/' );
		}

		$slug = sanitize_title( trim( $request_path, '/' ) );
		if ( ! $slug ) return;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$page = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE slug = %s AND status = 'active'", $slug ) );
		if ( ! $page ) return;

		$GLOBALS['ah_builder_page'] = $page;

		add_action( 'wp_enqueue_scripts', function () {
			wp_enqueue_style(
				'ah-builder-page',
				AH_PLUGIN_URL . '/assets/css/builder-page.css',
				[ 'ah-variables' ],
				AH_PLUGIN_VERSION
			);
		} );

		require_once AH_PLUGIN_DIR . '/inc/BuilderBlockRenderer.php';

		$_theme_tpl = locate_template( 'templates/AhBuilderPage.php' );
		include $_theme_tpl ?: AH_PLUGIN_DIR . '/templates/TemplateBuilderPage.php';
		exit;
	}
}
