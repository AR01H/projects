<?php
/**
 * Theme Cache Management
 *
 * Cache clearing logic and admin bar "Clear Cache" button.
 *
 * @package Adn\Theme\Common\Cache
 */
defined( 'ABSPATH' ) || exit;

/**
 * Clear or bypass cache intercepts.
 */
function adn_handle_cache_clear(): void {
	if ( class_exists( 'ADN_Cache' ) ) {
		if ( is_admin() && isset( $_POST['clear_cache'] ) && current_user_can( 'manage_options' ) ) {
			ADN_Cache::clear_all();
		}
		if ( isset( $_GET['clear_cache'] ) || isset( $_GET['cache_clear'] ) ) {
			if ( current_user_can( 'manage_options' ) || ! is_user_logged_in() ) {
				ADN_Cache::clear_all();
				if ( isset( $_GET['clear_cache'] ) ) {
					$redirect_url = remove_query_arg( 'clear_cache' );
					wp_safe_redirect( $redirect_url );
					exit;
				}
			}
		}
	}
}

/**
 * Add "Clear Cache" button to the WP Admin Bar.
 */
function adn_add_cache_clear_admin_bar( $wp_admin_bar ) {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$current_url = admin_url();
	$clear_url = add_query_arg( 'clear_cache', '1', $current_url );
	$wp_admin_bar->add_node( array(
		'id'    => 'adn-clear-cache',
		'title' => '⚡ Clear Cache',
		'href'  => $clear_url,
		'meta'  => array(
			'title' => 'Clear all theme filesystem and CMS caches',
		),
	) );
}
