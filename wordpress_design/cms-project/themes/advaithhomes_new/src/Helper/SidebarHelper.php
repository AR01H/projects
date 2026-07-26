<?php
namespace Adn\Theme\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'ADN_FAQS_CACHE_TTL' ) ) {
	define( 'ADN_FAQS_CACHE_TTL', HOUR_IN_SECONDS );
}

class SidebarHelper {

	private static $shown_ids = array();

	public static function getPageSidebarData( int $page_id = 0, int $faq_limit = 3 ): array {
		$sidebar = array();
		if ( function_exists( 'adn_service_contact_data' ) ) {
			$contact_data = adn_service_contact_data();
			if ( isset( $contact_data['contact_sidebar'] ) && is_array( $contact_data['contact_sidebar'] ) ) {
				$sidebar = $contact_data['contact_sidebar'];
			}
		}

		if ( function_exists( 'adn_cms_available' ) && adn_cms_available() && class_exists( 'AH_Faqs_Model' ) ) {
			try {
				$model = new \AH_Faqs_Model();
				$faqs  = $page_id > 0 ? $model->get_for_page( $page_id ) : array();
				if ( empty( $faqs ) ) {
					$faqs = $model->get_global();
				}
				if ( ! empty( $faqs ) ) {
					$sidebar['faqs'] = array_slice( $faqs, 0, $faq_limit );
				}
			} catch ( \Throwable $e ) {
				// If model fails, just show no FAQs.
			}
		}

		return $sidebar;
	}

	public static function getCmsPageId( string $type ): int {
		if ( ! function_exists( 'adn_cms_available' ) || ! adn_cms_available() || ! class_exists( 'AH_Pages_Model' ) ) {
			return 0;
		}
		try {
			$page = ( new \AH_Pages_Model() )->get_by_type( $type );
			return $page->id ?? 0;
		} catch ( \Throwable $e ) {
			return 0;
		}
	}

	public static function faqsCacheKey( int $page_id, bool $fallback_global ): string {
		return 'adn_faqs_' . $page_id . '_' . ( $fallback_global ? 1 : 0 );
	}

	public static function getPageFaqsGrouped( int $page_id = 0, bool $fallback_global = true ): array {
		$cache_key = self::faqsCacheKey( $page_id, $fallback_global );
		$cached    = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$groups = array();
		if ( ! function_exists( 'adn_cms_available' ) || ! adn_cms_available() || ! class_exists( 'AH_Faqs_Model' ) ) {
			return $groups;
		}
		try {
			$model = new \AH_Faqs_Model();
			$faqs  = $page_id > 0 ? $model->get_for_page( $page_id ) : array();
			if ( empty( $faqs ) && $fallback_global ) {
				$faqs = $model->get_global();
			}
		} catch ( \Throwable $e ) {
			return $groups;
		}
		foreach ( (array) $faqs as $faq ) {
			$section              = is_object( $faq ) ? (string) ( $faq->section ?? '' ) : (string) ( $faq['section'] ?? '' );
			$groups[ $section ][] = $faq;
		}

		set_transient( $cache_key, $groups, ADN_FAQS_CACHE_TTL );
		self::markFaqsShown( $groups );
		return $groups;
	}

	public static function markFaqsShown( array $groups ): void {
		if ( empty( $groups ) ) {
			return;
		}
		foreach ( $groups as $items ) {
			foreach ( (array) $items as $faq ) {
				$id = is_object( $faq ) ? (int) ( $faq->id ?? 0 ) : (int) ( $faq['id'] ?? 0 );
				if ( $id > 0 ) {
					self::$shown_ids[ $id ] = true;
				}
			}
		}
	}

	public static function faqsAlreadyShown( array $merge = array() ): array {
		if ( ! empty( $merge ) ) {
			self::$shown_ids = $merge + self::$shown_ids;
		}
		return self::$shown_ids;
	}

	public static function renderSlugAttachedFaqs(): void {
		if ( ! function_exists( 'adn_cms_available' ) || ! adn_cms_available() || ! class_exists( 'AH_Faqs_Model' ) ) {
			return;
		}

		$slug = (string) get_query_var( 'adn_cat_slug', '' );
		if ( '' === $slug ) {
			$slug = (string) get_query_var( 'adn_guide_term_slug', '' );
		}
		if ( '' === $slug ) {
			$queried = get_queried_object();
			$slug    = ( $queried instanceof \WP_Post ) ? (string) $queried->post_name : '';
		}
		if ( '' === $slug ) {
			return;
		}

		$cache_key = 'adn_faqs_slug_' . sanitize_key( $slug );
		$rows      = get_transient( $cache_key );
		if ( ! is_array( $rows ) ) {
			try {
				$rows = ( new \AH_Faqs_Model() )->get_by_slug( $slug );
			} catch ( \Throwable $e ) {
				$rows = array();
			}
			set_transient( $cache_key, $rows, ADN_FAQS_CACHE_TTL );
		}
		if ( empty( $rows ) ) {
			return;
		}

		$already_shown = self::faqsAlreadyShown();
		$groups        = array();
		foreach ( $rows as $faq ) {
			$id = is_object( $faq ) ? (int) ( $faq->id ?? 0 ) : (int) ( $faq['id'] ?? 0 );
			if ( $id > 0 && isset( $already_shown[ $id ] ) ) {
				continue;
			}
			$section              = is_object( $faq ) ? (string) ( $faq->section ?? '' ) : (string) ( $faq['section'] ?? '' );
			$groups[ $section ][] = $faq;
		}
		if ( empty( $groups ) ) {
			return;
		}

		adn_component( 'sections/faqs_footer', array( 'groups' => $groups ) );
	}

	public static function purgeFaqsCache(): void {
		delete_transient( self::faqsCacheKey( 0, true ) );
		if ( function_exists( 'adn_cms_available' ) && adn_cms_available() && class_exists( 'AH_Pages_Model' ) ) {
			try {
				foreach ( ( new \AH_Pages_Model() )->get_active() as $page ) {
					delete_transient( self::faqsCacheKey( (int) $page->id, true ) );
					delete_transient( self::faqsCacheKey( (int) $page->id, false ) );
				}
			} catch ( \Throwable $e ) {
				// Nothing to purge.
			}
		}

		global $wpdb;
		$like = $wpdb->esc_like( '_transient_adn_faqs_slug_' ) . '%';
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $like ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$like_timeout = $wpdb->esc_like( '_transient_timeout_adn_faqs_slug_' ) . '%';
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $like_timeout ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
}
