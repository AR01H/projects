<?php

namespace Adn\Theme\Feature\Tools;

defined( 'ABSPATH' ) || exit;

class ToolsFeature {
	public static function register(): void {
		add_action( 'after_setup_theme', [ self::class, 'setup' ] );
	}
	public static function setup(): void {}
}
