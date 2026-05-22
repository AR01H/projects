<?php
defined( 'ABSPATH' ) || exit;

/**
 * Theme helper for fetching content taxonomy terms from the plugin's
 * wp_ah_content_taxonomies + wp_ah_taxonomies tables.
 *
 * Requires the CMS plugin to be active (uses AH_DB_Helper for table names).
 * All methods return empty arrays gracefully when the plugin is absent.
 *
 * Usage:
 *   $result = AH_Theme_Content_Taxonomy::get_terms_for( $item_ids, 'news_bar_item' );
 *   $item_terms   = $result['item_terms'];   // [ object_id => [ stdClass, … ] ]
 *   $unique_terms = $result['unique_terms']; // [ slug => stdClass ]
 */
class AH_Theme_Content_Taxonomy {

	/**
	 * Fetch taxonomy terms for a set of object IDs of a given type.
	 *
	 * @param int[]  $ids         Object IDs (e.g. news bar item IDs).
	 * @param string $object_type Object type string stored in content_taxonomies
	 *                            (e.g. 'news_bar_item', 'service', 'post').
	 * @return array{
	 *   item_terms:   array<int, stdClass[]>,
	 *   unique_terms: array<string, stdClass>
	 * }
	 */
	public static function get_terms_for( array $ids, string $object_type ): array {
		$empty = [ 'item_terms' => [], 'unique_terms' => [] ];

		if ( empty( $ids ) || ! class_exists( 'AH_DB_Helper' ) ) {
			return $empty;
		}

		global $wpdb;

		$safe_ids = implode( ',', array_map( 'intval', $ids ) );
		$ct       = AH_DB_Helper::table( 'content_taxonomies' );
		$tax      = AH_DB_Helper::table( 'taxonomies' );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ct.object_id, t.id, t.name, t.slug
				 FROM `{$ct}` ct
				 INNER JOIN `{$tax}` t ON t.id = ct.taxonomy_id
				 WHERE ct.object_type = %s AND ct.object_id IN ({$safe_ids})
				 ORDER BY t.name ASC",
				$object_type
			)
		) ?: [];

		$item_terms   = [];
		$unique_terms = [];
		foreach ( $rows as $r ) {
			$item_terms[ (int) $r->object_id ][] = $r;
			$unique_terms[ $r->slug ]             = $r;
		}

		return compact( 'item_terms', 'unique_terms' );
	}

	/**
	 * Convenience wrapper - extracts IDs from an array of objects then calls get_terms_for().
	 *
	 * @param object[] $items       Objects that each have an ->id property.
	 * @param string   $object_type Object type string.
	 */
	public static function get_terms_for_items( array $items, string $object_type ): array {
		$ids = array_map( fn( $i ) => (int) $i->id, $items );
		return self::get_terms_for( $ids, $object_type );
	}

	/**
	 * Returns only the unique terms (slug-keyed) for a set of items.
	 *
	 * Useful for filter tabs / tag clouds.
	 */
	public static function get_unique_terms( array $items, string $object_type ): array {
		return self::get_terms_for_items( $items, $object_type )['unique_terms'];
	}
}
