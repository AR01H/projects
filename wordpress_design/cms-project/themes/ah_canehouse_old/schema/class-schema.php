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
		// FAQs are owned by the CMS plugin (ah_faqs); the theme has no faqs table.
		self::create_news_bar( $cs );
		self::create_contact_submissions( $cs );
		self::create_contact_logs( $cs );
		self::create_services( $cs );
		self::create_about_team( $cs );
		self::create_blog_posts( $cs );
		self::create_order_requests( $cs );
		self::create_order_activity_logs( $cs );
		self::create_certifications( $cs );
		self::create_booking_requests( $cs );
		self::create_booking_logs( $cs );
		self::create_franchise_enquiries( $cs );
		self::create_franchise_logs( $cs );
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
			status        VARCHAR(50)     NOT NULL DEFAULT 'new',
			admin_notes   TEXT            NOT NULL DEFAULT '',
			ip_address    VARCHAR(50)     NOT NULL DEFAULT '',
			created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY status (status),
			KEY created_at (created_at)
		) {$cs};";
		dbDelta( $sql );
	}

	private static function create_contact_logs( string $cs ): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$table = $wpdb->prefix . 'ch_contact_logs';
		$sql   = "CREATE TABLE IF NOT EXISTS `{$table}` (
			id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			submission_id   BIGINT UNSIGNED NOT NULL DEFAULT 0,
			action          VARCHAR(100)    NOT NULL DEFAULT '',
			old_value       TEXT            NOT NULL DEFAULT '',
			new_value       TEXT            NOT NULL DEFAULT '',
			admin_user_id   BIGINT UNSIGNED NOT NULL DEFAULT 0,
			admin_user_name VARCHAR(200)    NOT NULL DEFAULT '',
			created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY submission_id (submission_id)
		) {$cs};";
		dbDelta( $sql );
	}

	private static function create_services( string $cs ): void {
		global $wpdb;
		$wpdb->query( "CREATE TABLE IF NOT EXISTS `" . ch_theme_table( 'services' ) . "` (
			id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			icon        VARCHAR(10),
			title       VARCHAR(150) NOT NULL,
			description TEXT NOT NULL,
			details     TEXT,
			image_url   VARCHAR(500),
			status      ENUM('active','inactive') DEFAULT 'active',
			sort_order  INT DEFAULT 0,
			created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB {$cs}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	private static function create_booking_requests( string $cs ): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$table = $wpdb->prefix . 'ch_booking_requests';
		$sql   = "CREATE TABLE IF NOT EXISTS `{$table}` (
			id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name          VARCHAR(200)    NOT NULL DEFAULT '',
			email         VARCHAR(200)    NOT NULL DEFAULT '',
			phone         VARCHAR(50)     NOT NULL DEFAULT '',
			cane_types    TEXT            NOT NULL DEFAULT '',
			textures      TEXT            NOT NULL DEFAULT '',
			flavours      TEXT            NOT NULL DEFAULT '',
			occasion      VARCHAR(200)    NOT NULL DEFAULT '',
			event_date    DATE                         NULL,
			guest_count   INT UNSIGNED    NOT NULL DEFAULT 0,
			location      VARCHAR(300)    NOT NULL DEFAULT '',
			notes         TEXT            NOT NULL DEFAULT '',
			status        VARCHAR(50)     NOT NULL DEFAULT 'new',
			admin_notes   TEXT            NOT NULL DEFAULT '',
			ip_address    VARCHAR(50)     NOT NULL DEFAULT '',
			created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY status (status),
			KEY created_at (created_at)
		) {$cs};";
		dbDelta( $sql );
	}

	private static function create_booking_logs( string $cs ): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$table = $wpdb->prefix . 'ch_booking_logs';
		$sql   = "CREATE TABLE IF NOT EXISTS `{$table}` (
			id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			booking_id      BIGINT UNSIGNED NOT NULL DEFAULT 0,
			action          VARCHAR(100)    NOT NULL DEFAULT '',
			old_value       TEXT            NOT NULL DEFAULT '',
			new_value       TEXT            NOT NULL DEFAULT '',
			admin_user_id   BIGINT UNSIGNED NOT NULL DEFAULT 0,
			admin_user_name VARCHAR(200)    NOT NULL DEFAULT '',
			created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY booking_id (booking_id)
		) {$cs};";
		dbDelta( $sql );
	}

	private static function create_franchise_enquiries( string $cs ): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$table = $wpdb->prefix . 'ch_franchise_enquiries';
		$sql   = "CREATE TABLE IF NOT EXISTS `{$table}` (
			id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name             VARCHAR(200)    NOT NULL DEFAULT '',
			email            VARCHAR(200)    NOT NULL DEFAULT '',
			phone            VARCHAR(50)     NOT NULL DEFAULT '',
			city             VARCHAR(200)    NOT NULL DEFAULT '',
			franchise_type   VARCHAR(200)    NOT NULL DEFAULT '',
			timeline         VARCHAR(200)    NOT NULL DEFAULT '',
			investment_range VARCHAR(200)    NOT NULL DEFAULT '',
			experience       VARCHAR(200)    NOT NULL DEFAULT '',
			message          TEXT            NOT NULL DEFAULT '',
			status           VARCHAR(50)     NOT NULL DEFAULT 'new',
			admin_notes      TEXT            NOT NULL DEFAULT '',
			ip_address       VARCHAR(50)     NOT NULL DEFAULT '',
			created_at       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY status (status),
			KEY created_at (created_at)
		) {$cs};";
		dbDelta( $sql );
	}

	private static function create_franchise_logs( string $cs ): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$table = $wpdb->prefix . 'ch_franchise_logs';
		$sql   = "CREATE TABLE IF NOT EXISTS `{$table}` (
			id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			enquiry_id      BIGINT UNSIGNED NOT NULL DEFAULT 0,
			action          VARCHAR(100)    NOT NULL DEFAULT '',
			old_value       TEXT            NOT NULL DEFAULT '',
			new_value       TEXT            NOT NULL DEFAULT '',
			admin_user_id   BIGINT UNSIGNED NOT NULL DEFAULT 0,
			admin_user_name VARCHAR(200)    NOT NULL DEFAULT '',
			created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY enquiry_id (enquiry_id)
		) {$cs};";
		dbDelta( $sql );
	}

	private static function create_certifications( string $cs ): void {
		global $wpdb;
		$wpdb->query( "CREATE TABLE IF NOT EXISTS `" . ch_theme_table( 'certifications' ) . "` (
			id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			icon       VARCHAR(20)  NOT NULL DEFAULT '✅',
			title      VARCHAR(200) NOT NULL DEFAULT '',
			descr      TEXT         NOT NULL DEFAULT '',
			badge      VARCHAR(100) NOT NULL DEFAULT '',
			sort_order INT          NOT NULL DEFAULT 0,
			status     ENUM('active','inactive') DEFAULT 'active',
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB {$cs}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	private static function create_about_team( string $cs ): void {
		global $wpdb;
		$wpdb->query( "CREATE TABLE IF NOT EXISTS `" . ch_theme_table( 'about_team' ) . "` (
			id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			name        VARCHAR(200) NOT NULL,
			role        VARCHAR(100),
			bio         TEXT,
			image_url   VARCHAR(500),
			status      ENUM('active','inactive') DEFAULT 'active',
			sort_order  INT DEFAULT 0,
			created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB {$cs}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	private static function create_order_requests( string $cs ): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$table = $wpdb->prefix . 'ch_order_requests';
		$sql   = "CREATE TABLE IF NOT EXISTS `{$table}` (
			id               BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
			name             VARCHAR(200)     NOT NULL DEFAULT '',
			email            VARCHAR(200)     NOT NULL DEFAULT '',
			phone            VARCHAR(50)      NOT NULL DEFAULT '',
			delivery_address TEXT             NOT NULL DEFAULT '',
			delivery_area    VARCHAR(200)     NOT NULL DEFAULT '',
			preferred_date   DATE                         NULL,
			preferred_time   VARCHAR(50)      NOT NULL DEFAULT '',
			items            LONGTEXT         NOT NULL DEFAULT '',
			special_notes    TEXT             NOT NULL DEFAULT '',
			status           VARCHAR(50)      NOT NULL DEFAULT 'new',
			admin_notes      TEXT             NOT NULL DEFAULT '',
			ip_address       VARCHAR(50)      NOT NULL DEFAULT '',
			created_at       DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY status (status),
			KEY created_at (created_at)
		) {$cs};";
		dbDelta( $sql );
	}

	private static function create_order_activity_logs( string $cs ): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$table = $wpdb->prefix . 'ch_order_activity_logs';
		$sql   = "CREATE TABLE IF NOT EXISTS `{$table}` (
			id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			order_id        BIGINT UNSIGNED NOT NULL DEFAULT 0,
			action          VARCHAR(100)    NOT NULL DEFAULT '',
			field_name      VARCHAR(100)    NOT NULL DEFAULT '',
			old_value       TEXT            NOT NULL DEFAULT '',
			new_value       TEXT            NOT NULL DEFAULT '',
			admin_user_id   BIGINT UNSIGNED NOT NULL DEFAULT 0,
			admin_user_name VARCHAR(200)    NOT NULL DEFAULT '',
			created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY order_id (order_id)
		) {$cs};";
		dbDelta( $sql );
	}

	private static function create_blog_posts( string $cs ): void {
		global $wpdb;
		$wpdb->query( "CREATE TABLE IF NOT EXISTS `" . ch_theme_table( 'blog_posts' ) . "` (
			id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			title        VARCHAR(300) NOT NULL,
			slug         VARCHAR(300) UNIQUE,
			content      LONGTEXT NOT NULL,
			excerpt      TEXT,
			featured_image VARCHAR(500),
			author       VARCHAR(200),
			category     VARCHAR(100),
			status       ENUM('published','draft','archived') DEFAULT 'draft',
			published_at DATETIME,
			created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
		) ENGINE=InnoDB {$cs}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

}
