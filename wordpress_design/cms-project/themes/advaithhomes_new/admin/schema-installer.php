<?php
defined( 'ABSPATH' ) || exit;

/**
 * ADN_Schema
 *
 * Creates this theme's database tables. Safe to call repeatedly -
 * dbDelta only applies missing tables/columns. Call create_all()
 * before any insert (same pattern as canehouse's CH_Schema).
 */
class ADN_Schema {

	/** {prefix}adn_contact_submissions */
	public static function contact_table() {
		global $wpdb;
		return $wpdb->prefix . 'adn_contact_submissions';
	}

	public static function create_all() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();
		$table           = self::contact_table();

		dbDelta( "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(190) NOT NULL DEFAULT '',
			email VARCHAR(190) NOT NULL DEFAULT '',
			phone VARCHAR(50) NOT NULL DEFAULT '',
			topic VARCHAR(100) NOT NULL DEFAULT 'general',
			message TEXT NOT NULL,
			ip_address VARCHAR(45) NOT NULL DEFAULT '',
			status VARCHAR(20) NOT NULL DEFAULT 'new',
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY status (status),
			KEY created_at (created_at)
		) {$charset_collate};" );
	}
}
