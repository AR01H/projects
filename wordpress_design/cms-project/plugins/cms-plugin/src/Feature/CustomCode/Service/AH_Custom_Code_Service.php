<?php
defined( 'ABSPATH' ) || exit;

/**
 * Custom Code Service — handles per-slug CSS/JS injection and global styles.
 */
class AH_Custom_Code_Service {

	// ── Frontend Injection ────────────────────────────────────────────────

	public static function injectGlobalCss(): void {
		if ( is_admin() ) return;
		$css = trim( (string) get_option( 'ah_global_styles_css', '' ) );
		if ( '' === $css || ! get_option( 'ah_global_styles_active', 0 ) ) return;
		echo "\n<style id=\"ah-global-styles\">\n" . $css . "\n</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public static function injectGlobalJs(): void {
		if ( is_admin() ) return;
		$js = trim( (string) get_option( 'ah_global_styles_js', '' ) );
		if ( '' === $js || ! get_option( 'ah_global_styles_active', 0 ) ) return;
		echo "\n<script id=\"ah-global-scripts\">\n" . $js . "\n</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public static function injectSlugCss(): void {
		if ( is_admin() ) return;
		$slug = self::getCurrentSlug();
		if ( '' === $slug ) return;
		$row = self::getBySlug( $slug );
		if ( ! $row ) return;
		$css = trim( (string) ( $row->css ?? '' ) );
		if ( '' !== $css ) {
			echo "\n<style id=\"ah-custom-css-" . esc_attr( $slug ) . "\">\n" . $css . "\n</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	public static function injectSlugJs(): void {
		if ( is_admin() ) return;
		$slug = self::getCurrentSlug();
		if ( '' === $slug ) return;
		$row = self::getBySlug( $slug );
		if ( ! $row ) return;
		$js = trim( (string) ( $row->js ?? '' ) );
		if ( '' !== $js ) {
			echo "\n<script id=\"ah-custom-js-" . esc_attr( $slug ) . "\">\n(function(){\n" . $js . "\n})();\n</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	// ── AJAX Handlers ─────────────────────────────────────────────────────

	public static function ajaxSave(): void {
		if ( ! check_ajax_referer( 'ah_custom_code', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Security check failed.' ] );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Access denied.' ] );
		}

		$id   = (int) ( $_POST['entry_id'] ?? 0 );
		$slug = sanitize_key( wp_unslash( $_POST['slug'] ?? '' ) );
		$css  = wp_unslash( $_POST['css'] ?? '' );
		$js   = wp_unslash( $_POST['js'] ?? '' );

		if ( '' === $slug ) {
			wp_send_json_error( [ 'message' => 'Page slug is required.' ] );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'ah_custom_code';

		if ( 0 === $id ) {
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$table}` WHERE slug = %s LIMIT 1", $slug ) );
			if ( $exists ) {
				wp_send_json_error( [ 'message' => 'A rule for "' . esc_html( $slug ) . '" already exists.' ] );
			}
			$wpdb->insert( $table, [
				'slug'      => $slug,
				'css'       => $css,
				'js'        => $js,
				'is_active' => 1,
			], [ '%s', '%s', '%s', '%d' ] );
			$id = (int) $wpdb->insert_id;
		} else {
			$wpdb->update( $table, [ 'slug' => $slug, 'css' => $css, 'js' => $js ], [ 'id' => $id ], [ '%s', '%s', '%s' ], [ '%d' ] );
		}

		wp_send_json_success( [
			'message'  => 'Saved.',
			'id'       => $id,
			'redirect' => admin_url( 'admin.php?page=ah-custom-code&tab=per-page&action=edit&edit=' . $id ),
		] );
	}

	public static function ajaxDelete(): void {
		if ( ! check_ajax_referer( 'ah_custom_code', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Security check failed.' ] );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Access denied.' ] );
		}
		$id = (int) ( $_POST['entry_id'] ?? 0 );
		if ( ! $id ) {
			wp_send_json_error( [ 'message' => 'Invalid ID.' ] );
		}
		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'ah_custom_code', [ 'id' => $id ], [ '%d' ] );
		wp_send_json_success( [ 'message' => 'Deleted.' ] );
	}

	public static function ajaxToggle(): void {
		if ( ! check_ajax_referer( 'ah_custom_code', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Security check failed.' ] );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Access denied.' ] );
		}
		global $wpdb;
		$table = $wpdb->prefix . 'ah_custom_code';
		$id    = (int) ( $_POST['entry_id'] ?? 0 );
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT is_active FROM `{$table}` WHERE id = %d LIMIT 1", $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( ! $row ) {
			wp_send_json_error( [ 'message' => 'Not found.' ] );
		}
		$new = $row->is_active ? 0 : 1;
		$wpdb->update( $table, [ 'is_active' => $new ], [ 'id' => $id ], [ '%d' ], [ '%d' ] );
		wp_send_json_success( [ 'active' => $new ] );
	}

	public static function ajaxSaveGlobalStyles(): void {
		if ( ! check_ajax_referer( 'ah_custom_code', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Security check failed.' ] );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Access denied.' ] );
		}
		update_option( 'ah_global_styles_css', wp_unslash( $_POST['css'] ?? '' ) );
		update_option( 'ah_global_styles_js', wp_unslash( $_POST['js'] ?? '' ) );
		update_option( 'ah_global_styles_active', (int) ( $_POST['active'] ?? 0 ) );
		wp_send_json_success( [ 'message' => 'Global styles saved.' ] );
	}

	// ── Helpers ───────────────────────────────────────────────────────────

	private static function getCurrentSlug(): string {
		$qv = (string) get_query_var( 'adn_cat_slug', '' );
		if ( '' !== $qv ) return sanitize_key( $qv );
		$obj = get_queried_object();
		if ( $obj instanceof WP_Post ) return sanitize_key( $obj->post_name );
		$path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ?? '', '/' );
		$seg  = explode( '/', $path );
		return sanitize_key( $seg[0] ?? '' );
	}

	private static function getBySlug( string $slug ): ?object {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_custom_code';
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM `{$table}` WHERE slug = %s AND is_active = 1 LIMIT 1",
			$slug
		) );
	}
}
