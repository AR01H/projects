<?php
/**
 * Repository for topic/category listing page database queries.
 *
 * Extracts raw $wpdb queries from page_topic_category_logical.php into a
 * single, testable class. All public methods accept primitive
 * arguments and return plain objects or arrays.
 *
 * @package Adn\Theme\Repository
 */

namespace Adn\Theme\Repository;

defined( 'ABSPATH' ) || exit;

class TopicCategoryRepository {

	/**
	 * Fetch a parent term row by ID from ah_taxonomy_parent_terms.
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
	 * Fetch a parent term row by ID from ah_taxonomies (fallback).
	 */
	public function get_parent_taxonomy_by_id( int $id ): ?object {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_taxonomies';
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT id, name, slug, icon_emoji FROM `{$table}` WHERE id = %d LIMIT 1",
			$id
		) );
	}

	/**
	 * Get sibling terms (sub-categories) belonging to the same parent.
	 *
	 * Uses parent_term_id when available, falls back to parent_id.
	 * Excludes glossary-type terms.
	 *
	 * @param int    $parent_term_id  The parent term's ID.
	 * @param int    $exclude_term_id The current term to exclude.
	 * @param string $column          Either 'parent_term_id' or 'parent_id'.
	 * @param int    $limit           Max rows (0 = no limit).
	 * @return object[]
	 */
	public function get_sibling_terms( int $parent_term_id, int $exclude_term_id, string $column = 'parent_term_id', int $limit = 0 ): array {
		global $wpdb;
		$tax_t   = $wpdb->prefix . 'ah_taxonomies';
		$types_t = $wpdb->prefix . 'ah_taxonomy_types';

		$limit_clause = $limit > 0 ? "LIMIT {$limit}" : '';
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT t.id, t.name, t.slug, t.description, t.icon_emoji, t.image_id
			 FROM `{$tax_t}` t
			 LEFT JOIN `{$types_t}` tt ON tt.id = t.type_id
			 WHERE t.{$column} = %d AND t.id != %d AND t.status = 'active'
			   AND (tt.slug IS NULL OR tt.slug != 'glossary')
			 ORDER BY t.sort_order ASC, t.name ASC {$limit_clause}",
			$parent_term_id,
			$exclude_term_id
		) );

		return $rows ?: array();
	}

	/**
	 * Get all sibling terms for the sidebar (no limit).
	 */
	public function get_sidebar_sibling_terms( int $parent_term_id, int $exclude_term_id, string $column = 'parent_term_id' ): array {
		return $this->get_sibling_terms( $parent_term_id, $exclude_term_id, $column, 0 );
	}

	/**
	 * Get WP post IDs linked to a taxonomy term via ah_content_taxonomies.
	 *
	 * @param int $taxonomy_id The taxonomy term ID.
	 * @return int[]
	 */
	public function get_term_post_ids( int $taxonomy_id ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_content_taxonomies';
		$ids   = $wpdb->get_col( $wpdb->prepare(
			"SELECT object_id FROM `{$table}` WHERE object_type = 'wp_post' AND taxonomy_id = %d",
			$taxonomy_id
		) );
		return array_map( 'intval', (array) $ids );
	}
}
