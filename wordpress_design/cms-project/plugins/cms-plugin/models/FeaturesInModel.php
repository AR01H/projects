<?php
defined( 'ABSPATH' ) || exit;

/**
 * Features In - manages the ah_features_in table (e.g. logos of publishers).
 */
class AH_Features_In_Model extends AH_Model_Base {

	protected string $table_suffix = 'features_in';

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
