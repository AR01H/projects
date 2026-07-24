<?php
/**
 * Home Hero Banners - DB-backed manager (plugin level).
 *
 * Self-installing table: {prefix}ah_home_banners
 * Repeater-style save (delete-all + re-insert), like AH_Form_Builder::save_fields().
 * Autoplay interval is stored in a WP option.
 */
defined( 'ABSPATH' ) || exit;

class AH_Banners_Helper {

	const AUTOPLAY_OPTION = 'ah_banner_autoplay';

	/** Fully-qualified table name. */
	public static function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'ah_home_banners';
	}

	/**
	 * Ensure the table exists.
	 *
	 * The schema is owned by the central installer
	 * (database/class-db-installer.php → ensure_banners_table()). This just
	 * delegates to it, so a read never fails if the upgrade hook hasn't run yet
	 * - the CREATE TABLE statement lives only at the schema level.
	 */
	public static function install_table(): void {
		if ( method_exists( 'AH_DB_Installer', 'ensure_banners_table' ) ) {
			AH_DB_Installer::ensure_banners_table();
		}
	}

	/**
	 * Fetch banners.
	 *
	 * @param bool $only_active Return only active banners (for the frontend).
	 * @return array<int,array<string,mixed>>
	 */
	public static function get_all( bool $only_active = false ): array {
		global $wpdb;
		self::install_table();
		$t     = self::table();
		$where = $only_active ? "WHERE status = 'active'" : '';
		$rows  = $wpdb->get_results( "SELECT * FROM `{$t}` {$where} ORDER BY sort_order ASC, id ASC", ARRAY_A );
		return $rows ?: array();
	}

	/**
	 * Replace all banners with the supplied list (repeater save).
	 *
	 * @param array<int,array<string,mixed>> $banners
	 */
	public static function save_all( array $banners ): void {
		global $wpdb;
		self::install_table();
		$t = self::table();

		$wpdb->query( "DELETE FROM `{$t}`" );

		$order = 0;
		foreach ( $banners as $b ) {
			$image = esc_url_raw( $b['image'] ?? '' );
			$title = self::sanitize_title( $b['title'] ?? '' );
			// Skip empty rows (no image and no title = nothing to show).
			if ( '' === $image && '' === wp_strip_all_tags( $title ) ) {
				continue;
			}

			$wpdb->insert( $t, array(
				'image'        => $image,
				'image_mobile' => esc_url_raw( $b['image_mobile'] ?? '' ),
				'subtitle'    => sanitize_text_field( $b['subtitle'] ?? '' ),
				'title'       => $title,
				'description' => sanitize_textarea_field( $b['description'] ?? '' ),
				'btn_text'    => sanitize_text_field( $b['btn_text'] ?? '' ),
				'btn_url'     => esc_url_raw( $b['btn_url'] ?? '' ),
				'btn_target'  => ( ( $b['btn_target'] ?? '_self' ) === '_blank' ) ? '_blank' : '_self',
				'text_align'  => self::allowed( $b['text_align'] ?? 'center', array( 'left', 'center', 'right' ), 'center' ),
				'text_pos'    => self::allowed( $b['text_pos'] ?? 'middle', array( 'top', 'middle', 'bottom' ), 'middle' ),
				'overlay'     => self::sanitize_overlay( $b['overlay'] ?? '' ),
				'status'      => ( ( $b['status'] ?? 'active' ) === 'inactive' ) ? 'inactive' : 'active',
				'sort_order'  => $order++,
			) );
		}
	}

	/** Autoplay interval (ms). */
	public static function get_autoplay(): int {
		$v = (int) get_option( self::AUTOPLAY_OPTION, 5000 );
		return $v > 0 ? $v : 5000;
	}

	public static function save_autoplay( int $ms ): void {
		update_option( self::AUTOPLAY_OPTION, max( 1000, min( 30000, $ms ) ) );
	}

	/** Get a single banner by ID. */
	public static function find( int $id ): ?array {
		global $wpdb;
		self::install_table();
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `" . self::table() . "` WHERE id = %d", $id ), ARRAY_A );
		return $row ?: null;
	}

	/** Save a single banner (create or update). */
	public static function save_single( int $id, array $data ): int {
		global $wpdb;
		self::install_table();
		$t = self::table();
		$clean = array(
			'image'        => esc_url_raw( $data['image'] ?? '' ),
			'image_mobile' => esc_url_raw( $data['image_mobile'] ?? '' ),
			'subtitle'    => sanitize_text_field( $data['subtitle'] ?? '' ),
			'title'       => self::sanitize_title( $data['title'] ?? '' ),
			'description' => sanitize_textarea_field( $data['description'] ?? '' ),
			'btn_text'    => sanitize_text_field( $data['btn_text'] ?? '' ),
			'btn_url'     => esc_url_raw( $data['btn_url'] ?? '' ),
			'btn_target'  => ( ( $data['btn_target'] ?? '_self' ) === '_blank' ) ? '_blank' : '_self',
			'text_align'  => self::allowed( $data['text_align'] ?? 'center', array( 'left', 'center', 'right' ), 'center' ),
			'text_pos'    => self::allowed( $data['text_pos'] ?? 'middle', array( 'top', 'middle', 'bottom' ), 'middle' ),
			'overlay'     => self::sanitize_overlay( $data['overlay'] ?? '' ),
			'status'      => ( ( $data['status'] ?? 'active' ) === 'inactive' ) ? 'inactive' : 'active',
			'sort_order'  => (int) ( $data['sort_order'] ?? 0 ),
		);
		if ( $id > 0 ) {
			$wpdb->update( $t, $clean, array( 'id' => $id ), null, array( '%d' ) );
			return $id;
		}
		$wpdb->insert( $t, $clean );
		return (int) $wpdb->insert_id;
	}

	/** Delete a single banner. */
	public static function delete_single( int $id ): void {
		global $wpdb;
		$wpdb->delete( self::table(), array( 'id' => $id ), array( '%d' ) );
	}

	/** Default banners - used when the table is empty (first run / fallback). */
	public static function defaults(): array {
		return [];
	}

	// ── internal sanitisers ──────────────────────────────────────────────────

	private static function allowed( string $val, array $allowed, string $fallback ): string {
		return in_array( $val, $allowed, true ) ? $val : $fallback;
	}

	/** Allow only a tiny set of inline tags in the title (br/em/strong/span). */
	private static function sanitize_title( string $html ): string {
		return wp_kses( $html, array(
			'br'     => array(),
			'em'     => array(),
			'strong' => array(),
			'span'   => array( 'class' => array(), 'style' => array() ),
		) );
	}

	/** Keep overlay to a safe CSS colour value (rgba/hex/named-ish). */
	private static function sanitize_overlay( string $val ): string {
		$val = trim( $val );
		if ( '' === $val ) {
			return 'rgba(26,58,15,0.45)';
		}
		// Only allow characters valid in rgba()/hex CSS colour values.
		if ( preg_match( '/^[a-zA-Z0-9.,()#%\s]+$/', $val ) ) {
			return $val;
		}
		return 'rgba(26,58,15,0.45)';
	}
}
