<?php
defined( 'ABSPATH' ) || exit;

/**
 * Orchestrates database setup and upgrades.
 *
 * Delegates to four focused classes:
 *   AH_DB_Schema        - CREATE TABLE definitions
 *   AH_DB_Foreign_Keys  - ALTER TABLE FK constraints
 *   AH_DB_Seed          - default data on fresh install
 *   AH_DB_Migrations    - idempotent column/data migrations per client
 *
 * For new clients  → install() creates everything fresh.
 * For existing clients → maybe_upgrade() runs only migrations.
 * Adding a new column → add to AH_DB_Schema + add ensure method in AH_DB_Migrations.
 */
class AH_DB_Installer {

	public static function install(): void {
		global $wpdb;
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 0' );

		foreach ( AH_DB_Schema::tables() as $sql ) {
			$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		AH_DB_Foreign_Keys::apply();
		AH_DB_Seed::run();

		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 1' );

		update_option( AH_DB_VERSION_KEY, AH_THEME_VERSION );
	}

	public static function maybe_upgrade(): void {
		if ( get_option( AH_DB_VERSION_KEY ) !== AH_THEME_VERSION ) {
			self::install();
			AH_DB_Foreign_Keys::drop_broken();
			AH_DB_Migrations::run();
		}
	}

	public static function table( string $name ): string {
		global $wpdb;
		return $wpdb->prefix . 'ah_' . $name;
	}
}
