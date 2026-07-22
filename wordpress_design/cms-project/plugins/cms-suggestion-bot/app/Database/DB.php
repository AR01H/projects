<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Database;

defined( 'ABSPATH' ) || exit;

/**
 * Thin $wpdb accessor. Repositories are the only classes that should call
 * this - everything else goes through a Repository so raw SQL never leaks
 * into Services, Controllers, or Admin pages.
 */
final class DB {

	private function __construct() {}

	/**
	 * Full prefixed table name, e.g. table('cache') -> wp_cms_sug_bot_cache.
	 */
	public static function table( string $name ): string {
		global $wpdb;

		return $wpdb->prefix . CSB_TABLE_PREFIX . $name;
	}

	public static function wpdb(): \wpdb {
		global $wpdb;

		return $wpdb;
	}

	public static function tableExists( string $full_table_name ): bool {
		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $full_table_name ) ) === $full_table_name;
	}
}
