<?php
/**
 * intermediate/page_guides_listing_logical.php
 *
 * Intermediate logic for guides listing pages (e.g. /buying-guides/, /guides/).
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

	// Breadcrumb: fallback to Home → Guides when JSON has none.
	$_breadcrumb = isset( $data['breadcrumb'] ) && ! empty( $data['breadcrumb'] )
		? (array) $data['breadcrumb']
		: array(
			array( 'label' => defined( 'PAGE_TITLE_HOME' ) ? PAGE_TITLE_HOME : 'Home', 'url' => '/' ),
			array( 'label' => defined( 'SITE_CONTENT_PLURAL' ) ? SITE_CONTENT_PLURAL : 'Guides', 'url' => null ),
		);

	// Hero: fallback using the WP page title when JSON has none.
	$_hero = isset( $data['hero'] ) && ! empty( $data['hero'] )
		? (array) $data['hero']
		: array(
			'title'       => get_the_title() ?: ( defined( 'SITE_CONTENT_PLURAL' ) ? SITE_CONTENT_PLURAL : 'Guides' ),
			'description' => get_the_excerpt() ?: '',
		);

	$ctx = array(
		'slug'        => $slug,
		'meta'        => isset( $data['meta'] ) ? (array) $data['meta'] : array(),
		'breadcrumb'  => $_breadcrumb,
		'hero'        => $_hero,
		'sidebar'     => array(),
		'guides'      => isset( $data['guides'] )     ? (array) $data['guides']     : array(),
		'cta_banner'  => isset( $data['cta_banner'] ) ? (array) $data['cta_banner'] : array(),
		'bottom_grid' => array(),
		'newsletter'  => array(
			'icon'          => '📬',
			'title'         => defined( 'SITE_NEWSLETTER_TITLE' )  ? SITE_NEWSLETTER_TITLE  : 'Stay Informed',
			'description'   => defined( 'SITE_NEWSLETTER_DESC' )   ? SITE_NEWSLETTER_DESC   : 'Get the latest guides and updates delivered to your inbox.',
			'placeholder'   => defined( 'SITE_NEWSLETTER_PH' )     ? SITE_NEWSLETTER_PH     : 'Your email address',
			'button_label'  => defined( 'SITE_BTN_SUBSCRIBE' )     ? SITE_BTN_SUBSCRIBE     : 'Subscribe',
			'note'          => defined( 'SITE_NEWSLETTER_NOTE' )   ? SITE_NEWSLETTER_NOTE   : 'No spam. Unsubscribe anytime.',
		),
		'chrome'      => $chrome,
	);

	if ( ! ( function_exists( 'adn_cms_available' ) && adn_cms_available() ) ) {
		return $ctx;
	}

	// ── Guide cards ───────────────────────────────────────────────────────────
	$parent_slug = preg_replace( '/-guides?$/', '', $slug );
	$articles    = ( '' !== $parent_slug && $parent_slug !== $slug )
		? adn_cms_articles_for_parent( $parent_slug, 12 )
		: array();

	if ( empty( $articles ) ) {
		$articles = adn_cms_one_article_per_parent();
	}

	if ( ! empty( $articles ) ) {
		$guides               = is_array( $ctx['guides'] ) ? $ctx['guides'] : array();
		$guides['items']      = adn_guides_listing_cms_items( $articles );
		$guides['pagination'] = array( 'current' => 1, 'total' => 1 );
		$ctx['guides']        = $guides;
	}

	// ── Sidebar: categories with child topics ─────────────────────────────────
	$parents   = adn_cms_guide_parents( 50 );
	$cat_groups = array();
	$browse_cats = array();

	foreach ( $parents as $pt ) {
		$p_name = isset( $pt->name ) ? (string) $pt->name : '';
		$p_slug = isset( $pt->slug ) ? (string) $pt->slug : '';
		$p_icon = ! empty( $pt->icon_emoji ) ? (string) $pt->icon_emoji : '📁';
		$p_url  = home_url( '/' . trim( $p_slug, '/' ) . '/' );

		if ( '' === $p_name ) { continue; }

		$topics    = function_exists( 'adn_cms_topics' ) ? adn_cms_topics( (int) $pt->id, 30 ) : array();
		$sub_items = array();
		foreach ( $topics as $topic ) {
			$t_name = isset( $topic->name ) ? (string) $topic->name : '';
			$t_slug = isset( $topic->slug ) ? (string) $topic->slug : '';
			if ( '' === $t_name ) { continue; }
			$sub_items[] = array(
				'label' => $t_name,
				'url'   => home_url( '/' . trim( $t_slug, '/' ) . '/' ),
			);
		}

		$cat_groups[]  = array(
			'label'  => $p_name,
			'slug'   => $p_slug,
			'icon'   => $p_icon,
			'url'    => $p_url,
			'topics' => $sub_items,
		);
		$browse_cats[] = array(
			'label'  => $p_name,
			'slug'   => $p_slug,
			'active' => false,
		);
	}

	// ── Sidebar: categories only ─────────────────────────────────────────────
	$ctx['sidebar'] = array(
		'browse_cats' => $browse_cats,
		'cat_groups'  => $cat_groups,
	);

	// ── Bottom quick links: 4 fixed cards ─────────────────────────────────────
	// Resolve the best tools URL: first enabled calculator card_url, else tools page.
	$_tools_raw  = function_exists( 'adn_calculators' ) ? adn_calculators() : array();
	$_tools_meta = get_option( 'adn_calculators_meta', array() );
	$_tools_url  = defined( 'SITE_CALCULATORS_URL' ) ? SITE_CALCULATORS_URL : '/tools/';
	foreach ( array_slice( $_tools_raw, 0, 1, true ) as $_tk => $_tr ) {
		$_tm = ( isset( $_tools_meta[ $_tk ] ) && is_array( $_tools_meta[ $_tk ] ) ) ? $_tools_meta[ $_tk ] : array();
		if ( ! empty( $_tm['card_url'] ) ) { $_tools_url = (string) $_tm['card_url']; }
		break;
	}

	$ctx['bottom_grid'] = array(
		'links' => array(
			array(
				'icon'  => '📰',
				'label' => defined( 'SITE_LABEL_LATEST_NEWS' ) ? SITE_LABEL_LATEST_NEWS : 'Latest News',
				'url'   => defined( 'SITE_NEWS_URL' )          ? SITE_NEWS_URL          : '/news/',
			),
			array(
				'icon'  => '📚',
				'label' => defined( 'SITE_CONTENT_PLURAL' )    ? SITE_CONTENT_PLURAL    : 'Health Guides',
				'url'   => defined( 'SITE_GUIDES_URL' )        ? SITE_GUIDES_URL        : '/guides/',
			),
			array(
				'icon'  => '💬',
				'label' => defined( 'SITE_SIDEBAR_EXPERT_HELP' ) ? SITE_SIDEBAR_EXPERT_HELP : 'Ask an Expert',
				'url'   => defined( 'SITE_CONTACT_URL' )          ? SITE_CONTACT_URL         : '/contact/',
			),
			array(
				'icon'  => '🧮',
				'label' => defined( 'SITE_TOOLS_PLURAL' )      ? SITE_TOOLS_PLURAL      : 'Health Tools',
				'url'   => $_tools_url,
			),
		),
	);

	return $ctx;
}

/**
 * Map CMS articles → guide_listing_card props.
 */
function adn_guides_listing_cms_items( $articles ) {
	$img_classes = array( 'guide-img-green', 'guide-img-blue', 'guide-img-amber', 'guide-img-purple', 'guide-img-teal' );
	$items       = array();
	foreach ( $articles as $i => $post ) {
		$title = isset( $post->title ) ? $post->title : '';
		if ( '' === $title ) { continue; }
		$icon    = ! empty( $post->_parent_icon ) ? $post->_parent_icon : '📄';
		$items[] = array(
			'img_class' => $img_classes[ $i % count( $img_classes ) ],
			'icon'      => $icon,
			'category'  => ! empty( $post->category_name ) ? $post->category_name : ( defined( 'PARENT_TERM' ) ? PARENT_TERM : '' ),
			'title'     => $title,
			'desc'      => isset( $post->excerpt ) ? (string) $post->excerpt : '',
			'date'      => function_exists( 'adn_cms_post_date' ) ? adn_cms_post_date( $post ) : '',
			'read_time' => function_exists( 'adn_cms_read_time' ) ? adn_cms_read_time( isset( $post->content ) ? $post->content : '' ) : '',
			'url'       => function_exists( 'adn_cms_post_url' )  ? adn_cms_post_url( $post )  : '',
		);
	}
	return $items;
}
