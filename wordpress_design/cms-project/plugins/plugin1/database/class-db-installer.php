<?php
defined( 'ABSPATH' ) || exit;

class AH_DB_Installer {

	public static function install(): void {
		global $wpdb;
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 0' );

		foreach ( self::get_table_sqls() as $sql ) {
			$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		self::add_foreign_keys();
		self::seed_defaults();

		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 1' );

		update_option( AH_DB_VERSION_KEY, AH_THEME_VERSION );
	}

	public static function maybe_upgrade(): void {
		if ( get_option( AH_DB_VERSION_KEY ) !== AH_THEME_VERSION ) {
			self::install();
		}
		self::ensure_builder_table();
		self::ensure_required_settings();
		self::ensure_content_taxonomies();
		self::drop_broken_fks();
		self::ensure_review_short_desc();
		self::ensure_news_bar_content();
		self::ensure_news_bar_image();
		self::ensure_protected_taxonomy();
		self::ensure_taxonomy_media();
		self::ensure_taxonomy_parent_terms();
		self::ensure_trigger_logs();
		self::ensure_events_table();
		self::ensure_events_notification_columns();
		self::ensure_review_taxonomy_type();
		self::ensure_review_categories_taxonomy_type();
		self::ensure_review_images_table();
		self::ensure_faq_tags_taxonomy_type();
		self::ensure_sugarcane_contact_rule();
		self::ensure_contact_form_rule_cc();
	}

	/**
	 * Create the events (hire packages) table if it does not exist.
	 */
	public static function ensure_events_table(): void {
		global $wpdb;
		$t  = $wpdb->prefix . 'ah_events';
		$cs = $wpdb->get_charset_collate();
		$wpdb->query( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			"CREATE TABLE IF NOT EXISTS `{$t}` (
				`id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`icon`        VARCHAR(30)  NOT NULL DEFAULT '🎉',
				`title`       VARCHAR(200) NOT NULL,
				`description` TEXT         DEFAULT NULL,
				`items`       JSON         DEFAULT NULL,
				`color`       VARCHAR(30)  NOT NULL DEFAULT 'green',
				`is_featured` TINYINT(1)   NOT NULL DEFAULT 0,
				`sort_order`  INT          NOT NULL DEFAULT 0,
				`status`      ENUM('active','inactive') NOT NULL DEFAULT 'active',
				`created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				KEY `idx_status`   (`status`),
				KEY `idx_featured` (`is_featured`),
				KEY `idx_sort`     (`sort_order`)
			) ENGINE=InnoDB {$cs}"
		);
	}

