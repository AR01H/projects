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
		self::drop_broken_fks();
	}

	/**
	 * Drop FK constraints that reference ah_admin_users or ah_media via columns
	 * that are now populated with WP user IDs / WP attachment IDs.  Running
	 * DROP FOREIGN KEY is idempotent — MySQL silently ignores unknown constraint
	 * names when FOREIGN_KEY_CHECKS = 0.
	 */
	public static function drop_broken_fks(): void {
		global $wpdb;
		$p = $wpdb->prefix;
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 0' );
		$drops = array(
			// reviews: created_by → ah_admin_users (we pass WP user IDs)
			"ALTER TABLE `{$p}ah_reviews` DROP FOREIGN KEY IF EXISTS fk_rv_user",
			// reviews: reviewer_image_id → ah_media (we store WP attachment IDs)
			"ALTER TABLE `{$p}ah_reviews` DROP FOREIGN KEY IF EXISTS fk_rv_img",
			// news_bar_items: created_by → ah_admin_users
			"ALTER TABLE `{$p}ah_news_bar_items` DROP FOREIGN KEY IF EXISTS fk_nbi_user",
			// faqs: created_by → ah_admin_users
			"ALTER TABLE `{$p}ah_faqs` DROP FOREIGN KEY IF EXISTS fk_faq_user",
			// services: created_by → ah_admin_users
			"ALTER TABLE `{$p}ah_services` DROP FOREIGN KEY IF EXISTS fk_svc_user",
			// team members: created_by → ah_admin_users
			"ALTER TABLE `{$p}ah_team_members` DROP FOREIGN KEY IF EXISTS fk_tm_user",
		);
		foreach ( $drops as $sql ) {
			$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 1' );
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
			array( 'setting_key' => 'phone',            'setting_val' => '', 'field_type' => 'phone',    'group_name' => 'contact', 'label' => 'Phone'            ),
			array( 'setting_key' => 'whatsapp',         'setting_val' => '', 'field_type' => 'phone',    'group_name' => 'contact', 'label' => 'WhatsApp'         ),
			array( 'setting_key' => 'email',            'setting_val' => '', 'field_type' => 'email',    'group_name' => 'contact', 'label' => 'Email'            ),
			array( 'setting_key' => 'address',          'setting_val' => '', 'field_type' => 'textarea', 'group_name' => 'contact', 'label' => 'Address'          ),
			array( 'setting_key' => 'consultation_url', 'setting_val' => '', 'field_type' => 'url',      'group_name' => 'contact', 'label' => 'Consultation URL' ),
			array( 'setting_key' => 'youtube_url',      'setting_val' => '', 'field_type' => 'url',      'group_name' => 'social',  'label' => 'YouTube URL'      ),
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

			// 7. Navigation Menus
			"CREATE TABLE IF NOT EXISTS {$p}ah_nav_menus (
				id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				name        VARCHAR(100) NOT NULL,
				slug        VARCHAR(120) NOT NULL UNIQUE,
				status      ENUM('active','inactive') DEFAULT 'active',
				created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
			) ENGINE=InnoDB {$cs}",

			// 8. Nav Menu Items
			"CREATE TABLE IF NOT EXISTS {$p}ah_nav_menu_items (
				id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				menu_id     INT UNSIGNED NOT NULL,
				parent_id   INT UNSIGNED DEFAULT NULL,
				label       VARCHAR(150) NOT NULL,
				url         VARCHAR(500),
				page_slug   VARCHAR(200),
				target      ENUM('_self','_blank') DEFAULT '_self',
				icon_class  VARCHAR(100),
				sort_order  INT DEFAULT 0,
				status      ENUM('active','inactive') DEFAULT 'active',
				KEY idx_menu (menu_id),
				KEY idx_parent (parent_id)
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
				ip_address   VARCHAR(45),
				is_read      TINYINT(1) DEFAULT 0,
				status       ENUM('new','in_progress','resolved','spam') DEFAULT 'new',
				submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				KEY idx_status (status),
				KEY idx_is_read (is_read)
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

			// nav_menu_items
			"ALTER TABLE {$p}ah_nav_menu_items
				ADD CONSTRAINT fk_nmi_menu   FOREIGN KEY (menu_id)   REFERENCES {$p}ah_nav_menus(id)      ON DELETE CASCADE,
				ADD CONSTRAINT fk_nmi_parent FOREIGN KEY (parent_id) REFERENCES {$p}ah_nav_menu_items(id) ON DELETE SET NULL",

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
			// Silently fail if FK already exists — ignore duplicate FK errors.
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

	// ----------------------------------------------------------------
	// Utility: get prefixed table name
	// ----------------------------------------------------------------

	public static function table( string $name ): string {
		global $wpdb;
		return $wpdb->prefix . 'ah_' . $name;
	}
}
