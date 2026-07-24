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