	/**
	 * Add notification columns to events table if they don't exist.
	 */
	public static function ensure_events_notification_columns(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_events';

		$has_notify = $wpdb->get_results( $wpdb->prepare(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'notify_on_booking'",
			DB_NAME,
			$table
		) );
		if ( empty( $has_notify ) ) {
			$wpdb->query( "ALTER TABLE `{$table}` ADD COLUMN `notify_on_booking` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		$has_trigger = $wpdb->get_results( $wpdb->prepare(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'booking_trigger_name'",
			DB_NAME,
			$table
		) );
		if ( empty( $has_trigger ) ) {
			$wpdb->query( "ALTER TABLE `{$table}` ADD COLUMN `booking_trigger_name` VARCHAR(100) DEFAULT NULL AFTER `notify_on_booking`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
	}

	public static function ensure_taxonomy_parent_terms(): void {
		if ( class_exists( 'AH_Taxonomy_Parent_Model' ) ) {
			AH_Taxonomy_Parent_Model::ensure_table();
		}
	}

	public static function ensure_content_taxonomies(): void {
		if ( class_exists( 'AH_Content_Taxonomy_Model' ) ) {
			AH_Content_Taxonomy_Model::ensure_table();
		}
	}

	/**
	 * Add short_desc column to ah_reviews if it doesn't exist yet.
	 */
	public static function ensure_review_short_desc(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_reviews';
		$col   = $wpdb->get_results( $wpdb->prepare(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'short_desc'",
			DB_NAME,
			$table
		) );
		if ( empty( $col ) ) {
			$wpdb->query( "ALTER TABLE `{$table}` ADD COLUMN `short_desc` VARCHAR(400) DEFAULT NULL AFTER `reviewer_title`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
	}

	public static function ensure_news_bar_content(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_news_bar_items';
		$col   = $wpdb->get_results( $wpdb->prepare(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'content'",
			DB_NAME,
			$table
		) );
		if ( empty( $col ) ) {
			$wpdb->query( "ALTER TABLE `{$table}` ADD COLUMN `content` LONGTEXT NULL AFTER `text`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
	}

	public static function ensure_news_bar_image(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_news_bar_items';
		$col   = $wpdb->get_results( $wpdb->prepare(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'image_id'",
			DB_NAME,
			$table
		) );
		if ( empty( $col ) ) {
			$wpdb->query( "ALTER TABLE `{$table}` ADD COLUMN `image_id` INT UNSIGNED DEFAULT NULL AFTER `content`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
	}

	public static function ensure_protected_taxonomy(): void {
		global $wpdb;
		$p = $wpdb->prefix;

		// Add is_protected column to ah_taxonomies if missing
		$col = $wpdb->get_results( $wpdb->prepare(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'is_protected'",
			DB_NAME,
			"{$p}ah_taxonomies"
		) );
		if ( empty( $col ) ) {
			$wpdb->query( "ALTER TABLE `{$p}ah_taxonomies` ADD COLUMN `is_protected` TINYINT(1) NOT NULL DEFAULT 0 AFTER `sort_order`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		// Create DataProtected taxonomy type if missing
		$type_id = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM `{$p}ah_taxonomy_types` WHERE slug = %s",
			'data-protected'
		) );
		if ( ! $type_id ) {
			$wpdb->insert( "{$p}ah_taxonomy_types", array(
				'name'        => 'DataProtected',
				'slug'        => 'data-protected',
				'description' => 'System-protected taxonomy type',
			) );
			$type_id = $wpdb->insert_id;
		}

		// Seed protected terms
		foreach ( array(
			array( 'name' => 'Unchangeable', 'slug' => 'unchangeable' ),
			array( 'name' => 'Undeletable',  'slug' => 'undeletable'  ),
		) as $term ) {
			$exists = $wpdb->get_var( $wpdb->prepare(
				"SELECT id FROM `{$p}ah_taxonomies` WHERE type_id = %d AND slug = %s",
				$type_id,
				$term['slug']
			) );
			if ( ! $exists ) {
				$wpdb->insert( "{$p}ah_taxonomies", array(
					'type_id'      => $type_id,
					'name'         => $term['name'],
					'slug'         => $term['slug'],
					'status'       => 'active',
					'is_protected' => 1,
				) );
			} else {
				$wpdb->update( "{$p}ah_taxonomies", array( 'is_protected' => 1 ), array( 'id' => (int) $exists ) );
			}
		}
	}

	/**
	 * Add image_id and icon_emoji columns to ah_taxonomies if not present.
	 */
	public static function ensure_taxonomy_media(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_taxonomies';

		$has_image = $wpdb->get_results( $wpdb->prepare(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'image_id'",
			DB_NAME, $table
		) );
		if ( empty( $has_image ) ) {
			$wpdb->query( "ALTER TABLE `{$table}` ADD COLUMN `image_id` INT UNSIGNED DEFAULT NULL AFTER `sort_order`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		$has_icon = $wpdb->get_results( $wpdb->prepare(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'icon_emoji'",
			DB_NAME, $table
		) );
		if ( empty( $has_icon ) ) {
			$wpdb->query( "ALTER TABLE `{$table}` ADD COLUMN `icon_emoji` VARCHAR(20) DEFAULT NULL AFTER `image_id`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
	}

	/**
	 * Drop FK constraints that reference ah_admin_users or ah_media via columns
	 * that are populated with WP user IDs / WP attachment IDs.
	 *
	 * Uses INFORMATION_SCHEMA to find only the FKs that still exist before
	 * running ALTER TABLE - avoids the "IF EXISTS" syntax that requires MySQL 8.0.29+.
	 */
	public static function drop_broken_fks(): void {
		global $wpdb;

		// Full list of broken FK constraint names to remove.
		$target_names = array(
			// FKs → ah_admin_users: no rows ever exist; WP user IDs stored instead.
			'fk_ss_user', 'fk_ps_user', 'fk_pg_cr', 'fk_pg_up', 'fk_nbi_user',
			'fk_hero_user', 'fk_wu_user', 'fk_gt_user', 'fk_diff_user',
			'fk_fp_user', 'fk_exp_user', 'fk_wr_user', 'fk_svc_user',
			'fk_sph_user', 'fk_aph_user', 'fk_ast_user', 'fk_tm_user',
			'fk_rv_user', 'fk_faq_user', 'fk_pt_author', 'fk_plph_user',
			'fk_csh_user', 'fk_cpc_user', 'fk_fc_user', 'fk_al_user',
			// FKs → ah_media: WP attachment IDs stored, not ah_media IDs.
			'fk_rv_img', 'fk_svc_img', 'fk_tm_photo', 'fk_ast_img',
			'fk_av_img', 'fk_hero_img', 'fk_gt_img', 'fk_wuc_img',
			'fk_fpi_img', 'fk_ec_img', 'fk_hl_icon', 'fk_si_icon',
			'fk_csi_img', 'fk_cuj_img', 'fk_cg_img', 'fk_cvl_thumb',
			'fk_fc_logo', 'fk_pg_img', 'fk_psi_icon', 'fk_fw_icon',
			'fk_au_avatar',
		);

		// Ask INFORMATION_SCHEMA which of these FKs actually exist right now.
		$placeholders = implode( ',', array_fill( 0, count( $target_names ), '%s' ) );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		$existing = $wpdb->get_results( $wpdb->prepare(
			"SELECT TABLE_NAME, CONSTRAINT_NAME
			 FROM information_schema.TABLE_CONSTRAINTS
			 WHERE TABLE_SCHEMA = DATABASE()
			   AND CONSTRAINT_TYPE = 'FOREIGN KEY'
			   AND CONSTRAINT_NAME IN ({$placeholders})",
			...$target_names
		) );

		if ( empty( $existing ) ) {
			return; // All already dropped - nothing to do.
		}

		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 0' );
		foreach ( $existing as $row ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( "ALTER TABLE `{$row->TABLE_NAME}` DROP FOREIGN KEY `{$row->CONSTRAINT_NAME}`" );
		}
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 1' );
	}

	/**
	 * Create ah_trigger_logs if it does not already exist (for existing installations).
	 * New installations get it via get_table_sqls() during install().
	 */
	public static function ensure_trigger_logs(): void {
		global $wpdb;
		$t  = $wpdb->prefix . 'ah_trigger_logs';
		$cs = $wpdb->get_charset_collate();
		$wpdb->query( "CREATE TABLE IF NOT EXISTS `{$t}` (
			`id`            BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
			`rule_id`       INT UNSIGNED        NOT NULL,
			`trigger_name`  VARCHAR(100)        NOT NULL,
			`context_data`  JSON                DEFAULT NULL,
			`action_index`  TINYINT UNSIGNED    NOT NULL DEFAULT 0,
			`action_type`   VARCHAR(50)         NOT NULL DEFAULT '',
			`action_config` JSON                DEFAULT NULL,
			`status`        ENUM('pending','sent','failed','unsent') NOT NULL DEFAULT 'pending',
			`is_done`       TINYINT(1)          NOT NULL DEFAULT 0,
			`is_unsent`     TINYINT(1)          NOT NULL DEFAULT 0,
			`attempts`      TINYINT UNSIGNED    NOT NULL DEFAULT 0,
			`error_message` TEXT                DEFAULT NULL,
			`sent_at`       DATETIME            DEFAULT NULL,
			`failed_at`     DATETIME            DEFAULT NULL,
			`created_at`    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			KEY `idx_rule`    (`rule_id`),
			KEY `idx_status`  (`status`),
			KEY `idx_trigger` (`trigger_name`),
			KEY `idx_done`    (`is_done`),
			KEY `idx_unsent`  (`is_unsent`)
		) ENGINE=InnoDB {$cs}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	public static function ensure_builder_table(): void {
		global $wpdb;
		$p  = $wpdb->prefix;
		$cs = $wpdb->get_charset_collate();
		$wpdb->query( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			"CREATE TABLE IF NOT EXISTS {$p}ah_builder_pages (
				id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				title            VARCHAR(255) NOT NULL,
				slug             VARCHAR(280) NOT NULL UNIQUE,
				blocks           LONGTEXT DEFAULT NULL,
				status           ENUM('active','draft') DEFAULT 'draft',
				meta_title       VARCHAR(255),
				meta_description TEXT,
				created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				KEY idx_slug (slug), KEY idx_status (status)
			) ENGINE=InnoDB {$cs}"
		);
	}

	public static function ensure_required_settings(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_site_settings';
		$required = array(
			array( 'setting_key' => 'phone',            'setting_val' => '', 'field_type' => 'phone',    'group_name' => 'contact',       'label' => 'Phone'                     ),
			array( 'setting_key' => 'whatsapp',         'setting_val' => '', 'field_type' => 'phone',    'group_name' => 'contact',       'label' => 'WhatsApp'                  ),
			array( 'setting_key' => 'email',            'setting_val' => '', 'field_type' => 'email',    'group_name' => 'contact',       'label' => 'Email'                     ),
			array( 'setting_key' => 'address',          'setting_val' => '', 'field_type' => 'textarea', 'group_name' => 'contact',       'label' => 'Address'                   ),
			array( 'setting_key' => 'consultation_url', 'setting_val' => '', 'field_type' => 'url',      'group_name' => 'contact',       'label' => 'Consultation URL'          ),
			array( 'setting_key' => 'youtube_url',      'setting_val' => '', 'field_type' => 'url',      'group_name' => 'social',        'label' => 'YouTube URL'               ),
			// Notification / outbound email identity
			array( 'setting_key' => 'notif_from_name',  'setting_val' => '', 'field_type' => 'text',     'group_name' => 'notifications', 'label' => 'Notification From Name'    ),
			array( 'setting_key' => 'notif_from_email', 'setting_val' => '', 'field_type' => 'email',    'group_name' => 'notifications', 'label' => 'Notification From Email'   ),
			// SMTP overrides (leave blank to use WordPress default mailer)
			array( 'setting_key' => 'smtp_host',        'setting_val' => '', 'field_type' => 'text',     'group_name' => 'notifications', 'label' => 'SMTP Host'                 ),
			array( 'setting_key' => 'smtp_port',        'setting_val' => '', 'field_type' => 'text',     'group_name' => 'notifications', 'label' => 'SMTP Port'                 ),
			array( 'setting_key' => 'smtp_user',        'setting_val' => '', 'field_type' => 'text',     'group_name' => 'notifications', 'label' => 'SMTP Username'             ),
			array( 'setting_key' => 'smtp_pass',        'setting_val' => '', 'field_type' => 'text',     'group_name' => 'notifications', 'label' => 'SMTP Password'             ),
			array( 'setting_key' => 'smtp_secure',      'setting_val' => '', 'field_type' => 'text',     'group_name' => 'notifications', 'label' => 'SMTP Encryption (tls/ssl)' ),
		);
		foreach ( $required as $s ) {
			if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$table}` WHERE setting_key = %s", $s['setting_key'] ) ) ) {
				$wpdb->insert( $table, $s );
			}
		}
	}

	// ----------------------------------------------------------------
	// Table definitions
	// ----------------------------------------------------------------

	private static function get_table_sqls(): array {
		global $wpdb;
		$p  = $wpdb->prefix;
		$cs = $wpdb->get_charset_collate();

		return array(

			// 1. Taxonomy Types
			"CREATE TABLE IF NOT EXISTS {$p}ah_taxonomy_types (
				id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				name        VARCHAR(100) NOT NULL,
				slug        VARCHAR(120) NOT NULL UNIQUE,
				description TEXT,
				created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
			) ENGINE=InnoDB {$cs}",

			// 2. Taxonomies
			"CREATE TABLE IF NOT EXISTS {$p}ah_taxonomies (
				id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				type_id          INT UNSIGNED NOT NULL,
				parent_id        INT UNSIGNED DEFAULT NULL,
				name             VARCHAR(200) NOT NULL,
				slug             VARCHAR(220) NOT NULL,
				description      TEXT,
				meta_title       VARCHAR(255),
				meta_description TEXT,
				status           ENUM('active','inactive') DEFAULT 'active',
				sort_order       INT DEFAULT 0,
				created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				UNIQUE KEY uq_taxonomy_slug (type_id, slug),
				KEY idx_type (type_id),
				KEY idx_parent (parent_id)
			) ENGINE=InnoDB {$cs}",

			// 3. Media Library
			"CREATE TABLE IF NOT EXISTS {$p}ah_media (
				id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				file_name   VARCHAR(255) NOT NULL,
				file_path   VARCHAR(500) NOT NULL,
				file_url    VARCHAR(500) NOT NULL,
				mime_type   VARCHAR(100),
				file_size   INT UNSIGNED,
				width       SMALLINT UNSIGNED,
				height      SMALLINT UNSIGNED,
				alt_text    VARCHAR(255),
				caption     TEXT,
				uploaded_by INT UNSIGNED,
				created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				KEY idx_mime (mime_type)
			) ENGINE=InnoDB {$cs}",

			// 4. Admin Roles
			"CREATE TABLE IF NOT EXISTS {$p}ah_admin_roles (
				id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				name        VARCHAR(80) NOT NULL UNIQUE,
				permissions JSON,
				created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
			) ENGINE=InnoDB {$cs}",

			// 5. Admin Users
			"CREATE TABLE IF NOT EXISTS {$p}ah_admin_users (
				id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				role_id         INT UNSIGNED NOT NULL,
				full_name       VARCHAR(150) NOT NULL,
				email           VARCHAR(200) NOT NULL UNIQUE,
				password_hash   VARCHAR(255) NOT NULL,
				avatar_id       INT UNSIGNED,
				status          ENUM('active','inactive','suspended') DEFAULT 'active',
				last_login_at   TIMESTAMP NULL,
				created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				KEY idx_role (role_id),
				KEY idx_status (status)
			) ENGINE=InnoDB {$cs}",

			// 6. Site Settings
			"CREATE TABLE IF NOT EXISTS {$p}ah_site_settings (
				id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				setting_key VARCHAR(150) NOT NULL UNIQUE,
				setting_val TEXT,
				field_type  ENUM('text','textarea','image','color','url','email','phone','toggle','json') DEFAULT 'text',
				group_name  VARCHAR(100),
				label       VARCHAR(200),
				is_visible  TINYINT(1) DEFAULT 1,
				updated_by  INT UNSIGNED,
				updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				KEY idx_group (group_name)
			) ENGINE=InnoDB {$cs}",

			// 9. Pages
			"CREATE TABLE IF NOT EXISTS {$p}ah_pages (
				id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				title            VARCHAR(255) NOT NULL,
				slug             VARCHAR(255) NOT NULL UNIQUE,
				page_type        ENUM('home','about','services','contact','client_stories','blog_listing','news_listing','custom') DEFAULT 'custom',
				meta_title       VARCHAR(255),
				meta_description TEXT,
				meta_keywords    TEXT,
				og_image_id      INT UNSIGNED,
				status           ENUM('active','inactive','draft') DEFAULT 'active',
				created_by       INT UNSIGNED,
				updated_by       INT UNSIGNED,
				created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				KEY idx_slug (slug),
				KEY idx_status (status)
			) ENGINE=InnoDB {$cs}",

			// 10. Page Sections
			"CREATE TABLE IF NOT EXISTS {$p}ah_page_sections (
				id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				page_id       INT UNSIGNED NOT NULL,
				section_key   VARCHAR(150) NOT NULL,
				section_label VARCHAR(200),
				is_visible    TINYINT(1) DEFAULT 1,
				sort_order    INT DEFAULT 0,
				updated_by    INT UNSIGNED,
				updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				UNIQUE KEY uq_page_section (page_id, section_key)
			) ENGINE=InnoDB {$cs}",

			// 11. News Bar Items
			"CREATE TABLE IF NOT EXISTS {$p}ah_news_bar_items (
				id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				text        VARCHAR(500) NOT NULL,
				content     LONGTEXT NULL,
				link_url    VARCHAR(500),
				link_target ENUM('_self','_blank') DEFAULT '_self',
				status      ENUM('active','inactive') DEFAULT 'active',
				sort_order  INT DEFAULT 0,
				start_date  DATE,
				end_date    DATE,
				created_by  INT UNSIGNED,
				created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				KEY idx_status (status)
			) ENGINE=InnoDB {$cs}",

			// 12. Hero Section
			"CREATE TABLE IF NOT EXISTS {$p}ah_section_hero (
				id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				page_id             INT UNSIGNED NOT NULL,
				badge_text          VARCHAR(150),
				heading             VARCHAR(300) NOT NULL,
				subheading          TEXT,
				cta_primary_text    VARCHAR(100),
				cta_primary_url     VARCHAR(500),
				cta_secondary_text  VARCHAR(100),
				cta_secondary_url   VARCHAR(500),
				image_id            INT UNSIGNED,
				bg_color            VARCHAR(20),
				is_visible          TINYINT(1) DEFAULT 1,
				updated_by          INT UNSIGNED,
				updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				KEY idx_page (page_id)
			) ENGINE=InnoDB {$cs}",

			// 13. Highlights
			"CREATE TABLE IF NOT EXISTS {$p}ah_section_highlights (
				id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				page_id     INT UNSIGNED NOT NULL,
				icon_id     INT UNSIGNED,
				icon_class  VARCHAR(100),
				text        VARCHAR(300) NOT NULL,
				sort_order  INT DEFAULT 0,
				status      ENUM('active','inactive') DEFAULT 'active',
				KEY idx_page (page_id)
			) ENGINE=InnoDB {$cs}",

			// 14. Why Us
			"CREATE TABLE IF NOT EXISTS {$p}ah_section_why_us (
				id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				page_id        INT UNSIGNED NOT NULL,
				heading        VARCHAR(255),
				description    TEXT,
				more_link_text VARCHAR(100),
				more_link_url  VARCHAR(500),
				is_visible     TINYINT(1) DEFAULT 1,
				updated_by     INT UNSIGNED,
				updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				KEY idx_page (page_id)
			) ENGINE=InnoDB {$cs}",

			// 15. Why Us Cards
			"CREATE TABLE IF NOT EXISTS {$p}ah_section_why_us_cards (
				id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				why_us_id   INT UNSIGNED NOT NULL,
				image_id    INT UNSIGNED,
				title       VARCHAR(255) NOT NULL,
				description TEXT,
				link_url    VARCHAR(500),
				sort_order  INT DEFAULT 0,
				status      ENUM('active','inactive') DEFAULT 'active',
				KEY idx_why_us (why_us_id)
			) ENGINE=InnoDB {$cs}",

			// 16. Guide Through
			"CREATE TABLE IF NOT EXISTS {$p}ah_section_guide_through (
				id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				page_id        INT UNSIGNED NOT NULL,
				heading        VARCHAR(255),
				image_id       INT UNSIGNED,
				description    TEXT,
				more_link_text VARCHAR(100),
				more_link_url  VARCHAR(500),
				is_visible     TINYINT(1) DEFAULT 1,
				updated_by     INT UNSIGNED,
				updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				KEY idx_page (page_id)
			) ENGINE=InnoDB {$cs}",

			// 17. Guide Through Points
			"CREATE TABLE IF NOT EXISTS {$p}ah_section_guide_through_points (
				id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				guide_id   INT UNSIGNED NOT NULL,
				point_text VARCHAR(500) NOT NULL,
				sort_order INT DEFAULT 0,
				status     ENUM('active','inactive') DEFAULT 'active',
				KEY idx_guide (guide_id)
			) ENGINE=InnoDB {$cs}",

			// 18. Stack Items
			"CREATE TABLE IF NOT EXISTS {$p}ah_section_stack_items (
				id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				page_id    INT UNSIGNED NOT NULL,
				heading    VARCHAR(255),
				name       VARCHAR(150) NOT NULL,
				icon_id    INT UNSIGNED,
				icon_url   VARCHAR(500),
				sort_order INT DEFAULT 0,
				status     ENUM('active','inactive') DEFAULT 'active',
				KEY idx_page (page_id)
			) ENGINE=InnoDB {$cs}",

			// 19. Difference Section
			"CREATE TABLE IF NOT EXISTS {$p}ah_section_difference (
				id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				page_id        INT UNSIGNED NOT NULL,
				heading        VARCHAR(255),
				information    TEXT,
				more_link_text VARCHAR(100),
				more_link_url  VARCHAR(500),
				is_visible     TINYINT(1) DEFAULT 1,
				updated_by     INT UNSIGNED,
				updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				KEY idx_page (page_id)
			) ENGINE=InnoDB {$cs}",

			// 20. Difference Table Rows
			"CREATE TABLE IF NOT EXISTS {$p}ah_section_difference_table (
				id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				difference_id INT UNSIGNED NOT NULL,
				feature_label VARCHAR(255) NOT NULL,
				us_value      VARCHAR(500),
				others_value  VARCHAR(500),
				sort_order    INT DEFAULT 0,
				status        ENUM('active','inactive') DEFAULT 'active',
				KEY idx_diff (difference_id)
			) ENGINE=InnoDB {$cs}",

			// 21. Featured Properties
			"CREATE TABLE IF NOT EXISTS {$p}ah_section_featured_properties (
				id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				page_id    INT UNSIGNED NOT NULL,
				heading    VARCHAR(255),
				description TEXT,
				is_visible TINYINT(1) DEFAULT 1,
				updated_by INT UNSIGNED,
				updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				KEY idx_page (page_id)
			) ENGINE=InnoDB {$cs}",

			// 22. Featured Properties Items
			"CREATE TABLE IF NOT EXISTS {$p}ah_section_featured_properties_items (
				id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				section_id  INT UNSIGNED NOT NULL,
				image_id    INT UNSIGNED,
				title       VARCHAR(255),
				description TEXT,
				sort_order  INT DEFAULT 0,
				status      ENUM('active','inactive') DEFAULT 'active',
				KEY idx_section (section_id)
			) ENGINE=InnoDB {$cs}",

			// 23. Experience Section
			"CREATE TABLE IF NOT EXISTS {$p}ah_section_experience (
				id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				page_id        INT UNSIGNED NOT NULL,
				heading        VARCHAR(255),
				description    TEXT,
				more_link_text VARCHAR(100),
				more_link_url  VARCHAR(500),
				is_visible     TINYINT(1) DEFAULT 1,
				updated_by     INT UNSIGNED,
				updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				KEY idx_page (page_id)
			) ENGINE=InnoDB {$cs}",

			// 24. Experience Cards
			"CREATE TABLE IF NOT EXISTS {$p}ah_section_experience_cards (
				id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				section_id  INT UNSIGNED NOT NULL,
				image_id    INT UNSIGNED,
				title       VARCHAR(255) NOT NULL,
				description TEXT,
				link_url    VARCHAR(500),
				sort_order  INT DEFAULT 0,
				status      ENUM('active','inactive') DEFAULT 'active',
				KEY idx_section (section_id)
			) ENGINE=InnoDB {$cs}",

			// 25. Why Required
			"CREATE TABLE IF NOT EXISTS {$p}ah_section_why_required (
				id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				page_id        INT UNSIGNED NOT NULL,
				heading        VARCHAR(255),
				information    TEXT,
				more_link_text VARCHAR(100),
				more_link_url  VARCHAR(500),
				is_visible     TINYINT(1) DEFAULT 1,
				updated_by     INT UNSIGNED,
				updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				KEY idx_page (page_id)
			) ENGINE=InnoDB {$cs}",

			// 26. Why Required Cards
			"CREATE TABLE IF NOT EXISTS {$p}ah_section_why_required_cards (
				id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				section_id  INT UNSIGNED NOT NULL,
				youtube_url VARCHAR(500),
				title       VARCHAR(255),
				description TEXT,
				sort_order  INT DEFAULT 0,
				status      ENUM('active','inactive') DEFAULT 'active',
				KEY idx_section (section_id)
			) ENGINE=InnoDB {$cs}",

			// 27. Reviews
			"CREATE TABLE IF NOT EXISTS {$p}ah_reviews (
				id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				reviewer_name     VARCHAR(200) NOT NULL,
				reviewer_title    VARCHAR(200),
				reviewer_image_id INT UNSIGNED,
				review_text       TEXT NOT NULL,
				rating            TINYINT UNSIGNED DEFAULT 5,
				source            ENUM('manual','google','facebook','other') DEFAULT 'manual',
				is_featured       TINYINT(1) DEFAULT 0,
				status            ENUM('active','inactive') DEFAULT 'active',
				sort_order        INT DEFAULT 0,
				created_by        INT UNSIGNED,
				created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				KEY idx_status (status),
				KEY idx_featured (is_featured)
			) ENGINE=InnoDB {$cs}",

			// 28. Reviews Section Header
			"CREATE TABLE IF NOT EXISTS {$p}ah_section_reviews_header (
				id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				page_id        INT UNSIGNED NOT NULL,
				heading        VARCHAR(255),
				description    TEXT,
				more_link_text VARCHAR(100),
				more_link_url  VARCHAR(500),
				is_visible     TINYINT(1) DEFAULT 1,
				KEY idx_page (page_id)
			) ENGINE=InnoDB {$cs}",

			// 29. FAQs
			"CREATE TABLE IF NOT EXISTS {$p}ah_faqs (
				id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				question   TEXT NOT NULL,
				answer     TEXT NOT NULL,
				link_text  VARCHAR(150),
				link_url   VARCHAR(500),
				page_id    INT UNSIGNED,
				sort_order INT DEFAULT 0,
				status     ENUM('active','inactive') DEFAULT 'active',
				created_by INT UNSIGNED,
				created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				KEY idx_page (page_id),
				KEY idx_status (status)
			) ENGINE=InnoDB {$cs}",

			// 30. FAQ Section Header
			"CREATE TABLE IF NOT EXISTS {$p}ah_section_faq_header (
				id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				page_id     INT UNSIGNED NOT NULL UNIQUE,
				heading     VARCHAR(255),
				description TEXT,
				is_visible  TINYINT(1) DEFAULT 1
			) ENGINE=InnoDB {$cs}",

			// 31. Services
			"CREATE TABLE IF NOT EXISTS {$p}ah_services (
				id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				title            VARCHAR(255) NOT NULL,
				slug             VARCHAR(280) NOT NULL UNIQUE,
				image_id         INT UNSIGNED,
				short_desc       TEXT,
				full_desc        LONGTEXT,
				meta_title       VARCHAR(255),
				meta_description TEXT,
				sort_order       INT DEFAULT 0,
				status           ENUM('active','inactive') DEFAULT 'active',
				created_by       INT UNSIGNED,
				created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				KEY idx_slug (slug),
				KEY idx_status (status)
			) ENGINE=InnoDB {$cs}",

			// 32. Service Bullet Points
			"CREATE TABLE IF NOT EXISTS {$p}ah_service_bullet_points (
				id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				service_id INT UNSIGNED NOT NULL,
				point_text VARCHAR(500) NOT NULL,
				sort_order INT DEFAULT 0,
				KEY idx_service (service_id)
			) ENGINE=InnoDB {$cs}",

			// 33. Services Page Header
			"CREATE TABLE IF NOT EXISTS {$p}ah_services_page_header (
				id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				page_id     INT UNSIGNED NOT NULL UNIQUE,
				heading     VARCHAR(255),
				information TEXT,
				is_visible  TINYINT(1) DEFAULT 1,
				updated_by  INT UNSIGNED,
				updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
			) ENGINE=InnoDB {$cs}",

			// 34. Service Taxonomies pivot
			"CREATE TABLE IF NOT EXISTS {$p}ah_service_taxonomies (
				service_id  INT UNSIGNED NOT NULL,
				taxonomy_id INT UNSIGNED NOT NULL,
				PRIMARY KEY (service_id, taxonomy_id)
			) ENGINE=InnoDB {$cs}",

			// 35. About Page Header
			"CREATE TABLE IF NOT EXISTS {$p}ah_about_page_header (
				id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				page_id     INT UNSIGNED NOT NULL UNIQUE,
				heading     VARCHAR(255),
				information TEXT,
				is_visible  TINYINT(1) DEFAULT 1,
				updated_by  INT UNSIGNED,
				updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
			) ENGINE=InnoDB {$cs}",

			// 36. About Story
			"CREATE TABLE IF NOT EXISTS {$p}ah_about_story (
				id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				page_id     INT UNSIGNED NOT NULL UNIQUE,
				image_id    INT UNSIGNED,
				heading     VARCHAR(255),
				subheading  VARCHAR(255),
				is_visible  TINYINT(1) DEFAULT 1,
				updated_by  INT UNSIGNED,
				updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
			) ENGINE=InnoDB {$cs}",

			// 37. About Story Points
			"CREATE TABLE IF NOT EXISTS {$p}ah_about_story_points (
				id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				story_id   INT UNSIGNED NOT NULL,
				point_text VARCHAR(500) NOT NULL,
				sort_order INT DEFAULT 0,
				KEY idx_story (story_id)
			) ENGINE=InnoDB {$cs}",

			// 38. Team Members
			"CREATE TABLE IF NOT EXISTS {$p}ah_team_members (
				id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				photo_id     INT UNSIGNED,
				name         VARCHAR(200) NOT NULL,
				designation  VARCHAR(200),
				bio          TEXT,
				email        VARCHAR(200),
				linkedin_url VARCHAR(500),
				sort_order   INT DEFAULT 0,
				is_featured  TINYINT(1) DEFAULT 0,
				status       ENUM('active','inactive') DEFAULT 'active',
				created_by   INT UNSIGNED,
				created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				KEY idx_status (status)
			) ENGINE=InnoDB {$cs}",

			// 39. About Values
			"CREATE TABLE IF NOT EXISTS {$p}ah_about_values (
				id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				page_id     INT UNSIGNED NOT NULL,
				image_id    INT UNSIGNED,
				heading     VARCHAR(255),
				information TEXT,
				sort_order  INT DEFAULT 0,
				status      ENUM('active','inactive') DEFAULT 'active',
				KEY idx_page (page_id)
			) ENGINE=InnoDB {$cs}",

			// 40. Posts (blog/news/article/newsletter/guide)
			"CREATE TABLE IF NOT EXISTS {$p}ah_posts (
				id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				post_type         ENUM('blog','article','news','newsletter','guide') NOT NULL DEFAULT 'blog',
				title             VARCHAR(400) NOT NULL,
				slug              VARCHAR(420) NOT NULL,
				excerpt           TEXT,
				content           LONGTEXT,
				featured_image_id INT UNSIGNED,
				banner_image_id   INT UNSIGNED,
				author_id         INT UNSIGNED,
				status            ENUM('active','inactive','draft','scheduled') DEFAULT 'draft',
				is_featured       TINYINT(1) DEFAULT 0,
				published_at      TIMESTAMP NULL,
				scheduled_at      TIMESTAMP NULL,
				meta_title        VARCHAR(255),
				meta_description  TEXT,
				meta_keywords     TEXT,
				view_count        INT UNSIGNED DEFAULT 0,
				created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				UNIQUE KEY uq_post_slug (post_type, slug),
				KEY idx_type (post_type),
				KEY idx_status (status),
				KEY idx_featured (is_featured)
			) ENGINE=InnoDB {$cs}",

			// 41. Post Taxonomies pivot
			"CREATE TABLE IF NOT EXISTS {$p}ah_post_taxonomies (
				post_id     INT UNSIGNED NOT NULL,
				taxonomy_id INT UNSIGNED NOT NULL,
				PRIMARY KEY (post_id, taxonomy_id)
			) ENGINE=InnoDB {$cs}",

			// 41b. Universal content taxonomies pivot
			"CREATE TABLE IF NOT EXISTS {$p}ah_content_taxonomies (
				id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				object_type VARCHAR(50) NOT NULL,
				object_id   BIGINT UNSIGNED NOT NULL,
				taxonomy_id INT UNSIGNED NOT NULL,
				created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				UNIQUE KEY uq_object_taxonomy (object_type, object_id, taxonomy_id),
				KEY idx_object (object_type, object_id),
				KEY idx_taxonomy (taxonomy_id)
			) ENGINE=InnoDB {$cs}",

			// 42. Post Table Blocks
			"CREATE TABLE IF NOT EXISTS {$p}ah_post_table_blocks (
				id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				post_id    INT UNSIGNED NOT NULL,
				heading    VARCHAR(255),
				table_data JSON NOT NULL,
				sort_order INT DEFAULT 0,
				KEY idx_post (post_id)
			) ENGINE=InnoDB {$cs}",

			// 43. Post Links
			"CREATE TABLE IF NOT EXISTS {$p}ah_post_links (
				id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				post_id   INT UNSIGNED NOT NULL,
				label     VARCHAR(255),
				url       VARCHAR(500) NOT NULL,
				link_type ENUM('official','reference','related','cta') DEFAULT 'reference',
				sort_order INT DEFAULT 0,
				KEY idx_post (post_id)
			) ENGINE=InnoDB {$cs}",

			// 44. Post Stack Items
			"CREATE TABLE IF NOT EXISTS {$p}ah_post_stack_items (
				id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				post_id    INT UNSIGNED NOT NULL,
				name       VARCHAR(150),
				icon_id    INT UNSIGNED,
				link_url   VARCHAR(500),
				sort_order INT DEFAULT 0,
				KEY idx_post (post_id)
			) ENGINE=InnoDB {$cs}",

			// 45. Post Listing Page Header
			"CREATE TABLE IF NOT EXISTS {$p}ah_post_listing_page_header (
				id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				page_id      INT UNSIGNED NOT NULL UNIQUE,
				main_heading VARCHAR(255),
				description  TEXT,
				is_visible   TINYINT(1) DEFAULT 1,
				updated_by   INT UNSIGNED,
				updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
			) ENGINE=InnoDB {$cs}",

			// 46. Client Stories Header
			"CREATE TABLE IF NOT EXISTS {$p}ah_client_stories_header (
				id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				page_id     INT UNSIGNED NOT NULL UNIQUE,
				heading     VARCHAR(255),
				information TEXT,
				is_visible  TINYINT(1) DEFAULT 1,
				updated_by  INT UNSIGNED,
				updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
			) ENGINE=InnoDB {$cs}",

			// 47. Client Story Images
			"CREATE TABLE IF NOT EXISTS {$p}ah_client_story_images (
				id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				page_id     INT UNSIGNED NOT NULL,
				image_id    INT UNSIGNED NOT NULL,
				review_text TEXT,
				sort_order  INT DEFAULT 0,
				status      ENUM('active','inactive') DEFAULT 'active',
				KEY idx_page (page_id)
			) ENGINE=InnoDB {$cs}",

			// 48. Client Users Journey
			"CREATE TABLE IF NOT EXISTS {$p}ah_client_users_journey (
				id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				page_id    INT UNSIGNED NOT NULL,
				heading    VARCHAR(255),
				basic_info TEXT,
				image_id   INT UNSIGNED,
				user_name  VARCHAR(200),
				user_info  TEXT,
				sort_order INT DEFAULT 0,
				status     ENUM('active','inactive') DEFAULT 'active',
				KEY idx_page (page_id)
			) ENGINE=InnoDB {$cs}",

			// 49. Client Gallery
			"CREATE TABLE IF NOT EXISTS {$p}ah_client_gallery (
				id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				page_id     INT UNSIGNED NOT NULL,
				image_id    INT UNSIGNED NOT NULL,
				width_class ENUM('small','medium','large','full') DEFAULT 'medium',
				sort_order  INT DEFAULT 0,
				status      ENUM('active','inactive') DEFAULT 'active',
				KEY idx_page (page_id)
			) ENGINE=InnoDB {$cs}",

			// 50. Client Video Links
			"CREATE TABLE IF NOT EXISTS {$p}ah_client_video_links (
				id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				page_id      INT UNSIGNED NOT NULL,
				heading      VARCHAR(255),
				video_url    VARCHAR(500) NOT NULL,
				thumbnail_id INT UNSIGNED,
				sort_order   INT DEFAULT 0,
				status       ENUM('active','inactive') DEFAULT 'active',
				KEY idx_page (page_id)
			) ENGINE=InnoDB {$cs}",

			// 51. Contact Page Config
			"CREATE TABLE IF NOT EXISTS {$p}ah_contact_page_config (
				id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				page_id          INT UNSIGNED NOT NULL UNIQUE,
				heading          VARCHAR(255),
				basic_info       TEXT,
				email            VARCHAR(200),
				whatsapp_number  VARCHAR(30),
				phone_number     VARCHAR(30),
				maps_embed_url   TEXT,
				is_visible       TINYINT(1) DEFAULT 1,
				updated_by       INT UNSIGNED,
				updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
			) ENGINE=InnoDB {$cs}",

			// 52. Contact Form Submissions
			"CREATE TABLE IF NOT EXISTS {$p}ah_contact_form_submissions (
				id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				full_name    VARCHAR(200) NOT NULL,
				email        VARCHAR(200) NOT NULL,
				phone        VARCHAR(30),
				subject      VARCHAR(300),
				message      TEXT NOT NULL,
				page_url     VARCHAR(500),
				user_agent   VARCHAR(500),
				ip_address   VARCHAR(45),
				admin_notes  TEXT,
				is_read      TINYINT(1) DEFAULT 0,
				status       ENUM('new','in_progress','resolved','spam') DEFAULT 'new',
				submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				KEY idx_status (status),
				KEY idx_is_read (is_read)
			) ENGINE=InnoDB {$cs}",

			// 62. Trigger Execution Logs
			"CREATE TABLE IF NOT EXISTS {$p}ah_trigger_logs (
				id            BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
				rule_id       INT UNSIGNED        NOT NULL,
				trigger_name  VARCHAR(100)        NOT NULL,
				context_data  JSON                DEFAULT NULL,
				action_index  TINYINT UNSIGNED    NOT NULL DEFAULT 0,
				action_type   VARCHAR(50)         NOT NULL DEFAULT '',
				action_config JSON                DEFAULT NULL,
				status        ENUM('pending','sent','failed','unsent') NOT NULL DEFAULT 'pending',
				is_done       TINYINT(1)          NOT NULL DEFAULT 0,
				is_unsent     TINYINT(1)          NOT NULL DEFAULT 0,
				attempts      TINYINT UNSIGNED    NOT NULL DEFAULT 0,
				error_message TEXT                DEFAULT NULL,
				sent_at       DATETIME            DEFAULT NULL,
				failed_at     DATETIME            DEFAULT NULL,
				created_at    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY idx_rule    (rule_id),
				KEY idx_status  (status),
				KEY idx_trigger (trigger_name),
				KEY idx_done    (is_done),
				KEY idx_unsent  (is_unsent)
			) ENGINE=InnoDB {$cs}",

			// 53. Footer Config
			"CREATE TABLE IF NOT EXISTS {$p}ah_footer_config (
				id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				logo_id              INT UNSIGNED,
				site_name            VARCHAR(150),
				tagline              TEXT,
				copyright_text       VARCHAR(300),
				get_in_touch_heading VARCHAR(150),
				is_visible           TINYINT(1) DEFAULT 1,
				updated_by           INT UNSIGNED,
				updated_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
			) ENGINE=InnoDB {$cs}",

			// 54. Footer Contact Links
			"CREATE TABLE IF NOT EXISTS {$p}ah_footer_contact_links (
				id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				label      VARCHAR(150),
				value      VARCHAR(300) NOT NULL,
				link_url   VARCHAR(500),
				icon_class VARCHAR(100),
				sort_order INT DEFAULT 0,
				status     ENUM('active','inactive') DEFAULT 'active'
			) ENGINE=InnoDB {$cs}",

			// 55. Footer Social Links
			"CREATE TABLE IF NOT EXISTS {$p}ah_footer_social_links (
				id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				platform   VARCHAR(80) NOT NULL,
				url        VARCHAR(500) NOT NULL,
				icon_class VARCHAR(100),
				sort_order INT DEFAULT 0,
				status     ENUM('active','inactive') DEFAULT 'active'
			) ENGINE=InnoDB {$cs}",

			// 56. Random Blog Card Configs
			"CREATE TABLE IF NOT EXISTS {$p}ah_random_blog_card_configs (
				id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				page_id          INT UNSIGNED NOT NULL,
				heading          VARCHAR(255),
				more_link_text   VARCHAR(100),
				more_link_url    VARCHAR(500),
				post_type_filter SET('blog','article','news','newsletter','guide') DEFAULT 'blog',
				max_cards        TINYINT DEFAULT 3,
				is_visible       TINYINT(1) DEFAULT 1,
				sort_position    INT DEFAULT 0,
				KEY idx_page (page_id)
			) ENGINE=InnoDB {$cs}",

			// 57. News Detail Big Cards
			"CREATE TABLE IF NOT EXISTS {$p}ah_news_detail_big_cards (
				id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				post_id     INT UNSIGNED NOT NULL,
				heading     VARCHAR(255),
				information TEXT,
				sort_order  INT DEFAULT 0,
				status      ENUM('active','inactive') DEFAULT 'active',
				KEY idx_post (post_id)
			) ENGINE=InnoDB {$cs}",

			// 58. News Detail Card Links
			"CREATE TABLE IF NOT EXISTS {$p}ah_news_detail_card_links (
				id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				card_id    INT UNSIGNED NOT NULL,
				label      VARCHAR(255),
				url        VARCHAR(500) NOT NULL,
				sort_order INT DEFAULT 0,
				KEY idx_card (card_id)
			) ENGINE=InnoDB {$cs}",

			// 59. Related Posts
			"CREATE TABLE IF NOT EXISTS {$p}ah_related_posts (
				post_id         INT UNSIGNED NOT NULL,
				related_post_id INT UNSIGNED NOT NULL,
				PRIMARY KEY (post_id, related_post_id)
			) ENGINE=InnoDB {$cs}",

			// 60. Floating Widgets
			"CREATE TABLE IF NOT EXISTS {$p}ah_floating_widgets (
				id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				widget_type   ENUM('whatsapp','contact_us','chat','custom') NOT NULL,
				label         VARCHAR(150),
				link_url      VARCHAR(500),
				icon_id       INT UNSIGNED,
				bg_color      VARCHAR(20),
				position      ENUM('bottom_right','bottom_left','top_right','top_left') DEFAULT 'bottom_right',
				is_visible    TINYINT(1) DEFAULT 1,
				exclude_pages JSON
			) ENGINE=InnoDB {$cs}",

			// 61. Audit Logs
			"CREATE TABLE IF NOT EXISTS {$p}ah_audit_logs (
				id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				user_id    INT UNSIGNED,
				action     VARCHAR(100) NOT NULL,
				table_name VARCHAR(100),
				record_id  INT UNSIGNED,
				old_values JSON,
				new_values JSON,
				ip_address VARCHAR(45),
				user_agent VARCHAR(500),
				created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				KEY idx_user (user_id),
				KEY idx_action (action),
				KEY idx_table (table_name)
			) ENGINE=InnoDB {$cs}",
		);
	}

	// ----------------------------------------------------------------
	// Foreign key constraints (added after all tables exist)
	// ----------------------------------------------------------------

	private static function add_foreign_keys(): void {
		global $wpdb;
		$p = $wpdb->prefix;

		$fks = array(
			// taxonomies
			"ALTER TABLE {$p}ah_taxonomies
				ADD CONSTRAINT fk_tax_type   FOREIGN KEY (type_id)   REFERENCES {$p}ah_taxonomy_types(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_tax_parent FOREIGN KEY (parent_id) REFERENCES {$p}ah_taxonomies(id)     ON DELETE SET NULL",

			// admin_users
			"ALTER TABLE {$p}ah_admin_users
				ADD CONSTRAINT fk_au_role   FOREIGN KEY (role_id)   REFERENCES {$p}ah_admin_roles(id),
				ADD CONSTRAINT fk_au_avatar FOREIGN KEY (avatar_id) REFERENCES {$p}ah_media(id) ON DELETE SET NULL",

			// site_settings
			"ALTER TABLE {$p}ah_site_settings
				ADD CONSTRAINT fk_ss_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",
			// pages
			"ALTER TABLE {$p}ah_pages
				ADD CONSTRAINT fk_pg_img  FOREIGN KEY (og_image_id) REFERENCES {$p}ah_media(id)       ON DELETE SET NULL,
				ADD CONSTRAINT fk_pg_cr   FOREIGN KEY (created_by)  REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL,
				ADD CONSTRAINT fk_pg_up   FOREIGN KEY (updated_by)  REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			// page_sections
			"ALTER TABLE {$p}ah_page_sections
				ADD CONSTRAINT fk_ps_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_ps_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			// news_bar_items
			"ALTER TABLE {$p}ah_news_bar_items
				ADD CONSTRAINT fk_nbi_user FOREIGN KEY (created_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			// section_hero
			"ALTER TABLE {$p}ah_section_hero
				ADD CONSTRAINT fk_hero_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_hero_img  FOREIGN KEY (image_id)   REFERENCES {$p}ah_media(id)       ON DELETE SET NULL,
				ADD CONSTRAINT fk_hero_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			// section_highlights
			"ALTER TABLE {$p}ah_section_highlights
				ADD CONSTRAINT fk_hl_page FOREIGN KEY (page_id) REFERENCES {$p}ah_pages(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_hl_icon FOREIGN KEY (icon_id) REFERENCES {$p}ah_media(id) ON DELETE SET NULL",

			// section_why_us + cards
			"ALTER TABLE {$p}ah_section_why_us
				ADD CONSTRAINT fk_wu_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_wu_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",
			"ALTER TABLE {$p}ah_section_why_us_cards
				ADD CONSTRAINT fk_wuc_wu  FOREIGN KEY (why_us_id) REFERENCES {$p}ah_section_why_us(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_wuc_img FOREIGN KEY (image_id)  REFERENCES {$p}ah_media(id)          ON DELETE SET NULL",

			// guide through + points
			"ALTER TABLE {$p}ah_section_guide_through
				ADD CONSTRAINT fk_gt_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_gt_img  FOREIGN KEY (image_id)   REFERENCES {$p}ah_media(id)       ON DELETE SET NULL,
				ADD CONSTRAINT fk_gt_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",
			"ALTER TABLE {$p}ah_section_guide_through_points
				ADD CONSTRAINT fk_gtp_guide FOREIGN KEY (guide_id) REFERENCES {$p}ah_section_guide_through(id) ON DELETE CASCADE",

			// stack items
			"ALTER TABLE {$p}ah_section_stack_items
				ADD CONSTRAINT fk_si_page FOREIGN KEY (page_id) REFERENCES {$p}ah_pages(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_si_icon FOREIGN KEY (icon_id) REFERENCES {$p}ah_media(id) ON DELETE SET NULL",

			// difference
			"ALTER TABLE {$p}ah_section_difference
				ADD CONSTRAINT fk_diff_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_diff_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",
			"ALTER TABLE {$p}ah_section_difference_table
				ADD CONSTRAINT fk_dt_diff FOREIGN KEY (difference_id) REFERENCES {$p}ah_section_difference(id) ON DELETE CASCADE",

			// featured properties
			"ALTER TABLE {$p}ah_section_featured_properties
				ADD CONSTRAINT fk_fp_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_fp_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",
			"ALTER TABLE {$p}ah_section_featured_properties_items
				ADD CONSTRAINT fk_fpi_sec FOREIGN KEY (section_id) REFERENCES {$p}ah_section_featured_properties(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_fpi_img FOREIGN KEY (image_id)   REFERENCES {$p}ah_media(id) ON DELETE SET NULL",

			// experience
			"ALTER TABLE {$p}ah_section_experience
				ADD CONSTRAINT fk_exp_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_exp_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",
			"ALTER TABLE {$p}ah_section_experience_cards
				ADD CONSTRAINT fk_ec_sec FOREIGN KEY (section_id) REFERENCES {$p}ah_section_experience(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_ec_img FOREIGN KEY (image_id)   REFERENCES {$p}ah_media(id) ON DELETE SET NULL",

			// why required
			"ALTER TABLE {$p}ah_section_why_required
				ADD CONSTRAINT fk_wr_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_wr_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",
			"ALTER TABLE {$p}ah_section_why_required_cards
				ADD CONSTRAINT fk_wrc_sec FOREIGN KEY (section_id) REFERENCES {$p}ah_section_why_required(id) ON DELETE CASCADE",

			// reviews
			"ALTER TABLE {$p}ah_reviews
				ADD CONSTRAINT fk_rv_img  FOREIGN KEY (reviewer_image_id) REFERENCES {$p}ah_media(id)       ON DELETE SET NULL,
				ADD CONSTRAINT fk_rv_user FOREIGN KEY (created_by)        REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",
			"ALTER TABLE {$p}ah_section_reviews_header
				ADD CONSTRAINT fk_srh_page FOREIGN KEY (page_id) REFERENCES {$p}ah_pages(id) ON DELETE CASCADE",

			// faqs
			"ALTER TABLE {$p}ah_faqs
				ADD CONSTRAINT fk_faq_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE SET NULL,
				ADD CONSTRAINT fk_faq_user FOREIGN KEY (created_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",
			"ALTER TABLE {$p}ah_section_faq_header
				ADD CONSTRAINT fk_sfh_page FOREIGN KEY (page_id) REFERENCES {$p}ah_pages(id) ON DELETE CASCADE",

			// services
			"ALTER TABLE {$p}ah_services
				ADD CONSTRAINT fk_svc_img  FOREIGN KEY (image_id)   REFERENCES {$p}ah_media(id)       ON DELETE SET NULL,
				ADD CONSTRAINT fk_svc_user FOREIGN KEY (created_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",
			"ALTER TABLE {$p}ah_service_bullet_points
				ADD CONSTRAINT fk_sbp_svc FOREIGN KEY (service_id) REFERENCES {$p}ah_services(id) ON DELETE CASCADE",
			"ALTER TABLE {$p}ah_services_page_header
				ADD CONSTRAINT fk_sph_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_sph_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",
			"ALTER TABLE {$p}ah_service_taxonomies
				ADD CONSTRAINT fk_stax_svc FOREIGN KEY (service_id)  REFERENCES {$p}ah_services(id)  ON DELETE CASCADE,
				ADD CONSTRAINT fk_stax_tax FOREIGN KEY (taxonomy_id) REFERENCES {$p}ah_taxonomies(id) ON DELETE CASCADE",

			// about
			"ALTER TABLE {$p}ah_about_page_header
				ADD CONSTRAINT fk_aph_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_aph_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",
			"ALTER TABLE {$p}ah_about_story
				ADD CONSTRAINT fk_ast_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_ast_img  FOREIGN KEY (image_id)   REFERENCES {$p}ah_media(id)       ON DELETE SET NULL,
				ADD CONSTRAINT fk_ast_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",
			"ALTER TABLE {$p}ah_about_story_points
				ADD CONSTRAINT fk_asp_story FOREIGN KEY (story_id) REFERENCES {$p}ah_about_story(id) ON DELETE CASCADE",
			"ALTER TABLE {$p}ah_team_members
				ADD CONSTRAINT fk_tm_photo FOREIGN KEY (photo_id)   REFERENCES {$p}ah_media(id)       ON DELETE SET NULL,
				ADD CONSTRAINT fk_tm_user  FOREIGN KEY (created_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",
			"ALTER TABLE {$p}ah_about_values
				ADD CONSTRAINT fk_av_page FOREIGN KEY (page_id)  REFERENCES {$p}ah_pages(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_av_img  FOREIGN KEY (image_id) REFERENCES {$p}ah_media(id) ON DELETE SET NULL",

			// posts
			"ALTER TABLE {$p}ah_posts
				ADD CONSTRAINT fk_pt_feat   FOREIGN KEY (featured_image_id) REFERENCES {$p}ah_media(id)       ON DELETE SET NULL,
				ADD CONSTRAINT fk_pt_banner FOREIGN KEY (banner_image_id)   REFERENCES {$p}ah_media(id)       ON DELETE SET NULL,
				ADD CONSTRAINT fk_pt_author FOREIGN KEY (author_id)         REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",
			"ALTER TABLE {$p}ah_post_taxonomies
				ADD CONSTRAINT fk_ptax_post FOREIGN KEY (post_id)    REFERENCES {$p}ah_posts(id)      ON DELETE CASCADE,
				ADD CONSTRAINT fk_ptax_tax  FOREIGN KEY (taxonomy_id) REFERENCES {$p}ah_taxonomies(id) ON DELETE CASCADE",
			"ALTER TABLE {$p}ah_post_table_blocks
				ADD CONSTRAINT fk_ptb_post FOREIGN KEY (post_id) REFERENCES {$p}ah_posts(id) ON DELETE CASCADE",
			"ALTER TABLE {$p}ah_post_links
				ADD CONSTRAINT fk_pl_post FOREIGN KEY (post_id) REFERENCES {$p}ah_posts(id) ON DELETE CASCADE",
			"ALTER TABLE {$p}ah_post_stack_items
				ADD CONSTRAINT fk_psi_post FOREIGN KEY (post_id) REFERENCES {$p}ah_posts(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_psi_icon FOREIGN KEY (icon_id) REFERENCES {$p}ah_media(id) ON DELETE SET NULL",
			"ALTER TABLE {$p}ah_post_listing_page_header
				ADD CONSTRAINT fk_plph_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_plph_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			// client stories
			"ALTER TABLE {$p}ah_client_stories_header
				ADD CONSTRAINT fk_csh_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_csh_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",
			"ALTER TABLE {$p}ah_client_story_images
				ADD CONSTRAINT fk_csi_page FOREIGN KEY (page_id)  REFERENCES {$p}ah_pages(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_csi_img  FOREIGN KEY (image_id) REFERENCES {$p}ah_media(id) ON DELETE CASCADE",
			"ALTER TABLE {$p}ah_client_users_journey
				ADD CONSTRAINT fk_cuj_page FOREIGN KEY (page_id)  REFERENCES {$p}ah_pages(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_cuj_img  FOREIGN KEY (image_id) REFERENCES {$p}ah_media(id) ON DELETE SET NULL",
			"ALTER TABLE {$p}ah_client_gallery
				ADD CONSTRAINT fk_cg_page FOREIGN KEY (page_id)  REFERENCES {$p}ah_pages(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_cg_img  FOREIGN KEY (image_id) REFERENCES {$p}ah_media(id) ON DELETE CASCADE",
			"ALTER TABLE {$p}ah_client_video_links
				ADD CONSTRAINT fk_cvl_page  FOREIGN KEY (page_id)      REFERENCES {$p}ah_pages(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_cvl_thumb FOREIGN KEY (thumbnail_id) REFERENCES {$p}ah_media(id) ON DELETE SET NULL",

			// contact
			"ALTER TABLE {$p}ah_contact_page_config
				ADD CONSTRAINT fk_cpc_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_cpc_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			// footer
			"ALTER TABLE {$p}ah_footer_config
				ADD CONSTRAINT fk_fc_logo FOREIGN KEY (logo_id)    REFERENCES {$p}ah_media(id)       ON DELETE SET NULL,
				ADD CONSTRAINT fk_fc_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			// floating widgets
			"ALTER TABLE {$p}ah_floating_widgets
				ADD CONSTRAINT fk_fw_icon FOREIGN KEY (icon_id) REFERENCES {$p}ah_media(id) ON DELETE SET NULL",

			// news detail
			"ALTER TABLE {$p}ah_news_detail_big_cards
				ADD CONSTRAINT fk_ndbc_post FOREIGN KEY (post_id) REFERENCES {$p}ah_posts(id) ON DELETE CASCADE",
			"ALTER TABLE {$p}ah_news_detail_card_links
				ADD CONSTRAINT fk_ndcl_card FOREIGN KEY (card_id) REFERENCES {$p}ah_news_detail_big_cards(id) ON DELETE CASCADE",
			"ALTER TABLE {$p}ah_related_posts
				ADD CONSTRAINT fk_rp_post    FOREIGN KEY (post_id)         REFERENCES {$p}ah_posts(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_rp_related FOREIGN KEY (related_post_id) REFERENCES {$p}ah_posts(id) ON DELETE CASCADE",

			// audit logs
			"ALTER TABLE {$p}ah_audit_logs
				ADD CONSTRAINT fk_al_user FOREIGN KEY (user_id) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			// random blog cards
			"ALTER TABLE {$p}ah_random_blog_card_configs
				ADD CONSTRAINT fk_rbc_page FOREIGN KEY (page_id) REFERENCES {$p}ah_pages(id) ON DELETE CASCADE",
		);

		foreach ( $fks as $sql ) {
			// Silently fail if FK already exists - ignore duplicate FK errors.
			$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
	}

	// ----------------------------------------------------------------
	// Seed default data
	// ----------------------------------------------------------------

	private static function seed_defaults(): void {
		global $wpdb;
		$p = $wpdb->prefix;

		// Default admin role
		if ( ! $wpdb->get_var( "SELECT id FROM {$p}ah_admin_roles WHERE name = 'super_admin'" ) ) {
			$wpdb->insert(
				"{$p}ah_admin_roles",
				array(
					'name'        => 'super_admin',
					'permissions' => json_encode( array( '*' ) ),
				)
			);
		}

		// Default taxonomy types
		$default_types = array(
			array( 'name' => 'Category', 'slug' => 'category', 'description' => 'Content categories' ),
			array( 'name' => 'Tag',      'slug' => 'tag',      'description' => 'Content tags'       ),
			array( 'name' => 'Subtag',   'slug' => 'subtag',   'description' => 'Content subtags'    ),
		);
		foreach ( $default_types as $type ) {
			if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$p}ah_taxonomy_types WHERE slug = %s", $type['slug'] ) ) ) {
				$wpdb->insert( "{$p}ah_taxonomy_types", $type );
			}
		}

		// Default site settings
		$default_settings = array(
			array( 'setting_key' => 'site_name',      'setting_val' => 'Advith Homes',              'field_type' => 'text',     'group_name' => 'general', 'label' => 'Site Name'        ),
			array( 'setting_key' => 'site_logo',       'setting_val' => '',                           'field_type' => 'image',    'group_name' => 'general', 'label' => 'Site Logo'        ),
			array( 'setting_key' => 'whatsapp_number', 'setting_val' => '',                           'field_type' => 'phone',    'group_name' => 'contact', 'label' => 'WhatsApp Number'  ),
			array( 'setting_key' => 'contact_email',   'setting_val' => '',                           'field_type' => 'email',    'group_name' => 'contact', 'label' => 'Contact Email'    ),
			array( 'setting_key' => 'contact_phone',   'setting_val' => '',                           'field_type' => 'phone',    'group_name' => 'contact', 'label' => 'Contact Phone'    ),
			array( 'setting_key' => 'primary_color',   'setting_val' => '#2563eb',                    'field_type' => 'color',    'group_name' => 'design',  'label' => 'Primary Color'    ),
			array( 'setting_key' => 'facebook_url',    'setting_val' => '',                           'field_type' => 'url',      'group_name' => 'social',  'label' => 'Facebook URL'     ),
			array( 'setting_key' => 'twitter_url',     'setting_val' => '',                           'field_type' => 'url',      'group_name' => 'social',  'label' => 'Twitter URL'      ),
			array( 'setting_key' => 'linkedin_url',    'setting_val' => '',                           'field_type' => 'url',      'group_name' => 'social',  'label' => 'LinkedIn URL'     ),
			array( 'setting_key' => 'instagram_url',   'setting_val' => '',                           'field_type' => 'url',      'group_name' => 'social',  'label' => 'Instagram URL'    ),
			array( 'setting_key' => 'google_maps_url', 'setting_val' => '',                           'field_type' => 'url',      'group_name' => 'contact', 'label' => 'Google Maps URL'  ),
			array( 'setting_key' => 'footer_tagline',  'setting_val' => 'Your trusted home partner.', 'field_type' => 'textarea', 'group_name' => 'general', 'label' => 'Footer Tagline'   ),
		);
		foreach ( $default_settings as $setting ) {
			if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$p}ah_site_settings WHERE setting_key = %s", $setting['setting_key'] ) ) ) {
				$wpdb->insert( "{$p}ah_site_settings", $setting );
			}
		}

		// Default nav menus
		foreach ( array( array( 'name' => 'Primary Menu', 'slug' => 'primary' ), array( 'name' => 'Footer Menu', 'slug' => 'footer' ) ) as $menu ) {
			if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$p}ah_nav_menus WHERE slug = %s", $menu['slug'] ) ) ) {
				$wpdb->insert( "{$p}ah_nav_menus", $menu );
			}
		}

		// Default pages
		$default_pages = array(
			array( 'title' => 'Home',           'slug' => 'home',           'page_type' => 'home'          ),
			array( 'title' => 'About',          'slug' => 'about',          'page_type' => 'about'         ),
			array( 'title' => 'Services',       'slug' => 'services',       'page_type' => 'services'      ),
			array( 'title' => 'Contact',        'slug' => 'contact',        'page_type' => 'contact'       ),
			array( 'title' => 'Client Stories', 'slug' => 'client-stories', 'page_type' => 'client_stories'),
			array( 'title' => 'Blog',           'slug' => 'blog',           'page_type' => 'blog_listing'  ),
			array( 'title' => 'News',           'slug' => 'news',           'page_type' => 'news_listing'  ),
		);
		foreach ( $default_pages as $pg ) {
			if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$p}ah_pages WHERE slug = %s", $pg['slug'] ) ) ) {
				$wpdb->insert( "{$p}ah_pages", $pg );
			}
		}
	}

	/**
	 * Seed a "Review Categories" taxonomy type with four routing terms.
	 * Slugs match the $taxonomy_slug values used by ch_get_reviews() in the theme.
	 * Safe to call on every page load — idempotent.
	 */
	public static function ensure_review_categories_taxonomy_type(): void {
		global $wpdb;
		$p = $wpdb->prefix;

		$type_id = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM `{$p}ah_taxonomy_types` WHERE slug = %s",
			'review-categories'
		) );
		if ( ! $type_id ) {
			$wpdb->insert( "{$p}ah_taxonomy_types", array(
				'name'        => 'Review Categories',
				'slug'        => 'review-categories',
				'description' => 'Routes each review to the correct section on the website.',
			) );
			$type_id = (int) $wpdb->insert_id;
		}

		if ( ! $type_id ) {
			return;
		}

		// Terms — slugs must match the taxonomy_slug values used in theme components.
		$terms = array(
			array( 'name' => 'Customer Review',   'slug' => 'customer'     ),
			array( 'name' => 'Franchise Partner', 'slug' => 'partner'      ),
			array( 'name' => 'Event Review',      'slug' => 'event'        ),
			array( 'name' => 'Client Story',      'slug' => 'client-story' ),
		);
		foreach ( $terms as $term ) {
			$exists = $wpdb->get_var( $wpdb->prepare(
				"SELECT id FROM `{$p}ah_taxonomies` WHERE type_id = %d AND slug = %s",
				$type_id, $term['slug']
			) );
			if ( ! $exists ) {
				$wpdb->insert( "{$p}ah_taxonomies", array(
					'type_id' => $type_id,
					'name'    => $term['name'],
					'slug'    => $term['slug'],
					'status'  => 'active',
				) );
			}
		}
	}

	/**
	 * Seed a "FAQ Tags" taxonomy type with the default FAQ topics as terms.
	 * Attached to FAQs via the ah_content_taxonomies pivot (object_type = 'faq').
	 * Safe to call on every load — idempotent.
	 */
	public static function ensure_faq_tags_taxonomy_type(): void {
		global $wpdb;
		$p = $wpdb->prefix;

		$type_id = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM `{$p}ah_taxonomy_types` WHERE slug = %s",
			'faq-tags'
		) );
		if ( ! $type_id ) {
			$wpdb->insert( "{$p}ah_taxonomy_types", array(
				'name'        => 'FAQ Tags',
				'slug'        => 'faq-tags',
				'description' => 'Tags used to group and filter FAQs.',
			) );
			$type_id = (int) $wpdb->insert_id;
		}

		if ( ! $type_id ) {
			return;
		}

		// Default terms — the original theme FAQ topics.
		$terms = array(
			array( 'name' => 'General',   'slug' => 'general'   ),
			array( 'name' => 'Events',    'slug' => 'events'    ),
			array( 'name' => 'Juice',     'slug' => 'juice'     ),
			array( 'name' => 'Franchise', 'slug' => 'franchise' ),
		);
		foreach ( $terms as $term ) {
			$exists = $wpdb->get_var( $wpdb->prepare(
				"SELECT id FROM `{$p}ah_taxonomies` WHERE type_id = %d AND slug = %s",
				$type_id, $term['slug']
			) );
			if ( ! $exists ) {
				$wpdb->insert( "{$p}ah_taxonomies", array(
					'type_id' => $type_id,
					'name'    => $term['name'],
					'slug'    => $term['slug'],
					'status'  => 'active',
				) );
			}
		}
	}

	/**
	 * Create ah_review_images table for per-review occasion/gallery images.
	 */
	public static function ensure_review_images_table(): void {
		global $wpdb;
		$t  = $wpdb->prefix . 'ah_review_images';
		$cs = $wpdb->get_charset_collate();
		$wpdb->query( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			"CREATE TABLE IF NOT EXISTS `{$t}` (
				`id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`review_id`  INT UNSIGNED NOT NULL,
				`image_id`   INT UNSIGNED NOT NULL,
				`caption`    VARCHAR(300) DEFAULT NULL,
				`sort_order` INT          NOT NULL DEFAULT 0,
				PRIMARY KEY (`id`),
				KEY `idx_review` (`review_id`),
				KEY `idx_sort`   (`review_id`, `sort_order`)
			) ENGINE=InnoDB {$cs}"
		);
	}

	/**
	 * Seed a "Review Type" taxonomy type with Customer / Partner / Event terms.
	 * Safe to call on every load — uses INSERT IGNORE so it never duplicates.
	 */
	public static function ensure_review_taxonomy_type(): void {
		global $wpdb;
		$p = $wpdb->prefix;

		// 1. Taxonomy type
		$type_id = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM `{$p}ah_taxonomy_types` WHERE slug = %s",
			'review-type'
		) );
		if ( ! $type_id ) {
			$wpdb->insert( "{$p}ah_taxonomy_types", array(
				'name'        => 'Review Type',
				'slug'        => 'review-type',
				'description' => 'Categorises reviews by audience (Customer, Partner, Event).',
			) );
			$type_id = (int) $wpdb->insert_id;
		}

		if ( ! $type_id ) {
			return;
		}

		// 2. Seed default terms
		$terms = array(
			array( 'name' => 'Customer', 'slug' => 'customer' ),
			array( 'name' => 'Partner',  'slug' => 'partner'  ),
			array( 'name' => 'Event',    'slug' => 'event'    ),
		);
		foreach ( $terms as $term ) {
			$exists = $wpdb->get_var( $wpdb->prepare(
				"SELECT id FROM `{$p}ah_taxonomies` WHERE type_id = %d AND slug = %s",
				$type_id, $term['slug']
			) );
			if ( ! $exists ) {
				$wpdb->insert( "{$p}ah_taxonomies", array(
					'type_id' => $type_id,
					'name'    => $term['name'],
					'slug'    => $term['slug'],
					'status'  => 'active',
				) );
			}
		}
	}

	/**
	 * Seed a default rule for the Sugarcane contact form.
	 * Creates admin notification + auto-reply email actions.
	 * Skips if any rule for this trigger already exists — idempotent.
	 */
	public static function ensure_sugarcane_contact_rule(): void {
		if ( ! class_exists( 'AH_Rules_Engine' ) ) return;

		global $wpdb;
		$t = AH_Rules_Engine::table();

		$exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM `{$t}` WHERE trigger_name = %s LIMIT 1",
			'sugarcane_contact_form'
		) );
		if ( $exists ) return;

		AH_Rules_Engine::save( 0, array(
			'name'             => 'Sugarcane - Contact Form Emails',
			'trigger_name'     => 'sugarcane_contact_form',
			'conditions_match' => 'all',
			'conditions'       => array(),
			'status'           => 'active',
			'actions'          => array(
				array(
					'type'    => 'send_email',
					'to'      => '{config_email_from_email}',
					'subject' => 'New Contact Enquiry from {name} - {enquiry_label}',
					'body'    => "You have received a new enquiry via The Cane House website.\n\nName:         {name}\nEmail:        {email}\nPhone:        {phone}\nEnquiry Type: {enquiry_label}\n\nMessage:\n{message}\n\n---\nSent from {site_url}\nTime: {submitted_at}",
					'html'    => 0,
					'cc'      => '{config_email_from_email}',
				),
				array(
					'type'    => 'send_email',
					'to'      => '{email}',
					'subject' => 'Thanks for contacting The Cane House - we\'ll be in touch soon!',
					'body'    => "Hi {name},\n\nThank you for getting in touch with The Cane House! \xf0\x9f\x8c\xbf\n\nWe've received your message and will get back to you very soon - usually within a few hours.\n\nIn the meantime, if your enquiry is urgent, please call us directly.\n\nPressed Fresh. Served Cool.\nThe Cane House Team\nwww.thecanehouse.co.uk",
					'html'    => 0,
					'cc'      => '{config_email_from_email}',
				),
			),
		) );
	}

	/**
	 * One-time migration: patch the seeded Sugarcane contact rule to add CC
	 * on both actions if they currently have a blank CC field.
	 * Idempotent — skips actions that already have a CC value.
	 */
	public static function ensure_contact_form_rule_cc(): void {
		if ( ! class_exists( 'AH_Rules_Engine' ) ) return;

		global $wpdb;
		$t = AH_Rules_Engine::table();

		$row = $wpdb->get_row( $wpdb->prepare(
			"SELECT id, actions FROM `{$t}` WHERE trigger_name = %s AND name = %s LIMIT 1",
			'sugarcane_contact_form',
			'Sugarcane - Contact Form Emails'
		) );
		if ( ! $row ) return;

		$actions = json_decode( $row->actions, true );
		if ( ! is_array( $actions ) ) return;

		$changed = false;
		foreach ( $actions as &$action ) {
			if ( ( $action['type'] ?? '' ) === 'send_email' && empty( $action['cc'] ) ) {
				$action['cc'] = '{config_email_from_email}';
				$changed = true;
			}
		}
		unset( $action );

		if ( $changed ) {
			$wpdb->update(
				$t,
				array( 'actions' => wp_json_encode( $actions ) ),
				array( 'id' => (int) $row->id ),
				array( '%s' ),
				array( '%d' )
			);
		}
	}

	// ----------------------------------------------------------------
	// Utility: get prefixed table name
	// ----------------------------------------------------------------

	public static function table( string $name ): string {
		global $wpdb;
		return $wpdb->prefix . 'ah_' . $name;
	}
}
