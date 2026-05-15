<?php
defined( 'ABSPATH' ) || exit;

class AH_Media_Model extends AH_Model_Base {

	protected string $table_suffix = 'media';

	public function get_paginated( int $page = 1, string $search = '', string $mime_filter = '' ): array {
		$where    = array();
		$where_in = array();

		if ( $search ) {
			$s         = AH_DB_Helper::search_where( array( 'file_name', 'alt_text', 'caption' ), $search );
			$where[]   = $s['where'];
			$where_in  = array_merge( $where_in, $s['where_in'] );
		}
		if ( $mime_filter ) {
			$where[]   = 'mime_type LIKE %s';
			$where_in[] = $mime_filter . '%';
		}

		$args = array(
			'order_by' => 'created_at',
			'order'    => 'DESC',
		);
		if ( $where ) {
			$args['where']    = implode( ' AND ', $where );
			$args['where_in'] = $where_in;
		}

		return $this->paginate( $page, $args );
	}

	public function get_url( int $id ): string {
		$row = $this->find( $id );
		return $row ? esc_url( $row->file_url ) : '';
	}
}
