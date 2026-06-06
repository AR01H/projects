<?php
defined( 'ABSPATH' ) || exit;

class AH_Home_Model extends AH_Model_Base {

	protected string $table_suffix = 'section_hero';

	// ---- Hero ----

	public function get_hero( int $page_id ): ?object {
		return $this->find_by( 'page_id', $page_id );
	}

	public function save_hero( int $page_id, array $data ): void {
		$data['page_id']    = $page_id;
		$data['updated_by'] = get_current_user_id() ?: null;
		$existing           = $this->get_hero( $page_id );
		if ( $existing ) {
			$this->update( (int) $existing->id, $data );
		} else {
			$this->create( $data );
		}
	}

	// ---- Highlights ----

	public function get_highlights( int $page_id ): array {
		return AH_DB_Helper::get_list( AH_DB_Helper::table( 'section_highlights' ), array(
			'where'    => 'page_id = %d',
			'where_in' => array( $page_id ),
			'order_by' => 'sort_order',
			'order'    => 'ASC',
			'limit'    => 999,
		) );
	}

	public function save_highlight( array $data, int $id = 0 ): int {
		$t = AH_DB_Helper::table( 'section_highlights' );
		if ( $id ) {
			AH_DB_Helper::update( $t, $data, $id );
			return $id;
		}
		return AH_DB_Helper::insert( $t, $data );
	}

	public function delete_highlight( int $id ): bool {
		return AH_DB_Helper::delete( AH_DB_Helper::table( 'section_highlights' ), $id );
	}

	// ---- Why Us ----

	public function get_why_us( int $page_id ): ?object {
		return AH_DB_Helper::get_by( AH_DB_Helper::table( 'section_why_us' ), 'page_id', $page_id );
	}

	public function save_why_us( int $page_id, array $data ): void {
		$t    = AH_DB_Helper::table( 'section_why_us' );
		$data = array_merge( $data, array( 'page_id' => $page_id, 'updated_by' => get_current_user_id() ?: null ) );
		$row  = $this->get_why_us( $page_id );
		$row ? AH_DB_Helper::update( $t, $data, (int) $row->id ) : AH_DB_Helper::insert( $t, $data );
	}

	public function get_why_us_cards( int $why_us_id ): array {
		return AH_DB_Helper::get_list( AH_DB_Helper::table( 'section_why_us_cards' ), array(
			'where' => 'why_us_id = %d', 'where_in' => array( $why_us_id ),
			'order_by' => 'sort_order', 'order' => 'ASC', 'limit' => 999,
		) );
	}

	public function save_why_us_card( array $data, int $id = 0 ): int {
		$t = AH_DB_Helper::table( 'section_why_us_cards' );
		if ( $id ) { AH_DB_Helper::update( $t, $data, $id ); return $id; }
		return AH_DB_Helper::insert( $t, $data );
	}

	public function delete_why_us_card( int $id ): bool {
		return AH_DB_Helper::delete( AH_DB_Helper::table( 'section_why_us_cards' ), $id );
	}

	// ---- Guide Through ----

	public function get_guide( int $page_id ): ?object {
		return AH_DB_Helper::get_by( AH_DB_Helper::table( 'section_guide_through' ), 'page_id', $page_id );
	}

	public function save_guide( int $page_id, array $data ): void {
		$t   = AH_DB_Helper::table( 'section_guide_through' );
		$data = array_merge( $data, array( 'page_id' => $page_id, 'updated_by' => get_current_user_id() ?: null ) );
		$row = $this->get_guide( $page_id );
		$row ? AH_DB_Helper::update( $t, $data, (int) $row->id ) : AH_DB_Helper::insert( $t, $data );
	}

	public function get_guide_points( int $guide_id ): array {
		return AH_DB_Helper::get_list( AH_DB_Helper::table( 'section_guide_through_points' ), array(
			'where' => 'guide_id = %d', 'where_in' => array( $guide_id ),
			'order_by' => 'sort_order', 'order' => 'ASC', 'limit' => 999,
		) );
	}

	public function save_guide_point( array $data, int $id = 0 ): int {
		$t = AH_DB_Helper::table( 'section_guide_through_points' );
		if ( $id ) { AH_DB_Helper::update( $t, $data, $id ); return $id; }
		return AH_DB_Helper::insert( $t, $data );
	}

	public function delete_guide_point( int $id ): bool {
		return AH_DB_Helper::delete( AH_DB_Helper::table( 'section_guide_through_points' ), $id );
	}

	// ---- Stack Items ----

	public function get_stack_items( int $page_id ): array {
		return AH_DB_Helper::get_list( AH_DB_Helper::table( 'section_stack_items' ), array(
			'where' => 'page_id = %d', 'where_in' => array( $page_id ),
			'order_by' => 'sort_order', 'order' => 'ASC', 'limit' => 999,
		) );
	}

	public function save_stack_item( array $data, int $id = 0 ): int {
		$t = AH_DB_Helper::table( 'section_stack_items' );
		if ( $id ) { AH_DB_Helper::update( $t, $data, $id ); return $id; }
		return AH_DB_Helper::insert( $t, $data );
	}

	public function delete_stack_item( int $id ): bool {
		return AH_DB_Helper::delete( AH_DB_Helper::table( 'section_stack_items' ), $id );
	}

	// ---- Difference ----

	public function get_difference( int $page_id ): ?object {
		return AH_DB_Helper::get_by( AH_DB_Helper::table( 'section_difference' ), 'page_id', $page_id );
	}

