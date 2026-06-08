<?php
defined( 'ABSPATH' ) || exit;

// ── Includes - order matters ──────────────────────────────────────────────────
require_once get_template_directory() . '/includes/common_terms.php';  // first - defines constants
require_once get_template_directory() . '/includes/core_settings.php';
require_once get_template_directory() . '/includes/usefulfuntions.php';
require_once get_template_directory() . '/includes/mock-data.php';
require_once get_template_directory() . '/includes/helpers.php';
require_once get_template_directory() . '/includes/carousel-helpers.php';
require_once get_template_directory() . '/schema/class-schema.php';
require_once get_template_directory() . '/schema/class-data.php';

// ── Real-data classes (read from real_data/csv/ and real_data/json/) ─────────
require_once get_template_directory() . '/includes/data/class-real-loader.php';
require_once get_template_directory() . '/includes/data/class-site-data.php';
require_once get_template_directory() . '/includes/data/class-home-data.php';
require_once get_template_directory() . '/includes/data/class-menu-data.php';
require_once get_template_directory() . '/includes/data/class-about-data.php';
require_once get_template_directory() . '/includes/data/class-story-data.php';
require_once get_template_directory() . '/includes/data/class-hire-data.php';
require_once get_template_directory() . '/includes/data/class-order-data.php';
require_once get_template_directory() . '/includes/data/class-booking-data.php';
require_once get_template_directory() . '/includes/data/class-franchise-data.php';
require_once get_template_directory() . '/includes/data/class-contact-data.php';
require_once get_template_directory() . '/includes/data/class-blog-data.php';
require_once get_template_directory() . '/includes/data/class-shared-data.php';
require_once get_template_directory() . '/includes/class-rules-config.php';
require_once get_template_directory() . '/includes/class-theme-admin.php';
require_once get_template_directory() . '/mail/common_contact.php';
require_once get_template_directory() . '/admin/theme-reset.php';

// ── Clear stale nav data from other themes (runs once per session) ────────────
add_action( 'init', function () {
	if ( get_option( 'ch_nav_cleaned_v2' ) ) return;
	delete_option( 'ah_cms_navigation' );
	delete_option( 'ch_theme_navigation' );
	delete_option( 'ah_cms_nav_cta' );
	delete_option( 'ch_nav_cta' );
	update_option( 'ch_nav_cleaned_v2', true );
} );

// ── Page definitions ──────────────────────────────────────────────────────────
function ch_get_page_definitions(): array {
	return [
		[ 'title' => 'Home',               'slug' => 'home',           'template' => 'front-page.php',         'front' => true  ],
		[ 'title' => 'About',              'slug' => 'about',          'template' => 'page-about.php'                            ],
		[ 'title' => 'Why Sugarcane',      'slug' => 'why-sugarcane',  'template' => 'page-why-sugarcane.php'                    ],
		[ 'title' => 'Events',             'slug' => 'events',         'template' => 'page-events.php'                           ],
		[ 'title' => 'Franchise',          'slug' => 'franchise',      'template' => 'page-franchise.php'                        ],
		[ 'title' => 'Our Story',          'slug' => 'our-story',      'template' => 'page-story.php'                            ],
		[ 'title' => 'FAQs',               'slug' => 'faqs',           'template' => 'page-faqs.php'                             ],
		[ 'title' => 'Contact',            'slug' => 'contact',        'template' => 'page-contact.php'                          ],
		[ 'title' => 'Blog',               'slug' => 'blog',           'template' => 'page-blog.php' ],
		[ 'title' => 'Testing',            'slug' => 'testing',        'template' => 'page-testing.php' ],
		[ 'title' => 'Order To Deliver',   'slug' => 'ordertodeliver', 'template' => 'page-ordertodeliver.php' ],
		[ 'title' => 'Coming Soon',        'slug' => 'coming',         'template' => 'page-coming.php' ],
	];
}

// ── Create a single page if it doesn't exist ──────────────────────────────────
function ch_maybe_create_page( array $def ): int {
	$existing = get_page_by_path( $def['slug'] );
	if ( $existing ) {
		// Ensure the correct template is set
		if ( get_post_meta( $existing->ID, '_wp_page_template', true ) !== $def['template'] ) {
			update_post_meta( $existing->ID, '_wp_page_template', $def['template'] );
		}
		return $existing->ID;
	}

	$id = wp_insert_post( [
		'post_title'  => $def['title'],
		'post_name'   => $def['slug'],
		'post_status' => 'publish',
		'post_type'   => 'page',
		'post_author' => get_current_user_id() ?: 1,
	] );

	if ( $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, '_wp_page_template', $def['template'] );
		return $id;
	}
	return 0;
}

