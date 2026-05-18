<?php
defined( 'ABSPATH' ) || exit;

// ── Includes — order matters ──────────────────────────────────────────────────
require_once get_template_directory() . '/includes/mock-data.php';
require_once get_template_directory() . '/includes/helpers.php';
require_once get_template_directory() . '/includes/class-theme-admin.php';
require_once get_template_directory() . '/mail/common_contact.php';

// ── Init Theme Admin ──────────────────────────────────────────────────────────
if ( is_admin() ) {
	CH_Theme_Admin::init();
}

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
