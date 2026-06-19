<?php
/**
 * includes/class-expert-db.php - DB model for admin-managed experts / team members.
 *
 * Table: {prefix}ah_experts
 * One row per expert: stores profile info, bio, bullets, client images and optional
 * full custom HTML profile content.
 */

defined( 'ABSPATH' ) || exit;

class AH_Expert_DB {

	const DB_VERSION = '4';
	const DB_OPTION  = 'adn_expert_db_v';

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'ah_experts';
	}

	private static function table_exists() {
		global $wpdb;
		$t = self::table();
		return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $t ) ) === $t;
	}

	/** Return a single row by expert_slug, or null if not found / table missing. */
	public static function get( $slug ) {
		global $wpdb;
		if ( ! self::table_exists() ) { return null; }
		return $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE expert_slug = %s', sanitize_key( $slug ) ),
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
				$wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE status = %s ORDER BY name ASC', $status ),
				ARRAY_A
			);
		}
		return $wpdb->get_results( 'SELECT * FROM ' . self::table() . ' ORDER BY name ASC', ARRAY_A );
	}

	/**
	 * Insert or update an expert row.
	 *
	 * $data keys: expert_slug, name, title, category, status, photo_id,
	 *             bio, rating, reviews_count, location, phone, email,
	 *             bullets (PHP array), client_images (PHP array), mega_html.
	 */
	public static function save( $data ) {
		global $wpdb;
		if ( ! self::table_exists() ) { return false; }

		$slug   = sanitize_key( isset( $data['expert_slug'] ) ? $data['expert_slug'] : '' );
		$now    = current_time( 'mysql' );
		$exists = self::get( $slug );

		// bullets: accept array or already-encoded string.
		$bullets_raw = isset( $data['bullets'] ) ? $data['bullets'] : array();
		if ( is_array( $bullets_raw ) ) {
			$bullets = wp_json_encode( array_values( $bullets_raw ) );
		} else {
			$bullets = (string) $bullets_raw;
		}

		// client_images: accept array or already-encoded string.
		$ci_raw = isset( $data['client_images'] ) ? $data['client_images'] : array();
		if ( is_array( $ci_raw ) ) {
			$client_images = wp_json_encode( array_values( $ci_raw ) );
		} else {
			$client_images = (string) $ci_raw;
		}

		// rating clamped 0-5.
		$rating = isset( $data['rating'] ) ? floatval( $data['rating'] ) : 0.0;
		if ( $rating < 0 ) { $rating = 0.0; }
		if ( $rating > 5 ) { $rating = 5.0; }

		// banner: array of { icon, value, label } items or already-encoded JSON string.
		$banner_raw = isset( $data['banner'] ) ? $data['banner'] : array();
		if ( is_array( $banner_raw ) ) {
			$banner_json = wp_json_encode( array_values( $banner_raw ) );
		} else {
			$banner_json = (string) $banner_raw;
		}

		$row = array(
			'expert_slug'   => $slug,
			'name'          => sanitize_text_field( isset( $data['name'] )          ? $data['name']          : '' ),
			'title'         => sanitize_text_field( isset( $data['title'] )         ? $data['title']         : '' ),
			'category'      => sanitize_text_field( isset( $data['category'] )      ? $data['category']      : '' ),
			'status'        => ( isset( $data['status'] ) && 'inactive' === $data['status'] ) ? 'inactive' : 'active',
			'photo_id'      => absint( isset( $data['photo_id'] )       ? $data['photo_id']       : 0 ),
			'bio'           => sanitize_textarea_field( isset( $data['bio'] ) ? $data['bio'] : '' ),
			'rating'        => $rating,
			'reviews_count' => absint( isset( $data['reviews_count'] )  ? $data['reviews_count']  : 0 ),
			'location'      => sanitize_text_field( isset( $data['location'] )      ? $data['location']      : '' ),
			'phone'         => sanitize_text_field( isset( $data['phone'] )         ? $data['phone']         : '' ),
			'email'         => sanitize_email( isset( $data['email'] )              ? $data['email']         : '' ),
			'bullets'        => $bullets,
			'client_images'  => $client_images,
			'banner_image_id' => absint( isset( $data['banner_image_id'] ) ? $data['banner_image_id'] : 0 ),
			'banner_json'    => $banner_json,
			'mega_html'     => isset( $data['mega_html'] ) ? wp_unslash( $data['mega_html'] ) : '',
			'updated_at'    => $now,
		);

		if ( $exists ) {
			$wpdb->update( self::table(), $row, array( 'expert_slug' => $slug ) );
		} else {
			$row['created_at'] = $now;
			$wpdb->insert( self::table(), $row );
		}
		return true;
	}

	/** Delete an expert row by slug. */
	public static function delete( $slug ) {
		global $wpdb;
		if ( ! self::table_exists() ) { return; }
		$wpdb->delete( self::table(), array( 'expert_slug' => sanitize_key( $slug ) ) );
	}

	/** Create / upgrade the table via dbDelta. */
	public static function install() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset = $wpdb->get_charset_collate();
		$t       = self::table();
		dbDelta( "CREATE TABLE {$t} (
			id             BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
			expert_slug    VARCHAR(100)     NOT NULL,
			name           VARCHAR(200)     NOT NULL DEFAULT '',
			title          VARCHAR(200)     NOT NULL DEFAULT '',
			category       VARCHAR(100)     NOT NULL DEFAULT '',
			status         VARCHAR(10)      NOT NULL DEFAULT 'active',
			photo_id       BIGINT UNSIGNED  NOT NULL DEFAULT 0,
			bio            TEXT             NOT NULL,
			rating         DECIMAL(3,2)     NOT NULL DEFAULT 0.00,
			reviews_count  INT              NOT NULL DEFAULT 0,
			location       VARCHAR(300)     NOT NULL DEFAULT '',
			phone          VARCHAR(50)      NOT NULL DEFAULT '',
			email          VARCHAR(200)     NOT NULL DEFAULT '',
			bullets        LONGTEXT         NOT NULL,
			client_images  LONGTEXT         NOT NULL,
			banner_image_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			banner_json    LONGTEXT         NOT NULL,
			mega_html      LONGTEXT         NOT NULL,
			created_at     DATETIME         NOT NULL,
			updated_at     DATETIME         NOT NULL,
			PRIMARY KEY    (id),
			UNIQUE KEY     expert_slug (expert_slug)
		) {$charset};" );
		update_option( self::DB_OPTION, self::DB_VERSION );
	}

	/** Run install only when the stored version doesn't match OR the table is missing. */
	public static function maybe_install() {
		if ( get_option( self::DB_OPTION ) !== self::DB_VERSION || ! self::table_exists() ) {
			self::install();
		}
	}
}
