<?php
/**
 * core/class-helpers.php - Static helper methods used across templates.
 */

defined( 'ABSPATH' ) || exit;

class App_Helpers {

	public static function require_theme_file( $rel ) {
		$base = realpath( NT_THEME_DIR );
		$file = realpath( NT_THEME_DIR . '/' . ltrim( (string) $rel, '/' ) );
		if ( $base && $file && 0 === strpos( $file, $base ) && is_file( $file ) ) {
			require_once $file;
			return true;
		}
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[APP] Missing theme file: ' . $rel );
		}
		return false;
	}

	public static function component( $name, $context = array() ) {
		$base = realpath( NT_THEME_DIR . '/components' );
		$file = realpath( NT_THEME_DIR . '/components/' . $name . '.php' );
		if ( ! $base || ! $file || 0 !== strpos( $file, $base ) || ! is_file( $file ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( '[APP] Component not found: ' . $name );
			}
			return;
		}
		extract( $context, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract
		include $file;
	}

	public static function option( $group, $key = null, $default = '' ) {
		$admin    = App_Theme::config( 'admin' );
		$opt_name = $admin['options'][ $group ]['option'] ?? 'app_' . sanitize_key( $group );
		$data     = get_option( $opt_name, array() );
		$data     = is_array( $data ) ? $data : array();

		if ( null === $key ) {
			return $data;
		}
		return ( isset( $data[ $key ] ) && '' !== $data[ $key ] ) ? $data[ $key ] : $default;
	}

	public static function data( $name, $default = array() ) {
		static $cache = array();
		$name = basename( (string) $name, '.json' );
		if ( isset( $cache[ $name ] ) ) {
			return $cache[ $name ];
		}
		$base = realpath( NT_THEME_DIR . '/admin/data' );
		$file = realpath( NT_THEME_DIR . '/admin/data/' . $name . '.json' );
		$data = $default;
		if ( $base && $file && 0 === strpos( $file, $base ) && is_file( $file ) ) {
			$decoded = json_decode( (string) file_get_contents( $file ), true );
			if ( is_array( $decoded ) ) {
				$data = $decoded;
			} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( '[APP] Invalid JSON in admin/data/' . $name . '.json' );
			}
		}
		$cache[ $name ] = apply_filters( 'app_data_' . $name, $data );
		return $cache[ $name ];
	}

	public static function link( $url ) {
		$url = (string) $url;
		if ( '' === $url ) {
			return '#';
		}
		if ( '#' === $url[0] || preg_match( '#^https?://#i', $url ) ) {
			return $url;
		}
		return home_url( $url );
	}

	public static function page_url( $page_key ) {
		$pages = App_Theme::config( 'pages' );
		if ( ! isset( $pages[ $page_key ] ) ) {
			return home_url( '/' );
		}
		if ( ! empty( $pages[ $page_key ]['front'] ) ) {
			return home_url( '/' );
		}
		return home_url( '/' . $page_key . '/' );
	}

	public static function request_path() {
		$raw = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		return trim( (string) parse_url( $raw, PHP_URL_PATH ), '/' );
	}

	public static function svg( $name ) {
		$file = realpath( NT_THEME_DIR . '/assets/svg/' . $name . '.svg' );
		if ( $file && is_file( $file ) ) {
			echo file_get_contents( $file );
		}
	}
}
