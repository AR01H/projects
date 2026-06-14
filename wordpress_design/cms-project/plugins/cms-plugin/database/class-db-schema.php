<?php
defined( 'ABSPATH' ) || exit;

/**
 * All CREATE TABLE definitions.
 * Add new tables here. Never put ALTER or data here.
 */
class AH_DB_Schema {

	public static function tables(): array {
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
				is_protected     TINYINT(1) NOT NULL DEFAULT 0,
				image_id         INT UNSIGNED DEFAULT NULL,
				icon_emoji       VARCHAR(20) DEFAULT NULL,
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
				page_type        ENUM('home','services','contact','client_stories','blog_listing','news_listing','custom') DEFAULT 'custom',
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
				image_id    INT UNSIGNED DEFAULT NULL,
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
				description         TEXT,
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
				short_desc        VARCHAR(400) DEFAULT NULL,
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


			// 40. Posts
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

			// 41c. Universal related-content links (polymorphic source + target)
			"CREATE TABLE IF NOT EXISTS {$p}ah_related_links (
				id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				object_type   VARCHAR(50)  NOT NULL,
				object_id     BIGINT UNSIGNED NOT NULL,
				link_type     VARCHAR(50)  NOT NULL DEFAULT 'external',
				target_kind   VARCHAR(20)  NOT NULL DEFAULT 'url',
				target_id     BIGINT UNSIGNED NULL,
				url           TEXT NULL,
				label         VARCHAR(255) NULL,
				container     VARCHAR(120) NULL,
				target_window VARCHAR(10)  NOT NULL DEFAULT '_self',
				sort_order    INT NOT NULL DEFAULT 0,
				status        VARCHAR(20)  NOT NULL DEFAULT 'active',
				created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY idx_object (object_type, object_id),
				KEY idx_link_type (link_type),
				KEY idx_container (container),
				KEY idx_target (target_kind, target_id)
			) ENGINE=InnoDB {$cs}",

			// 41d. Static HTML pages/components (HTML stored in DB, not flat files)
			"CREATE TABLE IF NOT EXISTS {$p}ah_static_pages (
				id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				slug       VARCHAR(190) NOT NULL,
				title      VARCHAR(255) NULL,
				html       LONGTEXT NULL,
				page_id    BIGINT UNSIGNED NULL,
				status     VARCHAR(20) NOT NULL DEFAULT 'active',
				created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				UNIQUE KEY uq_slug (slug),
				KEY idx_page (page_id)
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

