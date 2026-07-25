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
				$cat_map[ $cat ] = array( 'key' => $cat, 'slug' => $cat, 'label' => $label, 'count' => 0 );
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
				'icon'       => ! empty( $calc['icon'] ) ? (string) $calc['icon'] : adn_term( 'icons.tools', '🧮' ),
				'name'       => $calc['title'] ?? '',
				'title'      => $calc['title'] ?? '',
				'categories' => isset( $meta['categories'] ) && is_array( $meta['categories'] ) ? $meta['categories'] : ( ! empty( $meta['category'] ) ? array( $meta['category'] ) : array( 'general' ) ),
				'desc'       => $meta['desc'] ?? '',
				'url'        => ! empty( $meta['card_url'] ) ? (string) $meta['card_url'] : adn_calc_page_url( $key ),
				'thumbnail'  => $_thumb,
				'highlight'  => $meta['highlight'] ?? '',
				'category'   => $meta['category'] ?? 'general',
				'popular'    => ! empty( $meta['is_popular'] ),
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
	/**
	 * @param string|null $breadcrumb_builder    BreadcrumbBuilder class name or null for default.
	 * @param string|null $newsletter_builder    NewsletterBuilder class name or null for default.
	 * @param string|null $related_posts_builder RelatedPostsBuilder class name or null for default.
	 */
	public static function getContext( $breadcrumb_builder = null, $newsletter_builder = null, $related_posts_builder = null ) {
		$breadcrumb_builder   = $breadcrumb_builder ?: \Adn\Theme\Shared\BreadcrumbBuilder::class;
		$newsletter_builder   = $newsletter_builder ?: \Adn\Theme\Shared\NewsletterBuilder::class;
		$related_posts_builder = $related_posts_builder ?: \Adn\Theme\Shared\RelatedPostsBuilder::class;
		$cache_key = 'page_tools_context';
		$cached = self::cacheGet( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$pg     = get_option( 'adn_calculators_page', array() );
		$gen    = get_option( 'adn_calculators_general', array() );
		$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();

		$all_tools = self::buildToolsItems();
		$popular   = self::buildPopularTools();

		// Featured & suggested tools for the featured section
		$featured_tools = array();
		$suggested_tools = array();
		$meta_all = get_option( 'adn_calculators_meta', array() );
		foreach ( $all_tools as $item ) {
			if ( empty( $featured_tools ) && ! empty( $item['highlight'] ) ) {
				$featured_tools[] = $item;
			}
		}
		// Pick a suggested tool (first popular that isn't the featured one)
		foreach ( $popular as $item ) {
			if ( empty( $featured_tools ) || $item['url'] !== ( $featured_tools[0]['url'] ?? '' ) ) {
				$suggested_tools[] = $item;
				break;
			}
		}

		// Filter tabs from categories
		$categories = self::buildCategories();
		$filter_tabs = array_merge(
			array( array( 'key' => 'all', 'slug' => 'all', 'label' => 'All', 'count' => count( $all_tools ) ) ),
			$categories
		);

		// News / regulations / hot topics from JSON service + CMS
		$_home_data = function_exists( 'adn_service_home_data' ) ? (array) adn_service_home_data() : array();
		$_hnews = ( isset( $_home_data['news'] ) && is_array( $_home_data['news'] ) ) ? $_home_data['news'] : array();
		$_hreg  = ( isset( $_home_data['regulations'] ) && is_array( $_home_data['regulations'] ) ) ? $_home_data['regulations'] : array();
		$_hht   = ( isset( $_home_data['hot_topics'] ) && is_array( $_home_data['hot_topics'] ) ) ? $_home_data['hot_topics'] : array();

		$news_items = $related_posts_builder::newsItems( 5, 'full' );

		$reg_items = array();
		if ( function_exists( 'adn_home_cms_regulations_items' ) ) {
			$reg_items = adn_home_cms_regulations_items();
		}

		$ht_items = array();
		if ( function_exists( 'adn_home_cms_hot_topics_items' ) ) {
			$ht_items = adn_home_cms_hot_topics_items();
		}

		$ctx = array(
			'chrome'          => is_array( $chrome ) ? $chrome : array(),
			'hero'            => self::buildHero( $pg, $gen ),
			'trust_bar'       => self::buildTrustBar(),
			'search_bar'      => self::buildSearchBar(),
			'categories'      => $categories,
			'all_tools'       => $all_tools,
			'popular_tools'   => $popular,
			'featured_tools'  => $featured_tools,
			'suggested_tools' => $suggested_tools,
			'filter_tabs'     => $filter_tabs,
			'find_cta'        => array(
				'label' => adn_term( 'calculators_page.find_cta', 'Find the Right Calculator' ),
				'url'   => defined( 'SITE_CALCULATORS_URL' ) ? SITE_CALCULATORS_URL : '/calculators/',
			),
			'news'            => array_merge( $_hnews, array( 'items' => $news_items ) ),
			'regulations'     => array_merge( $_hreg, array( 'items' => $reg_items ) ),
			'hot_topics'      => array_merge( $_hht, array( 'items' => $ht_items ) ),
			'breadcrumb'      => $breadcrumb_builder::toolsListing(),
			'newsletter'      => $newsletter_builder::cta(),
		);

		self::cacheSet( $cache_key, $ctx );
		return $ctx;
	}
}
