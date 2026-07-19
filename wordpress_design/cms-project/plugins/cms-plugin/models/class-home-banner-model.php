<?php
defined( 'ABSPATH' ) || exit;

/**
 * Home Banners - manages the ah_home_banners table.
 */
class AH_Home_Banners_Model extends AH_Model_Base {

	protected string $table_suffix = 'home_banners';

	public function get_active( int $limit = 0 ): array {
		$args = array(
			'where'    => "status = 'active'",
			'order_by' => 'sort_order',
			'order'    => 'ASC',
		);
		if ( $limit > 0 ) {
			$args['limit'] = $limit;
		}
		return $this->all( $args );
	}

	public function toggle_active( int $id ): bool {
		global $wpdb;
		$row = $this->find( $id );
		if ( ! $row ) { return false; }
		return (bool) $wpdb->update(
			$this->table(),
			array( 'status' => $row->status === 'active' ? 'inactive' : 'active' ),
			array( 'id' => $id ),
			array( '%s' ),
			array( '%d' )
		);
	}
}
