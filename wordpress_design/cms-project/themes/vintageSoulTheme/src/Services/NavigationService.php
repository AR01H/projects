<?php
namespace VintageSoul\Services;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\Plugins\NavigationBridgeService;

defined( 'ABSPATH' ) || exit;

final class NavigationService {

	/**
	 * Get navigation items by location ('primary', 'footer', etc.)
	 *
	 * Dynamically integrates with CMS Plugin Navigation Editor (`admin.php?page=ah-navigation`)
	 * with automatic fallback to WordPress menus and `config/navigation.json`.
	 *
	 * @param string $location
	 * @return array<int, array<string, mixed>>
	 */
	public static function menu( string $location ): array {
		if ( 'primary' === $location ) {
			return NavigationBridgeService::get_primary_navigation();
		}

		$fallback = JsonFileProvider::read( 'config/navigation.json' );
		return (array) ( $fallback[ $location ] ?? array() );
	}

	/**
	 * Get persistent header CTA button data from CMS Plugin or fallback
	 *
	 * @return array<string, string>
	 */
	public static function header_cta(): array {
		return NavigationBridgeService::get_header_cta();
	}

	/**
	 * Get footer navigation data from CMS Plugin or fallback
	 *
	 * @return array<string, mixed>
	 */
	public static function footer(): array {
		return NavigationBridgeService::get_footer_data();
	}
}

