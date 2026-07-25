<?php
namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

/**
 * TopicCategoryContext - Builds context for topic/category listing pages.
 *
 * Big getContext() is split into small focused methods for reusability.
 */
class TopicCategoryContext {

	// ── Repository ──────────────────────────────────────────────
	public static function repository() {
		static $repo = null;
		if ( null === $repo ) {
			$repo = new \Adn\Theme\Repository\TopicCategoryRepository();
		}
		return $repo;
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

	// ── Resolve slug and query params ───────────────────────────
	public static function resolveParams(): array {
		$slug   = sanitize_key( (string) get_query_var( 'adn_guide_term_slug', '' ) );
		$paged  = max( 1, isset( $_GET['paged'] ) ? (int) $_GET['paged'] : 1 ); // phpcs:ignore WordPress.Security.NonceVerification
		$search = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
		return array( 'slug' => $slug, 'paged' => $paged, 'search' => $search );
	}

	// ── Cache key ───────────────────────────────────────────────
	private static function cacheKey( string $slug, int $paged, string $search ): string {
		return 'page_topic_category_context_' . $slug . '_p' . $paged . '_' . md5( $search );
	}

	// ── Hero section ────────────────────────────────────────────
	public static function buildHero( $term, $parent ): array {
		if ( ! $term ) { return array(); }
		$name = (string) $term->name;
		$desc = (string) ( $term->description ?? '' );
		$icon = (string) ( $term->icon_emoji ?? '' );
		$img  = ! empty( $term->image_id ) ? (int) $term->image_id : 0;
		$parent_name = $parent && ! empty( $parent->name ) ? (string) $parent->name : '';

		return array(
			'title'       => $name,
			'description' => $desc,
			'image_icon'  => $icon,
			'image_id'    => $img,
			'parent_name' => $parent_name,
		);
	}

	// ── Meta + breadcrumb ───────────────────────────────────────
	public static function buildBreadcrumb( string $name, $parent ): array {
		$bc = array( array( 'label' => PAGE_TITLE_HOME, 'url' => '/' ) );
		if ( $parent && ! empty( $parent->name ) ) {
			$bc[] = array( 'label' => (string) $parent->name, 'url' => '/' . sanitize_title( (string) $parent->slug ) . '/' );
		}
		$bc[] = array( 'label' => $name, 'url' => null );
		return $bc;
	}

	// ── Search section ──────────────────────────────────────────
	public static function buildSearch( string $slug, string $search ): array {
		return array(
			'query'    => $search,
			'base_url' => home_url( '/' . $slug . '/' ),
		);
	}

	// ── Articles section ────────────────────────────────────────
	public static function buildArticles( $term, int $paged, int $per_page, string $search ): array {
		if ( ! $term ) { return array(); }
		$repo = self::repository();
		return $repo->get_articles( (int) $term->id, $paged, $per_page, $search );
	}

	// ── Pagination section ──────────────────────────────────────
	public static function buildPagination( array $articles, int $paged, int $per_page ): array {
		$total = isset( $articles['total'] ) ? (int) $articles['total'] : 0;
		$total_pages = $per_page > 0 ? (int) ceil( $total / $per_page ) : 1;
		return array(
			'current'     => $paged,
			'total'       => $total,
			'total_pages' => $total_pages,
			'per_page'    => $per_page,
		);
	}

	// ── Sidebar section ─────────────────────────────────────────
	public static function buildSidebar( string $slug, $term, $parent ): array {
		$repo = self::repository();
		$sidebar = array();
		$sidebar['sibling_topics'] = $repo->get_sibling_topics( $slug );
		$sidebar['calculators'] = function_exists( 'adn_get_parent_term_calculator_cards' )
			? adn_get_parent_term_calculator_cards( $parent ? (string) $parent->slug : '' )
			: array();
		$sidebar['news'] = array(
			'heading' => adn_term( 'sidebar.latest_news', 'Latest News' ),
			'items' => array(),
		);
		$sidebar['contact'] = array();
		return $sidebar;
	}

	// ── Related categories ──────────────────────────────────────
	public static function buildRelatedCategories( string $slug ): array {
		$repo = self::repository();
		return $repo->get_related_categories( $slug );
	}

	// ── Newsletter section ──────────────────────────────────────
	public static function buildNewsletter(): array {
		return array(
			'icon'         => '📬',
			'title'        => defined( 'SITE_NEWSLETTER_TITLE' ) ? SITE_NEWSLETTER_TITLE : 'Stay Informed',
			'description'  => defined( 'SITE_NEWSLETTER_DESC' )  ? SITE_NEWSLETTER_DESC  : 'Get the latest guides and updates delivered to your inbox.',
			'placeholder'  => defined( 'SITE_NEWSLETTER_PH' )    ? SITE_NEWSLETTER_PH    : 'Your email address',
			'button_label' => defined( 'SITE_BTN_SUBSCRIBE' )    ? SITE_BTN_SUBSCRIBE    : 'Subscribe',
			'note'         => defined( 'SITE_NEWSLETTER_NOTE' )  ? SITE_NEWSLETTER_NOTE  : 'No spam. Unsubscribe anytime.',
		);
	}

	// ── CTA section ─────────────────────────────────────────────
	public static function buildCtaHelp(): array {
		return array(
			'icon'        => '💬',
			'title'       => adn_term( 'topic_page.cta_title', 'Need help with this topic?' ),
			'description' => adn_term( 'topic_page.cta_desc', 'Our experts can answer your specific questions.' ),
			'button'      => array(
				'label' => adn_term( 'topic_page.cta_button', 'Ask an Expert' ),
				'url'   => SITE_ASK_EXPERT_URL,
				'style' => 'primary',
			),
		);
	}

	// ── Main getContext ─────────────────────────────────────────
	public static function getContext() {
		$params = self::resolveParams();
		$slug   = $params['slug'];
		$paged  = $params['paged'];
		$search = $params['search'];

		$cache_key = self::cacheKey( $slug, $paged, $search );
		$cached = self::cacheGet( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$chrome = adn_service_site_chrome();
		$repo   = self::repository();
		$per_page = defined( 'ADN_TOPIC_ARTICLES_PER_PAGE' ) ? (int) ADN_TOPIC_ARTICLES_PER_PAGE : 12;

		$term   = $repo->get_term_by_slug( $slug );
		$parent = $term && ! empty( $term->parent_term_id )
			? $repo->get_parent_term( (int) $term->parent_term_id )
			: null;

		$name = $term ? (string) $term->name : ucwords( str_replace( '-', ' ', $slug ) );

		$ctx = array(
			'chrome'             => is_array( $chrome ) ? $chrome : array(),
			'slug'               => $slug,
			'term'               => $term ? (array) $term : null,
			'parent'             => $parent ? (array) $parent : null,
			'hero'               => self::buildHero( $term, $parent ),
			'breadcrumb'         => self::buildBreadcrumb( $name, $parent ),
			'search'             => self::buildSearch( $slug, $search ),
			'articles'           => self::buildArticles( $term, $paged, $per_page, $search ),
			'pagination'         => array(),
			'related_categories' => self::buildRelatedCategories( $slug ),
			'highlight_posts'    => array(),
			'sidebar'            => self::buildSidebar( $slug, $term, $parent ),
			'news'               => array( 'heading' => array(), 'items' => array() ),
			'calculators'        => array( 'heading' => array(), 'items' => array() ),
			'cta_help'           => self::buildCtaHelp(),
			'newsletter'         => self::buildNewsletter(),
		);

		if ( ! empty( $ctx['articles']['items'] ) ) {
			$ctx['pagination'] = self::buildPagination( $ctx['articles'], $paged, $per_page );
		}

		self::cacheSet( $cache_key, $ctx );
		return $ctx;
	}

	// ── Parent term helper ──────────────────────────────────────
	public static function parentTerm( $slug ) {
		return self::repository()->get_parent_term_by_slug( $slug );
	}
}
