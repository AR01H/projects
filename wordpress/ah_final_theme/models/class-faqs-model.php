<?php
defined( 'ABSPATH' ) || exit;

class AH_Faqs_Model extends AH_Model_Base {

	protected string $table_suffix = 'faqs';

	public function get_paginated( int $page = 1, string $search = '', ?int $page_id = null ): array {
		$where    = array();
		$where_in = array();
		if ( $search ) {
			$s       = AH_DB_Helper::search_where( array( 'question', 'answer' ), $search );
			$where[] = $s['where'];
			$where_in = array_merge( $where_in, $s['where_in'] );
		}
		if ( $page_id !== null ) {
			$where[]    = 'page_id = %d';
			$where_in[] = $page_id;
		}
		$args = array( 'order_by' => 'sort_order', 'order' => 'ASC' );
		if ( $where ) { $args['where'] = implode( ' AND ', $where ); $args['where_in'] = $where_in; }
		return $this->paginate( $page, $args );
	}

	public function get_for_page( int $page_id ): array {
		return $this->all( array(
			'where'    => "page_id = {$page_id} AND status = 'active'",
			'order_by' => 'sort_order',
			'order'    => 'ASC',
		) );
	}

	public function get_global(): array {
		return $this->all( array(
			'where'    => "page_id IS NULL AND status = 'active'",
			'order_by' => 'sort_order',
			'order'    => 'ASC',
		) );
	}

	public function get_faq_header( int $page_id ): ?object {
		return AH_DB_Helper::get_by( AH_DB_Helper::table( 'section_faq_header' ), 'page_id', $page_id );
	}

	public function save_faq_header( int $page_id, array $data ): void {
		$t   = AH_DB_Helper::table( 'section_faq_header' );
		$data = array_merge( $data, array( 'page_id' => $page_id ) );
		$row = $this->get_faq_header( $page_id );
		$row ? AH_DB_Helper::update( $t, $data, (int) $row->id ) : AH_DB_Helper::insert( $t, $data );
	}
}
