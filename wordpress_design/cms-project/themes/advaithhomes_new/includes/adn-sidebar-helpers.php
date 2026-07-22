<?php
/**
 * includes/adn-sidebar-helpers.php
 *
 * adn_get_page_sidebar_data( $page_id ) - reusable sidebar data builder.
 * Returns an array compatible with components/parts/contact_sidebar.php.
 * Usage on any page template:
 *   adn_component( 'parts/contact_sidebar', array(
 *       'page_sidebar' => adn_get_page_sidebar_data( get_queried_object_id() ),
 *   ) );
 */
defined( 'ABSPATH' ) || exit;

/**
 * Build sidebar data (contact details + optional page FAQs) for any page.
 *
 * @param int $page_id  WP post/page ID; pass 0 or omit for global FAQs only.
 * @param int $faq_limit Max sidebar FAQ items (default 3).
 * @return array  Keys: whatsapp, email, faqs, coming_soon (same shape as contact_sidebar).
 */
function adn_get_page_sidebar_data( int $page_id = 0, int $faq_limit = 3 ): array {
	// Base contact info comes from adn_service_contact_data() (merges JSON + DB).
	$sidebar = array();
	if ( function_exists( 'adn_service_contact_data' ) ) {
		$contact_data = adn_service_contact_data();
		if ( isset( $contact_data['contact_sidebar'] ) && is_array( $contact_data['contact_sidebar'] ) ) {
			$sidebar = $contact_data['contact_sidebar'];
		}
	}

	// FAQs: page-specific first, fall back to global.
	if ( function_exists( 'adn_cms_available' ) && adn_cms_available() && class_exists( 'AH_Faqs_Model' ) ) {
		try {
			$model = new AH_Faqs_Model();
			$faqs  = $page_id > 0 ? $model->get_for_page( $page_id ) : array();
			if ( empty( $faqs ) ) {
				$faqs = $model->get_global();
			}
			if ( ! empty( $faqs ) ) {
				$sidebar['faqs'] = array_slice( $faqs, 0, $faq_limit );
			}
		} catch ( Throwable $e ) {
			// If model fails, just show no FAQs.
		}
	}

	return $sidebar;
}

/**
 * Resolve a CMS-plugin "page" registry row's ID by its page_type (e.g. 'home',
 * 'contact', 'services'). This is AH_Pages_Model's own row ID - NOT the
 * WordPress post ID - and is what the "Attached Page" dropdown in the FAQs
 * admin actually stores on each FAQ's page_id column.
 *
 * @param string $type page_type value.
 * @return int 0 if not found or the plugin isn't active.
 */
function adn_get_cms_page_id( string $type ): int {
	if ( ! function_exists( 'adn_cms_available' ) || ! adn_cms_available() || ! class_exists( 'AH_Pages_Model' ) ) {
		return 0;
	}
	try {
		$page = ( new AH_Pages_Model() )->get_by_type( $type );
		return $page->id ?? 0;
	} catch ( Throwable $e ) {
		return 0;
	}
}

if ( ! defined( 'ADN_FAQS_CACHE_TTL' ) ) {
	define( 'ADN_FAQS_CACHE_TTL', HOUR_IN_SECONDS );
}

function adn_faqs_cache_key( int $page_id, bool $fallback_global ): string {
	return 'adn_faqs_' . $page_id . '_' . ( $fallback_global ? 1 : 0 );
}

/**
 * Build a section-grouped FAQ list for a page. Cached in a transient (see
 * ADN_FAQS_CACHE_TTL) so repeat visits skip the DB; the FAQs admin page fires
 * ah_faqs_changed on save/delete which purges the cache immediately via
 * adn_purge_faqs_cache() below. Drag-reorder (sort_order only) isn't hooked,
 * so an order change can take up to ADN_FAQS_CACHE_TTL to show.
 *
 * Returns e.g. [ 'Common Questions' => [...], 'Buying Questions' => [...], '' => [...ungrouped...] ].
 *
 * @param int  $page_id         AH_Pages_Model row ID (see adn_get_cms_page_id()); pass 0 to only use Global FAQs.
 * @param bool $fallback_global When true and the page has no page-specific FAQs, use Global FAQs instead.
 * @return array<string,array>
 */
function adn_get_page_faqs_grouped( int $page_id = 0, bool $fallback_global = true ): array {
	$cache_key = adn_faqs_cache_key( $page_id, $fallback_global );
	$cached    = get_transient( $cache_key );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$groups = array();
	if ( ! function_exists( 'adn_cms_available' ) || ! adn_cms_available() || ! class_exists( 'AH_Faqs_Model' ) ) {
		return $groups;
	}
	try {
		$model = new AH_Faqs_Model();
		$faqs  = $page_id > 0 ? $model->get_for_page( $page_id ) : array();
		if ( empty( $faqs ) && $fallback_global ) {
			$faqs = $model->get_global();
		}
	} catch ( Throwable $e ) {
		return $groups;
	}
	foreach ( (array) $faqs as $faq ) {
		$section              = is_object( $faq ) ? (string) ( $faq->section ?? '' ) : (string) ( $faq['section'] ?? '' );
		$groups[ $section ][] = $faq;
	}

	set_transient( $cache_key, $groups, ADN_FAQS_CACHE_TTL );
	adn_mark_faqs_shown( $groups );
	return $groups;
}

