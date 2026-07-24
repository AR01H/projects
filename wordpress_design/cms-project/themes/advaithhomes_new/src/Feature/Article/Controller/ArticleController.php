<?php

namespace Adn\Theme\Feature\Article\Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Delegates to intermediate/post_logical.php functions.
 * Canonical entry point for single post/article page data.
 */
class ArticleController {

	public static function getContext(): array {
		return \Adn\Theme\Service\PostContext::getContext();
	}
}
