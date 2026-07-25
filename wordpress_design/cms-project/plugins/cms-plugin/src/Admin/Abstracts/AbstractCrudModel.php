<?php
namespace Ah\Cms\Admin\Abstracts;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract CRUD model with standard naming conventions.
 *
 * Naming pattern:
 *   - Model class: AH_{Name}_Model (e.g. AH_Banners_Model)
 *   - Table: {prefix}ah_{table_name} (e.g. ah_home_banners)
 *   - Methods: find(), get_all(), create(), update(), delete(), paginate()
 *
 * Usage: Extend and set $table_suffix.
 */
abstract class AbstractCrudModel {

	/** Table suffix (without prefix). e.g. 'banners' → {prefix}ah_banners */
	abstract protected function table_suffix(): string;

	/** Primary key column name. */
	protected function primary_key(): string { return 'id'; }

	/** Default sort column. */
	protected function sort_column(): string { return 'id'; }

	/** Default sort direction. */
	protected function sort_direction(): string { return 'DESC'; }

	/** Items per page for pagination. */
	protected function per_page(): int { return 20; }

	// ── Table ───────────────────────────────────────────────────

	protected function table(): string {
		return AH_DB_Helper::table( $this->table_suffix() );
	}

	// ── Read ────────────────────────────────────────────────────

	public function find( int $id ): ?object {
		global $wpdb;
		$table = $this->table();
		$key   = $this->primary_key();
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM `{$table}` WHERE `{$key}` = %d LIMIT 1", $id
		) );
	}

	public function find_by( string $column, $value ): ?object {
		global $wpdb;
		$table = $this->table();
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM `{$table}` WHERE `{$column}` = %s LIMIT 1", $value
		) );
	}

	public function get_all( array $args = array() ): array {
		global $wpdb;
		$table  = $this->table();
		$limit  = $args['limit'] ?? 999;
		$offset = $args['offset'] ?? 0;
		$where  = $args['where'] ?? '';
		$order  = $args['order_by'] ?? $this->sort_column();
		$dir    = $args['order'] ?? $this->sort_direction();

		$sql = "SELECT * FROM `{$table}`";
		if ( $where ) $sql .= " WHERE {$where}";
		$sql .= " ORDER BY `{$order}` {$dir} LIMIT {$limit} OFFSET {$offset}";

		return $wpdb->get_results( $sql ) ?: array(); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	public function count( string $where = '' ): int {
		global $wpdb;
		$table = $this->table();
		$sql   = "SELECT COUNT(*) FROM `{$table}`";
		if ( $where ) $sql .= " WHERE {$where}";
		return (int) $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	// ── Write ───────────────────────────────────────────────────

	public function create( array $data ): int {
		global $wpdb;
		$result = $wpdb->insert( $this->table(), $data );
		if ( $result ) {
			$id = (int) $wpdb->insert_id;
			AH_DB_Helper::log_action( 'create', $this->table_suffix(), $id, null, $data );
			return $id;
		}
		return 0;
	}

	public function update( int $id, array $data ): bool {
		global $wpdb;
		$key    = $this->primary_key();
		$old    = (array) $this->find( $id );
		$result = $wpdb->update( $this->table(), $data, array( $key => $id ) );
		if ( $result !== false ) {
			AH_DB_Helper::log_action( 'update', $this->table_suffix(), $id, $old, $data );
			return true;
		}
		return false;
	}

	public function delete( int $id ): bool {
		global $wpdb;
		$key    = $this->primary_key();
		$old    = (array) $this->find( $id );
		$result = $wpdb->delete( $this->table(), array( $key => $id ) );
		if ( $result ) {
			AH_DB_Helper::log_action( 'delete', $this->table_suffix(), $id, $old );
			return true;
		}
		return false;
	}

	// ── Pagination ──────────────────────────────────────────────

	public function paginate( int $page = 1, array $args = array() ): array {
		$page     = max( 1, $page );
		$per_page = $args['per_page'] ?? $this->per_page();
		$where    = $args['where'] ?? '';

		$total   = $this->count( $where );
		$meta    = AH_DB_Helper::paginate_meta( $total, $per_page, $page );
		$offset  = $meta['offset'];

		global $wpdb;
		$table = $this->table();
		$order = $args['order_by'] ?? $this->sort_column();
		$dir   = $args['order'] ?? $this->sort_direction();

		$sql = "SELECT * FROM `{$table}`";
		if ( $where ) $sql .= " WHERE {$where}";
		$sql .= " ORDER BY `{$order}` {$dir} LIMIT {$per_page} OFFSET {$offset}";

		return array(
			'items' => $wpdb->get_results( $sql ) ?: array(), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			'meta'  => $meta,
		);
	}

	// ── Search ──────────────────────────────────────────────────

	protected function search_where( array $columns, string $term ): array {
		return AH_DB_Helper::search_where( $columns, $term );
	}
}
