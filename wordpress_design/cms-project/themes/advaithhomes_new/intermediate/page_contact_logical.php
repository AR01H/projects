<?php
defined( 'ABSPATH' ) || exit;

function adn_contact_get_context() {
	$data   = function_exists( 'adn_service_contact_data' )   ? adn_service_contact_data()   : array();
	$chrome = function_exists( 'adn_service_site_chrome' )    ? adn_service_site_chrome()     : array();
	return array(
		'meta'            => isset( $data['meta'] )            ? (array) $data['meta']            : array(),
		'breadcrumb'      => isset( $data['breadcrumb'] )      ? (array) $data['breadcrumb']      : array(),
		'hero'            => isset( $data['hero'] )            ? (array) $data['hero']            : array(),
		'form'            => isset( $data['form'] )            ? (array) $data['form']            : array(),
		'contact_sidebar' => isset( $data['contact_sidebar'] ) ? (array) $data['contact_sidebar'] : array(),
		'process_steps'   => isset( $data['process_steps'] )   ? (array) $data['process_steps']   : array(),
		'resources'       => isset( $data['resources'] )       ? (array) $data['resources']       : array(),
		'chrome'          => $chrome,
	);
}
