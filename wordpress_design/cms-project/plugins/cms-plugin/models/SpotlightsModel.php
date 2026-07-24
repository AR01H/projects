<?php
defined( 'ABSPATH' ) || exit;

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
