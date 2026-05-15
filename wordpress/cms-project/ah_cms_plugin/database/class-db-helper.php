<?php
defined( 'ABSPATH' ) || exit;

/**
 * Static utility class wrapping $wpdb for common query patterns.
 * Always call AH_DB_Helper::table('name') to get the prefixed table name.
 */
class AH_DB_Helper {

	// ----------------------------------------------------------------
	// Table name helper
	// ----------------------------------------------------------------

	public static function table( string $name ): string {
		return AH_DB_Installer::table( $name );
	}

	// ----------------------------------------------------------------
	// Read helpers
	// ----------------------------------------------------------------

	public static function get_row( string $table, int $id ): ?object {
		global $wpdb;
		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM `{$table}` WHERE id = %d LIMIT 1", $id )
		);
	}

	public static function get_by( string $table, string $col, $value ): ?object {
		global $wpdb;
		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM `{$table}` WHERE `{$col}` = %s LIMIT 1", $value )
		);
	}

	/**
	 * Generic list query with optional WHERE, ORDER BY, LIMIT, OFFSET.
	 *
	 * @param string $table   Prefixed table name.
	 * @param array  $args {
	 *   @type string $where     Raw WHERE clause (without WHERE keyword). Use %s/%d placeholders.
	 *   @type array  $where_in  Values for placeholders in $where.
	 *   @type string $order_by  Column name.
	 *   @type string $order     ASC|DESC.
	 *   @type int    $limit
	 *   @type int    $offset
	 *   @type string $columns   SELECT columns, default *.
	 * }
	 */
	public static function get_list( string $table, array $args = array() ): array {
		global $wpdb;

		$cols     = $args['columns']  ?? '*';
		$where    = $args['where']    ?? '';
		$where_in = $args['where_in'] ?? array();
		$order_by = isset( $args['order_by'] ) ? sanitize_key( $args['order_by'] ) : 'id';
		$order    = strtoupper( $args['order'] ?? 'DESC' ) === 'ASC' ? 'ASC' : 'DESC';
		$limit    = isset( $args['limit'] )  ? (int) $args['limit']  : 20;
		$offset   = isset( $args['offset'] ) ? (int) $args['offset'] : 0;

		$sql = "SELECT {$cols} FROM `{$table}`";
		if ( $where ) {
			$sql .= ' WHERE ' . $where;
		}
		$sql .= " ORDER BY `{$order_by}` {$order} LIMIT {$limit} OFFSET {$offset}";

		if ( $where_in ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			return $wpdb->get_results( $wpdb->prepare( $sql, ...$where_in ) );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( $sql ) ?: array();
	}

	public static function count( string $table, string $where = '', array $where_in = array() ): int {
		global $wpdb;
		$sql = "SELECT COUNT(*) FROM `{$table}`";
		if ( $where ) {
			$sql .= ' WHERE ' . $where;
		}
		if ( $where_in ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			return (int) $wpdb->get_var( $wpdb->prepare( $sql, ...$where_in ) );
		}
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $sql );
	}

	// ----------------------------------------------------------------
	// Write helpers
	// ----------------------------------------------------------------

	public static function insert( string $table, array $data ): int|false {
		global $wpdb;
		$result = $wpdb->insert( $table, $data );
		return $result !== false ? $wpdb->insert_id : false;
	}

	public static function update( string $table, array $data, int $id ): bool {
		global $wpdb;
		return $wpdb->update( $table, $data, array( 'id' => $id ) ) !== false;
	}

	public static function delete( string $table, int $id ): bool {
		global $wpdb;
		return $wpdb->delete( $table, array( 'id' => $id ) ) !== false;
	}

	public static function delete_where( string $table, array $where ): bool {
		global $wpdb;
		return $wpdb->delete( $table, $where ) !== false;
	}

	public static function set_status( string $table, int $id, string $status ): bool {
		return self::update( $table, array( 'status' => sanitize_text_field( $status ) ), $id );
	}

	public static function update_sort_order( string $table, int $id, int $order ): bool {
		return self::update( $table, array( 'sort_order' => $order ), $id );
	}

	// ----------------------------------------------------------------
	// Audit helper
	// ----------------------------------------------------------------

	public static function log_action( string $action, string $table_name, int $record_id, ?array $old = null, ?array $new = null ): void {
		global $wpdb;
		$wpdb->insert(
			self::table( 'audit_logs' ),
			array(
				'user_id'    => get_current_user_id() ?: null,
				'action'     => $action,
				'table_name' => $table_name,
				'record_id'  => $record_id,
				'old_values' => $old ? wp_json_encode( $old ) : null,
				'new_values' => $new ? wp_json_encode( $new ) : null,
				'ip_address' => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
				'user_agent' => sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' ),
			)
		);
	}

	// ----------------------------------------------------------------
	// Search helper
	// ----------------------------------------------------------------

	public static function search_where( array $columns, string $term ): array {
		global $wpdb;
		$clauses = array();
		$values  = array();
		$like    = '%' . $wpdb->esc_like( $term ) . '%';
		foreach ( $columns as $col ) {
			$clauses[] = "`{$col}` LIKE %s";
			$values[]  = $like;
		}
		return array(
			'where'    => '(' . implode( ' OR ', $clauses ) . ')',
			'where_in' => $values,
		);
	}

	// ----------------------------------------------------------------
	// Pagination meta helper
	// ----------------------------------------------------------------

	public static function paginate_meta( int $total, int $per_page, int $current_page ): array {
		$total_pages = max( 1, (int) ceil( $total / max( 1, $per_page ) ) );
		return array(
			'total'        => $total,
			'per_page'     => $per_page,
			'current_page' => $current_page,
			'total_pages'  => $total_pages,
			'offset'       => ( $current_page - 1 ) * $per_page,
		);
	}
}
