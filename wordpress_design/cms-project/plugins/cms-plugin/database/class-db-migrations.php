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
		self::taxonomy_protected();
		self::taxonomy_media();
		self::rules_settings();
		self::trigger_logs_scheduled_at();

		// Table creations via migration (tables added after initial schema)
		self::ensure_content_taxonomies();
		self::ensure_taxonomy_parent_terms();
		self::ensure_static_pages();

		// Data migrations
		self::required_settings();
		self::protected_taxonomy_data();
		self::review_taxonomy_type();
		self::review_categories_taxonomy_type();
		self::faq_tags_taxonomy_type();
		self::sugarcane_contact_rule();
		self::contact_form_rule_cc();
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

	// ── Data migrations ───────────────────────────────────────────────────────

	public static function required_settings(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_site_settings';
		foreach ( array(
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

	public static function review_taxonomy_type(): void {
		global $wpdb;
		$p = $wpdb->prefix;
		$type_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$p}ah_taxonomy_types` WHERE slug = %s", 'review-type' ) );
		if ( ! $type_id ) {
			$wpdb->insert( "{$p}ah_taxonomy_types", array( 'name' => 'Review Type', 'slug' => 'review-type', 'description' => 'Categorises reviews by audience.' ) );
			$type_id = (int) $wpdb->insert_id;
		}
		if ( ! $type_id ) return;
		foreach ( array(
			array( 'name' => 'Customer', 'slug' => 'customer' ),
			array( 'name' => 'Partner',  'slug' => 'partner'  ),
			array( 'name' => 'Event',    'slug' => 'event'    ),
		) as $term ) {
			if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$p}ah_taxonomies` WHERE type_id = %d AND slug = %s", $type_id, $term['slug'] ) ) ) {
				$wpdb->insert( "{$p}ah_taxonomies", array( 'type_id' => $type_id, 'name' => $term['name'], 'slug' => $term['slug'], 'status' => 'active' ) );
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
		foreach ( array(
			array( 'name' => 'Customer Review',   'slug' => 'customer'     ),
			array( 'name' => 'Franchise Partner', 'slug' => 'partner'      ),
			array( 'name' => 'Event Review',      'slug' => 'event'        ),
			array( 'name' => 'Client Story',      'slug' => 'client-story' ),
		) as $term ) {
			if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$p}ah_taxonomies` WHERE type_id = %d AND slug = %s", $type_id, $term['slug'] ) ) ) {
				$wpdb->insert( "{$p}ah_taxonomies", array( 'type_id' => $type_id, 'name' => $term['name'], 'slug' => $term['slug'], 'status' => 'active' ) );
			}
		}
	}

	public static function faq_tags_taxonomy_type(): void {
		global $wpdb;
		$p = $wpdb->prefix;
		$type_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$p}ah_taxonomy_types` WHERE slug = %s", 'faq-tags' ) );
		if ( ! $type_id ) {
			$wpdb->insert( "{$p}ah_taxonomy_types", array( 'name' => 'FAQ Tags', 'slug' => 'faq-tags', 'description' => 'Tags used to group and filter FAQs.' ) );
			$type_id = (int) $wpdb->insert_id;
		}
		if ( ! $type_id ) return;
		foreach ( array(
			array( 'name' => 'General',   'slug' => 'general'   ),
			array( 'name' => 'Events',    'slug' => 'events'    ),
			array( 'name' => 'Juice',     'slug' => 'juice'     ),
			array( 'name' => 'Franchise', 'slug' => 'franchise' ),
		) as $term ) {
			if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$p}ah_taxonomies` WHERE type_id = %d AND slug = %s", $type_id, $term['slug'] ) ) ) {
				$wpdb->insert( "{$p}ah_taxonomies", array( 'type_id' => $type_id, 'name' => $term['name'], 'slug' => $term['slug'], 'status' => 'active' ) );
			}
		}
	}

	public static function sugarcane_contact_rule(): void {
		if ( ! class_exists( 'AH_Rules_Engine' ) ) return;
		global $wpdb;
		$t = AH_Rules_Engine::table();
		if ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$t}` WHERE trigger_name = %s LIMIT 1", 'sugarcane_contact_form' ) ) ) return;
		AH_Rules_Engine::save( 0, array(
			'name' => 'Sugarcane - Contact Form Emails', 'trigger_name' => 'sugarcane_contact_form',
			'conditions_match' => 'all', 'conditions' => array(), 'status' => 'active',
			'actions' => array(
				array( 'type' => 'send_email', 'to' => '{config_email_from_email}', 'subject' => 'New Contact Enquiry from {name} - {enquiry_label}', 'body' => "You have received a new enquiry via The Cane House website.\n\nName: {name}\nEmail: {email}\nPhone: {phone}\nEnquiry Type: {enquiry_label}\n\nMessage:\n{message}\n\n---\nSent from {site_url}\nTime: {submitted_at}", 'html' => 0, 'cc' => '{config_email_from_email}' ),
				array( 'type' => 'send_email', 'to' => '{email}', 'subject' => "Thanks for contacting The Cane House - we'll be in touch soon!", 'body' => "Hi {name},\n\nThank you for getting in touch with The Cane House! \xf0\x9f\x8c\xbf\n\nWe've received your message and will get back to you very soon.\n\nIn the meantime, if your enquiry is urgent, please call us directly.\n\nPressed Fresh. Served Cool.\nThe Cane House Team", 'html' => 0, 'cc' => '{config_email_from_email}' ),
			),
		) );
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

	// ── Helper ────────────────────────────────────────────────────────────────

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
