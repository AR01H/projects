<?php
/**
 * intermediate/page_home_logical.php — Home page container logic.
 *
 * RULE: This layer fetches via the services (apis/services.php), applies
 *       defaults so the template never crashes on missing keys, and hands
 *       page-home.php one ready-to-render $ctx array. No markup here;
 *       no direct data-source reads in the template.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build the full render context for the home page.
 *
 * @return array {
 *     chrome      : logo / nav / header_cta / footer (site-wide)
 *     hero        : title_lines, description, actions, trust_items, diagram
 *     journey     : heading + cards
 *     news        : heading + items
 *     regulations : heading + items
 *     hot_topics  : title, items, cta
 *     calculators : heading + items
 *     guides      : heading + items
 *     newsletter  : icon, title, description, placeholder, button_label, note
 * }
 */
function adn_home_get_context() {
	$data   = adn_service_home_data();
	$chrome = adn_service_site_chrome();

	$section = static function ( $key, $defaults = array() ) use ( $data ) {
		$value = isset( $data[ $key ] ) && is_array( $data[ $key ] ) ? $data[ $key ] : array();
		return array_merge( $defaults, $value );
	};

	return array(
		'chrome'      => is_array( $chrome ) ? $chrome : array(),
		'hero'        => $section( 'hero', array( 'title_lines' => array(), 'actions' => array(), 'trust_items' => array(), 'diagram' => array() ) ),
		'journey'     => $section( 'journey', array( 'heading' => array(), 'cards' => array() ) ),
		'news'        => $section( 'news', array( 'heading' => array(), 'items' => array() ) ),
		'regulations' => $section( 'regulations', array( 'heading' => array(), 'items' => array() ) ),
		'hot_topics'  => $section( 'hot_topics', array( 'title' => '', 'items' => array(), 'cta' => array() ) ),
		'calculators' => $section( 'calculators', array( 'heading' => array(), 'items' => array() ) ),
		'guides'      => $section( 'guides', array( 'heading' => array(), 'items' => array() ) ),
		'newsletter'  => $section( 'newsletter' ),
	);
}
