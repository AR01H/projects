<?php

namespace Adn\Theme\Feature\ComingSoon;

defined( 'ABSPATH' ) || exit;

class ComingSoonFeature {
	public static function register(): void {
		\add_action( 'after_setup_theme', [ self::class, 'setup' ] );
	}
	public static function setup(): void {}
}
