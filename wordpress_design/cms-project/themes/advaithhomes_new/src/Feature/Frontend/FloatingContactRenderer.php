<?php
namespace Adn\Theme\Feature\Frontend;

defined( 'ABSPATH' ) || exit;

class FloatingContactRenderer {

	public static function render(): void {
		if ( ! empty( $_GET['content'] ) && 'true' === (string) $_GET['content'] ) {
			return;
		}
		if ( function_exists( 'adn_component' ) ) {
			adn_component( 'parts/floating_contact' );
		}
	}
}
