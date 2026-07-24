<?php
/**
 * Floating Contact Widget
 *
 * Renders the floating WhatsApp + Call buttons (numbers from ah_site_settings DB).
 *
 * @package Adn\Theme\Common\Frontend
 */
defined( 'ABSPATH' ) || exit;

function adn_render_floating_contact(): void {
	if ( ! empty( $_GET['content'] ) && 'true' === (string) $_GET['content'] ) {
		return;
	}
	if ( function_exists( 'adn_component' ) ) {
		adn_component( 'parts/floating_contact' );
	}
}
