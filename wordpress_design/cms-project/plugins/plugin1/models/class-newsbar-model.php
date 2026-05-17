<?php
defined( 'ABSPATH' ) || exit;

class AH_Newsbar_Model extends AH_Model_Base {

	protected string $table_suffix = 'news_bar_items';

	public function get_active(): array {
		global $wpdb;
		$t    = $this->table();
		$today = current_time( 'Y-m-d' );
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM `{$t}` WHERE status = 'active'
			 AND (start_date IS NULL OR start_date <= %s)
			 AND (end_date IS NULL OR end_date >= %s)
			 ORDER BY sort_order ASC",
			$today, $today
		) ) ?: array();
	}

	public function get_paginated( int $page = 1, string $search = '' ): array {
		if ( $search ) {
			$s = AH_DB_Helper::search_where( array( 'text', 'content', 'link_url' ), $search );
			return $this->paginate( $page, array_merge( $s, array( 'order_by' => 'sort_order', 'order' => 'ASC' ) ) );
		}
		return $this->paginate( $page, array( 'order_by' => 'sort_order', 'order' => 'ASC' ) );
	}
}
