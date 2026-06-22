<?php
/**
 * AH_Analytics_Ajax - AJAX handlers for the Analytics Reports page.
 *
 * Actions:
 *   ah_analytics_run    - execute query, store result, return table data
 *   ah_analytics_save   - create or update a report record
 *   ah_analytics_delete - delete a report and all its stored results + files
 *   ah_analytics_export - (re-)export the latest result as CSV or JSON to uploads
 *   ah_analytics_history- return run history rows for a report
 */

defined( 'ABSPATH' ) || exit;

class AH_Analytics_Ajax {

	/* Max rows returned to browser to keep responses fast */
	const PREVIEW_LIMIT = 500;

	public static function init(): void {
		$actions = [
			'ah_analytics_run',
			'ah_analytics_save',
			'ah_analytics_delete',
			'ah_analytics_export',
			'ah_analytics_history',
		];
		foreach ( $actions as $a ) {
			add_action( 'wp_ajax_' . $a, [ self::class, 'dispatch' ] );
		}
	}

	public static function dispatch(): void {
		$action = sanitize_key( $_POST['action'] ?? '' );
		if ( ! check_ajax_referer( 'ah_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Security check failed.' ], 403 );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized.' ], 403 );
		}

		switch ( $action ) {
			case 'ah_analytics_run':    self::handle_run();    break;
			case 'ah_analytics_save':   self::handle_save();   break;
			case 'ah_analytics_delete': self::handle_delete(); break;
			case 'ah_analytics_export': self::handle_export(); break;
			case 'ah_analytics_history':self::handle_history();break;
			default: wp_send_json_error( [ 'message' => 'Unknown action.' ], 400 );
		}
	}

	/* ── Run query ──────────────────────────────────────────────── */

	private static function handle_run(): void {
		$report_id = (int) ( $_POST['report_id'] ?? 0 );
		$sql       = trim( wp_unslash( $_POST['query_sql'] ?? '' ) );

		if ( ! $sql ) {
			wp_send_json_error( [ 'message' => 'No query provided.' ] );
		}

		$err = self::validate_query( $sql );
		if ( $err ) {
			wp_send_json_error( [ 'message' => $err ] );
		}

		global $wpdb;
		$start   = microtime( true );
		$results = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$exec_ms = (int) round( ( microtime( true ) - $start ) * 1000 );
		$db_err  = $wpdb->last_error;

		if ( $db_err ) {
			$payload = [
				'status'        => 'error',
				'error_message' => $db_err,
				'exec_ms'       => $exec_ms,
				'row_count'     => 0,
				'result_json'   => '[]',
			];
			if ( $report_id ) {
				( new AH_Analytics_Result_Model() )->store( $report_id, $payload );
			}
			wp_send_json_error( [ 'message' => $db_err, 'exec_ms' => $exec_ms ] );
		}

		$rows      = $results ?: [];
		$row_count = count( $rows );

		/* Truncate the in-DB JSON for very large results */
		$store_rows = array_slice( $rows, 0, 2000 );
		$payload    = [
			'status'        => 'success',
			'row_count'     => $row_count,
			'exec_ms'       => $exec_ms,
			'result_json'   => wp_json_encode( $store_rows ),
			'error_message' => null,
		];

		$result_id = 0;
		if ( $report_id ) {
			$result_id = ( new AH_Analytics_Result_Model() )->store( $report_id, $payload );
			( new AH_Analytics_Report_Model() )->bump_run_count( $report_id );
		}

		/* Return only PREVIEW_LIMIT rows to the browser */
		$preview = array_slice( $rows, 0, self::PREVIEW_LIMIT );
		$columns = ! empty( $rows ) ? array_keys( $rows[0] ) : [];

		wp_send_json_success( [
			'columns'   => $columns,
			'rows'      => $preview,
			'row_count' => $row_count,
			'exec_ms'   => $exec_ms,
			'result_id' => $result_id,
			'truncated' => $row_count > self::PREVIEW_LIMIT,
		] );
	}

	/* ── Save report ────────────────────────────────────────────── */

	private static function handle_save(): void {
		$id          = (int) ( $_POST['id'] ?? 0 );
		$name        = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
		$description = sanitize_textarea_field( wp_unslash( $_POST['description'] ?? '' ) );
		$sql         = trim( wp_unslash( $_POST['query_sql'] ?? '' ) );

		if ( ! $name || ! $sql ) {
			wp_send_json_error( [ 'message' => 'Name and query are required.' ] );
		}

		$err = self::validate_query( $sql );
		if ( $err ) {
			wp_send_json_error( [ 'message' => $err ] );
		}

		$saved_id = ( new AH_Analytics_Report_Model() )->save_report( [
			'id'          => $id,
			'name'        => $name,
			'description' => $description,
			'query_sql'   => $sql,
		] );

		wp_send_json_success( [ 'id' => $saved_id, 'message' => 'Report saved.' ] );
	}

	/* ── Delete report ──────────────────────────────────────────── */

	private static function handle_delete(): void {
		$id = (int) ( $_POST['id'] ?? 0 );
		if ( ! $id ) wp_send_json_error( [ 'message' => 'Missing id.' ] );

		( new AH_Analytics_Report_Model() )->delete_with_results( $id );
		wp_send_json_success( [ 'message' => 'Report deleted.' ] );
	}

	/* ── Export to file ─────────────────────────────────────────── */

	private static function handle_export(): void {
		$report_id = (int) ( $_POST['report_id'] ?? 0 );
		$result_id = (int) ( $_POST['result_id'] ?? 0 );
		$format    = sanitize_key( $_POST['format'] ?? 'csv' );

		if ( ! $report_id ) wp_send_json_error( [ 'message' => 'Missing report_id.' ] );

		$result_model = new AH_Analytics_Result_Model();
		$result       = $result_id
			? $result_model->find( $result_id )
			: $result_model->latest_for( $report_id );

		if ( ! $result || $result->status !== 'success' ) {
			wp_send_json_error( [ 'message' => 'No successful result to export. Run the query first.' ] );
		}

		$rows = json_decode( $result->result_json, true ) ?: [];
		if ( empty( $rows ) ) {
			wp_send_json_error( [ 'message' => 'Result is empty.' ] );
		}

		$report    = ( new AH_Analytics_Report_Model() )->find( $report_id );
		$slug      = $report ? sanitize_title( $report->name ) : 'report-' . $report_id;
		$timestamp = gmdate( 'Ymd-His' );

		$dir = self::export_dir();
		if ( is_wp_error( $dir ) ) {
			wp_send_json_error( [ 'message' => $dir->get_error_message() ] );
		}

		if ( $format === 'json' ) {
			$filename = "{$slug}-{$timestamp}.json";
			$filepath = $dir . '/' . $filename;
			file_put_contents( $filepath, wp_json_encode( $rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
		} else {
			$filename = "{$slug}-{$timestamp}.csv";
			$filepath = $dir . '/' . $filename;
			$fh       = fopen( $filepath, 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
			fputcsv( $fh, array_keys( $rows[0] ) );
			foreach ( $rows as $row ) fputcsv( $fh, $row );
			fclose( $fh ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
		}

		$result_model->update_export_file( (int) $result->id, $filepath );

		$uploads  = wp_upload_dir();
		$file_url = $uploads['baseurl'] . '/ah-analytics/' . $filename;

		wp_send_json_success( [
			'file_url'  => $file_url,
			'filename'  => $filename,
			'format'    => $format,
			'row_count' => count( $rows ),
		] );
	}

	/* ── Run history ─────────────────────────────────────────────── */

	private static function handle_history(): void {
		$report_id = (int) ( $_POST['report_id'] ?? 0 );
		if ( ! $report_id ) wp_send_json_error( [ 'message' => 'Missing report_id.' ] );

		$history = ( new AH_Analytics_Result_Model() )->history_for( $report_id );

		wp_send_json_success( [ 'history' => $history ] );
	}

	/* ── Validation ─────────────────────────────────────────────── */

	/**
	 * Validate that $sql is a safe read-only SELECT query.
	 * Returns an error string or empty string if valid.
	 */
	public static function validate_query( string $sql ): string {
		$clean = trim( preg_replace( '/\s+/', ' ', $sql ) );

		if ( ! preg_match( '/^SELECT\s/i', $clean ) ) {
			return 'Only SELECT queries are allowed.';
		}

		/* Blocklist: destructive / permission-changing / file-reading keywords */
		$blocked = [
			'INSERT', 'UPDATE', 'DELETE', 'DROP', 'ALTER', 'CREATE', 'TRUNCATE',
			'REPLACE', 'GRANT', 'REVOKE', 'RENAME', 'LOAD', 'OUTFILE', 'INFILE',
			'CALL', 'EXEC', 'EXECUTE', 'PROCEDURE', 'FUNCTION', 'TRIGGER',
			'INTO\s+OUTFILE', 'INTO\s+DUMPFILE',
		];

		foreach ( $blocked as $kw ) {
			if ( preg_match( '/\b' . $kw . '\b/i', $clean ) ) {
				return "Query contains a blocked keyword: {$kw}. Only read-only SELECT is permitted.";
			}
		}

		return '';
	}

	/* ── Upload dir ─────────────────────────────────────────────── */

	private static function export_dir(): string|WP_Error {
		$uploads = wp_upload_dir();
		$dir     = $uploads['basedir'] . '/ah-analytics';

		if ( ! file_exists( $dir ) ) {
			wp_mkdir_p( $dir );
			/* Prevent directory listing */
			file_put_contents( $dir . '/.htaccess', 'Options -Indexes' . PHP_EOL ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
		}

		if ( ! is_writable( $dir ) ) {
			return new WP_Error( 'not_writable', 'Export directory is not writable: ' . $dir );
		}

		return $dir;
	}
}
