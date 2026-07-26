<?php
/**
 * apis/HomeFragmentCache.php - Thin wrappers delegating to OOP class.
 *
 * @package Adn\Theme\Feature\Cache
 */
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/../src/Feature/Cache/HomeFragmentCache.php';

if ( ! defined( 'ADN_HOME_FRAG_TTL' ) ) {
	define( 'ADN_HOME_FRAG_TTL', HOUR_IN_SECONDS );
}

function adn_home_frag_key( string $section ): string {
	return \Adn\Theme\Feature\Cache\HomeFragmentCache::key( $section );
}

function adn_home_frag_get( string $section ) {
	return \Adn\Theme\Feature\Cache\HomeFragmentCache::get( $section );
}

function adn_home_frag_set( string $section, string $html ): void {
	\Adn\Theme\Feature\Cache\HomeFragmentCache::set( $section, $html );
}

function adn_home_frag_purge_all(): void {
	\Adn\Theme\Feature\Cache\HomeFragmentCache::purgeAll();
}

function adn_home_frag_render( string $section, bool $store = true ): string {
	return \Adn\Theme\Feature\Cache\HomeFragmentCache::render( $section, $store );
}

function adn_home_frag_prewarm(): void {
	\Adn\Theme\Feature\Cache\HomeFragmentCache::prewarm();
}

// Register hooks via OOP class.
\Adn\Theme\Feature\Cache\HomeFragmentCache::register();
