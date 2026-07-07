<?php
/**
 * core/helpers.php - Small shared helpers used across templates.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Require a theme file by relative path with realpath containment,
 * so a registry entry can never include a file outside the theme.
 *
 * @return bool True when the file was loaded.
 */
function nt_require_theme_file( $rel ) {
	$base = realpath( NT_THEME_DIR );
	$file = realpath( NT_THEME_DIR . '/' . ltrim( (string) $rel, '/' ) );
	if ( $base && $file && 0 === strpos( $file, $base ) && is_file( $file ) ) {
		require_once $file;
		return true;
	}
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( '[NT] Missing theme file: ' . $rel );
	}
	return false;
}

/**
 * Render a component from /components/.
 *
 *   nt_component( 'parts/main_header' );
 *   nt_component( 'cards/post_card', array( 'post_id' => 42 ) );
 *
 * $context keys become local variables inside the component file.
 * Realpath containment: a tampered $name can never escape /components/.
 */
function nt_component( $name, $context = array() ) {
	$base = realpath( NT_THEME_DIR . '/components' );
	$file = realpath( NT_THEME_DIR . '/components/' . $name . '.php' );
	if ( ! $base || ! $file || 0 !== strpos( $file, $base ) || ! is_file( $file ) ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[NT] Component not found: ' . $name );
		}
		return;
	}
	extract( $context, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract
	include $file;
}

/**
 * Read one field from a saved admin option group (config/admin.php).
 *
 *   nt_option( 'general', 'phone', NT_BRAND_PHONE )
 *   nt_option( 'social' )  -> the whole group array
 */
function nt_option( $group, $key = null, $default = '' ) {
	$admin    = nt_config( 'admin' );
	$opt_name = $admin['options'][ $group ]['option'] ?? 'nt_' . sanitize_key( $group );
	$data     = get_option( $opt_name, array() );
	$data     = is_array( $data ) ? $data : array();

	if ( null === $key ) {
		return $data;
	}
	return ( isset( $data[ $key ] ) && '' !== $data[ $key ] ) ? $data[ $key ] : $default;
}

/**
 * Read a JSON data file from /admin/data/ as an associative array.
 *
 *   nt_data( 'home' )          -> admin/data/home.json
 *   nt_data( 'terms', array() )
 *
 * Used for demo/seed content and as a FALLBACK when no DB/admin value exists
 * yet, so pages render complete from day one ("fast availability"). Cached
 * per request; alterable via the 'nt_data_{name}' filter. Realpath-guarded.
 */
function nt_data( $name, $default = array() ) {
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
			error_log( '[NT] Invalid JSON in admin/data/' . $name . '.json' );
		}
	}
	$cache[ $name ] = apply_filters( 'nt_data_' . $name, $data );
	return $cache[ $name ];
}

/**
 * Normalize a link: '#', full http(s) URLs pass through; anything else is
 * treated as a site-relative path.
 */
function nt_link( $url ) {
	$url = (string) $url;
	if ( '' === $url ) {
		return '#';
	}
	if ( '#' === $url[0] || preg_match( '#^https?://#i', $url ) ) {
		return $url;
	}
	return home_url( $url );
}

/**
 * URL of a registered page by its config/pages.php key.
 *
 *   nt_page_url( 'contact' )  ->  https://site.tld/contact/
 */
function nt_page_url( $page_key ) {
	$pages = nt_config( 'pages' );
	if ( ! isset( $pages[ $page_key ] ) ) {
		return home_url( '/' );
	}
	if ( ! empty( $pages[ $page_key ]['front'] ) ) {
		return home_url( '/' );
	}
	return home_url( '/' . $page_key . '/' );
}

/**
 * Current request path with slashes trimmed ('' for the site root).
 */
function nt_request_path() {
	$raw = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	return trim( (string) parse_url( $raw, PHP_URL_PATH ), '/' );
}
