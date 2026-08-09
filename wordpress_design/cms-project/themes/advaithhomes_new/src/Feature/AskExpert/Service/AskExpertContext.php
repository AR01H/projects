<?php
namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

/**
 * AskExpertContext - Builds context for the ask expert page.
 *
 * Builders are injected via getContext() parameters with real class defaults.
 * This keeps the static interface for production while enabling test mocks.
 */
class AskExpertContext {

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
	public static function buildHero( array $banner, bool $is_unlocked ): array {
		$hero_title = ( ! empty( $banner['heading'] ) )
			? (string) $banner['heading']
			: ( \get_the_title() ?: SITE_EXPERT_LABEL );
		$hero_desc = ( ! empty( $banner['info'] ) )
			? (string) $banner['info']
			: \adn_term( 'expert_page.hero_desc_default', '' );

		return array(
			'title'       => $hero_title,
			'description' => $hero_desc,
			'bg_icon'     => \adn_term( 'icons.expert_hero', '🤝' ),
		);
	}

	// ── Trust/marquee items ─────────────────────────────────────
	public static function buildTrustItems( array $banner, array $db_rows, bool $use_db ): array {
		$marquee_items = ( ! empty( $banner['marquee_items'] ) && is_array( $banner['marquee_items'] ) )
			? $banner['marquee_items']
			: array();

		if ( empty( $marquee_items ) && $use_db ) {
			$_mq_cat_keys  = array_unique( array_filter( array_column( $db_rows, 'category' ) ) );
			$marquee_items = array(
				array( 'icon' => \adn_term( 'icons.trust_verified', '🏠' ), 'label' => count( $db_rows ) . '+',      'note' => \adn_term( 'expert_page.trust_verified_experts', 'Verified Experts' ) ),
				array( 'icon' => \adn_term( 'icons.trust_specialism', '📋' ), 'label' => count( $_mq_cat_keys ) . '+', 'note' => \adn_term( 'expert_page.trust_specialisms', 'Specialisms' ) ),
				array( 'icon' => \adn_term( 'icons.trust_time', '⚡' ), 'label' => '24h',                         'note' => \adn_term( 'expert_page.trust_avg_response', 'Avg Response Time' ) ),
				array( 'icon' => \adn_term( 'icons.trust_free', '✅' ), 'label' => '100%',                        'note' => \adn_term( 'expert_page.trust_free_to_use', 'Free to Use' ) ),
			);
		}

		return $marquee_items;
	}

	// ── Expert DB rows ──────────────────────────────────────────
	public static function buildExpertList( array $db_rows, bool $is_unlocked ): array {
		$experts = array();
		foreach ( $db_rows as $row ) {
			$photo_id  = isset( $row['photo_id'] ) ? (int) $row['photo_id'] : 0;
			$photo_url = ( $photo_id > 0 ) ? \wp_get_attachment_image_url( $photo_id, 'thumbnail' ) : '';
			if ( ! $photo_url ) { $photo_url = ''; }

			$bullets_raw = isset( $row['bullets'] ) ? $row['bullets'] : '';
			$bullets     = array();
			if ( '' !== $bullets_raw ) {
				$dec = json_decode( $bullets_raw, true );
				if ( is_array( $dec ) ) { $bullets = $dec; }
			}

			$slug        = isset( $row['expert_slug'] ) ? (string) $row['expert_slug'] : '';
			$profile_url = $slug ? \adn_expert_profile_url( $slug ) : \home_url( SITE_EXPERT_URL );

			$_row_locked = ( isset( $row['is_locked'] ) && $row['is_locked'] ) && ! $is_unlocked;

			$experts[] = array(
				'slug'          => $slug,
				'photo_url'     => $photo_url,
				'avatar'        => \adn_term( 'icons.expert_avatar', '👤' ),
				'name'          => isset( $row['name'] )          ? (string) $row['name']          : '',
				'title'         => isset( $row['title'] )         ? (string) $row['title']         : '',
				'category'      => isset( $row['category'] )      ? (string) $row['category']      : '',
				'rating'        => isset( $row['rating'] )        ? (float) $row['rating']         : 0.0,
				'reviews_count' => isset( $row['reviews_count'] ) ? (int) $row['reviews_count']    : 0,
				'reviews'       => isset( $row['reviews_count'] ) ? (int) $row['reviews_count']    : 0,
				'description'   => isset( $row['bio'] )           ? (string) $row['bio']           : '',
				'location'      => isset( $row['location'] )      ? (string) $row['location']      : '',
				'phone'         => '',
				'email'         => '',
				'tags'          => array_slice( $bullets, 0, 3 ),
				'bullets'       => $bullets,
				'url'           => $profile_url,
				'is_locked'     => $_row_locked ? 1 : 0,
			);
		}
		return $experts;
	}

