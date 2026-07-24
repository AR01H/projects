<?php

namespace Ah\Cms\Repository;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract base repository. All entity repositories extend this.
 *
 * Provides generic CRUD operations backed by WordPress $wpdb.
 * Concrete repositories only need to define table() and primaryKey().
 */
abstract class AbstractRepository {

	/** @var \wpdb */
	protected $db;

	public function __construct() {
		global $wpdb;
		$this->db = $wpdb;
	}

	/**
	 * Table name (without prefix). Concrete repos define this.
	 */
	abstract protected function table(): string;

	/**
	 * Primary key column name. Concrete repos define this.
	 */
	abstract protected function primaryKey(): string;

	/**
	 * Full table name with WordPress prefix.
	 */
	protected function fullTable(): string {
		return $this->db->prefix . 'ah_cms_plug_' . $this->table();
	}

	// ── Read ───────────────────────────────────────────────────────────────

	/**
	 * Find a single row by primary key.
	 */
	public function find( int $id ): ?array {
		$table = $this->fullTable();
		$pk    = $this->primaryKey();
		$row   = $this->db->get_row(
			$this->db->prepare( "SELECT * FROM `{$table}` WHERE `{$pk}` = %d", $id ),
			ARRAY_A
		);
		return $row ?: null;
	}

	/**
	 * Find all rows, with optional conditions, ordering, and limits.
	 *
	 * @param array $args {
	 *     @type string   $where      SQL WHERE clause (without WHERE keyword).
	 *     @type string   $order_by   Column to order by.
	 *     @type string   $order      ASC or DESC.
	 *     @type int      $limit      Max rows.
	 *     @type int      $offset     Row offset.
	 *     @type array    $where_in   [column => array of values] for IN clauses.
	 * }
	 */
	public function findAll( array $args = [] ): array {
		$table = $this->fullTable();
		$sql   = "SELECT * FROM `{$table}`";

		$conditions = [];
		$values     = [];

		if ( ! empty( $args['where'] ) ) {
			$conditions[] = $args['where'];
		}

		if ( ! empty( $args['where_in'] ) ) {
			foreach ( $args['where_in'] as $column => $values_list ) {
				$placeholders = implode( ',', array_fill( 0, count( $values_list ), '%s' ) );
				$conditions[] = "`{$column}` IN ({$placeholders})";
				$values       = array_merge( $values, array_map( 'strval', $values_list ) );
			}
		}

		if ( $conditions ) {
			$sql .= ' WHERE ' . implode( ' AND ', $conditions );
		}

		if ( ! empty( $args['order_by'] ) ) {
			$order = strtoupper( $args['order'] ?? 'ASC' );
			$sql  .= " ORDER BY `{$args['order_by']}` {$order}";
		}

		if ( ! empty( $args['limit'] ) ) {
			$offset = $args['offset'] ?? 0;
			$sql   .= $this->db->prepare( ' LIMIT %d OFFSET %d', $args['limit'], $offset );
		}

		if ( $values ) {
			$sql = $this->db->prepare( $sql, ...$values );
		}

		return $this->db->get_results( $sql, ARRAY_A ) ?: [];
	}

	/**
	 * Find one row by a specific column value.
	 */
	public function findOneBy( array $conditions ): ?array {
		$results = $this->findBy( $conditions, [], 1 );
		return $results[0] ?? null;
	}

