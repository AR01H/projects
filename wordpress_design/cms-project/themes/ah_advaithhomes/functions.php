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

// ── Topic Archive Pages ───────────────────────────────────────────────────────
// No rewrite rules needed. We intercept WordPress 404 responses, check the URL
// path against the parent-term table, and serve our templates when there's a match.
// This means no permalink flush is ever required.

/**
 * Parse the request path and return the matching parent-term row, or null.
 * Results are statically cached so the DB is hit at most once per request.
 */
function ah_resolve_topic_from_url(): ?object {
	static $cache = null;
	if ( $cache !== false && $cache !== null ) return $cache;
	if ( ! class_exists( 'AH_DB_Helper' ) ) { $cache = false; return null; }

	$path  = trim( (string) parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ), PHP_URL_PATH ), '/' );
	$parts = array_values( array_filter( explode( '/', $path ) ) );

	if ( empty( $parts ) || count( $parts ) > 2 ) { $cache = false; return null; }

	global $wpdb;
	$pt_table = AH_DB_Helper::table( 'taxonomy_parent_terms' );
	$pt       = $wpdb->get_row( $wpdb->prepare(
		"SELECT * FROM `{$pt_table}` WHERE slug = %s AND status = 1 LIMIT 1",
		sanitize_title( $parts[0] )
	) );

	if ( ! $pt ) { $cache = false; return null; }

	$pt->_cat_slug = count( $parts ) === 2 ? sanitize_title( $parts[1] ) : '';
	$cache = $pt;
	return $pt;
}

// Prevent redirect_canonical from bouncing valid topic URLs before our template fires.
add_filter( 'redirect_canonical', function ( $redirect_url ) {
	return ah_resolve_topic_from_url() ? false : $redirect_url;
} );

// Intercept WordPress 404 when the URL is a known parent-term (or category within one).
add_filter( 'template_include', function ( string $template ): string {
	if ( ! is_404() ) return $template;

	$pt = ah_resolve_topic_from_url();
	if ( ! $pt ) return $template;

	// Fix HTTP status code — this is a real page now.
	status_header( 200 );

	$GLOBALS['ah_current_pt'] = $pt;
	set_query_var( 'ah_pt_slug',  $pt->slug );
	set_query_var( 'ah_cat_slug', $pt->_cat_slug );

	$GLOBALS['ah_is_topic_page'] = true;

	if ( $pt->_cat_slug ) {
		return locate_template( 'topic-category.php' ) ?: $template;
	}
	return locate_template( 'topic-parent.php' ) ?: $template;
} );

// Load news-feed CSS on topic pages ($GLOBALS flag is set by template_include above,
// which fires before wp_enqueue_scripts since that hook runs inside get_header/wp_head).
add_action( 'wp_enqueue_scripts', function (): void {
	if ( empty( $GLOBALS['ah_is_topic_page'] ) ) return;
	$uri = get_template_directory_uri();
	$fv  = (string) @filemtime( get_template_directory() . '/assets/css/news-feed.css' );
	wp_enqueue_style( 'ah-news-feed', $uri . '/assets/css/news-feed.css', [ 'ah-components' ], $fv );
}, 20 );

// ── Breadcrumb helper — find the parent term for a WP category slug ──────────
/**
 * Returns the parent-term row for the given category slug, or null.
 * Used in single.php to build Home › PT › Cat › Post breadcrumbs.
 * Results are statically cached by slug.
 */
function ah_get_parent_term_for_cat( string $cat_slug ): ?object {
	static $cache = [];
	if ( array_key_exists( $cat_slug, $cache ) ) return $cache[ $cat_slug ];
	if ( ! class_exists( 'AH_DB_Helper' ) ) { $cache[ $cat_slug ] = null; return null; }

	global $wpdb;
	$tax_table = AH_DB_Helper::table( 'taxonomies' );
	$pt_table  = AH_DB_Helper::table( 'taxonomy_parent_terms' );
	$row       = $wpdb->get_row( $wpdb->prepare(
		"SELECT pt.* FROM `{$pt_table}` pt
		 INNER JOIN `{$tax_table}` t ON t.parent_term_id = pt.id
		 WHERE t.slug = %s AND pt.status = 1 AND t.status = 1
		 LIMIT 1",
		$cat_slug
	) );
	$cache[ $cat_slug ] = $row ?: null;
	return $cache[ $cat_slug ];
}

// ── Highlight Links meta box ──────────────────────────────────────────────────
add_action( 'add_meta_boxes', function (): void {
	add_meta_box(
		'ah_highlight_links',
		__( 'Highlight Links', 'ah-theme' ),
		'ah_highlight_links_render',
		'post',
		'side',
		'default'
	);
} );