	public function save_difference( int $page_id, array $data ): void {
		$t   = AH_DB_Helper::table( 'section_difference' );
		$data = array_merge( $data, array( 'page_id' => $page_id, 'updated_by' => get_current_user_id() ?: null ) );
		$row = $this->get_difference( $page_id );
		$row ? AH_DB_Helper::update( $t, $data, (int) $row->id ) : AH_DB_Helper::insert( $t, $data );
	}

	public function get_difference_rows( int $diff_id ): array {
		return AH_DB_Helper::get_list( AH_DB_Helper::table( 'section_difference_table' ), array(
			'where' => 'difference_id = %d', 'where_in' => array( $diff_id ),
			'order_by' => 'sort_order', 'order' => 'ASC', 'limit' => 999,
		) );
	}

	public function save_difference_row( array $data, int $id = 0 ): int {
		$t = AH_DB_Helper::table( 'section_difference_table' );
		if ( $id ) { AH_DB_Helper::update( $t, $data, $id ); return $id; }
		return AH_DB_Helper::insert( $t, $data );
	}

	public function delete_difference_row( int $id ): bool {
		return AH_DB_Helper::delete( AH_DB_Helper::table( 'section_difference_table' ), $id );
	}

	// ---- Experience ----

	public function get_experience( int $page_id ): ?object {
		return AH_DB_Helper::get_by( AH_DB_Helper::table( 'section_experience' ), 'page_id', $page_id );
	}

	public function save_experience( int $page_id, array $data ): void {
		$t   = AH_DB_Helper::table( 'section_experience' );
		$data = array_merge( $data, array( 'page_id' => $page_id, 'updated_by' => get_current_user_id() ?: null ) );
		$row = $this->get_experience( $page_id );
		$row ? AH_DB_Helper::update( $t, $data, (int) $row->id ) : AH_DB_Helper::insert( $t, $data );
	}

	public function get_experience_cards( int $section_id ): array {
		return AH_DB_Helper::get_list( AH_DB_Helper::table( 'section_experience_cards' ), array(
			'where' => 'section_id = %d', 'where_in' => array( $section_id ),
			'order_by' => 'sort_order', 'order' => 'ASC', 'limit' => 999,
		) );
	}

	public function save_experience_card( array $data, int $id = 0 ): int {
		$t = AH_DB_Helper::table( 'section_experience_cards' );
		if ( $id ) { AH_DB_Helper::update( $t, $data, $id ); return $id; }
		return AH_DB_Helper::insert( $t, $data );
	}

	public function delete_experience_card( int $id ): bool {
		return AH_DB_Helper::delete( AH_DB_Helper::table( 'section_experience_cards' ), $id );
	}

	// ---- Why Required (video) ----

	public function get_why_required( int $page_id ): ?object {
		return AH_DB_Helper::get_by( AH_DB_Helper::table( 'section_why_required' ), 'page_id', $page_id );
	}

	public function save_why_required( int $page_id, array $data ): void {
		$t   = AH_DB_Helper::table( 'section_why_required' );
		$data = array_merge( $data, array( 'page_id' => $page_id, 'updated_by' => get_current_user_id() ?: null ) );
		$row = $this->get_why_required( $page_id );
		$row ? AH_DB_Helper::update( $t, $data, (int) $row->id ) : AH_DB_Helper::insert( $t, $data );
	}

	public function get_why_required_cards( int $section_id ): array {
		return AH_DB_Helper::get_list( AH_DB_Helper::table( 'section_why_required_cards' ), array(
			'where' => 'section_id = %d', 'where_in' => array( $section_id ),
			'order_by' => 'sort_order', 'order' => 'ASC', 'limit' => 999,
		) );
	}

	public function save_why_required_card( array $data, int $id = 0 ): int {
		$t = AH_DB_Helper::table( 'section_why_required_cards' );
		if ( $id ) { AH_DB_Helper::update( $t, $data, $id ); return $id; }
		return AH_DB_Helper::insert( $t, $data );
	}

	public function delete_why_required_card( int $id ): bool {
		return AH_DB_Helper::delete( AH_DB_Helper::table( 'section_why_required_cards' ), $id );
	}

	// ---- Featured Properties ----

	public function get_featured( int $page_id ): ?object {
		return AH_DB_Helper::get_by( AH_DB_Helper::table( 'section_featured_properties' ), 'page_id', $page_id );
	}

	public function save_featured( int $page_id, array $data ): void {
		$t   = AH_DB_Helper::table( 'section_featured_properties' );
		$data = array_merge( $data, array( 'page_id' => $page_id, 'updated_by' => get_current_user_id() ?: null ) );
		$row = $this->get_featured( $page_id );
		$row ? AH_DB_Helper::update( $t, $data, (int) $row->id ) : AH_DB_Helper::insert( $t, $data );
	}

	public function get_featured_items( int $section_id ): array {
		return AH_DB_Helper::get_list( AH_DB_Helper::table( 'section_featured_properties_items' ), array(
			'where' => 'section_id = %d', 'where_in' => array( $section_id ),
			'order_by' => 'sort_order', 'order' => 'ASC', 'limit' => 999,
		) );
	}

	public function save_featured_item( array $data, int $id = 0 ): int {
		$t = AH_DB_Helper::table( 'section_featured_properties_items' );
		if ( $id ) { AH_DB_Helper::update( $t, $data, $id ); return $id; }
		return AH_DB_Helper::insert( $t, $data );
	}

	public function delete_featured_item( int $id ): bool {
		return AH_DB_Helper::delete( AH_DB_Helper::table( 'section_featured_properties_items' ), $id );
	}
}
