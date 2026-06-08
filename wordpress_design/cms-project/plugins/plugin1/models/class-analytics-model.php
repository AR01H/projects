<?php
defined( 'ABSPATH' ) || exit;

/* ── Reports ──────────────────────────────────────────────────────── */

class AH_Analytics_Report_Model extends AH_Model_Base {

	protected string $table_suffix = 'analytics_reports';

	public function all_with_last_result(): array {
		global $wpdb;
		$rt  = AH_DB_Helper::table( $this->table_suffix );
		$rst = AH_DB_Helper::table( 'analytics_results' );

		return $wpdb->get_results(
			"SELECT r.*,
			        res.status        AS last_status,
			        res.row_count     AS last_row_count,
			        res.exec_ms       AS last_exec_ms,
			        res.export_file   AS last_export_file
			FROM `{$rt}` r
			LEFT JOIN `{$rst}` res ON res.id = (
			    SELECT id FROM `{$rst}` WHERE report_id = r.id ORDER BY run_at DESC LIMIT 1
			)
			ORDER BY r.name ASC"
		);
	}

	/* Never log analytics runs into the audit trail */
	public function create( array $data ): int {
		global $wpdb;
		$now  = current_time( 'mysql' );
		$data = array_merge( [ 'created_at' => $now, 'updated_at' => $now ], $data );
		$wpdb->insert( $this->table(), $data );
		return (int) $wpdb->insert_id;
	}

	public function save_report( array $data ): int|false {
		$id = (int) ( $data['id'] ?? 0 );
		unset( $data['id'] );
		$data['updated_at'] = current_time( 'mysql' );

		if ( $id ) {
			global $wpdb;
			$wpdb->update( $this->table(), $data, [ 'id' => $id ] );
			return $id;
		}
		return $this->create( $data );
	}

	public function delete_with_results( int $id ): void {
		global $wpdb;
		/* Delete stored export files first */
		$rst = AH_DB_Helper::table( 'analytics_results' );
		$files = $wpdb->get_col( $wpdb->prepare( "SELECT export_file FROM `{$rst}` WHERE report_id = %d AND export_file != ''", $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		foreach ( $files as $path ) {
			$real = realpath( $path );
			$base = realpath( WP_CONTENT_DIR . '/uploads/ah-analytics' );
			if ( $real && $base && strpos( $real, $base ) === 0 && file_exists( $real ) ) {
				@unlink( $real ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			}
		}
		$wpdb->delete( $rst, [ 'report_id' => $id ], [ '%d' ] ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->delete( $this->table(), [ 'id' => $id ], [ '%d' ] );
	}

	public function bump_run_count( int $id ): void {
		global $wpdb;
		$t = $this->table();
		$wpdb->query( $wpdb->prepare( "UPDATE `{$t}` SET run_count = run_count + 1, last_run_at = %s WHERE id = %d", current_time( 'mysql' ), $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}
}

/* ── Results ──────────────────────────────────────────────────────── */

class AH_Analytics_Result_Model extends AH_Model_Base {

	protected string $table_suffix = 'analytics_results';

	public function store( int $report_id, array $result ): int {
		global $wpdb;
		$wpdb->insert( $this->table(), [
			'report_id'     => $report_id,
			'run_at'        => current_time( 'mysql' ),
			'row_count'     => $result['row_count']     ?? 0,
			'exec_ms'       => $result['exec_ms']       ?? 0,
			'status'        => $result['status']        ?? 'success',
			'result_json'   => $result['result_json']   ?? '[]',
			'error_message' => $result['error_message'] ?? null,
			'export_file'   => $result['export_file']   ?? null,
		] );
		return (int) $wpdb->insert_id;
	}

	public function latest_for( int $report_id ): ?object {
		global $wpdb;
		$t = $this->table();
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$t}` WHERE report_id = %d ORDER BY run_at DESC LIMIT 1", $report_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	public function history_for( int $report_id, int $limit = 20 ): array {
		global $wpdb;
		$t = $this->table();
		return $wpdb->get_results( $wpdb->prepare( "SELECT id, run_at, row_count, exec_ms, status, export_file FROM `{$t}` WHERE report_id = %d ORDER BY run_at DESC LIMIT %d", $report_id, $limit ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	public function update_export_file( int $result_id, string $path ): void {
		global $wpdb;
		$wpdb->update( $this->table(), [ 'export_file' => $path ], [ 'id' => $result_id ] );
	}
}
