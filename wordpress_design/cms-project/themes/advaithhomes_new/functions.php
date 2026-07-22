<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Block direct file access.
}

// ===========================
// THEME CONSTANTS
// ===========================
require_once get_template_directory() . '/includes/core_info.php';
require_once get_template_directory() . '/includes/core_terms.php';
require_once get_template_directory() . '/includes/core_settings.php';
require_once get_template_directory() . '/includes/rules_conditions.php';
require_once get_template_directory() . '/includes/core_routing.php';
require_once get_template_directory() . '/includes/class-category-settings.php';
require_once get_template_directory() . '/includes/class-calculator-db.php';
require_once get_template_directory() . '/includes/class-expert-db.php';
require_once get_template_directory() . '/includes/class-adn-enquiry.php';
require_once get_template_directory() . '/includes/class-adn-form-ajax.php';
require_once get_template_directory() . '/includes/adn-sidebar-helpers.php';
require_once get_template_directory() . '/includes/comment-callbacks.php';
require_once get_template_directory() . '/includes/seo.php';
require_once get_template_directory() . '/includes/class-adn-cache.php';

// ===========================
// LOAD HELPER FUNCTIONS
// ===========================
require_once ADN_THEME_DIR . '/explode_function.php';

// ===========================
// DATA LOADERS (csv / json / html / pdf)
// ===========================
require_once ADN_THEME_DIR . '/includes/data_fetcher/class-real-loader.php';

// ===========================
// ADMIN (tabs + subtabs page)
// ===========================
if ( is_admin() ) {
    require_once ADN_THEME_DIR . '/admin/class-theme-admin.php';
    ADN_Theme_Admin::init();
}

// ===========================
// HOOKS
// ===========================
add_action( 'after_setup_theme', 'ahn_include_files' );
add_action( 'after_setup_theme', 'adn_theme_register' );

// Clear or bypass cache intercepts
add_action( 'init', function() {
	if ( class_exists( 'ADN_Cache' ) ) {
		// Flush filesystem cache if admin clears CMS cache
		if ( is_admin() && isset( $_POST['clear_cache'] ) && current_user_can( 'manage_options' ) ) {
			ADN_Cache::clear_all();
		}
		// Also support query param cache clearing for admins/developers
		if ( isset( $_GET['clear_cache'] ) || isset( $_GET['cache_clear'] ) ) {
			if ( current_user_can( 'manage_options' ) || ! is_user_logged_in() ) {
				ADN_Cache::clear_all();

				// Clean redirect back to the page without clear_cache query parameter
				if ( isset( $_GET['clear_cache'] ) ) {
					$redirect_url = remove_query_arg( 'clear_cache' );
					wp_safe_redirect( $redirect_url );
					exit;
				}
			}
		}
	}
} );

// Add "Clear Cache" button to the WP Admin Bar (only in the WordPress Admin Panel/Dashboard)
add_action( 'admin_bar_menu', function( $wp_admin_bar ) {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$current_url = admin_url();
	$clear_url = add_query_arg( 'clear_cache', '1', $current_url );

	$wp_admin_bar->add_node( array(
		'id'    => 'adn-clear-cache',
		'title' => '⚡ Clear Cache',
		'href'  => $clear_url,
		'meta'  => array(
			'title' => 'Clear all theme filesystem and CMS caches',
		),
	) );
}, 100 );

// Site-wide notice popup - once per day, resets if content changes.
add_action( 'wp_footer', 'adn_render_site_notice_popup' );
function adn_render_site_notice_popup(): void {
	if ( class_exists( 'AH_Notice_Helper' ) ) {
		AH_Notice_Helper::render_frontend_popup();
	}
}

// Floating WhatsApp + Call buttons (numbers from ah_site_settings DB).
add_action( 'wp_footer', 'adn_render_floating_contact' );
function adn_render_floating_contact(): void {
	// Skip in content-only / embed mode.
	if ( ! empty( $_GET['content'] ) && 'true' === (string) $_GET['content'] ) {
		return;
	}
	if ( function_exists( 'adn_component' ) ) {
		adn_component( 'parts/floating_contact' );
	}
}

/**
 * Scroll-reveal: cards + section headings fade/rise into view as you scroll.
 * Head gate sets html.adn-reveal BEFORE paint (so targets hide with no flash);
 * footer script reveals them via IntersectionObserver. No-JS + reduced-motion
 * users see everything normally. Only server-rendered selectors are hidden, so
 * AJAX-added content (news grid) is never left invisible.
 */
