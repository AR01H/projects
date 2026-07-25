<?php

namespace Adn\Theme\Feature\Home\Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Delegates to \Adn\Theme\Service\HomeContext.
 * Canonical entry point for home page data.
 */
class HomeController {

	public static function getContext( $skip = array() ): array {
		return \Adn\Theme\Service\HomeContext::getContext( $skip );
	}

	public static function getFragmentContext( $section ): array {
		return \Adn\Theme\Service\HomeContext::getFragmentContext( $section );
	}

	public static function sectionVisible( $key ): bool {
		return \Adn\Theme\Service\HomeContext::sectionVisible( $key );
	}
}
