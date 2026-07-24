<?php

namespace Adn\Theme\Feature\CategoryGuide;

defined( 'ABSPATH' ) || exit;

class CategoryGuideFeature {
	public static function register(): void {
		\add_action( 'after_setup_theme', [ self::class, 'setup' ] );
	}
	public static function setup(): void {}
}