	// ── Category filter tabs ────────────────────────────────────
	public static function buildCategories( array $db_experts, bool $use_db ): array {
		$_cat_icons = array(
			'all'          => '⭐',
			'consultant'   => '💰',
			'solicitor'    => '📋',
			'surveyor'     => '🔍',
			'buyer-agent'  => '🏠',
			'removal'      => '🚛',
			'tax'          => '⚖️',
			'conveyancing' => '📜',
			'insurance'    => '🛡️',
			'financial'    => '💎',
			'legal'        => '⚖️',
			'planning'     => '📐',
		);

		if ( ! $use_db ) {
			return array(
				array( 'key' => 'all', 'label' => \adn_term( 'expert_page.filter_all_experts', 'All Experts' ), 'icon' => \adn_term( 'icons.expert_all', '⭐' ), 'active' => true ),
			);
		}

		$db_cat_keys = array();
		foreach ( $db_experts as $_de ) {
			$_raw = isset( $_de['category'] ) ? trim( (string) $_de['category'] ) : '';
			if ( '' === $_raw ) { continue; }
			$_nk = sanitize_key( $_raw );
			if ( ! isset( $db_cat_keys[ $_nk ] ) ) {
				$db_cat_keys[ $_nk ] = $_raw;
			}
		}

		// Alphabetical by display label - "All Experts" stays pinned first below.
		uasort( $db_cat_keys, static function ( $a, $b ) {
			return strcasecmp(
				ucwords( str_replace( array( '-', '_' ), ' ', $a ) ),
				ucwords( str_replace( array( '-', '_' ), ' ', $b ) )
			);
		} );

		$categories = array(
			array( 'key' => 'all', 'label' => \adn_term( 'expert_page.filter_all_experts', 'All Experts' ), 'icon' => \adn_term( 'icons.expert_all', '⭐' ), 'active' => true ),
		);
		foreach ( $db_cat_keys as $_nk => $_orig ) {
			$categories[] = array(
				'key'   => $_nk,
				'label' => ucwords( str_replace( array( '-', '_' ), ' ', $_orig ) ),
				'icon'  => isset( $_cat_icons[ $_nk ] ) ? $_cat_icons[ $_nk ] : ( isset( $_cat_icons[ $_orig ] ) ? $_cat_icons[ $_orig ] : \adn_term( 'icons.expert_avatar', '👤' ) ),
			);
		}

		return $categories;
	}

	// ── Full sidebar ────────────────────────────────────────────
	public static function sidebarData( $sidebar_builder = null ): array {
		$sidebar_builder = $sidebar_builder ?: \Adn\Theme\Shared\SidebarBuilder::class;
		return $sidebar_builder::askExpertSidebar();
	}

