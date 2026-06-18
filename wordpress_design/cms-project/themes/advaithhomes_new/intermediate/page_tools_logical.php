<?php
/**
 * intermediate/page_tools_logical.php
 *
 * Builds context for the /calculators/ page.
 *
 * Priority for every section:
 *   1. Admin options (adn_calculators_page + adn_calculators_general)
 *   2. Live registry (adn_calculators() + adn_calculators_meta)
 *   3. Hardcoded defaults  - no JSON file ever used.
 *
 * RULE: No markup here - only data shaping.
 */

defined( 'ABSPATH' ) || exit;

function adn_calculators_get_context() {
	$pg     = get_option( 'adn_calculators_page', array() );
	$gen    = get_option( 'adn_calculators_general', array() );
	$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();

	$pg_str = function( $key ) use ( $pg ) {
		return ( isset( $pg[ $key ] ) && '' !== $pg[ $key ] ) ? (string) $pg[ $key ] : '';
	};

	// Helper: first non-empty value.
	$first = function() {
		foreach ( func_get_args() as $v ) {
			if ( '' !== $v && null !== $v && false !== $v ) { return $v; }
		}
		return '';
	};

	// ── Hero ─────────────────────────────────────────────────────────────
	$hero = array(
		'title'       => $first( $pg_str( 'hero_title' ), isset( $gen['main_heading'] ) ? (string) $gen['main_heading'] : '', sprintf( adn_term( 'calculators_page.hero_title', 'All %s' ), SITE_TOOLS_PLURAL ) ),
		'description' => $first( $pg_str( 'hero_desc' ),  isset( $gen['intro'] )        ? (string) $gen['intro']        : '' ),
		'bg_icon'     => $first( $pg_str( 'hero_icon' ),  adn_term( 'icons.tools_hero', '🏠🧮' ) ),
	);

	// ── Trust bar ─────────────────────────────────────────────────────────
	$trust_defaults = array(
		array( 'icon' => '⏱', 'title' => adn_term( 'calculators_page.trust_bar.item1_title', 'Accurate & Up to Date' ), 'subtitle' => adn_term( 'calculators_page.trust_bar.item1_desc', 'Based on latest UK data' ) ),
		array( 'icon' => '✓',  'title' => adn_term( 'calculators_page.trust_bar.item2_title', 'Free to Use' ),           'subtitle' => adn_term( 'calculators_page.trust_bar.item2_desc', 'No sign-up required' ) ),
		array( 'icon' => '≡',  'title' => adn_term( 'calculators_page.trust_bar.item3_title', 'Easy to Understand' ),    'subtitle' => adn_term( 'calculators_page.trust_bar.item3_desc', 'Simple, clear results' ) ),
		array( 'icon' => '◎',  'title' => adn_term( 'calculators_page.trust_bar.item4_title', 'Independent' ),           'subtitle' => adn_term( 'calculators_page.trust_bar.item4_desc', 'Unbiased information' ) ),
	);
	$trust_items = array();
	for ( $i = 1; $i <= 4; $i++ ) {
		$icon     = $pg_str( 'trust_' . $i . '_icon' );
		$title    = $pg_str( 'trust_' . $i . '_title' );
		$subtitle = $pg_str( 'trust_' . $i . '_subtitle' );
		if ( $icon || $title ) {
			$trust_items[] = array( 'icon' => $icon, 'title' => $title, 'subtitle' => $subtitle );
		}
	}
	if ( empty( $trust_items ) ) {
		$trust_items = $trust_defaults;
	}

	// Marquee: admin override replaces default trust icons in the hero bottom bar.
	$_mq = function_exists( 'adn_parse_marquee_settings' ) ? adn_parse_marquee_settings( $gen ) : null;
	if ( $_mq ) {
		$trust_items      = array();   // hide the static tools_trust_bar
		$hero['trust_items'] = $_mq['trust'];
	}

	// ── Search ────────────────────────────────────────────────────────────
	$search = array(
		'placeholder' => $first( $pg_str( 'search_placeholder' ), sprintf( adn_term( 'calculators_page.search_placeholder', 'Search %s…' ), strtolower( SITE_TOOLS_PLURAL ) ) ),
	);

	// ── All calcs from registry ───────────────────────────────────────────
	$registry = function_exists( 'adn_calculators' ) ? adn_calculators() : array();
	$meta_all = get_option( 'adn_calculators_meta', array() );

	$defined_cats = function_exists( 'adn_calculator_categories' ) ? adn_calculator_categories() : array();
	$cat_counts = array_fill_keys( array_keys( $defined_cats ), 0 );

	$all_tools = array();
	foreach ( $registry as $key => $calc ) {
		$meta = ( isset( $meta_all[ $key ] ) && is_array( $meta_all[ $key ] ) ) ? $meta_all[ $key ] : array();

		if ( array_key_exists( 'enabled', $meta ) && empty( $meta['enabled'] ) ) { continue; }
		if ( ! empty( $meta['hidden_from_listing'] ) ) { continue; }

		$cats = isset( $meta['categories'] ) && is_array( $meta['categories'] ) ? $meta['categories'] : array();
		$url  = ! empty( $meta['card_url'] )
			? (string) $meta['card_url']
			: home_url( '/?ah_calc_page=' . rawurlencode( $key ) );

		$thumb = '';
		if ( ! empty( $meta['thumbnail_id'] ) ) {
			$t = wp_get_attachment_image_url( (int) $meta['thumbnail_id'], 'medium' );
			$thumb = $t ? (string) $t : '';
		}

		foreach ( $cats as $c ) {
			if ( isset( $cat_counts[ $c ] ) ) { $cat_counts[ $c ]++; }
		}

		$all_tools[] = array(
			'icon'       => ! empty( $calc['icon'] )      ? (string) $calc['icon']      : '🧮',
			'categories' => $cats,
			'title'      => ! empty( $meta['label'] )     ? (string) $meta['label']     : ( ! empty( $calc['title'] ) ? (string) $calc['title'] : $key ),
			'desc'       => ! empty( $meta['desc'] )      ? (string) $meta['desc']      : '',
			'url'        => $url,
			'thumbnail'  => $thumb,
			'highlight'  => ! empty( $meta['highlight'] ) ? (string) $meta['highlight'] : '',
		);
	}

	// ── Filter tabs ───────────────────────────────────────────────────────
	$filter_tabs = array( array( 'key' => 'all', 'label' => adn_term( 'calculators_page.filter_all', 'All' ) ) );
	foreach ( $defined_cats as $ckey => $clabel ) {
		if ( $cat_counts[ $ckey ] > 0 ) {
			$filter_tabs[] = array( 'key' => $ckey, 'label' => $clabel );
		}
	}

	// ── Sidebar categories ────────────────────────────────────────────────
	$sidebar_cats = array( array( 'key' => 'all', 'label' => adn_term( 'calculators_page.filter_all', 'All' ) . ' ' . SITE_TOOLS_PLURAL, 'count' => count( $all_tools ) ) );
	foreach ( $defined_cats as $ckey => $clabel ) {
		if ( $cat_counts[ $ckey ] > 0 ) {
			$sidebar_cats[] = array( 'key' => $ckey, 'label' => $clabel . ' ' . SITE_TOOLS_PLURAL, 'count' => $cat_counts[ $ckey ] );
		}
	}

	// ── Sidebar help CTA ─────────────────────────────────────────────────
	$help = array(
		'title'        => $pg_str( 'sidebar_help_title' ),
		'text'         => $pg_str( 'sidebar_help_text' ),
		'button_label' => $pg_str( 'sidebar_help_btn_label' ),
		'button_url'   => $pg_str( 'sidebar_help_btn_url' ),
	);

	// ── Popular calcs - driven by per-calc is_popular toggle ─────────────
	$popular_tools = array();
	foreach ( $registry as $pk => $pcalc ) {
		$pmeta = ( isset( $meta_all[ $pk ] ) && is_array( $meta_all[ $pk ] ) ) ? $meta_all[ $pk ] : array();
		if ( array_key_exists( 'enabled', $pmeta ) && empty( $pmeta['enabled'] ) ) { continue; }
		if ( ! empty( $pmeta['hidden_from_listing'] ) ) { continue; }
		if ( empty( $pmeta['is_popular'] ) ) { continue; }
		$pthumb = '';
		if ( ! empty( $pmeta['thumbnail_id'] ) ) {
			$t = wp_get_attachment_image_url( (int) $pmeta['thumbnail_id'], 'medium' );
			$pthumb = $t ? (string) $t : '';
		}
		$popular_tools[] = array(
			'icon'      => ! empty( $pcalc['icon'] )       ? (string) $pcalc['icon']       : adn_term( 'icons.tools', '🧮' ),
			'title'     => ! empty( $pmeta['label'] )      ? (string) $pmeta['label']      : ( ! empty( $pcalc['title'] ) ? (string) $pcalc['title'] : $pk ),
			'desc'      => ! empty( $pmeta['desc'] )       ? (string) $pmeta['desc']       : '',
			'url'       => ! empty( $pmeta['card_url'] )   ? (string) $pmeta['card_url']   : home_url( '/?ah_calc_page=' . rawurlencode( $pk ) ),
			'thumbnail' => $pthumb,
			'highlight' => ! empty( $pmeta['highlight'] )  ? (string) $pmeta['highlight']  : '',
		);
	}

	// ── Find CTA ─────────────────────────────────────────────────────────
	$find_cta = array(
		'title'        => $pg_str( 'find_cta_title' ),
		'description'  => $pg_str( 'find_cta_desc' ),
		'button_label' => $pg_str( 'find_cta_btn_label' ),
		'button_url'   => $pg_str( 'find_cta_btn_url' ),
	);

	// ── Breadcrumb ────────────────────────────────────────────────────────
	$breadcrumb = array(
		array( 'label' => PAGE_TITLE_HOME, 'url' => '/' ),
		array( 'label' => SITE_TOOLS_PLURAL, 'url' => null ),
	);

	return array(
		'meta'          => array(),
		'breadcrumb'    => $breadcrumb,
		'hero'          => $hero,
		'trust_items'   => $trust_items,
		'search'        => $search,
		'sidebar'       => array( 'categories' => $sidebar_cats, 'help' => $help ),
		'filter_tabs'   => $filter_tabs,
		'popular_tools' => $popular_tools,
		'all_tools'     => $all_tools,
		'find_cta'      => $find_cta,
		'chrome'        => $chrome,
	);
}