add_action( 'wp_head', 'adn_reveal_gate', 1 );
function adn_reveal_gate(): void {
	if ( ! empty( $_GET['content'] ) && 'true' === (string) $_GET['content'] ) {
		return;
	}
	echo "<script>(function(){try{if(!window.matchMedia||!matchMedia('(prefers-reduced-motion: reduce)').matches){document.documentElement.className+=' adn-reveal';}}catch(e){}})();</script>\n";
}

add_action( 'wp_footer', 'adn_reveal_runtime', 30 );
function adn_reveal_runtime(): void {
	if ( ! empty( $_GET['content'] ) && 'true' === (string) $_GET['content'] ) {
		return;
	}
	?>
<script>
(function(){
	var root = document.documentElement;
	if ( ! root.classList.contains('adn-reveal') ) { return; }
	var SEL = '.guide-card,.jny-card,.calc-card,.contact-resource-card,.glc,.spotlight-card,.expert-card,.featured-article,.section-header-wrap';
	var io  = null;
	if ( 'IntersectionObserver' in window ) {
		io = new IntersectionObserver( function( entries ){
			entries.forEach( function( en ){
				if ( en.isIntersecting ) {
					en.target.classList.add('adn-in');
					io.unobserve( en.target );
				}
			} );
		}, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 } );
	}
	/* Re-runnable: also called by premium.js after AJAX fragments are
	   injected, so new reveal targets are observed (never stuck hidden). */
	function scan(){
		var els = [].slice.call( document.querySelectorAll( SEL ) ).filter( function( e ){
			return ! e.dataset.adnRev;
		} );
		els.forEach( function( e ){
			e.dataset.adnRev = '1';
			if ( ! io ) { e.classList.add('adn-in'); return; }
			var sibs = e.parentNode ? [].indexOf.call( e.parentNode.children, e ) : 0;
			e.style.transitionDelay = ( Math.min( sibs % 8, 6 ) * 55 ) + 'ms';
			io.observe( e );
		} );
	}
	window.adnRevealScan = scan;
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', scan );
	} else {
		scan();
	}
}());
</script>
	<?php
}

// Change REST API URL prefix from /wp-json/ to /api/ → routes become /api/v1/...
add_filter( 'rest_url_prefix', function () { return 'api'; } );

// ── Lazy loading for theme images ──────────────────────────────────────────
// WordPress 5.5+ has native lazy loading for wp_get_attachment_image(). Enable it.
add_filter( 'wp_lazy_loading_enabled', '__return_true' );

// For inline <img> tags in post content, add loading="lazy" + decoding="async".
add_filter( 'the_content', 'adn_add_img_lazy_attr', 10 );
function adn_add_img_lazy_attr( string $content ): string {
	if ( false === strpos( $content, '<img' ) ) {
		return $content;
	}
	return preg_replace_callback(
		'/<img(?![^>]*\bloading=)[^>]*>/i',
		function ( $m ) {
			$tag = $m[0];
			// Skip images that already have loading or are marked eager
			if ( false !== strpos( $tag, 'loading=' ) ) {
				return $tag;
			}
			// Insert loading="lazy" and decoding="async" before the closing >
			$tag = rtrim( $tag, '/' . '>' );
			$tag = rtrim( $tag );
			return $tag . ' loading="lazy" decoding="async">';
		},
		$content
	);
}

// ── Image optimisation on upload ───────────────────────────────────────────
// WordPress 5.3+: automatically downscale any uploaded image wider than 1920px.
// The original is preserved as a "scaled" copy; WP serves the smaller version.
// add_filter( 'big_image_size_threshold', function () { return 1920; } );

// // Lower JPEG quality to 82% (WordPress default is 82, but make it explicit).
// // Reduces file size ~30–50% vs 100% quality with no visible difference on screen.
// add_filter( 'jpeg_quality',        function () { return 82; } );
// add_filter( 'wp_editor_set_quality', function () { return 82; } );

// // WordPress 5.8+: generate WebP versions of uploaded JPEG/PNG images automatically.
// // Browsers that support WebP will download the smaller WebP; others get the original.
// add_filter( 'wp_upload_image_mime_transforms', function ( $transforms ) {
// 	foreach ( array( 'image/jpeg', 'image/png' ) as $mime ) {
// 		if ( isset( $transforms[ $mime ] ) && ! in_array( 'image/webp', $transforms[ $mime ], true ) ) {
// 			$transforms[ $mime ][] = 'image/webp';
// 		}
// 	}
// 	return $transforms;
// } );
add_action( 'after_switch_theme', function () { flush_rewrite_rules(); } );

