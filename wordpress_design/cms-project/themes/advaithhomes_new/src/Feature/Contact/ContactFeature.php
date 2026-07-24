<?php

namespace Adn\Theme\Feature\Contact;

defined( 'ABSPATH' ) || exit;

class ContactFeature {
	public static function register(): void {
		\add_action( 'after_setup_theme', [ self::class, 'setup' ] );
	}
	public static function setup(): void {}
}
