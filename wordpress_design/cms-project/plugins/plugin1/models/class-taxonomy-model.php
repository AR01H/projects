<?php
defined( 'ABSPATH' ) || exit;

class AH_Taxonomy_Model extends AH_Model_Base {

	protected string $table_suffix = 'taxonomies';

	public function get_types(): array {
		return AH_DB_Helper::get_list( AH_DB_Helper::table( 'taxonomy_types' ), array(
			'order_by' => 'name', 'order' => 'ASC', 'limit' => 999,
		) );
	}

	public function get_type( int $id ): ?object {
		return AH_DB_Helper::get_row( AH_DB_Helper::table( 'taxonomy_types' ), $id );
	}

	public function create_type( array $data ): int|false {
		return AH_DB_Helper::insert( AH_DB_Helper::table( 'taxonomy_types' ), $data );
	}

	public function update_type( int $id, array $data ): bool {
		return AH_DB_Helper::update( AH_DB_Helper::table( 'taxonomy_types' ), $data, $id );
	}

	public function delete_type( int $id ): bool {
		return AH_DB_Helper::delete( AH_DB_Helper::table( 'taxonomy_types' ), $id );
	}

	public function get_paginated( int $page = 1, string $search = '', ?int $type_id = null ): array {
		$where    = array();
		$where_in = array();
		if ( $search ) {
			$s       = AH_DB_Helper::search_where( array( 'name', 'slug', 'description' ), $search );
			$where[] = $s['where'];
			$where_in = array_merge( $where_in, $s['where_in'] );
		}
		if ( $type_id ) { $where[] = 'type_id = %d'; $where_in[] = $type_id; }
		$args = array( 'order_by' => 'name', 'order' => 'ASC' );
		if ( $where ) { $args['where'] = implode( ' AND ', $where ); $args['where_in'] = $where_in; }
		return $this->paginate( $page, $args );
	}

	public function get_by_type( int $type_id ): array {
		return $this->all( array(
			'where'    => "type_id = {$type_id} AND status = 'active'",
			'order_by' => 'name',
			'order'    => 'ASC',
		) );
	}

	public function get_children( int $parent_id ): array {
		return $this->all( array(
			'where'    => "parent_id = {$parent_id}",
			'order_by' => 'sort_order',
			'order'    => 'ASC',
		) );
	}

	public function get_parent_terms( ?int $type_id = null ): array {
		$where = '(parent_id IS NULL OR parent_id = 0)';
		if ( $type_id ) $where .= " AND type_id = {$type_id}";
		return $this->all( array(
			'where'    => $where,
			'order_by' => 'name',
			'order'    => 'ASC',
		) );
	}

	public function count_children( int $parent_id ): int {
		global $wpdb;
		$table = AH_DB_Helper::table( 'taxonomies' );
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$table}` WHERE parent_id = %d", $parent_id ) );
	}
}