// One-time flush after URL prefix changed wp-json/adn/v1 → api/v1.
add_action( 'admin_init', function () {
    if ( ! get_option( 'adn_api_ns_flushed_v3' ) ) {
        flush_rewrite_rules();
        update_option( 'adn_api_ns_flushed_v3', true );
    }
} );

// Create default pages only once, when the theme is activated, instead of on every admin load.
add_action( 'after_switch_theme', 'adn_create_default_pages' );

// Install category settings DB table on theme activation and lazily on admin load.
add_action( 'after_switch_theme', array( 'AH_Category_Settings', 'install' ) );
add_action( 'admin_init',         array( 'AH_Category_Settings', 'maybe_install' ) );

// Install calculator DB table.
add_action( 'after_switch_theme', array( 'AH_Calculator_DB', 'install' ) );
add_action( 'admin_init',         array( 'AH_Calculator_DB', 'maybe_install' ) );

// Install expert DB table.
add_action( 'after_switch_theme', array( 'AH_Expert_DB', 'install' ) );
add_action( 'admin_init',         array( 'AH_Expert_DB', 'maybe_install' ) );

// Install enquiry submissions table.
add_action( 'after_switch_theme', array( 'AH_Enquiry_Model', 'install_table' ) );
add_action( 'admin_init',         array( 'AH_Enquiry_Model', 'maybe_install' ) );

// Merge DB-stored (admin-created) calculators into the adn_calculators() registry.
// File-based calculators already in the array take priority - DB only adds new keys.
add_filter( 'adn_calculators', 'adn_merge_db_calculators' );
function adn_merge_db_calculators( $tools ) {
	if ( ! class_exists( 'AH_Calculator_DB' ) ) { return $tools; }
	foreach ( AH_Calculator_DB::get_all( 'active' ) as $row ) {
		$k = $row['calc_key'];
		if ( isset( $tools[ $k ] ) ) { continue; }
		$tools[ $k ] = array(
			'title' => $row['title'],
			'label' => '' !== $row['label'] ? $row['label'] : $row['title'],
			'icon'  => $row['icon'],
			'view'  => '__db__',
		);
	}
	return $tools;
}

// Expert profile page routing (/ask-expert/SLUG/, legacy ?ah_expert=SLUG too).
add_action( 'template_redirect', 'adn_expert_full_page_render', 0 );

// Contact + guidance form AJAX (theme-level: contact/guidance page submissions + inbox status save).
add_action( 'init', array( 'ADN_Form_Ajax', 'init' ) );
add_action( 'init', array( 'ADN_Form_Ajax', 'init_public' ) );

// Expert contact form AJAX (listing page and profile page).
add_action( 'wp_ajax_adn_expert_contact',        'adn_expert_contact_ajax' );
add_action( 'wp_ajax_nopriv_adn_expert_contact', 'adn_expert_contact_ajax' );

// Expert profile unlock AJAX.
add_action( 'wp_ajax_adn_expert_unlock',        'adn_expert_unlock_ajax' );
add_action( 'wp_ajax_nopriv_adn_expert_unlock', 'adn_expert_unlock_ajax' );

// Post related articles AJAX — returns articles sharing the same CMS taxonomy terms.
add_action( 'wp_ajax_adn_post_related_articles',        'adn_post_related_articles_ajax' );
add_action( 'wp_ajax_nopriv_adn_post_related_articles', 'adn_post_related_articles_ajax' );

// Inline comment moderation (admin only).
add_action( 'wp_ajax_adn_moderate_comment', 'adn_moderate_comment_ajax' );

// AJAX comment submission (replaces wp-comments-post.php redirect).
add_action( 'wp_ajax_adn_submit_comment',        'adn_ajax_submit_comment' );
add_action( 'wp_ajax_nopriv_adn_submit_comment', 'adn_ajax_submit_comment' );

// AJAX load-more comments pagination.
add_action( 'wp_ajax_adn_load_comments',        'adn_ajax_load_comments' );
add_action( 'wp_ajax_nopriv_adn_load_comments', 'adn_ajax_load_comments' );

// Post helpful / like counter.
add_action( 'wp_ajax_adn_post_helpful',        'adn_post_helpful_ajax' );
add_action( 'wp_ajax_nopriv_adn_post_helpful', 'adn_post_helpful_ajax' );

