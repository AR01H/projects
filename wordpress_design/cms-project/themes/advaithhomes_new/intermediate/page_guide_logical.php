<?php
/**
 * intermediate/page_guide_logical.php
 *
 * Intermediate logic for individual guide/article pages.
 * Reads the current page slug, loads data/json/guide-{slug}.json via the
 * service layer, applies defaults and returns a single $ctx array.
 *
 * RULE: No markup here - only data shaping.
 * RULE: Caller is pages/page-topic_category_guide.php.
 *
 * Usage:
 *   require_once ADN_THEME_DIR . '/intermediate/page_guide_logical.php';
 *   $ctx = adn_guide_get_context();          // reads slug from queried object
 *   $ctx = adn_guide_get_context( 'buying-step-by-step' ); // explicit (tests/REST)
 */

defined( 'ABSPATH' ) || exit;

function adn_guide_get_context( $slug = '' ) {

	// ── 1. Resolve slug ──────────────────────────────────────────────
	if ( '' === $slug ) {
		$page = get_queried_object();
		$slug = ( $page instanceof WP_Post ) ? (string) $page->post_name : '';
	}
	$slug = sanitize_key( $slug );

	// ── 2. Load data via service layer ───────────────────────────────
	$data   = function_exists( 'adn_service_guide_data' ) ? adn_service_guide_data( $slug ) : array();
	$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome()      : array();

	// ── 3. Shape context with safe defaults ──────────────────────────
	return array(
		'slug'          => $slug,
		'meta'          => isset( $data['meta'] )          ? (array) $data['meta']          : array(),
		'breadcrumb'    => isset( $data['breadcrumb'] )    ? (array) $data['breadcrumb']    : array(),
		'article'       => isset( $data['article'] )       ? (array) $data['article']       : array(),
		'key_takeaways' => isset( $data['key_takeaways'] ) ? (array) $data['key_takeaways'] : array(),
		'toc'           => isset( $data['toc'] )           ? (array) $data['toc']           : array(),
		'sections'      => isset( $data['sections'] )      ? (array) $data['sections']      : array(),
		'feedback'      => isset( $data['feedback'] )      ? (array) $data['feedback']      : array(),
		'author'        => isset( $data['author'] )        ? (array) $data['author']        : array(),
		'sidebar'       => isset( $data['sidebar'] )       ? (array) $data['sidebar']       : array(),
		'stay_informed' => isset( $data['stay_informed'] ) ? (array) $data['stay_informed'] : array(),
		'chrome'        => $chrome,
	);
}
