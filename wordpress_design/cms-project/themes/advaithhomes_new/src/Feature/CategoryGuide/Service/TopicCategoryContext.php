<?php
namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

/**
 * TopicCategoryContext - Builds context for topic/category listing pages.
 * Restores all context keys from the original page_topic_category_logical.php.
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

	// ── Resolve params ──────────────────────────────────────────
	public static function resolveParams(): array {
		$slug   = sanitize_key( (string) get_query_var( 'adn_guide_term_slug', '' ) );
		$paged  = \Adn\Theme\Shared\PaginationBuilder::currentPage();
		$search = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
		if ( '' === $search ) {
			$search = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		}
		return array( 'slug' => $slug, 'paged' => $paged, 'search' => $search );
	}

	// ── Main getContext ─────────────────────────────────────────
	/**
	 * @param string|null $pagination_builder    PaginationBuilder class name or null for default.
	 * @param string|null $breadcrumb_builder    BreadcrumbBuilder class name or null for default.
	 * @param string|null $sidebar_builder       SidebarBuilder class name or null for default.
	 * @param string|null $related_posts_builder RelatedPostsBuilder class name or null for default.
	 * @param string|null $newsletter_builder    NewsletterBuilder class name or null for default.
	 */
	public static function getContext(
		$pagination_builder = null,
		$breadcrumb_builder = null,
		$sidebar_builder = null,
		$related_posts_builder = null,
		$newsletter_builder = null
	) {
		$pagination_builder  = $pagination_builder ?: \Adn\Theme\Shared\PaginationBuilder::class;
		$breadcrumb_builder  = $breadcrumb_builder ?: \Adn\Theme\Shared\BreadcrumbBuilder::class;
		$sidebar_builder     = $sidebar_builder ?: \Adn\Theme\Shared\SidebarBuilder::class;
		$related_posts_builder = $related_posts_builder ?: \Adn\Theme\Shared\RelatedPostsBuilder::class;
		$newsletter_builder  = $newsletter_builder ?: \Adn\Theme\Shared\NewsletterBuilder::class;

		$params = self::resolveParams();
		$slug = $params['slug'];
		$paged = $params['paged'];
		$search = $params['search'];

		$cache_key = 'page_topic_category_context_' . $slug . $pagination_builder::cacheSegment( $paged ) . '_' . md5( $search );
		$cached = self::cacheGet( $cache_key );
		if ( false !== $cached ) { return $cached; }

		$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();
		$repo = self::repository();
		$per_page = defined( 'ADN_TOPIC_ARTICLES_PER_PAGE' ) ? (int) ADN_TOPIC_ARTICLES_PER_PAGE : 12;

		$ctx = self::buildDefaultContext( $chrome, $slug );

		if ( '' === $slug ) {
			self::cacheSet( $cache_key, $ctx );
			return $ctx;
		}

		$term = $repo->get_term_by_slug( $slug );
		if ( ! $term ) {
			self::cacheSet( $cache_key, $ctx );
			return $ctx;
		}

		$ctx['term'] = $term;

		// ── Parent term ──────────────────────────────────────────
		$parent = self::resolveParent( $repo, $term );
		$ctx['parent'] = $parent;

		$parent_label = $parent && ! empty( $parent->name ) ? (string) $parent->name : SITE_DOMAIN_NOUN;

		// ── Hero ─────────────────────────────────────────────────
		$ctx['hero'] = self::buildHero( $slug, $term, $parent, $parent_label );

		// ── Breadcrumb ───────────────────────────────────────────
		$ctx['breadcrumb'] = self::buildBreadcrumb( $parent, wp_unslash( (string) $term->name ), $breadcrumb_builder );

		// ── Search ───────────────────────────────────────────────
		$ctx['search'] = array(
			'query'    => $search,
			'base_url' => home_url( '/' . $slug . '/' ),
		);

		// ── Articles ─────────────────────────────────────────────
		$articles_result = $repo->get_articles( (int) $term->id, $paged, $per_page, $search );
		$articles = $articles_result['items'];
		$ctx['articles'] = $articles;

		$ctx['pagination'] = $pagination_builder::build( $paged, $articles_result['total_pages'], home_url( '/' . $slug . '/' ) );

		// ── Sidebar ──────────────────────────────────────────────
		$ctx['sidebar'] = self::buildSidebar( $slug, $term, $parent, $parent_label, $articles, $sidebar_builder, $related_posts_builder );

		// ── Related categories ───────────────────────────────────
		$ctx['related_categories'] = $repo->get_related_categories( $slug );

		// ── Highlight posts (featured/popular/suggested) ─────────
		$ctx['highlight_posts'] = self::buildHighlightPosts( $term );

		// ── News ─────────────────────────────────────────────────
		$ctx['news'] = self::buildNews( $parent_label, $related_posts_builder );

		// ── Calculators ──────────────────────────────────────────
		$ctx['calculators'] = self::buildCalculators( $slug, $parent_label );

		// ── CTA help ─────────────────────────────────────────────
		$ctx['cta_help'] = array(
			'icon'        => '🏡',
			'title'       => adn_term( 'content.need_help_title', 'Need Help With' ) . ' ' . $term->name . '?',
			'description' => adn_term( 'content.need_help_description', 'Speak to one of our expert advisors and get personalised guidance tailored to your situation.' ),
			'cta'         => array( 'label' => adn_term( 'content.need_help_cta', 'Talk to an Expert' ), 'url' => home_url( SITE_CONTACT_URL ) ),
			'trust_items' => (function(){
				$items = adn_term( 'content.trust_items', '' );
				$decoded = $items ? json_decode( $items, true ) : array();
				if ( empty( $decoded ) ) {
					$decoded = array( 'Independent & Unbiased', 'No hidden fees', 'Plain English advice' );
				}
				return $decoded;
			})(),
		);

		self::cacheSet( $cache_key, $ctx );
		return $ctx;
	}

	// ── Breadcrumb ──────────────────────────────────────────────
	private static function buildBreadcrumb( $parent, $term_name = '', $breadcrumb_builder = null ) {
		$breadcrumb_builder = $breadcrumb_builder ?: \Adn\Theme\Shared\BreadcrumbBuilder::class;
		return $breadcrumb_builder::topicCategory( $parent, $term_name );
	}

	// ── Hero ───────────────────────────────────────────────────
	private static function buildHero( string $slug, $term, $parent, string $parent_label ): array {
		$_cs_all      = class_exists( 'AH_Category_Settings' ) ? \AH_Category_Settings::get_all( $slug ) : array();
		$_cs_app      = isset( $_cs_all['appearance'] ) && is_array( $_cs_all['appearance'] ) ? $_cs_all['appearance'] : array();
		$_cs_thumb_id = ! empty( $_cs_app['thumbnail_id'] ) ? (int) $_cs_app['thumbnail_id'] : 0;
		$_term_img_id = ! empty( $term->image_id ) ? (int) $term->image_id : 0;

		return array(
			'eyebrow'     => $parent
				? ( ! empty( $parent->icon_emoji ) ? adn_icon( $parent->icon_emoji ) . ' ' : '' ) . esc_html( $parent->name )
				: '',
			'title'       => wp_unslash( (string) $term->name ),
			'description' => wp_unslash( (string) $term->description ),
			'image_id'    => $_cs_thumb_id ?: $_term_img_id,
			'image_icon'  => (string) ( $term->icon_emoji ?? '' ),
			'trust_items' => array(),
			'parent_name' => $parent_label,
		);
	}

	// ── Sidebar ─────────────────────────────────────────────────
	private static function buildSidebar( string $slug, $term, $parent, string $parent_label, array $articles, $sidebar_builder = null, $related_posts_builder = null ) {
		$sidebar_builder     = $sidebar_builder ?: \Adn\Theme\Shared\SidebarBuilder::class;
		$related_posts_builder = $related_posts_builder ?: \Adn\Theme\Shared\RelatedPostsBuilder::class;
		$sidebar = array();

		// Latest updates (first 4 articles)
		$_sb_updates = array();
		foreach ( array_slice( $articles, 0, 4 ) as $_ua ) {
			$_ub_label = isset( $_ua['title'] ) ? (string) $_ua['title'] : '';
			if ( '' === $_ub_label ) { continue; }
			$_sb_updates[] = array(
				'label'     => $_ub_label,
				'url'       => isset( $_ua['url'] )       ? (string) $_ua['url']       : '',
				'thumbnail' => isset( $_ua['thumbnail'] ) ? (string) $_ua['thumbnail'] : '',
				'meta'      => isset( $_ua['date'] )      ? (string) $_ua['date']      : '',
			);
		}
		$sidebar['latest_updates'] = $_sb_updates;

		// Buying topics (sibling sub-categories)
		$topic_items = self::repository()->get_sibling_topics( $slug );
		if ( ! empty( $topic_items ) ) {
			$sidebar['buying_topics'] = array(
				'heading'  => sprintf( adn_term( 'topic_page.explore_heading', 'Explore %s' ), $parent_label ),
				'items'    => $topic_items,
				'view_all' => $parent ? array(
					'label' => adn_term( 'category_page.view_all', 'View all →' ),
					'url'   => home_url( '/' . trim( $parent->slug, '/' ) . '/' ),
				) : array(),
			);
		}

		// Quick tools
		if ( function_exists( 'adn_get_parent_term_calculator_cards' ) ) {
			$parent_slug = $parent ? (string) $parent->slug : $slug;
			$calc_links = array();
			foreach ( adn_get_parent_term_calculator_cards( $parent_slug, 5 ) as $card ) {
				$calc_links[] = array(
					'icon'  => $card['icon'],
					'label' => $card['label'],
					'url'   => $card['url'],
				);
			}
			if ( ! empty( $calc_links ) ) {
				$sidebar['quick_tools'] = array(
					'heading' => $parent_label . ' ' . SITE_TOOLS_PLURAL,
					'items'   => $calc_links,
					'cta'     => array( 'label' => 'All ' . strtolower( SITE_TOOLS_PLURAL ) . ' →', 'url' => home_url( SITE_CALCULATORS_URL ) ),
				);
			}
		}

		// Sidebar cards (guides, experts, guidance, contact, news, calculators)
		$sidebar['sidebar_cards'] = $sidebar_builder::sidebarCards( array( 'experts' ) );

		// Sidebar news
		$_news_items = $sidebar_builder::latestNews( 3 );
		$sidebar['news'] = array(
			'heading'  => adn_term( 'labels.latest_news', 'Latest News' ),
			'items'    => array_slice( $_news_items, 0, 3 ),
			'view_all' => array( 'label' => adn_term( 'content.view_all_news', 'View all →' ), 'url' => home_url( SITE_NEWS_URL ) ),
		);

		return $sidebar;
	}

	// ── Default context ─────────────────────────────────────────
	private static function buildDefaultContext( array $chrome, string $slug ): array {
		return array(
			'chrome'             => is_array( $chrome ) ? $chrome : array(),
			'slug'               => $slug,
			'term'               => null,
			'parent'             => null,
			'hero'               => array(),
			'breadcrumb'         => array(),
			'search'             => array( 'query' => '', 'base_url' => '' ),
			'articles'           => array(),
			'pagination'         => \Adn\Theme\Shared\PaginationBuilder::single(),
			'related_categories' => array(),
			'highlight_posts'    => array(),
			'sidebar'            => array(),
			'news'               => array( 'heading' => array(), 'items' => array() ),
			'calculators'        => array( 'heading' => array(), 'items' => array() ),
			'cta_help'           => array(),
			'newsletter'         => \Adn\Theme\Shared\NewsletterBuilder::cta(),
		);
	}

	// ── Resolve parent term ────────────────────────────────────
	private static function resolveParent( $repo, $term ) {
		$parent = null;
		if ( ! empty( $term->parent_term_id ) ) {
			$parent = $repo->get_parent_term( (int) $term->parent_term_id );
		}
		if ( ! $parent && ! empty( $term->parent_id ) ) {
			$parent = $repo->get_parent_taxonomy_by_id( (int) $term->parent_id );
		}
		return $parent;
	}

	// ── Highlight posts (featured/popular/suggested) ────────────
	private static function buildHighlightPosts( $term ): array {
		$_hl_panels = array();
		if ( ! $term || ! function_exists( 'adn_cms_available' ) || ! adn_cms_available() ) { return $_hl_panels; }

		$_post_ids = self::repository()->get_term_post_ids( (int) $term->id );

		if ( empty( $_post_ids ) ) { return $_hl_panels; }
		$_flag_defs = array(
			'featured'  => array( 'meta_key' => '_ah_is_featured',  'heading' => '⭐ Featured',  'fa' => 'fa-solid fa-star' ),
			'popular'   => array( 'meta_key' => '_ah_is_popular',   'heading' => '🔥 Popular',   'fa' => 'fa-solid fa-fire' ),
			'suggested' => array( 'meta_key' => '_ah_is_suggested', 'heading' => '💡 Suggested', 'fa' => 'fa-solid fa-lightbulb' ),
		);

		foreach ( $_flag_defs as $_fkey => $_fdef ) {
			$_fq = new \WP_Query( array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'post__in'       => $_post_ids,
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'meta_query'     => array(
					array( 'key' => $_fdef['meta_key'], 'value' => '1', 'compare' => '=' ),
				),
			) );

			if ( ! $_fq->have_posts() ) { wp_reset_postdata(); continue; }

			$_items = array();
			foreach ( $_fq->posts as $_fp ) {
				$_fp_id    = (int) $_fp->ID;
				$_thumb_id = get_post_thumbnail_id( $_fp_id );
				$_thumb    = $_thumb_id ? ( wp_get_attachment_image_url( $_thumb_id, 'thumbnail' ) ?: '' ) : '';
				$_items[]  = array(
					'icon'      => $_fdef['fa'],
					'title'     => $_fp->post_title,
					'text'      => $_fp->post_title,
					'label'     => $_fp->post_title,
					'date'      => get_the_date( 'M j, Y', $_fp_id ),
					'meta'      => get_the_date( 'M j, Y', $_fp_id ),
					'thumbnail' => $_thumb,
					'url'       => get_permalink( $_fp_id ),
				);
			}
			wp_reset_postdata();

			$_hl_panels[ $_fkey ] = array(
				'heading'  => $_fdef['heading'],
				'fa_icon'  => $_fdef['fa'],
				'items'    => $_items,
				'view_all' => array(),
			);
		}
		return $_hl_panels;
	}

	// ── News section ────────────────────────────────────────────
	private static function buildNews( string $parent_label, $related_posts_builder = null ) {
		$related_posts_builder = $related_posts_builder ?: \Adn\Theme\Shared\RelatedPostsBuilder::class;
		return array(
			'heading' => array(
				'title'      => sprintf( adn_term( 'topic_page.latest_news', 'Latest %s News' ), $parent_label ),
				'link_label' => adn_term( 'content.view_all_news', 'View all →' ),
				'link_url'   => home_url( SITE_NEWS_URL ),
			),
			'items' => $related_posts_builder::newsItems( 3, 'full' ),
		);
	}

	// ── Calculators section ─────────────────────────────────────
	private static function buildCalculators( string $slug, string $parent_label ): array {
		if ( ! function_exists( 'adn_get_parent_term_calculator_cards' ) ) { return array(); }
		$calc_items = array();
		$parent_slug = $slug;
		// Find parent slug from term
		if ( function_exists( 'adn_cms_taxonomy_term_by_slug' ) ) {
			$term = adn_cms_taxonomy_term_by_slug( $slug );
			if ( $term && ! empty( $term->parent_term_id ) ) {
				$repo = self::repository();
				$parent = $repo->get_parent_term( (int) $term->parent_term_id );
				if ( $parent ) { $parent_slug = (string) $parent->slug; }
			}
		}
		foreach ( adn_get_parent_term_calculator_cards( $parent_slug, 7 ) as $card ) {
			$calc_items[] = array(
				'icon'         => $card['icon'],
				'title'        => $card['label'],
				'desc'         => $card['desc'] ?? '',
				'updated_date' => $card['updated_date'] ?? '',
				'url'          => $card['url'],
				'thumbnail'    => $card['thumbnail'],
				'highlight'    => $card['highlight'],
			);
		}
		if ( empty( $calc_items ) ) { return array(); }
		return array(
			'heading' => array(
				'title'      => sprintf( adn_term( 'category_page.calculators_heading', 'Useful %s for %s' ), SITE_TOOLS_PLURAL, $parent_label ),
				'link_label' => adn_term( 'category_page.related_tools_heading', 'View all →' ),
				'link_url'   => home_url( SITE_CALCULATORS_URL ),
			),
			'items' => $calc_items,
		);
	}

	// ── Parent term helper ──────────────────────────────────────
	public static function parentTerm( $slug ) {
		return self::repository()->get_parent_term_by_slug( $slug );
	}
}
