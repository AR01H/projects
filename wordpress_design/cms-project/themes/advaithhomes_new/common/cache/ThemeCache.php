<?php
/**
 * Theme Cache Management — Thin wrappers delegating to OOP class.
 *
 * @package Adn\Theme\Common\Cache
 */
defined( 'ABSPATH' ) || exit;

function adn_handle_cache_clear(): void {
	\Adn\Theme\Feature\Cache\ThemeCacheHandler::handleClear();
}

function adn_add_cache_clear_admin_bar( $wp_admin_bar ) {
	\Adn\Theme\Feature\Cache\ThemeCacheHandler::addAdminBar( $wp_admin_bar );
}
