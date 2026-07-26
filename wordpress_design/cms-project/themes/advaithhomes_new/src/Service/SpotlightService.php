<?php
/**
 * SpotlightService - Data layer for spotlight terms and items.
 *
 * Fetches from DB and shapes data for the spotlights_widget component.
 */
namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

class SpotlightService {

	/**
	 * Get a spotlight term by slug.
	 *
	 * @return object|null DB row with id, name, slug, max_display, etc.
	 */
	public static function getTerm( string $slug ): ?object {
		if ( '' === $slug ) { return null; }
		global $wpdb;
		$tbl = $wpdb->prefix . 'ah_spotlight_terms';
		$row = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM `{$tbl}` WHERE slug = %s AND is_active = 1 LIMIT 1",
			$slug
		) );
		return $row ?: null;
	}

	/**
	 * Get active items for a spotlight term.
	 *
	 * @return array<int, object>
	 */
	public static function getItems( int $term_id, int $limit = 0 ): array {
		if ( $term_id <= 0 ) { return array(); }
		global $wpdb;
		$tbl  = $wpdb->prefix . 'ah_spotlights';
		$lim  = $limit > 0 ? $limit : 10;
		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM `{$tbl}` WHERE term_id = %d AND is_active = 1 ORDER BY sort_order ASC, id ASC LIMIT %d",
			$term_id,
			$lim
		) );
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Build props array for spotlights_widget component.
	 *
	 * Returns null if the term doesn't exist or has no items.
	 *
	 * @return array{heading: string, items: array, slug: string}|null
	 */
	public static function buildProps( string $slug, int $max_items = 0, string $override_title = '' ): ?array {
		$term = self::getTerm( $slug );
		if ( ! $term ) { return null; }

		$limit = $max_items > 0 ? $max_items : (int) $term->max_display;
		$rows  = self::getItems( (int) $term->id, $limit );
		if ( empty( $rows ) ) { return null; }

		$heading = '' !== $override_title ? $override_title : (string) $term->name;

		$items = array();
		foreach ( $rows as $row ) {
			$icon     = trim( (string) ( $row->icon ?? '' ) );
			$val      = trim( (string) ( $row->point_value ?? '' ) );
			$lbl      = trim( (string) ( $row->point_label ?? '' ) );
			$has_link = ! empty( $row->show_link ) && ! empty( $row->link_url );
			$url      = $has_link ? \adn_link( (string) $row->link_url ) : '';

			$items[] = array(
				'icon'        => '' !== $icon ? $icon : mb_strtoupper( mb_substr( (string) $row->title, 0, 1 ) ),
				'title'       => (string) $row->title,
				'value'       => $val,
				'label'       => $lbl,
				'meta'        => '' !== $val && '' !== $lbl ? $val . ' ' . $lbl : ( '' !== $val ? $val : $lbl ),
				'description' => ! empty( $row->description ) ? (string) $row->description : '',
				'link_label'  => ! empty( $row->link_label ) ? (string) $row->link_label : '',
				'url'         => $url,
				'has_link'    => $has_link,
			);
		}

		return array(
			'heading' => $heading,
			'items'   => $items,
			'slug'    => $slug,
		);
	}

	/**
	 * Build props for multiple terms (category pages).
	 *
	 * @param string[] $slugs
	 * @return array<int, array{heading: string, items: array, slug: string}>
	 */
	public static function buildMultipleProps( array $slugs, int $max_items = 0 ): array {
		$all = array();
		foreach ( $slugs as $slug ) {
			$props = self::buildProps( $slug, $max_items );
			if ( null !== $props ) {
				$all[] = $props;
			}
		}
		return $all;
	}
}
