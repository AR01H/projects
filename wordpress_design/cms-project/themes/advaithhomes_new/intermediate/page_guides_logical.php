<?php
/**
 * intermediate/page_guides_logical.php
 *
 * Builds the full render context for the /guides/ hub page.
 * Shows every Guide parent term with its child topic taxonomy terms.
 *
 * Data sources:
 *   groups / sidebar.guide_parents → adn_cms_guide_parents + adn_cms_topics (CMS plugin)
 *   sidebar.quick_tools            → adn_calculators registry + adn_calculators_meta option
 *   sidebar.news_mini              → News Bar → WP_Query fallback
 *
 * RULE: No markup here - only data shaping.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fetch 3 recent news items; News Bar first, then WP posts.
 * @return array[]  { title, date, tag, gradient, url }
 */
function adn_guides_news_items( $limit = 3 ) {
	$items = array();

	if ( function_exists( 'adn_cms_newsbar_items' ) ) {
		foreach ( adn_cms_newsbar_items( $limit ) as $i => $item ) {
			$title = isset( $item->text ) ? (string) $item->text : '';
			if ( '' === $title ) { continue; }
			$stamp   = ! empty( $item->start_date ) ? $item->start_date : '';
			$items[] = array(
				'title'    => $title,
				'date'     => $stamp ? date_i18n( 'M j, Y', strtotime( $stamp ) ) : '',
				'tag'      => 'NEWS',
				'gradient' => adn_cms_gradient( $i ),
				'url'      => ! empty( $item->link_url ) ? $item->link_url : SITE_NEWS_URL,
			);
		}
	}

	if ( empty( $items ) ) {
		$q = new WP_Query( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );
		if ( $q->have_posts() ) {
			foreach ( $q->posts as $i => $post ) {
				$items[] = array(
					'title'    => $post->post_title,
					'date'     => get_the_date( 'M j, Y', $post ),
					'tag'      => 'NEWS',
					'gradient' => adn_cms_gradient( $i ),
					'url'      => get_permalink( $post ),
				);
			}
			wp_reset_postdata();
		}
	}

	return $items;
}

/**
 * Build the full render context for the Guides Hub page.
 *
 * @return array
 */
function adn_guides_get_context() {
	$chrome  = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();
	$parents = function_exists( 'adn_cms_guide_parents' )   ? adn_cms_guide_parents( 20 ) : array();

	// ── Groups: one per parent term with its child topics ────────────────────
	$groups        = array();
	$sidebar_links = array();

	foreach ( $parents as $i => $pt ) {
		$pid     = (int) $pt->id;
		$name    = isset( $pt->name )        ? (string) $pt->name        : '';
		$slug    = isset( $pt->slug )        ? (string) $pt->slug        : '';
		$desc    = isset( $pt->description ) ? (string) $pt->description : '';
		$icon    = ! empty( $pt->icon_emoji ) ? (string) $pt->icon_emoji : '📚';
		$pt_url  = home_url( '/' . trim( $slug, '/' ) . '/' );

		// Parent thumbnail: image_id stored on the parent term row.
		$img_id  = ! empty( $pt->image_id ) ? (int) $pt->image_id : 0;
		$img_url = '';
		if ( $img_id ) {
			$_iu = wp_get_attachment_image_url( $img_id, 'medium' );
			$img_url = $_iu ? (string) $_iu : '';
		}

		$topics      = function_exists( 'adn_cms_topics' ) ? adn_cms_topics( $pid, 50 ) : array();
		$topic_cards = array();
		foreach ( $topics as $topic ) {
			$t_name  = isset( $topic->name )        ? (string) $topic->name        : '';
			$t_slug  = isset( $topic->slug )        ? (string) $topic->slug        : '';
			$t_icon  = ! empty( $topic->icon_emoji ) ? (string) $topic->icon_emoji : $icon;
			if ( '' === $t_name ) { continue; }
			$topic_cards[] = array(
				'icon'  => $t_icon,
				'title' => $t_name,
				'url'   => home_url( '/' . trim( $t_slug, '/' ) . '/' ),
			);
		}

		if ( '' === $name ) { continue; }

		$groups[] = array(
			'name'      => $name,
			'slug'      => $slug,
			'icon'      => $icon,
			'desc'      => mb_strimwidth( $desc, 0, 140, '…' ),
			'url'       => $pt_url,
			'gradient'  => function_exists( 'adn_cms_gradient' ) ? adn_cms_gradient( $i ) : '',
			'image_url' => $img_url,
			'topics'    => $topic_cards,
		);

		$sidebar_links[] = array(
			'icon'  => $icon,
			'label' => $name,
			'url'   => $pt_url,
			'count' => count( $topic_cards ),
		);
	}

	// ── Sidebar: calculators (top 5) ─────────────────────────────────────────
	$calcs_raw  = function_exists( 'adn_calculators' ) ? adn_calculators() : array();
	$meta_all   = get_option( 'adn_calculators_meta', array() );
	$calc_items = array();
	foreach ( array_slice( $calcs_raw, 0, 5, true ) as $key => $reg ) {
		$cmeta = ( isset( $meta_all[ $key ] ) && is_array( $meta_all[ $key ] ) ) ? $meta_all[ $key ] : array();
		if ( array_key_exists( 'enabled', $cmeta ) && empty( $cmeta['enabled'] ) ) { continue; }
		$calc_items[] = array(
			'icon'  => ! empty( $reg['icon'] )  ? (string) $reg['icon']  : '🧮',
			'label' => ! empty( $reg['title'] ) ? (string) $reg['title'] : (string) $key,
			'url'   => ! empty( $cmeta['card_url'] )
				? (string) $cmeta['card_url']
				: home_url( '/?ah_calc_page=' . rawurlencode( $key ) ),
		);
	}

	// ── Sidebar: news (3 items) ───────────────────────────────────────────────
	$news_items = adn_guides_news_items( 3 );

	return array(
		'meta'       => array(
			'page_title'       => SITE_DOMAIN_NOUN . ' ' . SITE_CONTENT_PLURAL . ' - ' . SITE_BRAND_NAME,
			'meta_description' => 'Browse our complete library of property guides covering buying, selling and moving home in the UK.',
		),
		'breadcrumb' => array(
			array( 'label' => PAGE_TITLE_HOME, 'url' => '/' ),
			array( 'label' => SITE_CONTENT_PLURAL, 'url' => null ),
		),
		'hero'       => array(
			'title'       => 'Property Guides',
			'description' => 'Browse our complete library of guides covering every step of buying, selling and moving home in the UK.',
		),
		'groups'  => $groups,
		'sidebar' => array(
			'guide_parents' => array(
				'heading' => 'Browse by Topic',
				'items'   => $sidebar_links,
			),
			'quick_tools' => array(
				'heading' => '🧮 ' . SITE_TOOLS_PLURAL,
				'items'   => $calc_items,
				'cta'     => array( 'label' => 'All ' . SITE_TOOLS_PLURAL . ' →', 'url' => SITE_CALCULATORS_URL ),
			),
			'news_mini' => array(
				'heading'  => 'Latest ' . SITE_NEWS_NOUN,
				'items'    => $news_items,
				'view_all' => array( 'label' => 'View all ' . SITE_NEWS_NOUN . ' →', 'url' => SITE_NEWS_URL ),
			),
		),
		'chrome' => $chrome,
	);
}
