<?php
/**
 * includes/class-category-settings.php  -  Per-category admin settings DB model.
 *
 * Table: {prefix}ah_category_settings
 *   id          bigint UNSIGNED AUTO_INCREMENT
 *   slug        varchar(100)   - parent term slug (buying, selling …)
 *   section     varchar(50)    - appearance | journey | hot_topics |
 *                                popular_posts | calculators | sidebar | cta_banner
 *   data        longtext       - JSON payload for that section
 *   updated_at  datetime
 *
 * One row per (slug × section). Upserted on save.
 */

defined( 'ABSPATH' ) || exit;

class AH_Category_Settings {

	const DB_VERSION = '1';
	const DB_OPTION  = 'adn_cat_settings_db_v';

	// ── Install ──────────────────────────────────────────────────────────────────

	public static function maybe_install() {
		if ( (string) get_option( self::DB_OPTION, '0' ) === self::DB_VERSION ) {
			return;
		}
		self::install();
		update_option( self::DB_OPTION, self::DB_VERSION );
	}

	public static function install() {
		global $wpdb;
		$table   = self::table();
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE {$table} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			slug varchar(100) NOT NULL,
			section varchar(50) NOT NULL,
			data longtext NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY uq_slug_section (slug,section)
		) {$charset};";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	// ── Table helper ─────────────────────────────────────────────────────────────

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'ah_category_settings';
	}

	private static function table_exists() {
		global $wpdb;
		$t = self::table();
		return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $t ) ) === $t;
	}

	// ── Read ─────────────────────────────────────────────────────────────────────

	/** Return decoded data for one section, or empty array when absent. */
	public static function get( $slug, $section ) {
		if ( ! self::table_exists() ) {
			return array();
		}
		global $wpdb;
		$row = $wpdb->get_var( $wpdb->prepare(
			'SELECT data FROM ' . self::table() . ' WHERE slug = %s AND section = %s LIMIT 1',
			sanitize_key( $slug ),
			sanitize_key( $section )
		) );
		if ( null === $row || '' === $row ) {
			return array();
		}
		$d = json_decode( $row, true );
		return is_array( $d ) ? $d : array();
	}

	/** Return all sections for a slug as [ section => data_array ]. */
	public static function get_all( $slug ) {
		if ( ! self::table_exists() ) {
			return array();
		}
		global $wpdb;
		$rows = $wpdb->get_results( $wpdb->prepare(
			'SELECT section, data FROM ' . self::table() . ' WHERE slug = %s',
			sanitize_key( $slug )
		) );
		$result = array();
		foreach ( (array) $rows as $row ) {
			$d = json_decode( $row->data, true );
			$result[ $row->section ] = is_array( $d ) ? $d : array();
		}
		return $result;
	}

	// ── Write ────────────────────────────────────────────────────────────────────

	/** Upsert one section's data. */
	public static function save( $slug, $section, $data ) {
		if ( ! self::table_exists() ) {
			self::install();
		}
		global $wpdb;
		$table   = self::table();
		$slug    = sanitize_key( $slug );
		$section = sanitize_key( $section );
		$json    = wp_json_encode( $data );
		$now     = current_time( 'mysql' );

		$existing = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM {$table} WHERE slug = %s AND section = %s LIMIT 1",
			$slug, $section
		) );

		if ( $existing ) {
			$wpdb->update(
				$table,
				array( 'data' => $json, 'updated_at' => $now ),
				array( 'id'   => (int) $existing ),
				array( '%s',   '%s' ),
				array( '%d' )
			);
		} else {
			$wpdb->insert(
				$table,
				array( 'slug' => $slug, 'section' => $section, 'data' => $json, 'updated_at' => $now ),
				array( '%s', '%s', '%s', '%s' )
			);
		}
	}
}
