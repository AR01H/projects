<?php
/**
 * intermediate/page_category_logical.php
 *
 * Intermediate logic for all category guide pages (buying, selling, house-movers…).
 * Reads the current page slug, loads the matching JSON via the service layer,
 * applies defaults and returns a single $ctx array.
 *
 * RULE: No markup here — only data shaping.
 * RULE: Caller is pages/page-category_guide.php (and eventually the plugin REST handler).
 *
 * Usage:
 *   require_once ADN_THEME_DIR . '/intermediate/page_category_logical.php';
 *   $ctx = adn_category_get_context();   // reads slug from the current queried object
 *   // OR:
 *   $ctx = adn_category_get_context( 'buying' ); // explicit slug (useful in tests / REST)
 */

defined( 'ABSPATH' ) || exit;

function adn_category_get_context( $slug = '' ) {

	// ── 1. Resolve slug ──────────────────────────────────────────────
	if ( '' === $slug ) {
		$page = get_queried_object();
		$slug = ( $page instanceof WP_Post ) ? (string) $page->post_name : '';
	}
	$slug = sanitize_key( $slug );

	// ── 2. Load data via service layer ───────────────────────────────
	$data   = function_exists( 'adn_service_category_data' ) ? adn_service_category_data( $slug ) : array();
	$chrome = function_exists( 'adn_service_site_chrome' )   ? adn_service_site_chrome()           : array();

	// ── 3. Shape context with safe defaults ──────────────────────────
	return array(
		'slug'        => $slug,
		'meta'        => isset( $data['meta'] )        ? (array) $data['meta']        : array(),
		'breadcrumb'  => isset( $data['breadcrumb'] )  ? (array) $data['breadcrumb']  : array(),
		'hero'        => isset( $data['hero'] )        ? (array) $data['hero']        : array(),
		'journey'     => isset( $data['journey'] )     ? (array) $data['journey']     : array(),
		'guides'      => isset( $data['guides'] )      ? (array) $data['guides']      : array(),
		'news'        => isset( $data['news'] )        ? (array) $data['news']        : array(),
		'regulations' => isset( $data['regulations'] ) ? (array) $data['regulations'] : array(),
		'calculators' => isset( $data['calculators'] ) ? (array) $data['calculators'] : array(),
		'sidebar'     => isset( $data['sidebar'] )     ? (array) $data['sidebar']     : array(),
		'cta_banner'  => isset( $data['cta_banner'] )  ? (array) $data['cta_banner']  : array(),
		'chrome'      => $chrome,
	);
}
