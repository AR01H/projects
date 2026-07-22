<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Repositories;

use CmsSuggestionBot\Contracts\RepositoryInterface;
use CmsSuggestionBot\Database\DB;

defined( 'ABSPATH' ) || exit;

/**
 * Shared $wpdb CRUD implementation. Concrete repositories only need to
 * declare their table suffix (see table()) and any query methods beyond
 * plain CRUD - all SQL stays behind this one class per table.
 */
abstract class AbstractRepository implements RepositoryInterface {

	/**
	 * Table suffix passed to Database\DB::table(), e.g. 'cache', 'knowledge'.
	 */
	abstract protected function tableSuffix(): string;

	protected function table(): string {
		return DB::table( $this->tableSuffix() );
	}

	protected function db(): \wpdb {
		return DB::wpdb();
	}

	public function find( int $id ): ?array {
		$row = $this->db()->get_row(
			$this->db()->prepare( "SELECT * FROM {$this->table()} WHERE id = %d", $id ),
			ARRAY_A
		);

		return is_array( $row ) ? $row : null;
	}

	public function all( array $where = array(), int $limit = 0, int $offset = 0 ): array {
		[ $clause, $values ] = $this->buildWhere( $where );

		$sql = "SELECT * FROM {$this->table()} {$clause} ORDER BY id DESC";
		if ( $limit > 0 ) {
			$sql   .= ' LIMIT %d OFFSET %d';
			$values[] = $limit;
			$values[] = $offset;
		}

		$prepared = empty( $values ) ? $sql : $this->db()->prepare( $sql, $values );
		$rows     = $this->db()->get_results( $prepared, ARRAY_A );

		return is_array( $rows ) ? $rows : array();
	}

	public function insert( array $data ): int {
		$this->db()->insert( $this->table(), $data );

		return (int) $this->db()->insert_id;
	}

	public function update( int $id, array $data ): bool {
		return false !== $this->db()->update( $this->table(), $data, array( 'id' => $id ) );
	}

	public function delete( int $id ): bool {
		return false !== $this->db()->delete( $this->table(), array( 'id' => $id ) );
	}

	public function count( array $where = array() ): int {
		[ $clause, $values ] = $this->buildWhere( $where );

		$sql      = "SELECT COUNT(*) FROM {$this->table()} {$clause}";
		$prepared = empty( $values ) ? $sql : $this->db()->prepare( $sql, $values );

		return (int) $this->db()->get_var( $prepared );
	}

	/**
	 * Builds a simple "WHERE col = %s AND col2 = %d" clause from an
	 * associative array. Intentionally minimal (equality only) - anything
	 * more complex belongs in a query method on the concrete repository.
	 *
	 * @param array<string, mixed> $where
	 * @return array{0: string, 1: array<int, mixed>}
	 */
	protected function buildWhere( array $where ): array {
		if ( empty( $where ) ) {
			return array( '', array() );
		}

		$parts  = array();
		$values = array();
		foreach ( $where as $column => $value ) {
			$parts[]  = '`' . preg_replace( '/[^a-zA-Z0-9_]/', '', $column ) . '` = ' . ( is_int( $value ) ? '%d' : '%s' );
			$values[] = $value;
		}

		return array( 'WHERE ' . implode( ' AND ', $parts ), $values );
	}
}
