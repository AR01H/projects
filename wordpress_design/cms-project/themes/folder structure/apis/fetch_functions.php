<?php
/**
 * apis/fetch_functions.php — REST API Route Registration
 *
 * RULE: All REST routes defined as one $routes array — loop registers them.
 *       All constants from static/page-sample.php (NPT_API_NS etc.).
 *       Every single-item callback runs npt_maybe_redirect() first.
 */

defined( 'ABSPATH' ) || exit;

// ── 1. Route definitions (array-driven) ──────────────────────────
// Add new routes here only — never add another register_rest_route() call.
$npt_routes = [
    [
        'route'      => '/posts',
        'methods'    => 'GET',
        'callback'   => 'npt_api_get_posts',
        'permission' => '__return_true',
    ],
    [
        'route'      => '/posts/(?P<id>\d+)',
        'methods'    => 'GET',
        'callback'   => 'npt_api_get_single_post',
        'permission' => '__return_true',
    ],
    [
        'route'      => '/posts/slug/(?P<slug>[a-z0-9-]+)',
        'methods'    => 'GET',
        'callback'   => 'npt_api_get_post_by_slug',   // alternate slug lookup
        'permission' => '__return_true',
    ],
    [
        'route'      => '/portfolios',
        'methods'    => 'GET',
        'callback'   => 'npt_api_get_portfolios',
        'permission' => '__return_true',
    ],
    [
        'route'      => '/portfolios/(?P<id>\d+)',
        'methods'    => 'GET',
        'callback'   => 'npt_api_get_single_portfolio',
        'permission' => '__return_true',
    ],
    [
        'route'      => '/contact',
        'methods'    => 'POST',
        'callback'   => 'npt_api_submit_contact',
        'permission' => '__return_true',
    ],
    // add more routes here …
];

// ── 2. Register all routes (loop — no repetition) ─────────────────
add_action( 'rest_api_init', function () use ( $npt_routes ): void {
    foreach ( $npt_routes as $route ) {
        register_rest_route( NPT_API_NS, $route['route'], [
            'methods'             => $route['methods'],
            'callback'            => $route['callback'],
            'permission_callback' => $route['permission'],
        ] );
    }
} );

// ═══════════════════════════════════════════════════════════════════
// ── 3. Callback functions
// ═══════════════════════════════════════════════════════════════════

/**
 * GET /npt/v1/posts
 * Paginated list of blog posts.
 */
function npt_api_get_posts( WP_REST_Request $request ): WP_REST_Response {
    $page     = max( 1, (int) $request->get_param( 'page' ) );
    $per_page = max( 1, (int) $request->get_param( 'per_page' ) ?: NPT_POSTS_PER_PAGE );

    $result = npt_fetch_blog_posts( $page, $per_page );

    return new WP_REST_Response( [
        'data'       => $result['items'],
        'total'      => $result['total'],
        'total_pages' => $result['pages'],
    ], 200 );
}

/**
 * GET /npt/v1/posts/{id}
 * Single post by ID — handles redirect if post has one set.
 */
function npt_api_get_single_post( WP_REST_Request $request ): WP_REST_Response {
    $id   = (int) $request->get_param( 'id' );
    $post = get_post( $id );

    if ( ! $post || $post->post_status !== 'publish' ) {
        return new WP_REST_Response( [ 'error' => 'Not found.' ], 404 );
    }

    $model = npt_model_post( $post );

    // ── Redirect check: return 301 response if redirect_url is set ──
    $redirect = npt_maybe_redirect( $model, 'rest' );
    if ( $redirect instanceof WP_REST_Response ) {
        return $redirect;
    }

    return new WP_REST_Response( $model, 200 );
}

/**
 * GET /npt/v1/posts/slug/{slug}
 * Alternate slug lookup — resolves by slug OR npt_alternate_slug meta.
 */
function npt_api_get_post_by_slug( WP_REST_Request $request ): WP_REST_Response {
    $slug = sanitize_title( $request->get_param( 'slug' ) );

    // 1. Try primary slug first
    $post = get_page_by_path( $slug, OBJECT, 'post' );

    // 2. Fall back to alternate slug meta search
    if ( ! $post ) {
        $query = new WP_Query( [
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'meta_query'     => [ [
                'key'   => NPT_META_ALT_SLUG,
                'value' => $slug,
            ] ],
        ] );
        $post = $query->posts[0] ?? null;
    }

    if ( ! $post ) {
        return new WP_REST_Response( [ 'error' => 'Not found.' ], 404 );
    }

    $model    = npt_model_post( $post );
    $redirect = npt_maybe_redirect( $model, 'rest' );
    if ( $redirect instanceof WP_REST_Response ) {
        return $redirect;
    }

    return new WP_REST_Response( $model, 200 );
}

/**
 * GET /npt/v1/portfolios
 * Paginated list of portfolio items.
 */
function npt_api_get_portfolios( WP_REST_Request $request ): WP_REST_Response {
    $per_page = max( 1, (int) $request->get_param( 'per_page' ) ?: NPT_POSTS_PER_PAGE );
    $category = sanitize_title( $request->get_param( 'category' ) ?: '' ) ?: null;

    $result = npt_fetch_portfolios( $category, $per_page );

    return new WP_REST_Response( [
        'data'        => $result['items'],
        'total'       => $result['total'],
        'total_pages' => $result['pages'],
    ], 200 );
}

/**
 * GET /npt/v1/portfolios/{id}
 * Single portfolio — handles redirect.
 */
function npt_api_get_single_portfolio( WP_REST_Request $request ): WP_REST_Response {
    $id   = (int) $request->get_param( 'id' );
    $post = get_post( $id );

    if ( ! $post || $post->post_type !== 'portfolio' || $post->post_status !== 'publish' ) {
        return new WP_REST_Response( [ 'error' => 'Not found.' ], 404 );
    }

    $model    = npt_model_portfolio( $post );
    $redirect = npt_maybe_redirect( $model, 'rest' );
    if ( $redirect instanceof WP_REST_Response ) {
        return $redirect;
    }

    return new WP_REST_Response( $model, 200 );
}

/**
 * POST /npt/v1/contact
 * Contact form submission endpoint.
 */
function npt_api_submit_contact( WP_REST_Request $request ): WP_REST_Response {
    $name    = sanitize_text_field( $request->get_param( 'name' ) );
    $email   = sanitize_email( $request->get_param( 'email' ) );
    $message = sanitize_textarea_field( $request->get_param( 'message' ) );

    if ( empty( $name ) || ! is_email( $email ) || empty( $message ) ) {
        return new WP_REST_Response( [ 'error' => 'Invalid input.' ], 422 );
    }

    // stub — send email, save to DB, trigger CRM webhook, etc.

    return new WP_REST_Response( [ 'success' => true, 'message' => 'Message received.' ], 200 );
}
