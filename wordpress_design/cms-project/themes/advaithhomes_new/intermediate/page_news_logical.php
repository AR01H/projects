<?php
/**
 * intermediate/page_news_logical.php
 *
 * Intermediate logic for the news listing page.
 *
 * Content priority (first source that returns data wins):
 *   1. CMS DB  - ah_taxonomy_parent_terms → ah_taxonomies → wp_posts (when plugin tables exist)
 *   2. WP_Query - all published posts ordered by date (no taxonomy required)
 *
 * Static layout (hero, sidebar, bottom_newsletter) still comes from news.json.
 * JSON article content is never used.
 *
 * "Easy post/page" rule: creating any WP post in the admin immediately populates
 * the news listing via the WP_Query fallback - no taxonomy tagging needed.
 *
 * RULE: No markup here - only data shaping.
 * RULE: Caller is pages/page-newsall.php.
 */

defined( 'ABSPATH' ) || exit;

function adn_news_get_context() {
	$data   = function_exists( 'adn_service_news_data' )   ? adn_service_news_data()   : array();
	$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();

	$ctx = array(
		'meta'              => isset( $data['meta'] )              ? (array) $data['meta']              : array(),
		'breadcrumb'        => isset( $data['breadcrumb'] )        ? (array) $data['breadcrumb']        : array(),
		'hero'              => isset( $data['hero'] )              ? (array) $data['hero']              : array(),
		'categories'        => array( array( 'key' => 'all', 'label' => sprintf( SITE_LABEL_ALL_PREFIX, SITE_NEWS_NOUN ), 'count' => '' ) ),
		'featured'          => array(),
		'sections'          => array(),
		'sidebar'           => isset( $data['sidebar'] )           ? (array) $data['sidebar']           : array(),
		'bottom_newsletter' => isset( $data['bottom_newsletter'] ) ? (array) $data['bottom_newsletter'] : array(),
		'chrome'            => $chrome,
	);

	// ── Source: Plugin News Bar (WP Admin → ah-news-bar) ───────────────────
	if ( function_exists( 'adn_cms_newsbar_items' ) ) {
		$nb_rows = adn_cms_newsbar_items( 100 );
		if ( ! empty( $nb_rows ) ) {
			$ctx['featured'] = adn_news_newsbar_featured( $nb_rows[0] );
			$nb_rest = array_slice( $nb_rows, 1 );
			if ( ! empty( $nb_rest ) ) {
				$ctx['sections'][] = array(
					'type'       => 'grid',
					'heading'    => sprintf( SITE_LABEL_ALL_PREFIX, SITE_NEWS_NOUN ),
					'link_label' => '',
					'link_url'   => '',
					'items'      => adn_news_newsbar_grid_items( $nb_rest ),
				);
			}
		}
	}

	// ── Sidebar: browse topics ───────────────────────────────────────────────
	$sidebar_topics = array();
	if ( function_exists( 'adn_cms_guide_parents' ) ) {
		foreach ( adn_cms_guide_parents( 12 ) as $parent ) {
			$pslug = isset( $parent->slug ) ? (string) $parent->slug : '';
			$pname = isset( $parent->name ) ? (string) $parent->name : ucwords( str_replace( '-', ' ', $pslug ) );
			if ( '' === $pslug ) { continue; }
			$sidebar_topics[] = array(
				'label' => $pname,
				'url'   => home_url( '/' . $pslug . '/' ),
			);
		}
	}

	// ── Sidebar: recent news with thumbnails ─────────────────────────────────
	$sidebar_news = array();
	if ( function_exists( 'adn_cms_newsbar_items' ) ) {
		foreach ( adn_cms_newsbar_items( 5 ) as $sni ) {
			$sn_label = isset( $sni->text ) ? (string) $sni->text : '';
			if ( '' === $sn_label ) { continue; }
			$sn_thumb = '';
			if ( ! empty( $sni->image_id ) ) {
				$t = wp_get_attachment_image_url( (int) $sni->image_id, 'thumbnail' );
				$sn_thumb = $t ? (string) $t : '';
			}
			$sn_stamp = ! empty( $sni->start_date ) ? $sni->start_date : ( isset( $sni->created_at ) ? $sni->created_at : '' );
			$sidebar_news[] = array(
				'label'     => $sn_label,
				'url'       => function_exists( 'adn_newsbar_item_url' ) ? adn_newsbar_item_url( $sni->id ) : '',
				'thumbnail' => $sn_thumb,
				'icon'      => $sn_thumb ? '' : 'fa-newspaper',
				'meta'      => $sn_stamp ? date_i18n( 'M j, Y', strtotime( $sn_stamp ) ) : '',
			);
		}
	}

	$ctx['sidebar']['topics']      = $sidebar_topics;
	$ctx['sidebar']['recent_news'] = $sidebar_news;

	return $ctx;
}