function adn_moderate_comment_ajax() {
	check_ajax_referer( 'adn_moderate_comment', 'nonce' );

	if ( ! current_user_can( 'moderate_comments' ) ) {
		wp_send_json_error( array( 'message' => 'Permission denied' ), 403 );
	}

	$comment_id = isset( $_POST['comment_id'] ) ? (int) $_POST['comment_id'] : 0;
	$mod_action = isset( $_POST['mod_action'] ) ? sanitize_key( (string) $_POST['mod_action'] ) : '';

	if ( ! $comment_id || ! in_array( $mod_action, array( 'approve', 'unapprove', 'spam', 'trash' ), true ) ) {
		wp_send_json_error( array( 'message' => 'Invalid request' ), 400 );
	}

	if ( ! get_comment( $comment_id ) ) {
		wp_send_json_error( array( 'message' => 'Comment not found' ), 404 );
	}

	$ok = false;
	switch ( $mod_action ) {
		case 'approve':   $ok = wp_set_comment_status( $comment_id, 'approve' ); break;
		case 'unapprove': $ok = wp_set_comment_status( $comment_id, 'hold' );    break;
		case 'spam':      $ok = wp_spam_comment( $comment_id );                  break;
		case 'trash':     $ok = wp_trash_comment( $comment_id );                 break;
	}

	if ( $ok ) {
		wp_send_json_success( array( 'action' => $mod_action ) );
	} else {
		wp_send_json_error( array( 'message' => 'Action failed' ) );
	}
}

function adn_ajax_submit_comment() {
	if ( ! check_ajax_referer( 'adn_comment_nonce', 'adn_nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh and try again.', ADN_TEXT_DOMAIN ) ), 403 );
	}

	// Email field is optional - bypass WP's require_name_email validation.
	add_filter( 'pre_option_require_name_email', '__return_zero' );
	$comment = wp_handle_comment_submission( wp_unslash( $_POST ) );
	remove_filter( 'pre_option_require_name_email', '__return_zero' );

	if ( is_wp_error( $comment ) ) {
		wp_send_json_error( array( 'message' => $comment->get_error_message() ) );
	}

	/* Render the new comment as HTML so JS can inject it */
	$_args = array(
		'style'     => 'ol',
		'max_depth' => (int) get_option( 'thread_comments_depth', 5 ),
	);
	ob_start();
	adn_comment_callback( $comment, $_args, 1 );
	adn_comment_end_callback( $comment, $_args, 1 );
	$html = ob_get_clean();

	wp_send_json_success( array(
		'html'     => $html,
		'approved' => (bool) $comment->comment_approved,
		'message'  => $comment->comment_approved
			? __( 'Your comment has been posted.', ADN_TEXT_DOMAIN )
			: __( 'Your comment is awaiting moderation. Thank you.', ADN_TEXT_DOMAIN ),
	) );
}

/** Fetch a page of comments (replace mode - always 10 max). */
function adn_ajax_load_comments() {
	check_ajax_referer( 'adn_load_comments', 'nonce' );

	$post_id  = isset( $_POST['post_id'] ) ? (int) $_POST['post_id']       : 0;
	$page     = isset( $_POST['page'] )    ? max( 1, (int) $_POST['page'] ) : 1;
	$per_page = 10;

	/* Order: desc = newest first (default), asc = oldest first */
	$order = ( isset( $_POST['order'] ) && 'asc' === (string) $_POST['order'] ) ? 'ASC' : 'DESC';

	/* Status: admins may request pending; everyone else sees approved only */
	$allowed_statuses = array( 'approve' );
	if ( current_user_can( 'moderate_comments' ) ) {
		$allowed_statuses[] = 'hold';
	}
	$req_status = isset( $_POST['status'] ) ? sanitize_key( (string) $_POST['status'] ) : 'approve';
	$status     = in_array( $req_status, $allowed_statuses, true ) ? $req_status : 'approve';

	if ( ! $post_id || ! get_post( $post_id ) ) {
		wp_send_json_error( array( 'message' => 'Invalid post.' ), 400 );
	}

	$total       = (int) get_comments( array(
		'post_id' => $post_id,
		'status'  => $status,
		'count'   => true,
		'type'    => 'comment',
	) );
	$total_pages = $total > 0 ? (int) ceil( $total / $per_page ) : 1;
	$offset      = ( $page - 1 ) * $per_page;

	$comments = get_comments( array(
		'post_id' => $post_id,
		'status'  => $status,
		'number'  => $per_page,
		'offset'  => $offset,
		'orderby' => 'comment_date_gmt',
		'order'   => $order,
		'type'    => 'comment',
	) );

	if ( empty( $comments ) ) {
		wp_send_json_success( array( 'html' => '', 'total' => 0, 'page' => 1, 'total_pages' => 1 ) );
	}

	$GLOBALS['post'] = get_post( $post_id );
	setup_postdata( $GLOBALS['post'] );

	$_args = array(
		'style'     => 'ol',
		'max_depth' => (int) get_option( 'thread_comments_depth', 5 ),
	);

	ob_start();
	foreach ( $comments as $_c ) {
		adn_comment_callback( $_c, $_args, 1 );
		adn_comment_end_callback( $_c, $_args, 1 );
	}
	$html = ob_get_clean();
	wp_reset_postdata();

	wp_send_json_success( array(
		'html'        => $html,
		'total'       => $total,
		'page'        => $page,
		'total_pages' => $total_pages,
	) );
}

