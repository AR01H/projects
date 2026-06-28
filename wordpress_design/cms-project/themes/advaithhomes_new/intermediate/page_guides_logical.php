<?php
/**
 * intermediate/page_guides_logical.php
 *
 * Builds the full render context for the /guides/ hub page.
 * Groups by parent term; topics inside each group = category-type terms only.
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

	// Dark premium gradients for the phg left panel (cycle per parent).
	$_phg_grads = array(
		'linear-gradient(150deg,#1a3d2b 0%,#2d6147 100%)',
		'linear-gradient(150deg,#2a1f40 0%,#4a3880 100%)',
		'linear-gradient(150deg,#1d3050 0%,#2d5496 100%)',
		'linear-gradient(150deg,#2d3b1a 0%,#4a6128 100%)',
		'linear-gradient(150deg,#3b1a1a 0%,#7a2e28 100%)',
		'linear-gradient(150deg,#1a2d3b 0%,#2d5068 100%)',
	);

	// ── Groups: one per parent term, topics = category-type child terms only ──
	$groups        = array();
	$sidebar_links = array();

	foreach ( $parents as $i => $pt ) {
		$pid    = (int) $pt->id;
		$name   = isset( $pt->name )         ? (string) $pt->name         : '';
		$slug   = isset( $pt->slug )         ? (string) $pt->slug         : '';
		$desc   = isset( $pt->description )  ? (string) $pt->description  : '';
		$icon   = ! empty( $pt->icon_emoji ) ? (string) $pt->icon_emoji   : '📚';
		$pt_url = home_url( '/' . trim( $slug, '/' ) . '/' );
		if ( '' === $name ) { continue; }

		$img_id  = ! empty( $pt->image_id ) ? (int) $pt->image_id : 0;
		$img_url = '';
		if ( $img_id ) {
			$_iu = wp_get_attachment_image_url( $img_id, 'medium' );
			$img_url = $_iu ? (string) $_iu : '';
		}

		// Fetch child terms for this parent, excluding non-category types (e.g. glossary).
		$raw_topics  = function_exists( 'adn_cms_category_topics' ) ? adn_cms_category_topics( $pid, 50 ) : array();
		$topic_cards = array();
		foreach ( $raw_topics as $topic ) {
			$t_name = isset( $topic->name )         ? (string) $topic->name         : '';
			$t_slug = isset( $topic->slug )         ? (string) $topic->slug         : '';
			$t_icon = ! empty( $topic->icon_emoji ) ? (string) $topic->icon_emoji   : $icon;
			if ( '' === $t_name ) { continue; }
			$topic_cards[] = array(
				'icon'  => $t_icon,
				'title' => $t_name,
				'url'   => home_url( '/' . trim( $t_slug, '/' ) . '/' ),
			);
		}

		// Latest 3 articles for this parent group.
		$raw_articles = function_exists( 'adn_cms_articles_for_parent' ) ? adn_cms_articles_for_parent( $slug, 3 ) : array();
		$latest_posts = array();
		foreach ( $raw_articles as $art ) {
			$_t = isset( $art->title ) ? (string) $art->title : '';
			if ( '' === $_t ) { continue; }
			$latest_posts[] = array(
				'title' => $_t,
				'date'  => function_exists( 'adn_cms_post_date' ) ? adn_cms_post_date( $art ) : '',
				'url'   => function_exists( 'adn_cms_post_url' )  ? adn_cms_post_url( $art )  : '',
				'tag'   => isset( $art->category_name ) ? (string) $art->category_name : '',
			);
		}

		// Skip parent terms with no categories and no articles - nothing to show.
		if ( empty( $topic_cards ) && empty( $latest_posts ) ) { continue; }

		$groups[] = array(
			'name'         => $name,
			'slug'         => $slug,
			'icon'         => $icon,
			'desc'         => mb_strimwidth( $desc, 0, 140, '…' ),
			'url'          => $pt_url,
			'gradient'     => $_phg_grads[ $i % count( $_phg_grads ) ],
			'image_url'    => $img_url,
			'topics'       => $topic_cards,
			'latest_posts' => $latest_posts,
		);

		$sidebar_links[] = array(
			'icon'  => $icon,
			'label' => $name,
			'url'   => $pt_url,
			'count' => count( $topic_cards ),
		);
	}

	// ── Sidebar: calculators (top 5) ─────────────────────────────────────────
	$tools_raw  = function_exists( 'adn_calculators' ) ? adn_calculators() : array();
	$meta_all   = get_option( 'adn_calculators_meta', array() );
	$calc_items = array();
	foreach ( array_slice( $tools_raw, 0, 5, true ) as $key => $reg ) {
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
			'meta_description' => adn_term( 'guides_page.meta_description', 'Browse our complete library of guides.' ),
		),
		'breadcrumb' => array(
			array( 'label' => PAGE_TITLE_HOME, 'url' => '/' ),
			array( 'label' => SITE_CONTENT_PLURAL, 'url' => null ),
		),
		'hero'       => array(
			'title'       => adn_term( 'guides_page.hero_title',       SITE_DOMAIN_NOUN . ' ' . SITE_CONTENT_PLURAL ),
			'description' => adn_term( 'guides_page.hero_description', '' ),
		),
		'groups'  => $groups,
		'sidebar' => array(
			'guide_parents' => array(
				'heading' => adn_term( 'guides_page.sidebar_browse_heading', 'Browse by Topic' ),
				'items'   => $sidebar_links,
			),
			'quick_tools' => array(
				'heading' => '🧮 ' . SITE_TOOLS_PLURAL,
				'items'   => $calc_items,
				'cta'     => array( 'label' => sprintf( SITE_LABEL_ALL_PREFIX, SITE_TOOLS_PLURAL ) . ' →', 'url' => SITE_CALCULATORS_URL ),
			),
			'news_mini' => array(
				'heading'  => SITE_LABEL_LATEST_NEWS,
				'items'    => $news_items,
				'view_all' => array( 'label' => CONTENT_VIEW_ALL_NEWS, 'url' => SITE_NEWS_URL ),
			),
			'expert_help' => array(
				'heading'  => defined( 'SITE_SIDEBAR_EXPERT_HELP' ) ? SITE_SIDEBAR_EXPERT_HELP : 'Need Expert Help?',
				'subtitle' => defined( 'SITE_GUIDANCE_LABEL' ) ? SITE_GUIDANCE_LABEL : 'Get personalised guidance',
				'experts'  => array(),
				'cta'      => array(
					'label' => defined( 'SITE_CONTACT_LABEL' ) ? SITE_CONTACT_LABEL : 'Contact Us',
					'url'   => defined( 'SITE_CONTACT_URL' )   ? SITE_CONTACT_URL   : '/contact/',
				),
			),
		),
		'chrome' => $chrome,
	);
}
