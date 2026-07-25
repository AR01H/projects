<?php
namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

/**
 * CategoryContext - Builds the full context array for category pages.
 *
 * Big getContext() is split into small focused methods for reusability.
 */
class CategoryContext {

	// ── Repository ──────────────────────────────────────────────
	public static function repository() {
		static $repo = null;
		if ( null === $repo ) {
			$repo = new \Adn\Theme\Repository\CategoryRepository();
		}
		return $repo;
	}

	// ── Slug resolution ─────────────────────────────────────────
	public static function resolveSlug( $slug = '' ): string {
		if ( '' === $slug ) {
			$qv = (string) get_query_var( 'adn_cat_slug', '' );
			if ( '' !== $qv ) {
				$slug = $qv;
			} else {
				$page = get_queried_object();
				$slug = ( $page instanceof \WP_Post ) ? (string) $page->post_name : '';
			}
		}
		return sanitize_key( $slug );
	}

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

	// ── Hero data ───────────────────────────────────────────────
	public static function buildHero( string $slug, array $term, array $cs_all ): array {
		$name = ! empty( $term['name'] ) ? (string) $term['name'] : ucwords( str_replace( '-', ' ', $slug ) );
		$desc = $term['description'] ?? '';
		$icon = $term['icon_emoji'] ?? '';
		$img  = ! empty( $term['image_id'] ) ? (int) $term['image_id'] : 0;

		$cs_app      = $cs_all['appearance'] ?? array();
		$thumb_id    = ! empty( $cs_app['thumbnail_id'] ) ? (int) $cs_app['thumbnail_id'] : 0;
		$cs_mq       = $cs_all['marquee'] ?? array();
		$mq_parsed   = function_exists( 'adn_parse_marquee_settings' ) ? adn_parse_marquee_settings( $cs_mq ) : null;
		if ( ! $mq_parsed ) {
			$home_s    = get_option( 'adn_home_sections', array() );
			$mq_parsed = function_exists( 'adn_parse_marquee_settings' ) ? adn_parse_marquee_settings( $home_s ) : null;
		}
		$trust_items = $mq_parsed['trust'] ?? array();

		return array(
			'title'       => $name,
			'description' => $desc,
			'image_icon'  => $icon,
			'image_id'    => $thumb_id ?: $img,
			'trust_items' => $trust_items,
		);
	}

	// ── Meta + breadcrumb ───────────────────────────────────────
	public static function buildMeta( string $slug, string $name, string $desc ): array {
		return array(
			'slug'             => $slug,
			'page_title'       => $name . ' - ' . SITE_BRAND_NAME,
			'meta_description' => $desc,
		);
	}

	public static function buildBreadcrumb( string $name ): array {
		return array(
			array( 'label' => PAGE_TITLE_HOME, 'url' => '/' ),
			array( 'label' => $name,           'url' => null ),
		);
	}

	// ── Guides section ──────────────────────────────────────────
	public static function buildGuides( string $slug ): array {
		return array(
			'heading' => array(
				'title'      => sprintf( adn_term( 'category_page.explore_guides_title', 'Explore %s' ), adn_term( 'taxonomy.parent_plural', 'Guides' ) ),
				'link_label' => adn_term( 'content.view_all_guides', 'View all →' ),
				'link_url'   => SITE_GUIDES_URL,
			),
			'items' => self::cmsGuides( $slug ),
		);
	}

	// ── Regulations / News section ──────────────────────────────
	public static function buildRegulations( string $slug ): array {
		return array(
			'heading' => array(
				'title'      => adn_term( 'category_page.latest_updates_title', 'Latest Updates' ),
				'link_label' => adn_term( 'category_page.latest_updates_view_all', 'View all →' ),
				'link_url'   => SITE_NEWS_URL,
			),
			'items' => self::latestUpdates( $slug, 5 ),
		);
	}

