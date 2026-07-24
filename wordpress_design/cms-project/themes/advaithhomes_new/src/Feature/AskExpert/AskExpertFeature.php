<?php

namespace Adn\Theme\Feature\AskExpert;

defined( 'ABSPATH' ) || exit;

class AskExpertFeature {
	public static function register(): void {
		\add_action( 'after_setup_theme', [ self::class, 'setup' ] );
	}
	public static function setup(): void {}
}
