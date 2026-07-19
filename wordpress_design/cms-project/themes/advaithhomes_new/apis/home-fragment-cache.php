<?php
/**
 * apis/home-fragment-cache.php
 *
 * WordPress-core transient caching for the home page HTML fragments.
 *
 * How this makes the site fast (same as every other fast WP site):
 *   1st visitor  → renders & stores HTML in transient → ~2s
 *   All others   → reads transient (1 DB read)         → ~30ms
 *   WP-Cron      → regenerates in background hourly    → visitors always get cached
 *
 * Cache keys: adn_hf_banners | adn_hf_news_row | adn_hf_tools | adn_hf_guides | adn_hf_resources
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'ADN_HOME_FRAG_TTL' ) ) {
	define( 'ADN_HOME_FRAG_TTL', HOUR_IN_SECONDS );
}

// ── Cache helpers ─────────────────────────────────────────────────────────────

function adn_home_frag_key( string $section ): string {
	return 'adn_hf_' . sanitize_key( $section );
}

function adn_home_frag_get( string $section ) {
	return get_transient( adn_home_frag_key( $section ) );
}

function adn_home_frag_set( string $section, string $html ): void {
	set_transient( adn_home_frag_key( $section ), $html, ADN_HOME_FRAG_TTL );
}

function adn_home_frag_purge_all(): void {
	foreach ( array( 'banners', 'news_row', 'tools', 'guides', 'resources' ) as $s ) {
		delete_transient( adn_home_frag_key( $s ) );
	}
}

// ── Render + cache one section ────────────────────────────────────────────────

function adn_home_frag_render( string $section, bool $store = true ): string {
	$logical = ADN_THEME_DIR . '/intermediate/page_home_logical.php';
	if ( ! function_exists( 'adn_home_get_fragment_context' ) && file_exists( $logical ) ) {
		require_once $logical;
	}
	if ( ! function_exists( 'adn_home_get_fragment_context' ) || ! function_exists( 'adn_component' ) ) {
		return '';
	}
	$ctx = adn_home_get_fragment_context( $section );
	ob_start();
	adn_component( 'sections/home_deferred_section', array(
		'section' => $section,
		'ctx'     => $ctx,
	) );
	$html = trim( (string) ob_get_clean() );
	if ( $store && '' !== $html ) {
		adn_home_frag_set( $section, $html );
	}
	return $html;
}

// ── Cache invalidation hooks ──────────────────────────────────────────────────

add_action( 'save_post',      'adn_home_frag_purge_all' );
add_action( 'deleted_post',   'adn_home_frag_purge_all' );
add_action( 'trashed_post',   'adn_home_frag_purge_all' );
add_action( 'untrashed_post', 'adn_home_frag_purge_all' );

foreach ( array(
	'adn_home_newsblocks',
	'adn_home_resources',
	'adn_home_featured',
	'adn_home_sections',
	'adn_calculators_meta',
	'adn_journey_card_images',
	'adn_home_page_options',
) as $_adn_hfc_opt ) {
	add_action( 'update_option_' . $_adn_hfc_opt, 'adn_home_frag_purge_all' );
	add_action( 'add_option_'    . $_adn_hfc_opt, 'adn_home_frag_purge_all' );
}
unset( $_adn_hfc_opt );

// ── WP-Cron: pre-warm every hour ─────────────────────────────────────────────

function adn_home_frag_prewarm(): void {
	foreach ( array( 'banners', 'news_row', 'tools', 'guides', 'resources' ) as $section ) {
		adn_home_frag_render( $section, true );
	}
}
add_action( 'adn_prewarm_home_frags', 'adn_home_frag_prewarm' );

add_action( 'init', static function () {
	if ( ! wp_next_scheduled( 'adn_prewarm_home_frags' ) ) {
		wp_schedule_event( time() + 30, 'hourly', 'adn_prewarm_home_frags' );
	}
} );
