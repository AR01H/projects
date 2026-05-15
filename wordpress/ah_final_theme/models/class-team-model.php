<?php
defined( 'ABSPATH' ) || exit;

class AH_Team_Model extends AH_Model_Base {

	protected string $table_suffix = 'team_members';

	public function get_paginated( int $page = 1, string $search = '' ): array {
		if ( $search ) {
			$s = AH_DB_Helper::search_where( array( 'name', 'designation', 'bio' ), $search );
			return $this->paginate( $page, array_merge( $s, array( 'order_by' => 'sort_order', 'order' => 'ASC' ) ) );
		}
		return $this->paginate( $page, array( 'order_by' => 'sort_order', 'order' => 'ASC' ) );
	}

	public function get_active(): array {
		return $this->all( array(
			'where'    => "status = 'active'",
			'order_by' => 'sort_order',
			'order'    => 'ASC',
		) );
	}
}