function adn_post_helpful_ajax() {
	check_ajax_referer( 'adn_post_helpful', 'nonce' );

	$post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
	if ( ! $post_id || ! get_post( $post_id ) ) {
		wp_send_json_error( array( 'message' => 'Invalid post' ), 400 );
	}

	$count        = max( 0, (int) get_post_meta( $post_id, '_adn_helpful_count', true ) );
	$already      = isset( $_POST['liked'] ) && '1' === (string) $_POST['liked'];

	if ( $already ) {
		$count = max( 0, $count - 1 );
	} else {
		$count++;
	}

	update_post_meta( $post_id, '_adn_helpful_count', $count );
	wp_send_json_success( array( 'count' => $count, 'liked' => ! $already ) );
}

// Persist the language choice early, before any template output starts.
add_action( 'init', 'adn_set_language_cookie' );

add_action( 'wp_enqueue_scripts', 'adn_enqueue_common_css' );
add_action( 'wp_enqueue_scripts', 'adn_enqueue_common_js' );
add_action( 'wp_enqueue_scripts', 'adn_enqueue_template_specific_assets' );

// ---------------------------------------------------
// Cache busting for images – append ?v=LOCAL_CACHE_VERSION
// ---------------------------------------------------
if ( defined( 'LOCAL_CACHE_VERSION' ) ) {
    /**
     * Append version to attachment URLs.
     */
    add_filter( 'wp_get_attachment_url', function ( $url ) {
        if ( false !== strpos( $url, 'v=' ) ) {
            return $url;
        }
        $sep = ( strpos( $url, '?' ) === false ) ? '?v=' : '&v=';
        return $url . $sep . LOCAL_CACHE_VERSION;
    } );

    /**
     * Append version to attachment image src arrays.
     */
    add_filter( 'wp_get_attachment_image_src', function ( $src ) {
        if ( empty( $src ) || ! is_array( $src ) ) {
            return $src;
        }
        $url = $src[0];
        if ( false !== strpos( $url, 'v=' ) ) {
            return $src;
        }
        $sep = ( strpos( $url, '?' ) === false ) ? '?v=' : '&v=';
        $src[0] = $url . $sep . LOCAL_CACHE_VERSION;
        return $src;
    } );

    /**
     * Append version to images embedded directly in post content.
     */
    add_filter( 'the_content', function ( $content ) {
        $pattern = '#(<img[^>]+src=["\"])([^"\"]+)(["\"])#i';
        return preg_replace_callback( $pattern, function ( $m ) {
            $url = $m[2];
            // Skip external URLs not belonging to this site.
            if ( preg_match( '#^https?://#i', $url ) && false === strpos( $url, home_url() ) ) {
                return $m[0];
            }
            if ( false !== strpos( $url, 'v=' ) ) {
                return $m[0];
            }
            $sep = ( strpos( $url, '?' ) === false ) ? '?v=' : '&v=';
            $url = $url . $sep . LOCAL_CACHE_VERSION;
            return $m[1] . $url . $m[3];
        }, $content );
    } );
    
    // Append version to background-image URLs in inline styles.
    add_filter( 'the_content', function ( $content ) {
        $pattern = '#(background(?:-image)?\s*:\s*url\(["\']?)([^"\')]+)(["\']?\))#i';
        return preg_replace_callback( $pattern, function ( $m ) {
            $url = $m[2];
            // Skip external URLs not belonging to this site.
            if ( preg_match( '#^https?://#i', $url ) && false === strpos( $url, home_url() ) ) {
                return $m[0];
            }
            if ( false !== strpos( $url, 'v=' ) ) {
                return $m[0];
            }
            $sep = ( strpos( $url, '?' ) === false ) ? '?v=' : '&v=';
            $url = $url . $sep . LOCAL_CACHE_VERSION;
            return $m[1] . $url . $m[3];
        }, $content );
    } );
}

