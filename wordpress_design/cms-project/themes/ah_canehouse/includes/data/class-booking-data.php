<?php
defined( 'ABSPATH' ) || exit;

class CH_Booking_Data {

	public static function statuses(): array {
		return [
			'new'         => [ 'label' => 'New',         'color' => '#2271b1' ],
			'contacted'   => [ 'label' => 'Contacted',   'color' => '#8c5a00' ],
			'in_progress' => [ 'label' => 'In Progress', 'color' => '#a16207' ],
			'confirmed'   => [ 'label' => 'Confirmed',   'color' => '#0a7c50' ],
			'completed'   => [ 'label' => 'Completed',   'color' => '#2d5a1b' ],
			'cancelled'   => [ 'label' => 'Cancelled',   'color' => '#666'    ],
			'declined'    => [ 'label' => 'Declined',    'color' => '#b91c1c' ],
		];
	}

	public static function get_all( array $args = [] ): array {
		global $wpdb;
		$table  = $wpdb->prefix . 'ch_booking_requests';
		$where  = '1=1';
		$params = [];

		if ( ! empty( $args['status'] ) ) {
			$where   .= ' AND status = %s';
			$params[] = $args['status'];
		}
		if ( ! empty( $args['search'] ) ) {
			$like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where   .= ' AND (name LIKE %s OR email LIKE %s OR phone LIKE %s OR location LIKE %s)';
			$params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
		}

		$limit  = isset( $args['limit'] )  ? (int) $args['limit']  : 25;
		$offset = isset( $args['offset'] ) ? (int) $args['offset'] : 0;

		$sql = "SELECT * FROM `{$table}` WHERE {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d"; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$params[] = $limit;
		$params[] = $offset;

		return (array) $wpdb->get_results( $wpdb->prepare( $sql, $params ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	public static function count( array $args = [] ): int {
		global $wpdb;
		$table  = $wpdb->prefix . 'ch_booking_requests';
		$where  = '1=1';
		$params = [];

		if ( ! empty( $args['status'] ) ) {
			$where   .= ' AND status = %s';
			$params[] = $args['status'];
		}
		if ( ! empty( $args['search'] ) ) {
			$like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where   .= ' AND (name LIKE %s OR email LIKE %s OR phone LIKE %s OR location LIKE %s)';
			$params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
		}

		$sql = "SELECT COUNT(*) FROM `{$table}` WHERE {$where}"; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return empty( $params )
			? (int) $wpdb->get_var( $sql ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			: (int) $wpdb->get_var( $wpdb->prepare( $sql, $params ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	public static function count_by_status(): array {
		global $wpdb;
		$table = $wpdb->prefix . 'ch_booking_requests';
		$rows  = $wpdb->get_results( "SELECT status, COUNT(*) AS cnt FROM `{$table}` GROUP BY status" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$out   = [];
		foreach ( (array) $rows as $row ) {
			$out[ $row->status ] = (int) $row->cnt;
		}
		return $out;
	}

	public static function get_by_id( int $id ): ?object {
		global $wpdb;
		$table = $wpdb->prefix . 'ch_booking_requests';
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE id = %d", $id ) ) ?: null; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	public static function update_status( int $id, string $new_status, int $admin_id, string $admin_name ): void {
		global $wpdb;
		$table  = $wpdb->prefix . 'ch_booking_requests';
		$row    = self::get_by_id( $id );
		$old    = $row ? $row->status : '';

		$wpdb->update( $table, [ 'status' => $new_status, 'updated_at' => current_time( 'mysql' ) ], [ 'id' => $id ], [ '%s', '%s' ], [ '%d' ] );

		$wpdb->insert( $wpdb->prefix . 'ch_booking_logs', [
			'booking_id'      => $id,
			'action'          => 'status_changed',
			'old_value'       => $old,
			'new_value'       => $new_status,
			'admin_user_id'   => $admin_id,
			'admin_user_name' => $admin_name,
			'created_at'      => current_time( 'mysql' ),
		], [ '%d','%s','%s','%s','%d','%s','%s' ] );
	}

	public static function add_admin_note( int $id, string $note, int $admin_id, string $admin_name ): void {
		global $wpdb;
		$table   = $wpdb->prefix . 'ch_booking_requests';
		$current = $wpdb->get_var( $wpdb->prepare( "SELECT admin_notes FROM `{$table}` WHERE id = %d", $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$updated = trim( $current . "\n\n[" . current_time( 'Y-m-d H:i' ) . " — {$admin_name}]\n{$note}" );
		$wpdb->update( $table, [ 'admin_notes' => $updated, 'updated_at' => current_time( 'mysql' ) ], [ 'id' => $id ], [ '%s', '%s' ], [ '%d' ] );

		$wpdb->insert( $wpdb->prefix . 'ch_booking_logs', [
			'booking_id'      => $id,
			'action'          => 'note_added',
			'old_value'       => '',
			'new_value'       => substr( $note, 0, 200 ),
			'admin_user_id'   => $admin_id,
			'admin_user_name' => $admin_name,
			'created_at'      => current_time( 'mysql' ),
		], [ '%d','%s','%s','%s','%d','%s','%s' ] );
	}

	public static function get_logs( int $id ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'ch_booking_logs';
		return (array) $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE booking_id = %d ORDER BY created_at ASC", $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
}
