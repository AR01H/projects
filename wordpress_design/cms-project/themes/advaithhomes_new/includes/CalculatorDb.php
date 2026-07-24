<?php
/**
 * includes/CalculatorDb.php - DB model for admin-created calculators.
 *
 * Table: {prefix}ah_calculators
 * One row per calculator: stores the key, display info, HTML markup and JS logic.
 * File-based calculators (calculators/views/*.php) are NOT stored here - they work
 * independently. Only calculators created via the admin "Add Calculator" tab live here.
 */

defined( 'ABSPATH' ) || exit;

class AH_Calculator_DB {

	const DB_VERSION = '1';
	const DB_OPTION  = 'adn_calc_db_v';

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'ah_calculators';
	}

	private static function table_exists() {
		global $wpdb;
		$t = self::table();
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $t ) ) === $t ) {
			return true;
		}
		// Table missing - attempt creation and re-check.
		self::install();
		return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $t ) ) === $t;
	}

	/** Return a single row by key, or null if not found / table missing. */
	public static function get( $key ) {
		global $wpdb;
		if ( ! self::table_exists() ) { return null; }
		return $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE calc_key = %s', sanitize_key( $key ) ),
			ARRAY_A
		);
	}

	/**
	 * Return all rows, optionally filtered by status ('active' | 'inactive').
	 * Returns an empty array when the table does not yet exist.
	 */
	public static function get_all( $status = '' ) {
		global $wpdb;
		if ( ! self::table_exists() ) { return array(); }
		if ( '' !== $status ) {
			return $wpdb->get_results(
				$wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE status = %s ORDER BY title ASC', $status ),
				ARRAY_A
			);
		}
		return $wpdb->get_results( 'SELECT * FROM ' . self::table() . ' ORDER BY title ASC', ARRAY_A );
	}

	/**
	 * Insert or update a calculator row.
	 * $data keys: calc_key, title, icon, label, html_content, js_content, status.
	 */
	public static function save( $data ) {
		global $wpdb;
		if ( ! self::table_exists() ) { return false; }

		$key    = sanitize_key( isset( $data['calc_key'] ) ? $data['calc_key'] : '' );
		$now    = current_time( 'mysql' );
		$exists = self::get( $key );

		$row = array(
			'calc_key'     => $key,
			'title'        => sanitize_text_field( isset( $data['title'] )    ? $data['title']    : '' ),
			'icon'         => sanitize_text_field( isset( $data['icon'] )     ? $data['icon']     : '' ),
			'label'        => sanitize_text_field( isset( $data['label'] )    ? $data['label']    : '' ),
			'html_content' => isset( $data['html_content'] ) ? wp_unslash( $data['html_content'] ) : '',
			'js_content'   => isset( $data['js_content'] )   ? wp_unslash( $data['js_content'] )   : '',
			'status'       => ( isset( $data['status'] ) && 'inactive' === $data['status'] ) ? 'inactive' : 'active',
			'updated_at'   => $now,
		);

		if ( $exists ) {
			$wpdb->update( self::table(), $row, array( 'calc_key' => $key ) );
		} else {
			$row['created_at'] = $now;
			$wpdb->insert( self::table(), $row );
		}
		return true;
	}

	/** Delete a DB-stored calculator by key. */
	public static function delete( $key ) {
		global $wpdb;
		if ( ! self::table_exists() ) { return; }
		$wpdb->delete( self::table(), array( 'calc_key' => sanitize_key( $key ) ) );
	}

	/** Create / upgrade the table via dbDelta. */
	public static function install() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset = $wpdb->get_charset_collate();
		$t       = self::table();
		dbDelta( "CREATE TABLE {$t} (
			id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			calc_key      VARCHAR(50)     NOT NULL,
			title         VARCHAR(200)    NOT NULL DEFAULT '',
			icon          VARCHAR(20)     NOT NULL DEFAULT '',
			label         VARCHAR(200)    NOT NULL DEFAULT '',
			html_content  LONGTEXT,
			js_content    LONGTEXT,
			status        VARCHAR(10)     NOT NULL DEFAULT 'active',
			created_at    DATETIME        NOT NULL,
			updated_at    DATETIME        NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY calc_key (calc_key)
		) {$charset};" );
		// Only mark installed if the table now exists (dbDelta fails silently).
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $t ) ) === $t ) {
			update_option( self::DB_OPTION, self::DB_VERSION );
		}
	}

	/** Run install only when the stored version doesn't match. */
	public static function maybe_install() {
		if ( get_option( self::DB_OPTION ) !== self::DB_VERSION ) {
			self::install();
		}
	}
}
