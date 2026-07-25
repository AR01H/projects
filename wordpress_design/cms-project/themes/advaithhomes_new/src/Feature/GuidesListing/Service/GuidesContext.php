<?php
namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

/**
 * GuidesContext - Builds context for the guides hub page.
 * Split into small focused methods.
 */
class GuidesContext {

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
	public static function buildHero(): array {
		return array(
			'eyebrow'     => adn_term( 'guides_page.hero_eyebrow', 'Explore' ),
			'title'       => adn_term( 'guides_page.hero_title', 'Guides & Resources' ),
			'description' => adn_term( 'guides_page.hero_desc', 'Expert guidance to help you navigate property decisions.' ),
			'bg_icon'     => adn_term( 'icons.guide_parent', '📚' ),
		);
	}

	// ── Groups section ──────────────────────────────────────────
	public static function buildGroups(): array {
		$parents = function_exists( 'adn_cms_guide_parents' ) ? adn_cms_guide_parents( 20 ) : array();
		$grads = array(
			'linear-gradient(150deg,#1a3d2b 0%,#2d6147 100%)',
			'linear-gradient(150deg,#2a1f40 0%,#4a3880 100%)',
			'linear-gradient(150deg,#1d3050 0%,#2d5496 100%)',
			'linear-gradient(150deg,#2d3b1a 0%,#4a6128 100%)',
			'linear-gradient(150deg,#3b1a1a 0%,#7a2e28 100%)',
			'linear-gradient(150deg,#1a2d3b 0%,#2d5068 100%)',
		);

		$groups = array();
		foreach ( $parents as $i => $term ) {
			$name = $term->name ?? '';
			if ( '' === $name ) { continue; }
			$img_id = ! empty( $term->image_id ) ? (int) $term->image_id : 0;
			$img_url = $img_id ? ( wp_get_attachment_image_url( $img_id, 'large' ) ?: '' ) : '';
			$term_id = (int) $term->id;
			$term_slug = $term->slug ?? '';

			// Fetch child topics for the right panel
			$topics = array();
			if ( function_exists( 'adn_cms_topics' ) ) {
				foreach ( adn_cms_topics( $term_id, 10 ) as $topic ) {
					$t_name = $topic->name ?? '';
					$t_slug = $topic->slug ?? '';
					if ( '' === $t_name ) { continue; }
					$topics[] = array(
						'title' => (string) $t_name,
						'url'   => home_url( '/' . trim( (string) $t_slug, '/' ) . '/' ),
					);
				}
			}

			// Fetch latest posts for the right panel
			$latest_posts = array();
			if ( function_exists( 'adn_cms_articles_for_parent' ) ) {
				foreach ( adn_cms_articles_for_parent( $term_slug, 3 ) as $post ) {
					$_t = $post->title ?? '';
					if ( '' === $_t ) { continue; }
					$_stamp = $post->created_at ?? '';
					$latest_posts[] = array(
						'title' => (string) $_t,
						'url'   => function_exists( 'adn_cms_post_url' ) ? adn_cms_post_url( $post ) : '#',
						'date'  => $_stamp ? date_i18n( 'M j, Y', strtotime( $_stamp ) ) : '',
						'tag'   => $post->category_name ?? $name,
					);
				}
			}

			$groups[] = array(
				'name'         => $name,
				'slug'         => $term_slug,
				'description'  => $term->description ?? '',
				'icon'         => $term->icon_emoji ?? adn_term( 'icons.guide_parent', '📚' ),
				'gradient'     => $grads[ $i % count( $grads ) ],
				'image_url'    => $img_url,
				'url'          => adn_cms_term_url( $term ),
				'count'        => isset( $term->count ) ? (int) $term->count : 0,
				'topics'       => $topics,
				'latest_posts' => $latest_posts,
			);
		}
		return $groups;
	}

	// ── News items ──────────────────────────────────────────────
	public static function newsItems( $limit = 3, $related_posts_builder = null ) {
		$related_posts_builder = $related_posts_builder ?: \Adn\Theme\Shared\RelatedPostsBuilder::class;
		return $related_posts_builder::newsItems( $limit, 'compact' );
	}

	// ── Main getContext ─────────────────────────────────────────
	/**
	 * @param string|null $breadcrumb_builder    BreadcrumbBuilder class name or null for default.
	 * @param string|null $related_posts_builder RelatedPostsBuilder class name or null for default.
	 */
	public static function getContext( $breadcrumb_builder = null, $related_posts_builder = null ) {
		$breadcrumb_builder   = $breadcrumb_builder ?: \Adn\Theme\Shared\BreadcrumbBuilder::class;
		$related_posts_builder = $related_posts_builder ?: \Adn\Theme\Shared\RelatedPostsBuilder::class;
		$cache_key = 'page_guides_context';
		$cached = self::cacheGet( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();

		$ctx = array(
			'meta'       => array(
				'slug'             => 'guides',
				'page_title'       => adn_term( 'guides_page.hero_title', 'Guides & Resources' ) . ' - ' . SITE_BRAND_NAME,
				'meta_description' => adn_term( 'guides_page.hero_desc', 'Expert guidance to help you navigate property decisions.' ),
			),
			'breadcrumb' => $breadcrumb_builder::guidesListing(),
			'hero'       => self::buildHero(),
			'groups'     => self::buildGroups(),
			'news'       => self::newsItems( 3, $related_posts_builder ),
			'sidebar'    => array(),
			'chrome'     => $chrome,
		);

		self::cacheSet( $cache_key, $ctx );
		return $ctx;
	}
}
