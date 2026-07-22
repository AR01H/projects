<?php
defined( 'ABSPATH' ) || exit;

class AH_Faqs_Model extends AH_Model_Base {

	protected string $table_suffix = 'faqs';

	/**
	 * Per-request cache for FAQ queries.
	 * Keys: 'global' or 'page_{id}' => array
	 *
	 * @var array<string,array>
	 */
	protected static array $faq_cache = array();

	public function get_paginated( int $page = 1, string $search = '', ?int $page_id = null, ?int $_unused = null, string $section = '' ): array {
		$where    = array();
		$where_in = array();
		if ( $search ) {
			$s        = AH_DB_Helper::search_where( array( 'question', 'answer' ), $search );
			$where[]  = $s['where'];
			$where_in = array_merge( $where_in, $s['where_in'] );
		}
		if ( $page_id !== null ) {
			$where[]    = 'page_id = %d';
			$where_in[] = $page_id;
		}
		if ( $section !== '' ) {
			$where[]    = 'section = %s';
			$where_in[] = $section;
		}
		$args = array( 'order_by' => 'sort_order', 'order' => 'ASC' );
		if ( $where ) { $args['where'] = implode( ' AND ', $where ); $args['where_in'] = $where_in; }
		return $this->paginate( $page, $args );
	}

	public function get_distinct_sections(): array {
		global $wpdb;
		$table = $this->table();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_col( "SELECT DISTINCT section FROM `{$table}` WHERE section IS NOT NULL AND section != '' ORDER BY section ASC" ) ?: array();
	}

	/**
	 * Return FAQs for a specific page. When null is passed, return global FAQs.
	 * Results are cached for the duration of the request.
	 *
	 * @param int|null $page_id
	 * @return array
	 */
	public function get_for_page( ?int $page_id ): array {
		if ( $page_id === null ) {
			return $this->get_global();
		}
		$key = 'page_' . (int) $page_id;
		if ( isset( self::$faq_cache[ $key ] ) ) {
			return self::$faq_cache[ $key ];
		}

		$page_id = (int) $page_id;
		$rows = $this->all( array(
			'where'    => "page_id = {$page_id} AND status = 'active'",
			'order_by' => 'sort_order',
			'order'    => 'ASC',
		) );

		self::$faq_cache[ $key ] = is_array( $rows ) ? $rows : array();
		return self::$faq_cache[ $key ];
	}

	public function get_global(): array {
		$key = 'global';
		if ( isset( self::$faq_cache[ $key ] ) ) {
			return self::$faq_cache[ $key ];
		}

		$rows = $this->all( array(
			// Must also exclude slug-attached rows (attached_slug IS NOT NULL) -
			// both "true global" and "slug-attached" FAQs share page_id IS NULL,
			// so without this they were indistinguishable and slug-attached FAQs
			// leaked into every "Global" surface (the /faqs/ listing, sidebar
			// fallback, REST /faqs endpoint).
			'where'    => "page_id IS NULL AND attached_slug IS NULL AND status = 'active'",
			'order_by' => 'sort_order',
			'order'    => 'ASC',
		) );

		self::$faq_cache[ $key ] = is_array( $rows ) ? $rows : array();
		return self::$faq_cache[ $key ];
	}

	/**
	 * Return active FAQs attached directly to a URL slug (independent of the
	 * page_id/AH_Pages_Model registry) - lets a FAQ target any page by slug.
	 * Results are cached for the duration of the request.
	 *
	 * @param string $slug
	 * @return array
	 */
	public function get_by_slug( string $slug ): array {
		$slug = sanitize_title( $slug );
		if ( '' === $slug ) {
			return array();
		}
		$key = 'slug_' . $slug;
		if ( isset( self::$faq_cache[ $key ] ) ) {
			return self::$faq_cache[ $key ];
		}

		$rows = $this->all( array(
			'where'    => "attached_slug = %s AND status = 'active'",
			'where_in' => array( $slug ),
			'order_by' => 'sort_order',
			'order'    => 'ASC',
		) );

		self::$faq_cache[ $key ] = is_array( $rows ) ? $rows : array();
		return self::$faq_cache[ $key ];
	}

	public function get_faq_header( int $page_id ): ?object {
		return AH_DB_Helper::get_by( AH_DB_Helper::table( 'section_faq_header' ), 'page_id', $page_id );
	}

	public function save_faq_header( int $page_id, array $data ): void {
		$t   = AH_DB_Helper::table( 'section_faq_header' );
		$data = array_merge( $data, array( 'page_id' => $page_id ) );
		$row = $this->get_faq_header( $page_id );
		$row ? AH_DB_Helper::update( $t, $data, (int) $row->id ) : AH_DB_Helper::insert( $t, $data );

		// Invalidate per-request cache for this page and global list
		$pkey = 'page_' . (int) $page_id;
		if ( isset( self::$faq_cache[ $pkey ] ) ) {
			unset( self::$faq_cache[ $pkey ] );
		}
		if ( isset( self::$faq_cache['global'] ) ) {
			unset( self::$faq_cache['global'] );
		}
	}
}
