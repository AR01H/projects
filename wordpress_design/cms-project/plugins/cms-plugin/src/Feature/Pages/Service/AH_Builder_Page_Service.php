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

		// A real page was found - correct the 404 the main query already decided on,
		// otherwise this renders fine but still ships as "404 Not Found" to browsers,
		// crawlers, and anything else that checks the response status.
		global $wp_query;
		$wp_query->is_404 = false;
		status_header( 200 );

		$GLOBALS['ah_builder_page'] = $page;

		// CSS is enqueued by the theme's Adn\Theme\Service\AssetLoader::loadBlockRenderer(),
		// which runs on the normal wp_enqueue_scripts hook and checks $GLOBALS['ah_builder_page']
		// (set above). It loads plugins/cms-plugin/assets/css/block-render-base.css (structural,
		// theme-agnostic) followed by the theme's own block-render-page-styles.css (colors/type) -
		// see plans/streamed-frolicking-hopcroft.md Phase 1. The old builder-page.css this used to
		// enqueue directly is superseded by those two files and no longer loaded.

		require_once AH_PLUGIN_DIR . '/inc/BuilderBlockRenderer.php';

		$_theme_tpl = locate_template( 'templates/AhBuilderPage.php' );
		include $_theme_tpl ?: AH_PLUGIN_DIR . '/templates/TemplateBuilderPage.php';
		exit;
	}
}
