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

	/** Look up a single item by its URL slug (?ah_news=<slug>). */
	public function get_by_slug( string $slug ): ?object {
		global $wpdb;
		$slug = sanitize_title( $slug );
		if ( '' === $slug ) {
			return null;
		}
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM `{$this->table()}` WHERE slug = %s LIMIT 1",
			$slug
		) ) ?: null;
	}

	/**
	 * Turn a title into a unique slug, appending -2/-3/... on collision.
	 * $exclude_id lets an edit keep its own existing slug out of the clash check.
	 */
	public function unique_slug_from_title( string $title, int $exclude_id = 0 ): string {
		global $wpdb;
		$base = sanitize_title( $title );
		if ( '' === $base ) {
			$base = 'news';
		}
		$slug = $base;
		$i    = 2;
		while ( true ) {
			$clash = $wpdb->get_var( $wpdb->prepare(
				"SELECT id FROM `{$this->table()}` WHERE slug = %s AND id != %d LIMIT 1",
				$slug, $exclude_id
			) );
			if ( ! $clash ) {
				return $slug;
			}
			$slug = $base . '-' . $i;
			$i++;
		}
	}
}
