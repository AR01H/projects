<?php

namespace Adn\Theme\Feature\News;

defined( 'ABSPATH' ) || exit;

class NewsFeature {
	public static function register(): void {
		add_action( 'after_setup_theme', [ self::class, 'setup' ] );
	}
	public static function setup(): void {}
}