	// ── Main getContext ─────────────────────────────────────────
	/**
	 * @param string|null $breadcrumb_builder   BreadcrumbBuilder class name or null for default.
	 * @param string|null $sidebar_builder      SidebarBuilder class name or null for default.
	 * @param string|null $related_posts_builder RelatedPostsBuilder class name or null for default.
	 */
	public static function getContext(
		$breadcrumb_builder = null,
		$sidebar_builder = null,
		$related_posts_builder = null
	): array {
		$breadcrumb_builder   = $breadcrumb_builder ?: \Adn\Theme\Shared\BreadcrumbBuilder::class;
		$sidebar_builder      = $sidebar_builder ?: \Adn\Theme\Shared\SidebarBuilder::class;
		$related_posts_builder = $related_posts_builder ?: \Adn\Theme\Shared\RelatedPostsBuilder::class;

		$cache_key = 'page_ask_expert_context';
		$cached    = self::cacheGet( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$chrome = \function_exists( 'adn_service_site_chrome' ) ? \adn_service_site_chrome() : array();
		$banner = \get_option( 'adn_expert_banner', array() );

		// Cookie-based unlock check.
		$_stored_pw      = isset( $banner['unlock_password'] ) ? (string) $banner['unlock_password'] : '';
		$_expected_token = ( '' !== $_stored_pw ) ? \hash_hmac( 'sha256', $_stored_pw, \wp_salt( 'secure_auth' ) ) : '';
		$_cookie_val     = isset( $_COOKIE['adn_experts_unlocked'] ) ? \sanitize_text_field( \wp_unslash( $_COOKIE['adn_experts_unlocked'] ) ) : '';
		$_is_unlocked    = ( '' !== $_expected_token && '' !== $_cookie_val && \hash_equals( $_expected_token, $_cookie_val ) );

		// Load experts from DB.
		$db_experts = array();
		$use_db     = false;
		$db_rows    = array();
		if ( \class_exists( 'AH_Expert_DB' ) ) {
			$db_rows = \AH_Expert_DB::get_all( 'active' );
			if ( ! empty( $db_rows ) ) {
				$use_db     = true;
				$db_experts = self::buildExpertList( $db_rows, $_is_unlocked );
			}
		}

		// Hero + trust items.
		$hero        = self::buildHero( $banner, $_is_unlocked );
		$trust_items = self::buildTrustItems( $banner, $db_rows, $use_db );
		$hero['trust_items'] = $trust_items;

		$breadcrumb = $breadcrumb_builder::expertListing();

		$meta = array(
			'page_title'       => \get_the_title() ?: SITE_EXPERT_LABEL,
			'meta_description' => \adn_term( 'expert_page.meta_desc_default', '' ),
		);

		$categories = self::buildCategories( $db_experts, $use_db );
		$sidebar    = self::sidebarData( $sidebar_builder );

		$cant_find_cta = array(
			'icon'         => \adn_term( 'icons.search', '🔍' ),
			'heading'      => \adn_term( 'expert_page.cant_find_heading', '' ),
			'desc'         => \adn_term( 'expert_page.cant_find_desc', '' ),
			'button_label' => \adn_term( 'expert_page.cant_find_btn', '' ),
			'button_url'   => SITE_GUIDANCE_URL,
		);

		$_has_locked = false;
		foreach ( $db_experts as $_ex ) {
			if ( ! empty( $_ex['is_locked'] ) ) {
				$_has_locked = true;
				break;
			}
		}

		$ctx = array(
			'meta'          => $meta,
			'breadcrumb'    => $breadcrumb,
			'hero'          => $hero,
			'stats'         => array(),
			'categories'    => $categories,
			'experts'       => $db_experts,
			'sidebar'       => $sidebar,
			'cant_find_cta' => $cant_find_cta,
			'chrome'        => $chrome,
			'ajax_url'      => \admin_url( 'admin-ajax.php' ),
			'contact_nonce' => \wp_create_nonce( 'adn_expert_contact' ),
			'has_locked'    => $_has_locked,
			'is_unlocked'   => $_is_unlocked,
			'unlock_nonce'  => \wp_create_nonce( 'adn_expert_unlock' ),
			'latest_news'   => $related_posts_builder::latestNewsWidget( 3 ),
		);

		self::cacheSet( $cache_key, $ctx );
		return $ctx;
	}
}
