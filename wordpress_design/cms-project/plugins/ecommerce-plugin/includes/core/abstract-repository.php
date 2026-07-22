<?php
namespace AHEcommerce\Core;

/**
 * Abstract Base Repository for interacting with the database.
 * Provides standard CRUD and shared paginated queries.
 */
abstract class Abstract_Repository {

	/**
	 * Get the table name (with prefix).
	 */
	abstract protected function get_table_name();

	/**
	 * Fetch a record by ID.
	 */
	public function get( $id ) {
		global $wpdb;
		$table = $this->get_table_name();
		$query = $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id );
		return $wpdb->get_row( $query );
	}

	/**
	 * Insert a new record.
	 */
	public function insert( $data ) {
		global $wpdb;
		$table = $this->get_table_name();
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
		return true;
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

	/**
	 * Get paginated results with optional search.
	 *
	 * @param int    $page           Current page (1-indexed).
	 * @param int    $per_page       Items per page.
	 * @param string $search         Search term.
	 * @param array  $search_columns Column names to search against.
	 * @return array{items: array, meta: array}
	 */
	public function get_paginated( $page = 1, $per_page = 20, $search = '', $search_columns = array() ) {
		global $wpdb;
		$table = $this->get_table_name();

		$where = '1=1';
		$args  = array();

		if ( ! empty( $search ) && ! empty( $search_columns ) ) {
			$conditions = array();
			$like       = '%' . $wpdb->esc_like( $search ) . '%';
			foreach ( $search_columns as $col ) {
				$conditions[] = "{$col} LIKE %s";
				$args[]       = $like;
			}
			$where .= ' AND (' . implode( ' OR ', $conditions ) . ')';
		}

		$offset = ( $page - 1 ) * $per_page;

		$args[] = $per_page;
		$args[] = $offset;

		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM {$table} WHERE {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d";
		$query = $wpdb->prepare( $query, ...$args );

		$items = $wpdb->get_results( $query );
		$total = (int) $wpdb->get_var( "SELECT FOUND_ROWS()" );

		return array(
			'items' => $items,
			'meta'  => array(
				'current_page' => $page,
				'per_page'     => $per_page,
				'total_items'  => $total,
				'total_pages'  => ceil( $total / $per_page ),
			),
		);
	}
}
