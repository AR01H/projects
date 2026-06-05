<?php
defined( 'ABSPATH' ) || exit;

/**
 * CH_Order_Data
 * Data layer for Order-to-Deliver requests.
 * Handles all DB reads/writes for orders and their activity logs.
 * Never outputs HTML — only returns data or writes to DB.
 */
class CH_Order_Data {

	// ── Table helpers ─────────────────────────────────────────────────────────

	public static function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'ch_order_requests';
	}

	public static function log_table(): string {
		global $wpdb;
		return $wpdb->prefix . 'ch_order_activity_logs';
	}

	// ── Status definitions ────────────────────────────────────────────────────

	public static function statuses(): array {
		return [
			'new'         => [ 'label' => 'New',         'color' => '#0073aa' ],
			'contacted'   => [ 'label' => 'Contacted',   'color' => '#856404' ],
			'in_progress' => [ 'label' => 'In Progress', 'color' => '#7a3d00' ],
			'completed'   => [ 'label' => 'Completed',   'color' => '#155724' ],
			'cancelled'   => [ 'label' => 'Cancelled',   'color' => '#721c24' ],
		];
	}

	// ── Reads ─────────────────────────────────────────────────────────────────

	/**
	 * Get all orders with optional filters and pagination.
	 *
	 * @param array $args {
	 *   @type string $status  Filter by status slug.
	 *   @type string $search  Search name/email/phone.
	 *   @type int    $limit   Rows per page (default 30).
	 *   @type int    $offset  Offset for pagination.
	 * }
	 */
	public static function get_all( array $args = [] ): array {
		global $wpdb;
		$table  = self::table();
		$status = sanitize_key( $args['status'] ?? '' );
		$search = sanitize_text_field( $args['search'] ?? '' );
		$limit  = max( 1, (int) ( $args['limit']  ?? 30 ) );
		$offset = max( 0, (int) ( $args['offset'] ?? 0  ) );

		$where  = [];
		$params = [];

		if ( $status && array_key_exists( $status, self::statuses() ) ) {
			$where[]  = 'status = %s';
			$params[] = $status;
		}

		if ( $search ) {
			$like     = '%' . $wpdb->esc_like( $search ) . '%';
			$where[]  = '(name LIKE %s OR email LIKE %s OR phone LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		$sql = "SELECT * FROM `{$table}`";
		if ( $where ) {
			$sql .= ' WHERE ' . implode( ' AND ', $where );
		}
		$sql .= ' ORDER BY created_at DESC';
		$sql .= $wpdb->prepare( ' LIMIT %d OFFSET %d', $limit, $offset );

		if ( $params ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			return (array) $wpdb->get_results( $wpdb->prepare( $sql, ...$params ) );
		}
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (array) $wpdb->get_results( $sql );
	}

	/**
	 * Count orders matching the same filters (for pagination).
	 */
	public static function count( array $args = [] ): int {
		global $wpdb;
		$table  = self::table();
		$status = sanitize_key( $args['status'] ?? '' );
		$search = sanitize_text_field( $args['search'] ?? '' );

		$where  = [];
		$params = [];

		if ( $status && array_key_exists( $status, self::statuses() ) ) {
			$where[]  = 'status = %s';
			$params[] = $status;
		}
		if ( $search ) {
			$like     = '%' . $wpdb->esc_like( $search ) . '%';
			$where[]  = '(name LIKE %s OR email LIKE %s OR phone LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		$sql = "SELECT COUNT(*) FROM `{$table}`";
		if ( $where ) {
			$sql .= ' WHERE ' . implode( ' AND ', $where );
		}

		if ( $params ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			return (int) $wpdb->get_var( $wpdb->prepare( $sql, ...$params ) );
		}
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Count orders grouped by status — used for dashboard stats.
	 */
	public static function count_by_status(): array {
		global $wpdb;
		$table = self::table();
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows  = $wpdb->get_results( "SELECT status, COUNT(*) AS cnt FROM `{$table}` GROUP BY status" );
		$out   = array_fill_keys( array_keys( self::statuses() ), 0 );
		foreach ( (array) $rows as $row ) {
			if ( isset( $out[ $row->status ] ) ) {
				$out[ $row->status ] = (int) $row->cnt;
			}
		}
		return $out;
	}

	/**
	 * Fetch a single order by ID. Returns object or null.
	 */
	public static function get_by_id( int $id ): ?object {
		global $wpdb;
		$table = self::table();
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE id = %d LIMIT 1", $id ) ) ?: null;
	}

	// ── Writes ────────────────────────────────────────────────────────────────

	/**
	 * Insert a new order. Returns the new row ID on success, false on failure.
	 *
	 * @param array $data {
	 *   name, email, phone, delivery_address, delivery_area,
	 *   preferred_date, preferred_time, items (JSON string),
	 *   special_notes, ip_address
	 * }
	 */
	public static function insert( array $data ): int|false {
		global $wpdb;
		$result = $wpdb->insert(
			self::table(),
			[
				'name'             => $data['name']             ?? '',
				'email'            => $data['email']            ?? '',
				'phone'            => $data['phone']            ?? '',
				'delivery_address' => $data['delivery_address'] ?? '',
				'delivery_area'    => $data['delivery_area']    ?? '',
				'preferred_date'   => $data['preferred_date']   ?: null,
				'preferred_time'   => $data['preferred_time']   ?? '',
				'items'            => $data['items']            ?? '[]',
				'special_notes'    => $data['special_notes']    ?? '',
				'status'           => 'new',
				'ip_address'       => $data['ip_address']       ?? '',
				'created_at'       => current_time( 'mysql' ),
			],
			[ '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
		);
		return $result !== false ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Change the status of an order and record an activity log entry.
	 */
	public static function update_status( int $id, string $new_status, int $admin_id = 0, string $admin_name = '' ): bool {
		global $wpdb;
		$order = self::get_by_id( $id );
		if ( ! $order ) return false;

		$old_status = $order->status;
		if ( $old_status === $new_status ) return true;

		$updated = $wpdb->update(
			self::table(),
			[ 'status' => $new_status ],
			[ 'id'     => $id ],
			[ '%s' ],
			[ '%d' ]
		);

		if ( $updated !== false ) {
			self::log_activity( [
				'order_id'        => $id,
				'action'          => 'status_changed',
				'field_name'      => 'status',
				'old_value'       => $old_status,
				'new_value'       => $new_status,
				'admin_user_id'   => $admin_id,
				'admin_user_name' => $admin_name,
			] );
		}

		return $updated !== false;
	}

	/**
	 * Append an admin note to an order and log the action.
	 */
	public static function add_admin_note( int $id, string $note, int $admin_id = 0, string $admin_name = '' ): bool {
		global $wpdb;
		$order = self::get_by_id( $id );
		if ( ! $order ) return false;

		$old_notes = $order->admin_notes ?? '';
		$timestamp = current_time( 'Y-m-d H:i' );
		$new_notes = trim( $old_notes . "\n\n[{$timestamp}] {$admin_name}:\n{$note}" );

		$updated = $wpdb->update(
			self::table(),
			[ 'admin_notes' => $new_notes ],
			[ 'id'          => $id ],
			[ '%s' ],
			[ '%d' ]
		);

		if ( $updated !== false ) {
			self::log_activity( [
				'order_id'        => $id,
				'action'          => 'note_added',
				'field_name'      => 'admin_notes',
				'old_value'       => '',
				'new_value'       => $note,
				'admin_user_id'   => $admin_id,
				'admin_user_name' => $admin_name,
			] );
		}

		return $updated !== false;
	}

	// ── Activity log ──────────────────────────────────────────────────────────

	/**
	 * Insert an activity log entry.
	 */
	public static function log_activity( array $data ): void {
		global $wpdb;
		$wpdb->insert(
			self::log_table(),
			[
				'order_id'        => (int) ( $data['order_id']        ?? 0 ),
				'action'          => $data['action']          ?? '',
				'field_name'      => $data['field_name']      ?? '',
				'old_value'       => $data['old_value']       ?? '',
				'new_value'       => $data['new_value']       ?? '',
				'admin_user_id'   => (int) ( $data['admin_user_id']   ?? 0 ),
				'admin_user_name' => $data['admin_user_name'] ?? '',
				'created_at'      => current_time( 'mysql' ),
			],
			[ '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s' ]
		);
	}

	/**
	 * Get activity log entries for a given order, newest first.
	 */
	public static function get_activity_logs( int $order_id ): array {
		global $wpdb;
		$table = self::log_table();
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (array) $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM `{$table}` WHERE order_id = %d ORDER BY created_at DESC", $order_id )
		);
	}

	// ── Static content from JSON (no DB needed) ──────────────────────────────

	/**
	 * Delivery product list — reads from delivery-products.json.
	 * Admin can override via ch_delivery_products wp_option (JSON string or array).
	 */
	public static function products(): array {
		$opt = get_option( 'ch_delivery_products', [] );
		if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
		if ( ! empty( $opt ) ) return (array) $opt;
		return CH_Real_Loader::json( 'delivery-products' );
	}

	/**
	 * Delivery feature cards — reads features[] from order-to-deliver.json.
	 * Admin can override via ch_delivery_features wp_option.
	 */
	public static function features(): array {
		$opt = get_option( 'ch_delivery_features', [] );
		if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
		if ( ! empty( $opt ) ) return (array) $opt;
		$data = CH_Real_Loader::json( 'order-to-deliver' );
		return isset( $data['features'] ) && is_array( $data['features'] ) ? $data['features'] : [];
	}

	/**
	 * Banner settings (tag, heading, sub, image) from order-to-deliver.json.
	 * Any key can be overridden via ch_settings (otd_tag / otd_heading / otd_sub / otd_image).
	 */
	public static function banner_settings(): array {
		$s    = ch_get_settings();
		$json = CH_Real_Loader::json( 'order-to-deliver' );
		$hd   = CH_Shared_Data::section_heading( 'order_deliver_section' );
		return [
			'tag'     => $s['otd_tag']     ?? $json['tag']     ?? $hd['tag']   ?? '',
			'heading' => $s['otd_heading'] ?? $json['heading'] ?? $hd['title'] ?? '',
			'sub'     => $s['otd_sub']     ?? $json['sub']     ?? $hd['body']  ?? '',
			'image'   => $s['otd_image']   ?? ( ! empty( $json['image'] ) ? $json['image'] : 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?auto=format&fit=crop&w=900&q=80' ),
		];
	}

	// ── Ensure tables exist (called from AJAX handler as a safety net) ────────

	public static function ensure_tables(): void {
		static $checked = false;
		if ( $checked ) return;
		$checked = true;

		global $wpdb;
		$t = self::table();
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $t ) ) !== $t ) {
			$cs = $wpdb->get_charset_collate();
			CH_Schema::create_all(); // re-runs all CREATE TABLE IF NOT EXISTS safely
		}
	}
}
