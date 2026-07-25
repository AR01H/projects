<?php

namespace Adn\Theme\Feature\AskExpert\Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Delegates to \Adn\Theme\Service\AskExpertContext.
 * Canonical entry point for ask-expert page data.
 */
class AskExpertController {

	public static function getContext(): array {
		return \Adn\Theme\Service\AskExpertContext::getContext();
	}
}
