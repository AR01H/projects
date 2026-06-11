<?php
defined( 'ABSPATH' ) || exit;

function adn_ask_expert_get_context() {
	$data   = function_exists( 'adn_service_ask_expert_data' ) ? adn_service_ask_expert_data() : array();
	$chrome = function_exists( 'adn_service_site_chrome' )     ? adn_service_site_chrome()     : array();
	return array(
		'meta'          => isset( $data['meta'] )          ? (array) $data['meta']          : array(),
		'breadcrumb'    => isset( $data['breadcrumb'] )    ? (array) $data['breadcrumb']    : array(),
		'hero'          => isset( $data['hero'] )          ? (array) $data['hero']          : array(),
		'stats'         => isset( $data['stats'] )         ? (array) $data['stats']         : array(),
		'categories'    => isset( $data['categories'] )    ? (array) $data['categories']    : array(),
		'experts'       => isset( $data['experts'] )       ? (array) $data['experts']       : array(),
		'sidebar'       => isset( $data['sidebar'] )       ? (array) $data['sidebar']       : array(),
		'cant_find_cta' => isset( $data['cant_find_cta'] ) ? (array) $data['cant_find_cta'] : array(),
		'chrome'        => $chrome,
	);
}
