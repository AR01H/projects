<?php
/**
 * middleware/page-sample.php - Request Middleware Sample
 *
 * Covers:
 *   - REST API authentication middleware
 *   - Rate-limiting stub
 *   - CORS header injection
 *
 * RULE: Middleware only inspects/modifies the request lifecycle.
 *       Never fetch data or render output here.
 *       Attach everything via filters on 'rest_pre_dispatch' or 'rest_request_before_callbacks'.
 */

defined( 'ABSPATH' ) || exit;

// ═══════════════════════════════════════════════════════════════════
// ── 1. Middleware stack (array-driven) ──────────────────────────────
// ═══════════════════════════════════════════════════════════════════

$npt_middleware = [
    // [ filter/action,                    callback,                    priority ]
    [ 'rest_pre_dispatch',                 'npt_mw_check_auth',         5  ],
    [ 'rest_pre_dispatch',                 'npt_mw_rate_limit',         6  ],
    [ 'rest_send_nocache_headers',         'npt_mw_cors_headers',       10 ],
];

foreach ( $npt_middleware as [ $hook, $cb, $prio ] ) {
    // rest_pre_dispatch is a filter (3 args); rest_send_nocache_headers is an action
    if ( str_contains( $hook, 'headers' ) ) {
        add_action( $hook, $cb, $prio );
    } else {
        add_filter( $hook, $cb, $prio, 3 );
    }
}

// ═══════════════════════════════════════════════════════════════════
// ── 2. Auth Middleware ──────────────────────────────────────────────
// ═══════════════════════════════════════════════════════════════════

/**
 * Optionally verify a Bearer token before any REST route runs.
 * Return null to let the request proceed, or a WP_Error to block it.
 *
 * @param mixed           $result  Current pre-dispatch result.
 * @param WP_REST_Server  $server
 * @param WP_REST_Request $request
 */
function npt_mw_check_auth( mixed $result, WP_REST_Server $server, WP_REST_Request $request ): mixed {
    // Only guard routes under /npt/v1/protected/*
    if ( ! str_starts_with( $request->get_route(), '/npt/v1/protected' ) ) {
        return $result; // pass through
    }

    $token = $request->get_header( 'Authorization' );

    if ( empty( $token ) ) {
        return new WP_Error( 'rest_forbidden', 'Authorization header missing.', [ 'status' => 401 ] );
    }

    // stub - validate JWT / API key here
    // if ( ! npt_validate_token( $token ) ) {
    //     return new WP_Error( 'rest_forbidden', 'Invalid token.', [ 'status' => 403 ] );
    // }

    return $result; // pass through
}

// ═══════════════════════════════════════════════════════════════════
// ── 3. Rate Limit Middleware ────────────────────────────────────────
// ═══════════════════════════════════════════════════════════════════

/**
 * Simple transient-based rate limiter stub.
 * In production, replace with Redis or a dedicated plugin.
 */
function npt_mw_rate_limit( mixed $result, WP_REST_Server $server, WP_REST_Request $request ): mixed {
    $ip      = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key     = 'npt_rate_' . md5( $ip );
    $limit   = 60;   // max requests
    $window  = 60;   // seconds

    $count = (int) get_transient( $key );

    if ( $count >= $limit ) {
        return new WP_Error( 'rest_too_many_requests', 'Rate limit exceeded.', [ 'status' => 429 ] );
    }

    if ( $count === 0 ) {
        set_transient( $key, 1, $window );
    } else {
        set_transient( $key, $count + 1, $window );
    }

    return $result;
}

// ═══════════════════════════════════════════════════════════════════
// ── 4. CORS Headers ─────────────────────────────────────────────────
// ═══════════════════════════════════════════════════════════════════

/**
 * Add CORS headers for headless / decoupled setups.
 * Restrict origins in production!
 */
function npt_mw_cors_headers(): void {
    $allowed_origins = [
        'https://yourfrontenddomain.com',
        // add more allowed origins …
    ];

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    if ( in_array( $origin, $allowed_origins, true ) ) {
        header( "Access-Control-Allow-Origin: {$origin}" );
        header( 'Access-Control-Allow-Methods: GET, POST, OPTIONS' );
        header( 'Access-Control-Allow-Headers: Authorization, Content-Type' );
    }
}
