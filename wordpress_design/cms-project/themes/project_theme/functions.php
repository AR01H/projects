<?php
/**
 * project_theme — functions.php
 *
 * Theme setup, asset enqueueing, and page auto-provisioning.
 * Follows the same pattern as ah_canehouse/functions.php.
 *
 * Prefix: pt_ (project theme — replace with client-specific prefix before launch)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ═══════════════════════════════════════════════════════════════
 * 1. THEME SETUP
 * ═════════════════════════════════════════════════════════════*/

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'gallery', 'caption' ] );
	add_theme_support( 'custom-logo' );
	add_theme_support( 'menus' );

	register_nav_menus( [
		'primary' => __( 'Primary Navigation', 'project_theme' ),
		'footer'  => __( 'Footer Navigation',  'project_theme' ),
	] );
} );

/* ═══════════════════════════════════════════════════════════════
 * 2. ENQUEUE ASSETS
 * ═════════════════════════════════════════════════════════════*/

add_action( 'wp_enqueue_scripts', function () {
	$v   = wp_get_theme()->get( 'Version' ) ?: '1.0.0';
	$dir = get_template_directory_uri() . '/assets';

	/* CSS — chain: variables → common → components → page-specific */
	wp_enqueue_style( 'pt-variables',  "$dir/css/vairables.css",  [],                    $v );
	wp_enqueue_style( 'pt-common',     "$dir/css/common.css",     [ 'pt-variables' ],    $v );
	wp_enqueue_style( 'pt-components', "$dir/css/components.css", [ 'pt-common' ],       $v );
	wp_enqueue_style( 'pt-carousel-video',      "$dir/css/carousel-video.css",      [ 'pt-components' ], $v );
	wp_enqueue_style( 'pt-carousel-mini-video', "$dir/css/carousel-mini-video.css", [ 'pt-components' ], $v );
	wp_enqueue_style( 'pt-form-step-modal',     "$dir/css/form-step-modal.css",     [ 'pt-components' ], $v );

	/* Home-page styles — only on the static front page */
	if ( is_front_page() ) {
		wp_enqueue_style( 'pt-home', "$dir/css/home.css", [ 'pt-components', 'pt-carousel-video', 'pt-carousel-mini-video' ], $v );
	}

	/* Stories CSS — loaded only on the stories page template */
	if ( is_page_template( 'page-stories.php' ) ) {
		wp_enqueue_style( 'pt-stories', "$dir/css/stories.css", [ 'pt-components' ], $v );
	}

	/* Main JS */
	if ( file_exists( get_template_directory() . '/assets/js/main.js' ) ) {
		wp_enqueue_script( 'pt-main', "$dir/js/main.js", [], $v, true );
	}

	/* Carousel JS */
	wp_enqueue_script( 'pt-carousel-video',      "$dir/js/carousel-video.js",      [], $v, true );
	wp_enqueue_script( 'pt-carousel-mini-video', "$dir/js/carousel-mini-video.js", [], $v, true );

	/* Form step-modal JS + ptTheme global */
	wp_enqueue_script( 'pt-form-step-modal', "$dir/js/form-step-modal.js", [], $v, true );
	wp_localize_script( 'pt-form-step-modal', 'ptTheme', [
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'pt_frontend_nonce' ),
		'siteUrl' => esc_url( home_url( '/' ) ),
	] );
} );

/* ═══════════════════════════════════════════════════════════════
 * 3. PAGE DEFINITIONS
 * Pages are auto-created on theme activation and when the
 * version key below is bumped. Each entry maps a slug → template.
 * ═════════════════════════════════════════════════════════════*/

/**
 * Returns the list of pages this theme manages.
 * Add new pages here; bump PT_PAGES_VERSION to trigger creation.
 */
function pt_get_page_definitions(): array {
	return [
		[
			'title'    => 'Home',
			'slug'     => 'home',
			'template' => 'front-page.php',
			'front'    => true,
		],
		[
			'title'    => 'Stories',
			'slug'     => 'stories',
			'template' => 'page-stories.php',
		],
		/* Add more pages here as the theme grows:
		[
			'title'    => 'About',
			'slug'     => 'about',
			'template' => 'page-about.php',
		],
		*/
	];
}

