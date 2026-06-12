<?php
/**
 * intermediate/page_category_logical.php
 *
 * Intermediate logic for all category guide pages (buying, selling, house-movers…).
 * Reads the current page slug, loads the matching JSON via the service layer,
 * then overrides article-list sections with live plugin/WP data where available.
 *
 * RULE: No markup here — only data shaping.
 * RULE: Caller is pages/page-category_guide.php (and eventually the plugin REST handler).
 *
 * Data priority:
 *   guides.items → CMS plugin (articles for this parent slug) → JSON fallback
 *   news.items   → Plugin News Bar → CMS latest news → WP_Query → JSON fallback
 *   All other sections (meta, hero, journey, regulations, calculators, sidebar, cta_banner)
 *                → JSON only (static layout/config, no plugin equivalent)
 */

defined( 'ABSPATH' ) || exit;

/**
 * Map CMS articles for a parent slug to guide card shape.
 */
function adn_category_cms_guides( $slug ) {
	if ( ! function_exists( 'adn_cms_articles_for_parent' ) ) {
		return array();
	}
	$posts = adn_cms_articles_for_parent( $slug, 6 );
	if ( empty( $posts ) ) {
		return array();
	}
	$items = array();
	foreach ( $posts as $i => $post ) {
		$items[] = array(
			'icon'        => '📄',
			'gradient'    => adn_cms_gradient( $i ),
			'category'    => isset( $post->category_name ) ? (string) $post->category_name : '',
			'title'       => isset( $post->title )         ? (string) $post->title         : '',
			'description' => isset( $post->excerpt )       ? wp_trim_words( (string) $post->excerpt, 18, '…' ) : '',
			'read_more'   => 'Read More →',
			'url'         => adn_cms_post_url( $post ),
		);
	}
	return $items;
}

/**
 * Fetch news items for the category page.
 * Priority: Plugin News Bar → CMS latest news → WP_Query.
 */
function adn_category_cms_news( $limit = 3 ) {
	$items = array();

	// 1. Plugin News Bar (independent table check).
	if ( function_exists( 'adn_cms_newsbar_items' ) ) {
		foreach ( adn_cms_newsbar_items( $limit ) as $i => $item ) {
			$title = isset( $item->text ) ? $item->text : '';
			if ( '' === $title ) {
				continue;
			}
			$stamp   = ! empty( $item->start_date ) ? $item->start_date : '';
			$items[] = array(
				'title'    => $title,
				'date'     => $stamp ? date_i18n( 'M j, Y', strtotime( $stamp ) ) : '',
				'tag'      => 'NEWS',
				'gradient' => adn_cms_gradient( $i ),
				'url'      => ! empty( $item->link_url ) ? $item->link_url : '/news/',
			);
		}
	}

	// 2. CMS latest news taxonomy posts.
	if ( empty( $items ) && function_exists( 'adn_cms_latest_news' ) ) {
		foreach ( adn_cms_latest_news( $limit ) as $i => $post ) {
			$title = isset( $post->title ) ? (string) $post->title : '';
			if ( '' === $title ) {
				continue;
			}
			$items[] = array(
				'title'    => $title,
				'date'     => adn_cms_post_date( $post ),
				'tag'      => 'NEWS',
				'gradient' => adn_cms_gradient( $i ),
				'url'      => adn_cms_post_url( $post ),
			);
		}
	}

	// 3. WP_Query fallback — any published WP post shows immediately.
	if ( empty( $items ) ) {
		$q = new WP_Query( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );
		if ( $q->have_posts() ) {
			foreach ( $q->posts as $i => $wp_post ) {
				$items[] = array(
					'title'    => $wp_post->post_title,
					'date'     => get_the_date( 'M j, Y', $wp_post ),
					'tag'      => 'NEWS',
					'gradient' => adn_cms_gradient( $i ),
					'url'      => get_permalink( $wp_post ),
				);
			}
			wp_reset_postdata();
		}
	}

	return $items;
}

function adn_category_get_context( $slug = '' ) {

	// ── 1. Resolve slug ──────────────────────────────────────────────
	if ( '' === $slug ) {
		$page = get_queried_object();
		$slug = ( $page instanceof WP_Post ) ? (string) $page->post_name : '';
	}
	$slug = sanitize_key( $slug );

	// ── 2. Load JSON defaults via service layer ───────────────────────
	$data   = function_exists( 'adn_service_category_data' ) ? adn_service_category_data( $slug ) : array();
	$chrome = function_exists( 'adn_service_site_chrome' )   ? adn_service_site_chrome()           : array();

	// ── 3. Override guides.items from CMS plugin ──────────────────────
	if ( $slug ) {
		$cms_guides = adn_category_cms_guides( $slug );
		if ( ! empty( $cms_guides ) ) {
			if ( ! isset( $data['guides'] ) || ! is_array( $data['guides'] ) ) {
				$data['guides'] = array();
			}
			$data['guides']['items'] = $cms_guides;
		}
	}

	// ── 4. Override news.items from plugin / WP ───────────────────────
	$cms_news = adn_category_cms_news( 3 );
	if ( ! empty( $cms_news ) ) {
		if ( ! isset( $data['news'] ) || ! is_array( $data['news'] ) ) {
			$data['news'] = array();
		}
		$data['news']['items'] = $cms_news;
	}

	// ── 5. Shape context with safe defaults ──────────────────────────
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