			// 63. Events (hire packages)
			"CREATE TABLE IF NOT EXISTS `{$p}ah_events` (
				`id`                   INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`icon`                 VARCHAR(30)  NOT NULL DEFAULT '🎉',
				`title`                VARCHAR(200) NOT NULL,
				`description`          TEXT         DEFAULT NULL,
				`items`                JSON         DEFAULT NULL,
				`color`                VARCHAR(30)  NOT NULL DEFAULT 'green',
				`is_featured`          TINYINT(1)   NOT NULL DEFAULT 0,
				`sort_order`           INT          NOT NULL DEFAULT 0,
				`status`               ENUM('active','inactive') NOT NULL DEFAULT 'active',
				`notify_on_booking`    TINYINT(1)   NOT NULL DEFAULT 0,
				`booking_trigger_name` VARCHAR(100) DEFAULT NULL,
				`created_at`           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				KEY `idx_status`   (`status`),
				KEY `idx_featured` (`is_featured`),
				KEY `idx_sort`     (`sort_order`)
			) ENGINE=InnoDB {$cs}",

			// 64. Home hero banners
			"CREATE TABLE IF NOT EXISTS `{$p}ah_home_banners` (
				`id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`image`        VARCHAR(500) NOT NULL DEFAULT '',
				`image_mobile` VARCHAR(500) NOT NULL DEFAULT '',
				`subtitle`     VARCHAR(255) NOT NULL DEFAULT '',
				`title`        VARCHAR(500) NOT NULL DEFAULT '',
				`description`  TEXT         DEFAULT NULL,
				`btn_text`     VARCHAR(255) NOT NULL DEFAULT '',
				`btn_url`      VARCHAR(500) NOT NULL DEFAULT '',
				`btn_target`   VARCHAR(10)  NOT NULL DEFAULT '_self',
				`text_align`   VARCHAR(10)  NOT NULL DEFAULT 'center',
				`text_pos`     VARCHAR(10)  NOT NULL DEFAULT 'middle',
				`overlay`      VARCHAR(100) NOT NULL DEFAULT 'rgba(26,58,15,0.45)',
				`status`       ENUM('active','inactive') NOT NULL DEFAULT 'active',
				`sort_order`   INT          NOT NULL DEFAULT 0,
				`created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				KEY `idx_status_sort` (`status`, `sort_order`)
			) ENGINE=InnoDB {$cs}",

			// 65. Page builder pages
			"CREATE TABLE IF NOT EXISTS `{$p}ah_builder_pages` (
				`id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				`title`            VARCHAR(255) NOT NULL,
				`slug`             VARCHAR(280) NOT NULL UNIQUE,
				`blocks`           LONGTEXT DEFAULT NULL,
				`status`           ENUM('active','draft') DEFAULT 'draft',
				`meta_title`       VARCHAR(255),
				`meta_description` TEXT,
				`created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				`updated_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				KEY `idx_slug` (`slug`),
				KEY `idx_status` (`status`)
			) ENGINE=InnoDB {$cs}",

			// 66. Review images
			"CREATE TABLE IF NOT EXISTS `{$p}ah_review_images` (
				`id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`review_id`  INT UNSIGNED NOT NULL,
				`image_id`   INT UNSIGNED NOT NULL,
				`caption`    VARCHAR(300) DEFAULT NULL,
				`sort_order` INT          NOT NULL DEFAULT 0,
				PRIMARY KEY (`id`),
				KEY `idx_review` (`review_id`),
				KEY `idx_sort`   (`review_id`, `sort_order`)
			) ENGINE=InnoDB {$cs}",

			// 67. Automation rules
			"CREATE TABLE IF NOT EXISTS `{$p}ah_rules` (
				`id`               INT UNSIGNED      NOT NULL AUTO_INCREMENT,
				`name`             VARCHAR(200)      NOT NULL DEFAULT '',
				`trigger_name`     VARCHAR(100)      NOT NULL DEFAULT 'form_submit',
				`conditions_match` ENUM('all','any') NOT NULL DEFAULT 'all',
				`conditions`       JSON              DEFAULT NULL,
				`actions`          JSON              DEFAULT NULL,
				`settings`         JSON              DEFAULT NULL,
				`status`           ENUM('active','inactive') NOT NULL DEFAULT 'active',
				`run_count`        INT UNSIGNED      NOT NULL DEFAULT 0,
				`last_run`         DATETIME          DEFAULT NULL,
				`created_at`       DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB {$cs}",

			// 68. Evaluate log
			"CREATE TABLE IF NOT EXISTS `{$p}ah_evaluate_log` (
				`id`           BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
				`trigger_name` VARCHAR(100)     NOT NULL DEFAULT '',
				`context_data` JSON             DEFAULT NULL,
				`rules_found`  TINYINT UNSIGNED NOT NULL DEFAULT 0,
				`rules_fired`  TINYINT UNSIGNED NOT NULL DEFAULT 0,
				`created_at`   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				KEY `idx_trigger` (`trigger_name`),
				KEY `idx_created` (`created_at`)
			) ENGINE=InnoDB {$cs}",

			// 69. Analytics Reports
			"CREATE TABLE IF NOT EXISTS `{$p}ah_analytics_reports` (
				`id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`name`        VARCHAR(200) NOT NULL DEFAULT '',
				`description` TEXT         DEFAULT NULL,
				`query_sql`   LONGTEXT     NOT NULL,
				`run_count`   INT UNSIGNED NOT NULL DEFAULT 0,
				`last_run_at` DATETIME     DEFAULT NULL,
				`created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				KEY `idx_name` (`name`)
			) ENGINE=InnoDB {$cs}",

			// 70. Analytics Results
			"CREATE TABLE IF NOT EXISTS `{$p}ah_analytics_results` (
				`id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`report_id`     INT UNSIGNED NOT NULL,
				`run_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`row_count`     INT UNSIGNED NOT NULL DEFAULT 0,
				`exec_ms`       INT UNSIGNED NOT NULL DEFAULT 0,
				`status`        ENUM('success','error') NOT NULL DEFAULT 'success',
				`result_json`   LONGTEXT     DEFAULT NULL,
				`error_message` TEXT         DEFAULT NULL,
				`export_file`   VARCHAR(500) DEFAULT NULL,
				PRIMARY KEY (`id`),
				KEY `idx_report` (`report_id`),
				KEY `idx_run_at` (`report_id`, `run_at`)
			) ENGINE=InnoDB {$cs}",
		);
	}
}
