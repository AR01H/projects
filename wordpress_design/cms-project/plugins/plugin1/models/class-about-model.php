<?php
defined( 'ABSPATH' ) || exit;

class AH_About_Model extends AH_Model_Base {

	protected string $table_suffix = 'about_page_header';

	public function get_page_header( int $page_id ): ?object {
		return AH_DB_Helper::get_by( AH_DB_Helper::table( 'about_page_header' ), 'page_id', $page_id );
	}

	public function save_page_header( int $page_id, array $data ): void {
		$t   = AH_DB_Helper::table( 'about_page_header' );
		$data = array_merge( $data, array( 'page_id' => $page_id, 'updated_by' => get_current_user_id() ?: null ) );
		$row = $this->get_page_header( $page_id );
		$row ? AH_DB_Helper::update( $t, $data, (int) $row->id ) : AH_DB_Helper::insert( $t, $data );
	}

	public function get_story( int $page_id ): ?object {
		return AH_DB_Helper::get_by( AH_DB_Helper::table( 'about_story' ), 'page_id', $page_id );
	}

	public function save_story( int $page_id, array $data ): int {
		$t   = AH_DB_Helper::table( 'about_story' );
		$data = array_merge( $data, array( 'page_id' => $page_id, 'updated_by' => get_current_user_id() ?: null ) );
		$row = $this->get_story( $page_id );
		if ( $row ) {
			AH_DB_Helper::update( $t, $data, (int) $row->id );
			return (int) $row->id;
		}
		return (int) AH_DB_Helper::insert( $t, $data );
	}

	public function get_story_points( int $story_id ): array {
		return AH_DB_Helper::get_list( AH_DB_Helper::table( 'about_story_points' ), array(
			'where' => 'story_id = %d', 'where_in' => array( $story_id ),
			'order_by' => 'sort_order', 'order' => 'ASC', 'limit' => 999,
		) );
	}

	public function save_story_points( int $story_id, array $points ): void {
		$t = AH_DB_Helper::table( 'about_story_points' );
		AH_DB_Helper::delete_where( $t, array( 'story_id' => $story_id ) );
		foreach ( $points as $i => $text ) {
			if ( trim( $text ) ) {
				AH_DB_Helper::insert( $t, array( 'story_id' => $story_id, 'point_text' => sanitize_text_field( $text ), 'sort_order' => $i ) );
			}
		}
	}

	public function get_values( int $page_id ): array {
		return AH_DB_Helper::get_list( AH_DB_Helper::table( 'about_values' ), array(
			'where' => 'page_id = %d', 'where_in' => array( $page_id ),
			'order_by' => 'sort_order', 'order' => 'ASC', 'limit' => 999,
		) );
	}

	public function save_value( array $data, int $id = 0 ): int {
		$t = AH_DB_Helper::table( 'about_values' );
		if ( $id ) { AH_DB_Helper::update( $t, $data, $id ); return $id; }
		return AH_DB_Helper::insert( $t, $data );
	}

	public function delete_value( int $id ): bool {
		return AH_DB_Helper::delete( AH_DB_Helper::table( 'about_values' ), $id );
	}
}