add_action( 'template_redirect', 'adn_check_coming_soon' );

// Search: show 12 results per page.
add_action( 'pre_get_posts', function( $q ) {
	if ( ! is_admin() && $q->is_main_query() && $q->is_search() ) {
		$q->set( 'posts_per_page', 12 );
	}
} );

// ===========================
// SHORTCODES
// ===========================
add_shortcode( 'adn_cat_calculators', 'adn_shortcode_cat_calculators' );
add_shortcode( 'adn_cookie_preferences', 'adn_shortcode_cookie_preferences' );

/**
 * Expert profile page: serve pages/page-expert-single.php for /ask-expert/{slug}/
 * (pretty, canonical form) - the legacy ?ah_expert=SLUG query string still works too.
 */
function adn_expert_full_page_render() {
	$slug = isset( $_GET['ah_expert'] ) ? sanitize_key( wp_unslash( $_GET['ah_expert'] ) ) : '';
	if ( '' === $slug && function_exists( 'adn_pretty_path_slug' ) && defined( 'SITE_EXPERT_URL' ) ) {
		$slug = adn_pretty_path_slug( SITE_EXPERT_URL );
	}
	if ( '' === $slug ) { return; }
	if ( ! class_exists( 'AH_Expert_DB' ) ) { return; }
	$expert = AH_Expert_DB::get( $slug );
	if ( ! $expert || 'active' !== $expert['status'] ) { return; }
	// Normalise so page-expert-single.php (which re-reads $_GET directly) sees
	// the slug regardless of whether it arrived via the pretty path or the query string.
	$_GET['ah_expert'] = $slug;
	$base     = realpath( ADN_THEME_DIR . '/pages' );
	$template = realpath( ADN_THEME_DIR . '/pages/page-expert-single.php' );
	if ( $base && $template && 0 === strpos( $template, $base ) && is_file( $template ) ) {
		// The pretty path (/ask-expert/{slug}/) has no matching WP child page, so
		// WordPress already flagged this request as a 404 before template_redirect
		// fired - clear that or the browser/search engines see a 404 status despite
		// real content rendering (harmless no-op for the legacy ?ah_expert= form,
		// which was never a 404 to begin with).
		global $wp_query;
		$wp_query->is_404 = false;
		status_header( 200 );
		nocache_headers();
		$_ver = defined( 'LOCAL_CACHE_VERSION' ) ? LOCAL_CACHE_VERSION : (defined( 'ADN_THEME_VERSION' ) ? ADN_THEME_VERSION : '1.0');
		wp_enqueue_style( 'adn-ask-expert-style', ADN_THEME_URI . '/assets/css/ask_expert.css', array(), $_ver );
		wp_enqueue_script( 'adn-ask-expert-script', ADN_THEME_URI . '/assets/js/ask_expert.js', array(), $_ver, true );
		wp_localize_script( 'adn-ask-expert-script', 'adnExpert', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'adn_expert_contact' ),
		) );
		include $template;
		exit;
	}
}

/**
 * Expert contact form AJAX handler - used by both the listing page and the profile page.
 */
