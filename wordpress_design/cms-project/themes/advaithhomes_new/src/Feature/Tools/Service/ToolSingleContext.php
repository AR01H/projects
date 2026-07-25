<?php
namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

/**
 * ToolSingleContext - Builds context for single tool/calculator pages.
 * Split into small focused methods.
 */
class ToolSingleContext {

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
	public static function buildHero( array $calc, array $meta ): array {
		return array(
			'title'       => $calc['title'] ?? '',
			'description' => $meta['desc'] ?? '',
			'bg_icon'     => ! empty( $calc['icon'] ) ? $calc['icon'] : '🧮',
			'image_id'    => ! empty( $meta['thumbnail_id'] ) ? (int) $meta['thumbnail_id'] : 0,
		);
	}

	// ── Breadcrumb section ──────────────────────────────────────
	public static function buildBreadcrumb( string $title, $breadcrumb_builder = null ) {
		$breadcrumb_builder = $breadcrumb_builder ?: \Adn\Theme\Shared\BreadcrumbBuilder::class;
		return $breadcrumb_builder::toolSingle( $title );
	}

	// ── Related tools section ───────────────────────────────────
	public static function buildRelated( string $key, array $categories, array $registry, array $meta_all, $related_posts_builder = null ) {
		$related_posts_builder = $related_posts_builder ?: \Adn\Theme\Shared\RelatedPostsBuilder::class;
		return $related_posts_builder::relatedCalculators( $key, $categories, $registry, $meta_all, 3 );
	}

	// ── Sidebar section ─────────────────────────────────────────
	public static function buildSidebar( $sidebar_builder = null ) {
		$sidebar_builder = $sidebar_builder ?: \Adn\Theme\Shared\SidebarBuilder::class;
		return array(
			'expert_help' => $sidebar_builder::expertHelp(),
		);
	}

	// ── Main getContext ─────────────────────────────────────────
	/**
	 * @param string $key
	 * @param string|null $breadcrumb_builder    BreadcrumbBuilder class name or null for default.
	 * @param string|null $sidebar_builder       SidebarBuilder class name or null for default.
	 * @param string|null $related_posts_builder RelatedPostsBuilder class name or null for default.
	 */
	public static function getContext( $key, $breadcrumb_builder = null, $sidebar_builder = null, $related_posts_builder = null ) {
		$breadcrumb_builder   = $breadcrumb_builder ?: \Adn\Theme\Shared\BreadcrumbBuilder::class;
		$sidebar_builder      = $sidebar_builder ?: \Adn\Theme\Shared\SidebarBuilder::class;
		$related_posts_builder = $related_posts_builder ?: \Adn\Theme\Shared\RelatedPostsBuilder::class;
		$key = sanitize_key( $key );
		$cache_key = 'page_tool_single_context_' . $key;

		$cached = self::cacheGet( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$registry = function_exists( 'adn_calculators' ) ? adn_calculators() : array();
		if ( '' === $key || ! isset( $registry[ $key ] ) ) {
			return null;
		}

		$calc     = $registry[ $key ];
		$meta_all = get_option( 'adn_calculators_meta', array() );
		$meta     = $meta_all[ $key ] ?? array();
		$chrome   = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();
		$title    = $calc['title'] ?? '';

		$ctx = array(
			'key'           => $key,
			'meta'          => array( 'slug' => $key, 'page_title' => $title . ' - ' . SITE_BRAND_NAME, 'meta_description' => $meta['desc'] ?? '' ),
			'hero'          => self::buildHero( $calc, $meta ),
			'breadcrumb'    => self::buildBreadcrumb( $title, $breadcrumb_builder ),
			'highlight'     => $meta['highlight'] ?? '',
			'before_content' => $meta['before_content'] ?? '',
			'after_content'  => $meta['after_content'] ?? '',
			'related'       => self::buildRelated( $key, $meta['categories'] ?? array(), $registry, $meta_all, $related_posts_builder ),
			'sidebar'       => self::buildSidebar( $sidebar_builder ),
			'chrome'        => $chrome,
		);

		self::cacheSet( $cache_key, $ctx );
		return $ctx;
	}
}