	// ── Journey section ─────────────────────────────────────────
	public static function buildJourney( array $cs_all, string $name ): array {
		$cs_journey = $cs_all['journey'] ?? array();
		if ( empty( $cs_journey['steps'] ) || ! is_array( $cs_journey['steps'] ) ) {
			return array();
		}

		$steps = array();
		foreach ( $cs_journey['steps'] as $s ) {
			if ( empty( $s['label'] ) ) { continue; }
			$steps[] = array(
				'icon'   => $s['icon'] ?? '',
				'num'    => (string) ( count( $steps ) + 1 ),
				'label'  => (string) $s['label'],
				'desc'   => $s['desc'] ?? '',
				'url'    => $s['url'] ?? '',
				'active' => ( 0 === count( $steps ) ),
			);
		}

		$tip = array();
		if ( ! empty( $cs_journey['tip_text'] ) ) {
			$tip = array(
				'icon'       => $cs_journey['tip_icon'] ?? '💡',
				'text'       => (string) $cs_journey['tip_text'],
				'link_label' => $cs_journey['tip_link_label'] ?? '',
				'link_url'   => $cs_journey['tip_link_url'] ?? '',
			);
		}

		return array(
			'heading' => $cs_journey['heading'] ?? sprintf( adn_term( 'category_page.journey_heading', 'Your %s Journey' ), $name ),
			'steps'   => $steps,
			'tip'     => $tip,
		);
	}

	// ── Calculators section ─────────────────────────────────────
	public static function buildCalculators( string $slug, string $name, array $cs_all ): array {
		$cs_calc = $cs_all['calculators'] ?? array();
		if ( ! function_exists( 'adn_get_parent_term_calculator_cards' ) ) {
			return array();
		}
		$items = adn_get_parent_term_calculator_cards( $slug );
		if ( empty( $items ) ) {
			return array();
		}
		return array(
			'heading' => array(
				'title'      => $cs_calc['heading'] ?? sprintf( adn_term( 'category_page.calculators_heading', '%s for %s' ), SITE_TOOLS_PLURAL, $name ),
				'link_label' => adn_term( 'category_page.related_tools_heading', 'View all →' ),
				'link_url'   => SITE_CALCULATORS_URL,
			),
			'items' => array_map( static function( $item ) {
				return array(
					'icon'      => $item['icon'],
					'name'      => $item['label'],
					'desc'      => $item['desc'] ?? '',
					'url'       => $item['url'],
					'thumbnail' => $item['thumbnail'],
					'highlight' => $item['highlight'] ?? '',
				);
			}, $items ),
		);
	}

	// ── Sidebar section ─────────────────────────────────────────
	public static function buildSidebar( string $slug, array $cs_all ): array {
		$cs_sidebar = $cs_all['sidebar'] ?? array();
		$cs_pp      = $cs_all['popular_posts'] ?? array();
		$cs_ft      = $cs_all['featured_topics'] ?? array();
		$cs_ht      = $cs_all['hot_topics'] ?? array();
		$cs_sp      = $cs_all['spotlights'] ?? array();

		$sidebar = array();
		$sidebar['groups'] = function_exists( 'adn_get_all_parent_terms_for_sidebar' )
			? adn_get_all_parent_terms_for_sidebar( $slug )
			: array();
		$sidebar['popular_posts'] = $cs_pp;
		$sidebar['featured_topics'] = $cs_ft;
		$sidebar['hot_topics'] = $cs_ht;
		$sidebar['calculators'] = $cs_all['calculators'] ?? array();
		$sidebar['spotlights'] = $cs_sp;
		$sidebar['news'] = array(
			'heading' => $cs_sidebar['news_heading'] ?? adn_term( 'sidebar.latest_news', 'Latest News' ),
			'items' => self::cmsNews( $slug, 4 ),
		);
		$sidebar['contact'] = $cs_sidebar['contact'] ?? array();
		return $sidebar;
	}

	// ── CTA section ─────────────────────────────────────────────
	public static function buildCta( array $cs_all ): array {
		return $cs_all['cta_banner'] ?? array();
	}

	// ── FAQs section ────────────────────────────────────────────
	public static function buildFaqs( array $cs_all ): array {
		return $cs_all['faqs'] ?? array();
	}