// ── Create all theme pages + configure reading settings ───────────────────────
function ch_setup_theme_pages(): void {
	$front_id = 0;

	foreach ( ch_get_page_definitions() as $def ) {
		$id = ch_maybe_create_page( $def );
		if ( $id && ! empty( $def['front'] ) ) {
			$front_id = $id;
		}
	}

	// Set the static front page if not already configured
	if ( $front_id && (int) get_option( 'page_on_front' ) !== $front_id ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $front_id );
	}

	// Flag that permalinks need flushing
	update_option( 'ch_needs_flush', true );
}

// ── Flush rewrite rules once after page creation ──────────────────────────────
add_action( 'init', function () {
	if ( get_option( 'ch_needs_flush' ) ) {
		flush_rewrite_rules();
		delete_option( 'ch_needs_flush' );
	}
} );

// ── Run on theme activation ───────────────────────────────────────────────────
add_action( 'after_switch_theme', function () {
	ch_setup_theme_pages();
	if ( class_exists( 'CH_Schema' ) ) {
		CH_Schema::create_all();
	}
} );

// ── Also run on admin_init to catch any missing pages ─────────────────────────
// (runs once per WP version bump or if option is missing)
add_action( 'admin_init', function () {
	// Bump this key whenever new page templates are added to ch_get_page_definitions().
	$done_key = 'ch_pages_created_v4';
	if ( get_option( $done_key ) ) return;
	if ( ! current_theme_supports( 'title-tag' ) ) return; // not our theme
	ch_setup_theme_pages();
	update_option( $done_key, true );
} );

// ── Manual trigger: ?ch_regen_pages=1 in WP admin ────────────────────────────
add_action( 'admin_init', function () {
	if ( ! isset( $_GET['ch_regen_pages'] ) ) return;
	if ( ! current_user_can( 'manage_options' ) ) return;
	delete_option( 'ch_pages_created_v4' );
	ch_setup_theme_pages();
	wp_safe_redirect( admin_url( 'themes.php?ch_regen=1' ) );
	exit;
} );

// Admin notice after manual regen
add_action( 'admin_notices', function () {
	if ( ! isset( $_GET['ch_regen'] ) ) return;
	echo '<div class="notice notice-success is-dismissible"><p><strong>The Cane House:</strong> All theme pages created/updated and permalink rules flushed.</p></div>';
} );

// ── Init Theme Admin ──────────────────────────────────────────────────────────
if ( is_admin() ) {
	CH_Theme_Admin::init();
}

// Navigation & Footer are managed by the CMS plugin (ah_cms_navigation /
// ah_cms_nav_cta / ah_cms_footer). Demo content for nav, footer, story cards
// and certifications is seeded from CSVs via Install Mock Data (see
// mock_data/csv/ and CH_Theme_Seeder).

// ── Theme Settings Save Handler ───────────────────────────────────────────────
add_action( 'admin_post_ch_theme_settings', function () {
	check_admin_referer( 'ch_theme_settings' );
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

	$existing = get_option( 'ch_site_settings', [] );
	if ( is_string( $existing ) ) $existing = json_decode( $existing, true ) ?: [];

	// Standard text/url/tel fields
	$text_fields = [
		'phone', 'email', 'address', 'website', 'whatsapp', 'tagline',
		'instagram_url', 'facebook_url', 'youtube_url',
		'cert_heading', 'cert_subtext'
	];
	foreach ( $text_fields as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			$existing[ $key ] = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
		}
	}

	// Textarea
	if ( isset( $_POST['cert_subtext'] ) ) {
		$existing['cert_subtext'] = sanitize_textarea_field( wp_unslash( $_POST['cert_subtext'] ) );
	}


	// Certifications array
	if ( ! empty( $_POST['cert'] ) && is_array( $_POST['cert'] ) ) {
		$certs = [];
		foreach ( $_POST['cert'] as $cert ) {
			$title = sanitize_text_field( wp_unslash( $cert['title'] ?? '' ) );
			if ( ! $title ) continue;
			$certs[] = [
				'icon'  => sanitize_text_field( wp_unslash( $cert['icon']  ?? '' ) ),
				'title' => $title,
				'desc'  => sanitize_text_field( wp_unslash( $cert['desc']  ?? '' ) ),
				'badge' => sanitize_text_field( wp_unslash( $cert['badge'] ?? '' ) ),
			];
		}
		$existing['certifications'] = wp_json_encode( $certs );
	}

	// Schema settings
	if ( ! empty( $_POST['schema'] ) && is_array( $_POST['schema'] ) ) {
		$schema_keys = [ 'enabled', 'name', 'description', 'phone', 'email', 'area_served' ];
		$schema      = [];
		foreach ( $schema_keys as $k ) {
			$schema[ $k ] = sanitize_text_field( wp_unslash( $_POST['schema'][ $k ] ?? '' ) );
		}
		$schema['enabled'] = isset( $_POST['schema']['enabled'] ) ? '1' : '0';
		$existing['schema'] = wp_json_encode( $schema );
	}

	update_option( 'ch_site_settings', $existing );

	$redirect = wp_get_referer() ?: admin_url( 'admin.php?page=ch-theme-settings' );
	wp_safe_redirect( add_query_arg( 'saved', '1', $redirect ) );
	exit;
} );

