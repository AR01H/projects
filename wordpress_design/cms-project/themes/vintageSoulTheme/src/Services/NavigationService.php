<?php
namespace VintageSoul\Services;

use VintageSoul\DataProviders\JsonFileProvider;

defined( 'ABSPATH' ) || exit;

final class NavigationService {

	public static function menu( string $location ): array {
		$locations = get_nav_menu_locations();
		if ( ! empty( $locations[ $location ] ) ) {
			$items = wp_get_nav_menu_items( $locations[ $location ] );
			if ( $items ) {
				return array_map(
					static fn( $item ) => array( 'label' => $item->title, 'url' => $item->url ),
					$items
				);
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
