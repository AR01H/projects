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

	// ── Source 0: Plugin News Bar (ah_news_bar_items) ───────────────────────
	$loaded = false;
	if ( ! $loaded && function_exists( 'adn_cms_newsbar_items' ) ) {
		$nb_rows = adn_cms_newsbar_items( 100 );
		if ( ! empty( $nb_rows ) ) {
			$ctx['featured']    = adn_news_newsbar_featured( $nb_rows[0] );
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
			$loaded = true;
		}
	}

	// ── Source 1: CMS plugin DB ──────────────────────────────────────────────
	if ( ! $loaded && function_exists( 'adn_cms_available' ) && adn_cms_available() ) {
		$ctx['categories'] = adn_news_cms_categories();
		$news_posts        = adn_cms_latest_news( 13 );

		if ( ! empty( $news_posts ) ) {
			$ctx['featured']    = adn_news_cms_featured( $news_posts[0] );
			$rest = array_slice( $news_posts, 1 );
			if ( ! empty( $rest ) ) {
				$ctx['sections'][] = array(
					'type'    => 'grid',
					'heading' => SITE_LABEL_LATEST_NEWS,
					'link_label' => '',
					'link_url'   => '',
					'items'   => adn_news_cms_grid_items( $rest ),
				);
			}
			$loaded = true;
		}
	}

	// ── Source 2: WP_Query fallback (only `news` post_type; avoid showing regular posts) ─
	if ( ! $loaded ) {
		$q = new WP_Query( array(
			'post_type'      => 'news',
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
					'heading'    => SITE_LABEL_LATEST_NEWS,
					'link_label' => '',
					'link_url'   => '',
					'items'      => adn_news_wp_grid_items( $rest ),
				);
			}

			// Flag for callers: whether we have any news to show
			$ctx['has_news'] = ( ! empty( $ctx['featured'] ) || ! empty( $ctx['sections'] ) );
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

/* ── CMS DB mappers ─────────────────────────────────────────────────────── */

function adn_news_cms_categories() {
	$cats        = array( array( 'key' => 'all', 'label' => sprintf( SITE_LABEL_ALL_PREFIX, SITE_NEWS_NOUN ), 'count' => '' ) );
	$news_parent = function_exists( 'adn_cms_parent_by_slug' ) ? adn_cms_parent_by_slug( 'news' ) : null;

	if ( $news_parent ) {
		// Specific 'news' parent exists — show its child topics.
		foreach ( adn_cms_topics( (int) $news_parent->id, 20 ) as $topic ) {
			$cats[] = array(
				'key'   => isset( $topic->slug ) ? $topic->slug : '',
				'label' => isset( $topic->name ) ? $topic->name : '',
				'count' => '',
			);
		}
	} elseif ( function_exists( 'adn_cms_guide_parents' ) ) {
		// Fall back to all parent terms.
		foreach ( adn_cms_guide_parents( 20 ) as $parent ) {
			$slug = isset( $parent->slug ) ? (string) $parent->slug : '';
			$name = isset( $parent->name ) ? (string) $parent->name : ucwords( str_replace( '-', ' ', $slug ) );
			if ( '' === $slug ) { continue; }
			$cats[] = array(
				'key'   => $slug,
				'label' => $name,
				'count' => '',
			);
		}
	}

	return $cats;
}

function adn_news_cms_featured( $post ) {
	return array(
		'bg_icon'   => 'fa-newspaper',
		'label'     => SITE_LABEL_FEATURED . ' ' . SITE_NEWS_NOUN,
		'tag'       => SITE_NEWS_NOUN,
		'title'     => isset( $post->title ) ? $post->title : '',
		'excerpt'   => isset( $post->excerpt ) ? (string) $post->excerpt : '',
		'date'      => adn_cms_post_date( $post ),
		'read_time' => adn_cms_read_time( isset( $post->content ) ? $post->content : '' ),
		'url'       => adn_cms_post_url( $post ),
		'thumbnail' => ( isset( $post->thumbnail ) && $post->thumbnail ) ? (string) $post->thumbnail : ( isset( $post->image_url ) ? (string) $post->image_url : '' ),
	);
}

function adn_news_cms_grid_items( $posts ) {
	$items = array();
	foreach ( $posts as $post ) {
		$title = isset( $post->title ) ? $post->title : '';
		if ( '' === $title ) {
			continue;
		}
		$cat     = ! empty( $post->category_name ) ? $post->category_name : SITE_NEWS_NOUN;
		$thumb = '';
		if ( isset( $post->thumbnail ) && $post->thumbnail ) { $thumb = (string) $post->thumbnail; }
		elseif ( isset( $post->image_url ) && $post->image_url ) { $thumb = (string) $post->image_url; }
		elseif ( isset( $post->image ) && $post->image ) { $thumb = (string) $post->image; }

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
			'thumbnail'  => $thumb,
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
		'label'     => SITE_LABEL_FEATURED . ' ' . SITE_NEWS_NOUN,
		'tag'       => SITE_NEWS_NOUN,
		'title'     => $post->post_title,
		'excerpt'   => $excerpt,
		'date'      => get_the_date( 'F j, Y', $post ),
		'read_time' => adn_cms_read_time( $post->post_content ),
		'url'       => get_permalink( $post ),
		'thumbnail' => get_the_post_thumbnail_url( $post->ID, 'medium') ?: '',
	);
}

function adn_news_wp_grid_items( $posts ) {
	$items = array();
	foreach ( $posts as $post ) {
		if ( '' === $post->post_title ) {
			continue;
		}
		$cats    = get_the_category( $post->ID );
		$cat     = ! empty( $cats ) ? $cats[0]->name : SITE_NEWS_NOUN;
		$excerpt = $post->post_excerpt
			?: wp_trim_words( wp_strip_all_tags( $post->post_content ), 25, '…' );
		$thumb = get_the_post_thumbnail_url( $post->ID, 'medium' ) ?: '';

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
			'thumbnail'  => $thumb,
		);
	}
	return $items;
}