/* ── Attachment thumbnail helper ────────────────────────────────────────── */

function adn_newsbar_item_thumb( $image_id, $size = 'medium' ) {
	if ( empty( $image_id ) ) { return ''; }
	$t = wp_get_attachment_image_url( (int) $image_id, $size );
	return $t ? (string) $t : '';
}

/* ── News Bar mappers ───────────────────────────────────────────────────── */

function adn_news_newsbar_featured( $item ) {
	$content = isset( $item->content ) ? (string) $item->content : '';
	$excerpt = wp_trim_words( wp_strip_all_tags( $content ), 30, '…' );
	$stamp   = ! empty( $item->start_date ) ? $item->start_date : ( isset( $item->created_at ) ? $item->created_at : '' );
	return array(
		'bg_icon'   => 'fa-newspaper',
		'label'     => SITE_LABEL_FEATURED . ' ' . SITE_NEWS_NOUN,
		'tag'       => SITE_NEWS_NOUN,
		'title'     => isset( $item->text ) ? (string) $item->text : '',
		'excerpt'   => $excerpt,
		'date'      => $stamp ? date_i18n( 'M j, Y', strtotime( $stamp ) ) : '',
		'read_time' => function_exists( 'adn_cms_read_time' ) ? adn_cms_read_time( $content ) : '',
		'url'       => function_exists( 'adn_newsbar_item_url' ) ? adn_newsbar_item_url( $item->id ) : '',
		'thumbnail' => adn_newsbar_item_thumb( isset( $item->image_id ) ? $item->image_id : 0 ),
	);
}

function adn_news_newsbar_grid_items( $rows ) {
	$items = array();
	foreach ( $rows as $item ) {
		$title = isset( $item->text ) ? (string) $item->text : '';
		if ( '' === $title ) { continue; }
		$content = isset( $item->content ) ? (string) $item->content : '';
		$excerpt = wp_trim_words( wp_strip_all_tags( $content ), 25, '…' );
		$stamp   = ! empty( $item->start_date ) ? $item->start_date : ( isset( $item->created_at ) ? $item->created_at : '' );
		$items[] = array(
			'cat_key'    => 'news',
			'icon'       => 'fa-newspaper',
			'bg_class'   => '',
			'pill_class' => 'pill-market',
			'category'   => SITE_NEWS_NOUN,
			'title'      => $title,
			'excerpt'    => $excerpt,
			'date'       => $stamp ? date_i18n( 'M j, Y', strtotime( $stamp ) ) : '',
			'read_time'  => function_exists( 'adn_cms_read_time' ) ? adn_cms_read_time( $content ) : '',
			'url'        => function_exists( 'adn_newsbar_item_url' ) ? adn_newsbar_item_url( $item->id ) : '',
			'thumbnail'  => adn_newsbar_item_thumb( isset( $item->image_id ) ? $item->image_id : 0 ),
		);
	}
	return $items;
}


