<?php
/**
 * Floating Contact Widget — Thin wrapper delegating to OOP class.
 *
 * @package Adn\Theme\Common\Frontend
 */
defined( 'ABSPATH' ) || exit;

function adn_render_floating_contact(): void {
	\Adn\Theme\Feature\Frontend\FloatingContactRenderer::render();
}