	// ── Main getContext ─────────────────────────────────────────
	public static function getContext( $slug = '' ) {
		$slug = self::resolveSlug( $slug );
		$cache_key = 'page_category_context_' . $slug;

		$cached = self::cacheGet( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$repo   = self::repository();
		$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();
		$term   = $repo->get_parent_term_by_slug( $slug );
		$term_data = $term ? (array) $term : array();
		$name   = $term_data['name'] ?? ucwords( str_replace( '-', ' ', $slug ) );
		$desc   = $term_data['description'] ?? '';

		$cs_all = class_exists( 'AH_Category_Settings' ) ? \AH_Category_Settings::get_all( $slug ) : array();

		$ctx = array(
			'slug'          => $slug,
			'meta'          => self::buildMeta( $slug, $name, $desc ),
			'breadcrumb'    => self::buildBreadcrumb( $name ),
			'hero'          => self::buildHero( $slug, $term_data, $cs_all ),
			'guides'        => self::buildGuides( $slug ),
			'regulations'   => self::buildRegulations( $slug ),
			'journey'       => self::buildJourney( $cs_all, $name ),
			'calculators'   => self::buildCalculators( $slug, $name, $cs_all ),
			'sidebar'       => self::buildSidebar( $slug, $cs_all ),
			'cta_banner'    => self::buildCta( $cs_all ),
			'faqs'          => self::buildFaqs( $cs_all ),
			'spotlights'    => $cs_all['spotlights'] ?? array(),
			'chrome'        => $chrome,
		);

		self::cacheSet( $cache_key, $ctx );
		return $ctx;
	}

	// ── CMS guides data ─────────────────────────────────────────
	public static function cmsGuides( $slug ) {
		if ( ! function_exists( 'adn_cms_guides_by_category' ) ) {
			return array();
		}
		$repo = self::repository();
		$topic_ids = array();
		if ( $slug !== '' && function_exists( 'adn_cms_available' ) && adn_cms_available() ) {
			$parent = $repo->get_parent_term_by_slug( $slug );
			if ( $parent && function_exists( 'adn_cms_topics' ) ) {
				$children = adn_cms_topics( (int) $parent->id, 50 );
				foreach ( (array) $children as $child ) {
					if ( ! empty( $child->id ) ) {
						$topic_ids[] = (int) $child->id;
					}
				}
			}
			if ( empty( $topic_ids ) && $parent ) {
				$topic_ids = $repo->get_child_topic_ids( (int) $parent->id );
			}
			if ( ! empty( $parent ) && empty( $topic_ids ) ) {
				return array();
			}
		}
		$rows  = adn_cms_guides_by_category( 1200, $topic_ids );
		$items = array();
		foreach ( $rows as $i => $post ) {
			$cat_name = isset( $post->category_name ) ? (string) $post->category_name : '';
			if ( '' === $cat_name ) { continue; }
			$_thumb = '';
			if ( ! empty( $post->ID ) ) {
				$_u = get_the_post_thumbnail_url( $post->ID, 'medium' );
				if ( ! $_u ) { $_u = get_the_post_thumbnail_url( $post->ID, 'full' ); }
				if ( $_u ) { $_thumb = (string) $_u; }
			}
			if ( empty( $_thumb ) && ! empty( $post->featured_image_id ) ) {
				$_u = wp_get_attachment_image_url( (int) $post->featured_image_id, 'medium' );
				if ( $_u ) { $_thumb = (string) $_u; }
			}
			$_desc = ! empty( $post->excerpt )
				? wp_trim_words( wp_strip_all_tags( $post->excerpt ), 15 )
				: wp_trim_words( wp_strip_all_tags( $post->content ?? '' ), 15 );
			$items[] = array(
				'thumbnail'   => $_thumb,
				'icon'        => '📋',
				'title'       => (string) $post->title,
				'url'         => function_exists( 'adn_cms_post_url' ) ? adn_cms_post_url( $post ) : '#',
				'description' => $_desc,
			);
		}
		if ( count( $items ) < 6 ) {
			$wp_posts = get_posts( array( 'numberposts' => 6 - count( $items ), 'post_status' => 'publish' ) );
			foreach ( $wp_posts as $p ) {
				$_thumb = get_the_post_thumbnail_url( $p->ID, 'medium' ) ?: get_the_post_thumbnail_url( $p->ID, 'full' );
				$_desc = ! empty( $p->post_excerpt ) ? wp_trim_words( wp_strip_all_tags( $p->post_excerpt ), 15 ) : wp_trim_words( wp_strip_all_tags( $p->post_content ), 15 );
				$items[] = array(
					'thumbnail'   => $_thumb ?: '',
					'icon'        => '📋',
					'title'       => get_the_title( $p->ID ),
					'url'         => get_permalink( $p->ID ),
					'description' => $_desc,
				);
			}
		}
		return $items;
	}

	// ── Latest updates / news ───────────────────────────────────
	public static function latestUpdates( $slug, $limit = 4 ) {
		$items = array();
		if ( function_exists( 'adn_cms_articles_for_parent' ) ) {
			$rows = adn_cms_articles_for_parent( $slug, $limit );
			foreach ( (array) $rows as $post ) {
				if ( empty( $post->title ) ) { continue; }
				$_thumb = '';
				if ( ! empty( $post->ID ) ) {
					$_u = get_the_post_thumbnail_url( $post->ID, 'medium' );
					if ( ! $_u ) { $_u = get_the_post_thumbnail_url( $post->ID, 'full' ); }
					if ( $_u ) { $_thumb = (string) $_u; }
				}
				if ( empty( $_thumb ) && ! empty( $post->featured_image_id ) ) {
					$_u = wp_get_attachment_image_url( (int) $post->featured_image_id, 'medium' );
					if ( $_u ) { $_thumb = (string) $_u; }
				}
				$_desc = ! empty( $post->excerpt ) ? wp_trim_words( wp_strip_all_tags( $post->excerpt ), 15 ) : wp_trim_words( wp_strip_all_tags( $post->content ?? '' ), 15 );
				$items[] = array(
					'thumbnail'   => $_thumb,
					'icon'        => '📋',
					'title'       => (string) $post->title,
					'url'         => function_exists( 'adn_cms_post_url' ) ? adn_cms_post_url( $post ) : '#',
					'description' => $_desc,
				);
			}
		}
		if ( count( $items ) < $limit ) {
			$wp_posts = get_posts( array( 'numberposts' => $limit - count( $items ), 'post_status' => 'publish' ) );
			foreach ( $wp_posts as $p ) {
				$_thumb = get_the_post_thumbnail_url( $p->ID, 'medium' ) ?: get_the_post_thumbnail_url( $p->ID, 'full' );
				$_desc = ! empty( $p->post_excerpt ) ? wp_trim_words( wp_strip_all_tags( $p->post_excerpt ), 15 ) : wp_trim_words( wp_strip_all_tags( $p->post_content ), 15 );
				$items[] = array(
					'thumbnail'   => $_thumb ?: '',
					'icon'        => '📋',
					'title'       => get_the_title( $p->ID ),
					'url'         => get_permalink( $p->ID ),
					'description' => $_desc,
				);
			}
		}
		return $items;
	}

	// ── CMS news data ───────────────────────────────────────────
	public static function cmsNews( $slug, $limit = 4 ) {
		if ( ! function_exists( 'adn_cms_articles_for_parent' ) ) {
			return array();
		}
		$items = array();
		$rows = adn_cms_articles_for_parent( $slug, $limit );
		foreach ( (array) $rows as $post ) {
			if ( empty( $post->title ) ) { continue; }
			$_thumb = '';
			if ( ! empty( $post->ID ) ) {
				$_u = get_the_post_thumbnail_url( $post->ID, 'medium' );
				if ( ! $_u ) { $_u = get_the_post_thumbnail_url( $post->ID, 'full' ); }
				if ( $_u ) { $_thumb = (string) $_u; }
			}
			if ( empty( $_thumb ) && ! empty( $post->featured_image_id ) ) {
				$_u = wp_get_attachment_image_url( (int) $post->featured_image_id, 'medium' );
				if ( $_u ) { $_thumb = (string) $_u; }
			}
			$_desc = ! empty( $post->excerpt ) ? wp_trim_words( wp_strip_all_tags( $post->excerpt ), 15 ) : wp_trim_words( wp_strip_all_tags( $post->content ?? '' ), 15 );
			$items[] = array(
				'thumbnail'   => $_thumb,
				'icon'        => '📰',
				'title'       => (string) $post->title,
				'url'         => function_exists( 'adn_cms_post_url' ) ? adn_cms_post_url( $post ) : '#',
				'description' => $_desc,
			);
		}
		return $items;
	}

	// ── Parent term helper ──────────────────────────────────────
	public static function parentTerm( $slug ) {
		return self::repository()->get_parent_term_by_slug( $slug );
	}
}
