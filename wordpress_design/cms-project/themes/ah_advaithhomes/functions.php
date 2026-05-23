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
	$dir = get_template_directory();
	$uri = get_template_directory_uri();

	// Use file modification time so browsers auto-bust cache on every CSS save.
	$fv = fn( string $rel ) => (string) @filemtime( $dir . $rel );

	wp_enqueue_style(
		'ah-google-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=DM+Sans:wght@300;400;500;600&family=Instrument+Serif:ital@0;1&display=swap',
		[],
		null
	);

	wp_enqueue_style( 'ah-variables',  $uri . '/assets/css/variables.css',  [ 'ah-google-fonts' ], $fv( '/assets/css/variables.css' ) );
	wp_enqueue_style( 'ah-base',       $uri . '/assets/css/base.css',       [ 'ah-variables' ],    $fv( '/assets/css/base.css' ) );
	wp_enqueue_style( 'ah-components', $uri . '/assets/css/components.css', [ 'ah-base' ],         $fv( '/assets/css/components.css' ) );
	wp_enqueue_style( 'ah-layout',     $uri . '/assets/css/layout.css',     [ 'ah-components' ],   $fv( '/assets/css/layout.css' ) );
	wp_enqueue_style( 'ah-forms',      $uri . '/assets/css/forms.css',      [ 'ah-base' ],         $fv( '/assets/css/forms.css' ) );
	wp_enqueue_style( 'ah-animations', $uri . '/assets/css/animations.css', [ 'ah-base' ],         $fv( '/assets/css/animations.css' ) );
	wp_enqueue_style( 'ah-style',      get_stylesheet_uri(),                [ 'ah-layout' ],       $fv( '/style.css' ) );

	// News & Info Feeder — load on that page template and the front page (which also uses it)
	if ( is_page_template( 'template-news-info-feeder.php' ) || is_front_page() ) {
		wp_enqueue_style( 'ah-news-feed', $uri . '/assets/css/news-feed.css', [ 'ah-components' ], $fv( '/assets/css/news-feed.css' ) );
	}

	wp_enqueue_script( 'ah-main',  $uri . '/assets/js/main.js',  [ 'jquery' ],  $fv( '/assets/js/main.js' ),  true );
	wp_enqueue_script( 'ah-forms', $uri . '/assets/js/forms.js', [ 'ah-main' ], $fv( '/assets/js/forms.js' ), true );

	wp_localize_script( 'ah-forms', 'ahTheme', [
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'ah_frontend_nonce' ),
		'siteUrl' => esc_url( home_url( '/' ) ),
	] );
} );

// ── News & Info Feeder — fix pagination 301 redirect ─────────────────────────
// WordPress's redirect_canonical() fires on template_redirect (before the page
// template loads) and strips ?page=X from static page URLs, redirecting back to
// the base URL. Disable it only for this template so ?page=X pagination works.
add_filter( 'redirect_canonical', function ( $redirect_url ) {
	if ( is_page_template( 'template-news-info-feeder.php' ) || is_front_page() ) {
		return false;
	}
	return $redirect_url;
} );

add_action( 'pre_get_posts', function ( $query ) {
	if ( ! is_admin() && $query->is_main_query() && $query->is_home() ) {
		$query->set( 'posts_per_page', 12 );
	}
} );

// ── Allow iframes in post content ────────────────────────────────────────────
// WordPress strips iframes by default via wp_kses_post. Adding them here lets
// editors embed YouTube/maps/etc. without switching to code view and losing them on save.
add_filter( 'wp_kses_allowed_html', function ( array $tags, string $context ) : array {
	if ( $context === 'post' ) {
		$tags['iframe'] = [
			'src'             => true,
			'width'           => true,
			'height'          => true,
			'frameborder'     => true,
			'allow'           => true,
			'allowfullscreen' => true,
			'allowpaymentrequest' => true,
			'loading'         => true,
			'title'           => true,
			'name'            => true,
			'id'              => true,
			'class'           => true,
			'style'           => true,
			'sandbox'         => true,
			'referrerpolicy'  => true,
		];
	}
	return $tags;
}, 10, 2 );

// ── Shared content formatter — tables, iframes, YouTube URLs ─────────────────
// Call ah_format_content() anywhere raw HTML needs the same treatment.
function ah_format_content( string $content ) : string {
	if ( empty( $content ) ) return $content;

	if ( strpos( $content, '<iframe' ) !== false ) {
		// Convert youtu.be short links
		$content = preg_replace_callback(
			'/(<iframe[^>]*\ssrc=")https?:\/\/youtu\.be\/([A-Za-z0-9_\-]+)([^"]*?)(")/i',
			fn( $m ) => $m[1] . 'https://www.youtube.com/embed/' . $m[2] . $m[3] . $m[4],
			$content
		);
		// Convert youtube.com/watch?v= links
		$content = preg_replace_callback(
			'/(<iframe[^>]*\ssrc=")https?:\/\/(?:www\.)?youtube\.com\/watch\?v=([A-Za-z0-9_\-]+)([^"]*?)(")/i',
			fn( $m ) => $m[1] . 'https://www.youtube.com/embed/' . $m[2] . $m[3] . $m[4],
			$content
		);
		// Wrap in responsive .prose-embed container
		$content = preg_replace_callback(
			'/<iframe[^>]*>[\s\S]*?<\/iframe>/i',
			fn( $m ) => '<div class="prose-embed">' . $m[0] . '</div>',
			$content
		);
		// Fix accidental double-wrap
		$content = preg_replace(
			'/<div class="prose-embed">\s*<div class="prose-embed">([\s\S]*?<\/iframe>)\s*<\/div>\s*<\/div>/i',
			'<div class="prose-embed">$1</div>',
			$content
		);
	}

	// Wrap tables in horizontally-scrollable container
	if ( strpos( $content, '<table' ) !== false && strpos( $content, 'prose-table-wrap' ) === false ) {
		$content = preg_replace_callback(
			'/<table[\s\S]*?<\/table>/i',
			fn( $m ) => '<div class="prose-table-wrap">' . $m[0] . '</div>',
			$content
		);
	}

	return $content;
}

// Hook the shared formatter into the standard WordPress content filter
add_filter( 'the_content', 'ah_format_content' );
