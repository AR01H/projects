<?php
/**
 * intermediate/page_news_logical.php
 *
 * Intermediate logic for the news listing page.
 * Loads data/json/news.json via the service layer, applies defaults
 * and returns a single $ctx array.
 *
 * RULE: No markup here — only data shaping.
 * RULE: Caller is pages/page-newsall.php.
 */

defined( 'ABSPATH' ) || exit;

function adn_news_get_context() {
	$data   = function_exists( 'adn_service_news_data' )   ? adn_service_news_data()    : array();
	$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome()  : array();

	return array(
		'meta'              => isset( $data['meta'] )              ? (array) $data['meta']              : array(),
		'breadcrumb'        => isset( $data['breadcrumb'] )        ? (array) $data['breadcrumb']        : array(),
		'hero'              => isset( $data['hero'] )              ? (array) $data['hero']              : array(),
		'categories'        => isset( $data['categories'] )        ? (array) $data['categories']        : array(),
		'featured'          => isset( $data['featured'] )          ? (array) $data['featured']          : array(),
		'sections'          => isset( $data['sections'] )          ? (array) $data['sections']          : array(),
		'sidebar'           => isset( $data['sidebar'] )           ? (array) $data['sidebar']           : array(),
		'bottom_newsletter' => isset( $data['bottom_newsletter'] ) ? (array) $data['bottom_newsletter'] : array(),
		'chrome'            => $chrome,
	);
}
