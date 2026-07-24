<?php

namespace Ah\Cms\Feature\CustomCode\Service;

defined( 'ABSPATH' ) || exit;

/**
 * Custom code service — manages per-slug CSS/JS injection and global styles.
 * Extracted from ah-cms.php inline functions.
 */
class CustomCodeService {

	/**
	 * Get the current page slug for custom code lookup.
	 */
	public static function getCurrentSlug(): string {
		$qv = (string) get_query_var( 'adn_cat_slug', '' );
		if ( '' !== $qv ) {
			return sanitize_key( $qv );
		}
		$obj = get_queried_object();
		if ( $obj instanceof \WP_Post ) {
			return sanitize_key( $obj->post_name );
		}
		$path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ?? '', '/' );
		$seg  = explode( '/', $path );
		return sanitize_key( $seg[0] ?? '' );
	}

	/**
	 * Get custom code row for a slug.
	 */
	public static function getBySlug( string $slug ): ?object {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_custom_code';
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM `{$table}` WHERE slug = %s AND is_active = 1 LIMIT 1",
				$slug
			)
		);
	}

	/**
	 * Inject global CSS in wp_head.
	 */
	public static function injectGlobalCss(): void {
		if ( is_admin() ) {
			return;
		}
		$css = trim( (string) get_option( 'ah_global_styles_css', '' ) );
		if ( '' === $css || ! get_option( 'ah_global_styles_active', 0 ) ) {
			return;
		}
		echo "\n<style id=\"ah-global-styles\">\n" . $css . "\n</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Inject global JS in wp_footer.
	 */
	public static function injectGlobalJs(): void {
		if ( is_admin() ) {
			return;
		}
		$js = trim( (string) get_option( 'ah_global_styles_js', '' ) );
		if ( '' === $js || ! get_option( 'ah_global_styles_active', 0 ) ) {
			return;
		}
		echo "\n<script id=\"ah-global-scripts\">\n" . $js . "\n</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Inject per-slug CSS in wp_head.
	 */
	public static function injectSlugCss(): void {
		if ( is_admin() ) {
			return;
		}
		$slug = self::getCurrentSlug();
		if ( '' === $slug ) {
			return;
		}
		$row = self::getBySlug( $slug );
		if ( ! $row ) {
			return;
		}
		$css = trim( (string) ( $row->css ?? '' ) );
		if ( '' !== $css ) {
			echo "\n<style id=\"ah-custom-css-" . esc_attr( $slug ) . "\">\n" . $css . "\n</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Inject per-slug JS in wp_footer.
	 */
	public static function injectSlugJs(): void {
		if ( is_admin() ) {
			return;
		}
		$slug = self::getCurrentSlug();
		if ( '' === $slug ) {
			return;
		}
		$row = self::getBySlug( $slug );
		if ( ! $row ) {
			return;
		}
		$js = trim( (string) ( $row->js ?? '' ) );
		if ( '' !== $js ) {
			echo "\n<script id=\"ah-custom-js-" . esc_attr( $slug ) . "\">\n(function(){\n" . $js . "\n})();\n</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Save custom code entry.
	 */
	public static function save( int $id, string $slug, string $css, string $js ): int {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_custom_code';

		if ( '' === $slug ) {
			return 0;
		}

		if ( 0 === $id ) {
			$exists = $wpdb->get_var(
				$wpdb->prepare( "SELECT id FROM `{$table}` WHERE slug = %s LIMIT 1", $slug )
			);
			if ( $exists ) {
				return 0;
			}
			$wpdb->insert( $table, [
				'slug'      => $slug,
				'css'       => $css,
				'js'        => $js,
				'is_active' => 1,
			], [ '%s', '%s', '%s', '%d' ] );
			return (int) $wpdb->insert_id;
		}

		$wpdb->update( $table, [ 'slug' => $slug, 'css' => $css, 'js' => $js ], [ 'id' => $id ], [ '%s', '%s', '%s' ], [ '%d' ] );
		return $id;
	}

	/**
	 * Delete custom code entry.
	 */
	public static function delete( int $id ): bool {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_custom_code';
		return $wpdb->delete( $table, [ 'id' => $id ], [ '%d' ] ) !== false;
	}
}
