<?php

namespace Adn\Theme\Feature\FAQs;

defined( 'ABSPATH' ) || exit;

class FaqsFeature {
	public static function register(): void {
		\add_action( 'after_setup_theme', [ self::class, 'setup' ] );
	}
	public static function setup(): void {}
}
