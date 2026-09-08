<?php
namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

class HowItWorksContext {

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

	/**
	 * Build a cache key that includes the JSON file's last-modified time.
	 * This means the cache auto-invalidates the moment the JSON file is saved —
	 * no manual cache-clearing required after content edits.
	 */
	private static function buildCacheKey(): string {
		$json_path = get_template_directory() . '/data/advaith/json/how-it-works.json';
		$mtime     = file_exists( $json_path ) ? (string) filemtime( $json_path ) : '0';
		return 'page_how_it_works_context_v' . $mtime;
	}

	public static function getContext() {
		$cache_key = self::buildCacheKey();
		$cached = self::cacheGet( $cache_key );
		if ( false !== $cached ) { return $cached; }

		$data = function_exists( 'adn_service_how_it_works_data' ) ? adn_service_how_it_works_data() : array();
		if ( empty( $data ) ) {
			$json_path = get_template_directory() . '/data/advaith/json/how-it-works.json';
			if ( file_exists( $json_path ) ) {
				$raw = file_get_contents( $json_path );
				$decoded = json_decode( $raw, true );
				if ( is_array( $decoded ) ) {
					$data = $decoded;
				}
			}
		}
		$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();

		$contact_sidebar = isset( $data['contact_sidebar'] ) ? (array) $data['contact_sidebar'] : array();
		if ( function_exists( 'adn_get_page_sidebar_data' ) ) {
			$global_sidebar = adn_get_page_sidebar_data( get_queried_object_id() );
			foreach ( array( 'whatsapp', 'email', 'phone', 'address' ) as $key ) {
				if ( ! isset( $contact_sidebar[$key] ) && isset( $global_sidebar[$key] ) ) {
					$contact_sidebar[$key] = $global_sidebar[$key];
				}
			}
		}

		$result = array(
			'meta'              => isset( $data['meta'] )              ? (array) $data['meta']              : array(),
			'breadcrumb'        => isset( $data['breadcrumb'] )        ? (array) $data['breadcrumb']        : array(),
			'hero'              => isset( $data['hero'] )              ? (array) $data['hero']              : array(),
			'stats'             => isset( $data['stats'] )             ? (array) $data['stats']             : array(),
			'commitment_notice' => isset( $data['commitment_notice'] ) ? (array) $data['commitment_notice'] : array(),
			'process'           => isset( $data['process'] )           ? (array) $data['process']           : array(),
			'story'             => isset( $data['story'] )             ? (array) $data['story']             : array(),
			'benefits'          => isset( $data['benefits'] )          ? (array) $data['benefits']          : array(),
			'comparison'        => isset( $data['comparison'] )        ? (array) $data['comparison']        : array(),
			'fit_check'         => isset( $data['fit_check'] )         ? (array) $data['fit_check']         : array(),
			'why_choose'        => isset( $data['why_choose'] )        ? (array) $data['why_choose']        : array(),
			'trust_callout'     => isset( $data['trust_callout'] )     ? (array) $data['trust_callout']     : array(),
			'faq_teaser'        => isset( $data['faq_teaser'] )        ? (array) $data['faq_teaser']        : array(),
			'cta_banner'        => isset( $data['cta_banner'] )        ? (array) $data['cta_banner']        : array(),
			'contact_sidebar'   => $contact_sidebar,
			'chrome'            => $chrome,
		);

		self::cacheSet( $cache_key, $result );
		return $result;
	}
}
