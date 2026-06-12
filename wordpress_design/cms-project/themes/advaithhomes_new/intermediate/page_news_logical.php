<?php
/**
 * intermediate/page_news_logical.php
 *
 * Intermediate logic for the news listing page.
 *
 * Content priority (first source that returns data wins):
 *   1. CMS DB  — ah_taxonomy_parent_terms → ah_taxonomies → wp_posts (when plugin tables exist)
 *   2. WP_Query — all published posts ordered by date (no taxonomy required)
 *
 * Static layout (hero, sidebar, bottom_newsletter) still comes from news.json.
 * JSON article content is never used.
 *
 * "Easy post/page" rule: creating any WP post in the admin immediately populates
 * the news listing via the WP_Query fallback — no taxonomy tagging needed.
 *
 * RULE: No markup here — only data shaping.
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
		'categories'        => array( array( 'key' => 'all', 'label' => 'All News', 'count' => '' ) ),
		'featured'          => array(),
		'sections'          => array(),
		'sidebar'           => isset( $data['sidebar'] )           ? (array) $data['sidebar']           : array(),
		'bottom_newsletter' => isset( $data['bottom_newsletter'] ) ? (array) $data['bottom_newsletter'] : array(),
		'chrome'            => $chrome,
	);

	// ── Source 1: CMS plugin DB ──────────────────────────────────────────────
	$loaded = false;
	if ( function_exists( 'adn_cms_available' ) && adn_cms_available() ) {
		$ctx['categories'] = adn_news_cms_categories();
		$news_posts        = adn_cms_latest_news( 13 );

		if ( ! empty( $news_posts ) ) {
			$ctx['featured']    = adn_news_cms_featured( $news_posts[0] );
			$rest = array_slice( $news_posts, 1 );
			if ( ! empty( $rest ) ) {
				$ctx['sections'][] = array(
					'type'    => 'grid',
					'heading' => 'Latest News',
					'link_label' => '',
					'link_url'   => '',
					'items'   => adn_news_cms_grid_items( $rest ),
				);
			}
			$loaded = true;
		}
	}

	// ── Source 2: WP_Query fallback (plain WP posts — no plugin needed) ──────
	if ( ! $loaded ) {
		$q = new WP_Query( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 13,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );

		if ( $q->have_posts() ) {
			$wp_posts = $q->posts;
			wp_reset_postdata();

			$ctx['featured'] = adn_news_wp_featured( $wp_posts[0] );
			$rest = array_slice( $wp_posts, 1 );
			if ( ! empty( $rest ) ) {
				$ctx['sections'][] = array(
					'type'       => 'grid',
					'heading'    => 'Latest News',
					'link_label' => '',
					'link_url'   => '',
					'items'      => adn_news_wp_grid_items( $rest ),
				);
			}
		}
	}

	return $ctx;
}

/* ── CMS DB mappers ─────────────────────────────────────────────────────── */

function adn_news_cms_categories() {
	$cats        = array( array( 'key' => 'all', 'label' => 'All News', 'count' => '' ) );
	$news_parent = function_exists( 'adn_cms_parent_by_slug' ) ? adn_cms_parent_by_slug( 'news' ) : null;
	if ( $news_parent ) {
		foreach ( adn_cms_topics( (int) $news_parent->id, 20 ) as $topic ) {
			$cats[] = array(
				'key'   => isset( $topic->slug ) ? $topic->slug : '',
				'label' => isset( $topic->name ) ? $topic->name : '',
				'count' => '',
			);
		}
	}
	return $cats;
}

function adn_news_cms_featured( $post ) {
	return array(
		'bg_icon'   => 'fa-newspaper',
		'label'     => 'Featured Story',
		'tag'       => 'News',
		'title'     => isset( $post->title ) ? $post->title : '',
		'excerpt'   => isset( $post->excerpt ) ? (string) $post->excerpt : '',
		'date'      => adn_cms_post_date( $post ),
		'read_time' => adn_cms_read_time( isset( $post->content ) ? $post->content : '' ),
		'url'       => adn_cms_post_url( $post ),
	);
}

function adn_news_cms_grid_items( $posts ) {
	$items = array();
	foreach ( $posts as $post ) {
		$title = isset( $post->title ) ? $post->title : '';
		if ( '' === $title ) {
			continue;
		}
		$cat     = ! empty( $post->category_name ) ? $post->category_name : 'News';
		$items[] = array(
			'cat_key'    => sanitize_key( $cat ),
			'icon'       => 'fa-newspaper',
			'bg_class'   => '',
			'pill_class' => 'pill-market',
			'category'   => $cat,
			'title'      => $title,
			'excerpt'    => isset( $post->excerpt ) ? (string) $post->excerpt : '',
			'date'       => adn_cms_post_date( $post ),
			'read_time'  => adn_cms_read_time( isset( $post->content ) ? $post->content : '' ),
			'url'        => adn_cms_post_url( $post ),
		);
	}
	return $items;
}

/* ── WP_Query fallback mappers ──────────────────────────────────────────── */

function adn_news_wp_featured( $post ) {
	$excerpt = $post->post_excerpt
		?: wp_trim_words( wp_strip_all_tags( $post->post_content ), 30, '…' );
	return array(
		'bg_icon'   => 'fa-newspaper',
		'label'     => 'Featured Story',
		'tag'       => 'News',
		'title'     => $post->post_title,
		'excerpt'   => $excerpt,
		'date'      => get_the_date( 'F j, Y', $post ),
		'read_time' => adn_cms_read_time( $post->post_content ),
		'url'       => get_permalink( $post ),
	);
}

function adn_news_wp_grid_items( $posts ) {
	$items = array();
	foreach ( $posts as $post ) {
		if ( '' === $post->post_title ) {
			continue;
		}
		$cats    = get_the_category( $post->ID );
		$cat     = ! empty( $cats ) ? $cats[0]->name : 'News';
		$excerpt = $post->post_excerpt
			?: wp_trim_words( wp_strip_all_tags( $post->post_content ), 25, '…' );
		$items[] = array(
			'cat_key'    => sanitize_key( $cat ),
			'icon'       => 'fa-newspaper',
			'bg_class'   => '',
			'pill_class' => 'pill-market',
			'category'   => $cat,
			'title'      => $post->post_title,
			'excerpt'    => $excerpt,
			'date'       => get_the_date( 'M j, Y', $post ),
			'read_time'  => adn_cms_read_time( $post->post_content ),
			'url'        => get_permalink( $post ),
		);
	}
	return $items;
}
