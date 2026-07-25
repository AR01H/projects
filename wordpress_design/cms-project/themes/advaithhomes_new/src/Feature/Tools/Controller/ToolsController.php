<?php

namespace Adn\Theme\Feature\Tools\Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Delegates to \Adn\Theme\Service\ToolsContext.
 * Canonical entry point for tools/calculators listing page data.
 */
class ToolsController {

	public static function getContext(): array {
		return \Adn\Theme\Service\ToolsContext::getContext();
	}
}
