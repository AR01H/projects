<?php
/**
 * Repository for category page database queries.
 *
 * Extracts raw $wpdb queries from page_category_logical.php into a
 * single, testable class. All public methods accept primitive
 * arguments and return plain objects or arrays.
 *
 * @package Adn\Theme\Repository
 */

namespace Adn\Theme\Repository;

defined( 'ABSPATH' ) || exit;

class CategoryRepository {

	/**
	 * Fetch the active parent term row by slug. Returns null when not found.
	 */
	public function get_parent_term_by_slug( string $slug ): ?object {
		if ( ! function_exists( 'adn_cms_available' ) || ! \adn_cms_available() ) {
			return null;
		}
		global $wpdb;
		$table = $wpdb->prefix . 'ah_taxonomy_parent_terms';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return null;
		}
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM `{$table}` WHERE slug = %s AND status = 'active' LIMIT 1",
			$slug
		) );
	}

	/**
	 * Fetch the active parent term row by ID.
	 * Used for the parent-term lookup in the topic category page.
	 */
	public function get_parent_term_by_id( int $id ): ?object {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_taxonomy_parent_terms';
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT id, name, slug, icon_emoji FROM `{$table}` WHERE id = %d LIMIT 1",
			$id
		) );
	}

	/**
	 * Check whether a column exists on a table.
	 */
	public function has_column( string $table, string $column ): bool {
		global $wpdb;
		return (bool) $wpdb->get_var( $wpdb->prepare( "SHOW COLUMNS FROM `{$table}` LIKE %s", $column ) );
	}

	/**
	 * Get child topic IDs for a given parent term via direct column query.
	 * Fallback when adn_cms_topics() returns nothing.
	 */
	public function get_child_topic_ids( int $parent_id ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_taxonomies';
		if ( ! $this->has_column( $table, 'parent_term_id' ) ) {
			return array();
		}
		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT id FROM `{$table}` WHERE parent_term_id = %d AND status = 'active'",
			$parent_id
		) );
		$ids = array();
		foreach ( (array) $rows as $r ) {
			$ids[] = (int) $r->id;
		}
		return $ids;
	}

	/**
	 * Fetch resources by IDs, preserving the given order.
	 */
	public function get_resources_by_ids( array $ids ): array {
		if ( empty( $ids ) ) {
			return array();
		}
		global $wpdb;
		$table    = $wpdb->prefix . 'ah_resources';
		$id_in    = implode( ',', array_map( 'intval', $ids ) );
		$results  = $wpdb->get_results( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			"SELECT * FROM `{$table}` WHERE id IN ({$id_in}) AND status = 'active' ORDER BY FIELD(id, {$id_in})"
		);
		return is_array( $results ) ? $results : array();
	}

	/**
	 * Fetch FAQs by IDs, preserving the given order.
	 *
	 * @param int[] $ids FAQ IDs in admin-defined order.
	 * @return array<int, array{id:int, question:string, answer:string, link_url:string, link_text:string}>
	 */
	public function get_faqs_by_ids( array $ids ): array {
		if ( empty( $ids ) ) {
			return array();
		}
		global $wpdb;
		$table = $wpdb->prefix . 'ah_faqs';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return array();
		}
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, question, answer, link_url, link_text FROM `{$table}` WHERE id IN ({$placeholders}) AND status = 'active'",
				...$ids
			)
		);

		// Restore admin-defined order.
		$id_pos = array_flip( $ids );
		usort( $rows, function ( $a, $b ) use ( $id_pos ) {
			return ( isset( $id_pos[ $a->id ] ) ? $id_pos[ $a->id ] : 0 )
			     - ( isset( $id_pos[ $b->id ] ) ? $id_pos[ $b->id ] : 0 );
		} );

		$built = array();
		foreach ( $rows as $row ) {
			$built[] = array(
				'id'        => (int)    $row->id,
				'question'  => (string) $row->question,
				'answer'    => (string) $row->answer,
				'link_url'  => (string) ( $row->link_url  ?? '' ),
				'link_text' => (string) ( $row->link_text ?? '' ),
			);
		}
		return $built;
	}
}
