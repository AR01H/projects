<?php
defined( 'ABSPATH' ) || exit;

/**
 * AH_Rest_Routes — single place for every plugin REST API endpoint.
 *
 * HOW TO ADD A NEW ROUTE
 * ──────────────────────
 * Add one entry to the $routes array inside register():
 *
 *   array(
 *       'route'               => '/your-path',          // appended to namespace
 *       'methods'             => 'GET',                 // GET | POST | PUT | DELETE | GET,POST
 *       'callback'            => array( self::class, '_cb_your_path' ),
 *       'permission_callback' => '__return_true',       // or a real cap check
 *       'args'                => array(),               // optional WP_REST_Request args schema
 *   ),
 *
 * Then add the static callback method below (prefix with _cb_).
 * That is ALL you need to do — the loop registers it automatically.
 *
 * Namespace : adn/v1
 * URL prefix : /api/  (set by rest_url_prefix filter in theme/functions.php)
 * Full base  : https://site.com/api/adn/v1/
 */
class AH_Rest_Routes {

	const NS = 'adn/v1';

	public static function register(): void {

		$routes = array(

			// ── Visitors ────────────────────────────────────────────────
			// GET /api/adn/v1/visitors
			// Returns aggregated visitor counts (no auth required).
			array(
				'route'               => '/visitors',
				'methods'             => 'GET',
				'callback'            => array( self::class, '_cb_visitors_get' ),
				'permission_callback' => '__return_true',
			),

			// POST /api/adn/v1/visitors/ping
			// Records a page view (IP, slug, referrer, session). No auth required.
			array(
				'route'               => '/visitors/ping',
				'methods'             => 'POST',
				'callback'            => array( self::class, '_cb_visitors_ping' ),
				'permission_callback' => '__return_true',
			),

			// ── Add new routes below this line ───────────────────────────
			// Example:
			// array(
			//     'route'               => '/hello',
			//     'methods'             => 'GET',
			//     'callback'            => array( self::class, '_cb_hello' ),
			//     'permission_callback' => '__return_true',
			// ),

		);

		foreach ( $routes as $r ) {
			register_rest_route( self::NS, $r['route'], array(
				'methods'             => $r['methods'],
				'callback'            => $r['callback'],
				'permission_callback' => $r['permission_callback'] ?? '__return_true',
				'args'                => $r['args'] ?? array(),
			) );
		}
	}

	/* ══════════════════════════════════════════════════════════════════
	   VISITOR CALLBACKS
	   ══════════════════════════════════════════════════════════════════ */

	/** GET /visitors — return aggregated stats. */
	public static function _cb_visitors_get(): WP_REST_Response {
		$m = new AH_Visitor_Model();
		return new WP_REST_Response( array(
			'total'        => $m->total(),
			'total_unique' => $m->total_unique(),
			'today'        => $m->today(),
			'today_unique' => $m->today_unique(),
			'this_month'   => $m->this_month(),
		), 200 );
	}

	/** POST /visitors/ping — record a visit, return updated stats. */
	public static function _cb_visitors_ping( WP_REST_Request $req ): WP_REST_Response {
		// Resolve real client IP (handles Cloudflare / reverse proxies).
		$ip = '';
		foreach ( array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR' ) as $key ) {
			$raw = $_SERVER[ $key ] ?? '';
			if ( $raw ) { $ip = trim( explode( ',', $raw )[0] ); break; }
		}

		$body = $req->get_json_params() ?: array();

		$m = new AH_Visitor_Model();
		$m->record( array(
			'ip_address' => $ip,
			'page_url'   => sanitize_text_field( $body['url']        ?? '' ),
			'page_slug'  => sanitize_text_field( $body['slug']       ?? '' ),
			'referrer'   => sanitize_text_field( $body['referrer']   ?? '' ),
			'user_agent' => sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' ),
			'session_id' => sanitize_text_field( $body['session_id'] ?? '' ),
		) );

		return new WP_REST_Response( array(
			'total'        => $m->total(),
			'total_unique' => $m->total_unique(),
			'today'        => $m->today(),
			'today_unique' => $m->today_unique(),
			'this_month'   => $m->this_month(),
		), 200 );
	}

	/* ══════════════════════════════════════════════════════════════════
	   ADD NEW CALLBACKS BELOW
	   ══════════════════════════════════════════════════════════════════ */

}
