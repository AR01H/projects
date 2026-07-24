<?php
defined( 'ABSPATH' ) || exit;

/**
 * Model for the ah_custom_code table (per-page CSS/JS rules).
 */
class AH_Custom_Code_Model extends AH_Model_Base {

	protected string $table_suffix = 'custom_code';

	/**
	 * Get all rules, optionally filtered by search term.
	 */
	public function get_paginated( int $page = 1, string $search = '' ): array {
		$args = array(
			'order_by' => 'created_at',
			'order'    => 'DESC',
		);

		if ( $search ) {
			$s        = AH_DB_Helper::search_where( array( 'slug' ), $search );
			$args['where']    = $s['where'];
			$args['where_in'] = $s['where_in'];
		}

		return $this->paginate( $page, $args );
	}

	/**
	 * Get all rules (no pagination).
	 */
	public function get_all(): array {
		return $this->all( array(
			'order_by' => 'created_at',
			'order'    => 'DESC',
			'limit'    => 999,
		) );
	}

	/**
	 * Find a rule by slug.
	 */
	public function find_by_slug( string $slug ): ?object {
		return $this->find_by( 'slug', $slug );
	}

	/**
	 * Save a rule (create or update).
	 */
	public function save( int $id, array $data ): int {
		if ( $id > 0 ) {
			$this->update( $id, $data );
			return $id;
		}
		return $this->create( $data );
	}

	/**
	 * Toggle the is_active flag.
	 */
	public function toggle_active( int $id ): bool {
		$row = $this->find( $id );
		if ( ! $row ) return false;

		$new_val = empty( $row->is_active ) ? 1 : 0;
		return $this->update( $id, array( 'is_active' => $new_val ) );
	}

	/**
	 * Get active rules that match a given page slug.
	 * Used by the frontend injector.
	 */
	public function get_active_for_slug( string $slug ): ?object {
		global $wpdb;
		$table = $this->table();
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM `{$table}` WHERE slug = %s AND is_active = 1 LIMIT 1",
			$slug
		) );
	}
}
