<?php

namespace Adn\Theme\Feature\Home;

defined( 'ABSPATH' ) || exit;

class HomeFeature {
	public static function register(): void {
		add_action( 'after_setup_theme', [ self::class, 'setup' ] );
	}

	public static function setup(): void {
		// Home page specific setup
	}
}
