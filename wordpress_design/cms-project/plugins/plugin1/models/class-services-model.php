<?php
defined( 'ABSPATH' ) || exit;

class AH_Services_Model extends AH_Model_Base {

	protected string $table_suffix = 'services';

	public function get_paginated( int $page = 1, string $search = '', string $status = '' ): array {
		$where    = array();
		$where_in = array();
		if ( $search ) {
			$s         = AH_DB_Helper::search_where( array( 'title', 'slug', 'short_desc' ), $search );
			$where[]   = $s['where'];
			$where_in  = array_merge( $where_in, $s['where_in'] );
		}
		if ( $status ) {
			$where[]    = 'status = %s';
			$where_in[] = $status;
		}
		$args = array( 'order_by' => 'sort_order', 'order' => 'ASC' );
		if ( $where ) {
			$args['where']    = implode( ' AND ', $where );
			$args['where_in'] = $where_in;
		}
		return $this->paginate( $page, $args );
	}

	public function get_bullet_points( int $service_id ): array {
		return AH_DB_Helper::get_list( AH_DB_Helper::table( 'service_bullet_points' ), array(
			'where' => 'service_id = %d', 'where_in' => array( $service_id ),
			'order_by' => 'sort_order', 'order' => 'ASC', 'limit' => 999,
		) );
	}

	public function save_bullet_points( int $service_id, array $points ): void {
		$t = AH_DB_Helper::table( 'service_bullet_points' );
		AH_DB_Helper::delete_where( $t, array( 'service_id' => $service_id ) );
		foreach ( $points as $i => $text ) {
			if ( trim( $text ) ) {
				AH_DB_Helper::insert( $t, array( 'service_id' => $service_id, 'point_text' => sanitize_text_field( $text ), 'sort_order' => $i ) );
			}
		}
	}

	public function get_page_header( int $page_id ): ?object {
		return AH_DB_Helper::get_by( AH_DB_Helper::table( 'services_page_header' ), 'page_id', $page_id );
	}

	public function save_page_header( int $page_id, array $data ): void {
		$t   = AH_DB_Helper::table( 'services_page_header' );
		$data = array_merge( $data, array( 'page_id' => $page_id, 'updated_by' => get_current_user_id() ?: null ) );
		$row = $this->get_page_header( $page_id );
		$row ? AH_DB_Helper::update( $t, $data, (int) $row->id ) : AH_DB_Helper::insert( $t, $data );
	}

	public function get_taxonomies( int $service_id ): array {
		return ( new AH_Content_Taxonomy_Model() )->get_terms( 'service', $service_id );
	}

	public function sync_taxonomies( int $service_id, array $taxonomy_ids ): void {
		( new AH_Content_Taxonomy_Model() )->sync_terms( 'service', $service_id, $taxonomy_ids );

		$t = AH_DB_Helper::table( 'service_taxonomies' );
		AH_DB_Helper::delete_where( $t, array( 'service_id' => $service_id ) );
		foreach ( array_unique( $taxonomy_ids ) as $tid ) {
			AH_DB_Helper::insert( $t, array( 'service_id' => $service_id, 'taxonomy_id' => (int) $tid ) );
		}
	}
}
