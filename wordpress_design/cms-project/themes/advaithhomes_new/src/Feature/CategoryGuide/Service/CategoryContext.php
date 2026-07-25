<?php
namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

/**
 * CategoryContext - Builds context for category pages.
 * Split into small focused methods.
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

	// ── Hero section ────────────────────────────────────────────
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
		return \Adn\Theme\Shared\BreadcrumbBuilder::category( $name );
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

	// ── Regulations section ─────────────────────────────────────
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

		// Hot Topics - shape items for sidebar component
		$hot_topics = array();
		$cs_ht = $cs_all['hot_topics'] ?? array();
		if ( ! empty( $cs_ht['items'] ) && is_array( $cs_ht['items'] ) ) {
			$ht_items = array();
			foreach ( $cs_ht['items'] as $t ) {
				if ( empty( $t['label'] ) && empty( $t['name'] ) ) { continue; }
				$ht_items[] = array(
					'icon'  => ! empty( $t['icon'] )  ? (string) $t['icon']  : '',
					'label' => ! empty( $t['label'] ) ? (string) $t['label'] : (string) ( $t['name'] ?? '' ),
					'url'   => ! empty( $t['url'] )   ? (string) $t['url']   : '#',
				);
			}
			if ( ! empty( $ht_items ) ) {
				$hot_topics = array(
					'heading'  => ! empty( $cs_ht['heading'] ) ? (string) $cs_ht['heading'] : adn_term( 'category_page.hot_topics_heading', '🔥 Hot Topics' ),
					'items'    => $ht_items,
					'view_all' => array(
						'label' => ! empty( $cs_ht['view_all_label'] ) ? (string) $cs_ht['view_all_label'] : '',
						'url'   => ! empty( $cs_ht['view_all_url'] )   ? (string) $cs_ht['view_all_url']   : '',
					),
				);
			}
		}

		// Featured Topics - shape items for sidebar component
		$featured_topics = array();
		$cs_ft = $cs_all['featured_topics'] ?? array();
		if ( ! empty( $cs_ft['items'] ) && is_array( $cs_ft['items'] ) ) {
			$ft_items = array();
			foreach ( $cs_ft['items'] as $t ) {
				if ( empty( $t['name'] ) && empty( $t['label'] ) ) { continue; }
				$ft_items[] = array(
					'icon'  => ! empty( $t['icon'] ) ? (string) $t['icon'] : '',
					'label' => ! empty( $t['name'] ) ? (string) $t['name'] : (string) ( $t['label'] ?? '' ),
					'url'   => ! empty( $t['url'] )  ? (string) $t['url']  : '#',
				);
			}
			if ( ! empty( $ft_items ) ) {
				$featured_topics = array(
					'heading' => ! empty( $cs_ft['heading'] ) ? (string) $cs_ft['heading'] : adn_term( 'category_page.browse_topics_heading', 'Browse Topics' ),
					'items'   => $ft_items,
				);
			}
		}

		return array(
			'groups' => function_exists( 'adn_get_all_parent_terms_for_sidebar' )
				? adn_get_all_parent_terms_for_sidebar( $slug )
				: array(),
			'popular_posts'    => $cs_all['popular_posts'] ?? array(),
			'featured_topics'  => $featured_topics,
			'hot_topics'       => $hot_topics,
			'calculators'      => $cs_all['calculators'] ?? array(),
			'spotlights'       => $cs_all['spotlights'] ?? array(),
			'news' => array(
				'heading' => $cs_sidebar['news_heading'] ?? adn_term( 'sidebar.latest_news', 'Latest News' ),
				'items' => self::cmsNews( $slug, 4 ),
			),
			'contact' => $cs_sidebar['contact'] ?? array(),
		);
	}

	// ── CTA + FAQs ──────────────────────────────────────────────
	public static function buildCta( array $cs_all ): array { return $cs_all['cta_banner'] ?? array(); }

	public static function buildFaqs( array $cs_all, string $name = '' ): array {
		$cs_faqs = $cs_all['faqs'] ?? array();
		if ( empty( $cs_faqs['items'] ) || ! is_array( $cs_faqs['items'] ) ) {
			return array();
		}

		// Collect FAQ IDs from admin settings
		$_faq_ids = array();
		foreach ( (array) $cs_faqs['items'] as $_fi ) {
			if ( ! empty( $_fi['faq_id'] ) ) {
				$_faq_ids[] = (int) $_fi['faq_id'];
			}
			if ( count( $_faq_ids ) >= 100 ) { break; }
		}
		$_faq_ids = array_filter( $_faq_ids );

		if ( empty( $_faq_ids ) ) {
			return array();
		}

		// Load FAQ items from the ah_faqs database table
		global $wpdb;
		$_faq_table = $wpdb->prefix . 'ah_faqs';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $_faq_table ) ) !== $_faq_table ) {
			return array();
		}

		$_placeholders = implode( ',', array_fill( 0, count( $_faq_ids ), '%d' ) );
		$_faq_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, question, answer, link_url, link_text FROM `{$_faq_table}` WHERE id IN ({$_placeholders}) AND status = 'active'",
				...$_faq_ids
			)
		);

		// Restore admin-defined order
		$_id_pos = array_flip( $_faq_ids );
		usort( $_faq_rows, function ( $a, $b ) use ( $_id_pos ) {
			return ( isset( $_id_pos[ $a->id ] ) ? $_id_pos[ $a->id ] : 0 )
			     - ( isset( $_id_pos[ $b->id ] ) ? $_id_pos[ $b->id ] : 0 );
		} );

		$_faq_built = array();
		foreach ( $_faq_rows as $_fr ) {
			$_faq_built[] = array(
				'id'        => (int)    $_fr->id,
				'question'  => (string) $_fr->question,
				'answer'    => (string) $_fr->answer,
				'link_url'  => (string) ( $_fr->link_url  ?? '' ),
				'link_text' => (string) ( $_fr->link_text ?? '' ),
			);
		}

		if ( empty( $_faq_built ) ) {
			return array();
		}

		return array(
			'heading' => ! empty( $cs_faqs['heading'] ) ? (string) $cs_faqs['heading'] : sprintf( '%s FAQs', $name ),
			'items'   => $_faq_built,
		);
	}

	// ── Main getContext ─────────────────────────────────────────
	public static function getContext( $slug = '' ) {
		$slug = self::resolveSlug( $slug );
		$cache_key = 'page_category_context_' . $slug;

		$cached = self::cacheGet( $cache_key );
		if ( false !== $cached ) { return $cached; }

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
			'faqs'          => self::buildFaqs( $cs_all, $name ),
			'spotlights'    => $cs_all['spotlights'] ?? array(),
			'chrome'        => $chrome,
		);

		self::cacheSet( $cache_key, $ctx );
		return $ctx;
	}

	// ── CMS guides data ─────────────────────────────────────────
	// NOTE: adn_cms_guides_by_category() returns TERM objects with:
	//   category_name, _term_slug, _term_desc, term_icon, term_image_id, parent_name, parent_icon
	// NOT WP post objects. Properties like $post->ID, $post->title, $post->featured_image_id do NOT exist.
	public static function cmsGuides( $slug ) {
		if ( ! function_exists( 'adn_cms_guides_by_category' ) ) { return array(); }
		$repo = self::repository();
		$topic_ids = array();
		if ( $slug !== '' && function_exists( 'adn_cms_available' ) && adn_cms_available() ) {
			$parent = $repo->get_parent_term_by_slug( $slug );
			if ( $parent && function_exists( 'adn_cms_topics' ) ) {
				foreach ( adn_cms_topics( (int) $parent->id, 50 ) as $child ) {
					if ( ! empty( $child->id ) ) { $topic_ids[] = (int) $child->id; }
				}
			}
			if ( empty( $topic_ids ) && $parent ) {
				$topic_ids = $repo->get_child_topic_ids( (int) $parent->id );
			}
			if ( ! empty( $parent ) && empty( $topic_ids ) ) { return array(); }
		}
		$rows = adn_cms_guides_by_category( 1200, $topic_ids );
		$items = array();
		foreach ( $rows as $term ) {
			$cat_name = $term->category_name ?? '';
			if ( '' === $cat_name ) { continue; }
			// Thumbnail: term_image_id on term objects (NOT featured_image_id or $post->ID)
			$_thumb = '';
			if ( ! empty( $term->term_image_id ) ) {
				$_u = wp_get_attachment_image_url( (int) $term->term_image_id, 'medium' );
				if ( $_u ) { $_thumb = (string) $_u; }
			}
			// URL: _term_slug builds category page URL (NOT adn_cms_post_url which expects WP post objects)
			$_url = ! empty( $term->_term_slug )
				? home_url( '/' . trim( (string) $term->_term_slug, '/' ) . '/' )
				: '#';
			$items[] = array(
				'thumbnail'   => $_thumb,
				'icon'        => $term->term_icon ?? $term->parent_icon ?? '📋',
				'title'       => (string) $cat_name,
				'url'         => $_url,
				'description' => wp_trim_words( wp_strip_all_tags( $term->_term_desc ?? '' ), 15 ),
			);
		}
		return $items;
	}

	// ── Latest updates ──────────────────────────────────────────
	public static function latestUpdates( $slug, $limit = 4 ) {
		$items = array();
		if ( function_exists( 'adn_cms_articles_for_parent' ) ) {
			foreach ( adn_cms_articles_for_parent( $slug, $limit ) as $post ) {
				if ( empty( $post->title ) ) { continue; }
				$_thumb = '';
				if ( ! empty( $post->ID ) ) {
					$_u = get_the_post_thumbnail_url( $post->ID, 'medium' ) ?: get_the_post_thumbnail_url( $post->ID, 'full' );
					if ( $_u ) { $_thumb = (string) $_u; }
				}
				if ( empty( $_thumb ) && ! empty( $post->featured_image_id ) ) {
					$_u = wp_get_attachment_image_url( (int) $post->featured_image_id, 'medium' );
					if ( $_u ) { $_thumb = (string) $_u; }
				}
				$items[] = array(
					'thumbnail'   => $_thumb,
					'icon'        => '📋',
					'title'       => (string) $post->title,
					'url'         => function_exists( 'adn_cms_post_url' ) ? adn_cms_post_url( $post ) : '#',
					'description' => wp_trim_words( wp_strip_all_tags( $post->excerpt ?? $post->content ?? '' ), 15 ),
				);
			}
		}
		return $items;
	}

	// ── CMS news ────────────────────────────────────────────────
	public static function cmsNews( $slug, $limit = 4 ) {
		if ( ! function_exists( 'adn_cms_articles_for_parent' ) ) { return array(); }
		$items = array();
		foreach ( adn_cms_articles_for_parent( $slug, $limit ) as $post ) {
			if ( empty( $post->title ) ) { continue; }
			$_thumb = '';
			if ( ! empty( $post->ID ) ) {
				$_u = get_the_post_thumbnail_url( $post->ID, 'medium' ) ?: get_the_post_thumbnail_url( $post->ID, 'full' );
				if ( $_u ) { $_thumb = (string) $_u; }
			}
			if ( empty( $_thumb ) && ! empty( $post->featured_image_id ) ) {
				$_u = wp_get_attachment_image_url( (int) $post->featured_image_id, 'medium' );
				if ( $_u ) { $_thumb = (string) $_u; }
			}
			$items[] = array(
				'thumbnail'   => $_thumb,
				'icon'        => '📰',
				'title'       => (string) $post->title,
				'url'         => function_exists( 'adn_cms_post_url' ) ? adn_cms_post_url( $post ) : '#',
				'description' => wp_trim_words( wp_strip_all_tags( $post->excerpt ?? $post->content ?? '' ), 15 ),
			);
		}
		return $items;
	}

	// ── Parent term helper ──────────────────────────────────────
	public static function parentTerm( $slug ) {
		return self::repository()->get_parent_term_by_slug( $slug );
	}
}
