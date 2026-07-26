<?php
namespace Adn\Theme\Feature\Cache;

defined( 'ABSPATH' ) || exit;

class ThemeCacheHandler {

	public static function handleClear(): void {
		if ( class_exists( 'ADN_Cache' ) ) {
			if ( is_admin() && isset( $_POST['clear_cache'] ) && current_user_can( 'manage_options' ) ) {
				\ADN_Cache::clear_all();
			}
		}
	}

	public static function addAdminBar( $wp_admin_bar ): void {
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
}
