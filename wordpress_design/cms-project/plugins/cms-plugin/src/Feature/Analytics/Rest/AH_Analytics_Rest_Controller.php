<?php
defined( 'ABSPATH' ) || exit;

/**
 * Analytics REST Controller — handles REST API endpoints for analytics reports.
 * Extracted from ah-cms.php inline rest_api_init handler.
 */
class AH_Analytics_Rest_Controller {

	public static function registerRoutes(): void {
		register_rest_route( 'ah-analytics/v1', '/report/(?P<id>\d+)', [
			'methods'             => 'GET',
			'callback'            => [ self::class, 'getReport' ],
			'permission_callback' => '__return_true',
		] );
	}

	public static function getReport( WP_REST_Request $request ) {
		$id     = (int) $request->get_param( 'id' );
		$report = ( new AH_Analytics_Report_Model() )->find( $id );

		if ( ! $report ) {
			return new WP_Error( 'not_found', 'Report not found.', [ 'status' => 404 ] );
		}

		if ( ( $report->api_visibility ?? 'private' ) === 'private' ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return new WP_Error( 'forbidden', 'You do not have permission to view this report.', [ 'status' => 403 ] );
			}
		}

		global $wpdb;
		$results = [];

		if ( ( $report->report_type ?? 'sql' ) === 'sql' ) {
			$sql = trim( $report->query_sql ?? '' );
			if ( ! $sql ) {
				return new WP_Error( 'empty_query', 'SQL query is empty.', [ 'status' => 500 ] );
			}
			$err = AH_Analytics_Ajax::validate_query( $sql );
			if ( $err ) {
				return new WP_Error( 'invalid_query', $err, [ 'status' => 400 ] );
			}
			$results = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			if ( $wpdb->last_error ) {
				return new WP_Error( 'db_error', $wpdb->last_error, [ 'status' => 500 ] );
			}
		} else {
			$php = trim( $report->query_php ?? '' );
			if ( ! $php ) {
				return new WP_Error( 'empty_query', 'PHP code is empty.', [ 'status' => 500 ] );
			}
			try {
				ob_start();
				$php_res = eval( $php );
				ob_end_clean();
				if ( is_array( $php_res ) ) {
					$results = $php_res;
				} else {
					return new WP_Error( 'invalid_return', 'PHP code must return an array.', [ 'status' => 500 ] );
				}
			} catch ( \Throwable $e ) {
				if ( ob_get_level() ) ob_end_clean();
				return new WP_Error( 'php_error', 'PHP Error: ' . $e->getMessage(), [ 'status' => 500 ] );
			}
		}

		( new AH_Analytics_Report_Model() )->bump_run_count( $id );

		return rest_ensure_response( [
			'report_name' => $report->name,
			'row_count'   => count( $results ?: [] ),
			'data'        => $results ?: [],
		] );
	}
}
