<?php
/**
 * Repository for home page database queries.
 *
 * Extracts raw $wpdb queries from page_home_logical.php into a
 * single, testable class. All public methods accept primitive
 * arguments and return plain objects or arrays.
 *
 * @package Adn\Theme\Repository
 */

namespace Adn\Theme\Repository;

defined( 'ABSPATH' ) || exit;

class HomeRepository {

	/**
	 * Fetch term icons (term + parent) for a set of post IDs.
	 *
	 * Uses a single JOIN query across content_taxonomies → taxonomies → parent taxonomies.
	 * Returns an associative array keyed by post ID, values are icon strings.
	 *
	 * @param int[] $post_ids WP post IDs to look up.
	 * @return array<int, string>  post_id => icon_emoji (primary term).
	 */
	public function get_hot_topic_icons( array $post_ids ): array {
		if ( empty( $post_ids ) ) {
			return array();
		}
		if ( ! function_exists( 'adn_cms_available' ) || ! \adn_cms_available() ) {
			return array();
		}

		global $wpdb;
		$ct     = \adn_cms_table( 'content_taxonomies' );
		$tax    = \adn_cms_table( 'taxonomies' );
		$id_in  = implode( ',', array_map( 'intval', $post_ids ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			"SELECT ct.post_id,
			        t.icon_emoji  AS term_icon,
			        pt.icon_emoji AS parent_icon
			 FROM `{$ct}` ct
			 JOIN `{$tax}` t  ON t.id = ct.taxonomy_id
			 LEFT JOIN `{$tax}` pt ON pt.id = t.parent_id
			 WHERE ct.post_id IN ({$id_in})
			 ORDER BY ct.post_id ASC, t.sort_order ASC"
		) ?: array();

		// Keep only first row per post_id (lowest sort_order = primary term).
		$icon_by_pid = array();
		foreach ( $rows as $row ) {
			$pid = (int) $row->post_id;
			if ( ! isset( $icon_by_pid[ $pid ] ) ) {
				$icon_by_pid[ $pid ] = ! empty( $row->term_icon )
					? (string) $row->term_icon
					: ( ! empty( $row->parent_icon ) ? (string) $row->parent_icon : '' );
			}
		}
		return $icon_by_pid;
	}
}
