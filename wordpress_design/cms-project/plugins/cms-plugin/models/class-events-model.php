<?php
defined( 'ABSPATH' ) || exit;

class AH_Events_Model extends AH_Model_Base {

	protected string $table_suffix = 'events';

	public function get_paginated( int $page = 1, string $search = '', string $status = '' ): array {
		$where    = array();
		$where_in = array();

		if ( $search ) {
			$s        = AH_DB_Helper::search_where( array( 'title', 'description' ), $search );
			$where[]  = $s['where'];
			$where_in = array_merge( $where_in, $s['where_in'] );
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

	/**
	 * Return all active events ordered by sort_order.
	 * Decodes the JSON items field into a PHP array on each row.
	 */
	public function get_active( int $limit = 0 ): array {
		$args = array(
			'where'    => "status = 'active'",
			'order_by' => 'sort_order',
			'order'    => 'ASC',
		);
		if ( $limit > 0 ) {
			$args['limit'] = $limit;
		}
		$rows = $this->all( $args );

		foreach ( $rows as $row ) {
			$row->items = $row->items ? json_decode( $row->items, true ) : array();
		}

		return $rows;
	}

	/**
	 * Normalise the items field before saving: accept array or newline/comma string.
	 */
	public static function normalise_items( $raw ): string {
		if ( is_array( $raw ) ) {
			$items = array_values( array_filter( array_map( 'trim', $raw ) ) );
		} elseif ( is_string( $raw ) && $raw !== '' ) {
			$items = array_values( array_filter( array_map( 'trim', preg_split( '/[\r\n]+/', $raw ) ) ) );
		} else {
			$items = array();
		}
		return wp_json_encode( $items );
	}
}
