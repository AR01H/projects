<?php
/**
 * HomeFragmentCache - Transient caching for home page HTML fragments.
 *
 * Cache keys: adn_hf_banners | adn_hf_news_row | adn_hf_tools | adn_hf_guides | adn_hf_resources
 */
namespace Adn\Theme\Feature\Cache;

defined( 'ABSPATH' ) || exit;

class HomeFragmentCache {

	private const SECTIONS = array( 'banners', 'news_row', 'tools', 'guides', 'resources' );

	private const OPTIONS_WATCH = array(
		'adn_home_newsblocks',
		'adn_home_resources',
		'adn_home_featured',
		'adn_home_sections',
		'adn_calculators_meta',
		'adn_journey_card_images',
		'adn_home_page_options',
	);

	public static function key( string $section ): string {
		return 'adn_hf_' . \sanitize_key( $section );
	}

	/** Read cached fragment. Returns false for logged-in users (admins always see live). */
	public static function get( string $section ) {
		if ( \is_user_logged_in() ) {
			return false;
		}
		return \get_transient( self::key( $section ) );
	}

	/** Store a rendered fragment. Skips when admin is logged in or cache-clear param present. */
	public static function set( string $section, string $html ): void {
		if ( \is_user_logged_in() || isset( $_GET['clear_cache'] ) || isset( $_GET['cache_clear'] ) ) {
			return;
		}
		\set_transient( self::key( $section ), $html, defined( 'ADN_HOME_FRAG_TTL' ) ? ADN_HOME_FRAG_TTL : \HOUR_IN_SECONDS );
	}

	/** Delete all home fragment transients. */
	public static function purgeAll(): void {
		foreach ( self::SECTIONS as $s ) {
			\delete_transient( self::key( $s ) );
		}
	}

	/** Render a section, optionally store the result. */
	public static function render( string $section, bool $store = true ): string {
		$logical = \ADN_THEME_DIR . '/intermediate/PageHomeLogical.php';
		if ( ! \function_exists( 'adn_home_get_fragment_context' ) && \file_exists( $logical ) ) {
			require_once $logical;
		}
		if ( ! \function_exists( 'adn_home_get_fragment_context' ) || ! \function_exists( 'adn_component' ) ) {
			return '';
		}
		$ctx = \adn_home_get_fragment_context( $section );
		\ob_start();
		\adn_component( 'sections/home_deferred_section', array(
			'section' => $section,
			'ctx'     => $ctx,
		) );
		$html = \trim( (string) \ob_get_clean() );
		if ( $store && '' !== $html ) {
			self::set( $section, $html );
		}
		return $html;
	}

	/** Pre-warm all sections. */
	public static function prewarm(): void {
		foreach ( self::SECTIONS as $section ) {
			self::render( $section, true );
		}
	}

	/** Register hooks: cache invalidation + cron pre-warm. */
	public static function register(): void {
		// Purge on post changes.
		\add_action( 'save_post',      array( __CLASS__, 'purgeAll' ) );
		\add_action( 'deleted_post',   array( __CLASS__, 'purgeAll' ) );
		\add_action( 'trashed_post',   array( __CLASS__, 'purgeAll' ) );
		\add_action( 'untrashed_post', array( __CLASS__, 'purgeAll' ) );

		// Purge on option changes.
		foreach ( self::OPTIONS_WATCH as $opt ) {
			\add_action( 'update_option_' . $opt, array( __CLASS__, 'purgeAll' ) );
			\add_action( 'add_option_'    . $opt, array( __CLASS__, 'purgeAll' ) );
		}

		// WP-Cron: pre-warm every hour.
		\add_action( 'adn_prewarm_home_frags', array( __CLASS__, 'prewarm' ) );
		\add_action( 'init', array( __CLASS__, 'scheduleCron' ) );
	}

	/** Schedule the hourly pre-warm cron if not already scheduled. */
	public static function scheduleCron(): void {
		if ( ! \wp_next_scheduled( 'adn_prewarm_home_frags' ) ) {
			\wp_schedule_event( time() + 30, 'hourly', 'adn_prewarm_home_frags' );
		}
	}
}