/* Bump this string whenever new pages are added to pt_get_page_definitions(). */
define( 'PT_PAGES_VERSION', 'pt_pages_v1' );

/* ═══════════════════════════════════════════════════════════════
 * 4. PAGE AUTO-PROVISIONING
 * ═════════════════════════════════════════════════════════════*/

/**
 * Creates a single page if it does not already exist;
 * if it does exist, ensures the correct template is assigned.
 */
function pt_maybe_create_page( array $def ): int {
	$existing = get_page_by_path( $def['slug'] );

	if ( $existing ) {
		update_post_meta( $existing->ID, '_wp_page_template', $def['template'] );
		return (int) $existing->ID;
	}

	$id = wp_insert_post( [
		'post_title'   => $def['title'],
		'post_name'    => $def['slug'],
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_content' => '',
	], true );

	if ( is_wp_error( $id ) ) return 0;

	update_post_meta( $id, '_wp_page_template', $def['template'] );
	return (int) $id;
}

/**
 * Creates all defined pages and optionally sets the front page.
 */
function pt_setup_pages(): void {
	$front_id = 0;

	foreach ( pt_get_page_definitions() as $def ) {
		$id = pt_maybe_create_page( $def );
		if ( $id && ! empty( $def['front'] ) ) {
			$front_id = $id;
		}
	}

	if ( $front_id ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $front_id );
	}

	/* Signal that permalinks need flushing */
	update_option( 'pt_needs_flush', true );
}

/* Run once when the theme is first activated */
add_action( 'after_switch_theme', 'pt_setup_pages' );

/* Re-run on every admin load until the current version key is recorded */
add_action( 'admin_init', function () {
	if ( get_option( PT_PAGES_VERSION ) ) return;
	if ( ! current_theme_supports( 'title-tag' ) ) return; /* confirms this theme is active */

	pt_setup_pages();
	update_option( PT_PAGES_VERSION, true );
} );

/* Flush rewrite rules once after page creation */
add_action( 'init', function () {
	if ( get_option( 'pt_needs_flush' ) ) {
		flush_rewrite_rules();
		delete_option( 'pt_needs_flush' );
	}
} );

/* Manual trigger — visit WP admin with ?pt_regen_pages=1 */
add_action( 'admin_init', function () {
	if ( isset( $_GET['pt_regen_pages'] ) && current_user_can( 'manage_options' ) ) {
		delete_option( PT_PAGES_VERSION );
		pt_setup_pages();
		wp_safe_redirect( admin_url( 'themes.php?pt_regen=1' ) );
		exit;
	}
} );

/* ═══════════════════════════════════════════════════════════════
 * 5. ADMIN — Stories manager
 * ═════════════════════════════════════════════════════════════*/

if ( is_admin() ) {
	$pt_admin_classes = [
		'/includes/admin/class-pt-stories-db.php',
		'/includes/admin/class-pt-stories-admin.php',
	];
	foreach ( $pt_admin_classes as $file ) {
		$path = get_template_directory() . $file;
		if ( file_exists( $path ) ) require_once $path;
	}
	if ( class_exists( 'PT_Stories_Admin' ) ) {
		PT_Stories_Admin::init();
	}
}

/* ═══════════════════════════════════════════════════════════════
 * 6. REST API
 * ═════════════════════════════════════════════════════════════*/

$pt_api = get_template_directory() . '/includes/api/class-pt-stories-api.php';
if ( file_exists( $pt_api ) ) {
	require_once $pt_api;
	PT_Stories_API::init();
}

/* ═══════════════════════════════════════════════════════════════
 * 6. INCLUDES
 * Load helper files as the theme grows.
 * ═════════════════════════════════════════════════════════════*/

$pt_includes = [
	'/includes/core_settings.php',
	'/includes/core_titles.php',
	'/includes/helper_functions.php',
	'/includes/data/class-pt-real-loader.php',
];

foreach ( $pt_includes as $file ) {
	$path = get_template_directory() . $file;
	if ( file_exists( $path ) ) {
		require_once $path;
	}
}
