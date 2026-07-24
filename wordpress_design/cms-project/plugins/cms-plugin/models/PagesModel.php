<?php
defined( 'ABSPATH' ) || exit;

class AH_Pages_Model extends AH_Model_Base {

	protected string $table_suffix = 'pages';

	public function get_by_slug( string $slug ): ?object {
		return $this->find_by( 'slug', $slug );
	}

	public function get_by_type( string $type ): ?object {
		return $this->find_by( 'page_type', $type );
	}

	public function get_active(): array {
		return $this->all( array(
			'where'    => "status = 'active'",
			'order_by' => 'title',
			'order'    => 'ASC',
		) );
	}

	public function get_sections( int $page_id ): array {
		global $wpdb;
		$t = AH_DB_Helper::table( 'page_sections' );
		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM `{$t}` WHERE page_id = %d ORDER BY sort_order ASC", $page_id )
		) ?: array();
	}

	public function upsert_section( int $page_id, string $section_key, bool $visible, int $sort_order ): void {
		global $wpdb;
		$t = AH_DB_Helper::table( 'page_sections' );
		$existing = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM `{$t}` WHERE page_id = %d AND section_key = %s",
			$page_id, $section_key
		) );
		$data = array(
			'is_visible' => $visible ? 1 : 0,
			'sort_order' => $sort_order,
			'updated_by' => get_current_user_id() ?: null,
		);
		if ( $existing ) {
			$wpdb->update( $t, $data, array( 'id' => (int) $existing ) );
		} else {
			$wpdb->insert( $t, array_merge( $data, array( 'page_id' => $page_id, 'section_key' => $section_key ) ) );
		}
	}

	public function get_paginated( int $page = 1, string $search = '' ): array {
		if ( $search ) {
			$s = AH_DB_Helper::search_where( array( 'title', 'slug' ), $search );
			return $this->paginate( $page, $s );
		}
		return $this->paginate( $page, array( 'order_by' => 'title', 'order' => 'ASC' ) );
	}
}
