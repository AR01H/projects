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

// Site-wide notice popup - once per day, resets if content changes.
add_action( 'wp_footer', 'adn_render_site_notice_popup' );
function adn_render_site_notice_popup(): void {
	if ( class_exists( 'AH_Notice_Helper' ) ) {
		AH_Notice_Helper::render_frontend_popup();
	}
}

// Change REST API URL prefix from /wp-json/ to /api/ → routes become /api/v1/...
add_filter( 'rest_url_prefix', function () { return 'api'; } );

// Flush rewrite rules when theme activates.
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

// Expert profile page routing (?ah_expert=SLUG).
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

/**
 * Expert profile page: serve pages/page-expert-single.php for ?ah_expert=SLUG.
 */
function adn_expert_full_page_render() {
	if ( ! isset( $_GET['ah_expert'] ) || '' === $_GET['ah_expert'] ) { return; }
	$slug = sanitize_key( wp_unslash( $_GET['ah_expert'] ) );
	if ( ! class_exists( 'AH_Expert_DB' ) ) { return; }
	$expert = AH_Expert_DB::get( $slug );
	if ( ! $expert || 'active' !== $expert['status'] ) { return; }
	$base     = realpath( ADN_THEME_DIR . '/pages' );
	$template = realpath( ADN_THEME_DIR . '/pages/page-expert-single.php' );
	if ( $base && $template && 0 === strpos( $template, $base ) && is_file( $template ) ) {
		nocache_headers();
		$_ver = defined( 'ADN_THEME_VERSION' ) ? ADN_THEME_VERSION : '1.0';
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
 * [adn_cat_calculators slug="buying"]
 * Renders a grid of calculator cards for the selected keys stored in AH_Category_Settings.
 */
function adn_shortcode_cat_calculators( $atts ) {
	$atts = shortcode_atts( array( 'slug' => '' ), $atts, 'adn_cat_calculators' );
	$slug = sanitize_key( $atts['slug'] );
	if ( ! $slug || ! class_exists( 'AH_Category_Settings' ) || ! function_exists( 'adn_calculators' ) ) {
		return '';
	}
	$calc_d       = AH_Category_Settings::get( $slug, 'calculators' );
	$selected     = ! empty( $calc_d['selected_keys'] ) && is_array( $calc_d['selected_keys'] ) ? $calc_d['selected_keys'] : array();
	if ( empty( $selected ) ) { return ''; }
	$all_tools    = adn_calculators();
	$calc_meta    = get_option( 'adn_calculators_meta', array() );
	$items        = array();
	foreach ( $selected as $key ) {
		$key = sanitize_key( $key );
		if ( ! isset( $all_tools[ $key ] ) ) { continue; }
		$reg   = $all_tools[ $key ];
		$cmeta = function_exists( 'adn_calculator_meta' ) ? adn_calculator_meta( $key ) : array();
		$items[] = array(
			'icon' => ! empty( $reg['icon'] )       ? (string) $reg['icon']        : '🧮',
			'name' => ! empty( $reg['title'] )      ? (string) $reg['title']       : $key,
			'url'  => ! empty( $cmeta['card_url'] ) ? (string) $cmeta['card_url']  : home_url( '/?ah_calc_page=' . rawurlencode( $key ) ),
		);
	}
	if ( empty( $items ) ) { return ''; }
	$heading = ! empty( $calc_d['heading'] ) ? (string) $calc_d['heading'] : '';
	ob_start();
	if ( $heading ) {
		echo '<div class="adn-cat-calc-heading">' . esc_html( $heading ) . '</div>';
	}
	echo '<div class="tool-grid tool-grid--7col">';
	foreach ( $items as $card ) {
		adn_component( 'cards/tool_card', array( 'card' => $card ) );
	}
	echo '</div>';
	return ob_get_clean();
}

