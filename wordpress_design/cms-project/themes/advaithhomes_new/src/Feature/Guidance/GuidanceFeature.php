<?php

namespace Adn\Theme\Feature\Guidance;

defined( 'ABSPATH' ) || exit;

class GuidanceFeature {
	public static function register(): void {
		\add_action( 'after_setup_theme', [ self::class, 'setup' ] );
	}
	public static function setup(): void {}
}
