<?php
defined( 'ABSPATH' ) || exit;

// ── Includes - order matters ──────────────────────────────────────────────────
require_once get_template_directory() . '/includes/common_terms.php';  // first - defines constants
require_once get_template_directory() . '/includes/mock-data.php';
require_once get_template_directory() . '/includes/helpers.php';
require_once get_template_directory() . '/schema/class-schema.php';
require_once get_template_directory() . '/schema/class-data.php';
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
		[ 'title' => 'Services',           'slug' => 'services',       'template' => 'page-services.php'                         ],
		[ 'title' => 'Our Juices',         'slug' => 'our-juices',     'template' => 'page-our-juices.php'                       ],
		[ 'title' => 'Why Sugarcane',      'slug' => 'why-sugarcane',  'template' => 'page-why-sugarcane.php'                    ],
		[ 'title' => 'Events & Hire',      'slug' => 'events',         'template' => 'page-events.php'                           ],
		[ 'title' => 'Client Stories',     'slug' => 'client-stories', 'template' => 'page-client-stories.php'                   ],
		[ 'title' => 'Franchise',          'slug' => 'franchise',      'template' => 'page-franchise.php'                        ],
		[ 'title' => 'Our Story',          'slug' => 'our-story',      'template' => 'page-story.php'                            ],
		[ 'title' => 'FAQs',               'slug' => 'faqs',           'template' => 'page-faqs.php'                             ],
		[ 'title' => 'Contact',            'slug' => 'contact',        'template' => 'page-contact.php'                          ],
		[ 'title' => 'Blog',               'slug' => 'blog',           'template' => 'page-blog.php'                             ],
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
add_action( 'after_switch_theme', 'ch_setup_theme_pages' );

// ── Also run on admin_init to catch any missing pages ─────────────────────────
// (runs once per WP version bump or if option is missing)
add_action( 'admin_init', function () {
	// Bump this key whenever new page templates are added to ch_get_page_definitions().
	$done_key = 'ch_pages_created_v3';
	if ( get_option( $done_key ) ) return;
	if ( ! current_theme_supports( 'title-tag' ) ) return; // not our theme
	ch_setup_theme_pages();
	update_option( $done_key, true );
} );

// ── Manual trigger: ?ch_regen_pages=1 in WP admin ────────────────────────────
add_action( 'admin_init', function () {
	if ( ! isset( $_GET['ch_regen_pages'] ) ) return;
	if ( ! current_user_can( 'manage_options' ) ) return;
	delete_option( 'ch_pages_created_v3' );
	ch_setup_theme_pages();
	wp_safe_redirect( admin_url( 'themes.php?ch_regen=1' ) );
	exit;
} );

// Admin notice after manual regen
add_action( 'admin_notices', function () {
	if ( ! isset( $_GET['ch_regen'] ) ) return;
	echo '<div class="notice notice-success is-dismissible"><p><strong>The Cane House:</strong> All theme pages created/updated and permalink rules flushed.</p></div>';
} );

