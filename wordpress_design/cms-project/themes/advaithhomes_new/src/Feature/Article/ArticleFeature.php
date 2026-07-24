<?php

namespace Adn\Theme\Feature\Article;

defined( 'ABSPATH' ) || exit;

class ArticleFeature {
	public static function register(): void {
		\add_action( 'after_setup_theme', [ self::class, 'setup' ] );
	}
	public static function setup(): void {}
}