// ── Schema JSON-LD builder ────────────────────────────────────────────────────
function ch_build_schema_json( bool $include_reviews_data = true ): array {
	$sc     = ch_get_schema_settings();
	$s      = ch_get_settings();
	$schema = [
		'@context' => 'https://schema.org',
		'@type'    => 'FoodEstablishment',
		'name'     => $sc['name'] ?: get_bloginfo( 'name' ),
		'url'      => home_url( '/' ),
		'logo'     => $sc['logo_url'] ?: get_template_directory_uri() . '/assets/images/logo.png',
		'image'    => $sc['logo_url'] ?: get_template_directory_uri() . '/assets/images/logo.png',
		'description'    => $sc['description'] ?: '',
		'areaServed'     => $sc['area_served'] ?: 'United Kingdom',
		'servesCuisine'  => 'Fresh Sugarcane Juice',
		'currenciesAccepted' => 'GBP',
		'hasMap'     => '',
	];

	if ( $sc['phone'] ) $schema['telephone'] = $sc['phone'];
	if ( $sc['email'] ) $schema['email']     = $sc['email'];



	// Social profiles
	$sameAs = array_filter( [
		$s['instagram_url'] ?? '',
		$s['facebook_url']  ?? '',
		$s['youtube_url']   ?? '',
	] );
	if ( $sameAs ) $schema['sameAs'] = array_values( $sameAs );



	return array_filter( $schema, fn( $v ) => $v !== '' && $v !== null );
}

// Schema JSON-LD is output directly in parts/header.php inside <head>
// to guarantee placement and avoid any \n text nodes in <body>.

// ── Theme Setup ───────────────────────────────────────────────────────────────
add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'ch-theme', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'script', 'style' ] );
	add_theme_support( 'custom-logo' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );

	add_image_size( 'ch-card',   600,  400, true );
	add_image_size( 'ch-hero',  1600,  800, true );
	add_image_size( 'ch-thumb',  480,  320, true );

	register_nav_menus( [
		'primary' => __( 'Primary Navigation', 'ch-theme' ),
		'footer'  => __( 'Footer Navigation',  'ch-theme' ),
	] );
} );

