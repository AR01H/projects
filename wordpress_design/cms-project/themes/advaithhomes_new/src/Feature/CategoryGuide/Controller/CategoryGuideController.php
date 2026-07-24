<?php

namespace Adn\Theme\Feature\CategoryGuide\Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Delegates to intermediate/page_category_logical.php functions.
 * Canonical entry point for category guide page data.
 */
class CategoryGuideController {

	public static function getContext( $slug = '' ): array {
		return \Adn\Theme\Service\CategoryContext::getContext( $slug );
	}

	public static function getTopicContext(): array {
		return \Adn\Theme\Service\TopicCategoryContext::getContext();
	}

	public static function getGuideContext( $slug = '' ): array {
		return \Adn\Theme\Service\GuideContext::getContext( $slug );
	}
}