/**
 * Tracks which FAQ IDs have already been printed on the current request (via
 * adn_get_page_faqs_grouped) so adn_render_slug_attached_faqs() never shows
 * the same FAQ twice on a page that already has it via Attached Page/Global.
 *
 * @param array<string,array> $groups
 */
function adn_mark_faqs_shown( array $groups ): void {
	static $shown = array();
	if ( empty( $groups ) ) {
		return;
	}
	foreach ( $groups as $items ) {
		foreach ( (array) $items as $faq ) {
			$id = is_object( $faq ) ? (int) ( $faq->id ?? 0 ) : (int) ( $faq['id'] ?? 0 );
			if ( $id > 0 ) {
				$shown[ $id ] = true;
			}
		}
	}
	// Stash on the static via a getter call so both functions share state
	// without needing a class - see adn_faqs_already_shown() below.
	adn_faqs_already_shown( $shown );
}

/**
 * Getter/setter for the shown-FAQ-ID registry (see adn_mark_faqs_shown()).
 * Call with no args to read; pass $merge to add more IDs to the set.
 *
 * @param array<int,bool> $merge
 * @return array<int,bool>
 */
function adn_faqs_already_shown( array $merge = array() ): array {
	static $ids = array();
	if ( ! empty( $merge ) ) {
		$ids = $merge + $ids;
	}
	return $ids;
}

/**
 * Print any FAQs attached directly to the current page's URL slug (see the
 * "Attached Slug" field on wp-admin -> FAQs), grouped by section, above the
 * footer. Skips any FAQ already shown on this page via Attached Page/Global
 * (adn_get_page_faqs_grouped) so nothing repeats. Called from adn_page_close()
 * so it works on every page template with no per-template wiring needed.
 */
function adn_render_slug_attached_faqs(): void {
	if ( ! function_exists( 'adn_cms_available' ) || ! adn_cms_available() || ! class_exists( 'AH_Faqs_Model' ) ) {
		return;
	}

	// Category/topic guide pages are virtual routes (see includes/core_routing.php) -
	// they never set get_queried_object() to a real WP_Post/WP_Term, so the slug has
	// to be read from the query vars those routers set instead. Priority: parent-term
	// (category) slug, then child-topic slug, then a regular post/page's own slug.
	$slug = (string) get_query_var( 'adn_cat_slug', '' );
	if ( '' === $slug ) {
		$slug = (string) get_query_var( 'adn_guide_term_slug', '' );
	}
	if ( '' === $slug ) {
		$queried = get_queried_object();
		$slug    = ( $queried instanceof WP_Post ) ? (string) $queried->post_name : '';
	}
	if ( '' === $slug ) {
		return;
	}

	$cache_key = 'adn_faqs_slug_' . sanitize_key( $slug );
	$rows      = get_transient( $cache_key );
	if ( ! is_array( $rows ) ) {
		try {
			$rows = ( new AH_Faqs_Model() )->get_by_slug( $slug );
		} catch ( Throwable $e ) {
			$rows = array();
		}
		set_transient( $cache_key, $rows, ADN_FAQS_CACHE_TTL );
	}
	if ( empty( $rows ) ) {
		return;
	}

	$already_shown = adn_faqs_already_shown();
	$groups        = array();
	foreach ( $rows as $faq ) {
		$id = is_object( $faq ) ? (int) ( $faq->id ?? 0 ) : (int) ( $faq['id'] ?? 0 );
		if ( $id > 0 && isset( $already_shown[ $id ] ) ) {
			continue; // Already printed via Attached Page/Global on this page.
		}
		$section              = is_object( $faq ) ? (string) ( $faq->section ?? '' ) : (string) ( $faq['section'] ?? '' );
		$groups[ $section ][] = $faq;
	}
	if ( empty( $groups ) ) {
		return;
	}

	adn_component( 'sections/faqs_footer', array( 'groups' => $groups ) );
}

/**
 * Purge every cached FAQ grouping. Hooked to ah_faqs_changed (fired by the
 * CMS plugin's FAQs admin page after a save or delete).
 */
function adn_purge_faqs_cache(): void {
	delete_transient( adn_faqs_cache_key( 0, true ) );
	if ( function_exists( 'adn_cms_available' ) && adn_cms_available() && class_exists( 'AH_Pages_Model' ) ) {
		try {
			foreach ( ( new AH_Pages_Model() )->get_active() as $page ) {
				delete_transient( adn_faqs_cache_key( (int) $page->id, true ) );
				delete_transient( adn_faqs_cache_key( (int) $page->id, false ) );
			}
		} catch ( Throwable $e ) {
			// Nothing to purge.
		}
	}

	// Slug-attached FAQ cache is keyed per-slug (adn_faqs_slug_{slug}) - there's
	// no fixed list of slugs to loop like the page registry above, so clear by
	// LIKE match directly.
	global $wpdb;
	$like = $wpdb->esc_like( '_transient_adn_faqs_slug_' ) . '%';
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $like ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$like_timeout = $wpdb->esc_like( '_transient_timeout_adn_faqs_slug_' ) . '%';
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $like_timeout ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}
add_action( 'ah_faqs_changed', 'adn_purge_faqs_cache' );
