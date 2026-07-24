<?php

namespace Adn\Theme\Feature\AskExpert\Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Delegates to intermediate/page_expert_single_logical.php functions.
 * Canonical entry point for single expert profile page data.
 */
class ExpertSingleController {

	public static function getContext( string $slug ): ?array {
		return \Adn\Theme\Service\ExpertSingleContext::getContext( $slug );
	}
}
