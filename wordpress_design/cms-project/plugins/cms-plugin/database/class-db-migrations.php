<?php
defined( 'ABSPATH' ) || exit;

/**
 * Column-level and data migrations.
 * Every method MUST be idempotent - safe to call on every admin load.
 * Add a new method here + register it in run() for each new migration.
 * Never put CREATE TABLE or FK constraints here.
 */
class AH_DB_Migrations {

	public static function run(): void {
		// Column additions
		self::hero_description();
		self::banners_mobile_image();
		self::events_notification_columns();
		self::reviews_short_desc();
		self::news_bar_content();
		self::news_bar_image();
		self::news_bar_label_excerpt();
		self::taxonomy_protected();
		self::taxonomy_media();
		self::rules_settings();
		self::trigger_logs_scheduled_at();

		// Table creations via migration (tables added after initial schema)
		self::ensure_content_taxonomies();
		self::ensure_taxonomy_parent_terms();
		self::ensure_static_pages();
		self::ensure_related_links();
		self::related_links_container();
		self::ensure_resources_table();
		self::resources_link_url();
		self::resources_highlight_label();

		// Data migrations
		self::required_settings();
		self::chrome_settings();
		self::chrome_settings_reorganize();
		self::contact_settings_seed();
		self::protected_taxonomy_data();
		self::review_categories_taxonomy_type();
		self::contact_form_rule_cc();
		self::spotlight_terms_page_slug();
		self::faq_section_column();
		self::site_notices_badge_position();
		self::site_notices_extra_features();
		self::site_notices_custom_freq();
	}

	// ── Column migrations ─────────────────────────────────────────────────────

	public static function hero_description(): void {
		self::add_column_if_missing( 'ah_section_hero', 'description', 'TEXT DEFAULT NULL AFTER `subheading`' );
	}

	public static function banners_mobile_image(): void {
		self::add_column_if_missing( 'ah_home_banners', 'image_mobile', "VARCHAR(500) NOT NULL DEFAULT '' AFTER `image`" );
	}

	public static function events_notification_columns(): void {
		self::add_column_if_missing( 'ah_events', 'notify_on_booking',    'TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`' );
		self::add_column_if_missing( 'ah_events', 'booking_trigger_name', 'VARCHAR(100) DEFAULT NULL AFTER `notify_on_booking`' );
	}

	public static function reviews_short_desc(): void {
		self::add_column_if_missing( 'ah_reviews', 'short_desc', 'VARCHAR(400) DEFAULT NULL AFTER `reviewer_title`' );
	}

	public static function news_bar_content(): void {
		self::add_column_if_missing( 'ah_news_bar_items', 'content', 'LONGTEXT NULL AFTER `text`' );
	}

	public static function news_bar_image(): void {
		self::add_column_if_missing( 'ah_news_bar_items', 'image_id', 'INT UNSIGNED DEFAULT NULL AFTER `content`' );
	}

	public static function news_bar_label_excerpt(): void {
		self::add_column_if_missing( 'ah_news_bar_items', 'label',   "VARCHAR(200) NOT NULL DEFAULT '' AFTER `id`" );
		self::add_column_if_missing( 'ah_news_bar_items', 'excerpt', "VARCHAR(500) NOT NULL DEFAULT '' AFTER `text`" );
	}

	public static function taxonomy_protected(): void {
		self::add_column_if_missing( 'ah_taxonomies', 'is_protected', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `sort_order`' );
	}

	public static function taxonomy_media(): void {
		self::add_column_if_missing( 'ah_taxonomies', 'image_id',    'INT UNSIGNED DEFAULT NULL AFTER `sort_order`' );
		self::add_column_if_missing( 'ah_taxonomies', 'icon_emoji',  "VARCHAR(20) DEFAULT NULL AFTER `image_id`" );
	}

	public static function rules_settings(): void {
		global $wpdb;
		$t = $wpdb->prefix . 'ah_rules';
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$t}'" ) && // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			! $wpdb->get_var( "SHOW COLUMNS FROM `{$t}` LIKE 'settings'" ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( "ALTER TABLE `{$t}` ADD COLUMN `settings` JSON DEFAULT NULL" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
	}

