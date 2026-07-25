<?php
namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

/**
 * GuidesListingContext - Builds context for guides listing pages.
 * Split into small focused methods.
 */
class GuidesListingContext {

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

	// ── Slug resolution ─────────────────────────────────────────
	private static function resolveSlug( $slug ): string {
		if ( '' === $slug ) {
			$page = get_queried_object();
			$slug = ( $page instanceof \WP_Post ) ? (string) $page->post_name : '';
		}
		return sanitize_key( (string) $slug );
	}

	// ── Hero section ────────────────────────────────────────────
	public static function buildHero( array $data ): array {
		return $data['hero'] ?? array(
			'title'       => adn_term( 'guides_page.hero_title', SITE_CONTENT_PLURAL ),
			'description' => adn_term( 'guides_page.hero_description', '' ),
		);
	}

	// ── Sidebar section ─────────────────────────────────────────
	public static function buildSidebar( $sidebar_builder = null ) {
		$sidebar_builder = $sidebar_builder ?: \Adn\Theme\Shared\SidebarBuilder::class;
		$parents = function_exists( 'adn_cms_guide_parents' ) ? adn_cms_guide_parents( 50 ) : array();
		$cat_groups = array();
		$browse_cats = array();

		foreach ( $parents as $pt ) {
			$p_name = $pt->name ?? '';
			$p_slug = $pt->slug ?? '';
			$p_icon = ! empty( $pt->icon_emoji ) ? $pt->icon_emoji : '📁';
			$p_url  = home_url( '/' . trim( $p_slug, '/' ) . '/' );
			if ( '' === $p_name ) { continue; }

			$topics    = function_exists( 'adn_cms_topics' ) ? adn_cms_topics( (int) $pt->id, 30 ) : array();
			$sub_items = array();
			foreach ( $topics as $topic ) {
				$t_name = $topic->name ?? '';
				$t_slug = $topic->slug ?? '';
				if ( '' === $t_name ) { continue; }
				$sub_items[] = array( 'label' => $t_name, 'url' => home_url( '/' . trim( $t_slug, '/' ) . '/' ) );
			}

			$cat_groups[] = array(
				'label'  => $p_name,
				'slug'   => $p_slug,
				'icon'   => $p_icon,
				'url'    => $p_url,
				'topics' => $sub_items,
			);
			$browse_cats[] = array( 'label' => $p_name, 'slug' => $p_slug, 'active' => false );
		}

		$tools_opt = get_option( 'adn_calculators_page', array() );
		return array(
			'browse_cats' => $browse_cats,
			'cat_groups'  => $cat_groups,
			'expert_help' => $sidebar_builder::expertHelp(),
		);
	}

	// ── Bottom grid section ─────────────────────────────────────
	public static function buildBottomGrid(): array {
		$_tools_url = SITE_CALCULATORS_URL;
		$_tools_raw = function_exists( 'adn_calculators' ) ? adn_calculators() : array();
		$_tools_meta = get_option( 'adn_calculators_meta', array() );
		foreach ( array_slice( $_tools_raw, 0, 1, true ) as $_tk => $_tr ) {
			$_tm = $_tools_meta[ $_tk ] ?? array();
			if ( ! empty( $_tm['card_url'] ) ) { $_tools_url = (string) $_tm['card_url']; }
			break;
		}

		return array(
			'links' => array(
				array( 'icon' => '📰', 'label' => SITE_LABEL_LATEST_NEWS, 'url' => SITE_NEWS_URL ),
				array( 'icon' => '📚', 'label' => SITE_CONTENT_PLURAL, 'url' => SITE_GUIDES_URL ),
				array( 'icon' => '💬', 'label' => SITE_SIDEBAR_EXPERT_HELP, 'url' => SITE_CONTACT_URL ),
				array( 'icon' => '🧮', 'label' => SITE_TOOLS_PLURAL, 'url' => $_tools_url ),
			),
		);
	}

	// ── Newsletter section ──────────────────────────────────────
	public static function buildNewsletter( $newsletter_builder = null ) {
		$newsletter_builder = $newsletter_builder ?: \Adn\Theme\Shared\NewsletterBuilder::class;
		return $newsletter_builder::cta();
	}

