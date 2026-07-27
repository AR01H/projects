<?php
defined( 'ABSPATH' ) || exit;

class AH_Shortcuts_Model extends AH_Model_Base {

	protected string $table_suffix = 'shortcuts';
	protected int $per_page = 20;

	/** Cache bucket for get_active() - same key/table pair used to read, set, and flush it. */
	private const CACHE_CID   = 'active';
	private const CACHE_TABLE = 'shortcuts';

	/**
	 * Active shortcuts only - used by the dynamic shortcode registration hook,
	 * which runs on `init` on EVERY request (frontend and admin), so this is
	 * cached via the plugin's existing AH_Cache (a thin wrapper over WP
	 * transients, already gated off in wp-admin and off-by-default on the
	 * frontend unless Global Settings > enable caching is on - so this only
	 * ever changes behaviour when the site owner has already opted into
	 * caching everywhere else, never a surprise regression on its own).
	 * Invalidated immediately by create()/update()/delete() below.
	 */
	public function get_active(): array {
		if ( class_exists( 'AH_Cache' ) ) {
			$cached = AH_Cache::get( self::CACHE_CID, self::CACHE_TABLE );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		global $wpdb;
		$t = $this->table();
		// This hook runs on `init`, before AH_DB_Installer::maybe_upgrade()'s
		// `wp_loaded` hook, so on the very first request after this table is
		// introduced it may not exist yet - check first rather than let $wpdb
		// log a query error.
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $t ) ) !== $t ) {
			return array();
		}
		$rows = $wpdb->get_results( "SELECT * FROM `{$t}` WHERE status = 'active'" ) ?: array();

		if ( class_exists( 'AH_Cache' ) ) {
			AH_Cache::set( self::CACHE_CID, $rows, self::CACHE_TABLE );
		}

		return $rows;
	}

	public function create( array $data ): int {
		$id = parent::create( $data );
		if ( $id ) {
			$this->flush_active_cache();
		}
		return $id;
	}

	public function update( int $id, array $data ): bool {
		$result = parent::update( $id, $data );
		if ( $result ) {
			$this->flush_active_cache();
		}
		return $result;
	}

	public function delete( int $id ): bool {
		$result = parent::delete( $id );
		if ( $result ) {
			$this->flush_active_cache();
		}
		return $result;
	}

	private function flush_active_cache(): void {
		if ( class_exists( 'AH_Cache' ) ) {
			AH_Cache::clear_all( self::CACHE_CID, self::CACHE_TABLE );
		}
	}

	public function get_paginated( int $page = 1, string $search = '', string $status = '' ): array {
		global $wpdb;
		$where_clauses = array();
		$where_values  = array();

		if ( $search ) {
			$search_result   = AH_DB_Helper::search_where( array( 'tag', 'label' ), $search );
			$where_clauses[] = $search_result['where'];
			$where_values    = array_merge( $where_values, $search_result['where_in'] );
		}
		if ( $status ) {
			$where_clauses[] = 'status = %s';
			$where_values[]  = $status;
		}

		$where = ! empty( $where_clauses ) ? implode( ' AND ', $where_clauses ) : '';

		return $this->paginate( $page, array(
			'where'    => $where,
			'where_in' => $where_values,
			'order_by' => 'label',
			'order'    => 'ASC',
		) );
	}

	/** True if $tag is already used by another row (or any row, when $exclude_id is 0). */
	public function tag_exists( string $tag, int $exclude_id = 0 ): bool {
		global $wpdb;
		$t = $this->table();
		return (bool) $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM `{$t}` WHERE tag = %s AND id != %d LIMIT 1",
			$tag, $exclude_id
		) );
	}
}