// ── Enqueue Assets ────────────────────────────────────────────────────────────
add_action( 'wp_enqueue_scripts', function () {
	$theme_v = wp_get_theme()->get( 'Version' );
	$dir     = get_template_directory();
	$uri     = get_template_directory_uri();

	// Version each local asset by its file mtime so edits always bust the
	// browser cache (the static theme version never changed between edits).
	$ver = static function ( string $rel ) use ( $dir, $theme_v ) {
		$path = $dir . $rel;
		return file_exists( $path ) ? (string) filemtime( $path ) : $theme_v;
	};

	wp_enqueue_style(
		'ch-google-fonts',
		'https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,400;0,600;0,700;0,800;0,900;1,700&family=Poppins:wght@300;400;500;600;700;800&display=swap',
		[],
		null
	);

	wp_enqueue_style( 'ch-variables',  $uri . '/assets/css/variables.css',  [ 'ch-google-fonts' ], $ver( '/assets/css/variables.css' ) );
	wp_enqueue_style( 'ch-base',       $uri . '/assets/css/base.css',       [ 'ch-variables' ],    $ver( '/assets/css/base.css' ) );
	wp_enqueue_style( 'ch-components', $uri . '/assets/css/components.css', [ 'ch-base' ],         $ver( '/assets/css/components.css' ) );
	wp_enqueue_style( 'ch-layout',     $uri . '/assets/css/layout.css',     [ 'ch-components' ],   $ver( '/assets/css/layout.css' ) );
	wp_enqueue_style( 'ch-forms',      $uri . '/assets/css/forms.css',      [ 'ch-base' ],         $ver( '/assets/css/forms.css' ) );
	wp_enqueue_style( 'ch-animations', $uri . '/assets/css/animations.css', [ 'ch-base' ],         $ver( '/assets/css/animations.css' ) );
	wp_enqueue_style( 'ch-cursors', $uri . '/assets/css/ch-cursors.css',    [ 'ch-base' ],         $ver( '/assets/css/ch-cursors.css' ) );
	wp_enqueue_style( 'ch-carousel', $uri . '/assets/css/carousel.css',    [ 'ch-base' ],         $ver( '/assets/css/carousel.css' ) );
	wp_enqueue_style( 'ch-carousel-video', $uri . '/assets/css/carousel-video.css', [ 'ch-base' ], $ver( '/assets/css/carousel-video.css' ) );
	wp_enqueue_style( 'ch-carousel-mini-video', $uri . '/assets/css/carousel-mini-video.css', [ 'ch-base' ], $ver( '/assets/css/carousel-mini-video.css' ) );

	wp_enqueue_style( 'ch-style',      get_stylesheet_uri(),                [ 'ch-layout' ],       $ver( '/style.css' ) );

	wp_enqueue_script( 'ch-main',  $uri . '/assets/js/main.js',  [ 'jquery' ], $ver( '/assets/js/main.js' ), true );
	wp_enqueue_script( 'ch-form-step-modal', $uri . '/assets/js/form-step-modal.js', [ 'ch-main' ], $ver( '/assets/js/form-step-modal.js' ), true );
	wp_enqueue_script( 'ch-forms', $uri . '/assets/js/forms.js', [ 'ch-main', 'ch-form-step-modal' ], $ver( '/assets/js/forms.js' ), true );
	wp_enqueue_script( 'ch-history-info', $uri . '/assets/js/history-info.js', [ 'ch-main' ], $ver( '/assets/js/history-info.js' ), true );
	wp_enqueue_script( 'ch-carousel', $uri . '/assets/js/carousel.js', [ 'ch-main' ], $ver( '/assets/js/carousel.js' ), true );
	wp_enqueue_script( 'ch-carousel-video', $uri . '/assets/js/carousel-video.js', [ 'ch-main' ], $ver( '/assets/js/carousel-video.js' ), true );
	wp_enqueue_script( 'ch-carousel-mini-video', $uri . '/assets/js/carousel-mini-video.js', [ 'ch-main' ], $ver( '/assets/js/carousel-mini-video.js' ), true );

	wp_localize_script( 'ch-forms', 'chTheme', [
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'ch_frontend_nonce' ),
		'siteUrl' => esc_url( home_url( '/' ) ),
	] );
} );

// ── Slug aliases: redirect alternative slugs to canonical URLs ────────────────
add_action( 'template_redirect', function () {

    if ( !defined( 'COMING_SOON' ) || ! COMING_SOON ) {
        return; // ← maintenance mode is off, do nothing
    }

    $pages       = ch_get_page_definitions();
    $coming_page = array_filter( $pages, fn( $p ) => $p['slug'] === 'coming' );
    $coming_slug = array_values( $coming_page )[0]['slug'] ?? 'coming';

    $maintenance_url = home_url( '/' . $coming_slug . '/' );

    if ( isset( $GLOBALS['pagenow'] ) && $GLOBALS['pagenow'] === 'wp-login.php' ) {
        return;
    }

    if ( is_page( $coming_slug ) ) {
        return;
    }

    if ( current_user_can( 'administrator' ) ) {
        return;
    }

    wp_redirect( $maintenance_url );
    exit;

}, 5 );

add_action( 'template_redirect', function () {
	$slug_aliases = [
		'home'       => '/',
		'index'      => '/',
		'about-us'   => '/about/',
		'who-we-are' => '/about/',
		'hire'       => '/events/',
		'hire-us'    => '/events/',
		'book'       => '/events/',
		'contact-us' => '/contact/',
		'get-in-touch' => '/contact/',
	];

	$request = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );

	if ( isset( $slug_aliases[ $request ] ) ) {
		wp_redirect( home_url( $slug_aliases[ $request ] ), 301 );
		exit;
	}
},10 );

/* ── 500 error helper ────────────────────────────────────────────────────────
 * Usage anywhere in the theme/plugin:
 *   ch_render_500();                  // generic 500
 *   ch_render_500( $exception );      // pass a caught Throwable for debug panel
 *
 * In production the debug panel is hidden unless the visitor is an admin
 * AND adds ?debug=true to the URL.
 * ──────────────────────────────────────────────────────────────────────────*/
function ch_render_500( ?Throwable $e = null ): void {
	global $ch_500_exception;
	$ch_500_exception = $e;
	status_header( 500 );
	nocache_headers();
	include get_template_directory() . '/500.php';
	exit;
}
