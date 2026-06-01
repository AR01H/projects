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

	// ── Occasion / Gallery Images ──────────────────────────────────────────────

	private function images_table(): string {
		global $wpdb;
		return $wpdb->prefix . 'ah_review_images';
	}

	/** Return all images for a review ordered by sort_order. */
	public function get_images( int $review_id ): array {
		global $wpdb;
		$t = $this->images_table();
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM `{$t}` WHERE review_id = %d ORDER BY sort_order ASC, id ASC",
			$review_id
		) ) ?: [];
	}

	/**
	 * Replace all images for a review with the supplied WP attachment IDs.
	 * Existing rows not in the new list are deleted.
	 *
	 * @param array<int> $image_ids  Ordered list of WP attachment IDs.
	 */
	public function save_images( int $review_id, array $image_ids ): void {
		global $wpdb;
		$t = $this->images_table();
		$wpdb->delete( $t, [ 'review_id' => $review_id ], [ '%d' ] );
		foreach ( array_values( $image_ids ) as $i => $img_id ) {
			$wpdb->insert( $t, [
				'review_id'  => $review_id,
				'image_id'   => (int) $img_id,
				'sort_order' => $i,
			], [ '%d', '%d', '%d' ] );
		}
	}

	/** Delete a single image row by its own ID. */
	public function delete_image( int $row_id ): void {
		global $wpdb;
		$wpdb->delete( $this->images_table(), [ 'id' => $row_id ], [ '%d' ] );
	}

	/**
	 * Return the names that should be highlighted inside this review's text.
	 * Pulls active taxonomy terms attached to the review whose type slug is 'highlight-names'.
	 * Results ordered longest-first so longer phrases are matched before shorter sub-strings.
	 */
	public function get_highlight_names( int $review_id ): array {
		global $wpdb;
		$ct    = AH_DB_Helper::table( 'content_taxonomies' );
		$tax   = AH_DB_Helper::table( 'taxonomies' );
		$types = AH_DB_Helper::table( 'taxonomy_types' );

		return $wpdb->get_col( $wpdb->prepare(
			"SELECT t.name
			 FROM `{$tax}` t
			 INNER JOIN `{$ct}` ct
			     ON ct.taxonomy_id = t.id
			    AND ct.object_type = 'review'
			    AND ct.object_id   = %d
			 INNER JOIN `{$types}` tt
			     ON tt.id = t.type_id
			    AND tt.slug = 'highlight-names'
			 WHERE t.status = 'active'
			 ORDER BY LENGTH(t.name) DESC",
			$review_id
		) ) ?: [];
	}

	/**
	 * Return active reviews tagged with a specific taxonomy term slug.
	 * Uses the ah_content_taxonomies pivot with object_type = 'review'.
	 *
	 * @param string $taxonomy_slug  e.g. 'partner', 'event', 'customer'
	 * @param int    $limit          0 = no limit
	 */
	public function get_by_taxonomy_slug( string $taxonomy_slug, int $limit = 0 ): array {
		global $wpdb;

		$rv  = AH_DB_Helper::table( 'reviews' );
		$ct  = AH_DB_Helper::table( 'content_taxonomies' );
		$tax = AH_DB_Helper::table( 'taxonomies' );

		$sql = $wpdb->prepare(
			"SELECT r.*
			 FROM `{$rv}` r
			 INNER JOIN `{$ct}` ct  ON ct.object_type = 'review' AND ct.object_id = r.id
			 INNER JOIN `{$tax}` t  ON t.id = ct.taxonomy_id AND t.slug = %s AND t.status = 'active'
			 WHERE r.status = 'active'
			 ORDER BY r.sort_order ASC, r.id ASC",
			$taxonomy_slug
		);

		if ( $limit > 0 ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql .= $wpdb->prepare( ' LIMIT %d', $limit );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( $sql ) ?: array();
	}
}
