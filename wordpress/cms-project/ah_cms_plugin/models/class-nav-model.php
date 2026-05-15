<?php
defined( 'ABSPATH' ) || exit;

class AH_Nav_Model extends AH_Model_Base {

	protected string $table_suffix = 'nav_menus';

	public function get_all_menus(): array {
		return $this->all( array( 'order_by' => 'name', 'order' => 'ASC' ) );
	}

	public function get_items( int $menu_id ): array {
		global $wpdb;
		$t = AH_DB_Helper::table( 'nav_menu_items' );
		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM `{$t}` WHERE menu_id = %d ORDER BY sort_order ASC", $menu_id )
		) ?: array();
	}

	public function get_items_tree( int $menu_id ): array {
		$flat   = $this->get_items( $menu_id );
		$tree   = array();
		$lookup = array();
		foreach ( $flat as $item ) {
			$item->children    = array();
			$lookup[ $item->id ] = $item;
		}
		foreach ( $lookup as $item ) {
			if ( $item->parent_id && isset( $lookup[ $item->parent_id ] ) ) {
				$lookup[ $item->parent_id ]->children[] = $item;
			} else {
				$tree[] = $item;
			}
		}
		return $tree;
	}

	public function add_item( array $data ): int|false {
		$t = AH_DB_Helper::table( 'nav_menu_items' );
		return AH_DB_Helper::insert( $t, $data );
	}

	public function update_item( int $id, array $data ): bool {
		$t = AH_DB_Helper::table( 'nav_menu_items' );
		return AH_DB_Helper::update( $t, $data, $id );
	}

	public function delete_item( int $id ): bool {
		$t = AH_DB_Helper::table( 'nav_menu_items' );
		return AH_DB_Helper::delete( $t, $id );
	}

	public function get_item( int $id ): ?object {
		$t = AH_DB_Helper::table( 'nav_menu_items' );
		return AH_DB_Helper::get_row( $t, $id );
	}
}
