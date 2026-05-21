<?php
defined( 'ABSPATH' ) || exit;

/**
 * AH_Schema
 * Owns all CREATE TABLE statements for the theme's custom database tables.
 * Keeps schema definitions separate from the seeder so table structure can be
 * changed without digging through seed data.
 *
 * Called from AH_Theme_Seeder::create_tables().
 */
class AH_Schema {

	public static function create_all(): void {
		global $wpdb;
		$cs = $wpdb->get_charset_collate();

		self::create_services( $cs );
		self::create_team( $cs );
		self::create_reviews( $cs );
		self::create_faqs( $cs );
		self::create_news_bar( $cs );
		// ah_taxonomy_types and ah_taxonomies are owned by the CMS plugin — not created here.
	}

	// ── Table definitions ─────────────────────────────────────────────────────

	private static function create_services( string $cs ): void {
		global $wpdb;
		$wpdb->query( "CREATE TABLE IF NOT EXISTS `" . ah_theme_table( 'services' ) . "` (
			id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			title      VARCHAR(255) NOT NULL,
			summary    TEXT,
			icon       VARCHAR(100),
			status     ENUM('active','inactive') DEFAULT 'active',
			sort_order INT DEFAULT 0,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB {$cs}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	private static function create_team( string $cs ): void {
		global $wpdb;
		$wpdb->query( "CREATE TABLE IF NOT EXISTS `" . ah_theme_table( 'team' ) . "` (
			id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			name       VARCHAR(200) NOT NULL,
			role       VARCHAR(200),
			bio        TEXT,
			photo_url  VARCHAR(500),
			status     ENUM('active','inactive') DEFAULT 'active',
			sort_order INT DEFAULT 0,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB {$cs}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	private static function create_reviews( string $cs ): void {
		global $wpdb;
		$wpdb->query( "CREATE TABLE IF NOT EXISTS `" . ah_theme_table( 'reviews' ) . "` (
			id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			author_name VARCHAR(200) NOT NULL,
			location    VARCHAR(200),
			review_text TEXT NOT NULL,
			rating      TINYINT UNSIGNED DEFAULT 5,
			result      VARCHAR(200),
			status      ENUM('active','inactive') DEFAULT 'active',
			created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB {$cs}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	private static function create_faqs( string $cs ): void {
		global $wpdb;
		$wpdb->query( "CREATE TABLE IF NOT EXISTS `" . ah_theme_table( 'faqs' ) . "` (
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
		$wpdb->query( "CREATE TABLE IF NOT EXISTS `" . ah_theme_table( 'news_bar' ) . "` (
			id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			message    VARCHAR(500) NOT NULL,
			status     ENUM('active','inactive') DEFAULT 'active',
			sort_order INT DEFAULT 0,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB {$cs}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

}
