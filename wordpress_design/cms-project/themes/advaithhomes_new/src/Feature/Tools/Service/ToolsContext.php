<?php
namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

/**
 * ToolsContext - Builds context for the tools/calculators listing page.
 *
 * Big getContext() is split into small focused methods.
 */
class ToolsContext {

	// ── Cache helpers ───────────────────────────────────────────
	private static function cacheGet( string $key ) {
		if ( class_exists( 'ADN_Cache' ) ) {
			return \ADN_Cache::get( $key, 'pages' );
		}
		return false;
	}

	private static function cacheSet( string $key, array $ctx ): void {
		if ( class_exists( 'ADN_Cache' ) ) {
			\ADN_Cache::set( $key, $ctx, 'pages', get_option( 'ah_cache_expiry', 3600 ) );
		}
	}

	// ── Hero section ────────────────────────────────────────────
	public static function buildHero( array $pg, array $gen ): array {
		$first = function () {
			foreach ( func_get_args() as $v ) {
				if ( '' !== $v && null !== $v && false !== $v ) { return $v; }
			}
			return '';
		};
		$pg_str = function ( $key ) use ( $pg ) {
			return ( isset( $pg[ $key ] ) && '' !== $pg[ $key ] ) ? (string) $pg[ $key ] : '';
		};

		$_glass_raw = $gen['glass_cards'] ?? '';
		$_glass_cards = array();
		if ( '' !== $_glass_raw ) {
			foreach ( explode( "\n", (string) $_glass_raw ) as $_line ) {
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

		return array(
			'eyebrow'     => $gen['subheading'] ?? '',
			'title'       => $first( $pg_str( 'hero_title' ), $gen['main_heading'] ?? '', sprintf( adn_term( 'calculators_page.hero_title', 'All %s' ), SITE_TOOLS_PLURAL ) ),
			'description' => $first( $pg_str( 'hero_desc' ), $gen['intro'] ?? '' ),
			'bg_icon'     => $first( $pg_str( 'hero_icon' ), adn_term( 'icons.tools_hero', '🏠🧮' ) ),
			'bg_url'      => $gen['thumbnail'] ?? '',
			'glass_cards' => $_glass_cards,
		);
	}

	// ── Trust bar section ───────────────────────────────────────
	public static function buildTrustBar(): array {
		return array(
			array( 'icon' => '⏱', 'title' => adn_term( 'calculators_page.trust_bar.item1_title', 'Accurate & Up to Date' ), 'subtitle' => adn_term( 'calculators_page.trust_bar.item1_desc', 'Based on latest UK data' ) ),
			array( 'icon' => '✓',  'title' => adn_term( 'calculators_page.trust_bar.item2_title', 'Free to Use' ),           'subtitle' => adn_term( 'calculators_page.trust_bar.item2_desc', 'No sign-up required' ) ),
			array( 'icon' => '🔒', 'title' => adn_term( 'calculators_page.trust_bar.item3_title', 'Private & Secure' ),       'subtitle' => adn_term( 'calculators_page.trust_bar.item3_desc', 'Your data stays on your device' ) ),
		);
	}

	// ── Search bar section ──────────────────────────────────────
	public static function buildSearchBar(): array {
		return array(
			'placeholder' => adn_term( 'calculators_page.search_placeholder', 'Search calculators...' ),
		);
	}

	// ── Categories section ──────────────────────────────────────
	public static function buildCategories(): array {
		$categories = array();
		$registry   = function_exists( 'adn_calculators' ) ? adn_calculators() : array();
		$meta_all   = get_option( 'adn_calculators_meta', array() );
		$cat_map    = array();

		foreach ( $registry as $key => $calc ) {
			$meta  = $meta_all[ $key ] ?? array();
			$cat   = $meta['category'] ?? 'general';
			$label = $meta['category_label'] ?? ucwords( str_replace( '-', ' ', $cat ) );
			if ( ! isset( $cat_map[ $cat ] ) ) {
				$cat_map[ $cat ] = array( 'slug' => $cat, 'label' => $label, 'count' => 0 );
			}
			$cat_map[ $cat ]['count']++;
		}
		return array_values( $cat_map );
	}

	// ── Tools items section ─────────────────────────────────────
	public static function buildToolsItems(): array {
		$registry = function_exists( 'adn_calculators' ) ? adn_calculators() : array();
		$meta_all = get_option( 'adn_calculators_meta', array() );
		$items    = array();

		foreach ( $registry as $key => $calc ) {
			$meta = $meta_all[ $key ] ?? array();
			if ( ! empty( $meta['hidden_from_listing'] ) ) { continue; }

			$_thumb = '';
			if ( ! empty( $meta['thumbnail_id'] ) ) {
				$_t = wp_get_attachment_image_url( (int) $meta['thumbnail_id'], 'medium' );
				$_thumb = $_t ? (string) $_t : '';
			}

			$items[] = array(
				'icon'      => ! empty( $calc['icon'] ) ? (string) $calc['icon'] : adn_term( 'icons.tools', '🧮' ),
				'name'      => $calc['title'] ?? '',
				'desc'      => $meta['desc'] ?? '',
				'url'       => $meta['card_url'] ?? adn_calc_page_url( $key ),
				'thumbnail' => $_thumb,
				'highlight' => $meta['highlight'] ?? '',
				'category'  => $meta['category'] ?? 'general',
				'popular'   => ! empty( $meta['is_popular'] ),
			);
		}
		return $items;
	}

	// ── Popular tools section ───────────────────────────────────
	public static function buildPopularTools(): array {
		$all = self::buildToolsItems();
		$popular = array_filter( $all, fn( $item ) => $item['popular'] );
		return array_values( $popular );
	}

	// ── Main getContext ─────────────────────────────────────────
	public static function getContext() {
		$cache_key = 'page_tools_context';
		$cached = self::cacheGet( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$pg     = get_option( 'adn_calculators_page', array() );
		$gen    = get_option( 'adn_calculators_general', array() );
		$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();

		$ctx = array(
			'chrome'       => is_array( $chrome ) ? $chrome : array(),
			'hero'         => self::buildHero( $pg, $gen ),
			'trust_bar'    => self::buildTrustBar(),
			'search_bar'   => self::buildSearchBar(),
			'categories'   => self::buildCategories(),
			'all_tools'    => self::buildToolsItems(),
			'popular_tools' => self::buildPopularTools(),
			'breadcrumb'   => array(
				array( 'label' => PAGE_TITLE_HOME, 'url' => '/' ),
				array( 'label' => SITE_TOOLS_PLURAL, 'url' => null ),
			),
		);

		self::cacheSet( $cache_key, $ctx );
		return $ctx;
	}
}
