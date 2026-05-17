<?php
defined( 'ABSPATH' ) || exit;

class AH_Reviews_Model extends AH_Model_Base {

	protected string $table_suffix = 'reviews';

	public function get_paginated( int $page = 1, string $search = '', string $status = '', string $source = '' ): array {
		$where    = array();
		$where_in = array();
		if ( $search ) {
			$s       = AH_DB_Helper::search_where( array( 'reviewer_name', 'reviewer_title', 'review_text' ), $search );
			$where[] = $s['where'];
			$where_in = array_merge( $where_in, $s['where_in'] );
		}
		if ( $status ) { $where[] = 'status = %s'; $where_in[] = $status; }
		if ( $source ) { $where[] = 'source = %s'; $where_in[] = $source; }
		$args = array( 'order_by' => 'sort_order', 'order' => 'ASC' );
		if ( $where ) { $args['where'] = implode( ' AND ', $where ); $args['where_in'] = $where_in; }
		return $this->paginate( $page, $args );
	}

	public function get_page_header( int $page_id ): ?object {
		return AH_DB_Helper::get_by( AH_DB_Helper::table( 'section_reviews_header' ), 'page_id', $page_id );
	}

	public function save_page_header( int $page_id, array $data ): void {
		$t   = AH_DB_Helper::table( 'section_reviews_header' );
		$data = array_merge( $data, array( 'page_id' => $page_id ) );
		$row  = $this->get_page_header( $page_id );
		$row ? AH_DB_Helper::update( $t, $data, (int) $row->id ) : AH_DB_Helper::insert( $t, $data );
	}

	public function get_featured(): array {
		return $this->all( array(
			'where'    => "is_featured = 1 AND status = 'active'",
			'order_by' => 'sort_order',
			'order'    => 'ASC',
		) );
	}
}
