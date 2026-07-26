<?php
/**
 * Theme Shortcodes — Thin wrappers delegating to OOP class.
 *
 * @package Adn\Theme\Common\Shortcodes
 */
defined( 'ABSPATH' ) || exit;

function adn_get_parent_term_calculator_cards( $parent_slug, $limit = 0 ) {
	return \Adn\Theme\Feature\Shortcodes\ThemeShortcodes::getParentTermCalculatorCards( $parent_slug, $limit );
}

function adn_shortcode_cat_calculators( $atts ) {
	return \Adn\Theme\Feature\Shortcodes\ThemeShortcodes::catCalculators( $atts );
}

function adn_shortcode_cookie_preferences( $atts ) {
	return \Adn\Theme\Feature\Shortcodes\ThemeShortcodes::cookiePreferences( $atts );
}
