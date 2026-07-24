<?php

namespace Ah\Cms\Database;

defined( 'ABSPATH' ) || exit;

/**
 * Database connection wrapper.
 *
 * Provides instance-based access to $wpdb for use in dependency injection.
 * Delegates to the existing AH_DB_Helper for backward compatibility.
 *
 * Usage:
 *   $conn = new Connection();
 *   $table = $conn->table( 'pages' );
 *   $row   = $conn->getById( $table, 42 );
 */
class Connection {

	/** @var \wpdb */
	private $wpdb;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	/**
	 * Get the raw $wpdb instance (escape hatch for complex queries).
	 */
	public function wpdb(): \wpdb {
		return $this->wpdb;
	}

	/**
	 * Get prefixed table name.
	 */
	public function table( string $name ): string {
		return \AH_DB_Installer::table( $name );
	}

	/**
	 * Get a single row by ID.
	 */
	public function getById( string $table, int $id ): ?object {
		return $this->wpdb->get_row(
			$this->wpdb->prepare( "SELECT * FROM `{$table}` WHERE id = %d LIMIT 1", $id )
		);
	}

	/**
	 * Get a single row by a specific column.
	 */
	public function getBy( string $table, string $column, $value ): ?object {
		return $this->wpdb->get_row(
			$this->wpdb->prepare( "SELECT * FROM `{$table}` WHERE `{$column}` = %s LIMIT 1", $value )
		);
	}

	/**
	 * Get multiple rows.
	 */
	public function getResults( string $sql, array $args = [] ): array {
		if ( $args ) {
			$sql = $this->wpdb->prepare( $sql, ...$args );
		}
		return $this->wpdb->get_results( $sql ) ?: [];
	}

	/**
	 * Get a single value.
	 */
	public function getVar( string $sql, array $args = [] ) {
		if ( $args ) {
			$sql = $this->wpdb->prepare( $sql, ...$args );
		}
		return $this->wpdb->get_var( $sql );
	}

	/**
	 * Insert a row. Returns insert ID.
	 */
	public function insert( string $table, array $data ): int {
		$result = $this->wpdb->insert( $table, $data );
		return $result !== false ? (int) $this->wpdb->insert_id : 0;
	}

	/**
	 * Update a row by ID.
	 */
	public function update( string $table, array $data, int $id ): bool {
		return $this->wpdb->update( $table, $data, [ 'id' => $id ] ) !== false;
	}

	/**
	 * Delete a row by ID.
	 */
	public function delete( string $table, int $id ): bool {
		return $this->wpdb->delete( $table, [ 'id' => $id ] ) !== false;
	}

	/**
	 * Delete rows matching conditions.
	 */
	public function deleteWhere( string $table, array $where ): bool {
		return $this->wpdb->delete( $table, $where ) !== false;
	}

	/**
	 * Count rows.
	 */
	public function count( string $table, string $where = '', array $whereIn = [] ): int {
		$sql = "SELECT COUNT(*) FROM `{$table}`";
		if ( $where ) {
			$sql .= ' WHERE ' . $where;
		}
		if ( $whereIn ) {
			return (int) $this->wpdb->get_var( $this->wpdb->prepare( $sql, ...$whereIn ) );
		}
		return (int) $this->wpdb->get_var( $sql );
	}

	/**
	 * Run a raw query (for complex operations).
	 */
	public function query( string $sql, array $args = [] ): bool {
		if ( $args ) {
			$sql = $this->wpdb->prepare( $sql, ...$args );
		}
		return $this->wpdb->query( $sql ) !== false;
	}

	/**
	 * Log an action to the audit trail.
	 */
	public function logAction( string $action, string $tableName, int $recordId, ?array $old = null, ?array $new = null ): void {
		$this->wpdb->insert(
			$this->table( 'audit_logs' ),
			[
				'user_id'    => get_current_user_id() ?: null,
				'action'     => $action,
				'table_name' => $tableName,
				'record_id'  => $recordId,
				'old_values' => $old ? wp_json_encode( $old ) : null,
				'new_values' => $new ? wp_json_encode( $new ) : null,
				'ip_address' => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
				'user_agent' => sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' ),
			]
		);
	}
}
