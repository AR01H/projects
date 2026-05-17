<?php
defined( 'ABSPATH' ) || exit;

class AH_Posts_Model extends AH_Model_Base {

	protected string $table_suffix = 'posts';

	public function get_paginated( int $page = 1, array $filters = array() ): array {
		$where    = array();
		$where_in = array();

		if ( ! empty( $filters['search'] ) ) {
			$s       = AH_DB_Helper::search_where( array( 'title', 'excerpt', 'slug' ), $filters['search'] );
			$where[] = $s['where'];
			$where_in = array_merge( $where_in, $s['where_in'] );
		}
		if ( ! empty( $filters['post_type'] ) ) { $where[] = 'post_type = %s'; $where_in[] = $filters['post_type']; }
		if ( ! empty( $filters['status'] ) )    { $where[] = 'status = %s';    $where_in[] = $filters['status']; }

		$args = array( 'order_by' => 'created_at', 'order' => 'DESC' );
		if ( $where ) { $args['where'] = implode( ' AND ', $where ); $args['where_in'] = $where_in; }

		return $this->paginate( $page, $args );
	}

	public function get_taxonomies( int $post_id ): array {
		global $wpdb;
		$pt = AH_DB_Helper::table( 'post_taxonomies' );
		$tt = AH_DB_Helper::table( 'taxonomies' );
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT t.* FROM `{$tt}` t INNER JOIN `{$pt}` pt ON pt.taxonomy_id = t.id WHERE pt.post_id = %d",
			$post_id
		) ) ?: array();
	}

	public function sync_taxonomies( int $post_id, array $taxonomy_ids ): void {
		$t = AH_DB_Helper::table( 'post_taxonomies' );
		AH_DB_Helper::delete_where( $t, array( 'post_id' => $post_id ) );
		foreach ( array_unique( $taxonomy_ids ) as $tid ) {
			AH_DB_Helper::insert( $t, array( 'post_id' => $post_id, 'taxonomy_id' => (int) $tid ) );
		}
	}

	public function get_links( int $post_id ): array {
		return AH_DB_Helper::get_list( AH_DB_Helper::table( 'post_links' ), array(
			'where' => 'post_id = %d', 'where_in' => array( $post_id ),
			'order_by' => 'sort_order', 'order' => 'ASC', 'limit' => 999,
		) );
	}

	public function save_links( int $post_id, array $links ): void {
		$t = AH_DB_Helper::table( 'post_links' );
		AH_DB_Helper::delete_where( $t, array( 'post_id' => $post_id ) );
		foreach ( $links as $i => $link ) {
			if ( ! empty( $link['url'] ) ) {
				AH_DB_Helper::insert( $t, array(
					'post_id'   => $post_id,
					'label'     => sanitize_text_field( $link['label'] ?? '' ),
					'url'       => esc_url_raw( $link['url'] ),
					'link_type' => sanitize_key( $link['link_type'] ?? 'reference' ),
					'sort_order' => $i,
				) );
			}
		}
	}

	public function get_stack_items( int $post_id ): array {
		return AH_DB_Helper::get_list( AH_DB_Helper::table( 'post_stack_items' ), array(
			'where' => 'post_id = %d', 'where_in' => array( $post_id ),
			'order_by' => 'sort_order', 'order' => 'ASC', 'limit' => 999,
		) );
	}

	public function get_table_blocks( int $post_id ): array {
		return AH_DB_Helper::get_list( AH_DB_Helper::table( 'post_table_blocks' ), array(
			'where' => 'post_id = %d', 'where_in' => array( $post_id ),
			'order_by' => 'sort_order', 'order' => 'ASC', 'limit' => 999,
		) );
	}

	public function get_listing_header( int $page_id ): ?object {
		return AH_DB_Helper::get_by( AH_DB_Helper::table( 'post_listing_page_header' ), 'page_id', $page_id );
	}

	public function save_listing_header( int $page_id, array $data ): void {
		$t   = AH_DB_Helper::table( 'post_listing_page_header' );
		$data = array_merge( $data, array( 'page_id' => $page_id, 'updated_by' => get_current_user_id() ?: null ) );
		$row = $this->get_listing_header( $page_id );
		$row ? AH_DB_Helper::update( $t, $data, (int) $row->id ) : AH_DB_Helper::insert( $t, $data );
	}

	public function get_news_cards( int $post_id ): array {
		return AH_DB_Helper::get_list( AH_DB_Helper::table( 'news_detail_big_cards' ), array(
			'where' => 'post_id = %d', 'where_in' => array( $post_id ),
			'order_by' => 'sort_order', 'order' => 'ASC', 'limit' => 999,
		) );
	}

	public function save_news_card( array $data, int $id = 0 ): int|false {
		$t = AH_DB_Helper::table( 'news_detail_big_cards' );
		if ( $id ) { AH_DB_Helper::update( $t, $data, $id ); return $id; }
		return AH_DB_Helper::insert( $t, $data );
	}

	public function delete_news_card( int $id ): bool {
		return AH_DB_Helper::delete( AH_DB_Helper::table( 'news_detail_big_cards' ), $id );
	}

	public function get_card_links( int $card_id ): array {
		return AH_DB_Helper::get_list( AH_DB_Helper::table( 'news_detail_card_links' ), array(
			'where' => 'card_id = %d', 'where_in' => array( $card_id ),
			'order_by' => 'sort_order', 'order' => 'ASC', 'limit' => 999,
		) );
	}

	public function increment_view( int $id ): void {
		global $wpdb;
		$t = $this->table();
		$wpdb->query( $wpdb->prepare( "UPDATE `{$t}` SET view_count = view_count + 1 WHERE id = %d", $id ) );
	}
}