	public static function trigger_logs_scheduled_at(): void {
		global $wpdb;
		$t = $wpdb->prefix . 'ah_trigger_logs';
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$t}'" ) && // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			! $wpdb->get_var( "SHOW COLUMNS FROM `{$t}` LIKE 'scheduled_at'" ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( "ALTER TABLE `{$t}` ADD COLUMN `scheduled_at` DATETIME DEFAULT NULL" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
	}

	// ── Table-level migrations ────────────────────────────────────────────────

	public static function ensure_content_taxonomies(): void {
		if ( class_exists( 'AH_Content_Taxonomy_Model' ) ) {
			AH_Content_Taxonomy_Model::ensure_table();
		}
	}

	public static function ensure_taxonomy_parent_terms(): void {
		if ( class_exists( 'AH_Taxonomy_Parent_Model' ) ) {
			AH_Taxonomy_Parent_Model::ensure_table();
		}
	}

	/** Ensure the static-pages table exists and import any legacy HTML files once. */
	public static function ensure_static_pages(): void {
		if ( class_exists( 'AH_Static_Pages_Model' ) ) {
			AH_Static_Pages_Model::ensure_table();
			AH_Static_Pages_Model::import_files_once();
		}
	}

	public static function ensure_related_links(): void {
		if ( class_exists( 'AH_Related_Links_Model' ) ) {
			AH_Related_Links_Model::ensure_table();
		}
	}

	public static function related_links_container(): void {
		self::add_column_if_missing( 'ah_related_links', 'container', "VARCHAR(120) NULL AFTER `label`" );
	}

	public static function ensure_resources_table(): void {
		if ( class_exists( 'AH_Resources_Model' ) ) {
			AH_Resources_Model::ensure_table();
		}
	}

	// ── Data migrations ───────────────────────────────────────────────────────

	public static function required_settings(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_site_settings';
		foreach ( array(
			array( 'setting_key' => 'company_name',     'setting_val' => '',              'field_type' => 'text',     'group_name' => 'general', 'label' => 'Company Name'      ),
			array( 'setting_key' => 'phone',            'setting_val' => '', 'field_type' => 'phone',    'group_name' => 'contact', 'label' => 'Phone'            ),
			array( 'setting_key' => 'whatsapp',         'setting_val' => '', 'field_type' => 'phone',    'group_name' => 'contact', 'label' => 'WhatsApp'         ),
			array( 'setting_key' => 'email',            'setting_val' => '', 'field_type' => 'email',    'group_name' => 'contact', 'label' => 'Email'            ),
			array( 'setting_key' => 'address',          'setting_val' => '', 'field_type' => 'textarea', 'group_name' => 'contact', 'label' => 'Address'          ),
			array( 'setting_key' => 'consultation_url', 'setting_val' => '', 'field_type' => 'url',      'group_name' => 'contact', 'label' => 'Consultation URL' ),
			array( 'setting_key' => 'youtube_url',      'setting_val' => '', 'field_type' => 'url',      'group_name' => 'social',  'label' => 'YouTube URL'      ),
		) as $s ) {
			if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$table}` WHERE setting_key = %s", $s['setting_key'] ) ) ) {
				$wpdb->insert( $table, $s );
			}
		}
	}

	/**
	 * Seed default values for contact settings that were inserted as empty strings.
	 * Only updates rows that are still empty - never overwrites a user-saved value.
	 */
	public static function contact_settings_seed(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_site_settings';
		$seeds = array(
			'whatsapp' => '',
			'email'    => '',
		);
		foreach ( $seeds as $key => $default_val ) {
			$current = $wpdb->get_var( $wpdb->prepare(
				"SELECT setting_val FROM `{$table}` WHERE setting_key = %s",
				$key
			) );
			if ( null !== $current && '' === (string) $current ) {
				$wpdb->update( $table, array( 'setting_val' => $default_val ), array( 'setting_key' => $key ) );
			}
		}
	}

	/**
	 * Seed site-chrome settings (logo, search, footer brand, social URLs, copyright, disclaimer).
	 * Previously in site_chrome.json - now editable in WP Admin → Settings.
	 *
	 * Groups:
	 *   general - logo, search, footer brand, copyright, made-with, disclaimer
	 *   social  - individual social profile URLs (chrome_social_*)
	 *
	 * Social links are stored as individual URL fields (not a JSON blob) so they
	 * appear cleanly in the Social tab alongside other social settings.
	 * The service layer builds the footer social array from these fields at render time.
	 *
	 * %YEAR% in chrome_copyright is replaced with the current 4-digit year at render time.
	 * Idempotent: only inserts rows that do not already exist.
	 */
	public static function chrome_settings(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_site_settings';

		$defaults = array(
			// ── General tab ────────────────────────────────────────────────────
			// Logo
			array( 'setting_key' => 'chrome_logo_icon',   'setting_val' => '',           'field_type' => 'text',     'group_name' => 'general', 'label' => 'Logo Icon'                              ),
			array( 'setting_key' => 'chrome_logo_name',   'setting_val' => '',              'field_type' => 'text',     'group_name' => 'general', 'label' => 'Logo Name'                              ),
			array( 'setting_key' => 'chrome_logo_sub',    'setting_val' => '',         'field_type' => 'text',     'group_name' => 'general', 'label' => 'Logo Subtext'                           ),
			array( 'setting_key' => 'chrome_logo_url',    'setting_val' => '/',             'field_type' => 'url',      'group_name' => 'general', 'label' => 'Logo Link URL'                          ),
			// Header search
			array( 'setting_key' => 'chrome_search_ph',   'setting_val' => '', 'field_type' => 'text', 'group_name' => 'general', 'label' => 'Search Placeholder'  ),
			// Footer brand
			array( 'setting_key' => 'chrome_footer_icon', 'setting_val' => '',           'field_type' => 'text',     'group_name' => 'general', 'label' => 'Footer Brand Icon'                      ),
			array( 'setting_key' => 'chrome_footer_name', 'setting_val' => '',              'field_type' => 'text',    'group_name' => 'general', 'label' => 'Footer Brand Name'                      ),
			array( 'setting_key' => 'chrome_footer_sub',  'setting_val' => '',              'field_type' => 'text',     'group_name' => 'general', 'label' => 'Footer Brand Tagline'                   ),
			// Footer copy
			array( 'setting_key' => 'chrome_copyright',   'setting_val' => '© %YEAR% All rights reserved.', 'field_type' => 'text',     'group_name' => 'general', 'label' => 'Copyright Text (%YEAR% = current year)' ),
			array( 'setting_key' => 'chrome_made_with',   'setting_val' => '', 'field_type' => 'text',     'group_name' => 'general', 'label' => '"Made With" Line'              ),
			array( 'setting_key' => 'chrome_disclaimer',  'setting_val' => '', 'field_type' => 'textarea', 'group_name' => 'general', 'label' => 'Footer Disclaimer' ),

			// ── Social tab ─────────────────────────────────────────────────────
			// Individual URL fields - service layer builds the footer social icon row from these.
			array( 'setting_key' => 'chrome_social_facebook',  'setting_val' => '', 'field_type' => 'url', 'group_name' => 'social', 'label' => 'Facebook URL'  ),
			array( 'setting_key' => 'chrome_social_instagram', 'setting_val' => '', 'field_type' => 'url', 'group_name' => 'social', 'label' => 'Instagram URL' ),
			array( 'setting_key' => 'chrome_social_youtube',   'setting_val' => '', 'field_type' => 'url', 'group_name' => 'social', 'label' => 'YouTube URL (footer)'  ),
		);

		foreach ( $defaults as $row ) {
			if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$table}` WHERE setting_key = %s", $row['setting_key'] ) ) ) {
				$wpdb->insert( $table, $row );
			}
		}
	}

	/**
	 * Reorganise chrome settings that were seeded with the old group names.
	 *
	 * Previous version used group_name = 'chrome' for everything and stored
	 * social links as a single JSON blob (chrome_footer_social).
	 * This migration:
	 *   - Moves non-social chrome_* rows to group_name = 'general'
	 *   - Moves chrome_social_* rows to group_name = 'social'
	 *   - Deletes the obsolete chrome_footer_social JSON row
	 * Idempotent.
	 */
	public static function chrome_settings_reorganize(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_site_settings';

		// Non-social chrome settings → general tab.
		$wpdb->query( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			"UPDATE `{$table}` SET group_name = 'general'
			 WHERE group_name = 'chrome'
			   AND setting_key != 'chrome_footer_social'
			   AND setting_key NOT LIKE 'chrome_social_%'"
		);

		// Social URL fields → social tab.
		$wpdb->query( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			"UPDATE `{$table}` SET group_name = 'social'
			 WHERE group_name = 'chrome'
			   AND setting_key LIKE 'chrome_social_%'"
		);

		// Remove the obsolete JSON blob (replaced by individual URL fields).
		$wpdb->delete( $table, array( 'setting_key' => 'chrome_footer_social' ) );
	}

	public static function protected_taxonomy_data(): void {
		global $wpdb;
		$p = $wpdb->prefix;

		$type_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$p}ah_taxonomy_types` WHERE slug = %s", 'data-protected' ) );
		if ( ! $type_id ) {
			$wpdb->insert( "{$p}ah_taxonomy_types", array(
				'name' => 'DataProtected', 'slug' => 'data-protected', 'description' => 'System-protected taxonomy type',
			) );
			$type_id = $wpdb->insert_id;
		}
		foreach ( array(
			array( 'name' => 'Unchangeable', 'slug' => 'unchangeable' ),
			array( 'name' => 'Undeletable',  'slug' => 'undeletable'  ),
		) as $term ) {
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$p}ah_taxonomies` WHERE type_id = %d AND slug = %s", $type_id, $term['slug'] ) );
			if ( ! $exists ) {
				$wpdb->insert( "{$p}ah_taxonomies", array( 'type_id' => $type_id, 'name' => $term['name'], 'slug' => $term['slug'], 'status' => 'active', 'is_protected' => 1 ) );
			} else {
				$wpdb->update( "{$p}ah_taxonomies", array( 'is_protected' => 1 ), array( 'id' => (int) $exists ) );
			}
		}
	}

	public static function review_categories_taxonomy_type(): void {
		global $wpdb;
		$p = $wpdb->prefix;
		$type_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$p}ah_taxonomy_types` WHERE slug = %s", 'review-categories' ) );
		if ( ! $type_id ) {
			$wpdb->insert( "{$p}ah_taxonomy_types", array( 'name' => 'Review Categories', 'slug' => 'review-categories', 'description' => 'Routes each review to the correct section.' ) );
			$type_id = (int) $wpdb->insert_id;
		}
		if ( ! $type_id ) return;
	}

	public static function contact_form_rule_cc(): void {
		if ( ! class_exists( 'AH_Rules_Engine' ) ) return;
		global $wpdb;
		$t   = AH_Rules_Engine::table();
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT id, actions FROM `{$t}` WHERE trigger_name = %s AND name = %s LIMIT 1", 'sugarcane_contact_form', 'Sugarcane - Contact Form Emails' ) );
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
			$wpdb->update( $t, array( 'actions' => wp_json_encode( $actions ) ), array( 'id' => (int) $row->id ), array( '%s' ), array( '%d' ) );
		}
	}

	public static function spotlight_terms_page_slug(): void {
		self::add_column_if_missing( 'ah_spotlight_terms', 'page_slug', "VARCHAR(200) NOT NULL DEFAULT '' AFTER `slug`" );
	}

	// ── Helper ────────────────────────────────────────────────────────────────


	public static function faq_section_column(): void {
		self::add_column_if_missing( 'ah_faqs', 'section', "VARCHAR(150) DEFAULT NULL AFTER `page_id`" );
	}

	public static function site_notices_badge_position(): void {
		self::add_column_if_missing( 'ah_site_notices', 'badge_text',  "VARCHAR(80) DEFAULT NULL AFTER `button_url`" );
		self::add_column_if_missing( 'ah_site_notices', 'badge_color', "VARCHAR(20) NOT NULL DEFAULT 'green' AFTER `badge_text`" );
		self::add_column_if_missing( 'ah_site_notices', 'position',    "ENUM('modal','corner') NOT NULL DEFAULT 'modal' AFTER `badge_color`" );
	}

	public static function site_notices_extra_features(): void {
		self::add_column_if_missing( 'ah_site_notices', 'trigger_scroll', "TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `trigger_delay`" );
		self::add_column_if_missing( 'ah_site_notices', 'device',         "ENUM('all','mobile','desktop') NOT NULL DEFAULT 'all' AFTER `slugs`" );
		self::add_column_if_missing( 'ah_site_notices', 'show_from',      "DATE DEFAULT NULL AFTER `device`" );
		self::add_column_if_missing( 'ah_site_notices', 'show_until',     "DATE DEFAULT NULL AFTER `show_from`" );
		self::add_column_if_missing( 'ah_site_notices', 'auto_close',     "SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER `show_until`" );
	}

	public static function site_notices_custom_freq(): void {
		self::add_column_if_missing( 'ah_site_notices', 'frequency_custom_mins', "SMALLINT UNSIGNED NOT NULL DEFAULT 60 AFTER `frequency`" );
	}

	public static function resources_link_url(): void {
		self::add_column_if_missing( 'ah_resources', 'link_url', "VARCHAR(1000) DEFAULT NULL AFTER `description`" );
	}

	public static function resources_highlight_label(): void {
		self::add_column_if_missing( 'ah_resources', 'highlight_label', "VARCHAR(80) DEFAULT NULL AFTER `link_url`" );
	}

	private static function add_column_if_missing( string $table_suffix, string $column, string $definition ): void {
		global $wpdb;
		$table = $wpdb->prefix . $table_suffix;
		if ( ! $wpdb->get_var( $wpdb->prepare(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
			DB_NAME, $table, $column
		) ) ) {
			$wpdb->query( "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
	}
}
