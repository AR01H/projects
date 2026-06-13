<?php
/**
 * intermediate/page_guides_listing_logical.php
 *
 * Intermediate logic for guides listing pages (e.g. /buying-guides/).
 * The page slug drives which JSON file is loaded so any category's
 * guides listing can reuse this same function.
 *
 * RULE: No markup here - only data shaping.
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

	if ( function_exists( 'adn_cms_available' ) && adn_cms_available() ) {

		// Sidebar browse_cats: replace JSON list with live parent terms so new
		// taxonomy categories appear automatically without a JSON edit.
		$parents = adn_cms_guide_parents( 50 );
		if ( ! empty( $parents ) ) {
			$browse_cats = array();
			foreach ( $parents as $pt ) {
				$browse_cats[] = array(
					'label'  => (string) $pt->name,
					'slug'   => (string) $pt->slug,
					'active' => false,
				);
			}
			$sidebar                = is_array( $ctx['sidebar'] ) ? $ctx['sidebar'] : array();
			$sidebar['browse_cats'] = $browse_cats;
			$ctx['sidebar']         = $sidebar;
		}

		// Guide card items: derive parent slug from page slug ("buying-guides" →
		// "buying"). On the hub page (slug = "guides" or no match), fall back to
		// one article per parent so every category is represented.
		$parent_slug = preg_replace( '/-guides?$/', '', $slug );
		$articles    = ( '' !== $parent_slug && $parent_slug !== $slug )
			? adn_cms_articles_for_parent( $parent_slug, 12 )
			: array();

		if ( empty( $articles ) ) {
			// Hub page or unmatched slug: one featured article per parent term.
			$articles = adn_cms_one_article_per_parent();
		}

		if ( ! empty( $articles ) ) {
			$guides               = is_array( $ctx['guides'] ) ? $ctx['guides'] : array();
			$guides['items']      = adn_guides_listing_cms_items( $articles );
			$guides['pagination'] = array( 'current' => 1, 'total' => 1 );
			$ctx['guides']        = $guides;
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
		// Use parent icon emoji (set by adn_cms_one_article_per_parent) when available.
		$icon = ! empty( $post->_parent_icon ) ? $post->_parent_icon : '📄';
		$items[] = array(
			'img_class' => $img_classes[ $i % count( $img_classes ) ],
			'icon'      => $icon,
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
