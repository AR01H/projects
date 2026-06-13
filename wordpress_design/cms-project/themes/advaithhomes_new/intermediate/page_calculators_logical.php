<?php
/**
 * intermediate/page_calculators_logical.php
 *
 * Intermediate logic for the calculators listing page.
 * Loads data/json/calculators.json via the service layer.
 *
 * RULE: No markup here - only data shaping.
 * RULE: Caller is pages/page-calculator.php.
 */

defined( 'ABSPATH' ) || exit;

function adn_calculators_get_context() {
	$data   = function_exists( 'adn_service_calculators_data' ) ? adn_service_calculators_data()  : array();
	$chrome = function_exists( 'adn_service_site_chrome' )      ? adn_service_site_chrome()        : array();

	return array(
		'meta'          => isset( $data['meta'] )          ? (array) $data['meta']          : array(),
		'breadcrumb'    => isset( $data['breadcrumb'] )    ? (array) $data['breadcrumb']    : array(),
		'hero'          => isset( $data['hero'] )          ? (array) $data['hero']          : array(),
		'trust_items'   => isset( $data['trust_items'] )   ? (array) $data['trust_items']   : array(),
		'search'        => isset( $data['search'] )        ? (array) $data['search']        : array(),
		'sidebar'       => isset( $data['sidebar'] )       ? (array) $data['sidebar']       : array(),
		'filter_tabs'   => isset( $data['filter_tabs'] )   ? (array) $data['filter_tabs']   : array(),
		'popular_calcs' => isset( $data['popular_calcs'] ) ? (array) $data['popular_calcs'] : array(),
		'all_calcs'     => isset( $data['all_calcs'] )     ? (array) $data['all_calcs']     : array(),
		'find_cta'      => isset( $data['find_cta'] )      ? (array) $data['find_cta']      : array(),
		'chrome'        => $chrome,
	);
}
