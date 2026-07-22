<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Installer;

use CmsSuggestionBot\Database\Schema;
use CmsSuggestionBot\Database\DB;

defined( 'ABSPATH' ) || exit;

/**
 * Creates/updates every cms_sug_bot_* table via dbDelta() and records the
 * applied version. Runs on activation and again on any version bump so
 * existing installs pick up schema changes automatically.
 */
final class Installer {

	private function __construct() {}

	public static function install(): void {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		foreach ( Schema::definitions() as $sql ) {
			dbDelta( $sql );
		}

		$wpdb = DB::wpdb();
		$wpdb->insert( DB::table( 'versions' ), array(
			'version'    => CSB_DB_VERSION,
			'applied_at' => current_time( 'mysql' ),
		) );

		update_option( CSB_DB_VERSION_OPTION, CSB_DB_VERSION );
	}

	/**
	 * Called on plugins_loaded - re-runs install() only when the stored DB
	 * version doesn't match the plugin's current CSB_DB_VERSION.
	 */
	public static function maybeUpgrade(): void {
		if ( get_option( CSB_DB_VERSION_OPTION ) !== CSB_DB_VERSION ) {
			self::install();
		}
	}
}