function ah_highlight_links_render( WP_Post $post ): void {
	wp_nonce_field( 'ah_highlight_links_save', 'ah_hl_nonce' );
	$links = json_decode( get_post_meta( $post->ID, '_ah_highlight_links', true ) ?: '[]', true );
	if ( ! is_array( $links ) ) $links = [];
	?>
	<div id="ah-hl-rows">
	<?php foreach ( $links as $link ) : ?>
	<div class="ah-hl-row" style="display:flex;gap:5px;margin-bottom:5px">
		<input type="text" name="ah_hl_name[]"
		       value="<?php echo esc_attr( $link['name'] ?? '' ); ?>"
		       placeholder="<?php esc_attr_e( 'Label', 'ah-theme' ); ?>"
		       style="flex:1;min-width:0">
		<input type="text" name="ah_hl_url[]"
		       value="<?php echo esc_attr( $link['url'] ?? '' ); ?>"
		       placeholder="<?php esc_attr_e( '/slug/ or URL', 'ah-theme' ); ?>"
		       style="flex:1.4;min-width:0">
		<button type="button" class="ah-hl-remove button" style="flex-shrink:0">✕</button>
	</div>
	<?php endforeach; ?>
	</div>
	<button type="button" id="ah-hl-add" class="button button-secondary" style="margin-top:4px;width:100%">
		+ <?php esc_html_e( 'Add Link', 'ah-theme' ); ?>
	</button>
	<script>
	(function($){
		$('#ah-hl-add').on('click',function(){
			$('#ah-hl-rows').append(
				'<div class="ah-hl-row" style="display:flex;gap:5px;margin-bottom:5px">' +
				'<input type="text" name="ah_hl_name[]" placeholder="<?php echo esc_js( __( 'Label', 'ah-theme' ) ); ?>" style="flex:1;min-width:0">' +
				'<input type="text" name="ah_hl_url[]"  placeholder="<?php echo esc_js( __( '/slug/ or URL', 'ah-theme' ) ); ?>" style="flex:1.4;min-width:0">' +
				'<button type="button" class="ah-hl-remove button" style="flex-shrink:0">✕</button>' +
				'</div>'
			);
		});
		$(document).on('click','.ah-hl-remove',function(){ $(this).closest('.ah-hl-row').remove(); });
	})(jQuery);
	</script>
	<?php
}

add_action( 'save_post_post', function ( int $post_id ): void {
	if ( ! isset( $_POST['ah_hl_nonce'] ) || ! wp_verify_nonce( $_POST['ah_hl_nonce'], 'ah_highlight_links_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$names = array_map( 'sanitize_text_field', wp_unslash( (array) ( $_POST['ah_hl_name'] ?? [] ) ) );
	$urls  = array_map( 'esc_url_raw',          wp_unslash( (array) ( $_POST['ah_hl_url']  ?? [] ) ) );
	$links = [];
	foreach ( $names as $i => $name ) {
		$url = $urls[ $i ] ?? '';
		if ( $name !== '' || $url !== '' ) {
			$links[] = [ 'name' => $name, 'url' => $url ];
		}
	}
	update_post_meta( $post_id, '_ah_highlight_links', wp_json_encode( $links ) );
} );

// ── Header Search Autosuggest (blogs & news only) ─────────────────────────────
add_action( 'wp_ajax_ah_search_suggest',        'ah_search_suggest_handler' );
add_action( 'wp_ajax_nopriv_ah_search_suggest', 'ah_search_suggest_handler' );
function ah_search_suggest_handler(): void {
	check_ajax_referer( 'ah_frontend_nonce', 'nonce' );
	$q = sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) );
	if ( mb_strlen( $q ) < 1 ) { wp_send_json_success( [] ); return; }

	$results = get_posts( [
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 6,
		's'              => $q,
	] );

	$out = [];
	foreach ( $results as $p ) {
		$cats  = get_the_category( $p->ID );
		$out[] = [
			'title'    => get_the_title( $p->ID ),
			'excerpt'  => wp_trim_words( get_the_excerpt( $p->ID ) ?: $p->post_content, 12, '…' ),
			'url'      => get_permalink( $p->ID ),
			'cat'      => $cats ? $cats[0]->name : '',
			'cat_slug' => $cats ? $cats[0]->slug : '',
			'thumb'    => get_the_post_thumbnail_url( $p->ID, 'ah-thumb' ) ?: '',
		];
	}
	wp_send_json_success( $out );
}
