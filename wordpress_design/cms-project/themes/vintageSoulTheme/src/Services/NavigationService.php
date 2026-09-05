<?php
namespace VintageSoul\Services;

use VintageSoul\DataProviders\JsonFileProvider;

defined( 'ABSPATH' ) || exit;

final class NavigationService {

	public static function menu( string $location ): array {
		$locations = get_nav_menu_locations();
		if ( ! empty( $locations[ $location ] ) ) {
			$items = wp_get_nav_menu_items( $locations[ $location ] );
			if ( ! empty( $items ) && is_array( $items ) ) {
				$parents  = array();
				$children = array();
				foreach ( $items as $item ) {
					$entry = array(
						'id'       => (int) $item->ID,
						'label'    => (string) $item->title,
						'url'      => (string) $item->url,
						'children' => array(),
					);
					if ( ! empty( $item->menu_item_parent ) ) {
						$children[ (int) $item->menu_item_parent ][] = $entry;
					} else {
						$parents[ (int) $item->ID ] = $entry;
					}
				}
				foreach ( $children as $parent_id => $kids ) {
					if ( isset( $parents[ $parent_id ] ) ) {
						$parents[ $parent_id ]['children'] = $kids;
					}
				}
				return array_values( $parents );
			}
		}

		$fallback = JsonFileProvider::read( 'config/navigation.json' );
		return (array) ( $fallback[ $location ] ?? array() );
	}

	public static function header_cta(): array {
		$fallback = JsonFileProvider::read( 'config/navigation.json' );
		$cta      = (array) ( $fallback['header_cta'] ?? array() );

		return array(
			'label'    => (string) ( $cta['label'] ?? '' ),
			'sublabel' => (string) ( $cta['sublabel'] ?? '' ),
			'route'    => (string) ( $cta['route'] ?? '' ),
		);
	}
}
