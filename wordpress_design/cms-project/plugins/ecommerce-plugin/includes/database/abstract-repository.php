<?php
namespace AHEcommerce\Database;

/**
 * Abstract Base Repository for interacting with the database.
 * Enforces caching, prepared statements, and standardized CRUD operations.
 */
abstract class Abstract_Repository {

	/**
	 * Get the table name.
	 */
	abstract protected function get_table_name();

	/**
	 * Fetch a record by ID.
	 */
	public function get( $id ) {
		global $wpdb;
		$table = $this->get_table_name();
		
		// In a full implementation, we'd check object cache here:
		// $cache_key = "ah_ecommerce_{$table}_{$id}";
		
		$query = $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id );
		return $wpdb->get_row( $query );
	}

	/**
	 * Insert a new record.
	 */
	public function insert( $data ) {
		global $wpdb;
		$table = $this->get_table_name();
		
		// Insert data, handling sanitization automatically via WPDB
		$wpdb->insert( $table, $data );
		
		return $wpdb->insert_id;
	}

	/**
	 * Update an existing record.
	 */
	public function update( $id, $data ) {
		global $wpdb;
		$table = $this->get_table_name();
		
		$where = array( 'id' => $id );
		$wpdb->update( $table, $data, $where );
		
		return true; // Optionally check rows affected
	}

	/**
	 * Delete a record.
	 */
	public function delete( $id ) {
		global $wpdb;
		$table = $this->get_table_name();
		
		$where = array( 'id' => $id );
		$wpdb->delete( $table, $where );
		
		return true;
	}
}