function adn_expert_contact_ajax() {
	check_ajax_referer( 'adn_expert_contact', 'nonce' );
	$slug         = sanitize_key( wp_unslash( isset( $_POST['expert_slug'] )   ? $_POST['expert_slug']   : '' ) );
	$sender_name  = sanitize_text_field( wp_unslash( isset( $_POST['sender_name'] )  ? $_POST['sender_name']  : '' ) );
	$sender_email = sanitize_email( wp_unslash( isset( $_POST['sender_email'] ) ? $_POST['sender_email'] : '' ) );
	$sender_phone = sanitize_text_field( wp_unslash( isset( $_POST['sender_phone'] ) ? $_POST['sender_phone'] : '' ) );
	$message      = sanitize_textarea_field( wp_unslash( isset( $_POST['message'] )  ? $_POST['message']  : '' ) );

	if ( '' === $sender_name || '' === $sender_email || '' === $message ) {
		wp_send_json_error( array( 'message' => __( 'Please fill in all required fields.', ADN_TEXT_DOMAIN ) ) );
	}
	if ( ! is_email( $sender_email ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', ADN_TEXT_DOMAIN ) ) );
	}

	$expert      = class_exists( 'AH_Expert_DB' ) ? AH_Expert_DB::get( $slug ) : null;
	$expert_name = $expert ? $expert['name'] : 'Expert';
	$to_email    = ( $expert && ! empty( $expert['email'] ) ) ? $expert['email'] : get_option( 'admin_email' );

	$subject = sprintf( '[' . SITE_BRAND_NAME . '] Enquiry for %s from %s', $expert_name, $sender_name );
	$body    = "Name: {$sender_name}\nEmail: {$sender_email}\nPhone: {$sender_phone}\n\nMessage:\n{$message}";
	$headers = array( 'Content-Type: text/plain; charset=UTF-8', "Reply-To: {$sender_name} <{$sender_email}>" );

	$sent = wp_mail( $to_email, $subject, $body, $headers );
	if ( $sent ) {
		wp_send_json_success( array( 'message' => sprintf( __( '%s will be in touch shortly.', ADN_TEXT_DOMAIN ), $expert_name ) ) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Message could not be sent. Please try again.', ADN_TEXT_DOMAIN ) ) );
	}
}

/**
 * Post related articles AJAX.
 *
 * Accepts a post_id. Looks up the CMS taxonomy term IDs linked to that post,
 * then returns up to 4 randomly picked published articles from those same terms
 * (excluding the source post itself). Each article includes its WP thumbnail.
 */
function adn_post_related_articles_ajax() {
	$post_id = absint( isset( $_POST['post_id'] ) ? $_POST['post_id'] : 0 );
	if ( ! $post_id || ! function_exists( 'adn_cms_available' ) || ! adn_cms_available() ) {
		wp_send_json_success( array( 'articles' => array() ) );
		return;
	}

	// Find CMS taxonomy term IDs linked to this post.
	global $wpdb;
	$ct       = adn_cms_table( 'content_taxonomies' );
	$term_ids = $wpdb->get_col( $wpdb->prepare(
		"SELECT taxonomy_id FROM `{$ct}` WHERE object_type = 'wp_post' AND object_id = %d",
		$post_id
	) );
	$term_ids = array_map( 'absint', (array) $term_ids );

	$articles = array();

	if ( ! empty( $term_ids ) && function_exists( 'adn_cms_articles' ) && function_exists( 'adn_cms_post_url' ) ) {
		$pool = (array) adn_cms_articles( 40, $term_ids );

		// Exclude the current post, shuffle, take 4.
		$pool = array_values( array_filter( $pool, function ( $p ) use ( $post_id ) {
			return (int) $p->ID !== $post_id;
		} ) );
		shuffle( $pool );
		$pool = array_slice( $pool, 0, 6 );

		foreach ( $pool as $p ) {
			$thumb   = get_the_post_thumbnail_url( $p->ID, 'medium' );
			$excerpt = isset( $p->excerpt ) && '' !== $p->excerpt ? (string) $p->excerpt : (string) ( $p->content ?? '' );
			$articles[] = array(
				'title'     => isset( $p->title ) ? (string) $p->title : '',
				'url'       => adn_cms_post_url( $p ),
				'excerpt'   => wp_trim_words( wp_strip_all_tags( $excerpt ), 16, '…' ),
				'date'      => function_exists( 'adn_cms_post_date' ) ? adn_cms_post_date( $p ) : '',
				'thumbnail' => $thumb ? (string) $thumb : '',
			);
		}
	}

	wp_send_json_success( array( 'articles' => $articles ) );
}

/**
 * Expert profile unlock AJAX - verifies password and returns a cookie token.
 */
function adn_expert_unlock_ajax() {
	check_ajax_referer( 'adn_expert_unlock', 'nonce' );
	$submitted = sanitize_text_field( wp_unslash( isset( $_POST['unlock_password'] ) ? $_POST['unlock_password'] : '' ) );
	$banner    = get_option( 'adn_expert_banner', array() );
	$stored    = isset( $banner['unlock_password'] ) ? (string) $banner['unlock_password'] : '';
	if ( '' === $stored ) {
		wp_send_json_error( array( 'message' => __( 'No unlock password is set.', ADN_TEXT_DOMAIN ) ) );
	}
	if ( '' === $submitted || ! hash_equals( $stored, $submitted ) ) {
		wp_send_json_error( array( 'message' => __( 'Incorrect password. Please try again.', ADN_TEXT_DOMAIN ) ) );
	}
	$token = hash_hmac( 'sha256', $stored, wp_salt( 'secure_auth' ) );
	wp_send_json_success( array( 'token' => $token ) );
}

/**
 * Return calculator cards that are explicitly assigned to a parent term slug.
 * This keeps calculator visibility tied to the parent-term level only.
 */
function adn_get_parent_term_calculator_cards( $parent_slug, $limit = 0 ) {
	$parent_slug = sanitize_key( (string) $parent_slug );
	if ( '' === $parent_slug || ! function_exists( 'adn_calculators' ) ) {
		return array();
	}

	$all_tools = adn_calculators();
	$meta_all  = get_option( 'adn_calculators_meta', array() );
	$items     = array();
	$parent_slug_lc = strtolower( $parent_slug );

	foreach ( $all_tools as $ckey => $creg ) {
		$cmeta = isset( $meta_all[ $ckey ] ) && is_array( $meta_all[ $ckey ] ) ? $meta_all[ $ckey ] : array();
		if ( array_key_exists( 'enabled', $cmeta ) && empty( $cmeta['enabled'] ) ) {
			continue;
		}
		if ( ! empty( $cmeta['hidden_from_listing'] ) ) {
			continue;
		}

		$_pt_list = ! empty( $cmeta['parent_terms'] ) && is_array( $cmeta['parent_terms'] ) ? $cmeta['parent_terms'] : array();
		if ( empty( $_pt_list ) ) {
			continue;
		}

		$_pt_lc = array_map( 'strtolower', array_map( 'trim', $_pt_list ) );
		if ( ! in_array( $parent_slug_lc, $_pt_lc, true ) ) {
			continue;
		}

		$thumb = '';
		if ( ! empty( $cmeta['thumbnail_id'] ) ) {
			$t = wp_get_attachment_image_url( (int) $cmeta['thumbnail_id'], 'thumbnail' );
			$thumb = $t ? (string) $t : '';
		}

		$desc = '';
		if ( ! empty( $cmeta['desc'] ) ) {
			$desc = wp_strip_all_tags( (string) $cmeta['desc'] );
		} elseif ( ! empty( $cmeta['description'] ) ) {
			$desc = wp_strip_all_tags( (string) $cmeta['description'] );
		} elseif ( ! empty( $creg['description'] ) ) {
			$desc = wp_strip_all_tags( (string) $creg['description'] );
		}
		if ( $desc !== '' ) {
			$desc = wp_trim_words( $desc, 12 );
		}

		$items[] = array(
			'key'       => sanitize_key( $ckey ),
			'icon'      => ! empty( $cmeta['icon'] ) ? (string) $cmeta['icon'] : ( ! empty( $creg['icon'] ) ? (string) $creg['icon'] : '🧮' ),
			'label'     => ! empty( $cmeta['label'] ) ? (string) $cmeta['label'] : ( ! empty( $creg['title'] ) ? (string) $creg['title'] : $ckey ),
			'desc'      => $desc,
			'url'       => ! empty( $cmeta['card_url'] ) ? (string) $cmeta['card_url'] : adn_calc_page_url( $ckey ),
			'thumbnail' => $thumb,
			'highlight' => ! empty( $cmeta['highlight'] ) ? (string) $cmeta['highlight'] : '',
		);

		if ( $limit > 0 && count( $items ) >= $limit ) {
			break;
		}
	}

	return $items;
}

/**
 * [adn_cat_calculators slug="buying"]
 * Renders a grid of calculator cards for the parent term only.
 */
function adn_shortcode_cat_calculators( $atts ) {
	$atts = shortcode_atts( array( 'slug' => '' ), $atts, 'adn_cat_calculators' );
	$slug = sanitize_key( $atts['slug'] );
	if ( ! $slug ) {
		return '';
	}

	$items = adn_get_parent_term_calculator_cards( $slug );
	if ( empty( $items ) ) {
		return '';
	}

	$heading = '';
	ob_start();
	echo '<div class="tool-grid tool-grid--7col">';
	foreach ( $items as $card ) {
		adn_component( 'cards/tool_card', array( 'card' => array(
			'icon' => $card['icon'],
			'name' => $card['label'],
			'desc' => $card['desc'] ?? '',
			'url'  => $card['url'],
		) ) );
	}
	echo '</div>';
	return ob_get_clean();
}

/**
 * [adn_cookie_preferences]
 * Embeds the same "Manage Preferences" toggle form the cookie banner opens as
 * a modal, but inline in page content - meant for the Cookie Policy page, so a
 * returning visitor can change their mind without hunting for the banner.
 * cookie-consent.js finds this container on every page load (mountEmbeds())
 * and fills it in; the markup below is only the placeholder + no-JS fallback.
 */
function adn_shortcode_cookie_preferences( $atts ) {
	ob_start();
	?>
	<div class="adn-cookie-prefs-embed">
		<div data-adn-cookie-prefs="embed">
			<noscript><?php esc_html_e( 'Enable JavaScript to manage your cookie preferences.', ADN_TEXT_DOMAIN ); ?></noscript>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

