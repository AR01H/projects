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

	/** Up to $limit active reviews for a carousel (e.g. Home page), ordered by sort_order. */
	public function get_carousel_reviews( int $limit = 8 ): array {
		return $this->all( array(
			'where'    => "status = 'active'",
			'order_by' => 'sort_order',
			'order'    => 'ASC',
			'limit'    => max( 1, $limit ),
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

	/**
	 * Available `representing` layouts for [ah_review id="X"] - key => admin-facing label.
	 * Single source of truth for the admin dropdown, list-table label, and save-time
	 * validation. Add a new layout by adding a key here + models/reviews/render-{key}.php
	 * (defining ah_review_render_{key}()) + a case in render_review() below.
	 */
	public static function representing_variants(): array {
		return array(
			'big_box'     => 'Big Box (full card)',
			'mini_card'   => 'Mini Card (compact)',
			'with_photos' => 'With Photos (card + photo strip)',
			'full_story'  => 'Full Story (untruncated text + photo gallery)',
		);
	}

	/**
	 * Self-contained single-review card markup, used by [ah_review id="X"].
	 * Each layout is its own file in models/reviews/ (a plain function per
	 * file) so every design is independently easy to find and edit. Every
	 * file ships its own scoped <style> so it renders consistently regardless
	 * of which theme/page it's dropped into (mirrors AH_Form_Builder::render()).
	 */
	public static function render_review( object $r ): string {
		require_once __DIR__ . '/reviews/render-big.php';
		require_once __DIR__ . '/reviews/render-mini.php';
		require_once __DIR__ . '/reviews/render-lightbox.php';
		require_once __DIR__ . '/reviews/render-with-photos.php';
		require_once __DIR__ . '/reviews/render-full-story.php';

		switch ( (string) ( $r->representing ?? '' ) ) {
			case 'mini_card':
				return ah_review_render_mini( $r );
			case 'with_photos':
				return ah_review_render_with_photos( $r );
			case 'full_story':
				return ah_review_render_full_story( $r );
			default:
				return ah_review_render_big( $r );
		}
	}

	/**
	 * Fixed-design card for horizontal carousels (e.g. Home page) - NOT one of
	 * the admin-selectable `representing` layouts. Every review renders the
	 * same compact design here regardless of its own `representing` value, so
	 * a carousel of mixed reviews still looks uniform. Review text is clamped
	 * to 4 lines with an ellipsis.
	 */
	public static function render_carousel_card( object $r ): string {
		require_once __DIR__ . '/reviews/render-carousel-card.php';
		return ah_review_render_carousel_card( $r );
	}
}
