<?php

namespace Adn\Theme\Feature\GuidesListing\Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Delegates to intermediate/page_guides_logical.php functions.
 * Canonical entry point for the /guides/ hub page data.
 */
class GuidesHubController {

	public static function getContext(): array {
		return \Adn\Theme\Service\GuidesContext::getContext();
	}
}
