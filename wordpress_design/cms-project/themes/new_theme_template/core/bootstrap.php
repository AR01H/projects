<?php
/**
 * core/bootstrap.php - Wires the whole theme together.
 *
 * Order: constants -> config loader -> engines -> site includes -> hooks.
 * This file (and everything in /core/) is GENERIC - per-site behaviour is
 * changed by editing /config/ arrays, never by editing engines.
 */

defined( 'ABSPATH' ) || exit;

// 1. Constants (the one config file that does not return an array).
require_once __DIR__ . '/../config/theme.php';

/**
 * Config loader. Reads /config/{name}.php once, caches it, and passes it
 * through the 'nt_config_{name}' filter so child themes / plugins can alter
 * any registry without touching theme files (Drupal-style alter hooks).
 */
function nt_config( $name ) {
	static $cache = array();
	$name = basename( (string) $name, '.php' );
	if ( isset( $cache[ $name ] ) ) {
		return $cache[ $name ];
	}
	$file = NT_THEME_DIR . '/config/' . $name . '.php';
	$data = is_file( $file ) ? require $file : array();
	$cache[ $name ] = apply_filters( 'nt_config_' . $name, is_array( $data ) ? $data : array() );
	return $cache[ $name ];
}

// 2. Engines (always loaded, order matters only for helpers).
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/router.php';
require_once __DIR__ . '/assets.php';
require_once __DIR__ . '/ajax.php';
require_once __DIR__ . '/rest.php';
require_once __DIR__ . '/redirects.php';

if ( is_admin() ) {
	require_once __DIR__ . '/admin.php';
	nt_admin_boot();
}

// 3. Site code includes (config/files.php map, loops per context).
nt_include_mapped_files();
function nt_include_mapped_files() {
	$map     = nt_config( 'files' );
	$buckets = array( 'always' );
	$buckets[] = is_admin() ? 'admin' : 'front';

	foreach ( $buckets as $bucket ) {
		foreach ( (array) ( $map[ $bucket ] ?? array() ) as $rel ) {
			nt_require_theme_file( $rel );
		}
	}
}

// 4. Hooks - each engine exposes one entry function; loops do the rest.
add_action( 'after_setup_theme',   'nt_setup_theme' );
add_action( 'wp_enqueue_scripts',  'nt_enqueue_global_assets' );
add_action( 'wp_enqueue_scripts',  'nt_enqueue_page_assets', 20 );
add_filter( 'template_include',    'nt_router_static_pages', 98 );
add_filter( 'template_include',    'nt_router_dynamic_routes', 99 );
add_filter( 'redirect_canonical',  'nt_router_suppress_canonical', 1, 2 );
add_action( 'template_redirect',   'nt_handle_redirects', 1 );
add_action( 'init',                'nt_register_ajax_actions' );
add_action( 'rest_api_init',       'nt_register_rest_routes' );

// Activation tasks: create registered pages, install registered DB tables,
// set front page, flush rewrites.
add_action( 'after_switch_theme', 'nt_sync_pages' );
add_action( 'after_switch_theme', 'nt_db_install_all' );
add_action( 'after_switch_theme', 'flush_rewrite_rules' );

/**
 * Theme registration - loops config/setup.php.
 */
function nt_setup_theme() {
	$setup = nt_config( 'setup' );

	foreach ( (array) ( $setup['supports'] ?? array() ) as $feature ) {
		add_theme_support( $feature );
	}
	if ( ! empty( $setup['html5'] ) ) {
		add_theme_support( 'html5', (array) $setup['html5'] );
	}

	$menus = array();
	foreach ( (array) ( $setup['menus'] ?? array() ) as $location => $label ) {
		$menus[ $location ] = __( $label, NT_TEXT_DOMAIN ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
	}
	if ( $menus ) {
		register_nav_menus( $menus );
	}

	foreach ( (array) ( $setup['image_sizes'] ?? array() ) as $name => $size ) {
		add_image_size( $name, $size[0], $size[1], ! empty( $size[2] ) );
	}

	load_theme_textdomain( NT_TEXT_DOMAIN, NT_THEME_DIR . '/languages' );
}
