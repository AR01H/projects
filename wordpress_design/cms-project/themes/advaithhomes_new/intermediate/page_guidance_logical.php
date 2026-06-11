<?php
defined( 'ABSPATH' ) || exit;

function adn_guidance_get_context() {
	$data   = function_exists( 'adn_service_guidance_data' ) ? adn_service_guidance_data() : array();
	$chrome = function_exists( 'adn_service_site_chrome' )   ? adn_service_site_chrome()   : array();
	return array(
		'meta'        => isset( $data['meta'] )        ? (array) $data['meta']        : array(),
		'breadcrumb'  => isset( $data['breadcrumb'] )  ? (array) $data['breadcrumb']  : array(),
		'hero'        => isset( $data['hero'] )        ? (array) $data['hero']        : array(),
		'form'        => isset( $data['form'] )        ? (array) $data['form']        : array(),
		'services'    => isset( $data['services'] )    ? (array) $data['services']    : array(),
		'why_choose'  => isset( $data['why_choose'] )  ? (array) $data['why_choose']  : array(),
		'chrome'      => $chrome,
	);
}
