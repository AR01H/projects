<?php
/**
 * intermediate/page_calculator_single_logical.php
 *
 * Builds context for /calculators/?ah_calc_page=KEY - the full single-calculator
 * detail page (header + footer + sidebar).
 *
 * Returns null when $key does not match any active calculator.
 * RULE: No markup here - only data shaping.
 */

defined( 'ABSPATH' ) || exit;

function adn_calculator_single_get_context( $key ) {
	$key      = sanitize_key( $key );
	$registry = function_exists( 'adn_calculators' ) ? adn_calculators() : array();

	if ( '' === $key || ! isset( $registry[ $key ] ) ) {
		return null;
	}

	$calc     = $registry[ $key ];
	$meta_all = get_option( 'adn_calculators_meta', array() );
	$meta     = ( isset( $meta_all[ $key ] ) && is_array( $meta_all[ $key ] ) ) ? $meta_all[ $key ] : array();

	// ── Core fields ───────────────────────────────────────────────────────
	$title = ( isset( $meta['label'] ) && '' !== $meta['label'] )
		? (string) $meta['label']
		: ( ! empty( $calc['title'] ) ? (string) $calc['title'] : $key );

	$desc  = ( isset( $meta['desc'] ) && '' !== $meta['desc'] ) ? (string) $meta['desc'] : '';
	$icon  = ! empty( $calc['icon'] ) ? (string) $calc['icon'] : '🧮';

	$thumbnail_url = '';
	if ( ! empty( $meta['thumbnail_id'] ) ) {
		$t = wp_get_attachment_image_url( (int) $meta['thumbnail_id'], 'large' );
		$thumbnail_url = $t ? (string) $t : '';
	}

	$highlight  = ( isset( $meta['highlight'] ) && '' !== $meta['highlight'] ) ? (string) $meta['highlight'] : '';
	$categories = ( isset( $meta['categories'] ) && is_array( $meta['categories'] ) ) ? $meta['categories'] : array();

	// ── Hero ─────────────────────────────────────────────────────────────
	$hero = array(
		'title'       => $title,
		'description' => $desc,
		'bg_icon'     => $icon,
	);

	// ── Breadcrumb ────────────────────────────────────────────────────────
	$breadcrumb = array(
		array( 'label' => PAGE_TITLE_HOME, 'url' => '/' ),
		array( 'label' => SITE_TOOLS_PLURAL, 'url' => home_url( SITE_CALCULATORS_URL ) ),
		array( 'label' => $title,        'url' => null ),
	);

	// ── Related calculators (same categories, up to 6) ────────────────────
	$related = array();
	if ( ! empty( $categories ) ) {
		foreach ( $registry as $rkey => $rcalc ) {
			if ( $rkey === $key ) { continue; }
			$rmeta = ( isset( $meta_all[ $rkey ] ) && is_array( $meta_all[ $rkey ] ) ) ? $meta_all[ $rkey ] : array();
			if ( array_key_exists( 'enabled', $rmeta ) && empty( $rmeta['enabled'] ) ) { continue; }
			$rcats = ( isset( $rmeta['categories'] ) && is_array( $rmeta['categories'] ) ) ? $rmeta['categories'] : array();
			if ( empty( array_intersect( $categories, $rcats ) ) ) { continue; }
			$rthumb = '';
			if ( ! empty( $rmeta['thumbnail_id'] ) ) {
				$t = wp_get_attachment_image_url( (int) $rmeta['thumbnail_id'], 'thumbnail' );
				$rthumb = $t ? (string) $t : '';
			}
			$related[] = array(
				'icon'      => ! empty( $rcalc['icon'] )       ? (string) $rcalc['icon']       : '🧮',
				'name'      => ! empty( $rmeta['label'] )      ? (string) $rmeta['label']      : ( ! empty( $rcalc['title'] ) ? (string) $rcalc['title'] : $rkey ),
				'url'       => ! empty( $rmeta['card_url'] )   ? (string) $rmeta['card_url']   : home_url( '/?ah_calc_page=' . rawurlencode( $rkey ) ),
				'thumbnail' => $rthumb,
				'highlight' => ! empty( $rmeta['highlight'] )  ? (string) $rmeta['highlight']  : '',
			);
			if ( count( $related ) >= 6 ) { break; }
		}
	}

	// ── Guide link ────────────────────────────────────────────────────────
	$guide = array(
		'label' => ( isset( $meta['guide_label'] ) && '' !== $meta['guide_label'] ) ? (string) $meta['guide_label'] : 'Read the full guide →',
		'url'   => ( isset( $meta['guide_url'] )   && '' !== $meta['guide_url'] )   ? (string) $meta['guide_url']   : '',
	);

	// ── Sidebar categories (all calcs - links back to listing page) ───────
	$defined_cats = function_exists( 'adn_calculator_categories' ) ? adn_calculator_categories() : array(
		'buying'        => 'Buying',
		'selling'       => 'Selling',
		'moving'        => 'Moving Home',
		'mortgage'      => 'Mortgage',
		'tax'           => 'Tax',
		'affordability' => 'Affordability',
	);
	$cat_counts     = array_fill_keys( array_keys( $defined_cats ), 0 );
	$total_enabled  = 0;
	foreach ( $registry as $rk => $rc ) {
		$rm = ( isset( $meta_all[ $rk ] ) && is_array( $meta_all[ $rk ] ) ) ? $meta_all[ $rk ] : array();
		if ( array_key_exists( 'enabled', $rm ) && empty( $rm['enabled'] ) ) { continue; }
		$total_enabled++;
		foreach ( ( isset( $rm['categories'] ) && is_array( $rm['categories'] ) ) ? $rm['categories'] : array() as $c ) {
			if ( isset( $cat_counts[ $c ] ) ) { $cat_counts[ $c ]++; }
		}
	}
	$sidebar_cats = array( array(
		'key'   => 'all',
		'label' => 'All ' . SITE_TOOLS_PLURAL,
		'count' => $total_enabled,
		'url'   => home_url( SITE_CALCULATORS_URL ),
	) );
	foreach ( $defined_cats as $ckey => $clabel ) {
		if ( $cat_counts[ $ckey ] > 0 ) {
			$sidebar_cats[] = array(
				'key'   => $ckey,
				'label' => $clabel . ' ' . SITE_TOOLS_PLURAL,
				'count' => $cat_counts[ $ckey ],
				'url'   => home_url( SITE_CALCULATORS_URL . '#' . $ckey ),
			);
		}
	}

	// ── Sidebar help / expert contact (from page settings) ───────────────
	$pg = get_option( 'adn_calculators_page', array() );

	$_eh_title  = ( isset( $pg['sidebar_help_title'] )     && '' !== $pg['sidebar_help_title'] )     ? (string) $pg['sidebar_help_title']     : 'Need Expert Help?';
	$_eh_text   = ( isset( $pg['sidebar_help_text'] )      && '' !== $pg['sidebar_help_text'] )      ? (string) $pg['sidebar_help_text']      : 'Speak to one of our mortgage or property experts today.';
	$_eh_btn_l  = ( isset( $pg['sidebar_help_btn_label'] ) && '' !== $pg['sidebar_help_btn_label'] ) ? (string) $pg['sidebar_help_btn_label'] : SITE_EXPERT_LABEL;
	$_eh_btn_u  = ( isset( $pg['sidebar_help_btn_url'] )   && '' !== $pg['sidebar_help_btn_url'] )   ? (string) $pg['sidebar_help_btn_url']   : SITE_EXPERT_URL;

	$expert_help_sidebar = array(
		'heading'  => $_eh_title,
		'subtitle' => $_eh_text,
		'experts'  => array(),
		'cta'      => array( 'label' => $_eh_btn_l, 'url' => $_eh_btn_u ),
	);

	// ── Sidebar newsletter ────────────────────────────────────────────────
	$newsletter_sidebar = array(
		'heading'      => ! empty( $pg['sidebar_nl_heading'] )     ? (string) $pg['sidebar_nl_heading']     : 'Stay in the Know',
		'description'  => ! empty( $pg['sidebar_nl_desc'] )        ? (string) $pg['sidebar_nl_desc']        : 'Get property news, guides and calculator tips straight to your inbox.',
		'placeholder'  => ! empty( $pg['sidebar_nl_placeholder'] ) ? (string) $pg['sidebar_nl_placeholder'] : 'Your email address',
		'button_label' => ! empty( $pg['sidebar_nl_btn_label'] )   ? (string) $pg['sidebar_nl_btn_label']   : 'Subscribe',
		'note'         => 'No spam. Unsubscribe any time.',
	);

	// ── Chrome (header/footer data) ──────────────────────────────────────
	$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();

	// ── Latest news from CMS plugin ───────────────────────────────────────
	$news_items = array();
	if ( function_exists( 'adn_cms_latest_news' ) ) {
		foreach ( (array) adn_cms_latest_news( 5 ) as $n ) {
			$nid          = isset( $n->ID ) ? (int) $n->ID : 0;
			$news_items[] = array(
				'title' => isset( $n->title )    ? (string) $n->title    : '',
				'url'   => isset( $n->post_url ) ? (string) $n->post_url : ( $nid ? get_permalink( $nid ) : '' ),
				'date'  => isset( $n->date )     ? (string) $n->date     : '',
				'icon'  => '📰',
			);
		}
	}

	// ── News mini (sidebar) - first 3 newsbar items ─────────────────────
	$news_mini_items = array();
	foreach ( array_slice( $news_items, 0, 3 ) as $n ) {
		$news_mini_items[] = array(
			'title'    => $n['title'],
			'url'      => $n['url'],
			'date'     => $n['date'],
			'gradient' => 'linear-gradient(135deg,var(--color-primary,#1a4a7a),var(--color-primary-dark,#0f2d4a))',
		);
	}

	// ── Share data ────────────────────────────────────────────────────────
	$share_url = home_url( add_query_arg( array( 'ah_calc_page' => $key ), '/' ) );

	return array(
		'chrome'        => $chrome,
		'key'           => $key,
		'title'         => $title,
		'desc'          => $desc,
		'icon'          => $icon,
		'highlight'     => $highlight,
		'thumbnail_url' => $thumbnail_url,
		'hero'          => $hero,
		'breadcrumb'    => $breadcrumb,
		'guide'         => $guide,
		'help_text'     => ( isset( $meta['help'] ) && '' !== $meta['help'] ) ? (string) $meta['help'] : '',
		'related'       => $related,
		'news'          => $news_items,
		'share'         => array( 'url' => $share_url, 'title' => $title ),
		'sidebar'       => array(
			'categories'  => $sidebar_cats,
			'expert_help' => $expert_help_sidebar,
			'newsletter'  => $newsletter_sidebar,
			'news_mini'   => ! empty( $news_mini_items ) ? array(
				'heading'  => 'Latest ' . SITE_NEWS_NOUN,
				'items'    => $news_mini_items,
				'view_all' => array( 'label' => 'View all ' . SITE_NEWS_NOUN, 'url' => SITE_NEWS_URL ),
			) : array(),
		),
	);
}
