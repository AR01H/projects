<?php

namespace Adn\Theme\Feature\GuidesListing\Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Delegates to intermediate/page_guides_listing_logical.php functions.
 * Canonical entry point for guides listing page data.
 */
class GuidesListingController {

	public static function getContext(): array {
		return \Adn\Theme\Service\GuidesListingContext::getContext();
	}
}