	/**
	 * Find rows by conditions.
	 */
	public function findBy( array $conditions, array $orderBy = [], int $limit = 0, int $offset = 0 ): array {
		$whereParts = [];
		$values     = [];

		foreach ( $conditions as $column => $value ) {
			if ( is_array( $value ) ) {
				$placeholders = implode( ',', array_fill( 0, count( $value ), '%s' ) );
				$whereParts[] = "`{$column}` IN ({$placeholders})";
				$values       = array_merge( $values, array_map( 'strval', $value ) );
			} else {
				$whereParts[] = "`{$column}` = %s";
				$values[]     = strval( $value );
			}
		}

		$args = [
			'where'    => $whereParts ? implode( ' AND ', $whereParts ) : '',
			'order_by' => $orderBy[0] ?? '',
			'order'    => $orderBy[1] ?? 'ASC',
			'limit'    => $limit,
			'offset'   => $offset,
		];

		if ( $values ) {
			$table = $this->fullTable();
			$sql   = "SELECT * FROM `{$table}`";

			if ( $args['where'] ) {
				$sql .= " WHERE {$args['where']}";
			}

			if ( $args['order_by'] ) {
				$sql .= " ORDER BY `{$args['order_by']}` {$args['order']}";
			}

			if ( $args['limit'] ) {
				$sql .= $this->db->prepare( ' LIMIT %d OFFSET %d', $args['limit'], $args['offset'] );
			}

			$sql = $this->db->prepare( $sql, ...$values );
			return $this->db->get_results( $sql, ARRAY_A ) ?: [];
		}

		return $this->findAll( $args );
	}

	/**
	 * Count rows matching conditions.
	 */
	public function count( array $conditions = [] ): int {
		$table = $this->fullTable();
		$sql   = "SELECT COUNT(*) FROM `{$table}`";

		$whereParts = [];
		$values     = [];

		foreach ( $conditions as $column => $value ) {
			$whereParts[] = "`{$column}` = %s";
			$values[]     = strval( $value );
		}

		if ( $whereParts ) {
			$sql .= ' WHERE ' . implode( ' AND ', $whereParts );
		}

		if ( $values ) {
			$sql = $this->db->prepare( $sql, ...$values );
		}

		return (int) $this->db->get_var( $sql );
	}

	// ── Write ──────────────────────────────────────────────────────────────

	/**
	 * Insert a row. Returns the insert ID.
	 */
	public function insert( array $data ): int {
		$table = $this->fullTable();
		$this->db->insert( $table, $data );
		return (int) $this->db->insert_id;
	}

	/**
	 * Update a row by primary key. Returns true on success.
	 */
	public function update( int $id, array $data ): bool {
		$table = $this->fullTable();
		$pk    = $this->primaryKey();
		$result = $this->db->update( $table, $data, [ $pk => $id ] );
		return $result !== false;
	}

	/**
	 * Delete a row by primary key. Returns true on success.
	 */
	public function delete( int $id ): bool {
		$table = $this->fullTable();
		$pk    = $this->primaryKey();
		$result = $this->db->delete( $table, [ $pk => $id ] );
		return $result !== false;
	}

	/**
	 * Truncate the table (dangerous — use only in migrations/seeding).
	 */
	public function truncate(): void {
		$table = $this->fullTable();
		$this->db->query( "TRUNCATE TABLE `{$table}`" );
	}

	// ── Pagination ─────────────────────────────────────────────────────────

	/**
	 * Paginated results.
	 *
	 * @return array{items: array, total: int, pages: int, current_page: int}
	 */
	public function paginate( int $page = 1, int $perPage = 20, array $args = [] ): array {
		$args['limit']  = $perPage;
		$args['offset'] = ( max( 1, $page ) - 1 ) * $perPage;

		$items = $this->findAll( $args );
		$total = $this->count( $args['where'] ?? [] );
		$pages = (int) ceil( $total / $perPage );

		return [
			'items'        => $items,
			'total'        => $total,
			'pages'        => $pages,
			'current_page' => $page,
		];
	}

	// ── Search ─────────────────────────────────────────────────────────────

	/**
	 * Search rows by a LIKE query across specified columns.
	 */
	public function search( string $query, array $columns ): array {
		if ( empty( $query ) || empty( $columns ) ) {
			return [];
		}

		$table  = $this->fullTable();
		$like   = '%' . $this->db->esc_like( $query ) . '%';
		$wheres = [];

		foreach ( $columns as $col ) {
			$wheres[] = "`{$col}` LIKE %s";
		}

		$sql = "SELECT * FROM `{$table}` WHERE " . implode( ' OR ', $wheres );
		$sql = $this->db->prepare( $sql, ...array_fill( 0, count( $columns ), $like ) );

		return $this->db->get_results( $sql, ARRAY_A ) ?: [];
	}
}
