<?php
defined( 'ABSPATH' ) || exit;

/**
 * Spotlight Terms - manages the ah_spotlight_terms table.
 * Each term is a named group (e.g. advaith, health, organics).
 */
class AH_Spotlight_Terms_Model extends AH_Model_Base {

	protected string $table_suffix = 'spotlight_terms';

	public function get_all_active(): array {
		return $this->all( array(
			'where'    => 'is_active = 1',
			'order_by' => 'sort_order',
			'order'    => 'ASC',
		) );
	}

	public function get_by_slug( string $slug ): ?object {
		return $this->find_by( 'slug', $slug );
	}

	public function get_by_page_slug( string $page_slug ): ?object {
		return $this->find_by( 'page_slug', $page_slug );
	}

	public function toggle_active( int $id ): bool {
		global $wpdb;
		$row = $this->find( $id );
		if ( ! $row ) { return false; }
		return (bool) $wpdb->update(
			$this->table(),
			array( 'is_active' => $row->is_active ? 0 : 1 ),
			array( 'id' => $id ),
			array( '%d' ),
			array( '%d' )
		);
	}

	public function item_count( int $term_id ): int {
		global $wpdb;
		$t = AH_DB_Helper::table( 'spotlights' );
		return (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM `{$t}` WHERE term_id = %d",
			$term_id
		) );
	}

	public function delete_with_items( int $id ): void {
		global $wpdb;
		$wpdb->delete( AH_DB_Helper::table( 'spotlights' ), array( 'term_id' => $id ), array( '%d' ) );
		$this->delete( $id );
	}
}

/**
 * Spotlights - manages the ah_spotlights table (items).
 */
class AH_Spotlights_Model extends AH_Model_Base {

	protected string $table_suffix = 'spotlights';

	public function get_by_term( int $term_id, int $limit = 999 ): array {
		return $this->all( array(
			'where'    => 'term_id = ' . (int) $term_id . " AND is_active = 1",
			'order_by' => 'sort_order',
			'order'    => 'ASC',
			'limit'    => $limit,
		) );
	}

	public function get_for_home( string $term_slug, int $limit = 0 ): array {
		$terms_model = new AH_Spotlight_Terms_Model();
		$term = $terms_model->get_by_slug( $term_slug );
		if ( ! $term || ! $term->is_active ) { return array(); }
		$max = $limit > 0 ? $limit : (int) $term->max_display;
		return $this->get_by_term( (int) $term->id, $max );
	}

	public function get_paginated_for_admin( int $page = 1, int $term_id = 0 ): array {
		$args = array( 'order_by' => 'sort_order', 'order' => 'ASC' );
		if ( $term_id ) {
			$args['where']    = 'term_id = %d';
			$args['where_in'] = array( $term_id );
		}
		return $this->paginate( $page, $args );
	}

	public function toggle_active( int $id ): bool {
		global $wpdb;
		$row = $this->find( $id );
		if ( ! $row ) { return false; }
		return (bool) $wpdb->update(
			$this->table(),
			array( 'is_active' => $row->is_active ? 0 : 1 ),
			array( 'id' => $id ),
			array( '%d' ),
			array( '%d' )
		);
	}
}
