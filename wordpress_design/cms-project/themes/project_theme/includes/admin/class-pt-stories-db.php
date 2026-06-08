<?php
/**
 * PT_Stories_DB — database layer for the wp_pt_stories table.
 *
 * Call PT_Stories_DB::maybe_create() on admin_init (done in class-pt-stories-admin.php).
 * Bump DB_VERSION when columns change to trigger an automatic dbDelta upgrade.
 */

defined( 'ABSPATH' ) || exit;

class PT_Stories_DB {

	const TABLE      = 'pt_stories';
	const DB_VERSION = 'pt_stories_db_v1';

	/* ── Table name ──────────────────────────────────────────────── */

	public static function table(): string {
		global $wpdb;
		return $wpdb->prefix . self::TABLE;
	}

	/* ── Schema ──────────────────────────────────────────────────── */

	public static function maybe_create(): void {
		if ( get_option( self::DB_VERSION ) ) return;
		self::create_table();
		update_option( self::DB_VERSION, true );
	}

	public static function create_table(): void {
		global $wpdb;
		$t      = self::table();
		$cs     = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$t} (
			id              VARCHAR(100)  NOT NULL,
			title           VARCHAR(255)  NOT NULL DEFAULT '',
			client          VARCHAR(255)  NOT NULL DEFAULT '',
			industry        VARCHAR(100)  NOT NULL DEFAULT '',
			tagline         VARCHAR(500)  NOT NULL DEFAULT '',
			summary         TEXT          NOT NULL DEFAULT '',
			result_1_label  VARCHAR(100)  NOT NULL DEFAULT '',
			result_1_value  VARCHAR(50)   NOT NULL DEFAULT '',
			result_2_label  VARCHAR(100)  NOT NULL DEFAULT '',
			result_2_value  VARCHAR(50)   NOT NULL DEFAULT '',
			result_3_label  VARCHAR(100)  NOT NULL DEFAULT '',
			result_3_value  VARCHAR(50)   NOT NULL DEFAULT '',
			image           VARCHAR(500)  NOT NULL DEFAULT '',
			featured        TINYINT(1)    NOT NULL DEFAULT 0,
			published       TINYINT(1)    NOT NULL DEFAULT 1,
			sort_order      INT           NOT NULL DEFAULT 0,
			created_at      DATETIME      NOT NULL DEFAULT '0000-00-00 00:00:00',
			updated_at      DATETIME      NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY idx_published  (published),
			KEY idx_featured   (featured),
			KEY idx_sort       (sort_order)
		) {$cs};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	public static function drop_table(): void {
		global $wpdb;
		$t = esc_sql( self::table() );
		$wpdb->query( "DROP TABLE IF EXISTS `{$t}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		delete_option( self::DB_VERSION );
	}

	/* ── Read ────────────────────────────────────────────────────── */

	public static function all( bool $published_only = false ): array {
		global $wpdb;
		$t = self::table();
		if ( $published_only ) {
			return $wpdb->get_results( "SELECT * FROM `{$t}` WHERE published = 1 ORDER BY sort_order ASC, created_at ASC", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}
		return $wpdb->get_results( "SELECT * FROM `{$t}` ORDER BY sort_order ASC, created_at ASC", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	public static function find( string $id ): ?array {
		global $wpdb;
		$row = $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM `' . self::table() . '` WHERE id = %s LIMIT 1', $id ),
			ARRAY_A
		);
		return $row ?: null;
	}

	public static function count(): int {
		global $wpdb;
		return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM `' . self::table() . '`' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/* ── Write ───────────────────────────────────────────────────── */

	/**
	 * Insert or update a story row.
	 * $data must include 'id'. Returns true on success.
	 */
	public static function save( array $data ): bool {
		global $wpdb;

		$id       = sanitize_title( $data['id'] ?? '' );
		$now      = current_time( 'mysql' );
		$existing = self::find( $id );

		$row = [
			'id'             => $id,
			'title'          => sanitize_text_field( $data['title']         ?? '' ),
			'client'         => sanitize_text_field( $data['client']        ?? '' ),
			'industry'       => sanitize_text_field( $data['industry']      ?? '' ),
			'tagline'        => sanitize_text_field( $data['tagline']       ?? '' ),
			'summary'        => sanitize_textarea_field( $data['summary']   ?? '' ),
			'result_1_label' => sanitize_text_field( $data['result_1_label'] ?? '' ),
			'result_1_value' => sanitize_text_field( $data['result_1_value'] ?? '' ),
			'result_2_label' => sanitize_text_field( $data['result_2_label'] ?? '' ),
			'result_2_value' => sanitize_text_field( $data['result_2_value'] ?? '' ),
			'result_3_label' => sanitize_text_field( $data['result_3_label'] ?? '' ),
			'result_3_value' => sanitize_text_field( $data['result_3_value'] ?? '' ),
			'image'          => esc_url_raw( $data['image']    ?? '' ),
			'featured'       => (int) ! empty( $data['featured'] ),
			'published'      => (int) ! empty( $data['published'] ),
			'sort_order'     => (int) ( $data['sort_order'] ?? 0 ),
			'updated_at'     => $now,
		];

		$formats = [ '%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%d','%d','%d','%s' ];

		if ( $existing ) {
			return (bool) $wpdb->update( self::table(), $row, [ 'id' => $id ], $formats, [ '%s' ] );
		}

		$row['created_at'] = $now;
		$formats[]         = '%s';
		return (bool) $wpdb->insert( self::table(), $row, $formats );
	}

	public static function delete( string $id ): bool {
		global $wpdb;
		return (bool) $wpdb->delete( self::table(), [ 'id' => $id ], [ '%s' ] );
	}

	/**
	 * Bulk-update sort_order. $order is an array of IDs in desired order.
	 */
	public static function reorder( array $order ): void {
		global $wpdb;
		$t = self::table();
		foreach ( array_values( $order ) as $i => $id ) {
			$wpdb->update( $t, [ 'sort_order' => $i + 1 ], [ 'id' => sanitize_title( $id ) ], [ '%d' ], [ '%s' ] );
		}
	}
}
