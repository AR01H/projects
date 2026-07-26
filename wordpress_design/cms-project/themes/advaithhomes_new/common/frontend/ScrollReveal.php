<?php
/**
 * Scroll Reveal Animation — Thin wrappers delegating to OOP class.
 *
 * @package Adn\Theme\Common\Frontend
 */
defined( 'ABSPATH' ) || exit;

function adn_reveal_gate(): void {
	\Adn\Theme\Feature\Frontend\ScrollRevealHandler::gate();
}

function adn_reveal_runtime(): void {
	\Adn\Theme\Feature\Frontend\ScrollRevealHandler::runtime();
}
