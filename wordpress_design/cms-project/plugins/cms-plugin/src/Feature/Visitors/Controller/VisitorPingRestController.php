<?php
namespace Ah\Cms\Feature\Visitors\Controller;

defined( 'ABSPATH' ) || exit;

class VisitorPingRestController {

	public static function registerRoutes(): void {
		\register_rest_route( 'ah-visitors/v1', '/ping', [
			'methods'             => 'POST',
			'callback'            => [ self::class, 'handlePing' ],
			'permission_callback' => '__return_true',
		] );
	}

	public static function handlePing( \WP_REST_Request $request ): \WP_REST_Response {
		$ip = $request->get_param( 'ip' ) ?? '';
		$page = $request->get_param( 'page' ) ?? '';
		$ua = $request->get_param( 'ua' ) ?? '';

		$ip = sanitize_text_field( $ip );
		$page = sanitize_text_field( $page );
		$ua = sanitize_text_field( $ua );

		if ( empty( $ip ) ) {
			return new \WP_REST_Response( [ 'error' => 'IP required' ], 400 );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'ah_visitor_logs';

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) !== $table ) {
			return new \WP_REST_Response( [ 'error' => 'Table not found' ], 500 );
		}

		$wpdb->insert( $table, [
			'ip_address' => $ip,
			'page_url'   => $page,
			'user_agent' => $ua,
			'created_at' => current_time( 'mysql' ),
		], [ '%s', '%s', '%s', '%s' ] );

		return new \WP_REST_Response( [ 'success' => true ], 200 );
	}
}
