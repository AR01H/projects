<?php
defined( 'ABSPATH' ) || exit;

/**
 * CH_Schema
 * Owns all CREATE TABLE statements for the theme's custom database tables.
 * Keeps schema definitions separate from the seeder so table structure can be
 * changed without digging through seed data.
 *
 * Called from CH_Theme_Seeder::create_tables().
 */
class CH_Schema {

	public static function create_all(): void {
		global $wpdb;
		$cs = $wpdb->get_charset_collate();

		self::create_reviews( $cs );
		self::create_faqs( $cs );
		self::create_news_bar( $cs );
		self::create_contact_submissions( $cs );
	}

	// ── Table definitions ─────────────────────────────────────────────────────

	private static function create_reviews( string $cs ): void {
		global $wpdb;
		$wpdb->query( "CREATE TABLE IF NOT EXISTS `" . ch_theme_table( 'reviews' ) . "` (
			id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			author_name VARCHAR(200) NOT NULL,
			location    VARCHAR(200),
			review_text TEXT NOT NULL,
			rating      DECIMAL(3,1) UNSIGNED DEFAULT 5.0,
			result      VARCHAR(200),
			status      ENUM('active','inactive') DEFAULT 'active',
			created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB {$cs}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	private static function create_faqs( string $cs ): void {
		global $wpdb;
		$wpdb->query( "CREATE TABLE IF NOT EXISTS `" . ch_theme_table( 'faqs' ) . "` (
			id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			topic      VARCHAR(150),
			question   TEXT NOT NULL,
			answer     TEXT NOT NULL,
			status     ENUM('active','inactive') DEFAULT 'active',
			sort_order INT DEFAULT 0,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB {$cs}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	private static function create_news_bar( string $cs ): void {
		global $wpdb;
		$wpdb->query( "CREATE TABLE IF NOT EXISTS `" . ch_theme_table( 'news_bar' ) . "` (
			id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			message    VARCHAR(500) NOT NULL,
			status     ENUM('active','inactive') DEFAULT 'active',
			sort_order INT DEFAULT 0,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB {$cs}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	private static function create_contact_submissions( string $cs ): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$table = $wpdb->prefix . 'ch_contact_submissions';
		$sql   = "CREATE TABLE IF NOT EXISTS `{$table}` (
			id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name          VARCHAR(200)    NOT NULL DEFAULT '',
			email         VARCHAR(200)    NOT NULL DEFAULT '',
			phone         VARCHAR(50)     NOT NULL DEFAULT '',
			enquiry_type  VARCHAR(100)    NOT NULL DEFAULT 'general',
			message       TEXT            NOT NULL DEFAULT '',
			ip_address    VARCHAR(50)     NOT NULL DEFAULT '',
			created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) {$cs};";
		dbDelta( $sql );
	}

}