// ── Theme Activation Hook ─────────────────────────────────────────────────────
register_activation_hook( __FILE__, function () {
	if ( class_exists( 'CH_Theme_Reset' ) ) {
		CH_Theme_Reset::reset_all();
	}
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
		'instagram_url', 'facebook_url', 'tiktok_url', 'youtube_url',
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

	// Checkbox: show_prices
	$existing['show_prices'] = isset( $_POST['show_prices'] ) ? '1' : '0';

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
		$schema_keys = [ 'enabled', 'type', 'name', 'description', 'phone', 'email', 'area_served', 'price_range', 'include_price', 'include_reviews' ];
		$schema      = [];
		foreach ( $schema_keys as $k ) {
			$schema[ $k ] = sanitize_text_field( wp_unslash( $_POST['schema'][ $k ] ?? '' ) );
		}
		$schema['enabled']         = isset( $_POST['schema']['enabled'] )         ? '1' : '0';
		$schema['include_price']   = isset( $_POST['schema']['include_price'] )   ? '1' : '0';
		$schema['include_reviews'] = isset( $_POST['schema']['include_reviews'] ) ? '1' : '0';
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
		'@type'    => $sc['type'] ?: 'FoodEstablishment',
		'name'     => $sc['name'] ?: get_bloginfo( 'name' ),
		'url'      => home_url( '/' ),
		'logo'     => $sc['logo_url'] ?: get_template_directory_uri() . '/assets/images/logo.png',
		'image'    => $sc['logo_url'] ?: get_template_directory_uri() . '/assets/images/logo.png',
		'description'    => $sc['description'] ?: '',
		'areaServed'     => $sc['area_served'] ?: 'United Kingdom',
		'servesCuisine'  => 'Fresh Sugarcane Juice',
		'currenciesAccepted' => 'GBP',
		'paymentAccepted' => 'Cash, Card',
		'hasMap'     => '',
	];

	if ( $sc['phone'] ) $schema['telephone'] = $sc['phone'];
	if ( $sc['email'] ) $schema['email']     = $sc['email'];

	if ( $sc['include_price'] === '1' && $sc['price_range'] ) {
		$schema['priceRange'] = $sc['price_range'];
	}

	// Social profiles
	$sameAs = array_filter( [
		$s['instagram_url'] ?? '',
		$s['facebook_url']  ?? '',
		$s['tiktok_url']    ?? '',
		$s['youtube_url']   ?? '',
	] );
	if ( $sameAs ) $schema['sameAs'] = array_values( $sameAs );

	// Aggregate rating from DB reviews
	if ( $sc['include_reviews'] === '1' && $include_reviews_data ) {
		$reviews = ch_get_reviews( 100 );
		if ( ! empty( $reviews ) ) {
			$total  = count( $reviews );
			$sum    = array_sum( array_map( fn( $r ) => (float) ( is_array( $r ) ? $r['rating'] : ( $r->rating ?? 5 ) ), $reviews ) );
			$avg    = round( $sum / $total, 1 );
			$schema['aggregateRating'] = [
				'@type'       => 'AggregateRating',
				'ratingValue' => $avg,
				'reviewCount' => $total,
				'bestRating'  => 5,
				'worstRating' => 1,
			];
		}
	}

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
	$v   = wp_get_theme()->get( 'Version' );
	$uri = get_template_directory_uri();

	wp_enqueue_style(
		'ch-google-fonts',
		'https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,400;0,600;0,700;0,800;0,900;1,700&family=Poppins:wght@300;400;500;600;700;800&display=swap',
		[],
		null
	);

	wp_enqueue_style( 'ch-variables',  $uri . '/assets/css/variables.css',  [ 'ch-google-fonts' ], $v );
	wp_enqueue_style( 'ch-base',       $uri . '/assets/css/base.css',       [ 'ch-variables' ],    $v );
	wp_enqueue_style( 'ch-components', $uri . '/assets/css/components.css', [ 'ch-base' ],         $v );
	wp_enqueue_style( 'ch-layout',     $uri . '/assets/css/layout.css',     [ 'ch-components' ],   $v );
	wp_enqueue_style( 'ch-forms',      $uri . '/assets/css/forms.css',      [ 'ch-base' ],         $v );
	wp_enqueue_style( 'ch-animations', $uri . '/assets/css/animations.css', [ 'ch-base' ],         $v );
	wp_enqueue_style( 'ch-style',      get_stylesheet_uri(),                [ 'ch-layout' ],       $v );

	wp_enqueue_script( 'ch-main',  $uri . '/assets/js/main.js',  [ 'jquery' ], $v, true );
	wp_enqueue_script( 'ch-forms', $uri . '/assets/js/forms.js', [ 'ch-main' ], $v, true );

	wp_localize_script( 'ch-forms', 'chTheme', [
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'ch_frontend_nonce' ),
		'siteUrl' => esc_url( home_url( '/' ) ),
	] );
} );
