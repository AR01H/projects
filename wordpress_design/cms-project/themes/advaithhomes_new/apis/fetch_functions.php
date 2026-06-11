<?php
/**
 * apis/fetch_functions.php - REST API route registration (single source of truth).
 *
 * RULE: Every route is one entry in the $adn_routes array; a single loop
 *       registers them all. Never add another register_rest_route() call.
 *       Namespace + page size come from includes/core_settings.php
 *       (ADN_API_NS, ADN_API_PER_PAGE). Models live in apis/models/post.php.
 *
 * Base URL: /wp-json/advaithhomes/v1/...
 */

defined( 'ABSPATH' ) || exit;

// ── 1. Route table - add new endpoints here only ─────────────────────
$adn_routes = array(
	array(
		'route'      => '/posts',
		'methods'    => 'GET',
		'callback'   => 'adn_api_get_posts',
		'permission' => '__return_true',
	),
	array(
		'route'      => '/posts/(?P<id>\d+)',
		'methods'    => 'GET',
		'callback'   => 'adn_api_get_single_post',
		'permission' => '__return_true',
	),
	array(
		'route'      => '/posts/slug/(?P<slug>[a-z0-9-]+)',
		'methods'    => 'GET',
		'callback'   => 'adn_api_get_post_by_slug',
		'permission' => '__return_true',
	),
	array(
		'route'      => '/faqs',
		'methods'    => 'GET',
		'callback'   => 'adn_api_get_faqs',
		'permission' => '__return_true',
	),
	array(
		'route'      => '/contact',
		'methods'    => 'POST',
		'callback'   => 'adn_api_submit_contact',
		'permission' => '__return_true',
	),
	// add more routes here …
);

// ── 2. Register them all (one loop, no repetition) ───────────────────
add_action( 'rest_api_init', function () use ( $adn_routes ) {
	foreach ( $adn_routes as $route ) {
		register_rest_route( ADN_API_NS, $route['route'], array(
			'methods'             => $route['methods'],
			'callback'            => $route['callback'],
			'permission_callback' => $route['permission'],
		) );
	}
} );

// ═══════════════════════════════════════════════════════════════════
// 3. Callbacks
// ═══════════════════════════════════════════════════════════════════

/**
 * GET /advaithhomes/v1/posts - paginated blog list.
 * Query: ?page=1&per_page=9
 */
function adn_api_get_posts( WP_REST_Request $request ): WP_REST_Response {
	$page     = max( 1, (int) $request->get_param( 'page' ) );
	$per_page = (int) $request->get_param( 'per_page' );
	$per_page = $per_page > 0 ? min( $per_page, 50 ) : ADN_API_PER_PAGE;

	$query = new WP_Query( array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => $per_page,
		'paged'          => $page,
	) );

	return new WP_REST_Response( array(
		'data'        => array_map( 'adn_model_post', $query->posts ),
		'total'       => (int) $query->found_posts,
		'total_pages' => (int) $query->max_num_pages,
		'page'        => $page,
	), 200 );
}

/**
 * GET /advaithhomes/v1/posts/{id} - single post (honours redirect meta).
 */
function adn_api_get_single_post( WP_REST_Request $request ): WP_REST_Response {
	$post = get_post( (int) $request->get_param( 'id' ) );

	if ( ! $post || 'post' !== $post->post_type || 'publish' !== $post->post_status ) {
		return new WP_REST_Response( array( 'error' => 'Not found.' ), 404 );
	}

	$model    = adn_model_post( $post );
	$redirect = adn_maybe_redirect( $model, 'rest' );
	if ( $redirect instanceof WP_REST_Response ) {
		return $redirect;
	}

	return new WP_REST_Response( $model, 200 );
}

/**
 * GET /advaithhomes/v1/posts/slug/{slug} - single post by slug.
 */
function adn_api_get_post_by_slug( WP_REST_Request $request ): WP_REST_Response {
	$slug = sanitize_title( (string) $request->get_param( 'slug' ) );
	$post = get_page_by_path( $slug, OBJECT, 'post' );

	if ( ! $post || 'publish' !== $post->post_status ) {
		return new WP_REST_Response( array( 'error' => 'Not found.' ), 404 );
	}

	$model    = adn_model_post( $post );
	$redirect = adn_maybe_redirect( $model, 'rest' );
	if ( $redirect instanceof WP_REST_Response ) {
		return $redirect;
	}

	return new WP_REST_Response( $model, 200 );
}

/**
 * GET /advaithhomes/v1/faqs - reads data/csv/faqs.csv via the data loader.
 */
function adn_api_get_faqs( WP_REST_Request $request ): WP_REST_Response {
	$faqs = class_exists( 'ADN_Real_Loader' ) ? ADN_Real_Loader::csv( 'faqs' ) : array();

	return new WP_REST_Response( array(
		'data'  => $faqs,
		'total' => count( $faqs ),
	), 200 );
}

/**
 * POST /advaithhomes/v1/contact - contact form submission.
 * Fires the automation rules engine (ADN_Rules::CONTACT_FORM) when the CMS
 * plugin is active, so admins can attach email / WhatsApp actions to it.
 */
function adn_api_submit_contact( WP_REST_Request $request ): WP_REST_Response {
	// Honeypot: real users never fill adn_hp - pretend success so bots learn nothing.
	if ( '' !== (string) $request->get_param( 'adn_hp' ) ) {
		return new WP_REST_Response( array( 'success' => true, 'message' => 'Thanks!' ), 200 );
	}

	$name    = sanitize_text_field( (string) $request->get_param( 'name' ) );
	$email   = sanitize_email( (string) $request->get_param( 'email' ) );
	$phone   = sanitize_text_field( (string) $request->get_param( 'phone' ) );
	$topic   = sanitize_key( (string) $request->get_param( 'topic' ) );
	$message = sanitize_textarea_field( (string) $request->get_param( 'message' ) );

	if ( '' === $name || ! is_email( $email ) || '' === $message ) {
		return new WP_REST_Response( array(
			'success' => false,
			'error'   => 'Please provide a valid name, email and message.',
		), 422 );
	}

	// ── Store in DB ───────────────────────────────────────────────────
	ADN_Schema::create_all();
	global $wpdb;
	$inserted = $wpdb->insert(
		ADN_Schema::contact_table(),
		array(
			'name'       => $name,
			'email'      => $email,
			'phone'      => $phone,
			'topic'      => $topic ?: 'general',
			'message'    => $message,
			'ip_address' => sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ),
			'status'     => 'new',
			'created_at' => current_time( 'mysql' ),
		),
		array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
	);

	if ( false === $inserted ) {
		return new WP_REST_Response( array(
			'success' => false,
			'error'   => 'Sorry, your message could not be saved. Please try again.',
		), 500 );
	}

	// ── Rules Engine: admins attach email/WhatsApp actions to this trigger ──
	if ( class_exists( 'AH_Rules_Engine' ) && class_exists( 'ADN_Rules' ) ) {
		AH_Rules_Engine::evaluate( ADN_Rules::CONTACT_FORM, array(
			'submission_id' => (int) $wpdb->insert_id,
			'name'          => $name,
			'email'         => $email,
			'phone'         => $phone,
			'topic'         => $topic ?: 'general',
			'message'       => $message,
			'site_url'      => home_url(),
			'submitted_at'  => current_time( 'Y-m-d H:i:s' ),
		), true );
	}

	return new WP_REST_Response( array(
		'success' => true,
		'message' => 'Thanks! Your message has been received.',
	), 200 );
}
