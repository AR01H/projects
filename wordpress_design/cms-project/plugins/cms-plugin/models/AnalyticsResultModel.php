<?php
defined( 'ABSPATH' ) || exit;

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
