<?php
/**
 * core/assets.php - Asset loading driven by config/assets.php + the per-page
 * css/js declared in config/pages.php and config/routes.php.
 *
 * Everything is filemtime-versioned (save a file -> browsers refetch it
 * immediately) and existence-checked (a missing file is skipped, never 404s).
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue one theme-relative style/script pair of arrays.
 */
function nt_enqueue_list( $list, $type, $handle_prefix = 'nt' ) {
	foreach ( (array) $list as $key => $rel ) {
		$path = NT_THEME_DIR . '/' . ltrim( (string) $rel, '/' );
		if ( ! file_exists( $path ) ) {
			continue;
		}
		$handle = is_string( $key ) ? $handle_prefix . '-' . $key : $handle_prefix . '-' . basename( $rel, '.' . $type );
		$ver    = (string) filemtime( $path );
		if ( 'css' === $type ) {
			wp_enqueue_style( $handle, NT_THEME_URI . '/' . ltrim( $rel, '/' ), array(), $ver );
		} else {
			wp_enqueue_script( $handle, NT_THEME_URI . '/' . ltrim( $rel, '/' ), array(), $ver, true );
		}
	}
}

/**
 * Global assets + the window.ntSite JS config object.
 */
function nt_enqueue_global_assets() {
	$assets = nt_config( 'assets' );

	// External CDN styles first.
	foreach ( (array) ( $assets['external_css'] ?? array() ) as $handle => $ext ) {
		wp_enqueue_style( 'nt-ext-' . $handle, $ext['src'], array(), $ext['ver'] ?? null );
	}

	nt_enqueue_list( $assets['css'] ?? array(), 'css' );
	nt_enqueue_list( $assets['js'] ?? array(), 'js' );

	// One config object for ALL JS: ajax url, rest base and a nonce per
	// registered ajax action (keys match config/ajax.php). NT.ajax()/NT.rest()
	// in common.js consume this - templates never hand-build nonces.
	$nonces = array();
	foreach ( nt_config( 'ajax' ) as $action => $def ) {
		if ( ! isset( $def['nonce'] ) || false !== $def['nonce'] ) {
			$nonces[ $action ] = wp_create_nonce( 'nt_ajax_' . $action );
		}
	}

	$rest_cfg = nt_config( 'rest' );
	$ns       = (string) ( $rest_cfg['namespace'] ?? 'nt/v1' );

	wp_add_inline_script(
		'nt-common',
		'window.ntSite=' . wp_json_encode( array(
			'homeUrl'   => home_url( '/' ),
			'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
			'restUrl'   => rest_url( $ns ),
			'restNonce' => wp_create_nonce( 'wp_rest' ),
			'nonces'    => $nonces,
		) ) . ';',
		'before'
	);
}

/**
 * Per-page assets. Uses the registry key stamped by the router
 * (nt_active_page / nt_active_route) and falls back to is_page_template()
 * for pages WP resolved directly from the _wp_page_template meta.
 */
function nt_enqueue_page_assets() {
	$def = nt_active_page_def();
	if ( ! $def ) {
		return;
	}
	nt_enqueue_list( $def['css'] ?? array(), 'css', 'nt-page' );
	nt_enqueue_list( $def['js'] ?? array(), 'js', 'nt-page' );
}

/**
 * The registry entry for the page being rendered, or null.
 * Works for real pages, virtual pages and dynamic routes.
 */
function nt_active_page_def() {
	$pages = nt_config( 'pages' );

	$active = (string) get_query_var( 'nt_active_page', '' );
	if ( '' !== $active && isset( $pages[ $active ] ) ) {
		return $pages[ $active ];
	}

	$route = (string) get_query_var( 'nt_active_route', '' );
	if ( '' !== $route ) {
		$routes = nt_config( 'routes' );
		if ( isset( $routes[ $route ] ) ) {
			return $routes[ $route ];
		}
	}

	// Fallback: template assigned through page meta (router not involved).
	foreach ( $pages as $def ) {
		if ( ! empty( $def['template'] ) && is_page_template( $def['template'] ) ) {
			return $def;
		}
	}

	return null;
}
