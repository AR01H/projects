<?php

namespace Adn\Theme\Feature\Tools\Controller;

defined( 'ABSPATH' ) || exit;

class ToolsController {

	public static function getContext(): array {
		$cache_key = 'page_tools_context';
		if ( \class_exists( 'ADN_Cache' ) ) {
			$cached = \ADN_Cache::get( $cache_key, 'pages' );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		$pg     = \get_option( '\adn_calculators_page', array() );
		$gen    = \get_option( '\adn_calculators_general', array() );
		$chrome = \function_exists( '\adn_service_site_chrome' ) ? \adn_service_site_chrome() : array();

		$pg_str = function( $key ) use ( $pg ) {
			return ( isset( $pg[ $key ] ) && '' !== $pg[ $key ] ) ? (string) $pg[ $key ] : '';
		};

		$first = function() {
			foreach ( func_get_args() as $v ) {
				if ( '' !== $v && null !== $v && false !== $v ) {
					return $v;
				}
			}
			return '';
		};

		// Hero
		$_glass_cards_raw = isset( $gen['glass_cards'] ) ? trim( (string) $gen['glass_cards'] ) : '';
		$_glass_cards     = array();
		if ( '' !== $_glass_cards_raw ) {
			foreach ( explode( "\n", $_glass_cards_raw ) as $_line ) {
				$_line = trim( $_line );
				if ( '' === $_line ) {
					continue;
				}
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
			'title'       => $first( $pg_str( 'hero_title' ), isset( $gen['main_heading'] ) ? (string) $gen['main_heading'] : '', sprintf( \adn_term( 'calculators_page.hero_title', 'All %s' ), SITE_TOOLS_PLURAL ) ),
			'description' => $first( $pg_str( 'hero_desc' ),  isset( $gen['intro'] )        ? (string) $gen['intro']        : '' ),
			'bg_icon'     => $first( $pg_str( 'hero_icon' ),  \adn_term( 'icons.tools_hero', '🏠🧮' ) ),
			'bg_url'      => isset( $gen['thumbnail'] ) ? (string) $gen['thumbnail'] : '',
			'glass_cards' => $_glass_cards,
		);

		// Trust bar
		$trust_defaults = array(
			array( 'icon' => '⏱', 'title' => \adn_term( 'calculators_page.trust_bar.item1_title', 'Accurate & Up to Date' ), 'subtitle' => \adn_term( 'calculators_page.trust_bar.item1_desc', 'Based on latest UK data' ) ),
			array( 'icon' => '✓',  'title' => \adn_term( 'calculators_page.trust_bar.item2_title', 'Free to Use' ),           'subtitle' => \adn_term( 'calculators_page.trust_bar.item2_desc', 'No sign-up required' ) ),
			array( 'icon' => '≡',  'title' => \adn_term( 'calculators_page.trust_bar.item3_title', 'Easy to Understand' ),    'subtitle' => \adn_term( 'calculators_page.trust_bar.item3_desc', 'Simple, clear results' ) ),
			array( 'icon' => '◎',  'title' => \adn_term( 'calculators_page.trust_bar.item4_title', 'Independent' ),           'subtitle' => \adn_term( 'calculators_page.trust_bar.item4_desc', 'Unbiased information' ) ),
		);
		$trust_items = array();
		for ( $i = 1; $i <= 4; $i++ ) {
			$idx     = $i - 1;
			$t_item  = $trust_defaults[ $idx ];
			$t_title = $pg_str( 'trust_bar_' . $i . '_title' );
			$t_desc  = $pg_str( 'trust_bar_' . $i . '_desc' );
			if ( '' !== $t_title ) {
				$t_item['title'] = $t_title;
			}
			if ( '' !== $t_desc ) {
				$t_item['subtitle'] = $t_desc;
			}
			$trust_items[] = $t_item;
		}

		// Categories
		$categories = array(
			array( 'key' => 'all', 'label' => sprintf( \adn_term( 'calculators_page.filter_all', 'All %s' ), SITE_TOOLS_PLURAL ), 'icon' => '🧮', 'active' => true ),
		);

		// Calculator items
		$all_items = array();
		if ( \function_exists( '\adn_calculators' ) ) {
			$_meta_all = \get_option( '\adn_calculators_meta', array() );
			$_seen_cats = array();
			foreach ( \adn_calculators() as $_k => $_calc ) {
				$_m = ( isset( $_meta_all[ $_k ] ) && is_array( $_meta_all[ $_k ] ) ) ? $_meta_all[ $_k ] : array();
				if ( array_key_exists( 'enabled', $_m ) && empty( $_m['enabled'] ) ) {
					continue;
				}
				if ( ! empty( $_m['hidden_from_listing'] ) ) {
					continue;
				}
				$_thumb = '';
				if ( ! empty( $_m['thumbnail_id'] ) ) {
					$_t = \wp_get_attachment_image_url( (int) $_m['thumbnail_id'], 'medium' );
					$_thumb = $_t ? (string) $_t : '';
				}
				$_cat = isset( $_m['category'] ) ? sanitize_key( (string) $_m['category'] ) : 'all';
				if ( '' === $_cat ) {
					$_cat = 'all';
				}
				if ( ! isset( $_seen_cats[ $_cat ] ) ) {
					$_seen_cats[ $_cat ] = ucwords( str_replace( '-', ' ', $_cat ) );
				}
				$all_items[] = array(
					'key'       => $_k,
					'icon'      => ! empty( $_calc['icon'] )      ? (string) $_calc['icon']      : \adn_term( 'icons.tools', '🧮' ),
					'name'      => $_calc['title'] ?? '',
					'url'       => ! empty( $_m['card_url'] )  ? (string) $_m['card_url']  : \adn_calc_page_url( $_k ),
					'thumbnail' => $_thumb,
					'highlight' => ! empty( $_m['highlight'] ) ? (string) $_m['highlight'] : '',
					'desc'      => $_m['desc'] ?? '',
					'category'  => $_cat,
					'is_popular' => ! empty( $_m['is_popular'] ),
				);
			}
			arsort( $_seen_cats );
			foreach ( $_seen_cats as $_ck => $_cl ) {
				$categories[] = array( 'key' => $_ck, 'label' => $_cl, 'icon' => '🧮' );
			}
		}

		// Popular items
		$popular_items = array();
		foreach ( $all_items as $_item ) {
			if ( ! empty( $_item['is_popular'] ) ) {
				$popular_items[] = $_item;
			}
		}

		// Sidebar
		$sidebar = array(
			'categories' => array(),
		);
		if ( \function_exists( '\adn_cms_guide_parents' ) ) {
			foreach ( \adn_cms_guide_parents( 6 ) as $_parent ) {
				$_slug = isset( $_parent->slug ) ? (string) $_parent->slug : '';
				$_name = isset( $_parent->name ) ? (string) $_parent->name : '';
				if ( '' === $_slug || '' === $_name ) {
					continue;
				}
				$sidebar['categories'][] = array(
					'icon'  => ! empty( $_parent->icon_emoji ) ? (string) $_parent->icon_emoji : \adn_term( 'icons.guide_parent', '📚' ),
					'label' => $_name,
					'url'   => '/' . $_slug . '/',
				);
			}
		}

		// Search bar
		$search_bar = array(
			'placeholder' => sprintf( \adn_term( 'calculators_page.search_placeholder', 'Search %s...' ), SITE_TOOLS_PLURAL ),
		);

		$ctx = array(
			'meta'       => array(
				'page_title'       => sprintf( \adn_term( 'calculators_page.page_title', 'All %s' ), SITE_TOOLS_PLURAL ),
				'meta_description' => $first( $pg_str( 'meta_desc' ), '' ),
			),
			'breadcrumb' => array(
				array( 'label' => PAGE_TITLE_HOME, 'url' => \home_url( '/' ) ),
				array( 'label' => sprintf( SITE_LABEL_ALL_PREFIX, SITE_TOOLS_PLURAL ), 'url' => null ),
			),
			'hero'       => $hero,
			'trust_bar'  => $trust_items,
			'search_bar' => $search_bar,
			'categories' => $categories,
			'popular'    => $popular_items,
			'all_items'  => $all_items,
			'sidebar'    => $sidebar,
			'chrome'     => $chrome,
		);

		if ( \class_exists( 'ADN_Cache' ) ) {
			\ADN_Cache::set( $cache_key, $ctx, 'pages', \get_option( 'ah_cache_expiry', 3600 ) );
		}
		return $ctx;
	}
}
