<?php

namespace Adn\Theme\Feature\News\Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Delegates to \Adn\Theme\Service\NewsContext.
 * Canonical entry point for news listing page data.
 */
class NewsController {

	public static function getContext(): array {
		return \Adn\Theme\Service\NewsContext::getContext();
	}
}