	// ── CMS articles to items ───────────────────────────────────
	public static function cmsItems( $articles ) {
		$img_classes = array( 'guide-img-green', 'guide-img-blue', 'guide-img-amber', 'guide-img-purple', 'guide-img-teal' );
		$items = array();
		foreach ( $articles as $i => $post ) {
			$title = $post->title ?? '';
			if ( '' === $title ) { continue; }
			$items[] = array(
				'img_class' => $img_classes[ $i % count( $img_classes ) ],
				'icon'      => $post->_parent_icon ?? '📄',
				'category'  => $post->category_name ?? PARENT_TERM,
				'title'     => $title,
				'desc'      => (string) ( $post->excerpt ?? '' ),
				'date'      => function_exists( 'adn_cms_post_date' ) ? adn_cms_post_date( $post ) : '',
				'read_time' => function_exists( 'adn_cms_read_time' ) ? adn_cms_read_time( $post->content ?? '' ) : '',
				'url'       => function_exists( 'adn_cms_post_url' ) ? adn_cms_post_url( $post ) : '',
			);
		}
		return $items;
	}

	// ── Main getContext ─────────────────────────────────────────
	/**
	 * @param string $slug
	 * @param string|null $breadcrumb_builder    BreadcrumbBuilder class name or null for default.
	 * @param string|null $sidebar_builder       SidebarBuilder class name or null for default.
	 * @param string|null $newsletter_builder    NewsletterBuilder class name or null for default.
	 * @param string|null $pagination_builder    PaginationBuilder class name or null for default.
	 */
	public static function getContext( $slug = '', $breadcrumb_builder = null, $sidebar_builder = null, $newsletter_builder = null, $pagination_builder = null ) {
		$slug = self::resolveSlug( $slug );
		$breadcrumb_builder = $breadcrumb_builder ?: \Adn\Theme\Shared\BreadcrumbBuilder::class;
		$sidebar_builder    = $sidebar_builder ?: \Adn\Theme\Shared\SidebarBuilder::class;
		$newsletter_builder = $newsletter_builder ?: \Adn\Theme\Shared\NewsletterBuilder::class;
		$pagination_builder = $pagination_builder ?: \Adn\Theme\Shared\PaginationBuilder::class;
		$cache_key = 'page_guides_listing_context_' . $slug;

		$cached = self::cacheGet( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$data   = function_exists( 'adn_service_guides_listing_data' ) ? adn_service_guides_listing_data( $slug ) : array();
		$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();

		$ctx = array(
			'slug'        => $slug,
			'meta'        => $data['meta'] ?? array(),
			'breadcrumb'  => $data['breadcrumb'] ?? $breadcrumb_builder::guidesListing(),
			'hero'        => self::buildHero( $data ),
			'sidebar'     => array(),
			'guides'      => $data['guides'] ?? array(),
			'cta_banner'  => $data['cta_banner'] ?? array(),
			'bottom_grid' => array(),
			'newsletter'  => self::buildNewsletter( $newsletter_builder ),
			'chrome'      => $chrome,
		);

		if ( function_exists( 'adn_cms_available' ) && adn_cms_available() ) {
			$parent_slug = preg_replace( '/-guides?$/', '', $slug );
			$articles = ( '' !== $parent_slug && $parent_slug !== $slug )
				? adn_cms_articles_for_parent( $parent_slug, 50 )
				: adn_cms_articles( 100 );

			if ( ! empty( $articles ) ) {
				$guides = is_array( $ctx['guides'] ) ? $ctx['guides'] : array();
				$guides['items']      = self::cmsItems( $articles );
				$guides['pagination'] = $pagination_builder::single();
				$ctx['guides'] = $guides;
			}

			$ctx['sidebar'] = self::buildSidebar( $sidebar_builder );
			$ctx['bottom_grid'] = self::buildBottomGrid();
		}

		self::cacheSet( $cache_key, $ctx );
		return $ctx;
	}
}
