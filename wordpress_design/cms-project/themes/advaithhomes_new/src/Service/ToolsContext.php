<?php
namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

class ToolsContext {

	public static function getContext() {
		$cache_key = 'page_tools_context';
		if ( class_exists( 'ADN_Cache' ) ) {
			$cached = \ADN_Cache::get( $cache_key, 'pages' );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		$pg     = get_option( 'adn_calculators_page', array() );
		$gen    = get_option( 'adn_calculators_general', array() );
		$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();

		$pg_str = function( $key ) use ( $pg ) {
			return ( isset( $pg[ $key ] ) && '' !== $pg[ $key ] ) ? (string) $pg[ $key ] : '';
		};

		$first = function() {
			foreach ( func_get_args() as $v ) {
				if ( '' !== $v && null !== $v && false !== $v ) { return $v; }
			}
			return '';
		};

		$_glass_cards_raw = isset( $gen['glass_cards'] ) ? trim( (string) $gen['glass_cards'] ) : '';
		$_glass_cards     = array();
		if ( '' !== $_glass_cards_raw ) {
			foreach ( explode( "\n", $_glass_cards_raw ) as $_line ) {
				$_line = trim( $_line );
				if ( '' === $_line ) { continue; }
				$_parts = explode( '|', $_line );
				if ( count( $_parts ) >= 3 ) {
					$_glass_cards[] = array(
						'icon'  => trim( $_parts[0] ),
						'title' => trim( $_parts[1] ),
						'desc'  => trim( $_parts[2] ),
					);
				}
			}
		}

		$hero = array(
			'eyebrow'     => isset( $gen['subheading'] ) ? (string) $gen['subheading'] : '',
			'title'       => $first( $pg_str( 'hero_title' ), isset( $gen['main_heading'] ) ? (string) $gen['main_heading'] : '', sprintf( adn_term( 'calculators_page.hero_title', 'All %s' ), SITE_TOOLS_PLURAL ) ),
			'description' => $first( $pg_str( 'hero_desc' ),  isset( $gen['intro'] )        ? (string) $gen['intro']        : '' ),
			'bg_icon'     => $first( $pg_str( 'hero_icon' ),  adn_term( 'icons.tools_hero', '🏠🧮' ) ),
			'bg_url'      => isset( $gen['thumbnail'] ) ? (string) $gen['thumbnail'] : '',
			'glass_cards' => $_glass_cards,
		);

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

		$_mq = function_exists( 'adn_parse_marquee_settings' ) ? adn_parse_marquee_settings( $gen ) : null;
		if ( $_mq ) {
			$trust_items      = array();
			$hero['trust_items'] = $_mq['trust'];
		} else {
			$hero['trust_items'] = $trust_items;
		}

		$search = array(
			'placeholder' => $first( $pg_str( 'search_placeholder' ), SITE_PLACEHOLDER_SEARCH_CALC ),
		);

		$registry = function_exists( 'adn_calculators' ) ? adn_calculators() : array();
		$meta_all = get_option( 'adn_calculators_meta', array() );

		$defined_cats = function_exists( 'adn_calculator_categories' ) ? adn_calculator_categories() : array();

		foreach ( $registry as $key => $calc ) {
			$meta = ( isset( $meta_all[ $key ] ) && is_array( $meta_all[ $key ] ) ) ? $meta_all[ $key ] : array();
			if ( array_key_exists( 'enabled', $meta ) && empty( $meta['enabled'] ) ) { continue; }
			if ( ! empty( $meta['hidden_from_listing'] ) ) { continue; }

			$cats = isset( $meta['categories'] ) && is_array( $meta['categories'] ) ? $meta['categories'] : array();
			foreach ( $cats as $c ) {
				$c = sanitize_key( $c );
				if ( '' !== $c && ! isset( $defined_cats[ $c ] ) ) {
					$defined_cats[ $c ] = ucwords( str_replace( '-', ' ', $c ) );
				}
			}
		}

		$cat_counts = array_fill_keys( array_keys( $defined_cats ), 0 );

		$all_tools = array();
		foreach ( $registry as $key => $calc ) {
			$meta = ( isset( $meta_all[ $key ] ) && is_array( $meta_all[ $key ] ) ) ? $meta_all[ $key ] : array();

			if ( array_key_exists( 'enabled', $meta ) && empty( $meta['enabled'] ) ) { continue; }
			if ( ! empty( $meta['hidden_from_listing'] ) ) { continue; }

			$cats = isset( $meta['categories'] ) && is_array( $meta['categories'] ) ? $meta['categories'] : array();
			$url  = ! empty( $meta['card_url'] )
				? (string) $meta['card_url']
				: adn_calc_page_url( $key );

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
				'title'      => $calc['title']??'',
				'desc'       => ! empty( $meta['desc'] )      ? (string) $meta['desc']      : '',
				'url'        => $url,
				'thumbnail'  => $thumb,
				'highlight'  => ! empty( $meta['highlight'] ) ? (string) $meta['highlight'] : '',
			);
		}

		$filter_tabs = array( array( 'key' => 'all', 'label' => adn_term( 'calculators_page.filter_all', 'All' ) ) );
		foreach ( $defined_cats as $ckey => $clabel ) {
			$filter_tabs[] = array( 'key' => $ckey, 'label' => $clabel );
		}

		$sidebar_cats = array( array( 'key' => 'all', 'label' => adn_term( 'calculators_page.filter_all', 'All' ), 'count' => count( $all_tools ) ) );
		foreach ( $defined_cats as $ckey => $clabel ) {
			$sidebar_cats[] = array( 'key' => $ckey, 'label' => $clabel , 'count' => isset( $cat_counts[ $ckey ] ) ? $cat_counts[ $ckey ] : 0 );
		}

		$help = array(
			'title'        => $pg_str( 'sidebar_help_title' ),
			'text'         => $pg_str( 'sidebar_help_text' ),
			'button_label' => $pg_str( 'sidebar_help_btn_label' ),
			'button_url'   => $pg_str( 'sidebar_help_btn_url' ),
		);

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
				'title'     => $pcalc['title'] ?? '',
				'desc'      => ! empty( $pmeta['desc'] )       ? (string) $pmeta['desc']       : '',
				'url'       => ! empty( $pmeta['card_url'] )   ? (string) $pmeta['card_url']   : adn_calc_page_url( $pk ),
				'thumbnail' => $pthumb,
				'highlight' => ! empty( $pmeta['highlight'] )  ? (string) $pmeta['highlight']  : '',
			);
		}

		$featured_tools = array();
		foreach ( $registry as $fk => $fcalc ) {
			$fmeta = ( isset( $meta_all[ $fk ] ) && is_array( $meta_all[ $fk ] ) ) ? $meta_all[ $fk ] : array();
			if ( array_key_exists( 'enabled', $fmeta ) && empty( $fmeta['enabled'] ) ) { continue; }
			if ( ! empty( $fmeta['hidden_from_listing'] ) ) { continue; }
			if ( empty( $fmeta['is_featured'] ) ) { continue; }
			$fthumb = '';
			if ( ! empty( $fmeta['thumbnail_id'] ) ) {
				$t = wp_get_attachment_image_url( (int) $fmeta['thumbnail_id'], 'large' );
				$fthumb = $t ? (string) $t : '';
			}
			$featured_tools[] = array(
				'key'            => $fk,
				'icon'           => ! empty( $fcalc['icon'] )       ? (string) $fcalc['icon']       : '🧮',
				'title'          => $fcalc['title'] ?? '',
				'desc'           => ! empty( $fmeta['desc'] )       ? (string) $fmeta['desc']       : '',
				'url'            => ! empty( $fmeta['card_url'] )   ? (string) $fmeta['card_url']   : adn_calc_page_url( $fk ),
				'thumbnail'      => $fthumb,
				'highlight'      => ! empty( $fmeta['highlight'] )  ? (string) $fmeta['highlight']  : '',
				'featured_title' => ! empty( $fmeta['featured_title'] ) ? (string) $fmeta['featured_title'] : '',
				'featured_desc'  => ! empty( $fmeta['featured_desc'] )  ? (string) $fmeta['featured_desc']  : '',
				'benefit_1'      => ! empty( $fmeta['benefit_1'] )      ? (string) $fmeta['benefit_1']      : '',
				'benefit_2'      => ! empty( $fmeta['benefit_2'] )      ? (string) $fmeta['benefit_2']      : '',
				'benefit_3'      => ! empty( $fmeta['benefit_3'] )      ? (string) $fmeta['benefit_3']      : '',
				'benefit_4'      => ! empty( $fmeta['benefit_4'] )      ? (string) $fmeta['benefit_4']      : '',
			);
		}

		$suggested_tools = array();
		foreach ( $registry as $sk => $scalc ) {
			$smeta = ( isset( $meta_all[ $sk ] ) && is_array( $meta_all[ $sk ] ) ) ? $meta_all[ $sk ] : array();
			if ( array_key_exists( 'enabled', $smeta ) && empty( $smeta['enabled'] ) ) { continue; }
			if ( ! empty( $smeta['hidden_from_listing'] ) ) { continue; }
			if ( empty( $smeta['is_suggestion'] ) ) { continue; }
			$suggested_tools[] = array(
				'key'       => $sk,
				'icon'      => ! empty( $scalc['icon'] )       ? (string) $scalc['icon']       : '🧮',
				'title'     => $scalc['title'] ?? '',
				'desc'      => ! empty( $smeta['desc'] )       ? (string) $smeta['desc']       : '',
				'url'       => ! empty( $smeta['card_url'] )   ? (string) $smeta['card_url']   : adn_calc_page_url( $sk ),
				'benefits'  => array_values( array_filter( array(
					! empty( $smeta['benefit_1'] ) ? (string) $smeta['benefit_1'] : '',
					! empty( $smeta['benefit_2'] ) ? (string) $smeta['benefit_2'] : '',
					! empty( $smeta['benefit_3'] ) ? (string) $smeta['benefit_3'] : '',
					! empty( $smeta['benefit_4'] ) ? (string) $smeta['benefit_4'] : '',
				) ) ),
			);
		}

		if ( count( $suggested_tools ) > 1 ) {
			shuffle( $suggested_tools );
			$suggested_tools = array_slice( $suggested_tools, 0, 1 );
		}

		$find_cta = array(
			'title'        => $pg_str( 'find_cta_title' ),
			'description'  => $pg_str( 'find_cta_desc' ),
			'button_label' => $pg_str( 'find_cta_btn_label' ),
			'button_url'   => $pg_str( 'find_cta_btn_url' ),
		);

		$breadcrumb = array(
			array( 'label' => PAGE_TITLE_HOME, 'url' => '/' ),
			array( 'label' => SITE_TOOLS_PLURAL, 'url' => null ),
		);

		$_hd   = function_exists( 'adn_service_home_data' ) ? (array) adn_service_home_data() : array();
		$_hnews = ( isset( $_hd['news'] ) && is_array( $_hd['news'] ) )       ? $_hd['news']       : array();
		$_hreg  = ( isset( $_hd['regulations'] ) && is_array( $_hd['regulations'] ) ) ? $_hd['regulations'] : array();
		$_hht   = ( isset( $_hd['hot_topics'] ) && is_array( $_hd['hot_topics'] ) )   ? $_hd['hot_topics']  : array();

		$ctx = array(
			'meta'          => array(),
			'breadcrumb'    => $breadcrumb,
			'hero'          => $hero,
			'trust_items'   => $trust_items,
			'search'        => $search,
			'sidebar'       => array(
				'categories' => $sidebar_cats,
				'help'       => $help,
				'sections'   => array(
					array(
						'heading' => $pg_str( 'sidebar_hl1_heading' ),
						'links'   => isset( $pg['sidebar_hl1_items'] ) && is_array( $pg['sidebar_hl1_items'] ) ? $pg['sidebar_hl1_items'] : array(),
					),
					array(
						'heading' => $pg_str( 'sidebar_hl2_heading' ),
						'links'   => isset( $pg['sidebar_hl2_items'] ) && is_array( $pg['sidebar_hl2_items'] ) ? $pg['sidebar_hl2_items'] : array(),
					),
				),
			),
			'filter_tabs'   => $filter_tabs,
			'popular_tools' => $popular_tools,
			'featured_tools' => $featured_tools,
			'suggested_tools' => $suggested_tools,
			'all_tools'     => $all_tools,
			'find_cta'      => $find_cta,
			'newsletter'    => array(
				'icon'         => '🔧',
				'title'        => defined( 'SITE_NEWSLETTER_TITLE' ) ? SITE_NEWSLETTER_TITLE : 'Stay Informed',
				'description'  => defined( 'SITE_NEWSLETTER_DESC' )  ? SITE_NEWSLETTER_DESC  : 'Get the latest tools and health updates delivered to your inbox.',
				'placeholder'  => defined( 'SITE_NEWSLETTER_PH' )    ? SITE_NEWSLETTER_PH    : 'Your email address',
				'button_label' => defined( 'SITE_BTN_SUBSCRIBE' )    ? SITE_BTN_SUBSCRIBE    : 'Subscribe',
				'note'         => defined( 'SITE_NEWSLETTER_NOTE' )  ? SITE_NEWSLETTER_NOTE  : 'No spam. Unsubscribe anytime.',
			),
			'chrome'        => $chrome,
			'latest_news'   => array(
				'heading' => array(
					'title'      => adn_term( 'labels.latest_news', 'Latest News' ),
					'link_label' => adn_term( 'buttons.view_all', 'View all →' ),
					'link_url'   => defined( 'SITE_NEWS_URL' ) ? SITE_NEWS_URL : '/',
				),
				'items' => adn_shared_latest_news_items( 3 ),
			),
			'news'        => array_merge( $_hnews, array( 'items' => \Adn\Theme\Service\HomeContext::cmsNewsItems() ) ),
			'regulations' => array_merge( $_hreg,  array( 'items' => \Adn\Theme\Service\HomeContext::cmsRegulationsItems() ) ),
			'hot_topics'  => array_merge( $_hht,   array( 'items' => \Adn\Theme\Service\HomeContext::cmsHotTopicsItems() ) ),
		);

		if ( class_exists( 'ADN_Cache' ) ) {
			\ADN_Cache::set( $cache_key, $ctx, 'pages', get_option( 'ah_cache_expiry', 3600 ) );
		}
		return $ctx;
	}
}
