<?php
/**
 * intermediate/page_guides_listing_logical.php
 *
 * Intermediate logic for guides listing pages (e.g. /buying-guides/).
 * The page slug drives which JSON file is loaded so any category's
 * guides listing can reuse this same function.
 *
 * RULE: No markup here — only data shaping.
 * RULE: Caller is pages/page-guides_listing.php.
 */

defined( 'ABSPATH' ) || exit;

function adn_guides_listing_get_context( $slug = '' ) {
	if ( '' === $slug ) {
		$page = get_queried_object();
		$slug = ( $page instanceof WP_Post ) ? (string) $page->post_name : '';
	}
	$slug   = sanitize_key( (string) $slug );
	$data   = function_exists( 'adn_service_guides_listing_data' ) ? adn_service_guides_listing_data( $slug ) : array();
	$chrome = function_exists( 'adn_service_site_chrome' )          ? adn_service_site_chrome()               : array();

	return array(
		'slug'       => $slug,
		'meta'       => isset( $data['meta'] )       ? (array) $data['meta']       : array(),
		'breadcrumb' => isset( $data['breadcrumb'] ) ? (array) $data['breadcrumb'] : array(),
		'hero'       => isset( $data['hero'] )       ? (array) $data['hero']       : array(),
		'sidebar'    => isset( $data['sidebar'] )    ? (array) $data['sidebar']    : array(),
		'guides'     => isset( $data['guides'] )     ? (array) $data['guides']     : array(),
		'chrome'     => $chrome,
	);
}
