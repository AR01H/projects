<?php
defined( 'ABSPATH' ) || exit;

// ── Includes - order matters ──────────────────────────────────────────────────
require_once get_template_directory() . '/includes/mini-helping-functions.php';  
require_once get_template_directory() . '/includes/common_constants.php';  // CTA & site-wide string constants
require_once get_template_directory() . '/includes/common_terms.php';      // client brand name constants
require_once get_template_directory() . '/includes/mock-data.php';         // fallback data arrays
require_once get_template_directory() . '/includes/helpers.php';           // DB-first data functions + utilities
require_once get_template_directory() . '/includes/class-theme-admin.php'; // WP admin menu for this theme
require_once get_template_directory() . '/mail/common_contact.php';        // AJAX form handlers
require_once get_template_directory() . '/models/class-content-taxonomy.php'; // AH_Theme_Content_Taxonomy

// ── Init Theme Admin ──────────────────────────────────────────────────────────
if ( is_admin() ) {
	AH_Theme_Admin::init();
}

// ── Theme Setup ───────────────────────────────────────────────────────────────
add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'ah-theme', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'script', 'style' ] );
	add_theme_support( 'custom-logo' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );

	add_image_size( 'ah-card',   600,  400, true );
	add_image_size( 'ah-hero',  1600,  800, true );
	add_image_size( 'ah-thumb',  480,  320, true );

	register_nav_menus( [
		'primary' => __( 'Primary Navigation', 'ah-theme' ),
		'footer'  => __( 'Footer Navigation',  'ah-theme' ),
	] );
} );

// ── Enqueue Assets ────────────────────────────────────────────────────────────
add_action( 'wp_enqueue_scripts', function () {
	$v   = wp_get_theme()->get( 'Version' );
	$uri = get_template_directory_uri();

	wp_enqueue_style(
		'ah-google-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=DM+Sans:wght@300;400;500;600&family=Instrument+Serif:ital@0;1&display=swap',
		[],
		null
	);

	wp_enqueue_style( 'ah-variables',  $uri . '/assets/css/variables.css',  [ 'ah-google-fonts' ], $v );
	wp_enqueue_style( 'ah-base',       $uri . '/assets/css/base.css',       [ 'ah-variables' ],    $v );
	wp_enqueue_style( 'ah-components', $uri . '/assets/css/components.css', [ 'ah-base' ],         $v );
	wp_enqueue_style( 'ah-layout',     $uri . '/assets/css/layout.css',     [ 'ah-components' ],   $v );
	wp_enqueue_style( 'ah-forms',      $uri . '/assets/css/forms.css',      [ 'ah-base' ],         $v );
	wp_enqueue_style( 'ah-animations', $uri . '/assets/css/animations.css', [ 'ah-base' ],         $v );
	wp_enqueue_style( 'ah-style',      get_stylesheet_uri(),                [ 'ah-layout' ],       $v );

	// News & Info Feeder — load only on that page template
	if ( is_page_template( 'template-news-info-feeder.php' ) ) {
		wp_enqueue_style( 'ah-news-feed', $uri . '/assets/css/news-feed.css', [ 'ah-components' ], $v );
	}

	wp_enqueue_script( 'ah-main',  $uri . '/assets/js/main.js',  [ 'jquery' ], $v, true );
	wp_enqueue_script( 'ah-forms', $uri . '/assets/js/forms.js', [ 'ah-main' ], $v, true );

	wp_localize_script( 'ah-forms', 'ahTheme', [
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'ah_frontend_nonce' ),
		'siteUrl' => esc_url( home_url( '/' ) ),
	] );
} );
