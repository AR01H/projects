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

	$ctx = array(
		'slug'       => $slug,
		'meta'       => isset( $data['meta'] )       ? (array) $data['meta']       : array(),
		'breadcrumb' => isset( $data['breadcrumb'] ) ? (array) $data['breadcrumb'] : array(),
		'hero'       => isset( $data['hero'] )       ? (array) $data['hero']       : array(),
		'sidebar'    => isset( $data['sidebar'] )    ? (array) $data['sidebar']    : array(),
		'guides'     => isset( $data['guides'] )     ? (array) $data['guides']     : array(),
		'chrome'     => $chrome,
	);

	// Fetch the guide cards from the CMS: articles under the matching Guide
	// parent (slug "buying-guides" → parent "buying"). Keeps toolbar / sort /
	// download CTA from JSON; only the card list comes from the DB.
	if ( function_exists( 'adn_cms_available' ) && adn_cms_available() ) {
		$parent_slug = preg_replace( '/-guides$/', '', $slug );
		$articles    = adn_cms_articles_for_parent( $parent_slug, 12 );
		if ( ! empty( $articles ) ) {
			$guides              = is_array( $ctx['guides'] ) ? $ctx['guides'] : array();
			$guides['items']     = adn_guides_listing_cms_items( $articles );
			$guides['pagination'] = array( 'current' => 1, 'total' => 1 );
			$ctx['guides']       = $guides;
		}
	}

	return $ctx;
}

/**
 * Map CMS articles → guide_listing_card props
 * { img_class, icon, category, title, desc, date, read_time, url }.
 */
function adn_guides_listing_cms_items( $articles ) {
	$img_classes = array( 'guide-img-green', 'guide-img-blue', 'guide-img-amber', 'guide-img-purple', 'guide-img-teal' );
	$items       = array();
	foreach ( $articles as $i => $post ) {
		$title = isset( $post->title ) ? $post->title : '';
		if ( '' === $title ) {
			continue;
		}
		$items[] = array(
			'img_class' => $img_classes[ $i % count( $img_classes ) ],
			'icon'      => '📄',
			'category'  => ! empty( $post->category_name ) ? $post->category_name : 'Guide',
			'title'     => $title,
			'desc'      => isset( $post->excerpt ) ? (string) $post->excerpt : '',
			'date'      => adn_cms_post_date( $post ),
			'read_time' => adn_cms_read_time( isset( $post->content ) ? $post->content : '' ),
			'url'       => adn_cms_post_url( $post ),
		);
	}
	return $items;
}
