<?php
defined( 'ABSPATH' ) || exit;

class AH_Footer_Model extends AH_Model_Base {

	protected string $table_suffix = 'footer_config';

	public function get_config(): ?object {
		global $wpdb;
		$t = $this->table();
		return $wpdb->get_row( "SELECT * FROM `{$t}` LIMIT 1" );
	}

	public function save_config( array $data ): void {
		$t   = $this->table();
		$row = $this->get_config();
		$data['updated_by'] = get_current_user_id() ?: null;
		$row ? AH_DB_Helper::update( $t, $data, (int) $row->id ) : AH_DB_Helper::insert( $t, $data );
	}

	public function get_contact_links(): array {
		return AH_DB_Helper::get_list( AH_DB_Helper::table( 'footer_contact_links' ), array(
			'where' => "status = 'active'", 'order_by' => 'sort_order', 'order' => 'ASC', 'limit' => 999,
		) );
	}

	public function save_contact_link( array $data, int $id = 0 ): int|false {
		$t = AH_DB_Helper::table( 'footer_contact_links' );
		if ( $id ) { AH_DB_Helper::update( $t, $data, $id ); return $id; }
		return AH_DB_Helper::insert( $t, $data );
	}

	public function delete_contact_link( int $id ): bool {
		return AH_DB_Helper::delete( AH_DB_Helper::table( 'footer_contact_links' ), $id );
	}

	public function get_social_links(): array {
		return AH_DB_Helper::get_list( AH_DB_Helper::table( 'footer_social_links' ), array(
			'where' => "status = 'active'", 'order_by' => 'sort_order', 'order' => 'ASC', 'limit' => 999,
		) );
	}

	public function save_social_link( array $data, int $id = 0 ): int|false {
		$t = AH_DB_Helper::table( 'footer_social_links' );
		if ( $id ) { AH_DB_Helper::update( $t, $data, $id ); return $id; }
		return AH_DB_Helper::insert( $t, $data );
	}

	public function delete_social_link( int $id ): bool {
		return AH_DB_Helper::delete( AH_DB_Helper::table( 'footer_social_links' ), $id );
	}
}
