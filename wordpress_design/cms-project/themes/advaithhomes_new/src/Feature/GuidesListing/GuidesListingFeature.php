<?php

namespace Adn\Theme\Feature\GuidesListing;

defined( 'ABSPATH' ) || exit;

class GuidesListingFeature {
	public static function register(): void {
		\add_action( 'after_setup_theme', [ self::class, 'setup' ] );
	}
	public static function setup(): void {}
}
